<?php

/**
 * Myriad/Plugin/Acl.php
 *
 * @author Myriad Interactive, LLC.
 */

require_once 'Myriad/Acl.php';

class Myriad_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{
    private $_objAcl = null;
 
    public function __construct(Zend_Acl $objAcl)
    {
        $this->_objAcl = $objAcl;
    }
 
    public function preDispatch(Zend_Controller_Request_Abstract $objRequest)
    {
/*
        $strRole       = (Zend_Auth::getInstance()->hasIdentity()) ? 'user' : 'guest';
        $strModule     = $objRequest->getModuleName();
        $strController = $objRequest->getControllerName();

        $strResource = $strModule;

        if ($strModule == 'admin') {
            if ($this->_objAcl->isAllowed($strRole, $strResource)) {
                echo $strRole . ' is allowed access to ' . $strResource;
                // $objRequest->setModuleName('admin')->setControllerName('login')->setActionName('index');
            } else {
                echo $strRole . ' is denied access to ' . $strResource;
            }
        }
*/
    }
}
