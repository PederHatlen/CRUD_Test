<?php
    define("IS_INCLUDED", TRUE);
    include 'php/phpRepo.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C.R.U.D.</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header><h1>C.R.U.D.</h1></header>
    <main>
        <div id="messageBoard">
            <?php
                $con = connect();

                // SQL-Injection proof SQL query
                $stmt = $con->prepare('SELECT * FROM  messages');
                $stmt->execute();

                $result = $stmt->get_result()->fetch_all(MYSQLI_BOTH);
    
                $con->close();

                for ($i=0; $i < count($result); $i++) { 
                    echo '<p><span class="time">['.strtotime($result[$i]["sent_at"]).']</span> '.$result[$i]["message"].'</p>';
                }
            ?>
        </div>
        <form action="php/post.php" method="post">
            <input type="text" name="message" id="message" placeholder="Message (0 < 255 char)" maxlength="255">
            <input type="submit" value="Send">
        </form>
    </main>
    <script>
        var element = document.getElementById("messageBoard");
        element.scrollTop = element.scrollHeight;
    </script>
</body>
</html>