<?php
/**
 * Phpmodbus Copyright (c) 2004, 2012 Jan Krakora
 *  
 * This source file is subject to the "PhpModbus license" that is bundled
 * with this package in the file license.txt.
 *   
 *
 * @copyright  Copyright (c) 2004, 2012 Jan Krakora
 * @license PhpModbus license 
 * @category Phpmodbus
 * @tutorial Phpmodbus.pkg 
 * @package Phpmodbus 
 * @version $id$
 *  
 */

require_once dirname(__FILE__) . '/ModbusMaster.php'; 

/**
 * ModbusMasterUdp
 *
 * This class deals with the MODBUS master using UDP stack.
 *  
 * Implemented MODBUS master functions:
 *   - FC  1: read coils
 *   - FC  3: read multiple registers
 *   - FC 15: write multiple coils 
 *   - FC 16: write multiple registers
 *   - FC 23: read write registers
 *   
 * @author Jan Krakora
 * @copyright  Copyright (c) 2004, 2012 Jan Krakora
 * @package Phpmodbus  
 *
 */
class ModbusMasterUdp extends ModbusMaster {
  
  /**
   * ModbusMasterUdp
   *
   * This is the constructor that defines {@link $host} IP address of the object. 
   *     
   * @param String $host An IP address of a Modbus UDP device. E.g. "192.168.1.1".
   */         
  function ModbusMasterUdp($host){
    $this->host = $host;
    $this->socket_protocol = "UDP";    
  }
}
