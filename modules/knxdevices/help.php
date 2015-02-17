<?php
/*
    EIBD client library examples
    Copyright (C) 2005-2008 Martin Koegler <mkoegler@auto.tuwien.ac.at>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

function gaddrparse ($addr)
{
  $addr = explode ("/", $addr);
  if (count ($addr) >= 3)
    $r =
      (($addr[0] & 0x1f) << 11) | (($addr[1] & 0x7) << 8) |
      (($addr[2] & 0xff));
  if (count ($addr) == 2)
    $r = (($addr[0] & 0x1f) << 11) | (($addr[1] & 0x7ff));
  if (count ($addr) == 1)
    $r = (($addr[1] & 0xffff));
  return $r;
}

function formatiaddr ($addr)
{
  return sprintf ("%d.%d.%d", ($addr >> 12) & 0x0f, ($addr >> 8) & 0x0f,
                  ($addr >> 0) & 0xff);
}

function formatgaddr ($addr)
{
  return sprintf ("%d/%d/%d", ($addr >> 11) & 0x1f, ($addr >> 8) & 0x07,
                  ($addr >> 0) & 0xff);
}

function groupswrite ($con, $addr, $val)
{
  $addr = gaddrparse ($addr);
  $val = ($val + 0) & 0x3f;
  $val |= 0x0080;
  $r = $con->EIBOpenT_Group ($addr, 1);
  if ($r == -1)
    return -1;
  $r = $con->EIBSendAPDU (pack ("n", $val));
  if ($r == -1)
    return -1;
  return $con->EIBReset ();
}

function groupwrite ($con, $addr, $val)
{
  $addr = gaddrparse ($addr);
  $header = 0x0080;
  $r = $con->EIBOpenT_Group ($addr, 1);
  if ($r == -1)
    return -1;
  $data = pack ("n", $header);
  for ($i = 0; $i < count ($val); $i++)
    $data .= pack ("C", $val[$i]);
  $r = $con->EIBSendAPDU ($data);
  if ($r == -1)
    return -1;
  return $con->EIBReset ();
}

function cacheread ($con, $addr, $age = 0)
{
  $buf = new EIBBuffer;
  $src = new EIBAddr;
  $addr = gaddrparse ($addr);
  $r = $con->EIB_Cache_Read_Sync ($addr, $src, $buf, $age);
  if ($r == -1 && $con->GetLastError() == EIBConnection::ENOENT)
    return array (formatgaddr ($addr), -2);
  if ($r == -1)
    return array (formatgaddr ($addr), -1);
  $data = $buf->buffer;
  if ($data[0] & 0x3 || ($data[1] & 0xC0) == 0xC0)
    return array (formatgaddr ($addr), -3);
  if (strlen ($data) == 2)
    {
      $res = unpack ("nval", $data);
      $val = $res["val"] & 0x3f;
      return array (formatgaddr ($addr), formatiaddr ($src->addr), $val);
    }
  else
    {
      $res = array (formatgaddr ($addr), formatiaddr ($src->addr));
      for ($i = 2; $i < strlen ($data); $i++)
        {
          $r = unpack ("Cval", $data[$i]);
          $res[] = $r["val"];
        }
      return $res;
    }
}

function f2_decode($val)
{
  $exp = ($val[0] & 0x78) >> 3;
  $sign = ($val[0] & 0x80) >> 7;
  $mant = ($val[0] & 0x07) << 8 | $val[1];
  if ($sign)
    $sign = -1 << 11;
  else
    $sign = 0;

  $val = ($mant | $sign) * pow (2, $exp) * 0.01;
 return $val; 
}

function f2_encode($val)
{
  if($val<0)
    {
      $sign = 1;
      $val = - $val;
    }
  else
    $sign = 0;
  $val = $val * 100.0;
  $exp = 0;
  while ($val > 2047)
    {
      $exp ++;
      $val = $val / 2;
    }
  if ($sign)
    $val = - $val;
  $val = $val & 0x7ff;

  return array(($sign << 7) | (($exp & 0x0f)<<3)| (($val >> 8)&0x07), ($val& 0xff));
}

/**
* Title
*
* Description
*
* @access public
*/
 function knxwrite($con, $addr, $value, $typ) {

  $res=-1;

  switch($typ)
    {
    case "small":
      $res=groupswrite ($con, $addr, $value);
      break;

    case "p1":
      if ($value < 0)
        $value = 0;
      if ($value > 100)
        $value = 100;
      $value = (int)(($value * 255) / 100 + 0.5);
      $res=groupwrite ($con, $addr, array ($value & 0xff));
      break;

    case "b1":
      $res=groupwrite ($con, $addr, array ($value & 0xff));
      break;

    case "b2":
      $res=groupwrite ($con, $addr, array (($value >> 8) & 0xff, $value & 0xff));
      break;

    case "f2":
      $res=groupwrite ($con, $addr, f2_encode($value));
      break;
    }
    return $res;
 }

?>