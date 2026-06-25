from mjd_constants import *
import re
import MySQLdb as mdb
import sys
import six
import datetime
import time
from typing import List, Any
from datetime import datetime as DT, timedelta
import mjdm as mj

def timeConvert(tm):
    tm = tm.split(':')
    hour = int(tm[0])
    min = int(tm[1])
    # Присваиваем значениям  y=год  m=месяц d=день полученые из datetime.date.today()
    y, m, d = str(datetime.date.today()).split('-')
    # Присваем котрежу значения y, m, d , а так же hour, min и возращаем полученое время от начала эпохи до заданного времени
    return time.mktime((int(y), int(m), int(d), hour, min, 0, 0, 0, 0))


def timeBetween(tm1, tm2):
    trueTime1 = timeConvert(tm1)
    trueTime2 = timeConvert(tm2)

    if trueTime1 > trueTime2:
        if trueTime2 < time.time():
            trueTime2 += 24 * 60 * 60
            
        else:
            trueTime1 -= 24 * 60 * 60
           
    if time.time() >= trueTime1 and time.time() <= trueTime2:
        return True
    else:
        return False
