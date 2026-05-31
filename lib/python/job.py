from mjd_constants import *
import re
import MySQLdb as mdb
import sys
import six
import datetime
import time
from typing import List, Any
from datetime import datetime as DT, timedelta
import mjdm

def clearScheduledJob(title):
    con = mdb.connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, charset='utf8')
    cur = con.cursor()
    cur.execute = ("DELETE FROM jobs WHERE TITLE LIKE '" + title + "'")
    con.commit()
    con.close()
    return


def addScheduledJob(title, commands, datetime, expire=1800):
    # апускаем функцию для удлаения задания
    clearScheduledJob(title)
    # datetime_fmt = '%Y-%m-%d %H:%M:%S'
    # Получаем время которое прибавляеться к заданому, приводим получение сторку к дате и скалдываем
    expire = (DT.strptime(datetime, '%Y-%m-%d %H:%M:%S') + timedelta(seconds=expire)).strftime('%Y-%m-%d %H:%M:%S')
    con = mdb.connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, charset='utf8')
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