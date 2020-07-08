<?php

/** This file is part of KCFinder project
  *
  *      @desc Session class
  *   @package KCFinder
  *   @version 3.12
  *    @author Pavel Tzonkov <sunhater@sunhater.com>
  * @copyright 2010-2014 KCFinder Project
  *   @license http://opensource.org/licenses/GPL-3.0 GPLv3
  *   @license http://opensource.org/licenses/LGPL-3.0 LGPLv3
  *      @link http://kcfinder.sunhater.com
  */

namespace kcfinder;

class session {

    const SESSION_VAR = "_sessionVar";
    public $values;
    protected $config;

    public function __construct($configFile) {

        // Start session if it is not already started
        session_name('prj');
        if (!session_id())
            session_start();


        $config = require($configFile);

        // _sessionVar option is set
        if (isset($config[self::SESSION_VAR])) {
            $session = &$config[self::SESSION_VAR];


            // _sessionVar option is string
            if (is_string($session))
                $session = &$_SESSION[$session];

            if (!is_array($session))
                $session = array();

            //var_dump($session);exit;

        // Use global _SESSION array if _sessionVar option is not set
        } else
            $session = &$_SESSION;

        // Securing the session
        $stamp = array(
            'ip' => $_SERVER['REMOTE_ADDR'],
            'agent' => md5($_SERVER['HTTP_USER_AGENT'])
        );
        if (!isset($session['stamp']))
            $session['stamp'] = $stamp;
        elseif (!is_array($session['stamp']) || ($session['stamp'] !== $stamp)) {
            // Destroy session if user agent is different (e.g. after browser update)
            if ($session['stamp']['ip'] === $stamp['ip'])
                session_destroy();
            die;
        }

        // Load session configuration
        foreach ($config as $key => $val)
            $this->config[$key] = ((substr($key, 0, 1) != "_") && isset($session[$key]))
                ? $session[$key]
                : $val;

        // Session data goes to 'self' element
        if (!isset($session['self']))
            $session['self'] = array();
        $this->values = &$session['self'];
    }

    public function getConfig() {
        return $this->config;
    }

}
