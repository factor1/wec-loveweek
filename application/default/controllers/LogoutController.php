<?php

require_once 'Zend/Auth.php';

class LogoutController extends Zend_Controller_Action
{
    public function indexAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::forgetMe();
        $this->_helper->FlashMessenger('You have logged out successfully.');
        $this->_redirect('/');
    }
}
