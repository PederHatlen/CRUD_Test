import asyncio
import time
import socket
import websockets
import json
import MySQLdb
from termcolor import colored

connections = []

# Colored output Table
# IP			=	cyan
# Client	 	=	magenta
# Server		=	yellow
# Error/Discon	=	red
# Success		=	green
# Info			=	blue
# id			=	white

async def handler(websocket):
	userIP = colored("[{0}]".format(websocket.remote_address[0]), "magenta")
	try:
		global connections

		initData = json.loads(await websocket.recv())
		if initData["uuid"] == "":
			print(userIP, colored("Incorrect init data.", "red"))
			await websocket.close()
			return False

		cursor.execute("""SELECT ip, color FROM users WHERE uuid = %s and ip = %s """, [str(initData["uuid"]), str(websocket.remote_address[0])])
		conn.commit()

		if not cursor.rowcount:
			print(userIP, colored("IP does not match UUID.", "red"))
			await websocket.close()
			return False

		color = cursor.fetchone()[1]

		print(userIP, colored("Connected", "green"))
		connections.append(websocket)
	except Exception as err:
		await websocket.close()
		print(userIP, colored("Something went wrong:", "red"), err)
		return False
	try:
		while websocket.open:
			try:
				data = json.loads(await websocket.recv())

				if data["request"] == "send":
					if "msg" in data and len(data["msg"]) != 0:
						cursor.execute("""INSERT INTO messages (msg, uuid) VALUES (%s, %s)""", [str(data["msg"]), str(data["uuid"])])
						conn.commit()
						messageId = cursor.lastrowid
						unixtime = round(time.time() * 1000)
						sendtTo = []
						sendData = {
							"action":"send",
							"time": unixtime,
							"uuid": data["uuid"],
							"msg": data["msg"],
							"id": messageId,
							"color": color
						}
						for i in connections:
							await i.send(json.dumps(sendData))
							sendtTo.append(colored(i.remote_address[0], "cyan"))
						print(colored("MSG from", "blue"), userIP, colored("Sendt to", "blue"), colored(", ", "blue").join(sendtTo))
					else:
						print(userIP, colored("Not enought data", "red"))
				elif data["request"] == "edit":
					cursor.execute("""SELECT uuid FROM messages WHERE id = %s""", [str(data["id"])])
					conn.commit()
					ress = cursor.fetchone()
					if ress[0] == data["uuid"]:
						cursor.execute("""UPDATE messages SET msg = %s where id = %s""", [str(data["msg"]), str(data["id"])])
						conn.commit()
						sendData = {
							"action":"edit",
							"msg": data["msg"],
							"id": data["id"]
						}
						for i in connections: await i.send(json.dumps(sendData))
						print(userIP, colored("Edited", "blue"), colored(data["id"],"white"))
				elif data["request"] == "delete":
					cursor.execute("""SELECT uuid FROM messages WHERE id = %s""", [str(data["id"])])
					conn.commit()
					ress = cursor.fetchone()
					if ress[0] == data["uuid"]:
						cursor.execute("""DELETE FROM messages WHERE id = %s""", [str(data["id"])])
						conn.commit()
						sendData = {
							"action":"delete",
							"id": data["id"]
						}
						for i in connections: await i.send(json.dumps(sendData))
						print(userIP, colored("Deleted", "blue"), colored(data["id"],"white"))
			except websockets.exceptions.ConnectionClosed: break
			except Exception as err:
				print(userIP, colored("Something went wrong:", "red"), err)
				break
			await asyncio.sleep(1)
		connections.remove(websocket)
		print(userIP, colored("Disconnected", "red"))
	except Exception as err:
		await websocket.close()
		print(userIP, colored("Something went wrong:", "red"), err)
		connections.remove(websocket)
		return False
	

async def main():
	global connections
	global conn
	global cursor

	try: conn = MySQLdb.connect("localhost", "root", "", "crud")
	except: 
		print(colored("-- Can't connect to database. --", "red"))
		return False
	else: 
		print(colored("-- Connection to database was succesfull! --", "green"))
	cursor = conn.cursor()

	s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
	s.connect(("8.8.8.8", 80))
	address = s.getsockname()[0]
	s.close()
	
	print(colored("Host ip:", "yellow"), colored(address, "cyan"))

	async with websockets.serve(handler, host = address, port = 5678):
		await asyncio.Future()  # run forever
	conn.close()

if __name__ == "__main__":
	try: 
		asyncio.run(main())
	except KeyboardInterrupt:
		print(colored("-- Keyboard interupt triggered --", "yellow"))
		conn.close()