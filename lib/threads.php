<?php

// Thanks to http://habrahabr.ru/post/75454/

class Threads {
    public $phpPath = 'php';
    
    private $lastId = 0;
    private $descriptorSpec = array(
        0 => array('pipe', 'r'),
        1 => array('pipe', 'w')
    );
    private $handles = array();
    private $streams = array();
    private $results = array();
    private $pipes = array();
    private $commandLines = array();
    private $timeout = 10;
    private $lastCheck = 0;
    
    public function newThread($filename, $params=array()) {
        if (!file_exists($filename)) {
            throw new ThreadsException('FILE_NOT_FOUND');
        }
        
        $params = addcslashes(serialize($params), '"');
        $command = $this->phpPath.' -q '.$filename.' --params "'.$params.'"';
        ++$this->lastId;

        $this->commandLines[$this->lastId] = $command;        
        $this->handles[$this->lastId] = proc_open($command, $this->descriptorSpec, $pipes);
        $this->streams[$this->lastId] = $pipes[1];
        $this->pipes[$this->lastId] = $pipes;
        
        return $this->lastId;
    }

    public function newXThread($filename, $display='101', $params=array()) {
        /*
        Функция создает поток на отдельном экране в LINUX.
        */
        if (!(substr(php_uname(), 0, 5) == "Linux")) {
            throw new ThreadsException('FOR_LINUX_ONLY');
        }
        if (!file_exists($filename)) {
            throw new ThreadsException('FILE_NOT_FOUND');
        }
        
        $params = addcslashes(serialize($params), '"');
        $command = 'DISPLAY=:'.$display.' '.$this->phpPath.' '.$filename.' --params "'.$params.'"';
        ++$this->lastId;

        $this->commandLines[$this->lastId] = $command;        
        $this->handles[$this->lastId] = proc_open($command, $this->descriptorSpec, $pipes);
        $this->streams[$this->lastId] = $pipes[1];
        $this->pipes[$this->lastId] = $pipes;
        
        return $this->lastId;
    }

    public function getPipes() {
     return $this->pipes;
    }
    
    public function iteration() {
        if (!count($this->streams)) {
            return false;
        }
        $read = $this->streams;

        if (false === ($number_of_streams=stream_select($read, $write=null, $except=null, $this->timeout))) {
         DebMes("No active streams");
         return 0;
        }

       /* 
        $stream = next($read);
        if (!$stream) {
         reset($read);
         $stream=current($read);
        }
        */

        foreach($read as $stream) {


        $id = array_search($stream, $this->streams);
        //$result = stream_get_contents($this->pipes[$id][1]);
        if (feof($stream) || ($contents = fread($stream, 255))==false) {
            fclose($this->pipes[$id][0]);
            fclose($this->pipes[$id][1]);
            proc_close($this->handles[$id]);
            echo "\n".date('H:i:s')." Closing thread: ".$this->commandLines[$id];
            DebMes("Closing thread: ".$this->commandLines[$id]);
            unset($this->handles[$id]);
            unset($this->streams[$id]);
            unset($this->pipes[$id]);
            unset($this->commandLines[$id]);
        } else {
          echo $contents;
          //echo "\n".date('H:i:s')." Thread is running OK: ".$this->commandLines[$id];
        }

        }

        return $result;
    }
    
    public static function getParams() {
        foreach ($_SERVER['argv'] as $key => $argv) {
            if ($argv == '--params' && isset($_SERVER['argv'][$key + 1])) {
                return unserialize($_SERVER['argv'][$key + 1]);
            }
        }
        return false;
    }
    
}

class ThreadsException extends Exception {
}

?>