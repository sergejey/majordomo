# -*- coding: utf-8 -*-
from mjd_constants import *

try:
    import urllib.request as urllib2
    from urllib.parse import quote
    from urllib.parse import urlencode
except ImportError:
    import urllib2
    from urllib import urlencode
import re
import MySQLdb as mdb
import sys
import six




def getURL(url):
    response = urllib2.urlopen(url).read()
    return response


def callAPI(api_url, method="GET", params={}):
    params['no_session'] = 1
    url = re.sub(r"^/api/", BASE_URL + ROOTHTML + 'api.php/', api_url)
    data = urlencode(params).encode('utf-8')

    if (method == "POST"):
        req = urllib2.Request(url, data)
        response = urllib2.urlopen(req)
    else:
        data=data.decode("utf-8")
        
        url += "?" + data
        response = urllib2.urlopen(url)

    the_page = response.read()
    
    return the_page


def say(ph, level=0, member_id=0, source=1):
    '''
    Работает аналогично функции saySafe на пхп, принимает значения в следующем виде
    say("Привет мажордомо от Питона",0)
    say(Тип параметра строка STR "", Тип параметра целое число INT, Тип параметра целое число INT)
    '''
    ph = {"ph": ph}
    data = urlencode(ph).encode('utf-8')
    data=data.decode("utf-8")

    getURL(BASE_URL + ROOTHTML + "objects/?say=1&" + data + "&level=" + str(level) + "&member_id=" + str(
        member_id) + "&source=" + str(source))
    return 1


def runScript(script_name, params):
    '''
    Принимает значения в следующем виде
    runScript ('action',{"status":"0", 'brightness':"5",'color':'#fffff2' }),
    либо
    runScript ('action')

    runScript (Имя скрипта строка STR "", Тип значения Словарь все значения внутри словаря заполняются как строки STR ""{Праметр1:Значение 1, праметр2:значение2, и т.д)
    '''
    callAPI("/api/script/" + script_name, "GET", params)
    return 1


def callMethod(method_name, params):
    '''
    Принимает значения в следующем виде
    callMethod('XiRgbgt02.action',{"status":"0", 'brightness':"5",'color':'#fffff2' }),
    либо
    callMethod('XiRelay10.turnOff')

    callMethod (Имя метода строка STR "", Тип параметра Словарь все значения внутри словаря заполняются как строки STR ""{Праметр1:Значение 1, праметр2:значение2, и т.д)
    '''
    callAPI("/api/method/" + method_name, "GET", params)
    return 1


def setGlobal(property, value):
    '''
    Принимает значения в следующем виде mjdm.setGlobal('Zokalo2.TEST', '1')
    Все принимаемые значения строка STR "".
    '''
    callAPI("/api/data/" + property, "POST", {"data": value})
    return 1


def sg(property, value):
    '''
    Принимает значения в следующем виде mjdm.sg('Zokalo2.TEST', '1')
    Все принимаемые значения строка STR "".
    '''
    result = setGlobal(property, value)
    return result


def getGlobal(property):
    '''
    Принимает значения в следующем виде mjdm.getGlobal('Zokalo2.TEST')
    Все принимаемые значения строка STR "".

    '''
    con = 0
    result = "";
    try:
        con = mdb.connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, charset='utf8')
        cur = con.cursor()
        cur.execute("SELECT VALUE FROM pvalues WHERE PROPERTY_NAME='" + property + "'")
        db_result = cur.fetchone()
        if (db_result):
            result = db_result[0]
    finally:
        if con:
            con.close()
    return result


def gg(property):
    '''
    Принимает значения в следующем виде mjdm.gg('Zokalo2.TEST')
    Все принимаемые значения строка STR "".

    '''
    result = getGlobal(property)
    return result


def getObjectsByClass(class_name):
    '''
    Принимает значения в следующем виде mjdm.getObjectsByClass ('USERS')
    Все принимаемые значения строка STR "".
    Возвращает значения в виде списка [ "Admin ", "User1 ", "User2 ", и так далее], все значние в типе строка STR
    '''
    con = 0
    result = ""
    subClass_tuple = ()
    try:
        # Подключаемся к БД
        con = mdb.connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, charset='utf8')
        # Создаем курсор - это специальный объект который делает запросы и получает их результаты
        cur = con.cursor()
        # Делаем SELECT запрос к базе данных, используя обычный SQL-синтаксис и получаем все данные из стобцов ID,SUB_LIS табоицы classes
        cur.execute("SELECT ID,SUB_LIST FROM classes WHERE TITLE = '" + class_name + "'OR ID = '" + class_name + "'")
        # Получаем данные в виде кортежа
        class_record = cur.fetchone()
        # Берем вторую позицию в кортеже
        calc = class_record[1]
        # Если там None
        if calc == None:
            a = 1
        # Если не None
        else:
            # Делаем разбиенеи второй позыции кортежа через "," превращая ее в строку
            calc = calc.split(',')
            # Считаем, сколько в списке позиций
            calc = len(calc)
            a = calc
        # Если родительский класс не едениственный падаем глубже и смотрим что там есть
        if a > 1:
            # Берем вторую позицию в кортеже
            subClass = class_record[1]
            # Делаем разбиенеи второй позыции кортежа через "," превращая ее в строку
            subClass = subClass.split(',')
            # Циклом FOR в бд перебираем все ID  из полученного ранее списка
            for i in subClass:
                cur.execute(("SELECT TITLE  FROM objects WHERE CLASS_ID = '" + i + "'"))
                # Получаем данные в виде кортежа
                sub_classes = cur.fetchall()
                # Полученные данные из кортежа вставляем в ранее созданый нами кортеж subClass_tuple
                subClass_tuple += (sub_classes)
                # Выводим результат распаковывая встроеные кортежи из картежа в список
            result = list(sum(subClass_tuple, ()))

        # Если родительский класс единственный выводим инфу
        elif (class_record[0]):
            clasId = str(class_record[0])
            cur.execute("SELECT TITLE  FROM objects WHERE CLASS_ID = '" + clasId + "'")
            sub_classes = list(cur.fetchall())
            result = list(sum(sub_classes, ()))
        else:
            print('ERROR')
            if con:
                #закрываем подключение к бд
                con.close()

    finally:
        if con:
            con.close()
    return result
def RoomDevices(room):
    '''
    Принимает значения в следующем виде mjdm.RoomDevices('Hall')
    Все принимаемые значения строка STR "".
    Возвращает значения в виде списка [ "XiOpenclose01 ", "Notebook " и так далее], все значние в типе строка STR
    '''
    con = 0
    result = ""
    subClass_tuple = ()
    try:
        # Подключаемся к БД
        con = mdb.connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, charset='utf8')
        # Создаем курсор - это специальный объект который делает запросы и получает их результаты
        cur = con.cursor()
        cur.execute("SELECT LOCATION_ID FROM objects WHERE TITLE = '" + room + "'OR ID = '" + room + "'")
        # Получаем данные в виде кортежа
        class_record = cur.fetchone()
        # Берем вторую позицию в кортеже
        subClass = str(class_record[0])
        # Циклом FOR в бд перебираем все ID  из полученного ранее списка
        for i in subClass:
            cur.execute(("SELECT TITLE  FROM objects WHERE LOCATION_ID = '" + i + "'"))
            # Получаем данные в виде кортежа
            sub_classes = cur.fetchall()
            # Полученные данные из кортежа вставляем в ранее созданый нами кортеж subClass_tuple
            subClass_tuple += (sub_classes)
            # Выводим результат распаковывая встроеные кортежи из картежа в список
        result = list(sum(subClass_tuple, ()))
    finally:
        if con:
            con.close()
    return result
def getObjects(name,info):
    '''
    Принимает значения в следующем виде mjdm.getObjects('USERS','DESCRIPTION') либо mjdm.getObjects('USERS','DESCRIPTION, ID')
    Все принимаемые значения строка STR "".
    info может принимать следующие аргументы (id, title,CLASS_ID,DESCRIPTION,LOCATION_ID,KEEP_HISTORY,SYSTEM)
    Возвращает значения info в виде строки если вызван один аргумет info, или списка если вызвано несколько аргументов
    '''
    con = 0
    result = ""
    subClass_tuple = ()
    info=info.upper()
    lenifo=len(info.split(','))

    try:
        # Подключаемся к БД
        con = mdb.connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, charset='utf8')
        # Создаем курсор - это специальный объект который делает запросы и получает их результаты
        cur = con.cursor()
        # Делаем SELECT запрос к базе данных, используя обычный SQL-синтаксис и получаем все данные из стобцов ID,SUB_LIS табоицы classes
        cur.execute("SELECT " + info + " FROM objects WHERE TITLE = '" + name + "'OR ID = '" + name + "'")
        # Получаем данные в виде кортежа
        class_record = cur.fetchall()
        if int(lenifo) > 1:
            class_record=list(sum(class_record, ()))
            result=class_record
        else:

            class_record = list(sum(class_record, ()))
            result = class_record[0]
    finally:
        if con:
            con.close()
    return str(result)

