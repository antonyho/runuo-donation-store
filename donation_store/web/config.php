<?php

/*** MYSQL DATABASE SETTINGS ***/

//IP or domain name of your MySQL host
define("DB_HOST","dbexample.hkuoshard.com");

//The database name which we are using
define("DB_NAME","hkuoshard_example_db");

//user that can access to your MySQL database, be careful that this account should be CREATE TABLE privilege
define("DB_USER","example");

//password
define("DB_PASS","randomPassword");




/*** ADMIN PANEL SETTINGS ***/

//administrator login name
define("ADMIN_USER","sample_admin");

//password
define("ADMIN_PASS","adminPass");



/*** PAYPAL IPN SETTINGS ***/

//your email account for PayPal
$my_email = 'example@hkuoshard.com';
//your PayPal merchant ID
$my_merchant_id = 'N9TS8TE77L4YN';
//The currency of donation
$local_currency = "HKD";
//IPN handler URL, modify the domain name and the directory path to suit your site
$ipn_handler_url = "http://example.hkuoshard.com/paypal/donation_paypal_ipn_handler.php";

// do not edit below URL if you don't know what it is doing
$paypal_ipn_resp_addr = 'ssl://www.paypal.com';
//$paypal_ipn_resp_addr = 'ssl://www.sandbox.paypal.com';	/** for testing purpose **/

//logs file location of your webserver
$request_log = $_SERVER['DOCUMENT_ROOT'].'/logs/paypal_request.log';
$log = $_SERVER['DOCUMENT_ROOT'].'/logs/donation_paypal.log';
$error_log = $_SERVER['DOCUMENT_ROOT'].'/logs/donation_paypal_error.log';
$invalid_txn_log = $_SERVER['DOCUMENT_ROOT'].'/logs/donation_paypal_invalid_txn.log';

?>