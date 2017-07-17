<?php

require_once 'ControllerAbstract.php';

class Admin_IndexController extends Admin_ControllerAbstract
{
	public function indexAction()
    {
    	
      $this->view->headTitle('Adminstration');
	
    }
}
