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
import datetime
import time
from typing import List, Any



def getURL(url,params='local'):
    if params !='local':
        response = urllib2.urlopen(burl + url).read()
        return response
    else:
        burl = BASE_URL
        response = urllib2.urlopen(burl + url).read()
        return response


def callAPI(api_url, method="GET", params={}):
    params['no_session'] = 1
    url = re.sub(r"^/api/", BASE_URL + ROOTHTML + 'api.php/', api_url)
    data = urlencode(params).encode('utf-8')

    if (method == "POST"):
        req = urllib2.Request(url, data)
        response = urllib2.urlopen(req)
    else:
        data = str(data)
        url += "?" + data
        response = urllib2.urlopen(url)

    the_page = response.read()
    # print the_page
    return the_page


def saySafe(ph, level=0, member_id=0, source=1):
    ph = {"ph": ph}
    data = urlencode(ph).encode('utf-8')
    data = str(data)
    sum = len(data)
    data = data[2:sum - 1]
    getURL(
        "/objects/?say=1&" + data + "&level=" + str(level) + "&member_id=" + str(member_id) + "&source=" + str(source))
    return 1


def runScript(script_name, params={}):
    callAPI("/api/script/" + script_name, "GET", params)
    return 1


def callMethod(method_name, params={}):
    callAPI("/api/method/" + method_name, "GET", params)
    return 1


def setGlobal(property, value):
    callAPI("/api/data/" + property, "POST", {"data": value})
    return 1


def sg(property, value):
    result = setGlobal(property, value)
    return result


def getGlobal(property):
    con = 0
    result = "";
    try:
        con = mdb.connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
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
    result = getGlobal(property)
    return result


def getObjectsByClass(class_name):
    con = 0
    result = ""
    subClass_tuple = ()
    try:
        con = mdb.connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
        cur = con.cursor()
        cur.execute("SELECT ID,SUB_LIST FROM classes WHERE TITLE = '" + class_name + "'OR ID = '" + class_name + "'")
        class_record = cur.fetchone()
        calc = class_record[1]
        if calc == None:
            a = 1
        else:
            calc = calc.split(',')
            calc = len(calc)
            a = calc
        # Если родительский класс не едениственный падаем глубже и смотрим что там есть
        if a > 1:
            subClass = class_record[1]
            subClass = subClass.split(',')
            for i in subClass:
                cur.execute(("SELECT TITLE  FROM objects WHERE CLASS_ID = '" + i + "'"))
                sub_classes = cur.fetchall()
                subClass_tuple += (sub_classes)
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
                con.close()

    finally:
        if con:
            con.close()
    return result


def registerEvent(eventName, details, expire_in):
    # def registerEvent(eventName, expire_in):
    con = 0
    result = ""
    subClass_tuple = ()
    # Получаем текщее дату и время в формате 2020-10-11 20:53:35.741341
    today = datetime.datetime.today()
    # переводим today к виду 2020-10-11 20:53:35 .
    today_added_tostr = str(today)
    # Разделям строку today_added_tostr по точке получая список
    today_added_tostr = today_added_tostr.split('.')
    # получаем today_added  для предачи в базу выбирая первую позицию в списке
    today_added = today_added_tostr[0]
    # print(today_added_tostr[0])
    # Получаем дату expire_in времени жизни события в днях
    delta = datetime.timedelta(days=expire_in)
    expire = today + delta
    # переводим expire встроку для удаления значени после .
    expire_added_tostr = str(expire)
    # Разделям строку expire_added_tostr по точке получая список
    expire_added_tostr = expire_added_tostr.split('.')
    # получаем today_added  для предачи в базу выбирая первую позицию в списке
    expire_added = expire_added_tostr[0]
    # print(expire_added_tostr[0])
    time_sec = time.time()
    time_sec = str(time_sec)
    time_sec = time_sec.split('.')
    time_sec = time_sec[0]
    EVENT_TYPE = "system"
    TITLE = "updated"
    try:

        # Подключаемся к БД
        con = mdb.connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
        # Создаем курсор - это специальный объект который делает запросы и получает их результаты
        cur = con.cursor()
        # Делаем SELECT запрос к базе данных, используя обычный SQL-синтаксис и получаем все данные из стобца EVENT_NAME табоицы events
        cur.execute("SELECT  EVENT_NAME FROM  events ")
        # Переводим кортеж с кортежами в спискок с кортежами
        get_event_list = list(cur.fetchall())
        # распаковываем кортыжи и перводим их в спико через ,  .
        get_event_list = list(sum(get_event_list, ()))
        # Проверяем есть ли такое имя события если есть такое событие меняем его

        if eventName in get_event_list:
            cur.execute("SELECT ID  FROM events WHERE EVENT_NAME = '" + eventName + "'")
            id_eventname = list(cur.fetchall())
            id_eventname = list(sum(id_eventname, ()))
            id_eventname = id_eventname[0]
            sql = "UPDATE events SET DETAILS=%s, ADDED=%s, EXPIRE=%s WHERE ID=%s "
            val = (details, today_added, expire_added, id_eventname)
            cur.execute(sql, val)
            con.commit()
            sql = "UPDATE events_params SET UPDATED=%s, TITLE=%s, VALUE=%s WHERE EVENT_ID=%s "
            val = (today_added, TITLE, time_sec, id_eventname)
            cur.execute(sql, val)
            con.commit()
            # if type(details) is dict:
            #     if 'rec' in details:
            #         if 'obj' and 'prop' in details:
            #             property = details['obj'] + '.' + details['prop']
            #             setGlobal(property, details['value'])
            #             obj = details['obj']
            #             prop = details['prop']
            #             sql = "UPDATE events_params SET LINKED_OBJECT=%s, LINKED_PROPERTY=%s WHERE EVENT_ID=%s "
            #             val = (obj, prop, id_eventname)
            #             cur.execute(sql, val)
            #             con.commit()
            #         elif 'obj' and 'meth' in details:
            #             method = details['obj'] + '.' + details['meth']
            #             callMethod(method, details['param'])
            #             obj = details['obj']
            #             meth = details['meth']
            #             sql = "UPDATE events_params SET LINKED_OBJECT=%s, LINKED_METHOD=%s WHERE EVENT_ID=%s "
            #             val = (obj, meth)
            #             cur.execute(sql, val)
            #             con.commit()

                # else:
                #     if 'obj' and 'prop' in details:
                #         property = details['obj'] + '.' + details['prop']
                #         setGlobal(property, details['value'])
                # 
                #     elif 'LINKED_OBJECT' and 'LINKED_METHOD' in details:
                #         method = details['obj'] + '.' + details['meth']
                #         callMethod(method, details['param'])


        else:
            # Если события нет создаем его
            sql = "INSERT INTO events (EVENT_TYPE,DETAILS,ADDED, EXPIRE,PROCESSED, EVENT_NAME)  VALUES (%s,%s,%s,%s, %s,%s)"
            val = (EVENT_TYPE, details, today_added, expire_added, 1, eventName)
            cur.execute(sql, val)
            con.commit()
            cur.execute("SELECT ID  FROM events WHERE EVENT_NAME = '" + eventName + "'")
            id_eventname = list(cur.fetchall())
            id_eventname = list(sum(id_eventname, ()))
            id_eventname = id_eventname[0]
            sql = "INSERT INTO events_params (TITLE,UPDATED,VALUE, EVENT_ID )  VALUES (%s,%s,%s,%s)"
            val = (TITLE, today_added, time_sec, id_eventname)
            cur.execute(sql, val)
            con.commit()

            # if type(details) is dict:
            #     if 'rec' in details:
            #         if 'obj' and 'prop' in details:
            #             property = details['obj'] + '.' + details['prop']
            #             setGlobal(property, details['value'])
            #             obj = details['obj']
            #             prop = details['prop']
            #             sql = "INSERT INTO events_params (LINKED_OBJECT,LINKED_PROPERTY )  VALUES (%s,%s)"
            #             val = (obj, prop)
            #             cur.execute(sql, val)
            #             con.commit()
            #         elif 'LINKED_OBJECT' and 'LINKED_METHOD' in details:
            #             method = details['obj'] + '.' + details['meth']
            #             callMethod(method, details['param'])
            #             obj = details['obj']
            #             meth = details['meth']
            #             sql = "INSERT INTO events_params (LINKED_OBJECT,LINKED_METHOD )  VALUES (%s,%s)"
            #             val = (obj, meth)
            #             cur.execute(sql, val)
            #             con.commit()
            # 
            #     else:
            #         if 'obj' and 'prop' in details:
            # 
            #             property = details['obj'] + '.' + details['prop']
            #             setGlobal(property, details['value'])
            # 
            #         elif 'obj' and 'meth' in details:
            #             method = details['obj'] + '.' + details['meth']
            #             callMethod(method, details['param'])

    finally:

        if con:
            con.close()
    return 1


def registeredEventTime(eventName):
    con = mdb.connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
    cur = con.cursor()
    cur.execute(
        "SELECT UNIX_TIMESTAMP(ADDED)  AS TM FROM events WHERE EVENT_TYPE = 'system'  AND EVENT_NAME = '" + eventName + "' ORDER BY ADDED DESC LIMIT 1 ")
    eventtime = cur.fetchone()
    if eventtime == None:
        return False
    else:
        eventtime = list(eventtime)
        eventtime = list(sum(eventtime, ()))
        eventtime = eventtime[0]
        eventtime = int(eventtime)
        return eventtime


def registeredEventDetails(eventName):
    con = mdb.connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
    cur = con.cursor()
    cur.execute("SELECT DETAILS FROM events WHERE  EVENT_NAME = '" + eventName + "'")
    details_record = cur.fetchone()
    if details_record == None:
        return False
    else:
        event_details = details_record[0]
        event_details = event_details.split(',')
        return event_details


def timeISO():
    time_sec = time.time()
    time_sec = str(time_sec)
    time_sec = time_sec.split('.')
    time_sec = int(time_sec[0])
    return time_sec


def timeConvert(tm):
    tm = tm.split(':')
    hour = int(tm[0])
    min = int(tm[1])
    y, m, d = str(datetime.date.today()).split('-')
    return time.mktime((int(y), int(m), int(d), hour, min, 0, 0, 0, 0))


def timeBetween(tm1, tm2):
    trueTime1 = timeConvert(tm1)
    trueTime2 = timeConvert(tm2)

    if trueTime1 > trueTime2:
        if trueTime2 < time.time():
            trueTime2 += 24 * 60 * 60
            print('trueTime2;', trueTime2)
        else:
            trueTime1 -= 24 * 60 * 60
            print('trueTime1;', trueTime1)
    if time.time() >= trueTime1 and time.time() <= trueTime2:
        return True
    else:
        return False


def clearScheduledJob(title):
    con = mdb.connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
    cur = con.cursor()
    cur.execute = ("DELETE FROM jobs WHERE TITLE LIKE '" + title + "'")
    con.commit()
    con.close()
    return


def clearTimeOut(title):
    return clearScheduledJob(title)


def addScheduledJob(title, commands, datetime, expire=1800):
    clearScheduledJob(title)
    datetime_fmt = '%Y-%d-%m %H:%M:%S'
    expire = (DT.strptime(datetime, datetime_fmt) + timedelta(seconds=expire)).strftime(datetime_fmt)
    con = mdb.connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
    cur = con.cursor()
    sql = "INSERT INTO jobs (TITLE,COMMANDS, RUNTIME,EXPIRE)  VALUES (%s,%s,%s,%s)"
    val = (title, commands, datetime, expire)
    cur.execute(sql, val)
    con.commit()
    cur.execute("SELECT ID  FROM jobs WHERE TITLE = '" + title + "'")
    jobID = list(cur.fetchall())
    jobID = list(sum(jobID, ()))
    jobID = jobID[0]
    con.close()
    return jobID


def setTimeOut(title, commands, timeout):
    clearTimeOut(title)
    res = addScheduledJob(title, commands, time.time() + timeout)
    return res


class mjdObject:
    object_name = ""

    def __init__(self, object_name):
        self.object_name = object_name

    def setProperty(self, property_name, value):
        setGlobal(self.object_name + "." + property_name, value)
        return 1

    def getProperty(self, property_name):
        result = getGlobal(self.object_name + "." + property_name)
        return result

    def callMethod(self, method_name, params={}):
        result = callMethod(self.object_name + "." + method_name, params)
        return result
