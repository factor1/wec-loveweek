<?php

require_once 'ControllerAbstract.php';

class ErrorController extends ControllerAbstract
{
	public function init()
	{
        parent::init();
		$this->view->strPrimary = 'Main Menu';
		$this->view->arrStyles  = array();
		$this->view->arrScripts = array();
	}

	public function errorAction()
	{
        $objErrors    = $this->_getParam('error_handler');
        $objException = $objErrors->exception;
        $objRequest   = $objErrors->request;

        Myriad_Log::getInstance()->writeLog($objException->getMessage() . "\n" .  $objException->getTraceAsString(), Zend_Log::ERR);

        switch ($objErrors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 Error - controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->_forward('page-not-found');
                break;
            default:
                // 500 Error - application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->strError = $objErrors->exception;
                $this->_forward('application-error');
                break;
        }

        $this->getResponse()->clearBody();
	}

	public function pageNotFoundAction()
    {
        $this->view->headTitle('Page Not Found');
        $this->view->strTitle = 'HTTP 404 Not Found';
        $this->view->strSecondary = 'Page Not Found';
        $this->view->strStyles = 'error.css';
    }

	public function applicationErrorAction()
    {
        $this->view->headTitle('Application Error');
        $this->view->strTitle = 'Application Error';
        $this->view->strSecondary = 'Application Error';
        $this->view->strStyles = 'error.css';
    }
}
