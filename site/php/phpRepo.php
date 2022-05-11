<?php
	session_start();

	if (!defined("IS_INCLUDED")){header("location: https://youtu.be/dQw4w9WgXcQ");}

	if (!isset($_SESSION["uuid"])){
		// Generate a UUID (Universally unique identifier) to use as temporary user identification
		$_SESSION["uuid"] = uniqid();
		
		// echo("UUID: ".$_SESSION["uuid"].", IP: ".$_SERVER["REMOTE_ADDR"]);

		$con = connect();

		// SQL-Injection proof SQL query
		$stmt = $con->prepare('INSERT into users (uuid, ip) VALUES (?, ?)');
		$stmt->bind_param('ss', $_SESSION["uuid"], $_SERVER["REMOTE_ADDR"]); // 's' specifies the variable type => 'string'
		$stmt->execute();

		$con->close();
	}

	// Basic connect functions
	function connect(){
		$servername = "localhost:3306";
		$username = "root";
		$password = "";
		$dbname = "crud";
	
		// Create connection
		$con = mysqli_connect($servername, $username, $password, $dbname);
		// Check connection
		if (!$con) {die("Connection failed: " . mysqli_connect_error());}
	
		//Angi UTF-8 som tegnsett
		$con->set_charset("utf8");
	
		return $con;
	}
?>