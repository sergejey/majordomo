# -*- coding: utf-8 -*-
#from __future__ import print_function, unicode_literals
#import time
#https://github.com/jonisb/AudioEndpointControl

import AudioEndpointControl
from AudioEndpointControl import Render, Capture, All
from AudioEndpointControl import Console, Multimedia, Communications
from AudioEndpointControl import (
    DEVICE_STATE_ACTIVE,
    DEVICE_STATE_DISABLED,
    DEVICE_STATE_NOTPRESENT,
    DEVICE_STATE_UNPLUGGED,
    DEVICE_STATEMASK_ALL
    )
from AudioEndpointControl import (
    Device_FriendlyName,
    Device_DeviceDesc,
    DeviceInterface_FriendlyName)

from comtypes import GUID
AppID = GUID('{00000000-0000-0000-0000-000000000001}')

import pyaudio
import wave
import sys
#import subprocess


p = pyaudio.PyAudio()

# аргумент -devicelist - список выходных устройств
# аргумент -play nameoffile devicenumber проиграет файл(имя) на устройстве номер(1) 
# аргумент -setvolume volume  devicename установит громкость волуме на звуковой карте девайснаме
# аргумент -getvolume devicename получим уровень громкости на девайснаме

if (sys.argv[1] == '-devicelist'):
    # get device list
    AudioDevices = AudioEndpointControl.AudioEndpoints(DEVICE_STATE=DEVICE_STATE_ACTIVE, PKEY_Device=Device_FriendlyName, EventContext=AppID)

    out = ''
    info = p.get_host_api_info_by_index(0)
    numdevices = info.get('deviceCount')
    for i in range(0, numdevices):
        if (p.get_device_info_by_host_api_device_index(0, i).get('maxOutputChannels')) > 0 :
            temp = str(p.get_device_info_by_host_api_device_index(0, i).get('name')).encode('latin1').decode('cp1251')
            for device in AudioDevices:
                if (str(device).find(temp) != -1):
                    out = out + str(i) + "^" + str(device) + ','
    print (str(out))


elif (sys.argv[1] == '-play' and sys.argv[2] != "" and sys.argv[3] != ""):

    CHUNK = 1024

    wf = wave.open(sys.argv[2], 'rb')

    stream = p.open(format=p.get_format_from_width(wf.getsampwidth()),
                channels=wf.getnchannels(),
                rate=wf.getframerate(),
                output=True,
                output_device_index=int(sys.argv[3]))

    data = wf.readframes(CHUNK)

    while data :
        stream.write(data)
        data = wf.readframes(CHUNK)

    stream.stop_stream()
    stream.close()
    
elif (sys.argv[1] == '-setvolume' and sys.argv[2] != "" and sys.argv[3] != ""):
    device = sys.argv[3]
    AudioDevices = AudioEndpointControl.AudioEndpoints(DEVICE_STATE=DEVICE_STATE_ACTIVE, PKEY_Device=Device_FriendlyName, EventContext=AppID)
    volume = sys.argv[2]
    
    for endpoint in AudioDevices:
        if (str(endpoint).find(device) != -1):
            endpoint.volume.Set(float(volume))
elif (sys.argv[1] == '-getvolume' and sys.argv[2] != ""):
    device = sys.argv[2]
    AudioDevices = AudioEndpointControl.AudioEndpoints(DEVICE_STATE=DEVICE_STATE_ACTIVE, PKEY_Device=Device_FriendlyName, EventContext=AppID)
    
    for endpoint in AudioDevices:
        if (str(endpoint).find(device) != -1):
            VolSave = endpoint.volume.Get()
    print (VolSave)

else :
	print ("неправильный аргумент")	
	print ("# аргумент -devicelist - список выходных устройств")
	print ("# аргумент -play nameoffile devicenumber проиграет файл(имя) на устройстве номер(1)")
