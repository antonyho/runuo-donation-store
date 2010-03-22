<?php

/***************************************************************************
 *                       donation_paypal_ipn_handler.php
 *                       -------------------------------
 *   begin                : Oct 24, 2009
 *   copyright            : (C) Antony Ho
 *   email                : ntonyworkshop@gmail.com
 *   website              : http://antonyho.net/
 *
 ***************************************************************************/


// PHP 4.1

$today = date('d/m/Y H:i:s', time());


//customize your own handling
function handle_payment($post_data)
{
	global $today;
	global $log;
	global $error_log;
	require_once('mysqldb_lib.php');
	require_once('config.php');
	//your handling in here.
	
	/**** record the transaction ****/
	// check the existence of transaction record in our db
	$query = "SELECT 1 FROM paypal_transaction WHERE txn_id='{$post_data['txn_id']}'";
	$result = mysql_query($query);
	if ($result === FALSE)
	{
		//db failure
		
		if ($error_log_fp = fopen($error_log, 'a+'))
		{
			$error_log_string = "=====================================\n";
			$error_log_string .= "database failure\n";
			$error_log_string .= "transaction id: ".$post_data['txn_id']."\n";
			$error_log_string .= "today: ".$today."\n";
			$error_log_string .= "Error message: ".mysql_error()."\n";
			$error_log_string .= "=====================================\n";
			write_to_log($error_log_fp, $error_log_string);
			fclose($error_log_fp);
		}
		return;
	}
	
	if (mysql_num_rows($result) > 0)
	{
		//having existing record
		$query = "UPDATE paypal_transaction SET ";
		
		foreach ($post_data as $field => $value)
		{
			$query .= $field."=";
			if (empty($value))
				$query .= "NULL,";
			else
				$query .= $value.",";
		}
		$query = rtrim($query, ",");
		$query .= " WHERE txn_id='{$post_data['txn_id']}'";
		
		$result = mysql_query($query);
		if ($result === FALSE || mysql_affected_rows($result) != 1)
		{
			if ($error_log_fp = fopen($error_log, 'a+'))
			{
				$error_log_string = "=====================================\n";
				$error_log_string .= "database [UPDATE] failure\n";
				$error_log_string .= "transaction id: ".$post_data['txn_id']."\n";
				$error_log_string .= "today: ".$today."\n";
				$error_log_string .= "query string: ".$query."\n";
				$error_log_string .= "query succeed?: ".$result."\n";
				$error_log_string .= "affected rows: ".mysql_affected_rows($result)."\n";
				$error_log_string .= "Error message: ".mysql_error()."\n";
				$error_log_string .= "=====================================\n";
				write_to_log($error_log_fp, $error_log_string);
				fclose($error_log_fp);
			}
			return;
		}
	}
	else
	{
		//no existing record
		$query_field_string = "";
		$query_value_string = "";
		
		foreach ($post_data as $field => $value)
		{
			$query_field_string .= $field.",";
			if (empty($value))
				$query_value_string .= "NULL,";
			else
				$query_value_string .= "'".$value."',";
		}
		$query_field_string = rtrim($query_field_string, ",");
		$query_value_string = rtrim($query_value_string, ",");
		
		$query = "INSERT INTO paypal_transaction ($query_field_string) VALUES ($query_value_string)";
		$result = mysql_query($query);
		if ($result === FALSE)
		{
			if ($error_log_fp = fopen($error_log, 'a+'))
			{
				$error_log_string = "=====================================\n";
				$error_log_string .= "database [INSERT] failure\n";
				$error_log_string .= "transaction id: ".$post_data['txn_id']."\n";
				$error_log_string .= "today: ".$today."\n";
				$error_log_string .= "query string: ".$query."\n";
				$error_log_string .= "Error message: ".mysql_error()."\n";
				$error_log_string .= "=====================================\n";
				write_to_log($error_log_fp, $error_log_string);
				fclose($error_log_fp);
			}
			return;
		}
	}
	mysql_free_result($result);
	
	// handle complete payment
	if (strcmp(trim($post_data['payment_status']), "Completed") == 0)
	{
		$txn_id = $post_data['txn_id'];
		$account_name = mysql_real_escape_string(trim($post_data['option_selection1']));
		if (empty($account_name))
			$account_name = mysql_real_escape_string(trim($post_data['custom']));
		$item_type_id = trim($post_data['item_number']);
		$item_quantity = trim($post_data['quantity']);
		
		$payment_amount = trim($post_data['mc_gross']);
		$payment_currency = trim($post_data['mc_currency']);
		
		
		$result = mysql_query("SELECT price FROM gift_type WHERE type_id='$item_type_id'");
		$row = mysql_fetch_assoc($result);
		$item_price = $row['price'];
		$payment_currency = trim($payment_currency);
		$local_currency = trim($local_currency);
		
		if ((strcmp(strtoupper($payment_currency), strtoupper($local_currency)) != 0 ) || $payment_amount != ($item_quantity*$item_price))
		{
			if ($error_log_fp = fopen($error_log, 'a+'))
			{
				$error_log_string = "=====================================\n";
				$error_log_string .= "currency or payment amount invalid\n";
				$error_log_string .= "transaction id: ".$txn_id."\n";
				$error_log_string .= "today: ".$today."\n";
				$error_log_string .= "account name: ".$account_name."\n";
				$error_log_string .= "local currency: ".$local_currency."\n";
				$error_log_string .= "IPN currency: ".$payment_currency."\n";
				$error_log_string .= "Payment amount: ".$payment_amount."\n";
				$error_log_string .= "quantity x price: ".$item_quantity." x ".$item_price."\n";
				$error_log_string .= "=====================================\n";
				write_to_log($error_log_fp, $error_log_string);
				fclose($error_log_fp);
			}
			return;
		}
		
		mysql_free_result($result);
		$check_txn_processed_query = "SELECT create_time FROM paypal_processed_txn WHERE txn_id='$txn_id'";
		$result = mysql_query($check_txn_processed_query);
		if (!$result || mysql_num_rows($result) > 0)
		{
			if ($error_log_fp = fopen($error_log, 'a+'))
			{
				$error_log_string = "=====================================\n";
				if (!result)
					$error_log_string .= "database query problem[check transaction existence]\n";
				else
					$error_log_string .= "processed transaction\n";
				$error_log_string .= "transaction id: ".$txn_id."\n";
				$error_log_string .= "today: ".$today."\n";
				$error_log_string .= "account name: ".$account_name."\n";
				if (!$result)
				{
					$error_log_string .= "dababase query result: ".$result."\n";
					$error_log_string .= "query: ".$check_txn_processed_query."\n";
				}
				else
				{
					$row = mysql_fetch_assoc($result);
					$error_log_string .= "last process time: ".$row['create_time']."\n";
				}
				$error_log_string .= "=====================================\n";
				write_to_log($error_log_fp, $error_log_string);
				fclose($error_log_fp);
			}
			return;
		}
		
		$now = time();
		
		mysql_free_result($result);
		$complete_transaction_query = "INSERT INTO paypal_processed_txn (txn_id) VALUES ('$txn_id')";
		$result = mysql_query($complete_transaction_query);
		if (!$result)
		{
			if ($error_log_fp = fopen($error_log, 'a+'))
			{
				$error_log_string = "=====================================\n";
				$error_log_string .= "database query problem[record processed transaction]\n";
				$error_log_string .= "transaction id: ".$txn_id."\n";
				$error_log_string .= "today: ".$today."\n";
				$error_log_string .= "account name: ".$account_name."\n";
				$error_log_string .= "query: ".$complete_transaction_query."\n";
				$error_log_string .= "Error message: ".mysql_error()."\n";
				$error_log_string .= "=====================================\n";
				write_to_log($error_log_fp, $error_log_string);
				fclose($error_log_fp);
			}
			return;
		}
		
		mysql_free_result($result);
		$add_gift_query = "INSERT INTO redeemable_gift (type_id,account_name,donate_time,paypal_txn_id) VALUES ('$item_type_id','$account_name','$now','$txn_id')";
		for ($i = 0; $i < $item_quantity; $i++)
		{
			mysql_free_result($result);
			$result = mysql_query($add_gift_query);
			if ($result === false)
			{
				if ($error_log_fp = fopen($error_log, 'a+'))
				{
					$error_log_string = "=====================================\n";
					$error_log_string .= "unable to insert all item into db\n";
					$error_log_string .= "transaction id: ".$txn_id."\n";
					$error_log_string .= "today: ".$today."\n";
					$error_log_string .= "account name: ".$account_name."\n";
					$error_log_string .= "total redeemable gift: ".$item_quantity."\n";
					$error_log_string .= "inserted number of gift: ".($i+1)."\n";
					$error_log_string .= "Error message: ".mysql_error()."\n";
					$error_log_string .= "=====================================\n";
					write_to_log($error_log_fp, $error_log_string);
					fclose($error_log_fp);
				}
				break;
			}
		}
	}
}




// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';


foreach ($_POST as $key => $value)
{
	$value = urlencode(stripslashes($value));
	$req .= "&$key=$value";
}

if (!function_exists('apache_request_headers'))
{
	eval('
		function apache_request_headers()
		{
			foreach($_SERVER as $key=>$value)
			{
				if (substr($key,0,5)=="HTTP_")
				{
					$key=str_replace(" ","-",ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
					$out[$key]=$value;
				}
			}
			return $out;
		}
	');
}

$headers = apache_request_headers();

if ($request_log_fp = fopen($request_log, 'a+'))
{
	$request_log_string = "=====================================\n";
	$request_log_string .= "[HEADERS]\n";
	foreach ($headers as $key => $value)
		$request_log_string .= $key.": ".$value."\n";
	
	$request_log_string .= "=====================================\n";
	$request_log_string .= "[DATA]\n";
	foreach ($_POST as $key => $value)
		$request_log_string .= $key.": ".$value."\n";
	
	$request_log_string .= "=====================================\n";
	write_to_log($request_log_fp, $request_log_string);
	fclose($request_log_fp);
}

// post back to PayPal system to validate
$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$fp = fsockopen ($paypal_ipn_resp_addr, 443, $errno, $errstr, 30);

// assign posted variables to local variables
$item_name = $_POST['item_name'];
$item_number = $_POST['item_number'];
$item_amount = $_POST['quantity'];
$option_name1 = $_POST['option_name1'];		//we use this as game account name information
$custom = $_POST['custom'];				//we use this as alternative game account name information
$option_value1 = $_POST['option_selection1'];
$payment_status = $_POST['payment_status'];
$payment_amount = $_POST['mc_gross'];
$payment_currency = $_POST['mc_currency'];
$txn_id = $_POST['txn_id'];
$receiver_email = $_POST['receiver_email'];
$receiver_id = $_POST['receiver_id'];
$payer_email = $_POST['payer_email'];

if (!$fp)
{
	// HTTP ERROR
	
	if ($log_fp = fopen($log, 'a+'))
	{
		write_to_log($log_fp, "=====================================\n".$today."\n".$header.$req."\n"."=====================================\n");
		fclose($log_fp);
	}
}
else
{
	$log_string = "=====================================\n";
	$log_string .= "today: ".$today."\n";
	$log_string .= "item name: ".$item_name."\n";
	$log_string .= "item number: ".$item_number."\n";
	$log_string .= "item amount: ".$item_amount."\n";
	$log_string .= $option_name1.": ".$option_value1."\n";
	$log_string .= "custom: ".$custom."\n";
	$log_string .= "payment status: ".$payment_status."\n";
	$log_string .= "payment amount: ".$payment_amount."\n";
	$log_string .= "payment currency: ".$payment_currency."\n";
	$log_string .= "transaction ID: ".$txn_id."\n";
	$log_string .= "receiver email: ".$receiver_email."\n";
	$log_string .= "receiver id: " .$receiver_id."\n";
	$log_string .= "payer email: ".$payer_email."\n";
	$log_string .= "=====================================\n";
	
	fputs ($fp, $header . $req);
	while (!feof($fp))
	{
		$res = fgets ($fp, 1024);
		if (strcmp ($res, "VERIFIED") == 0)
		{
			// check the payment_status is Completed
			// check that txn_id has not been previously processed
			// check that receiver_email is your Primary PayPal email
			// check that payment_amount/payment_currency are correct
			// process payment
			if ($log_fp = fopen($log, 'a+'))
			{
				write_to_log($log_fp, $log_string);
				fclose($log_fp);
			}
			
			if ((strcmp($receiver_email, $my_email) !== 0) || (strcmp($receiver_id, $my_merchant_id) !== 0))
			{
				if ($invalid_txn_log_fp = fopen($invalid_txn_log, 'a+'))
				{
					$invalid_log_string = "===============================================\n";
					$invalid_log_string .= "Transaction ID: ".$txn_id."\n";
					$invalid_log_string .= "Date: ".$today."\n";
					$invalid_log_string .= "invalid email. transaction rejected\n";
					$invalid_log_string .= "IPN receiver email: ".$receiver_email."\n";
					$invalid_log_string .= "Our email: ".$my_email."\n";
					$invalid_log_string .= "IPN receiver ID: ".$receiver_id."\n";
					$invalid_log_string .= "Our merchant ID: ".$my_merchant_id."\n";
					$invalid_log_string .= "===============================================\n";
					write_to_log($invalid_txn_log_fp, $invalid_log_string);
					fclose($invalid_txn_log_fp);
					fclose($fp);
				}
				return;
			}
			
			handle_payment($_POST);
		}
		else if (strcmp ($res, "INVALID") == 0)
		{
			// log for manual investigation
			if ($invalid_txn_log_fp = fopen($invalid_txn_log, 'a+'))
			{
				$our_ipn_response = $header.$req;
				$invalid_log_string = "===============================================\n";
				$invalid_log_string .= "Our response packet:\n";
				$invalid_log_string .= $our_ipn_response."\n";
				$invalid_log_string .= $log_string;
				$invalid_log_string .= "===============================================\n";
				write_to_log($invalid_txn_log_fp, $invalid_log_string);
				fclose($invalid_txn_log_fp);
			}
		}
	}
	fclose ($fp);
}


function write_to_log($fd, $string)
{
	for ($written = 0; $written < strlen($string); $written += $fwrite)
	{
		$fwrite = fwrite($fd, substr($string, $written));
		if (!$fwrite)
			return $written;
	}
	return $written;
}
?>
