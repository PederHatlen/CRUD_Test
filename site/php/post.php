<?php
    define("IS_INCLUDED", TRUE);
    include 'phpRepo.php';

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if (isset($_POST["message"]) and isset($_SESSION["uuid"]) and strlen(trim($_POST["message"]))>0){
            $con = connect();

            // SQL-Injection proof SQL query
            $stmt = $con->prepare('INSERT into messages (message, user) VALUES (?, ?)');
            $stmt->bind_param('ss', $_POST["message"], $_SESSION["uuid"]); // 's' specifies the variable type => 'string'
            $stmt->execute();

            $con->close();
        }
    }else{
        // If the user didn't come here by post they get a surprise
        header("location: https://youtu.be/dQw4w9WgXcQ");
    }

    header("location: ../index.php");
?>