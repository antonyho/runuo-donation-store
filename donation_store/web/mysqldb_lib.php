<?php

/******
* MySQL connection library
******/
	require_once('config.php');
	
	function connect_mysql()
	{
		$database = mysql_connect(DB_HOST,DB_USER,DB_PASS) or die();
		mysql_select_db(DB_NAME, $database);
			
		return $database;
	}
	
	if (!$database) {
		$database = connect_mysql();
	}

?>