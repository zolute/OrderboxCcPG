<?php
if(!isset($_SESSION)) {
     session_start();
}
	 session_save_path("./"); //path on your server where you are storing session


	//file which has required functions
	require("functions.php");	
	require("libfuncs.php");

 ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" >
<html>
<head><title>Payment Execution - Final Step</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<font size=4>

<?php
		//ccavenue return params
		$WorkingKey = "your_ccavenue_key" ; //put in the 32 bit working key in the quotes provided here
	$Merchant_Id= $_REQUEST['Merchant_Id'];
	$Amount= $_REQUEST['Amount'];
	$Order_Id= $_REQUEST['Order_Id'];
	$Merchant_Param= $_REQUEST['Merchant_Param'];
	$Checksum= $_REQUEST['Checksum'];
	$AuthDesc=$_REQUEST['AuthDesc'];
	$card_category = $_REQUEST['card_category'];
		
    $cc_Checksum = cc_verifyChecksum($Merchant_Id, $Order_Id , $Amount,$AuthDesc,$Checksum,$WorkingKey);
	if($cc_Checksum == "true" && ($AuthDesc =="Y" || $AuthDesc=="N"))
	{
		//end of ccavenue return params
		
		//calculate transaction charge multiplier
		if($card_category == "CREDITCARD")
		{
			$multiplier = 0.9212;
		}elseif($card_category == "DEBITCARD"){
			$multiplier = 0.9859;
		}elseif($card_category == "NETBANKING"){
			$multiplier = 0.9550;
		}else{
			$multiplier = 0; //to avoid cheats/frauds
		}
		//end of calculate transsaction charge multiplier
		
		$resellerURL = $_SESSION['resellerURL'];
		$resellerKEY = array(
		"username.myorderbox.com"=>"yourkeygoeshere",
		"username.myorderbox.com"=>"yourkeysgoeshere");//enter the keys in array. it will be selected depending on resellerurl
		$key = $resellerKEY[$resellerURL]; //replace ur 32 bit secure key , Get your secure key from your Reseller Control panel
	    

		$redirectUrl = $_SESSION['redirecturl'];  // redirectUrl received from foundation
		$transId = $_SESSION['transid'];		 //Pass the same transid which was passsed to your Gateway URL at the beginning of the transaction.
		$sellingCurrencyAmount = round($multiplier*$_SESSION['sellingcurrencyamount'],2);
		$accountingCurrencyAmount = round($multiplier*$_SESSION['accountingcurencyamount'],2);
$transaction_fees = $Amount - $accountingCurrencyAmount;

		$status = $AuthDesc;	 // Transaction status received from your Payment Gateway
        //This can be either 'Y' or 'N'. A 'Y' signifies that the Transaction went through SUCCESSFULLY and that the amount has been collected.
        //An 'N' on the other hand, signifies that the Transaction FAILED.

		/**HERE YOU HAVE TO VERIFY THAT THE STATUS PASSED FROM YOUR PAYMENT GATEWAY IS VALID.
	    * And it has not been tampered with. The data has not been changed since it can * easily be done with HTTP request. 
		*
		**/
		
		srand((double)microtime()*1000000);
		$rkey = rand();


		$checksum =generateChecksum($transId,$sellingCurrencyAmount,$accountingCurrencyAmount,$status, $rkey,$key);


		//here we will update invoice by either adding payment or cancelling the invoice depending if transaction has failed.
		
			
		
			

?>
<!-- here goes the code -->
<div class="wrapper">
  <div align="center" ><a href="http://www.zolute.com"><img src="zolute-logo.png" border="0" /></a><br />
    <em><strong>Payment Execution- Final Step </strong></em><br />
  </div>
  <div class="main">
    
    <p align="justify">&nbsp;</p>
    <center><table width="300" border="0">
  <tr>
    <td><strong><em>Transaction ID</em></strong></td>
    <td><?php echo $transId; ?></td>
  </tr>
  <tr>
    <td><strong><em>Status</em></strong></td>
    <td><?php if($status=="N"){
		echo "<font color=\"#FF0000\">Failed</font>";
	}elseif($status == "Y"){
		echo "Successful";
	}?></td>
  </tr>
  <tr>
    <td><strong><em>Payment Mode</em></strong></td>
    <td><?php echo $card_category;?></td>
  </tr>
  <tr>
    <td><strong><em>Amount</em></strong></td>
    <td><?php echo $Amount;?></td>
  </tr>
  <tr>
    <td><strong><em>Transaction Fees</em></strong></td>
    <td><?php echo $transaction_fees;?></td>
  </tr>
  <tr>
    <td><strong><em>Credit Amount</em></strong></td>
    <td><?php echo $accountingCurrencyAmount; ?></td>
  </tr>
  
    </table></center>
    <form name="f1" action="<?php echo $redirectUrl;?>">
			<input type="hidden" name="transid" value="<?php echo $transId;?>">
		    <input type="hidden" name="status" value="<?php echo $status;?>">
			<input type="hidden" name="rkey" value="<?php echo $rkey;?>">
		    <input type="hidden" name="checksum" value="<?php echo $checksum;?>">
		    <input type="hidden" name="sellingamount" value="<?php echo $sellingCurrencyAmount;?>">
			<input type="hidden" name="accountingamount" value="<?php echo $accountingCurrencyAmount;?>">
    <input type="submit" value="Click to Complete Process!" class="payment-button"/>
    </form>

  </div>
  <div class="footernote"></div>
</div>
        <?php
	}else
	{
		echo "<br>Security Error. Illegal access detected";
	}
		?>
</font>
</body>
</html>
<?php session_destroy(); ?>