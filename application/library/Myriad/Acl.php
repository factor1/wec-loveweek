<?php

/**
 * Myriad/Acl.php
 *
 * @author Myriad Interactive, LLC.
 */

require_once 'Zend/Acl.php';
require_once 'Zend/Acl/Role.php';
require_once 'Zend/Acl/Resource.php';
require_once 'Myriad/Log.php';
require_once 'Myriad/Acl/Exception.php';

class Myriad_Acl extends Zend_Acl
{
    public function __construct()
    {
        $this->addRole(new Zend_Acl_Role('guest'));
        $this->addRole(new Zend_Acl_Role('user'), 'guest');
        $this->addRole(new Zend_Acl_Role('admin'), 'user');
 
        $this->add(new Zend_Acl_Resource('default'));
        $this->add(new Zend_Acl_Resource('admin'));
 
        $this->allow('user', 'admin');
        $this->allow('admin', 'admin');
    }
}
