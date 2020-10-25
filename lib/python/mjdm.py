# -*- coding: utf-8 -*-
from mjd_constants import *
import re
import sys
import six
import events as ev
import job
import historys as hist
import timers as tm
import general as gn


def getURL(url):

    return gn.getURL(url)


def callAPI(api_url, method="GET", params={}):

    return gn.callAPI(api_url, method="GET", params={})


def say(ph, level=0, member_id=0, source=1):
    gn.say(ph, level=0, member_id=0, source=1)
    return 1


def runScript(script_name, params={}):
    gn.runScript(script_name, params)
    return 1


def callMethod(method_name, params={}):
    gn.callMethod(method_name, params)
    return 1


def setGlobal(property, value):
    gn.setGlobal(property, value)
    return 1


def sg(property, value):
    result = gn.setGlobal(property, value)
    return result


def getGlobal(property):
    return gn.getGlobal(property)


def gg(property):
    result = gn.getGlobal(property)
    return result


def getObjectsByClass(class_name):
    return gn.getObjectsByClass(class_name)


def registerEvent(eventName, details, expire_in):
    ev.registerEvent(eventName, details, expire_in)
    return 1


def registeredEventTime(eventName):
    return ev.registeredEventTime(eventName)



def registeredEventDetails(eventName):
    return ev.registeredEventDetails(eventName)


def timeConvert(tm):
    return tm.timeConvert(tm)


def timeBetween(tm1, tm2):
   return tm.timeBetween(tm1, tm2)


def clearScheduledJob(title):
    job.clearScheduledJob(title)
    return


def addScheduledJob(title, commands, datetime, expire=1800):
    return job.addScheduledJob(title, commands, datetime, expire=1800)

def setTimeOut(title, commands, timeout):
    return job.setTimeOut(title, commands, timeout)


def getHistory(obj_prop, n_value):
    return  hist.getHistory(obj_prop, n_value)


def getHistoryexec(obj_prop, n_value, param):
    return hist.getHistoryexec(obj_prop, n_value, param)


class mjdObject:
    object_name = ""

    def __init__(self, object_name):
        self.object_name = object_name

    def setProperty(self, property_name, value):
        gn.setGlobal(self.object_name + "." + property_name, value)
        return 1

    def getProperty(self, property_name):
        result = gn.getGlobal(self.object_name + "." + property_name)
        return result

    def callMethod(self, method_name, params={}):
        result = gn.callMethod(self.object_name + "." + method_name, params)
        return result
