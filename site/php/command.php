<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        html{height: 100%}
        body{
            min-height: 100%;
            font-family: "consolata", monospace;
            color: white;
            background: black;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 0;
            margin: 0;
        }
    </style>
</head>
<body>
    <form method="post"><input type="text" name="command" placeholder="Command"><input type="submit" value="Submit"></form>
    <?php
        $outText = "";
        if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST["command"] == "Resett colors"){
            // if($_POST["command"] == "Reset colors")
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
            
            $stmt = $con->prepare('SELECT * from users');
            $stmt->execute();
            $ress = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            for ($i=0; $i < Count($ress); $i++) {
                $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                $stmt = $con->prepare('UPDATE users SET color = ? where uuid = ?');
                $stmt->bind_param('ss', $color, $ress[$i]["uuid"]); // 's' specifies the variable type => 'string'
                $stmt->execute();
                echo "<span style=\"color: $color;\">".$ress[$i]["uuid"]."</span><br>";
            }
        }
    ?>
</body>
</html>