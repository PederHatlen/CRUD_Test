<?php
    define("IS_INCLUDED", TRUE);
    include 'phpRepo.php';

    if ($_SERVER['REQUEST_METHOD'] == "POST" and isset($_POST["form"])) {

        // Connection function is in phpRepo
        $con = connect();

        switch ($_POST["form"]) {
            case 'send':
                if (isset($_POST["message"]) and isset($_SESSION["uuid"]) and strlen(trim($_POST["message"]))>0){
                    // SQL-Injection proof SQL query
                    $stmt = $con->prepare('INSERT into messages (message, user) VALUES (?, ?)');
                    $stmt->bind_param('ss', $_POST["message"], $_SESSION["uuid"]); // 's' specifies the variable type => 'string'
                    $stmt->execute();
                }
                break;

            case 'edit':
                if (isset($_POST["message"]) and isset($_POST["elementid"]) and isset($_SESSION["uuid"]) and ($_POST["uuid"] == $_SESSION["uuid"]) and strlen(trim($_POST["message"]))>0){
                    // SQL-Injection proof SQL query
                    $stmt = $con->prepare('UPDATE messages SET message = ? where id = ?');
                    $stmt->bind_param('si', $_POST["message"], $_POST["elementid"]); // 's' specifies the variable type => 'string'
                    $stmt->execute();
                }
                break;

            case 'delete':
                if ($_POST["uuid"] == $_SESSION["uuid"]){
                    // SQL-Injection proof SQL query
                    $stmt = $con->prepare('DELETE from messages where id = ?');
                    $stmt->bind_param('i', $_POST["elementid"]); // 'i' specifies the variable type => 'int'
                    $stmt->execute();
                }
                break;
        }

        $con->close();

    }else{
        // If the user didn't come here by post they get a surprise
        header("location: https://youtu.be/dQw4w9WgXcQ");
        exit;
    }

    header("location: ../index.php");
?>