<?php
/*
VERSION: 2007.01.11 - 17:05  BRST

Copyright (c) 2007 Spadim Technology / Brazil. All rights reserved.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or (at
your option) any later version.

This program is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
        
OWFS is an open source project developed by Paul Alfille and hosted at
http://www.owfs.org

 mailto:roberto@spadim.com.br
        www.spadim.com.br
        
        
*/


/*
from php.net
pack($format, ... )     
        format='i' : signed integer (machine dependent size and byte order)
 we will have problems with 64bits ?? using this-> pack / unpack builtin function
 
 
from linux sources
#include <arpa/inet.h>

uint32_t                        (pack format = 'N')
htonl(uint32_t hostlong);

uint16_t                        (pack format = 'n')
htons(uint16_t hostshort);

uint32_t                        (pack format = 'n')
ntohl(uint32_t netlong);

uint16_t                        (pack format = 'n')
ntohs(uint16_t netshort);

from http://www.linuxjournal.com/article/6788
        IP's byte order also is big endian.
*/

// Constants for the OWNet class
define('OWNET_DEFAULT_HOST'     ,'127.0.0.1');
define('OWNET_DEFAULT_PORT'     ,4304);
define('OWNET_LINK_TYPE_SOCKET' ,0);
define('OWNET_LINK_TYPE_STREAM' ,1);
define('OWNET_LINK_TYPE_TCP'    ,0);
define('OWNET_LINK_TYPE_UDP'    ,1);

/*
Constants for the owserver api message types. from ow.h
enum msg_classification {
    msg_error,
    msg_nop,
    msg_read,
    msg_write,
    msg_dir,
    msg_size,                   // No longer used, leave here to compatibility
    msg_presence,
};
*/
define('OWNET_MSG_ERROR'        ,0);
define('OWNET_MSG_NOP'          ,1);
define('OWNET_MSG_READ'         ,2);
define('OWNET_MSG_WRITE'        ,3);
define('OWNET_MSG_DIR'          ,4);
define('OWNET_MSG_SIZE'         ,5);
define('OWNET_MSG_PRESENCE'     ,6);
define('OWNET_MSG_DIR_ALL'      ,7);
define('OWNET_MSG_READ_ANY'     ,99999);


if (!defined('TCP_NODELAY'))
        define('TCP_NODELAY',1);
if (!defined('IPPROTO_TCP'))
        define('IPPROTO_TCP',6);

$OWNET_GLOBAL_CACHE_STRUCTURE=array();  // cache value types length read write....
class OWNet{
        protected $link=0;
        protected $host='';
        protected $port=0;
        protected $sock_type=OWNET_LINK_TYPE_TCP;
        protected $link_type=OWNET_LINK_TYPE_SOCKET;
        protected $link_connected=false;
        protected $timeout=0;
        protected $use_swig_dir=true;

        function __construct($host='',$timeout=5,$use_swig_dir=true){
                // just set default configurations
                $this->setHost($host);
                $this->timeout=abs((double)$timeout);
                $this->use_swig_dir=(bool)$use_swig_dir;
        }
        function setTimeout($timeout=5){
                $this->timeout=abs((double)$timeout);
        }
        function getTimeout(){
                return($this->timeout);
        }
        function setUseSwigDir($use){
                $this->use_swig_dir=(bool)$use;
        }
        function getUseSwigDir(){
                return($this->use_swig_dir);
        }
        function setHost($host=''){     
                // host must be "anything://host:port" or "anything://host" OR 'anything that don't parse_url and get default values'
                // use "stream://host:port" or "ow-stream://host:port" to prefer stream instead sockets
                $tmp_path       =@parse_url($host);     // get URL information from host
                if (!isset($tmp_path['scheme']))
                        $tmp_path       =@parse_url("tcp://$host");
                
                $this->host     =       (!isset($tmp_path['host'])?OWNET_DEFAULT_HOST:$tmp_path['host']);       // if don't have host get default host
                $this->port     =(int)  (!isset($tmp_path['port'])?OWNET_DEFAULT_PORT:$tmp_path['port']);       // if don't have port get default port
                $prefer_sock    =(isset($tmp_path['scheme'])?
                        ($tmp_path['scheme']!='stream' && $tmp_path['scheme']!='ow-stream' &&
                         $tmp_path['scheme']!='stream-udp' && $tmp_path['scheme']!='ow-stream-udp')
                        :true); // check if prefer using streams instead socket
                if (strpos($tmp_path['scheme'],'udp')!==false)
                        $this->sock_type=OWNET_LINK_TYPE_UDP;
                else
                        $this->sock_type=OWNET_LINK_TYPE_TCP;
                unset($tmp_path);
                
                if (function_exists('socket_connect') && $prefer_sock){ 
                        $this->link_type=OWNET_LINK_TYPE_SOCKET;        // prefer socks
                }else{
                        $this->link_type=OWNET_LINK_TYPE_STREAM;        // prefer stream
                }
                $this->link_connected           =false;
                return(true);
        }
        function getHost(){
                // return and URI that can be used with setHost again
                if ($this->link_type==OWNET_LINK_TYPE_STREAM)   
                        return('ow-stream'.($this->sock_type==OWNET_LINK_TYPE_UDP?'-udp':'').'://'.$this->host.':'.$this->port);        // using streams
                return('ow'.($this->sock_type==OWNET_LINK_TYPE_UDP?'-udp':'').'://'.$this->host.':'.$this->port);                       // using sockets if possible
        }
        protected function pack_htonl( $val ){
                // builtin function to use htonl, big endian style
                $bval   =str_pad(decbin(bcadd($val,0,0)),8*4,'0',STR_PAD_LEFT);
                $b1     =bindec(substr($bval,0,8));
                $b2     =bindec(substr($bval,8,8));
                $b3     =bindec(substr($bval,16,8));
                $b4     =bindec(substr($bval,24,8));
                return(chr($b1).chr($b2).chr($b3).chr($b4));
        }
        protected function unpack_ntohl($str){
                // builtin function to use ntohl, big endian style, not shure if it's right
                $size=strlen($str)/4;
                $ret=array();
                for($i=0;$i<$size;$i++){
                        $bval   =substr($str,$i*4,4);
                        $b1     =ord(substr($bval,0,1));
                        $b2     =ord(substr($bval,1,1));
                        $b3     =ord(substr($bval,2,1));
                        $b4     =ord(substr($bval,3,1));
                        $value  =$b4 + ($b3<<8) + ($b2<<16) + ($b1<<24);
                        $ret[$i]=$value;
                }
                return($ret);
        }
        private function disconnect(){
                // disconnect link
                if ($this->link_type==OWNET_LINK_TYPE_SOCKET){          // socket
                        @socket_set_block($this->link);
                        if ($this->sock_type==OWNET_LINK_TYPE_TCP)
                                @socket_shutdown($this->link,2);
                        @socket_close($this->link);
                }else{
                        @fclose($this->link);                           // streams
                }
                $this->link=NULL;
                $this->link_connected=false;
        }
        private function connect(){
                // connect with sockets or stream
                if ($this->link_connected)
                        return(true);           // if connected don't continue
                if ($this->link_type==OWNET_LINK_TYPE_SOCKET){          // socket
                        if ($this->sock_type==OWNET_LINK_TYPE_TCP){
                                $this->link=@socket_create(AF_INET, SOCK_STREAM, SOL_TCP);              // create socket
                                if ($this->link){
                                        @socket_set_block($this->link);                                 // set it to blocking
                                        $ok=@socket_connect($this->link,$this->host,$this->port);       // try to connect
                                        if (!$ok){
                                                $errno  =@socket_last_error();                          // get error when connecting
                                                $errstr =@socket_strerror(socket_last_error());
                                                trigger_error("Can't create socket [ow://".$this->host.":".$this->port."], errno: $errno, error: $errstr",E_USER_NOTICE);
                                                @socket_shutdown($this->link,2);                        // unload socket
                                                @socket_close($this->link);
                                                $this->link=NULL;
                                                return(false);                                          // return false on error or can't connect
                                        }       // socket created and connected
                                }else{
                                        $errno  =@socket_last_error();                                  // get error when creating socket
                                        $errstr =@socket_strerror(@socket_last_error());
                                        trigger_error("Can't create socket [ow://".$this->host.":".$this->port."], errno: $errno, error: $errstr",E_USER_NOTICE);
                                        return(false);                                                  // return false on error or can't connect
                                }
                        }else{  // udp
                                $this->link=@socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);               // create socket
                                if ($this->link){
                                        @socket_set_block($this->link);                                 // set it to blocking
                                }else{
                                        $errno  =@socket_last_error();                                  // get error when creating socket
                                        $errstr =@socket_strerror(@socket_last_error());
                                        trigger_error("Can't create socket [ow-udp://".$this->host.":".$this->port."], errno: $errno, error: $errstr",E_USER_NOTICE);
                                        return(false);                                                  // return false on error or can't connect
                                }
                        }
                }else{                                                  // stream
                        $this->link     =@stream_socket_client(
                                ($this->sock_type==OWNET_LINK_TYPE_TCP?'tcp://':'udp://').
                                $this->host.":".$this->port, $errno, $errstr, $this->timeout);          // connect with streams, could be with fsockopen but stream_socket_client is faster (we will use PHP 5+)
                        if (!$this->link){
                                trigger_error("Can't create stream [ow-stream".($this->sock_type!=OWNET_LINK_TYPE_TCP?'-udp':'')."://".$this->host.":".$this->port."], errno: $errno, error: $errstr",E_USER_NOTICE);
                                return(false);                                                  // return false on error or can't connect
                        }
                }
                $this->set_link_options();      // set socket options or stream options
                $this->link_connected=true;
                return(true);                   // ok 
        }
        private function set_link_options(){
                // set link options
                if (!$this->link_connected)
                        return(false);
                if ($this->link_type==OWNET_LINK_TYPE_SOCKET){          // socket
                        socket_set_block(       $this->link);                           // set blocking mode
                        if ($this->sock_type==OWNET_LINK_TYPE_TCP){
                                socket_set_option(      $this->link,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>0, "usec"=>100));     // receive timeout
                                socket_set_option(      $this->link,SOL_SOCKET, SO_SNDTIMEO, array("sec"=>0, "usec"=>100));     // send timeout
                                socket_set_option($this->link, SOL_SOCKET, SO_REUSEADDR, 1);    // reuse address
                                socket_set_option($this->link, SOL_SOCKET, SO_OOBINLINE, 1);    // out off band inline
                                @socket_set_option($this->link, IPPROTO_TCP, TCP_NODELAY, 1);   // no delay  can have bug with windows?!
                        }
                        socket_set_option($this->link, SOL_SOCKET, SO_RCVBUF, 8192);    // set receive buffer
                        socket_set_option($this->link, SOL_SOCKET, SO_SNDBUF, 8192);    // set send buffer
                }else{
                        stream_set_timeout($this->link          ,20);                   // set timeout
                        stream_set_blocking($this->link         ,1);                    // set blocking mode
                        stream_set_write_buffer($this->link     ,0);                    // flush everything directly without buffer (faster than with buffer=8192)
                }
                return(true);
        }
        private function get_msg($msg_size=24){         // return false on error
                // get messagem from server
                $num_changed_sockets    =0;
                $read_data              ='';
                $last_read              =microtime(1);
                $t1=intval($this->timeout);
                $t2=($this->timeout*1000000)%1000000;
                while ($num_changed_sockets<=0){        // can loop forever? owserver must send something! or disconnect!
                        $read=array($this->link);
                        if ($this->link_type==OWNET_LINK_TYPE_SOCKET)
                                @$num_changed_sockets = socket_select($read, $write = NULL, $except = NULL, $t1,$t2);    // use socket_select
                        else
                                @$num_changed_sockets = stream_select($read, $write = NULL, $except = NULL, $t1,$t2);    // use stream_select
                        if ($num_changed_sockets===false){      // error handling select
                                $this->disconnect();
                                trigger_error("Error handling get_msg#1",E_USER_NOTICE);
                                return(false);                  // return false when have error
                        }elseif($num_changed_sockets>0){        // we can read!
                                if ($this->link_type==OWNET_LINK_TYPE_SOCKET){
                                        if ($this->sock_type==OWNET_LINK_TYPE_TCP){
                                                $read_data=socket_read($this->link,$msg_size,PHP_BINARY_READ);  // read with sockets
                                        }else{
                                                $ret=socket_recvfrom($this->link,$read_data,$msg_size,$tmp_host,$tmp_port);     // read with sockets
                                                if ($ret>0){
                                                        $this->host=$tmp_host;
                                                        $this->port=$tmp_port;
                                                }
                                        }
                                }else
                                        $read_data=fread($this->link,$msg_size);                        // read with streams
                                if ($read_data==''){            // disconnected :'(
                                        $this->disconnect();
                                        trigger_error("Disconnected",E_USER_NOTICE);
                                        return(false);                  // return false when have error
                                }else
                                        $last_read              =microtime(1);
                                break;
                        }else
                                break;
                        if (microtime(1)-$last_read>$this->timeout)
                                break;
                }
                return($read_data);                     // return data
        }
        private function send_msg($string){     // return false on error and true on success, trigger error on disconnection
                // send message to server
                if (!$this->link_connected)
                        return(false);
                $num_changed_sockets=0;
                while ($num_changed_sockets<=0){
                        $write=array($this->link);
                        if ($this->link_type==OWNET_LINK_TYPE_SOCKET)
                                @$num_changed_sockets = socket_select($read = NULL, $write , $except = NULL, 0,1000);    // use socket_select
                        else
                                @$num_changed_sockets = stream_select($read = NULL, $write , $except = NULL, 0,1000);    // use stream_select
                        if ($num_changed_sockets===false){              // error handling
                                $this->disconnect();
                                trigger_error("Error handling send_msg#1",E_USER_NOTICE);
                                return(false);                          // return false on error
                        }
                }
                $size=strlen($string);
                $sent=0;
                while($sent<$size){
                        // we will not use select, using can be slower, and without work! :D
                        if ($this->link_type==OWNET_LINK_TYPE_SOCKET){
                                if ($this->sock_type==OWNET_LINK_TYPE_TCP)
                                        $ret=socket_write($this->link, $string, strlen($string));                               // write and get sent bytes
                                else
                                        $ret=socket_sendto($this->link, $string, strlen($string),0,$this->host,$this->port);    // write and get sent bytes
                        }else
                                $ret=fwrite($this->link,$string,strlen($string));               // write and get sent bytes
                        if ($ret===false){
                                // error sending
                                $this->disconnect();
                                trigger_error("Error writing send_msg#1",E_USER_NOTICE);
                                return(false);                          // return false on error
                        }
                        $sent+=$ret;    // add sent bytes
                }
                return(true);           // ok everything sent
        }
        function read($path,$parse_value=true){
                // return NULL on error or no file
                // if $parse_value return php parsed value type (boolean,double,string), if not return an string value
                return($this->get($path,OWNET_MSG_READ,false,$parse_value));            // return get with right flags
        }
        function dir($path){
                // return NULL on error or no directory
                // return numeric array starting from 0 with directory list
                return($this->get($path,OWNET_MSG_DIR_ALL,false,false));                // return get with right flags
        }
        function presence($path){
                // return NULL on error
                // return true or false on success
                return($this->get($path,OWNET_MSG_PRESENCE,false,false));       // return get with right flags
        }
        function get($path='/',$get_type=OWNET_MSG_READ,$return_full_info_array=false,$parse_php_type=true){            // return NULL on error
                // path = path of file or directory
                // get_type =   OWNET_MSG_READ_ANY      (READ AND DIR)
                //              OWNET_MSG_READ          read an file
                //              OWNET_MSG_DIR or OWNET_MSG_DIR_ALL              read an directory (output is an array)
                //              OWNET_MSG_PRESENCE      check presence output is true or false
                // return_full_info_array       if true return everything from comunication and unit if variables is an temperature, ['data'] is returned data and ['data_php'] is an parsed data in (double) or (string) types
                // parse_php_type               if true try to get right data_php variable type
        
                // return NULL on error or not founded
                // return an array on OWNET_MSG_DIR or when return_full_info_array=true
                // return true or false when OWNET_MSG_PRESENCE
        
                $path=trim($path);      // trim path
                if ($get_type==OWNET_MSG_READ_ANY){
                                        // getting first read
                        $ret=$this->get($path,OWNET_MSG_READ,$return_full_info_array,$parse_php_type);
                        if ($ret!==NULL)
                                return($ret);   // ok we get and result
                        return($this->get($path,OWNET_MSG_DIR_ALL,$return_full_info_array,$parse_php_type));    // return dir
                }
                if ($get_type!=OWNET_MSG_DIR && $get_type!=OWNET_MSG_DIR_ALL){
                        if (substr($path,strlen($path)-1,1)=='/')       // isn't a dir, dir must end with characters != '/'
                                return(NULL);
                }
                $this->disconnect();            // be sure that we are disconnected
                $this->connect();               // try to connect
                if (!$this->link_connected){
                        trigger_error("Can't connect get#1",E_USER_NOTICE);
                        return(NULL);
                }
                // get value
                if ($get_type==OWNET_MSG_DIR || $get_type==OWNET_MSG_DIR_ALL){  // get right send function
                        $msg=$this->pack($get_type              ,strlen($path)+1,0      );
                }elseif ($get_type==OWNET_MSG_PRESENCE){
                        $msg=$this->pack(OWNET_MSG_PRESENCE     ,strlen($path)+1,0      );
                }else{
                        $get_type=OWNET_MSG_READ;
                        $msg=$this->pack(OWNET_MSG_READ         ,strlen($path)+1,8192   );
                }
                if ( $this->send_msg($msg)===false ){
                        trigger_error("Can't write to resource get#1",E_USER_NOTICE);
                        return(NULL);   // error sending
                }
                if ( $this->send_msg($path.chr(0))===false){
                        trigger_error("Can't write to resource get#2",E_USER_NOTICE);
                        return(NULL);   // error sending
                }
                if ($parse_php_type)
                        global $OWNET_GLOBAL_CACHE_STRUCTURE;   // we will use for parse_php_type
                // get return
                $return=NULL;           // set to NULL if nothing returned
                while(1){
                        unset($ret,$data,$tmp_ret,$start,$data_len);
                        $data='';$tmp_ret='';
                        $start=microtime(1);
                        while(1){
                                $tmp_ret=$this->get_msg(24);
                                if ($tmp_ret===false){
                                        trigger_error("Can't read from resource get#3",E_USER_NOTICE);
                                        $this->disconnect();
                                        return(NULL);
                                }
                                if (strlen($tmp_ret)>0) $start=microtime(1);
                                $data.=$tmp_ret;
                                unset($tmp_ret);
                                if(strlen($data)>=24 || (microtime(1)-$start)>$this->timeout)
                                        break;
                        }
                        $ret=$this->unpack(substr($data,0,24)); // unpack 24bytes into 6 data 
                        if (count($ret)<6){
                                if ($get_type==OWNET_MSG_DIR_ALL)       // old servers
                                        return($this->get($path,OWNET_MSG_DIR,$return_full_info_array,$parse_php_type));        
                                $data=substr($data,0,24);
                                trigger_error("Error unpacking data get#1 [".strlen($data)."] ".$data,E_USER_NOTICE);
                                $this->disconnect();
                                return(NULL);
                        }
                        if ($get_type==OWNET_MSG_PRESENCE){
                                // check presence
                                if (!isset($ret[2]))
                                        return(NULL);   // error ?! maybe not (count < 6)
                                return($ret[2]===0);    // ret[2]!=0 => not present
                        }
                        $data=substr($data,24);         // if any data was read with more than 24 bytes
                        if (!isset($ret[1]))
                                $ret[1]=false;          // just to be sure that will not get into $ret parsing
                        if ($ret[1]>0){
                                if ($get_type==OWNET_MSG_DIR || $get_type==OWNET_MSG_DIR_ALL)   // reading directory use $ret[1] for read data
                                        $data_len=$ret[1];
                                else
                                        $data_len=$ret[2];      // reading file use $ret[2] for read data
                                $tmp_ret='';
                                $start=microtime(1);            // set start time for timeout read
                                if ($data_len>0)
                                        while(1){
                                                $tmp_ret=$this->get_msg($data_len);
                                                if ($tmp_ret===false){
                                                        trigger_error("Can't read from resource get#4",E_USER_NOTICE);  // error receiving
                                                        $this->disconnect();
                                                        return(NULL);           // return NULL on error
                                                }
                                                if (strlen($tmp_ret)>0) $start=microtime(1);
                                                $data.=$tmp_ret;unset($tmp_ret);
                                                if(strlen($data)>=$data_len || (microtime(1)-$start)>$this->timeout)    // timedout or got every data that we need
                                                        break;
                                        }
                                if (!$return_full_info_array && strlen($data)!=$data_len){      // if just return value and data < data_len return as an error
                                        trigger_error("Can't read full data get#1",E_USER_NOTICE);
                                        $this->disconnect();
                                        return(NULL);           // return NULL on error
                                }
                                if ($get_type==OWNET_MSG_DIR){  // reading dir
                                        $ret['data']    =$data;
                                        $ret['data_len']=strlen($data);
                                        $ret['data_php']=substr($data,0,$ret[4]);       // ret[4] is the right filename size ?! it's work :D
                                        if ($return===NULL)
                                                $return=array();        // se return as an array to get values
                                        if ($return_full_info_array)
                                                $return[]=$ret;         // return an array
                                        else
                                                $return[]=$ret['data_php'];     // return file name
                                        // continue while...
                                }else{
                                        $ret['data']    =substr($data,0,$data_len);     // data_php length is the same as $ret[2]
                                        $ret['data_len']=strlen($data);
                                        $this->disconnect();            // disconnect from server
                                        $type=false;                    // check type?
                                        if ($parse_php_type && $get_type!=OWNET_MSG_DIR_ALL){
                                                $tmp            =explode('/',$path);$c=count($tmp)-1;
                                                if ($c>0){              // must be something like '/dir/file'   array('dir', 'file'), count()-1 = 1 > 0
                                                        $variavel       =$tmp[$c];              // get last two uri args
                                                        $ow             =$tmp[$c-1];
                                                        unset($tmp);
                                                        if (preg_match('/([0-9A-F]{2})[\.]{0,1}[0-9A-F]{12}/',$ow,$tmp)){       // check if ow is an OW id ("XX.ZZZZZZZZZZZZ or XXZZZZZZZZZZZZ")
                                                                $tmp=$tmp[1];
                                                                if (!isset($OWNET_GLOBAL_CACHE_STRUCTURE[$tmp.'/'.$variavel])){ // check if we have structure information
                                                                        $tmp_v=@$this->get("/structure/$tmp/$variavel",OWNET_MSG_READ,false,false);     // get estrutucture information
                                                                        if ($tmp_v!==NULL){
                                                                                $tmp_v=explode(',',$tmp_v);                                             // ok :D we will get real php values now!
                                                                                $OWNET_GLOBAL_CACHE_STRUCTURE[$tmp.'/'.$variavel]=$tmp_v;
                                                                                $type=$tmp_v;
                                                                        }
                                                                }else
                                                                        $type=$OWNET_GLOBAL_CACHE_STRUCTURE[$tmp.'/'.$variavel];
                                                        }
                                                }
                                                unset($tmp,$tmp_v,$variavel,$ow,$c);
                                        }
                                        if ($type!==false){
                                                // get real php values
                                                $ret['data_php']=$ret['data'];
                                                if($type[0]=='i'){
                                                        $ret['data_php']=bcadd(trim($ret['data_php']),0,0);   // integer (using bcmath for bigger precision)
                                                }elseif($type[0]=='u'){
                                                        $ret['data_php']=bcadd(trim($ret['data_php']),0,0);   // unsigned integer (using bcmath for bigger precision)
                                                        if (bccomp($ret['data_php'],0,0)==-1){
                                                                $ret['data_php']=substr($ret['data_php'],1);            // be shure that it's unsigned
                                                        }
                                                }elseif(in_array($type[0],array('f','t',chr(152)))){
                                                        $ret['data_php']=(double)$ret['data_php'];      // using float (double) values, maybe sprinf("%.50f",$value) could get an string representation
                                                        if ($return_full_info_array){
                                                                if ($type[0]=='t'){                     // temperature (last owserver versions... maybe an 'v' for volts and 'A' for amps could be implemented)
                                                                        // we can't cache cause server may restart or change configurations while we have old cache information, here we will have +- .1 seconds of performace lost, set return full information off if you don't want it
                                                                        $tmp_v=@$this->get("/settings/units/temperature_scale",OWNET_MSG_READ,false,false);     // get server current scale
                                                                        if ($tmp_v!==NULL)              // just for compatible with old owservers
                                                                                $ret['unit']=$tmp_v;    // use unit if return full information
                                                                }
                                                        }
                                                }elseif(in_array($type[0],array('a','b','d'))){
                                                        $ret['data_php']=(string)$ret['data_php'];      // string (maybe without it could work too, but's it's pretty :D ) 
                                                }elseif($type[0]=='y'){
                                                        $ret['data_php']=($ret['data_php']==1?true:false);      // boolean content
                                                }       // another contents are parsed as string too
                                        }else{
                                                $ret['data_php']=&$ret['data'];         // we will use not parsed values (we use it when getting structure information! or setting $parse_php_type=false)
                                        }
                                        if ($get_type==OWNET_MSG_DIR_ALL){
                                                $return=& $ret;
                                                break;
                                        }
                                        if ($return_full_info_array){
                                                return($ret);                   // return array
                                        }else{
                                                return($ret['data_php']);       // return just php parsed value
                                        }
                                }
                        }else{
                                break;
                        }
                }
                $this->disconnect();    // disconnect from server (dir listing)
                if ($get_type==OWNET_MSG_DIR_ALL && $return===NULL)     // old servers
                        return($this->get($path,OWNET_MSG_DIR,$return_full_info_array,$parse_php_type));        
                if ($return!==NULL){
                        if ($this->use_swig_dir && !$return_full_info_array && $get_type==OWNET_MSG_DIR)
                                if (is_array($return))
                                        $return=implode(',',$return);
                        if ($this->use_swig_dir==false && $get_type==OWNET_MSG_DIR_ALL){
                                if ($return_full_info_array){
                                        $tmp=explode(',',$return['data_php']);
                                        $r=array();
                                        for ($i=0;$i<count($tmp);$i++)
                                                $r[]=array(
                                                        0=>$return[0],
                                                        1=>$return[1],
                                                        2=>$return[2],
                                                        3=>$return[3],
                                                        4=>$return[4],
                                                        5=>$return[5],
                                                        'data'=>$return['data'],
                                                        'data_len'=>$return['data_len'],
                                                        'data_php'=>$tmp[$i]
                                                );
                                        $return=$r;
                                        unset($tmp,$r);
                                }else
                                        $return=explode(',',$return);
                        }
                }
                return($return);
        }
        function set($path,$value=''){
                // set and value to path checking before if path is readonly or not
                $path=trim($path);      // trim path
                $tmp    =explode('/',$path);$c=count($tmp)-1;
                $type=false;
                if ($c>0){      // try to get information about structure, if file is readonly or not
                        $variavel       =$tmp[$c];
                        $ow             =$tmp[$c-1];
                        unset($tmp);
                        if (preg_match('/([0-9A-F]{2})[\.]{0,1}[0-9A-F]{12}/',$ow,$tmp)){
                                $tmp=$tmp[1];
                                if (!isset($OWNET_GLOBAL_CACHE_STRUCTURE[$tmp.'/'.$variavel])){
                                        $tmp_v=@$this->get("/structure/$tmp/$variavel",OWNET_MSG_READ,false,false);     // get estrutucture information
                                        if ($tmp_v!==NULL){
                                                $tmp_v=explode(',',$tmp_v);
                                                $OWNET_GLOBAL_CACHE_STRUCTURE[$tmp.'/'.$variavel]=$tmp_v;               // ok we will test it
                                                $type=$tmp_v;
                                        }
                                }else
                                        $type=$OWNET_GLOBAL_CACHE_STRUCTURE[$tmp.'/'.$variavel];                        // very fine, it was cached! :D performace boost
                        }
                }
                if ($type!==false){
                        if ($type[3]=='ro'){    // read only file
                                trigger_error("Read only value set#1 [$path]",E_USER_NOTICE);
                                return(false);  // return false on error
                        }
                }
                unset($type,$tmp,$tmp_v,$variavel,$ow,$c);
        
                $this->disconnect();
                $this->connect();
                if (!$this->link_connected){
                        trigger_error("Can't connect set#1",E_USER_NOTICE);
                        return(false);          // return false on error
                }
                if (!is_string($value))
                        $value=(string)$value;  // be sure that's an string
                $msg=$this->pack(OWNET_MSG_WRITE,strlen($path)+1+strlen($value)+1,strlen($value)+1);    // pack data
                if ( $this->send_msg($msg)===false ){
                        trigger_error("Can't write to resource set#1",E_USER_NOTICE);
                        return(false);  // error sending
                }
                if ( $this->send_msg($path.chr(0).$value.chr(0))===false ){
                        trigger_error("Can't write to resource set#2",E_USER_NOTICE);
                        return(false);  // error sending value
                }

                $data='';$tmp_ret='';
                $start=microtime(1);    // start timeout counter
                while(1){
                        $tmp_ret=$this->get_msg(24);
                        if ($tmp_ret===false){
                                trigger_error("Can't read from resource set#1",E_USER_NOTICE);
                                $this->disconnect();
                                return(false);  // error reading return
                        }
                        if (strlen($tmp_ret)>0) $start=microtime(1);
                        $data.=$tmp_ret;unset($tmp_ret);
                        if(strlen($data)>=24 || (microtime(1)-$start)>$this->timeout)   // timed out or got content
                                break;
                }
                $ret=$this->unpack($data);      // unpack data return
                if (count($ret)<6){
                        trigger_error("Error unpacking data set#1 [".strlen($data)."] ".$data,E_USER_NOTICE);
                        $this->disconnect();
                        return(false);          // return false on error
                }
                if (!isset($ret[2]))
                        $ret[2]=1;              // be sure that we will work with error_reporting(E_ALL)
                if ($ret[2]!=0)
                        $ret=false;             // return false on error
                $this->disconnect();            // disconnect from server
                if ($ret!==false)
                        return(true);           // very fine :)
                return($ret);                   // :( 
        }
        private function unpack($data){ // unpack returned contents (24 bytes data)
                $unpack=$this->unpack_ntohl($data);
                return($unpack);
                // version= 0, payload_len=1, ret_value=2, format_flags=3, data_len=4, offset=5
        }
        private function pack($function,$payload_len,$data_len){        // pack msg information (24 bytes)
                $pack=  $this->pack_htonl(0).
                        $this->pack_htonl($payload_len).
                        $this->pack_htonl($function).
                        $this->pack_htonl(258).
                        $this->pack_htonl($data_len).
                        $this->pack_htonl(0);
                return($pack);
        }
}
/*
explanation:


EVERY FUNCTION TRIGGER AN ERROR! you should use @$ow->function to don't handle errors


$ow=new OWNet($host,$timeout=5,$use_swig_dir=true);
    in top of file we have some constants: (line 58)
        define('OWNET_DEFAULT_HOST'    ,'127.0.0.1');
        define('OWNET_DEFAULT_PORT'    ,4304);   
        i don't know what's the default OW port, i'm using today 4304 :)
    for OWNet($host) we can use this types of host value:
    "scheme://host:port" "scheme://host" "anything that can't be parsed with parseurl"
    if port isn't set we get default port, if host isn't set we get default host
    if scheme == "stream" or scheme == "ow-stream"
       we will prefer to use PHP stream_socket_client functions
       else if function socket_create exists we will use socket_connect() with sockets we can set TCP_NODELAY SO_REUSEADDR SO_OOBINLINE(we use it?)  SO_RCVBUF SO_SNDBUF
$ow->setHost($host)
    is the same thing that new OWNet($host)
$ow->getHost()
    get host URI
       owstream://host:port if using stream_socket_client
       ow://host:port if using sockets
$ow->setTimeout($timeout=5)
    set read timeout, default 5
$ow->getTimeout()
    get read timeout
$ow->set($path,$value='')
    set an value to path
    true on success
    false on error or if file is readonly
$ow->read($path,$parse_value=true)
        return value if parse_value return an php parsed value, if not return an string
$ow->dir($path)
        return an dir (see setUserSwigDir and OWNet constructor)
$ow->presence($path)
        get presence

$ow->get($path='/',$get_type=OWNET_MSG_READ_ANY,$return_full_info_array=false,$parse_php_type=true)
    on error return NULL
    get_type can be:
       OWNET_MSG_READ (execute OWNET_MSG_READ and if don't find execute OWNET_MSG_DIR)
       OWNET_MSG_READ *default
          read an file from owserver
       OWNET_MSG_DIR
          read an dir from owserver
       OWNET_MSG_PRESENCE
          read presence from owserver (return false or true)
    return_full_info_array
       if true return an array with everything that was received from owserver more data_php and unit if file is an temperature value
    parse_php_type
       try to check what type is an file (temperature, boolean, string, number (double or integer))

$ow->setUseSwigDir($use)        // use swig dir style (dir,dir,dir) and not php array (array(dir,dir,dir))
$ow->getUseSwigDir()            // get if using swig dir style


END :)

examples:

$ow=new OWNet("tcp://172.16.1.100:4304");
var_dump($ow->get("/",OWNET_MSG_DIR,true));
var_dump($ow->get("/10.E8C1C9000800/temperature",OWNET_MSG_READ,true));
var_dump($ow->get("/10.E8C1C9000800",OWNET_MSG_PRESENCE,true));
var_dump($ow->get("/WRONG VALUE",OWNET_MSG_PRESENCE,true));

var_dump($ow->get("/",OWNET_MSG_DIR,false));
var_dump($ow->get("/10.E8C1C9000800/temperature",OWNET_MSG_READ,false));
var_dump($ow->get("/10.E8C1C9000800",OWNET_MSG_PRESENCE,false));
var_dump($ow->get("/WRONG VALUE",OWNET_MSG_PRESENCE,false));


return:

array(9) {
  [0]=>  array(9) {
    [0]=>    int(0)
    [1]=>    int(42)
    [2]=>    int(0)
    [3]=>    int(258)
    [4]=>    int(5)
    [5]=>    int(0)
    ["data"]=>    string(42) "bus.0athaðãaPÜa»a0"
    ["data_len"]=>    int(42)
    ["data_php"]=>    string(5) "bus.0"
  }
  [1]=>  array(9) {
    [0]=>    int(0)
    [1]=>    int(42)
    [2]=>    int(0)
    [3]=>    int(258)
    [4]=>    int(8)
    [5]=>    int(0)
    ["data"]=>    string(42) "settings0"
    ["data_len"]=>    int(42)
    ["data_php"]=>    string(8) "settings"
  }
  [2]=>  array(9) {
    [0]=>    int(0)
    [1]=>    int(42)
    [2]=>    int(0)
    [3]=>    int(258)
    [4]=>    int(6)
    [5]=>    int(0)
    ["data"]=>    string(42) "system0"
    ["data_len"]=>    int(42)
    ["data_php"]=>    string(6) "system"
  }
  [3]=>
  array(9) {
    [0]=>    int(0)
    [1]=>    int(42)
    [2]=>    int(0)
    [3]=>    int(258)
    [4]=>    int(10)
    [5]=>    int(0)
    ["data"]=>    string(42) "statistics0"
    ["data_len"]=>    int(42)
    ["data_php"]=>    string(10) "statistics"
  }
  [4]=>
  array(9) {
    [0]=>    int(0)
    [1]=>    int(42)
    [2]=>    int(0)
    [3]=>    int(258)
    [4]=>    int(16)
    [5]=>    int(0)
    ["data"]=>    string(42) "/10.E8C1C9000800P×aàÑa »a0"
    ["data_len"]=>    int(42)
    ["data_php"]=>    string(16) "/10.E8C1C9000800"
  }
  [5]=>
  array(9) {
    [0]=>    int(0)
    [1]=>    int(42)
    [2]=>    int(0)
    [3]=>    int(258)
    [4]=>    int(16)
    [5]=>    int(0)
    ["data"]=>    string(42) "/10.54FDED000800P×aàÑa »a0"
    ["data_len"]=>    int(42)
    ["data_php"]=>    string(16) "/10.54FDED000800"
  }
  [6]=>
  array(9) {
    [0]=>    int(0)
    [1]=>    int(42)
    [2]=>    int(0)
    [3]=>    int(258)
    [4]=>    int(16)
    [5]=>    int(0)
    ["data"]=>    string(42) "/10.6F7EC9000800P×aàÑa »a0"
    ["data_len"]=>    int(42)
    ["data_php"]=>    string(16) "/10.6F7EC9000800"
  }
  [7]=>
  array(9) {
    [0]=>    int(0)
    [1]=>    int(42)
    [2]=>    int(0)
    [3]=>    int(258)
    [4]=>    int(16)
    [5]=>    int(0)
    ["data"]=>    string(42) "/28.924FE0000000P×aàÑa »a0"
    ["data_len"]=>    int(42)
    ["data_php"]=>    string(16) "/28.924FE0000000"
  }
  [8]=>
  array(9) {
    [0]=>    int(0)
    [1]=>    int(42)
    [2]=>    int(0)
    [3]=>    int(258)
    [4]=>    int(16)
    [5]=>    int(0)
    ["data"]=>    string(42) "/28.5D59E0000000P×aàÑa »a0"
    ["data_len"]=>    int(42)
    ["data_php"]=>    string(16) "/28.5D59E0000000"
  }
}
array(10) {
  [0]=>  int(0)
  [1]=>  int(8192)
  [2]=>  int(12)
  [3]=>  int(0)
  [4]=>  int(12)
  [5]=>  int(0)
  ["data"]=>  string(12) "      28.625"
  ["data_len"]=>  int(12)
  ["data_php"]=>  float(28.625)
  ["unit"]=>  string(1) "C"
}
bool(true)
bool(false)
array(9) {
  [0]=>  string(5) "bus.0"
  [1]=>  string(8) "settings"
  [2]=>  string(6) "system"
  [3]=>  string(10) "statistics"
  [4]=>  string(16) "/10.E8C1C9000800"
  [5]=>  string(16) "/10.54FDED000800"
  [6]=>  string(16) "/10.6F7EC9000800"
  [7]=>  string(16) "/28.924FE0000000"
  [8]=>  string(16) "/28.5D59E0000000"
}
float(28.625)
bool(true)

*/
?>
