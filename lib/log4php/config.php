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
            'file' => 'debmes/%s.log',
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
            'file' => 'debmes/%s.log',
            'datePattern' => 'Y-m-d',
         )
      ),
      'defaultDB' => array(
         'class' => 'LoggerAppenderPDO',
         'params' => array(
            'dsn' => 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
            'user' => DB_USER,
            'password' => DB_PASSWORD,
            'table' => 'log4php_log',
         )
      ), 
    ),
   'loggers' => array(
      'class.object' => array(
         'level' => 'TRACE',
         'appenders' => array('objectsFileLog'),
         'additivity' => false,
      ),
      'dblog' => array(
         'level' => 'TRACE',
         'appenders' => array('defaultDB'),
         'additivity' => false
      )
   ),
);

?>