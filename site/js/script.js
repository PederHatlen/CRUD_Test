let messageBoardEl = document.getElementById("messageBoard");

let socket = new WebSocket("ws://" + window.location.host + ":5678?");
let connectionInfoEL = document.getElementById("connectionInfo");

let sendFormEl = document.getElementById("sendMSG");
let sendMSGInputEl = document.getElementById("sendMessage");

let editFormEl = document.getElementById("editMSG");
let editFormWrapperEl = document.getElementById("editFormWrapper");
let editIDInputEl = document.getElementById("editElementId");
let editUUIDInputEl = document.getElementById("editElementUUID");
let editMSGInputEl = document.getElementById("editMessage");

let deleteFormEl = document.getElementById("deleteMSG");
let deleteIDInputEl = document.getElementById("deleteElementId");
let deleteUUIDInputEl = document.getElementById("deleteElementUUID");

function sendForm(form){
	const data = new FormData(form);
	const json = JSON.stringify(Object.fromEntries(data.entries()));
	socket.send(json);
}

function deleteMSG(id, uuid){
	if (socket.readyState === WebSocket.OPEN){
		deleteIDInputEl.value = id;
		deleteUUIDInputEl.value = uuid;
		sendForm(deleteFormEl);
	}else{
		deleteIDInputEl.value = id;
		deleteUUIDInputEl.value = uuid;
		deleteFormEl.submit();
	}
}
function showEdit(id, uuid){
	// console.log(id, uuid);
	editIDInputEl.value = id;
	editUUIDInputEl.value = uuid;
	editFormWrapperEl.style.display = "flex";
}

function sanetize(s){
	const e = document.createElement("div");
	e.innerText = s;
	return e.innerHTML;
}

function renderMessage(data){
	// let data = JSON.parse(rawdata);
	// console.log(data);
	switch (data["action"]) {
		case "send":
			let messageWrapper = document.createElement("div");
			messageWrapper.classList.add("messageWrapper");
			messageWrapper.dataset.msgid = data["id"];
			messageWrapper.dataset.uuid = data["uuid"];

			let timeEl = document.createElement("span");
			let time = new Date(data["time"]);
			let ftime = ('00'+time.getDate()).slice(-2)+"."+('00'+(time.getMonth()+1)).slice(-2)+" "+('00'+time.getHours()).slice(-2)+":"+('00'+time.getMinutes()).slice(-2);
			timeEl.classList.add("time");
			timeEl.innerHTML = `[${ftime}]`;

			let msgEl = document.createElement("span");
			msgEl.classList.add("msg");
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

			messageBoardEl.scrollTop = messageBoardEl.scrollHeight;
			break;
		case "edit":
			let elToBeedited = document.getElementById(`msg${data["id"]}`);
			elToBeedited.innerHTML = sanetize(data["msg"]);
			break;
	
		case "delete":
			let deleteEl = document.querySelector(`[data-msgid="${data["id"]}"]`);
			deleteEl.remove();
			break;
	}
}

socket.onopen = () => {
	socket.send(JSON.stringify({"uuid":uuid}));
	connectionInfoEL.innerHTML += "Connected!";
	connectionInfoEL.style.backgroundColor = "green";
	connectionInfoEL.style.display = "";

	socket.onclose = (e) => {
		connectionInfoEL.innerHTML = "Disconnected :(";
		connectionInfoEL.style.backgroundColor = "red";
		Array.from(document.forms).map(e=>{e.onsubmit = null});
	}
	sendFormEl.onsubmit = (e) => {
		e.preventDefault();
		sendForm(e.target);
		sendMSGInputEl.value = "";
	}
	editFormEl.onsubmit = (e) => {
		e.preventDefault();
		sendForm(e.target);
		editMSGInputEl.value = "";
	}
	
	socket.onmessage = (e) => {
		renderMessage(JSON.parse(e.data));
	};
};

window.onload = ()=>{
	for(i in initMessages){
		initMessages[i]["action"] = "send";
		renderMessage(initMessages[i]);
	}
	messageBoardEl.scrollTop = messageBoardEl.scrollHeight;
}