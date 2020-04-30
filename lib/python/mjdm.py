# -*- coding: utf-8 -*-
from mjd_constants import *

try:
    import urllib.request as urllib2
    from urllib.parse import urlencode
except ImportError:
    import urllib2
    from urllib import urlencode
import re
import MySQLdb as mdb
import sys

def getURL(url):
    response = urllib2.urlopen(url).read()
    return response

def callAPI(api_url, method = "GET", **params):

    params['no_session']=1
    url = re.sub(r"^/api/", BASE_URL+ROOTHTML+'api.php/', api_url)

    data = urlencode(params).encode('utf-8')
    if (method == "POST"):
        req = urllib2.Request(url, data)
        response = urllib2.urlopen(req)
    else:
        url += "?"+data
        response = urllib2.urlopen(url)

    the_page = response.read()
    #print the_page
    return the_page

def runScript(script_name, **params):
    callAPI("/api/script/"+script_name,"GET",params)
    return 1

def callMethod(method_name, **params):
    callAPI("/api/method/"+method_name,"GET",params)
    return 1

def setGlobal(property, value):
    callAPI("/api/data/"+property, "POST", data=value)
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
        cur.execute("SELECT VALUE FROM pvalues WHERE PROPERTY_NAME='"+property+"'")
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


class mjdObject:
    object_name = ""
    def __init__(self, object_name):
        self.object_name = object_name

    def setProperty(self, property_name, value):
        setGlobal(self.object_name+"."+property_name, value)
        return 1

    def getProperty(self, property_name):
        result =  getGlobal(self.object_name+"."+property_name)
        return result

    def callMethod(self, method_name, **params):
        result =  callMethod(self.object_name+"."+method_name, params)
        return result
