<?php

require_once APPLICATION_PATH . '/default/controllers/ControllerAbstract.php';

class Admin_ControllerAbstract extends ControllerAbstract
{
	public function init()
	{
        parent::init();

        self::_authorize(); // authorize

        $this->view->layout()->setLayoutPath(APPLICATION_PATH . '/default/views/scripts/')->setLayout('layout');
        $this->view->setHelperPath(APPLICATION_PATH . '/default/views/helpers/');

        $this->view->boolAdmin = true;


        /**
         * Path Helpers
         */

        $this->view->strHome   = PUBLIC_PATH;
        $this->view->strModule = '/' . $this->getRequest()->getModuleName();
        $this->view->strSelf   = '/' . $this->getRequest()->getModuleName() . '/' . $this->getRequest()->getControllerName();


        /**
         * Head Helpers
         */

        $this->view->headTitle()->set('Admin');
        $this->view->headTitle()->setSeparator(' - ');

        $this->view->headMeta()->appendName('pragma', 'no-cache');
        
        // $this->view->headLink()->appendStylesheet('/css/admin.css', 'all');

        // $this->view->headScript()->appendFile('/js/admin.js');

        $this->view->arrErrors = array();
	}

    private function _authorize()
    {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $objSession = Zend_Registry::get('session');
            $objSession->redirect = Myriad_Utils::getCurrentURI();
            $this->_redirect('/login');
        }
    }
}
