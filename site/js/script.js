let messageBoardEl = document.getElementById("messageBoard");

let socket = new WebSocket("ws://" + window.location.host + ":5678?");
let connectionInfoEL = document.getElementById("connectionInfo");

let sendMSGFormEl = document.getElementById("sendMSG");
let sendMessageInnputEl = document.getElementById("sendMessage");

let editMSGFormEl = document.getElementById("editMSG");

let editFormWrapperEl = document.getElementById("editFormWrapper");
let editElementIdEl = document.getElementById("editElementId");
let editElementUUIDEl = document.getElementById("editElementUUID");
let editElementMessageEl = document.getElementById("editMessage");

let deleteMSGFormEl = document.getElementById("deleteMSG");
let deleteElementIdEl = document.getElementById("deleteElementId");
let deleteElementUUIDEl = document.getElementById("deleteElementUUID");

function sendForm(form){
	const data = new FormData(form);
	const json = JSON.stringify(Object.fromEntries(data.entries()));
	// console.log(json);
	socket.send(json);
}

function deleteMSG(id, uuid){
	deleteElementIdEl.value = id;
	deleteElementUUIDEl.value = uuid;
	sendForm(deleteMSGFormEl);
}
function showEdit(id, uuid){
	console.log(id, uuid);
	editElementIdEl.value = id;
	editElementUUIDEl.value = uuid;
	editFormWrapperEl.style.display = "flex";
}

function sanetize(s){
	const e = document.createElement("div");
	e.innerText = s;
	return e.innerHTML;
}

socket.onopen = () => {
	connectionInfoEL.innerHTML += "Connected!";
	connectionInfoEL.style.backgroundColor = "green";
	connectionInfoEL.style.display = "";

	socket.onclose = (e) => {
		connectionInfoEL.innerHTML = "Disconnected :(";
		connectionInfoEL.style.backgroundColor = "red";
		Array.from(document.forms).map(e=>{e.onsubmit = null});
		deleteMSG = ()=>{
			deleteElementIdEl.value = id;
			deleteElementUUIDEl.value = uuid;
			deleteMSGFormEl.submit();
		}
	}
	Array.from(document.forms).map(form=>{
		form.onsubmit = (e) => {
			e.preventDefault();
			sendForm(e.target)
		}
	});
	
	socket.onmessage = (e) => {
		let data = JSON.parse(e.data);
		// console.log(data);
		// console.log(uuid, data["uuid"], data["uuid"] == uuid)
		switch (data["action"]) {
			case "send":
				console.log(data);
				let messageWrapper = document.createElement("div");
				messageWrapper.classList.add("messageWrapper");
				messageWrapper.dataset.msgid = data["id"];
				messageWrapper.dataset.msgid = data["uuid"];

				let time = new Date(data["time"]);
				let ftime = ('00'+time.getDate()).slice(-2)+"."+('00'+(time.getMonth()+1)).slice(-2)+" "+('00'+time.getHours()).slice(-2)+":"+('00'+time.getMinutes()).slice(-2);
				let timeEl = document.createElement("span");
				timeEl.classList.add("time");
				timeEl.innerHTML = `[${ftime}]`;

				let msgEl = document.createElement("span");
				msgEl.id = `msg${data["id"]}`;
				msgEl.innerHTML = sanetize(data["msg"]);

				messageWrapper.append(timeEl);
				messageWrapper.append(msgEl);

				if (data["uuid"] == uuid) {
					let optionsEl = document.createElement("div");
					optionsEl.classList.add("options");

					let editBTNEl = document.createElement("a");
					editBTNEl.classList.add("material-icons")
					editBTNEl.classList.add("editBTN");
					editBTNEl.href = "#";
					editBTNEl.onclick = ()=>{showEdit(data["id"], data["uuid"]);}
					editBTNEl.innerHTML = "edit";

					let deleteBTNEl = document.createElement("a");
					deleteBTNEl.classList.add("material-icons")
					deleteBTNEl.classList.add("deleteBTN");
					deleteBTNEl.href = "#";
					deleteBTNEl.onclick = ()=>{deleteMSG(data["id"], data["uuid"]);}
					deleteBTNEl.innerHTML = "delete";

					optionsEl.append(editBTNEl);
					optionsEl.append(deleteBTNEl);

					messageWrapper.append(optionsEl);
				}
				
				messageBoardEl.append(messageWrapper);

				// `<div class="message" data-msgid="${data["id"]}" data-uuid="${data["uuid"]}"><p><span class="time">[${ftime}]</span>&nbsp;${sanetize(data["msg"])}</p>`+
					// (data["uuid"] == uuid? `
					// <span class='options'>
					// 	<a name='edit' href='#' onclick='showEdit("${data["id"]}", "${data["uuid"]}")'>
					// 		<span class='material-icons editBTN'>edit</span>
					// 	</a>
					// 	<a name='delete' href='#' onclick='deleteMSG("${data["id"]}", "${data["uuid"]}");'>
					// 		<span class='material-icons deleteBTN'>delete</span>
					// 	</a>
					// </span>`:'')+'</div>';
				messageBoardEl.scrollTop = messageBoardEl.scrollHeight;
				break;
			case "edit":
				let editEl = document.querySelector(`data-msgid="${data["id"]}"`);
				el.innerHTML = sanetize(data["msg"]);
				break;
		
			case "delete":
				let deleteEl = document.querySelector(`[data-msgid="${data["id"]}"]`);
				deleteEl.remove();
				break;
		}
	};
};

messageBoardEl.scrollTop = messageBoardEl.scrollHeight;
window.onload = ()=>{messageBoardEl.scrollTop = messageBoardEl.scrollHeight;}