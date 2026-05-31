# -*- coding: utf-8 -*-
from mjd_constants import *
import re
import sys
import six

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
