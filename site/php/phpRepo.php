<?php
    session_start();

    if (!defined("IS_INCLUDED")){header("location: https://youtu.be/dQw4w9WgXcQ");}

    if (!isset($_SESSION["uuid"])){
        // Generate a UUID (Universally unique identifier) to use as temporary user identification
        $_SESSION["uuid"] = uniqid();
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