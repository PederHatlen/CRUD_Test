<?php
    define("IS_INCLUDED", TRUE);
    include 'phpRepo.php';

    if ($_SERVER['REQUEST_METHOD'] != "POST" or !isset($_POST["request"]) or $_POST["uuid"] != $_SESSION["uuid"]) {
        // If the user didn't come here by post they get a surprise
        header("location: https://youtu.be/dQw4w9WgXcQ");
        exit;
    }

    // Connection function is in phpRepo
    $con = connect();

    switch ($_POST["request"]) {
        case 'send':
            if (isset($_POST["msg"]) and isset($_SESSION["uuid"]) and strlen(trim($_POST["msg"]))>0){
                // SQL-Injection proof SQL query
                $stmt = $con->prepare('INSERT into messages (msg, uuid) VALUES (?, ?)');
                $stmt->bind_param('ss', $_POST["msg"], $_SESSION["uuid"]); // 's' specifies the variable type => 'string'
                $stmt->execute();
            }
            break;

        case 'edit':
            if (isset($_POST["msg"]) and isset($_POST["id"]) and isset($_SESSION["uuid"]) and ($_POST["uuid"] == $_SESSION["uuid"]) and strlen(trim($_POST["msg"]))>0){
                // SQL-Injection proof SQL query
                $stmt = $con->prepare('UPDATE messages SET msg = ? where id = ?');
                $stmt->bind_param('si', $_POST["msg"], $_POST["id"]); // 's' specifies the variable type => 'string'
                $stmt->execute();
            }
            break;

        case 'delete':
            if ($_POST["uuid"] == $_SESSION["uuid"]){
                // SQL-Injection proof SQL query
                $stmt = $con->prepare('DELETE from messages where id = ?');
                $stmt->bind_param('i', $_POST["id"]); // 'i' specifies the variable type => 'int'
                $stmt->execute();
            }
            break;
    }

    $con->close();

    header("location: ../index.php");
?>