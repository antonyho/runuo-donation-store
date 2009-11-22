<?php

session_start();

//shows all the transaction response from IPN in a table
function query_to_table($query)
{
	require_once('mysqldb_lib.php');
	
	$result = mysql_query($query);
	
	$table = "<table border=\"1\">\n";
	$table_header = "<tr>";
	for ($i = 0; $i < mysql_num_fields($result); $i++)
	{
		$meta = mysql_fetch_field($result, $i);
		$table_header .= "<th>".$meta->name."</th>";
	}
	$table_header .= "</tr>\n";
	
	$table_rows = "";
	while ($row = mysql_fetch_assoc($result))
	{
		$table_rows .= "<tr>";
		foreach($row as $field_value)
		{
			$table_rows .= "<td>$field_value</td>";
		}
		$table_rows .= "</tr>\n";
	}
	$table .= $table_header.$table_rows;
	$table .= "</table>\n";
	
	return $table;
}

function add_new_gift($name, $class_name, $price)
{
	require_once('mysqldb_lib.php');
	
	$query = "INSERT INTO gift_type (type_name,class_name,price) VALUES ('$name','$class_name','$price')";
	
	return mysql_query($query);
}

function remove_gift($id)
{
	require_once('mysqldb_lib.php');
	
	$query = "DELETE FROM gift_type WHERE type_id='$id'";
	
	return mysql_query($query);
}

function get_gift_code_table()
{
	require_once('mysqldb_lib.php');
	
	$query = "SELECT * FROM gift_type";
	
	$result = mysql_query($query);
	
	$table = "<table border=\"1\">\n";
	$table_header = "<tr>";
	for ($i = 0; $i < mysql_num_fields($result); $i++)
	{
		$meta = mysql_fetch_field($result, $i);
		$table_header .= "<th>".$meta->name."</th>";
	}
	$table_header .= "<th>Get Code</th>";
	$table_header .= "</tr>\n";
	
	$table_rows = "";
	while ($row = mysql_fetch_assoc($result))
	{
		$table_rows .= "<tr>";
		foreach($row as $field_value)
		{
			$table_rows .= "<td>$field_value</td>";
		}
		$table_rows .= "<td><a href=\"get_button_code.php?id={$row['type_id']}&name={$row['type_name']}&price={$row['price']}\" target=\"_blank\">[Get]</a></td>";
		$table_rows .= "</tr>\n";
	}
	$table .= $table_header.$table_rows;
	$table .= "</table>\n";
	
	return $table;
}

function manual_add_gift($type_id, $account_name, $quantity)
{
	require_once('mysqldb_lib.php');
	
	$now = time();
	$query = "INSERT INTO redeemable_gift (type_id,account_name,donate_time,paypal_txn_id) VALUES ('$type_id','$account_name','$now','0000000000')";		// use 0000000000 as manual added gifts
	for ($i=0; $i < $quantity; $i++)
	{
		mysql_query($query);
	}
}

function get_gift_types()
{
	require_once('mysqldb_lib.php');
	
	$query = "SELECT type_id, type_name FROM gift_type";
	$result = mysql_query($query);
	$gift_types = array();
	while ($row = mysql_fetch_assoc($result))
		array_push($gift_types, $row);
	
	return $gift_types;
}

function admin_login($username, $password)
{
	require_once('config.php');
	
	if (strcmp($username, ADMIN_USER) === 0 && strcmp($password, ADMIN_PASS) === 0)
	{
		$_SESSION['login_succeed'] = true;
	}
	else
	{
		$_SESSION['login_succeed'] = false;
	}
}
?>