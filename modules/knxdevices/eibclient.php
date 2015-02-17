<?php
/*
    EIBD client library
    Copyright (C) 2005-2008 Martin Koegler <mkoegler@auto.tuwien.ac.at>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    In addition to the permissions in the GNU General Public License, 
    you may link the compiled version of this file into combinations
    with other programs, and distribute those combinations without any 
    restriction coming from the use of this file. (The General Public 
    License restrictions do apply in other respects; for example, they 
    cover modification of the file, and distribution when not linked into 
    a combine executable.)

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
class EIBBuffer
{
  public $buffer;
  function _construct ($buf = "")
  {
    $this->buffer = $buf;
  }

}

class EIBAddr
{
  public $addr;
  function _construct ($a = 0)
  {
    $this->addr = $a;
  }

}

class EIBInt8
{
  public $data;
  function _construct ($val = 0)
  {
    $this->val = $a;
  }
}

class EIBInt16
{
  public $data;
  function _construct ($val = 0)
  {
    $this->val = $a;
  }

}


class EIBConnection
{


  const IMG_UNKNOWN_ERROR = 0;
  const IMG_UNRECOG_FORMAT = 1;
  const IMG_INVALID_FORMAT = 2;
  const IMG_NO_BCUTYPE = 3;
  const IMG_UNKNOWN_BCUTYPE = 4;
  const IMG_NO_CODE = 5;
  const IMG_NO_SIZE = 6;
  const IMG_LODATA_OVERFLOW = 7;
  const IMG_HIDATA_OVERFLOW = 8;
  const IMG_TEXT_OVERFLOW = 9;
  const IMG_NO_ADDRESS = 10;
  const IMG_WRONG_SIZE = 11;
  const IMG_IMAGE_LOADABLE = 12;
  const IMG_NO_DEVICE_CONNECTION = 13;
  const IMG_MASK_READ_FAILED = 14;
  const IMG_WRONG_MASK_VERSION = 15;
  const IMG_CLEAR_ERROR = 16;
  const IMG_RESET_ADDR_TAB = 17;
  const IMG_LOAD_HEADER = 18;
  const IMG_LOAD_MAIN = 19;
  const IMG_ZERO_RAM = 20;
  const IMG_FINALIZE_ADDR_TAB = 21;
  const IMG_PREPARE_RUN = 22;
  const IMG_RESTART = 23;
  const IMG_LOADED = 24;
  const IMG_NO_START = 25;
  const IMG_WRONG_ADDRTAB = 26;
  const IMG_ADDRTAB_OVERFLOW = 27;
  const IMG_OVERLAP_ASSOCTAB = 28;
  const IMG_OVERLAP_TEXT = 29;
  const IMG_NEGATIV_TEXT_SIZE = 30;
  const IMG_OVERLAP_PARAM = 31;
  const IMG_OVERLAP_EEPROM = 32;
  const IMG_OBJTAB_OVERFLOW = 33;
  const IMG_WRONG_LOADCTL = 34;
  const IMG_UNLOAD_ADDR = 35;
  const IMG_UNLOAD_ASSOC = 36;
  const IMG_UNLOAD_PROG = 37;
  const IMG_LOAD_ADDR = 38;
  const IMG_WRITE_ADDR = 39;
  const IMG_SET_ADDR = 40;
  const IMG_FINISH_ADDR = 41;
  const IMG_LOAD_ASSOC = 42;
  const IMG_WRITE_ASSOC = 43;
  const IMG_SET_ASSOC = 44;
  const IMG_FINISH_ASSOC = 45;
  const IMG_LOAD_PROG = 46;
  const IMG_ALLOC_LORAM = 47;
  const IMG_ALLOC_HIRAM = 48;
  const IMG_ALLOC_INIT = 49;
  const IMG_ALLOC_RO = 50;
  const IMG_ALLOC_EEPROM = 51;
  const IMG_ALLOC_PARAM = 52;
  const IMG_SET_PROG = 53;
  const IMG_SET_TASK_PTR = 54;
  const IMG_SET_OBJ = 55;
  const IMG_SET_TASK2 = 56;
  const IMG_FINISH_PROC = 57;
  const IMG_WRONG_CHECKLIM = 58;
  const IMG_INVALID_KEY = 59;
  const IMG_AUTHORIZATION_FAILED = 60;
  const IMG_KEY_WRITE = 61;
  const EINVAL = 1;
  const ECONNRESET = 2;
  const EBUSY = 3;
  const EADDRINUSE = 4;
  const ETIMEDOUT = 5;
  const EADDRNOTAVAIL = 6;
  const EIO = 7;
  const EPERM = 8;
  const ENOENT = 9;
  const ENODEV = 10;
  const EBADF = 11;

    private $errno = 0;

  public function getLastError ()
  {
    return $this->errno;
  }
  private $buf;
  private $ptr1;
  private $ptr2;
  private $ptr3;
  private $ptr4;
  private $ptr5;
  private $ptr6;
  private $sendlen;

  private $complete;

  public function EIBComplete ()
  {
    $name = $this->complete;
    return $this->$name ();
  }
  private $data;
  private $head;
  private $readlen;
  private $datalen;
  private $socket;

  function __construct ($host, $port = 6720)
  {
    $this->readlen = 0;
    $this->socket = stream_socket_client ("tcp://".$host.":".$port);
    if ($this->socket === FALSE)
      throw new Exception ("connect failed");
  }

  private function _EIB_SendRequest ($data)
  {
    if ($this->socket === FALSE)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    if (strlen ($data) > 0xffff || strlen ($data) < 2)
      {
        $this->errno = self::EINVAL;
        return -1;
      }
    $len = pack ("n", strlen ($data));
    if (fwrite ($this->socket, $len.$data) != strlen ($data) + 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }

  private function _EIB_CheckRequest ($block)
  {
    if ($this->socket === FALSE || feof ($this->socket))
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    if ($this->readlen == 0)
      {
        $this->head = array (" ", " ");
        $this->data = array ();
      }

    if ($this->readlen < 2)
      {
        stream_set_blocking ($this->socket, $block ? 1 : 0);
        $read = fread ($this->socket, 2 - $this->readlen);
        if ($read === FALSE)
          {
            $this->errno = self::ECONNRESET;
            return -1;
          }
        for ($i = 0; $i < strlen ($read); $i++)
          $this->head[$this->readlen++] = substr ($read, $i, 1);
      }
    if ($this->readlen < 2)
      return 0;
    $this->datalen = EIBConnection::upack (implode ("", $this->head), "n");
    if (feof ($this->socket))
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    if ($this->readlen < $this->datalen + 2)
      {
        stream_set_blocking ($this->socket, $block ? 1 : 0);
        $read = fread ($this->socket, $this->datalen + 2 - $this->readlen);
        if ($read === FALSE)
          {
            $this->errno = self::ECONNRESET;
            return -1;
          }
        for ($i = 0; $i < strlen ($read); $i++)
          $this->data[($this->readlen++) - 2] = substr ($read, $i, 1);
      }
    return 0;
  }

  private function _EIB_GetRequest ()
  {
    do
      {
        if ($this->_EIB_CheckRequest (true) == -1)
          return -1;
      }
    while ($this->readlen < 2
           || ($this->readlen >= 2 && $this->readlen < $this->datalen + 2));
    $this->data = implode ("", $this->data);
    $this->readlen = 0;
    return 0;
  }

  public function EIB_Poll_Complete ()
  {
    if ($this->_EIB_CheckRequest (false) == -1)
      return -1;
    if ($this->readlen < 2
        || ($this->readlen >= 2 && $this->readlen < $this->datalen + 2))
      return 0;
    return 1;
  }

  public function EIBClose ()
  {
    if ($socket === FALSE)
      {
        $this->errno = self::EBADF;
        return -1;
      }
    fclose ($this->socket);
    $this->socket = FALSE;
  }

  public function EIBClose_sync ()
  {
    $this->EIBReset ();
    return $this->EIBClose ();
  }




  private function EIBGetAPDU_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0025
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    $this->buf->buffer = substr ($this->data, 2);
    return strlen ($this->buf->buffer);
  }





  public function EIBGetAPDU_async (EIBBuffer $buf)
  {
    $head = array ();
    for ($i = 0; $i < 2; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $this->buf = $buf;
    $this->complete = "EIBGetAPDU"."_complete";
    return 0;
  }
  public function EIBGetAPDU (EIBBuffer $buf)
  {
    if ($this->EIBGetAPDU_async ($buf) == -1)
      return -1;
    return $this->EIBComplete ();
  }






  private function EIBGetAPDU_Src_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0025
        || strlen ($this->data) < 4)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    if ($this->ptr5 != null)
      $this->ptr5->addr =
        (EIBConnection::upack (substr (substr ($this->data, 2), 0, 2), "n"));
    $this->buf->buffer = substr ($this->data, 4);
    return strlen ($this->buf->buffer);
  }






  public function EIBGetAPDU_Src_async (EIBBuffer $buf, EIBAddr $src)
  {
    $head = array ();
    for ($i = 0; $i < 2; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $this->buf = $buf;
    $this->ptr5 = $src;
    $this->complete = "EIBGetAPDU_Src"."_complete";
    return 0;
  }
  public function EIBGetAPDU_Src (EIBBuffer $buf, EIBAddr $src)
  {
    if ($this->EIBGetAPDU_Src_async ($buf, $src) == -1)
      return -1;
    return $this->EIBComplete ();
  }







  private function EIBGetBusmonitorPacket_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0014
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    $this->buf->buffer = substr ($this->data, 2);
    return strlen ($this->buf->buffer);
  }





  public function EIBGetBusmonitorPacket_async (EIBBuffer $buf)
  {
    $head = array ();
    for ($i = 0; $i < 2; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $this->buf = $buf;
    $this->complete = "EIBGetBusmonitorPacket"."_complete";
    return 0;
  }
  public function EIBGetBusmonitorPacket (EIBBuffer $buf)
  {
    if ($this->EIBGetBusmonitorPacket_async ($buf) == -1)
      return -1;
    return $this->EIBComplete ();
  }






  private function EIBGetGroup_Src_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0027
        || strlen ($this->data) < 6)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    if ($this->ptr5 != null)
      $this->ptr5->addr =
        (EIBConnection::upack (substr (substr ($this->data, 2), 0, 2), "n"));
    if ($this->ptr6 != null)
      $this->ptr6->addr =
        (EIBConnection::upack (substr (substr ($this->data, 4), 0, 2), "n"));
    $this->buf->buffer = substr ($this->data, 6);
    return strlen ($this->buf->buffer);
  }







  public function EIBGetGroup_Src_async (EIBBuffer $buf, EIBAddr $src,
                                         EIBAddr $dest)
  {
    $head = array ();
    for ($i = 0; $i < 2; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $this->buf = $buf;
    $this->ptr5 = $src;
    $this->ptr6 = $dest;
    $this->complete = "EIBGetGroup_Src"."_complete";
    return 0;
  }
  public function EIBGetGroup_Src (EIBBuffer $buf, EIBAddr $src,
                                   EIBAddr $dest)
  {
    if ($this->EIBGetGroup_Src_async ($buf, $src, $dest) == -1)
      return -1;
    return $this->EIBComplete ();
  }








  private function EIBGetTPDU_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0025
        || strlen ($this->data) < 4)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    if ($this->ptr5 != null)
      $this->ptr5->addr =
        (EIBConnection::upack (substr (substr ($this->data, 2), 0, 2), "n"));
    $this->buf->buffer = substr ($this->data, 4);
    return strlen ($this->buf->buffer);
  }






  public function EIBGetTPDU_async (EIBBuffer $buf, EIBAddr $src)
  {
    $head = array ();
    for ($i = 0; $i < 2; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $this->buf = $buf;
    $this->ptr5 = $src;
    $this->complete = "EIBGetTPDU"."_complete";
    return 0;
  }
  public function EIBGetTPDU (EIBBuffer $buf, EIBAddr $src)
  {
    if ($this->EIBGetTPDU_async ($buf, $src) == -1)
      return -1;
    return $this->EIBComplete ();
  }







  private function EIB_Cache_Clear_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0072
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }





  public function EIB_Cache_Clear_async ()
  {
    $head = array ();
    for ($i = 0; $i < 2; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[0] = EIBConnection::packb ((0x0072 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0072) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_Cache_Clear"."_complete";
    return 0;
  }
  public function EIB_Cache_Clear ()
  {
    if ($this->EIB_Cache_Clear_async () == -1)
      return -1;
    return $this->EIBComplete ();
  }






  private function EIB_Cache_Disable_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0071
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }





  public function EIB_Cache_Disable_async ()
  {
    $head = array ();
    for ($i = 0; $i < 2; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[0] = EIBConnection::packb ((0x0071 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0071) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_Cache_Disable"."_complete";
    return 0;
  }
  public function EIB_Cache_Disable ()
  {
    if ($this->EIB_Cache_Disable_async () == -1)
      return -1;
    return $this->EIBComplete ();
  }






  private function EIB_Cache_Enable_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) == 0x0001)
      {
        $this->errno = self::EBUSY;
        return -1;
      }
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0070
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }






  public function EIB_Cache_Enable_async ()
  {
    $head = array ();
    for ($i = 0; $i < 2; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[0] = EIBConnection::packb ((0x0070 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0070) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_Cache_Enable"."_complete";
    return 0;
  }
  public function EIB_Cache_Enable ()
  {
    if ($this->EIB_Cache_Enable_async () == -1)
      return -1;
    return $this->EIBComplete ();
  }






  private function EIB_Cache_Read_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0075
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    if ((EIBConnection::
         upack (substr (substr ($this->data, 4), 0, 2), "n")) == 0)
      {
        $this->errno = self::ENODEV;
        return -1;
      }
    if (strlen ($this->data) <= 6)
      {
        $this->errno = self::ENOENT;
        return -1;
      }
    if ($this->ptr5 != null)
      $this->ptr5->addr =
        (EIBConnection::upack (substr (substr ($this->data, 2), 0, 2), "n"));
    $this->buf->buffer = substr ($this->data, 6);
    return strlen ($this->buf->buffer);
  }
  public function EIB_Cache_Read_async ($dst, EIBAddr $src, EIBBuffer $buf)
  {
    $head = array ();
    for ($i = 0; $i < 4; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $this->buf = $buf;
    $this->ptr5 = $src;
    $ibuf[2] = EIBConnection::packb (($dst >> 8) & 0xff);
    $ibuf[2 + 1] = EIBConnection::packb (($dst) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0075 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0075) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_Cache_Read"."_complete";
    return 0;
  }
  public function EIB_Cache_Read ($dst, EIBAddr $src, EIBBuffer $buf)
  {
    if ($this->EIB_Cache_Read_async ($dst, $src, $buf) == -1)
      return -1;
    return $this->EIBComplete ();
  }

  private function EIB_Cache_Read_Sync_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0074
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    if ((EIBConnection::
         upack (substr (substr ($this->data, 4), 0, 2), "n")) == 0)
      {
        $this->errno = self::ENODEV;
        return -1;
      }
    if (strlen ($this->data) <= 6)
      {
        $this->errno = self::ENOENT;
        return -1;
      }
    if ($this->ptr5 != null)
      $this->ptr5->addr =
        (EIBConnection::upack (substr (substr ($this->data, 2), 0, 2), "n"));
    $this->buf->buffer = substr ($this->data, 6);
    return strlen ($this->buf->buffer);
  }
  public function EIB_Cache_Read_Sync_async ($dst, EIBAddr $src,
                                             EIBBuffer $buf, $age)
  {
    $head = array ();
    for ($i = 0; $i < 6; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $this->buf = $buf;
    $this->ptr5 = $src;
    $ibuf[2] = EIBConnection::packb (($dst >> 8) & 0xff);
    $ibuf[2 + 1] = EIBConnection::packb (($dst) & 0xff);
    $ibuf[4] = EIBConnection::packb (($age >> 8) & 0xff);
    $ibuf[4 + 1] = EIBConnection::packb (($age) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0074 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0074) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_Cache_Read_Sync"."_complete";
    return 0;
  }
  public function EIB_Cache_Read_Sync ($dst, EIBAddr $src, EIBBuffer $buf,
                                       $age)
  {
    if ($this->EIB_Cache_Read_Sync_async ($dst, $src, $buf, $age) == -1)
      return -1;
    return $this->EIBComplete ();
  }

  private function EIB_Cache_Remove_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0073
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }





  public function EIB_Cache_Remove_async ($dest)
  {
    $head = array ();
    for ($i = 0; $i < 4; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb (($dest >> 8) & 0xff);
    $ibuf[2 + 1] = EIBConnection::packb (($dest) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0073 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0073) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_Cache_Remove"."_complete";
    return 0;
  }
  public function EIB_Cache_Remove ($dest)
  {
    if ($this->EIB_Cache_Remove_async ($dest) == -1)
      return -1;
    return $this->EIBComplete ();
  }







  private function EIB_LoadImage_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0063
        || strlen ($this->data) < 4)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return (EIBConnection::
            upack (substr (substr ($this->data, 2), 0, 2), "n"));
  }





  public function EIB_LoadImage_async ($image)
  {
    $head = array ();
    for ($i = 0; $i < 2; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    if (strlen ($image) < 0)
      {
        $this->errno = self::EINVAL;
        return -1;
      }
    $this->sendlen = strlen ($image);
    for ($i = 0; $i < strlen ($image); $i++)
      $ibuf[] = substr ($image, $i, 1);
    $ibuf[0] = EIBConnection::packb ((0x0063 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0063) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_LoadImage"."_complete";
    return 0;
  }
  public function EIB_LoadImage ($image)
  {
    if ($this->EIB_LoadImage_async ($image) == -1)
      return -1;
    return $this->EIBComplete ();
  }







  private function EIB_MC_Authorize_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0057
        || strlen ($this->data) < 3)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return (EIBConnection::
            upack (substr (substr ($this->data, 2), 0, 1), "C"));
  }





  public function EIB_MC_Authorize_async ($key)
  {
    $head = array ();
    for ($i = 0; $i < 6; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    if (strlen ($key) != 4)
      {
        $this->errno = self::EINVAL;
        return -1;
      }
    for ($i = 0; $i < 4; $i++)
      $ibuf[2 + $i] = substr ($key, $i, 1);
    $ibuf[0] = EIBConnection::packb ((0x0057 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0057) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_MC_Authorize"."_complete";
    return 0;
  }
  public function EIB_MC_Authorize ($key)
  {
    if ($this->EIB_MC_Authorize_async ($key) == -1)
      return -1;
    return $this->EIBComplete ();
  }







  private function EIB_MC_Connect_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0050
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }





  public function EIB_MC_Connect_async ($dest)
  {
    $head = array ();
    for ($i = 0; $i < 4; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb (($dest >> 8) & 0xff);
    $ibuf[2 + 1] = EIBConnection::packb (($dest) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0050 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0050) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_MC_Connect"."_complete";
    return 0;
  }
  public function EIB_MC_Connect ($dest)
  {
    if ($this->EIB_MC_Connect_async ($dest) == -1)
      return -1;
    return $this->EIBComplete ();
  }







  private function EIB_MC_GetMaskVersion_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0059
        || strlen ($this->data) < 4)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return (EIBConnection::
            upack (substr (substr ($this->data, 2), 0, 2), "n"));
  }





  public function EIB_MC_GetMaskVersion_async ()
  {
    $head = array ();
    for ($i = 0; $i < 2; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[0] = EIBConnection::packb ((0x0059 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0059) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_MC_GetMaskVersion"."_complete";
    return 0;
  }
  public function EIB_MC_GetMaskVersion ()
  {
    if ($this->EIB_MC_GetMaskVersion_async () == -1)
      return -1;
    return $this->EIBComplete ();
  }






  private function EIB_MC_GetPEIType_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0055
        || strlen ($this->data) < 4)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return (EIBConnection::
            upack (substr (substr ($this->data, 2), 0, 2), "n"));
  }





  public function EIB_MC_GetPEIType_async ()
  {
    $head = array ();
    for ($i = 0; $i < 2; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[0] = EIBConnection::packb ((0x0055 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0055) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_MC_GetPEIType"."_complete";
    return 0;
  }
  public function EIB_MC_GetPEIType ()
  {
    if ($this->EIB_MC_GetPEIType_async () == -1)
      return -1;
    return $this->EIBComplete ();
  }






  private function EIB_MC_Progmode_Off_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0060
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }





  public function EIB_MC_Progmode_Off_async ()
  {
    $head = array ();
    for ($i = 0; $i < 3; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb ((0) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0060 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0060) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_MC_Progmode_Off"."_complete";
    return 0;
  }
  public function EIB_MC_Progmode_Off ()
  {
    if ($this->EIB_MC_Progmode_Off_async () == -1)
      return -1;
    return $this->EIBComplete ();
  }







  private function EIB_MC_Progmode_On_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0060
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }





  public function EIB_MC_Progmode_On_async ()
  {
    $head = array ();
    for ($i = 0; $i < 3; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb ((1) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0060 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0060) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_MC_Progmode_On"."_complete";
    return 0;
  }
  public function EIB_MC_Progmode_On ()
  {
    if ($this->EIB_MC_Progmode_On_async () == -1)
      return -1;
    return $this->EIBComplete ();
  }







  private function EIB_MC_Progmode_Status_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0060
        || strlen ($this->data) < 3)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return (EIBConnection::
            upack (substr (substr ($this->data, 2), 0, 1), "C"));
  }





  public function EIB_MC_Progmode_Status_async ()
  {
    $head = array ();
    for ($i = 0; $i < 3; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb ((3) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0060 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0060) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_MC_Progmode_Status"."_complete";
    return 0;
  }
  public function EIB_MC_Progmode_Status ()
  {
    if ($this->EIB_MC_Progmode_Status_async () == -1)
      return -1;
    return $this->EIBComplete ();
  }







  private function EIB_MC_Progmode_Toggle_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0060
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }





  public function EIB_MC_Progmode_Toggle_async ()
  {
    $head = array ();
    for ($i = 0; $i < 3; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb ((2) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0060 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0060) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_MC_Progmode_Toggle"."_complete";
    return 0;
  }
  public function EIB_MC_Progmode_Toggle ()
  {
    if ($this->EIB_MC_Progmode_Toggle_async () == -1)
      return -1;
    return $this->EIBComplete ();
  }







  private function EIB_MC_PropertyDesc_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0061
        || strlen ($this->data) < 6)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    if ($this->ptr2 != null)
      $this->ptr2->data =
        (EIBConnection::upack (substr (substr ($this->data, 2), 0, 1), "C"));
    if ($this->ptr4 != null)
      $this->ptr4->data =
        (EIBConnection::upack (substr (substr ($this->data, 3), 0, 2), "n"));
    if ($this->ptr3 != null)
      $this->ptr3->data =
        (EIBConnection::upack (substr (substr ($this->data, 5), 0, 1), "C"));
    return 0;
  }
  public function EIB_MC_PropertyDesc_async ($obj, $property, EIBInt8 $type,
                                             EIBInt16 $max_nr_of_elem,
                                             EIBInt8 $access)
  {
    $head = array ();
    for ($i = 0; $i < 4; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $this->ptr2 = $type;
    $this->ptr4 = $max_nr_of_elem;
    $this->ptr3 = $access;
    $ibuf[2] = EIBConnection::packb (($obj) & 0xff);
    $ibuf[3] = EIBConnection::packb (($property) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0061 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0061) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_MC_PropertyDesc"."_complete";
    return 0;
  }
  public function EIB_MC_PropertyDesc ($obj, $property, EIBInt8 $type,
                                       EIBInt16 $max_nr_of_elem,
                                       EIBInt8 $access)
  {
    if ($this->
        EIB_MC_PropertyDesc_async ($obj, $property, $type, $max_nr_of_elem,
                                   $access) == -1)
      return -1;
    return $this->EIBComplete ();
  }

  private function EIB_MC_PropertyRead_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0053
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    $this->buf->buffer = substr ($this->data, 2);
    return strlen ($this->buf->buffer);
  }





  public function EIB_MC_PropertyRead_async ($obj, $property, $start,
                                             $nr_of_elem, EIBBuffer $buf)
  {
    $head = array ();
    for ($i = 0; $i < 7; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $this->buf = $buf;
    $ibuf[2] = EIBConnection::packb (($obj) & 0xff);
    $ibuf[3] = EIBConnection::packb (($property) & 0xff);
    $ibuf[4] = EIBConnection::packb (($start >> 8) & 0xff);
    $ibuf[4 + 1] = EIBConnection::packb (($start) & 0xff);
    $ibuf[6] = EIBConnection::packb (($nr_of_elem) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0053 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0053) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_MC_PropertyRead"."_complete";
    return 0;
  }
  public function EIB_MC_PropertyRead ($obj, $property, $start, $nr_of_elem,
                                       EIBBuffer $buf)
  {
    if ($this->
        EIB_MC_PropertyRead_async ($obj, $property, $start, $nr_of_elem,
                                   $buf) == -1)
      return -1;
    return $this->EIBComplete ();
  }

  private function EIB_MC_PropertyScan_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0062
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    $this->buf->buffer = substr ($this->data, 2);
    return strlen ($this->buf->buffer);
  }





  public function EIB_MC_PropertyScan_async (EIBBuffer $buf)
  {
    $head = array ();
    for ($i = 0; $i < 2; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $this->buf = $buf;
    $ibuf[0] = EIBConnection::packb ((0x0062 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0062) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_MC_PropertyScan"."_complete";
    return 0;
  }
  public function EIB_MC_PropertyScan (EIBBuffer $buf)
  {
    if ($this->EIB_MC_PropertyScan_async ($buf) == -1)
      return -1;
    return $this->EIBComplete ();
  }







  private function EIB_MC_PropertyWrite_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0054
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    $this->buf->buffer = substr ($this->data, 2);
    return strlen ($this->buf->buffer);
  }





  public function EIB_MC_PropertyWrite_async ($obj, $property, $start,
                                              $nr_of_elem, $buf,
                                              EIBBuffer $res)
  {
    $head = array ();
    for ($i = 0; $i < 7; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb (($obj) & 0xff);
    $ibuf[3] = EIBConnection::packb (($property) & 0xff);
    $ibuf[4] = EIBConnection::packb (($start >> 8) & 0xff);
    $ibuf[4 + 1] = EIBConnection::packb (($start) & 0xff);
    $ibuf[6] = EIBConnection::packb (($nr_of_elem) & 0xff);
    if (strlen ($buf) < 0)
      {
        $this->errno = self::EINVAL;
        return -1;
      }
    $this->sendlen = strlen ($buf);
    for ($i = 0; $i < strlen ($buf); $i++)
      $ibuf[] = substr ($buf, $i, 1);
    $this->buf = $res;
    $ibuf[0] = EIBConnection::packb ((0x0054 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0054) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_MC_PropertyWrite"."_complete";
    return 0;
  }
  public function EIB_MC_PropertyWrite ($obj, $property, $start, $nr_of_elem,
                                        $buf, EIBBuffer $res)
  {
    if ($this->
        EIB_MC_PropertyWrite_async ($obj, $property, $start, $nr_of_elem,
                                    $buf, $res) == -1)
      return -1;
    return $this->EIBComplete ();
  }

  private function EIB_MC_ReadADC_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0056
        || strlen ($this->data) < 4)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    if ($this->ptr1 != null)
      $this->ptr1->data =
        (EIBConnection::upack (substr (substr ($this->data, 2), 0, 2), "n"));
    return 0;
  }






  public function EIB_MC_ReadADC_async ($channel, $count, EIBInt16 $val)
  {
    $head = array ();
    for ($i = 0; $i < 4; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $this->ptr1 = $val;
    $ibuf[2] = EIBConnection::packb (($channel) & 0xff);
    $ibuf[3] = EIBConnection::packb (($count) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0056 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0056) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_MC_ReadADC"."_complete";
    return 0;
  }
  public function EIB_MC_ReadADC ($channel, $count, EIBInt16 $val)
  {
    if ($this->EIB_MC_ReadADC_async ($channel, $count, $val) == -1)
      return -1;
    return $this->EIBComplete ();
  }

  private function EIB_MC_Read_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0051
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    $this->buf->buffer = substr ($this->data, 2);
    return strlen ($this->buf->buffer);
  }





  public function EIB_MC_Read_async ($addr, $buf_len, EIBBuffer $buf)
  {
    $head = array ();
    for ($i = 0; $i < 6; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $this->buf = $buf;
    $ibuf[2] = EIBConnection::packb (($addr >> 8) & 0xff);
    $ibuf[2 + 1] = EIBConnection::packb (($addr) & 0xff);
    $ibuf[4] = EIBConnection::packb ((PAR (($buf_len)) >> 8) & 0xff);
    $ibuf[4 + 1] = EIBConnection::packb ((PAR (($buf_len))) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0051 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0051) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_MC_Read"."_complete";
    return 0;
  }
  public function EIB_MC_Read ($addr, $buf_len, EIBBuffer $buf)
  {
    if ($this->EIB_MC_Read_async ($addr, $buf_len, $name) == -1)
      return -1;
    return $this->EIBComplete ();
  }

  private function EIB_MC_Restart_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x005a
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }





  public function EIB_MC_Restart_async ()
  {
    $head = array ();
    for ($i = 0; $i < 2; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[0] = EIBConnection::packb ((0x005a >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x005a) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_MC_Restart"."_complete";
    return 0;
  }
  public function EIB_MC_Restart ()
  {
    if ($this->EIB_MC_Restart_async () == -1)
      return -1;
    return $this->EIBComplete ();
  }






  private function EIB_MC_SetKey_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) == 0x0002)
      {
        $this->errno = self::EPERM;
        return -1;
      }
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0058
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }







  public function EIB_MC_SetKey_async ($key, $level)
  {
    $head = array ();
    for ($i = 0; $i < 7; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    if (strlen ($key) != 4)
      {
        $this->errno = self::EINVAL;
        return -1;
      }
    for ($i = 0; $i < 4; $i++)
      $ibuf[2 + $i] = substr ($key, $i, 1);
    $ibuf[6] = EIBConnection::packb (($level) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0058 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0058) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_MC_SetKey"."_complete";
    return 0;
  }
  public function EIB_MC_SetKey ($key, $level)
  {
    if ($this->EIB_MC_SetKey_async ($key, $level) == -1)
      return -1;
    return $this->EIBComplete ();
  }








  private function EIB_MC_Write_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) == 0x0044)
      {
        $this->errno = self::EIO;
        return -1;
      }
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0052
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return $this->sendlen;
  }






  public function EIB_MC_Write_async ($addr, $buf)
  {
    $head = array ();
    for ($i = 0; $i < 6; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb (($addr >> 8) & 0xff);
    $ibuf[2 + 1] = EIBConnection::packb (($addr) & 0xff);
    $ibuf[4] = EIBConnection::packb ((PAR ((strlen ($buf))) >> 8) & 0xff);
    $ibuf[4 + 1] = EIBConnection::packb ((PAR ((strlen ($buf)))) & 0xff);
    if (strlen ($buf) < 0)
      {
        $this->errno = self::EINVAL;
        return -1;
      }
    $this->sendlen = strlen ($buf);
    for ($i = 0; $i < strlen ($buf); $i++)
      $ibuf[] = substr ($buf, $i, 1);
    $ibuf[0] = EIBConnection::packb ((0x0052 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0052) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_MC_Write"."_complete";
    return 0;
  }
  public function EIB_MC_Write ($addr, $buf)
  {
    if ($this->EIB_MC_Write_async ($addr, $buf) == -1)
      return -1;
    return $this->EIBComplete ();
  }

  private function EIB_MC_Write_Plain_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x005b
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return $this->sendlen;
  }





  public function EIB_MC_Write_Plain_async ($addr, $buf)
  {
    $head = array ();
    for ($i = 0; $i < 6; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb (($addr >> 8) & 0xff);
    $ibuf[2 + 1] = EIBConnection::packb (($addr) & 0xff);
    $ibuf[4] = EIBConnection::packb ((PAR ((strlen ($buf))) >> 8) & 0xff);
    $ibuf[4 + 1] = EIBConnection::packb ((PAR ((strlen ($buf)))) & 0xff);
    if (strlen ($buf) < 0)
      {
        $this->errno = self::EINVAL;
        return -1;
      }
    $this->sendlen = strlen ($buf);
    for ($i = 0; $i < strlen ($buf); $i++)
      $ibuf[] = substr ($buf, $i, 1);
    $ibuf[0] = EIBConnection::packb ((0x005b >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x005b) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_MC_Write_Plain"."_complete";
    return 0;
  }
  public function EIB_MC_Write_Plain ($addr, $buf)
  {
    if ($this->EIB_MC_Write_Plain_async ($addr, $buf) == -1)
      return -1;
    return $this->EIBComplete ();
  }

  private function EIB_M_GetMaskVersion_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0031
        || strlen ($this->data) < 4)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return (EIBConnection::
            upack (substr (substr ($this->data, 2), 0, 2), "n"));
  }





  public function EIB_M_GetMaskVersion_async ($dest)
  {
    $head = array ();
    for ($i = 0; $i < 4; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb (($dest >> 8) & 0xff);
    $ibuf[2 + 1] = EIBConnection::packb (($dest) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0031 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0031) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_M_GetMaskVersion"."_complete";
    return 0;
  }
  public function EIB_M_GetMaskVersion ($dest)
  {
    if ($this->EIB_M_GetMaskVersion_async ($dest) == -1)
      return -1;
    return $this->EIBComplete ();
  }







  private function EIB_M_Progmode_Off_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0030
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }





  public function EIB_M_Progmode_Off_async ($dest)
  {
    $head = array ();
    for ($i = 0; $i < 5; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb (($dest >> 8) & 0xff);
    $ibuf[2 + 1] = EIBConnection::packb (($dest) & 0xff);
    $ibuf[4] = EIBConnection::packb ((0) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0030 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0030) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_M_Progmode_Off"."_complete";
    return 0;
  }
  public function EIB_M_Progmode_Off ($dest)
  {
    if ($this->EIB_M_Progmode_Off_async ($dest) == -1)
      return -1;
    return $this->EIBComplete ();
  }








  private function EIB_M_Progmode_On_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0030
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }





  public function EIB_M_Progmode_On_async ($dest)
  {
    $head = array ();
    for ($i = 0; $i < 5; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb (($dest >> 8) & 0xff);
    $ibuf[2 + 1] = EIBConnection::packb (($dest) & 0xff);
    $ibuf[4] = EIBConnection::packb ((1) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0030 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0030) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_M_Progmode_On"."_complete";
    return 0;
  }
  public function EIB_M_Progmode_On ($dest)
  {
    if ($this->EIB_M_Progmode_On_async ($dest) == -1)
      return -1;
    return $this->EIBComplete ();
  }








  private function EIB_M_Progmode_Status_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0030
        || strlen ($this->data) < 3)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return (EIBConnection::
            upack (substr (substr ($this->data, 2), 0, 1), "C"));
  }





  public function EIB_M_Progmode_Status_async ($dest)
  {
    $head = array ();
    for ($i = 0; $i < 5; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb (($dest >> 8) & 0xff);
    $ibuf[2 + 1] = EIBConnection::packb (($dest) & 0xff);
    $ibuf[4] = EIBConnection::packb ((3) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0030 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0030) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_M_Progmode_Status"."_complete";
    return 0;
  }
  public function EIB_M_Progmode_Status ($dest)
  {
    if ($this->EIB_M_Progmode_Status_async ($dest) == -1)
      return -1;
    return $this->EIBComplete ();
  }








  private function EIB_M_Progmode_Toggle_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0030
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }





  public function EIB_M_Progmode_Toggle_async ($dest)
  {
    $head = array ();
    for ($i = 0; $i < 5; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb (($dest >> 8) & 0xff);
    $ibuf[2 + 1] = EIBConnection::packb (($dest) & 0xff);
    $ibuf[4] = EIBConnection::packb ((2) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0030 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0030) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_M_Progmode_Toggle"."_complete";
    return 0;
  }
  public function EIB_M_Progmode_Toggle ($dest)
  {
    if ($this->EIB_M_Progmode_Toggle_async ($dest) == -1)
      return -1;
    return $this->EIBComplete ();
  }








  private function EIB_M_ReadIndividualAddresses_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0032
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    $this->buf->buffer = substr ($this->data, 2);
    return strlen ($this->buf->buffer);
  }





  public function EIB_M_ReadIndividualAddresses_async (EIBBuffer $buf)
  {
    $head = array ();
    for ($i = 0; $i < 2; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $this->buf = $buf;
    $ibuf[0] = EIBConnection::packb ((0x0032 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0032) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_M_ReadIndividualAddresses"."_complete";
    return 0;
  }
  public function EIB_M_ReadIndividualAddresses (EIBBuffer $buf)
  {
    if ($this->EIB_M_ReadIndividualAddresses_async ($buf) == -1)
      return -1;
    return $this->EIBComplete ();
  }







  private function EIB_M_WriteIndividualAddress_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) == 0x0041)
      {
        $this->errno = self::EADDRINUSE;
        return -1;
      }
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) == 0x0043)
      {
        $this->errno = self::ETIMEDOUT;
        return -1;
      }
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) == 0x0042)
      {
        $this->errno = self::EADDRNOTAVAIL;
        return -1;
      }
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0040
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }
  public function EIB_M_WriteIndividualAddress_async ($dest)
  {
    $head = array ();
    for ($i = 0; $i < 4; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb (($dest >> 8) & 0xff);
    $ibuf[2 + 1] = EIBConnection::packb (($dest) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0040 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0040) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIB_M_WriteIndividualAddress"."_complete";
    return 0;
  }
  public function EIB_M_WriteIndividualAddress ($dest)
  {
    if ($this->EIB_M_WriteIndividualAddress_async ($dest) == -1)
      return -1;
    return $this->EIBComplete ();
  }







  private function EIBOpenBusmonitor_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) == 0x0001)
      {
        $this->errno = self::EBUSY;
        return -1;
      }
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0010
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }






  public function EIBOpenBusmonitor_async ()
  {
    $head = array ();
    for ($i = 0; $i < 2; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[0] = EIBConnection::packb ((0x0010 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0010) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIBOpenBusmonitor"."_complete";
    return 0;
  }
  public function EIBOpenBusmonitor ()
  {
    if ($this->EIBOpenBusmonitor_async () == -1)
      return -1;
    return $this->EIBComplete ();
  }






  private function EIBOpenBusmonitorText_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) == 0x0001)
      {
        $this->errno = self::EBUSY;
        return -1;
      }
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0011
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }






  public function EIBOpenBusmonitorText_async ()
  {
    $head = array ();
    for ($i = 0; $i < 2; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[0] = EIBConnection::packb ((0x0011 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0011) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIBOpenBusmonitorText"."_complete";
    return 0;
  }
  public function EIBOpenBusmonitorText ()
  {
    if ($this->EIBOpenBusmonitorText_async () == -1)
      return -1;
    return $this->EIBComplete ();
  }






  private function EIBOpen_GroupSocket_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0026
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }






  public function EIBOpen_GroupSocket_async ($write_only)
  {
    $head = array ();
    for ($i = 0; $i < 5; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[4] = EIBConnection::packb (($write_only) ? 0xff : 0);
    $ibuf[0] = EIBConnection::packb ((0x0026 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0026) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIBOpen_GroupSocket"."_complete";
    return 0;
  }
  public function EIBOpen_GroupSocket ($write_only)
  {
    if ($this->EIBOpen_GroupSocket_async ($write_only) == -1)
      return -1;
    return $this->EIBComplete ();
  }







  private function EIBOpenT_Broadcast_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0023
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }





  public function EIBOpenT_Broadcast_async ($write_only)
  {
    $head = array ();
    for ($i = 0; $i < 5; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[4] = EIBConnection::packb (($write_only) ? 0xff : 0);
    $ibuf[0] = EIBConnection::packb ((0x0023 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0023) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIBOpenT_Broadcast"."_complete";
    return 0;
  }
  public function EIBOpenT_Broadcast ($write_only)
  {
    if ($this->EIBOpenT_Broadcast_async ($write_only) == -1)
      return -1;
    return $this->EIBComplete ();
  }








  private function EIBOpenT_Connection_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0020
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }





  public function EIBOpenT_Connection_async ($dest)
  {
    $head = array ();
    for ($i = 0; $i < 5; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb (($dest >> 8) & 0xff);
    $ibuf[2 + 1] = EIBConnection::packb (($dest) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0020 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0020) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIBOpenT_Connection"."_complete";
    return 0;
  }
  public function EIBOpenT_Connection ($dest)
  {
    if ($this->EIBOpenT_Connection_async ($dest) == -1)
      return -1;
    return $this->EIBComplete ();
  }







  private function EIBOpenT_Group_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0022
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }





  public function EIBOpenT_Group_async ($dest, $write_only)
  {
    $head = array ();
    for ($i = 0; $i < 5; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb (($dest >> 8) & 0xff);
    $ibuf[2 + 1] = EIBConnection::packb (($dest) & 0xff);
    $ibuf[4] = EIBConnection::packb (($write_only) ? 0xff : 0);
    $ibuf[0] = EIBConnection::packb ((0x0022 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0022) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIBOpenT_Group"."_complete";
    return 0;
  }
  public function EIBOpenT_Group ($dest, $write_only)
  {
    if ($this->EIBOpenT_Group_async ($dest, $write_only) == -1)
      return -1;
    return $this->EIBComplete ();
  }








  private function EIBOpenT_Individual_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0021
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }






  public function EIBOpenT_Individual_async ($dest, $write_only)
  {
    $head = array ();
    for ($i = 0; $i < 5; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb (($dest >> 8) & 0xff);
    $ibuf[2 + 1] = EIBConnection::packb (($dest) & 0xff);
    $ibuf[4] = EIBConnection::packb (($write_only) ? 0xff : 0);
    $ibuf[0] = EIBConnection::packb ((0x0021 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0021) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIBOpenT_Individual"."_complete";
    return 0;
  }
  public function EIBOpenT_Individual ($dest, $write_only)
  {
    if ($this->EIBOpenT_Individual_async ($dest, $write_only) == -1)
      return -1;
    return $this->EIBComplete ();
  }








  private function EIBOpenT_TPDU_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0024
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }





  public function EIBOpenT_TPDU_async ($src)
  {
    $head = array ();
    for ($i = 0; $i < 5; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb (($src >> 8) & 0xff);
    $ibuf[2 + 1] = EIBConnection::packb (($src) & 0xff);
    $ibuf[0] = EIBConnection::packb ((0x0024 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0024) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIBOpenT_TPDU"."_complete";
    return 0;
  }
  public function EIBOpenT_TPDU ($src)
  {
    if ($this->EIBOpenT_TPDU_async ($src) == -1)
      return -1;
    return $this->EIBComplete ();
  }







  private function EIBOpenVBusmonitor_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) == 0x0001)
      {
        $this->errno = self::EBUSY;
        return -1;
      }
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0012
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }






  public function EIBOpenVBusmonitor_async ()
  {
    $head = array ();
    for ($i = 0; $i < 2; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[0] = EIBConnection::packb ((0x0012 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0012) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIBOpenVBusmonitor"."_complete";
    return 0;
  }
  public function EIBOpenVBusmonitor ()
  {
    if ($this->EIBOpenVBusmonitor_async () == -1)
      return -1;
    return $this->EIBComplete ();
  }






  private function EIBOpenVBusmonitorText_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) == 0x0001)
      {
        $this->errno = self::EBUSY;
        return -1;
      }
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0013
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }






  public function EIBOpenVBusmonitorText_async ()
  {
    $head = array ();
    for ($i = 0; $i < 2; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[0] = EIBConnection::packb ((0x0013 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0013) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIBOpenVBusmonitorText"."_complete";
    return 0;
  }
  public function EIBOpenVBusmonitorText ()
  {
    if ($this->EIBOpenVBusmonitorText_async () == -1)
      return -1;
    return $this->EIBComplete ();
  }






  private function EIBReset_complete ()
  {
    if ($this->_EIB_GetRequest () == -1)
      return -1;
    if (((EIBConnection::upack (substr ($this->data, 0, 2), "n"))) != 0x0004
        || strlen ($this->data) < 2)
      {
        $this->errno = self::ECONNRESET;
        return -1;
      }
    return 0;
  }





  public function EIBReset_async ()
  {
    $head = array ();
    for ($i = 0; $i < 2; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[0] = EIBConnection::packb ((0x0004 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0004) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    $this->complete = "EIBReset"."_complete";
    return 0;
  }
  public function EIBReset ()
  {
    if ($this->EIBReset_async () == -1)
      return -1;
    return $this->EIBComplete ();
  }






  public function EIBSendAPDU ($data)
  {
    $head = array ();
    for ($i = 0; $i < 2; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    if (strlen ($data) < 2)
      {
        $this->errno = self::EINVAL;
        return -1;
      }
    $this->sendlen = strlen ($data);
    for ($i = 0; $i < strlen ($data); $i++)
      $ibuf[] = substr ($data, $i, 1);
    $ibuf[0] = EIBConnection::packb ((0x0025 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0025) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    return $this->sendlen;
  }







  public function EIBSendGroup ($dest, $data)
  {
    $head = array ();
    for ($i = 0; $i < 4; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb (($dest >> 8) & 0xff);
    $ibuf[2 + 1] = EIBConnection::packb (($dest) & 0xff);
    if (strlen ($data) < 2)
      {
        $this->errno = self::EINVAL;
        return -1;
      }
    $this->sendlen = strlen ($data);
    for ($i = 0; $i < strlen ($data); $i++)
      $ibuf[] = substr ($data, $i, 1);
    $ibuf[0] = EIBConnection::packb ((0x0027 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0027) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    return $this->sendlen;
  }








  public function EIBSendTPDU ($dest, $data)
  {
    $head = array ();
    for ($i = 0; $i < 4; $i++)
      $head[$i] = " ";
    $ibuf =& $head;
    $ibuf[2] = EIBConnection::packb (($dest >> 8) & 0xff);
    $ibuf[2 + 1] = EIBConnection::packb (($dest) & 0xff);
    if (strlen ($data) < 2)
      {
        $this->errno = self::EINVAL;
        return -1;
      }
    $this->sendlen = strlen ($data);
    for ($i = 0; $i < strlen ($data); $i++)
      $ibuf[] = substr ($data, $i, 1);
    $ibuf[0] = EIBConnection::packb ((0x0025 >> 8) & 0xff);
    $ibuf[1] = EIBConnection::packb ((0x0025) & 0xff);
    if ($this->_EIB_SendRequest (implode ("", $ibuf)) == -1)
      return -1;
    return $this->sendlen;
  }
  protected static function upack ($data, $type)
  {
    $res = unpack ($type."val", $data);
    return $res["val"];
  }

  protected static function packb ($data)
  {
    return pack ("C", $data);
  }

}
?>
