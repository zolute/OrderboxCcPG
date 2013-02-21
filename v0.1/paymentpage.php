<?php
	session_start();
	@session_save_path("./");  //specify path where you want to save the session.
	require("functions.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" >	
<html>
<head><title>Zolute - Payment Execution - Step 1 </title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>


<?php
		$resellerURL = $_GET["resellerURL"]; //this will store reseller url
		//based on resellerURL we will now decide the api key to be used
		//change the array key to your provided orderbox url
		$resellerKEY = array(
		"username.myorderbox.com"=>"yourkeygoeshere",
		"username.myorderbox.com"=>"yourkeysgoeshere");

		
		$key = $resellerKEY[$resellerURL]; //replace ur 32 bit secure key , Get your secure key from your Reseller Control panel
		
		
		//Below are the  parameters which will be passed from foundation as http GET request

		$paymentTypeId = $_GET["paymenttypeid"];  //payment type id
		$transId = $_GET["transid"];			   //This refers to a unique transaction ID which we generate for each transaction
		$userId = $_GET["userid"];               //userid of the user who is trying to make the payment
		$userType = $_GET["usertype"];  		   //This refers to the type of user perofrming this transaction. The possible values are "Customer" or "Reseller"
		$transactionType = $_GET["transactiontype"];  //Type of transaction (ResellerAddFund/CustomerAddFund/ResellerPayment/CustomerPayment)

		$invoiceIds = $_GET["invoiceids"];		   //comma separated Invoice Ids, This will have a value only if the transactiontype is "ResellerPayment" or "CustomerPayment"
		$debitNoteIds = $_GET["debitnoteids"];	   //comma separated DebitNotes Ids, This will have a value only if the transactiontype is "ResellerPayment" or "CustomerPayment"

		$description = $_GET["description"];
		
		$sellingCurrencyAmount = $_GET["sellingcurrencyamount"]; //This refers to the amount of transaction in your Selling Currency
        $accountingCurrencyAmount = $_GET["accountingcurrencyamount"]; //This refers to the amount of transaction in your Accounting Currency

		$redirectUrl = $_GET["redirecturl"];  //This is the URL on our server, to which you need to send the user once you have finished charging him

						
		$checksum = $_GET["checksum"];	 //checksum for validation
		

		

		if(verifyChecksum($paymentTypeId, $transId, $userId, $userType, $transactionType, $invoiceIds, $debitNoteIds, $description, $sellingCurrencyAmount, $accountingCurrencyAmount, $key, $checksum))
		{
			//get all params required for billing
			$b_name = $_GET['name'];
			$b_email = $_GET['emailAddr'];
			$b_address1 = $_GET['address1'];
			$b_city = $_GET['city'];
			$b_state = $_GET['state'];
			$b_country = $_GET['country'];
			$b_tel = $_GET['telNo'];
			$b_zip = $_GET['zip'];
			
			
		
		/*
		lets start ccavenue checksum
		*/
		require("libfuncs.php");

	$Merchant_Id = "your_ccavenue_id" ;//This id(also User Id)  available at "Generate Working Key" of "Settings & Options" 
	$Amount = $accountingCurrencyAmount ;//your script should substitute the amount in the quotes provided here
	$Order_Id = $transId; ;//your script should substitute the order description in the quotes provided here
	$Redirect_Url = "https://<yoururl>/postpayment.php" ;//your redirect URL where your customer will be redirected after authorisation from CCAvenue
	$WorkingKey = "your_ccavenue_key"  ;//put in the 32 bit alphanumeric key in the quotes provided here.Please note that get this key ,login to your CCAvenue merchant account and visit the "Generate Working Key" section at the "Settings & Options" page. 
	$cc_Checksum = getCheckSum($Merchant_Id,$Amount,$Order_Id ,$Redirect_Url,$WorkingKey);

			

			
			$_SESSION['redirecturl']=$redirectUrl;
			$_SESSION['transid']=$transId;
			$_SESSION['sellingcurrencyamount']=$sellingCurrencyAmount;
			$_SESSION['accountingcurencyamount']=$accountingCurrencyAmount;
			$_SESSION['resellerURL']=$resellerURL;

?>
<!-- here goes our code by Zolute -->
<div class="wrapper">
  <div align="center" ><a href="http://www.zolute.com"><img src="zolute-logo.png" border="0" /></a><br />
    <em><strong>Payment Execution- Step 1 </strong></em><br />
  </div>
  <div class="main">
    <p>Welcome <?php echo $b_name; ?></p>
    <p align="justify">&nbsp;</p>
    <center><table width="300" border="0">
  <tr>
    <td><strong><em>Your ID</em></strong></td>
    <td><?php echo $userId; ?></td>
  </tr>
  <tr>
    <td><strong><em>Name</em></strong></td>
    <td><?php echo $b_name;?></td>
  </tr>
  <tr>
    <td><strong><em>Email Address</em></strong></td>
    <td><?php echo $b_email;?></td>
  </tr>
  <tr>
    <td><strong><em>Address</em></strong></td>
    <td><?php echo $b_address1;?></td>
  </tr>
  <tr>
    <td><strong><em>City</em></strong></td>
    <td><?php echo $b_city;?></td>
  </tr>
  <tr>
    <td><strong><em>State</em></strong></td>
    <td><?php echo $b_state;?></td>
  </tr>
  <tr>
    <td><strong><em>Country</em></strong></td>
    <td><?php echo $b_country;?></td>
  </tr>
  <tr>
    <td><strong><em>Tel</em></strong></td>
    <td><?php echo $b_tel; ?></td>
  </tr>
  <tr>
    <td><strong><em>Transaction Type</em></strong></td>
    <td><?php echo $transactionType; ?></td>
  </tr>
  <tr>
    <td><strong><em>Amount</em></strong></td>
    <td><?php echo $accountingCurrencyAmount; ?></td>
  </tr>
    </table></center>
    <form method="post" action="https://www.ccavenue.com/shopzone/cc_details.jsp">
	<input type="hidden" name="Merchant_Id" value="<?php echo $Merchant_Id; ?>">
	<input type="hidden" name="Amount" value="<?php echo $Amount; ?>">
	<input type="hidden" name="Order_Id" value="<?php echo $Order_Id; ?>">
	<input type="hidden" name="Redirect_Url" value="<?php echo $Redirect_Url; ?>">
	<input type="hidden" name="Checksum" value="<?php echo $cc_Checksum; ?>">
    <input type="hidden" name="billing_cust_name" value="<?php echo $b_name; ?>"> 
	<input type="hidden" name="billing_cust_address" value="<?php echo $b_address1; ?>"> 
	<input type="hidden" name="billing_cust_country" value="<?php echo $b_country; ?>"> 
	<input type="hidden" name="billing_cust_state" value="<?php echo $b_state; ?>"> 
	<input type="hidden" name="billing_zip" value="<?php echo $b_zip; ?>"> 
	<input type="hidden" name="billing_cust_tel" value="<?php echo $b_tel; ?>"> 
	<input type="hidden" name="billing_cust_email" value="<?php echo $b_email; ?>"> 
    <input type="submit" name="pay" value="Proceed to payment" class="payment-button">
    </form>

  </div>
  <div class="footernote"> </div>
</div>


<?php

		}
		else
		{
			/**This message will be dispayed in any of the following case
			*
			* 1. You are not using a valid 32 bit secure key from your Reseller Control panel
			* 2. The data passed from foundation has been tampered.
			*
			* In both these cases the customer has to be shown error message and shound not
			* be allowed to proceed  and do the payment.
			*
			**/

			echo "Checksum mismatch !";			

		}
?>
</body>
</html>
