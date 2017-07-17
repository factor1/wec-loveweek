<?php
//header("Location: http://www.watersedgechurch.net/loveweekenddates/");
//die();




/**
 * Include Paths
 */

define('PUBLIC_PATH', realpath(dirname(__FILE__)));
define('LIBRARY_PATH', PUBLIC_PATH . '/application/library');
define('APPLICATION_PATH', PUBLIC_PATH . '/application');

define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

set_include_path(implode(PATH_SEPARATOR, array(
    LIBRARY_PATH,
    APPLICATION_PATH,
    get_include_path(),
)));


/**
 * Zend_Application
 */

require_once 'Zend/Application.php';

$objApplication = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$objApplication->bootstrap()->run();