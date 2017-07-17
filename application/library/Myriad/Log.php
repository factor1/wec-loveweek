<?php

/**
 * Myriad/Log.php
 * @author Myriad Interactive, LLC.
 * @version 2.0
 */

require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Stream.php';
require_once 'Zend/Registry.php';
require_once 'Myriad/Log/Exception.php';

class Myriad_Log extends Zend_Log
{
    private static $_objMyriadLogInstance;
    private static $_objZendLog;

    public function __construct()
    {

    }

    public static function getInstance()
    {
        if (Myriad_Log::$_objMyriadLogInstance === NULL) {
            Myriad_Log::$_objMyriadLogInstance = new Myriad_Log();
            $strStream = APPLICATION_PATH . '/data/logs/' . date('Ymd') . '_' . Zend_Registry::get('config')->logger->filename;
            $objWriter = new Zend_Log_Writer_Stream($strStream);
            self::$_objZendLog = new Zend_Log($objWriter);
        }

        return Myriad_Log::$_objMyriadLogInstance;
    }


    /**
     * Logs messages to log file
     *
     * @param string $strMessage
     * @param string $intLevel
     */

    public function writeLog($strMessage, $intLevel = Zend_Log::INFO)
    {
        self::$_objZendLog->log($strMessage, $intLevel);
    }
}
