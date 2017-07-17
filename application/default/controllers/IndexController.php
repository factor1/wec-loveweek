<?php

require_once 'ControllerAbstract.php';

class IndexController extends ControllerAbstract
{
    public function init()
    {
        parent::init();

        $this->view->strPrimary = 'Home';

        // if (!Zend_Auth::getInstance()->hasIdentity()) {
        //     $this->_redirect('/login');
        // } else {
            $this->_redirect('/events');
        // }
    }

    public function indexAction()
    {
        $this->view->headTitle('Home');
    }
}
