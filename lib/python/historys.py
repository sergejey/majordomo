from mjd_constants import *

try:
    import urllib.request as urllib2
    from urllib.parse import quote
    from urllib.parse import urlencode
except ImportError:
    import urllib2
    from urllib import urlencode
import re
import sys
import six
import json
import mjdm

def getHistory(obj_prop, n_value):
    '''
    obj_prop:
        объект и его свойсво (switch1.status)
    n_value:
        может принимать значения: n (число, часов/дней/недель/мясцев/лет) + day, week, month, year
        Пример 2day или 5year
        Возращает данные в виде словаря
    пример ввода:
        getHistory(switch1.status, 2day)
    '''
    #выполняем api запрос
    hystory = BASE_URL + ROOTHTML +"api/history/" + obj_prop + "/" + n_value
    #Модуль urllib.request предназначен для того, чтобы открывать или загружать файлы через HTTP
    req = urllib2.Request(hystory)
    # открываем соединение к URL-адресу с помощью urllib2
    response = urllib2.urlopen(req)
    #читаем страничку
    the_page = response.read()
    #перекодируем прочитаную табличку из бинарнного вывода в читабельный для json вид
    the_page = the_page.decode("utf-8").replace("'", '"')
    #при помощи модуля json.loads пребразуем полученный выод на страничке в словарь
    the_page = json.loads(the_page)
    resault = the_page['result']
    return resault


def getHistoryexec(obj_prop, n_value, param):
    '''
    obj_prop:
        объект и его свойсво (switch1.status)
    n_value:
        может принимать значения: n (число, часов/дней/недель/мясцев/лет) + day, week, month, year
        Пример 2day или 5year
    param:
        может принимать значения:
            max -- максимальное значение за период
            min -- минимальное значение за период
            avg -- среднее значение за период
            sum -- сумма значений за период
            count -- количество значений за период
    пример ввода:
        getHistory(switch1.status, 2day, sum )
    '''
    hystory = BASE_URL + ROOTHTML +"api/history/" + obj_prop + "/" + n_value + "/" + param
    req = urllib2.Request(hystory)
    response = urllib2.urlopen(req)
    the_page = response.read()
    the_page = the_page.decode("utf-8").replace("'", '"')
    the_page = json.loads(the_page)
    resault = the_page['result']
    return resault