<?php

/**
 * @author col.shrapnel@gmail.com
 * @license Apache license 2.0
 *
 * The best PDO wrapper
 */

namespace DAL;

use \PDO as PDO;
use \PDOStatement as PDOStatement;

class DataBase
{
   const HOST_NAME = "localhost";
   const DATABASE_NAME = 'db_terminal';
   const DATABASE_USER = 'root';
   const DATABASE_PASSWORD = '1qazxsw2';
   
   const DBTYPE__INT = 1;
   const DBTYPE__STR = 2;
   const DBTYPE__DATE = 3;
   const DBTYPE__DATETIME = 4;
   
   protected static $instance = null;

   final private function __construct() {}
   final private function __clone() {}

   public static function instance()
   {
      if (self::$instance === null)
      {
         $opt  = array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STATEMENT_CLASS    => array('DAL\myPDOStatement'),
         );
         $dsn = 'mysql:host=' . self::HOST_NAME . ';dbname=' . self::DATABASE_NAME . ';charset=utf8';
         self::$instance = new PDO($dsn, self::DATABASE_USER, self::DATABASE_PASSWORD, $opt);
      }
      return self::$instance;
   }
   
   public static function __callStatic($method, $args)
   {
      return call_user_func_array(array(self::instance(), $method), $args);
   }
   
   public static function SelectQuery($query)
   {
      return self::prepare($query)->execute()->fetchAll();
   }
   
   public static function WriteException($message)
   {
      $log = getLogger();
      $log->error($message);
   }
}

class myPDOStatement extends PDOStatement
{
   function execute($data = array())
   { 
      
      parent::execute($data);
      
      return $this;
   }
}

