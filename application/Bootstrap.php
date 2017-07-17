<?php

/**
 * Application bootstrap
 * 
 * @uses Zend_Application_Bootstrap_Bootstrap
 */

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * Bootstrap Config
     * 
     * @return void
     */

    protected function _initConfig()
    {
        $objConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        Zend_Registry::set('config', $objConfig);
    }


    /**
     * Bootstrap Error Reporting
     * 
     * @return void
     */

    protected function _initErrorReporting()
    {
        error_reporting(E_ALL | E_STRICT);
        ini_set('display_errors', '1');
    }


    /**
     * Bootstrap Default Time Zone
     * 
     * @return void
     */

    protected function _initDefaultTimeZone()
    {
        date_default_timezone_set('America/Phoenix');
    }


    /**
     * Bootstrap Logger
     * 
     * @return void
     */

    protected function _initLogger()
    {
        require_once 'Myriad/Log.php';
    }


    /**
     * Bootstrap Session
     * 
     * @return void
     */

    protected function _initSession()
    {
        Zend_Session::setOptions(array(
            'name'                => 'company',                              // unique name
            'save_path'           => APPLICATION_PATH . '/data/sessions',    // save storage path
            'use_only_cookies'    => 'on',
            'cookie_httponly'     => true,                                   // hardening against XSS
            'remember_me_seconds' => 864000
        ));

        Zend_Registry::set('session', new Zend_Session_Namespace('company'));
    }


    /**
     * Bootstrap Autoloader for Application Resources
     * 
     * @return Zend_Application_Module_Autoloader
     */

    protected function _initAutoload()
    {
        $objLoader = Zend_Loader_Autoloader::getInstance();

        $objLoader->registerNamespace('Myriad_'); // add Myriad namespace so Myriad library classes will be autoloaded

        /**
         * Register an anonymous function for loading model files
         */

        $objLoader->pushAutoloader(function($strClassName) {
            $strModelPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'models';
            $arrPath = explode('_', strtolower($strClassName));
            $strPath = $strModelPath . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $arrPath) . '.phpm';
            
            if (file_exists($strPath)) {
                include($strPath);
                return class_exists($strClassName, false);
            }
            return false;
        });
        
        return $objLoader;
    }

}