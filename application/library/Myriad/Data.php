<?php

/**
 * Myriad/Data.php
 * Creates a connection on instantiation
 * @author Myriad Interactive, LLC.
 * @version 2.0
 */

require_once 'Zend/Registry.php';
require_once 'Myriad/Log.php';
require_once 'Myriad/Data/Exception.php';

class Myriad_Data
{
    /**
     * Singleton
     *
     * @var mixed
     */

    private static $dbAdapter;

    private function __construct()
    {
        // construct
    }

    public static function getAdapter()
    {
        if (Myriad_Data::$dbAdapter === NULL) {
            try {
                Myriad_Data::$dbAdapter = Zend_Db::factory(Zend_Registry::get('config')->resources->db);
            } catch (Zend_Db_Adapter_Exception $e) {
                // perhaps a failed login credential, or perhaps the RDBMS is not running
                Myriad_Log::getInstance()->writeLog($e->getMessage(), Zend_Log::ERR );
            } catch (Zend_Exception $e) {
                // perhaps factory() failed to load the specified Adapter class
                Myriad_Log::getInstance()->writeLog($e->getMessage(), Zend_Log::ERR );
            }
        }

        return Myriad_Data::$dbAdapter;
    }
}
