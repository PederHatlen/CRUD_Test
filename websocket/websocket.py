import asyncio
import time
import socket
from tokenize import Number
import websockets
import json
import MySQLdb
from termcolor import colored

connections = []

async def handler(websocket):
	userIP = colored("[{0}]".format(websocket.remote_address[0]), "magenta")
	try:
		global connections
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
					if len(data["message"]) != 0:
						cursor.execute("""INSERT INTO messages (message, user) VALUES (%s, %s)""", [str(data["message"]), str(data["uuid"])])
						conn.commit()
						messageId = cursor.lastrowid
						unixtime = round(time.time() * 1000)
						sendtTo = []
						sendData = {
							"action":"send",
							"time": unixtime,
							"uuid": data["uuid"],
							"msg": data["message"],
							"id": messageId
						}
						for i in connections:
							await i.send(json.dumps(sendData))
							sendtTo.append(colored(i.remote_address[0], "cyan"))
						print(colored("Sendt to", "green"), colored(", ", "green").join(sendtTo))
				elif data["request"] == "edit":
					cursor.execute("""SELECT User FROM messages WHERE id = %d""", [Number(data["elementid"])])
					conn.commit()
					ress = cursor.fetchone()
					if ress[0] == data["uuid"]:
						cursor.execute("""UPDATE messages SET message = %s where id = %d""", [str(data["message"]), str(data["elementid"])])
						conn.commit()
						sendData = {
							"action":"edit",
							"msg": data["message"],
							"id": data["elementid"]
						}
						for i in connections: await i.send(json.dumps(sendData))
						print(userIP, colored("Edited", "blue"), data["elementid"])
				elif data["request"] == "delete":
					cursor.execute("""SELECT User FROM messages WHERE id = %s""", [str(data["elementid"])])
					conn.commit()
					ress = cursor.fetchone()
					if ress[0] == data["uuid"]:
						cursor.execute("""DELETE FROM messages WHERE id = %s""", [str(data["elementid"])])
						conn.commit()
						sendData = {
							"action":"delete",
							"id": data["elementid"]
						}
						for i in connections: await i.send(json.dumps(sendData))
						print(userIP, colored("Deleted", "blue"), data["elementid"])
			except websockets.exceptions.ConnectionClosed:
				break
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
	global conversations
	global conn
	global cursor

	try: conn = MySQLdb.connect("localhost", "root", "", "crud")
	except: 
		print(colored("Can't connect to database.", "red"))
		return False
	else: 
		print(colored("Connection to database was succesfull!", "green"))
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
	asyncio.run(main())