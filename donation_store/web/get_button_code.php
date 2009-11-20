<?php
session_start();

if (isset($_SESSION['login_succeed']) && $_SESSION['login_succeed'] === true)
{
	$id = $_GET['id'];
	$name = $_GET['name'];
	$price = $_GET['price'];
	
	if (!(empty($id) || empty($name) || empty($price)))
	{
		require_once('config.php');
		$pp_uri = str_replace("ssl", "http", $paypal_ipn_resp_addr)."/cgi-bin/webscr";
	}
}

$temp = "<form action='$pp_uri' method='post'>
<!-- Identify your business so that you can collect the payments. -->
<input type='hidden' name='business' value='$my_merchant_id'>
<!-- Specify a Donate button. -->
<input type='hidden' name='cmd' value='_xclick'>
<!-- Specify details about the contribution -->
<input type='hidden' name='item_name' value='$name'>
<input type='hidden' name='item_number' value='$id'>
<input type='hidden' name='amount' value='$price'>
<input type='hidden' name='undefined_quantity' value='1'>
<input type='hidden' name='currency_code' value='$local_currency'>
<input type='hidden' name='tax' value='0'>
<input type='hidden' name='no_shipping' value='1'>
<input type='hidden' name='notify_url' value='$ipn_handler_url'>
Game account name: <input type='text' name='custom'><strong><font style='color: red;'>Remeber to fill your account name here!</font></strong><br/>
<!-- Display the payment button. -->
<input type='image' name='submit' border='0' src='https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif' alt='PayPal - The safer, easier way to pay online'>
<img alt='' border='0' width='1' height='1' src='https://www.paypal.com/en_US/i/scr/pixel.gif' >
</form>";

$output = htmlspecialchars($temp);

?>

<?php echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"; ?>

<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title>Donation Gift Button PayPal HTML Code</title>
	</head>
	<body>
		Copy the HTML code below:<br/>
		<textarea cols="80" rows="20" onclick="select()" readonly><?=$output ?></textarea>
	</body>
</html>