<?php
	define("IS_INCLUDED", TRUE);
	include 'php/phpRepo.php';

	$con = connect();

	// SQL-Injection proof SQL query
	$stmt = $con->prepare('SELECT messages.id, messages.msg, messages.uuid, messages.time, users.color FROM  messages LEFT JOIN users on messages.uuid = users.uuid');
	$stmt->execute();
	$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

	$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>C.R.U.D</title>
	<link rel="stylesheet" href="css/style.css">

	<!-- Icons from material icons by Google -->
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	<script>
		let uuid = "<?php echo($_SESSION["uuid"]);?>";
		let initMessages = <?php echo json_encode($messages);?>;
	</script>
</head>
<body>
	<header><h1>C.R.U.D.</h1><span id="connectionInfo"></span></header>
	<div id="messageBoard"></div>
	<footer>
		<form id="sendMSG" action="php/post.php" method="post">
			<input type="hidden" name="request" value="send">
			<input type="text" name="msg" id="sendMessage" placeholder="Message (max 255 char)" maxlength="255">
			<input type="hidden" name="uuid" id="uuidSend" value="<?php echo($_SESSION["uuid"]);?>">
			<input type="submit" value="Send">
		</form>
		<span>Peder 2022</span>
	</footer>

	<div id="editFormWrapper">
		<form id="editMSG" action="php/post.php" method="post">
			<h2>Edit <a class="deleteBTN" href="#" onclick="editFormWrapperEl.style.display = '';">X</a></h2>
			<input type="hidden" name="request" value="edit">
			<input type="hidden" name="id" id="editElementId">
			<input type="hidden" name="uuid" id="editElementUUID">
			<input type="text" name="msg" id="editMessage" placeholder="Message (0 < 255 char)" maxlength="255">
			<input type="submit" value="Send">
		</form>
	</div>
	
	<form id="deleteMSG" action="php/post.php" method="post">
		<input type="hidden" name="request" value="delete">
		<input type="hidden" name="id" id="deleteElementId">
		<input type="hidden" name="uuid" id="deleteElementUUID">
	</form>
	<script src="js/script.js"></script>
</body>
</html>