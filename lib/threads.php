<?php

/**
 * Summary of Threads
 * Thanks to http://habrahabr.ru/post/75454/
 */
class Threads
{
   public $phpPath = 'php';
   private $lastId = 0;
   
   private $descriptorSpec = array(
       0 => array('pipe', 'r'),
       1 => array('pipe', 'w')
   );

   public $handles      = array();
   private $streams      = array();
   private $results      = array();
   private $pipes        = array();
   public $commandLines = array();
   private $timeout      = 5;
   private $lastCheck    = 0;


   public function closeThread($id) {
      $pstatus = proc_get_status($this->handles[$id]);
      $pid = $pstatus['pid'];
      $cmd = $pstatus['command'];
      DebMes("Closing thread $pid ($cmd)",'threads');
      //if (!proc_terminate($this->handles[$id])) {
         //DebMes("Cannot close process $pid ($cmd) with proc_terminate",'threads');
         if (IsWindowsOS()) {
            $exec_str="taskkill /F /T /PID $pid";
         } else {
            $exec_str="kill -9 $pid";
         }
         DebMes("Executing: ".$exec_str,'threads');
         $output = array();
         $result = exec($exec_str,$output);
         DebMes("Result: ".implode("\n",$output),'threads');
         /*
      } else {
         DebMes("Process $pid ($cmd) closed with proc_terminate",'threads');
      }
         */
   }

   /**
    * Summary of newThread
    * @param mixed $filename File name
    * @param mixed $params   Params
    * @throws ThreadsException
    * @return double|int
    */
   public function newThread($filename, $params = array())
   {
      if (!file_exists($filename))
      {
         DebMes("Cannot start thread '$filename' -- FILE NOT FOUND");
         return false;
      }

      $params = addcslashes(serialize($params), '"');

      //if (defined('LOG_CYCLES') && LOG_CYCLES=='1') {
      $fileToWrite = DOC_ROOT . '/cms/debmes/log_' . date('Y-m-d') . '-' . basename($filename) . '.txt';
      $command = $this->phpPath . ' -q ' . $filename . ' --params "' . $params . '">>' . $fileToWrite;

      if (!IsWindowsOS()) {
       $command='exec '.$command;
      }

      /*} else {
      $command = $this->phpPath.' -q '.$filename.' --params "'.$params.'"';
      }
       */
      ++$this->lastId;

      echo date('H:i:s') . " Starting thread: " . $command . "\n";
      $this->commandLines[$this->lastId] = $command;
      $this->handles[$this->lastId]      = proc_open($command, $this->descriptorSpec, $pipes);

      stream_set_blocking($pipes[0], 0);
      stream_set_blocking($pipes[1], 0);
      stream_set_timeout($pipes[0], $this->timeout);
      stream_set_timeout($pipes[1], $this->timeout);
      
      $this->streams[$this->lastId] = $pipes[1];
      $this->pipes[$this->lastId]   = $pipes;

      return $this->lastId;
   }

   /**
    * Функция создает поток на отдельном экране в LINUX
    * @param mixed $filename File name
    * @param mixed $display  Display
    * @param mixed $params   Params
    * @throws ThreadsException
    * @return double|int
    */
   public function newXThread($filename, $display = '101', $params = array())
   {
      if (IsWindowsOS())
         throw new ThreadsException('FOR_LINUX_ONLY');

      if (!file_exists($filename)) {
         DebMes("Cannot start thread '$filename' -- FILE NOT FOUND");
         return false;
      }

      $params  = addcslashes(serialize($params), '"');
      $command = 'DISPLAY=:' . $display . ' ' . $this->phpPath . ' ' . $filename . ' --params "' . $params . '"';
      
      ++$this->lastId;

      $this->commandLines[$this->lastId] = $command;

      echo date('H:i:s') . " Starting threadx: " . $command . "\n";
      $this->handles[$this->lastId]      = proc_open($command, $this->descriptorSpec, $pipes);
      
      stream_set_timeout($pipes[0], $this->timeout);
      stream_set_timeout($pipes[1], $this->timeout);
      stream_set_blocking($pipes[0], 0);
      stream_set_blocking($pipes[1], 0);
      
      $this->streams[$this->lastId] = $pipes[1];
      $this->pipes[$this->lastId]   = $pipes;

      return $this->lastId;
   }

   /**
    * Summary of getPipes
    * @return array[]
    */
   public function getPipes()
   {
      return $this->pipes;
   }

   /**
    * Summary of iteration
    * @return bool|int|string
    */
   public function iteration()
   {
      $result = '';
      
      if (!count($this->streams))
      {
         return false;
      }

      $read   = $this->streams;
      $write  = null;
      $except = null;

      //echo date('H:i:s')." Selecting streams"."\n";
      if (false === ($number_of_streams = stream_select($read, $write, $except, $this->timeout)))
      {
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
      global $output_show;
      global $delayed;

      $now = time();

      foreach ($read as $stream)
      {
         $id = array_search($stream, $this->streams);

         stream_set_blocking($stream, 1);
         stream_set_timeout($stream, $this->timeout);
         
         $stream_status = stream_get_meta_data($stream);
         $proc_status   = proc_get_status($this->handles[$id]);

         if ($output_show[$this->commandLines[$id]] != $now)
         {
            $output_show[$this->commandLines[$id]] = $now;
            
            $name = $this->commandLines[$id];
            
            if (preg_match('/cycle_.+?\.php/', $name, $m))
            {
               $name = $m[0];
            }

            //echo date('H:i:s') . " working thread: " . $name . "\n";
            //echo "Status:\n";
            //print_r($proc_status);
            //echo "\n";
         }

         if (!$proc_status['running'])
         {
            //feof($stream)
            echo date('H:i:s') . " Closing thread: " . $this->commandLines[$id] . "\n";
            DebMes("Closing thread: " . $this->commandLines[$id]);
            
            $result .= "THREAD CLOSED: [" . $this->commandLines[$id] . "]\n";
            
            fclose($this->pipes[$id][0]);
            fclose($this->pipes[$id][1]);

            proc_close($this->handles[$id]);

            unset($this->handles[$id]);
            unset($this->streams[$id]);
            unset($this->pipes[$id]);
            unset($this->commandLines[$id]);
         }
         else
         {
            $result .= "1";
         }

         /*
         //$result = stream_get_contents($this->pipes[$id][1]);
         if (feof($stream) || ($contents = fread($stream, 150))==false) { //)
         //if (feof($stream) || ($contents = fgets($stream, 4096))==false) {
         //if (feof($stream) || ($contents = stream_socket_recvfrom($stream, 4096, STREAM_PEEK))) {
         echo date('H:i:s')." Closing thread: ".$this->commandLines[$id]."\n";
         DebMes("Closing thread: ".$this->commandLines[$id]);
         $result.="THREAD CLOSED: [".$this->commandLines[$id]."]\n";
         fclose($this->pipes[$id][0]);
         fclose($this->pipes[$id][1]);
         proc_close($this->handles[$id]);
         unset($this->handles[$id]);
         unset($this->streams[$id]);
         unset($this->pipes[$id]);
         unset($this->commandLines[$id]);
         } else {
         echo "Got: ";
         echo $contents;
         $result.=$contents;
         }
          */
      }

      if ($delayed != $now)
      {
         $delayed = $now;
         sleep(1);
      }

      return $result;
   }

   /**
    * Summary of getParams
    * @return mixed
    */
   public static function getParams()
   {
      foreach ($_SERVER['argv'] as $key => $argv)
      {
         if ($argv == '--params' && isset($_SERVER['argv'][$key + 1]))
         {
            return unserialize($_SERVER['argv'][$key + 1]);
         }
      }

      return false;
   }
}

/**
 * Summary of ThreadsException
 */
class ThreadsException extends Exception
{
}

