# -*- coding: utf-8 -*-
from mjd_constants import *
import MySQLdb as mdb
import sys
#import six
import datetime
import time
from typing import List, Any

def registerEvent(eventName, details, expire_in):
    '''
    Принимает значения в следующем виде
    registerEvent("adminEvent4", "LOL" ,20)
    либо с установкой значения объекту
    registerEvent("adminEvent4", {"rec":"","obj":"Zokalo2","prop":"TEST","value":"22"}, 20)
    либо с запуском метода объекта с параметрами
    registerEvent("adminEvent4", {"rec":"","obj":"Zokalo2","meth":"checkBt","param":"{"status":"0", 'brightness':"5",'color':'#fffff2' "}, 20)
    либо с запуском метода объекта без паметров
    registerEvent("adminEvent4", {"rec":"","obj":"Zokalo2","meth":"checkBt","param":"{}"}, 20)
    либо с установкой значения объекту и запуском метода для объекта
    registerEvent("adminEvent4", {"rec":"","obj":"Zokalo2","prop":"TEST","value":"","meth":"checkBt","param":"{}"}, 20)

    registerEvent("Имя Эвента строка STR ", Детали (строка, целое значение, словарь) , время жизни в днях целое значение INT)
    Запуск методов и установки значений свойств пока не работает так как надо код дорабатывается

    obj - имя объекта
    prop – имя свойства
    value- значение свойства
    meth – имя метода
    param – параметры запускаемого метода
    "rec":""– записать в бд, без просто исполнить.
    '''
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

    # Полуаем время в секундах от начала эпохи
    time_sec = time.time()
    # Переводим секунды в строку
    time_sec = str(time_sec)
    # Разделяем строку по точке получая список
    time_sec = time_sec.split('.')
    # Выбираем первую позицю в списке
    time_sec = time_sec[0]
    EVENT_TYPE = "system"
    TITLE = "updated"
    try:

        # Подключаемся к БД
        con = mdb.connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, charset='utf8')
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
            # делаем запрос в бд к таблице эвент для получения ID по имени эвента
            cur.execute("SELECT ID  FROM events WHERE EVENT_NAME = '" + eventName + "'")
            id_eventname = list(cur.fetchall())
            id_eventname = list(sum(id_eventname, ()))
            id_eventname = id_eventname[0]
            #Создаем переменные sql и val. где в sql сформированый запрос в бд и =%s значение для отравки в стоблец   которое будет подсталвяться к каждому их пречисленых столбцоы
            #порядку из переменной val в бд по факту отправляеться следующий запрос
            #(UPDATE events SET DETAILS=details, ADDED=today_added, EXPIRE=expire_added WHERE ID=id_eventname)
            sql = "UPDATE events SET DETAILS=%s, ADDED=%s, EXPIRE=%s WHERE ID=%s "
            val = (details, today_added, expire_added, id_eventname)
            cur.execute(sql, val)
            #подтверждаем щапись в бд
            con.commit()
            sql = "UPDATE events_params SET UPDATED=%s, TITLE=%s, VALUE=%s WHERE EVENT_ID=%s "
            val = (today_added, TITLE, time_sec, id_eventname)
            cur.execute(sql, val)
            con.commit()
            #Проверяем что в details есть словарь
            if type(details) is dict:
                # В словаре соедржиться rec мы переходим в блок где будет записываться в бд
                if 'rec' in details:
                    # В словаре соедржиться obj и prop мы выполняем функцию setGlobal и записываем ее в бд для последующего выполнения из других модулей
                    if 'obj' and 'prop' in details:
                        # Получаем строковое значение property складывая строки obj и prop
                        # т.е. мы ввели объект Zokalo  и его свойство TEST. но для того что функция setGlobal
                        # нормально приняла значения мы должны ей отправить Zokalo.TEST
                        # Для этого мы скалыдваем (кокатенируем) 3 (три строки)
                        # объект Zokalo + . (точка) + свойство TEST
                        # На примере details['obj']
                        # мы перемнной details получаем значение ключа obj
                        property = details['obj'] + '.' + details['prop']
                        mj.setGlobal(property, details['value'])
                        obj = details['obj']
                        prop = details['prop']
                        sql = "UPDATE events_params SET LINKED_OBJECT=%s, LINKED_PROPERTY=%s WHERE EVENT_ID=%s "
                        val = (obj, prop, id_eventname)
                        cur.execute(sql, val)
                        con.commit()
                    elif 'obj' and 'meth' in details:
                        obj = details['obj']
                        meth = details['meth']
                        sql = "UPDATE events_params SET LINKED_OBJECT=%s, LINKED_METHOD=%s WHERE EVENT_ID=%s "
                        val = (obj, meth)
                        cur.execute(sql, val)
                        con.commit()
                        method = details['obj'] + '.' + details['meth']
                        mj.callMethod(method, details['param'])

            # В словаре соедржиться не содержиться rec мы переходим в блок где просто будут выполняться функции
            #setGlobal и callMethod
            else:
                if 'obj' and 'prop' in details:
                    property = details['obj'] + '.' + details['prop']
                    mj.setGlobal(property, details['value'])

                elif 'obj' and 'meth' in details:
                    method = details['obj'] + '.' + details['meth']
                    mj.callMethod(method, details['param'])


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

            if type(details) is dict:
                if 'rec' in details:
                    if 'obj' and 'prop' in details:
                        property = details['obj'] + '.' + details['prop']
                        mj.setGlobal(property, details['value'])
                        obj = details['obj']
                        prop = details['prop']
                        sql = "INSERT INTO events_params (LINKED_OBJECT,LINKED_PROPERTY )  VALUES (%s,%s)"
                        val = (obj, prop)
                        cur.execute(sql, val)
                        con.commit()
                    elif 'obj' and 'meth' in details:
                        obj = details['obj']
                        meth = details['meth']
                        sql = "INSERT INTO events_params (LINKED_OBJECT,LINKED_METHOD )  VALUES (%s,%s)"
                        val = (obj, meth)
                        cur.execute(sql, val)
                        con.commit()
                        method = details['obj'] + '.' + details['meth']
                        mj.callMethod(method, details['param'])


                else:
                    if 'obj' and 'prop' in details:

                        property = details['obj'] + '.' + details['prop']
                        mj.setGlobal(property, details['value'])

                    elif 'obj' and 'meth' in details:
                        method = details['obj'] + '.' + details['meth']
                        mj.callMethod(method, details['param'])

    finally:

        if con:
            con.close()
    return 1


def registeredEventTime(eventName):
    '''Получение времни создания ивента в секундах от начала эпохи
     Пример ввода registeredEventTime("adminEvent")
     '''
    try:
        con = mdb.connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, charset='utf8')
        cur = con.cursor()
        cur.execute(
            "SELECT UNIX_TIMESTAMP(ADDED)  AS TM FROM events WHERE EVENT_TYPE = 'system'  AND EVENT_NAME = '" + eventName + "' ORDER BY ADDED DESC LIMIT 1 ")
        eventtime = cur.fetchone()
        if eventtime == None:
            return False
        else:
            eventtime = list(eventtime)
            eventtime = eventtime[0]
            eventtime = int(eventtime)
            return eventtime
    finally:

        if con:
            con.close()


def registeredEventDetails(eventName):
    '''Получениеполучение деталий эвента
    Пример ввода registeredEventTime("adminEvent") 
    '''

    try:
        con = mdb.connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, charset='utf8')
        cur = con.cursor()
        cur.execute("SELECT DETAILS FROM events WHERE  EVENT_NAME = '" + eventName + "'")
        details_record = cur.fetchone()
        if details_record == None:
            return False
        else:
            event_details = details_record[0]
            event_details = event_details.split(',')
            return event_details
    finally:

        if con:
            con.close()
