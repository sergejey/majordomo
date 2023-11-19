import logging
import asyncio
import websockets
import json
from datetime import datetime, timedelta
import time
import re
import sys
import threading

from pathlib import Path
root_path = str(Path( __file__ ).parent.parent.absolute())

sys.path.insert(0, root_path+'/lib/python')
sys.path.insert(0, root_path+'/cms/python')
import mjd_constants
from general import getGlobal
from general import setGlobal
from general import callAPI


class ThreadSafeDict:
    def __init__(self):
        self._dict = {}
        self._lock = threading.Lock()

    def __getitem__(self, key):
        with self._lock:
            return self._dict[key]

    def __setitem__(self, key, value):
        with self._lock:
            self._dict[key] = value

    def __delitem__(self, key):
        with self._lock:
            del self._dict[key]

    def __len__(self):
        with self._lock:
            return len(self._dict)

    def __contains__(self, key):
        with self._lock:
            return key in self._dict

    def keys(self):
        with self._lock:
            return list(self._dict.keys())

    def values(self):
        with self._lock:
            return list(self._dict.values())

    def items(self):
        with self._lock:
            return list(self._dict.items())

# Создаем логгер
logger = logging.getLogger('my_logger')
logger.setLevel(logging.DEBUG)

def get_log_filename():
    current_datetime = datetime.now()
    return current_datetime.strftime(root_path+"/cms/debmes/%Y-%m-%d_websokets.log")

# Настройка обработчика для файла
file_handler = logging.FileHandler(get_log_filename())
file_handler.setLevel(logging.INFO)
file_formatter = logging.Formatter('%(asctime)s [%(levelname)s]: %(message)s', datefmt='%Y-%m-%d %H:%M:%S')
file_handler.setFormatter(file_formatter)

# Настройка обработчика для консоли
console_handler = logging.StreamHandler()
console_handler.setLevel(logging.DEBUG)
console_formatter = logging.Formatter('[%(levelname)s]: %(message)s')
console_handler.setFormatter(console_formatter)

# Добавляем обработчики к логгеру
logger.addHandler(file_handler)
logger.addHandler(console_handler)

# Берем конфиг из config.php
with open(root_path+'/config.php', 'r') as file:
    file_contents = file.read()

pattern = r"[D|d]efine\('([^']*)','?([^']*|[\d^.]*)'?\);"
matches = re.findall(pattern, file_contents)
config = {}
for match in matches:
    key, value = match
    config[key] = value.strip()

#for key, value in config.items():
#    print(f"{key}: {value}")

WEBSOCKETS_PORT = config["WEBSOCKETS_PORT"]

# Время старта
started = datetime.now()
logger.info("Cycle started")

# Словарь для хранения всех подключенных клиентов
clients = ThreadSafeDict()
# Словарь для хранения кэша значений
cachedProperties = {}
cacheStates = {}

async def sendData(client, action, data):
    message = {"action":action,"data":json.dumps(data, default=list)}
    data = json.dumps(message)
    key = str(client.id)
    logger.debug(f"{key} <- {data}")
    clients[key]['outPacket'] += 1
    clients[key]['outBytes'] += len(data)
    clients[key]['updated'] = datetime.now()
    await client.send(data)    

async def statusAction(client):
    status={}
    status["YOUR_ID"] = str(client.id)
    status["STARTED"] = started.strftime("%Y/%m/%d %H:%M:%S")
    status["COUNT_CLIENTS"] = len(clients)
    status["COUNT_CACHED"] = len(cachedProperties)
    #status["CACHED"] = cachedProperties
    status["CLIENTS"] = []
    for key,cl in clients.items():
        data = {}
        data['ID'] = key
        data['IP'] = cl['client'].remote_address[0]
        data['CONNECTED'] = cl['connected'].strftime("%Y/%m/%d %H:%M:%S")
        data['UPDATED'] = cl['updated'].strftime("%Y/%m/%d %H:%M:%S")
        data['WATCHED_PROPERTIES'] = cl['properties']
        data['WATCHED_DEVICES'] = cl['devices']
        data['WATCHED_COMMANDS'] = cl['commands']
        data['WATCHED_SCENES'] = cl['scenes']
        traffic = {}
        traffic['IN_PACKET'] = cl['inPacket']
        traffic['OUT_PACKET'] = cl['outPacket']
        traffic['IN_BYTES'] = cl['inBytes']
        traffic['OUT_BYTES'] = cl['outBytes']
        data['TRAFFIC'] = traffic;
        status["CLIENTS"].append(data)
    await sendData(client, "status", status)
    
async def subscribeAction(client,data):
    if (data['TYPE'] == 'events'):
        if (data['EVENTS'] == ''):
            return
        events = data['EVENTS'].split(",")
        clients[str(client.id)]['events'] = events
        await sendData(client, "subscribed", data)
    elif (data['TYPE'] == 'properties'):
        if (data['PROPERTIES'] == ''):
            return
        properties = data['PROPERTIES'].lower().split(",")
        for prop in properties:
            if prop not in clients[str(client.id)]['properties']:
                clients[str(client.id)]['properties'].append(prop)
        await sendData(client, "subscribed", data)
        data = []
        for prop in properties:
          name = prop.lower()
          if name in cachedProperties.keys():
            data.append({'PROPERTY':name, 'VALUE': cachedProperties[name]})
          else:
            value = getGlobal(name)
            cachedProperties[name] = value
            data.append({'PROPERTY':name, 'VALUE': value})
        if len(data) > 0:
            await sendData(client,"properties",data)
    elif (data['TYPE'] == 'devices'):
        device_id = 0
        if 'DEVICE_ID' in data.keys():
            device_id = data['DEVICE_ID']
        res = callAPI("/api/modulefunction/devices/getWatchedProperties", "POST", {"args": [device_id]})
        props = json.loads(res)['apiHandleResult']
        for prop in props:
            name = prop['PROPERTY'].lower()
            if name not in clients[str(client.id)]['devices']:
                clients[str(client.id)]['devices'][name] = set()
            clients[str(client.id)]['devices'][name].add(int(prop['DEVICE_ID']))
        await sendData(client,"subscribed",data)
    elif (data['TYPE'] == 'commands'):
        parent_id = 0
        if 'PARENT_ID' in data.keys():
            parent_id = data['PARENT_ID']
        res = callAPI("/api/modulefunction/commands/getWatchedProperties", "POST", {"args": [parent_id]})
        props = json.loads(res)['apiHandleResult']
        for prop in props:
            name = prop['PROPERTY'].lower()
            if name not in clients[str(client.id)]['commands']:
                clients[str(client.id)]['commands'][name] = set()
            clients[str(client.id)]['commands'][name].add(int(prop['COMMAND_ID']))
        await sendData(client,"subscribed",data)
    elif (data['TYPE'] == 'scenes'):
        updateStates()
        scene_id = "all"
        if 'SCENE_ID' in data.keys():
            if data['SCENE_ID'] !="":
                scene_id = data['SCENE_ID']
        scene_id = json.dumps({scene_id:"1"})
        res = callAPI("/api/modulefunction/scenes/getWatchedProperties", "POST", {"args": f"[{scene_id}]"})
        #logger.debug(res)
        props = json.loads(res)['apiHandleResult']
        for prop in props:
            if 'PROPERTY' in prop:
                name = prop['PROPERTY'].lower()
                if name not in clients[str(client.id)]['scenes']:
                    clients[str(client.id)]['scenes'][name] = set()
                clients[str(client.id)]['scenes'][name].add(prop['STATE_ID'])
        await sendData(client,"subscribed",data)
    else:
        logger.warning(data)

def updateStates():
    cacheStates.clear()
    res = callAPI("/api/modulefunction/scenes/getDynamicElements", "POST", {"args": []})
    res = json.loads(res)['apiHandleResult']
    for el in res:
        for state in el['STATES']:
            cacheStates[state['ID']] = state
    
async def postPropertyAction(client,data):
    if not isinstance(data, list):
        data = [data]
        #logger.debug(data)
    for prop in data:
        if isinstance(prop, str): 
            logger.warning(prop)
            continue
        #logger.debug(prop)
        name = prop["NAME"].lower()
        cachedProperties[name] = prop["VALUE"]
        for key,subscriber in clients.items():
            if (key == str(client.id)):
                continue
            if (name in subscriber["properties"]):
                data = [{'PROPERTY':name, 'VALUE':prop["VALUE"]}]
                await sendData(subscriber['client'],'properties',data)
            if name in subscriber["devices"]:
                data = []
                devices = subscriber["devices"][name]
                for device_id in devices:
                    res = callAPI("/api/modulefunction/devices/processDevice", "POST", {"args": [device_id]})
                    res = json.loads(res)['apiHandleResult']
                    data.append({"DEVICE_ID":res['DEVICE_ID'], "DATA":res['HTML']}) 
                send_data = {'DATA' : data}
                await sendData(subscriber['client'],"devices",send_data)
            if name in subscriber["commands"]:
                data_label = []
                data_value = []
                commands = subscriber["commands"][name]
                for command_id in commands:
                    res = callAPI("/api/modulefunction/commands/processMenuItem", "POST", {"args": [command_id]})
                    res = json.loads(res)['apiHandleResult']
                    if 'LABEL' in res.keys():
                        data_label.append({"ID":res['ID'], "DATA":res['LABEL']})
                    if 'VALUE' in res.keys():
                        data_value.append({"ID":res['ID'], "DATA":res['VALUE']})
                send_data = {'LABELS' : data_label, "VALUES" : data_value}
                await sendData(subscriber['client'],"commands",send_data)
            if name in subscriber["scenes"]:
                data = []
                states = subscriber["scenes"][name]
                for state_id in states:
                    if state_id in cacheStates:
                        q = json.dumps(cacheStates[state_id])
                        res = callAPI("/api/modulefunction/scenes/processState", "POST", {"args": f'[{q}]'})
                        res = json.loads(res)['apiHandleResult']
                        #logger.debug(res)
                        data.append(res[0])
                await sendData(subscriber['client'],"states",data)

async def postEventAction(client,data):
    logger.debug("Event: "+str(data["NAME"])+" - "+str(data["VALUE"]))
    for key,subscriber in clients.items():
       if key == str(client.id):
            continue
       if data["NAME"] in subscriber["events"]:
            send_data = {'EVENT_DATA':data}
            await sendData(subscriber['client'],'events',send_data)

# Функция, которая будет вызываться при соединении клиента
async def handle_client(websocket, path):
    try:
        # Добавляем клиента в список подключенных
        clients[str(websocket.id)]={'client':websocket, 'connected': datetime.now(),'updated':datetime.now(), 'events':[], 'properties':[],'devices':{},'commands':{},'scenes':{},'inPacket':0,'outPacket':0,'inBytes':0,'outBytes':0}
        logger.info(f"Подключился клиент - {websocket.id}")
        setGlobal("WSClientsTotal", len(clients))
        
        # Ожидаем сообщения от клиента
        async for message in websocket:
            key = str(websocket.id)
            logger.debug(f"{key} -> {message} - {path}")
            clients[key]['updated'] = datetime.now()
            clients[key]['inPacket'] += 1
            clients[key]['inBytes'] += len(message)
            data = json.loads(message)
            if (data['action'] == "Status"):
                await statusAction(websocket)
            elif (data['action'] == "Kick"):
                id = data['data']['id']
                await clients[id]['client'].close()
            elif (data['action'] == "ResetCache"):
                cachedProperties.clear()
            elif (data['action'] == "Subscribe"):
                await subscribeAction(websocket,data['data'])
            elif (data['action'] == "PostProperty"):
                await postPropertyAction(websocket,data['data'])
            elif (data['action'] == "PostEvent"):
                await postEventAction(websocket,data['data'])
            else:
                logger.warning(f"Получено сообщение: {message} - {path}")
            
                
    except websockets.exceptions.ConnectionClosed as e:
        logger.info(f"Соединение закрыто {websocket.id}{e}", exc_info=True)
    except Exception as e:
        logger.error(f'Произошло исключение: {e}', exc_info=True)
    finally:
        del clients[str(websocket.id)]
        setGlobal("WSClientsTotal", len(clients))

async def updateStatus():
    cycleName = 'cycle_websocketsRun'
    while True:
        logger.debug("Ping-pong")
        for key,subscriber in clients.items():
            if subscriber['client'].remote_address[0] == '127.0.0.1':
                continue
            try:
                pong_waiter = await subscriber['client'].ping()
                latency = await pong_waiter
                logger.debug("Client "+key+" latency - "+str(latency)+"ms")
            except websockets.exceptions.ConnectionClosedError:
                logger.info(f"Соединение закрыто {key}")
            except Exception as e:
                logger.info(f'Произошло исключение ping-pong (id:{key}): {e}', exc_info=True)
        logger.debug("UpdateStatus")
        ts = int(time.time())
        try:
            setGlobal(cycleName, ts)
        except Exception as e:
            logger.error(f'Произошло исключение: {e}', exc_info=True)
        await asyncio.sleep(15)

# Создаем асинхронный цикл событий
async def main():
    # Создаем задачу для периодического выполнения updateStatus()
    task = asyncio.create_task(updateStatus())
    # Создаем WebSocket сервер
    logger.info(f'Start server on {WEBSOCKETS_PORT}')
    server = await websockets.serve(handle_client, "0.0.0.0", WEBSOCKETS_PORT, ping_interval=None)

    await asyncio.gather(task, server.wait_closed())

if __name__ == "__main__":
    asyncio.run(main())

