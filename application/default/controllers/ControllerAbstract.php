<?php

class ControllerAbstract extends Zend_Controller_Action
{
    public function init()
    {
        $this->view->strZendModule     = $this->getRequest()->getModuleName();
        $this->view->strZendController = $this->getRequest()->getControllerName();
        $this->view->strZendAction     = $this->getRequest()->getActionName();

        /**
         * Countdown
         */

        $timeEnd = mktime(8, 0, 0, 8, 6, 2018);
                // $timeEnd = mktime(24h, min, sec, M, D, Y);
        $timeNow = time();
        $this->view->timeDif = $timeEnd - $timeNow;
        $this->view->timeDifDays = floor($this->view->timeDif / 60 / 60 / 24);

        /**
         * Zend Auth
         */

        $objAuth = Zend_Auth::getInstance();
        $this->view->objAuth = $this->objAuth = $objAuth;

        $this->boolIsUser = false;
        $this->boolIsAdmin = false;

        if ($objAuth->hasIdentity()) {
            $this->boolIsUser = true;
            if ($objAuth->getIdentity()->user_is_admin == 1) {
                $this->boolIsAdmin = true;
            }
        }

        $this->view->boolIsUser = $this->boolIsUser;
        $this->view->boolIsAdmin = $this->boolIsAdmin;

        /**
         * Zend Paginator
         */

        Zend_Paginator::setDefaultScrollingStyle('Sliding');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('partials/paginator/full.phtml');
    }
}
