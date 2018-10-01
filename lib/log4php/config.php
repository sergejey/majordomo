<?php

return array(
   'rootLogger' => array(
      'appenders' => array('default'),
      'level' => 'TRACE'
   ),
   'appenders' => array(
      'default' => array(
         'class' => 'LoggerAppenderDailyFile',
         'layout' => array(
            'class' => 'LoggerLayoutPattern',
            'params' => array(
               'conversionPattern' => '%d{H:i:s} %p [%c]: %m (at %F:%L) %x%n',
             ),
         ),
         'params' => array(
            'file' => 'cms/debmes/%s.log',
            'datePattern' => 'Y-m-d',
         )
      ),
      'objectsFileLog' => array(
         'class' => 'LoggerAppenderDailyFile',
         'layout' => array(
            'class' => 'LoggerLayoutPattern',
            'params' => array(
               'conversionPattern' => '%d{H:i:s} %p [%c]: %m (at %F:%L) %x%n',
             ),
         ),
         'params' => array(
            'file' => 'cms/debmes/%s.log',
            'datePattern' => 'Y-m-d',
         )
      )
    ),
   'loggers' => array(
      'class.object' => array(
         'level' => 'TRACE',
         'appenders' => array('default'),
         'additivity' => false,
      ),
      'dblog' => array(
         'level' => 'TRACE',
         'appenders' => array('default'),
         'additivity' => false
      )
   ),
);

?>