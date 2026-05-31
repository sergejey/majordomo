<?php

/**
 * PDO database class
 *
 * @package MajorDoMo
 * @author Sergey Fedotov <fsa@tavda.net>
 * @copyright https://tavda.net/ (c) 2018
 * @version 0.1
 */
class DB {

    /**
     * PDO object
     * @var type 
     */
    private static $_instance=null;

    /**
     * Singleton class
     */
    private function __construct() {
        
    }

    private function __clone() {
        
    }

    public function __sleep() {
        
    }

    public static function getInstance() {
        if (self::$_instance) {
            return self::$_instance;
        }
        self::$_instance=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8',DB_USER,DB_PASSWORD);
        self::$_instance->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        return self::$_instance;
    }

    public static function isConnected() {
        return !is_null(self::$_instance);
    }

    public static function insert($table,$values) {
        $keys=array_keys($values);
        $stmt=self::prepare('INSERT INTO '.$table.' ('.join(',',$keys).') VALUES (:'.join(',:',$keys).')');
        $stmt->execute($values);
        $id=DB::lastInsertId();
        $stmt->closeCursor();
        return $id;
    }

    public static function update($table,$values,$index='ID') {
        $keys=array_keys($values);
        $i=array_search($index,$keys);
        if ($i!==false) {
            unset($keys[$i]);
        }
        foreach ($keys as &$key) {
            $key=$key.'=:'.$key;
        }
        $stmt=self::prepare('UPDATE '.$table.' SET '.join(',',$keys).' WHERE '.$index.'=:'.$index);
        $stmt->execute($values);
    }

    
    
    public static function __callStatic($name,$args) {
        $callback=array(self::getInstance(),$name);
        return call_user_func_array($callback,$args);
    }

}
