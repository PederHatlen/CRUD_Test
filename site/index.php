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
	<title>C.R.U.D</title>
	<link rel="stylesheet" href="css/style.css">

	<!-- Icons from material icons by Google -->
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
	<header><h1>C.R.U.D.</h1><span id="connectionInfo"></span></header>
	<div id="messageBoard">
		<?php
			$con = connect();

			// SQL-Injection proof SQL query
			$stmt = $con->prepare('SELECT * FROM  messages');
			$stmt->execute();
			$result = $stmt->get_result()->fetch_all(MYSQLI_BOTH);

			$con->close();

			for ($i=0; $i < count($result); $i++) { 
				echo '<div class="message" data-msgid="'.$result[$i]["id"].'" data-uuid="'.$result[$i]["user"].'"><p><span class="time">['.date("d.m H:i", strtotime($result[$i]["sent_at"])).']</span>&nbsp;'.$result[$i]["message"].'</p>'.
				($result[$i]["user"] == $_SESSION["uuid"]? "<span class='options'><a name='edit' href='#' onclick='showEdit(\"".$result[$i]["id"]."\", \"".$result[$i]["user"]."\")'><span class='material-icons editBTN'>edit</span></a><a name='delete' href='#' onclick='deleteMSG(\"".$result[$i]["id"]."\", \"".$result[$i]["user"]."\");'><span class='material-icons deleteBTN'>delete</span></a></span>":'').'</div>';
			}
		?>
	</div>
	<footer>
		<form id="sendMSG" action="php/post.php" method="post">
			<input type="hidden" name="form" value="send">
			<input type="text" name="message" id="sendMessage" placeholder="Message (max 255 char)" maxlength="255">
			<input type="hidden" name="uuid" id="uuidSend" value="<?php echo($_SESSION["uuid"]);?>">
			<input type="submit" value="Send">
		</form>
		<span>Peder 2022</span>
	</footer>

	<div id="editFormWrapper">
		<form id="editMSG" action="php/post.php" method="post">
			<h2>Edit <a class="deleteBTN" href="#" onclick="editFormWrapperEl.style.display = '';">X</a></h2>
			<input type="hidden" name="form" value="edit">
			<input type="hidden" name="elementid" id="editElementId">
			<input type="hidden" name="uuid" id="editElementUUID">
			<input type="text" name="message" id="editMessage" placeholder="Message (0 < 255 char)" maxlength="255">
			<input type="submit" value="Send">
		</form>
	</div>
	
	<form id="deleteMSG" action="php/post.php" method="post">
		<input type="hidden" name="form" value="delete">
		<input type="hidden" name="elementid" id="deleteElementId">
		<input type="hidden" name="uuid" id="deleteElementUUID">
	</form>
	<script>
		let messageBoardEl = document.getElementById("messageBoard");

		let uuid = "<?php echo($_SESSION["uuid"]);?>";
		let socket = new WebSocket("ws://" + window.location.host + ":5678?");
		let connectionInfoEL = document.getElementById("connectionInfo");
		
		let sendMSGFormEl = document.getElementById("sendMSG");
		let sendMessageInnputEl = document.getElementById("sendMessage");

		let editFormWrapperEl = document.getElementById("editFormWrapper");
		let editElementIdEl = document.getElementById("editElementId");
		let editElementUUIDEl = document.getElementById("editElementUUID");
		let editElementMessageEl = document.getElementById("editMessage");

		let deleteMSGFormEl = document.getElementById("deleteMSG");
		let deleteElementIdEl = document.getElementById("deleteElementId");
		let deleteElementUUIDEl = document.getElementById("deleteElementUUID");
		
		function deleteMSG(id, uuid){
			console.log(id, uuid);
			deleteElementIdEl.value = id;
			deleteElementUUIDEl.value = uuid;
			deleteMSGFormEl.submit();
		}
		function showEdit(id, uuid){
			console.log(id, uuid);
			editElementIdEl.value = id;
			editElementUUIDEl.value = uuid;
			editFormWrapperEl.style.display = "flex";
		}

		socket.onopen = () => {
			connectionInfoEL.innerHTML += "Connected!";
			connectionInfoEL.style.backgroundColor = "green";
			connectionInfoEL.style.display = "";

			socket.onclose = (e) => {
				connectionInfoEL.innerHTML = "Disconnected :(";
				connectionInfoEL.style.backgroundColor = "red";
				sendMSGFormEl.onsubmit = null;
			}
			
			socket.onmessage = (e) => {
				let data = JSON.parse(e.data);
				console.log(data);
				console.log(uuid, data["uuid"], data["uuid"] == uuid)
				
				let time = new Date(data["time"]);
				let ftime = ('00'+time.getDate()).slice(-2)+"."+('00'+(time.getMonth()+1)).slice(-2)+" "+('00'+time.getHours()).slice(-2)+":"+('00'+time.getMinutes()).slice(-2);
				messageBoardEl.innerHTML += `<div class="message" data-msgid="${data["id"]}",}" data-uuid="${data["uuid"]}">
					<p><span class="time">[${ftime}]</span>&nbsp;${data["msg"]}</p>`+
					(data["uuid"] == uuid? `<span class='options'><a name='edit' href='#' onclick='showEdit("${data["id"]}", "${data["uuid"]}")'><span class='material-icons editBTN'>edit</span></a><a name='delete' href='#' onclick='deleteMSG("${data["id"]}", "${data["uuid"]}");'><span class='material-icons deleteBTN'>delete</span></a></span>`:'')+'</div>';
					messageBoardEl.scrollTop = messageBoardEl.scrollHeight;
			};
			
			sendMSGFormEl.onsubmit = (e) => {
				e.preventDefault();
				send();
			}
		};

		function send(message = sendMessageInnputEl.value) {
			socket.send(JSON.stringify({message:message, uuid:uuid}));
			sendMessageInnputEl.value = "";
		}

		messageBoardEl.scrollTop = messageBoardEl.scrollHeight;
	</script>
</body>
</html>