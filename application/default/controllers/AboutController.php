<?php

require_once 'ControllerAbstract.php';

class AboutController extends ControllerAbstract
{
    public function init()
    {
        parent::init();

        $this->view->strPrimary = 'About';
    }

    public function indexAction()
    {
        $this->view->headTitle('About');
    }
}
