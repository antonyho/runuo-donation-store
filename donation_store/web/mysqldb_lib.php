<?php

/******
* MySQL connection library
******/
	require_once('config.php');
	
	function connect_mysql()
	{
		$database = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
			
		return $database;
	}
	
	if (!$database) {
		$database = connect_mysql();
	}

?>