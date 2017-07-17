<?php

require_once 'ControllerAbstract.php';

class Admin_UsersController extends Admin_ControllerAbstract
{
    public function init()
    {
        parent::init();

        $this->view->strAdminPrimary = 'Users';
        $this->view->headTitle('Users');

        if ((!$this->boolIsAdmin) && ($this->_getParam('action') != 'manage')) {
            $this->_helper->FlashMessenger(array('danger' => 'Only administrators can access the users page.'));
            $this->_redirect('/');
        }
    }

    public function indexAction()
    {
        $this->_forward('list');
    }

    public function listAction()
    {
        /**
         * Column Headers
         */

        $this->view->arrFields = array(
            'user_last_name'  => 'Last name',
            'user_first_name' => 'First name',
            'username'        => 'Email address',
            'user_phone'      => 'Phone',
            'user_is_admin'   => 'Admin',
            'user_is_active'  => 'Active',
        );

        /**
         * Get Users
         */

        $objUsers = new Users();
        $objUsers->setKeywords($this->_getParam('keywords', ''));
        $objUsers->setField(array('user_first_name', 'user_last_name', 'username', 'user_city'));
        $objUsers->setSort($this->_getParam('sort', 'user_last_name'));
        $objUsers->setDir($this->_getParam('sdir', 'ASC'));

        /**
         * Zend Paginator
         */

        $objPaginator = Zend_Paginator::factory($objUsers->getSelectInstance());
        $objPaginator->setCurrentPageNumber($this->_getParam('page', 1));
        $objPaginator->setItemCountPerPage(30);
        $this->view->objPaginator = $objPaginator;

        /**
         * Assign Common View Variables
         */

        $this->view->strKeywords = $objUsers->getKeywords();
        $this->view->strField    = $objUsers->getField();
        $this->view->strSort     = $objUsers->getSort();
        $this->view->strDir      = $objUsers->getDir();
    }

    public function viewAction()
    {
        $intId = (int) $this->_getParam('id', 0);

        $objUsers = new Users();
        $objUser = $objUsers->find($intId)->current();

        if ($objUser) {
            $this->view->headTitle($objUser->user_first_name . ' ' . $objUser->user_last_name);
            $this->view->objUser = $objUser;
        } else {
            $this->_helper->FlashMessenger(array('danger' => 'The user you requested could not be found.'));
            $this->_redirect('/admin/users');
        }
    }

    public function manageAction()
    {
        $intId = (int) $this->_getParam('id', 0);
        $strReferer = $this->_getParam('referer', getenv('HTTP_REFERER'));

        /**
         * Only Adminstrators Add or Edit People (except themselves)
         */

        if ((Zend_Auth::getInstance()->getIdentity()->user_is_admin != 1) && Zend_Auth::getInstance()->getIdentity()->id != $intId) {
            $strAction = ($intId > 0) ? 'edit' : 'add';
            $this->_helper->FlashMessenger(array('warning' => 'Only administrators are allowed to ' . $strAction . ' users.'));
            $this->_redirect(getenv('HTTP_REFERER'));
        }

        if (Zend_Auth::getInstance()->getIdentity()->user_is_admin != 1) {
            $this->view->boolAdmin = false;
        }


        /**
         * Process
         */

        if ($this->getRequest()->getPost()) {

            $arrData = array();
 
            $arrData['username']        = trim($this->getRequest()->getPost('username'));
            $arrData['password']        = trim($this->getRequest()->getPost('password'));
            $arrData['user_first_name'] = trim($this->getRequest()->getPost('user_first_name'));
            $arrData['user_last_name']  = trim($this->getRequest()->getPost('user_last_name'));
            $arrData['user_phone']      = trim($this->getRequest()->getPost('user_phone'));
            $arrData['user_age']        = trim($this->getRequest()->getPost('user_age'));
            $arrData['user_address']    = trim($this->getRequest()->getPost('user_address'));
            $arrData['user_city']       = trim($this->getRequest()->getPost('user_city'));
            $arrData['user_state']      = trim($this->getRequest()->getPost('user_state'));
            $arrData['user_zip']        = trim($this->getRequest()->getPost('user_zip'));
            $arrData['user_is_admin']   = (int) $this->getRequest()->getPost('user_is_admin');
            $arrData['user_is_active']  = (int) $this->getRequest()->getPost('user_is_active');

            $arrData = Myriad_Utils::stripSlashes($arrData);


            /**
             * Error Checking
             */

           if ($intId == 0) {
                if ($arrData['password'] == '') {
                    $this->view->arrErrors[] = 'Missing Password';
                } else {
                    $arrData['password_salt'] = Myriad_Utils::generateSalt();
                    $arrData['password'] = md5($arrData['password'] . $arrData['password_salt']);
                    $arrData['user_hash'] = md5($arrData['username'] . $arrData['password_salt']);
                }
            } else {
                if ($arrData['password'] == '') {
                    unset($arrData['password']);  // assume that we are not updating the password
                } else {
                    $arrData['password_salt'] = Myriad_Utils::generateSalt();
                    $arrData['password'] = md5($arrData['password'] . $arrData['password_salt']);
                    $arrData['user_hash'] = md5($arrData['username'] . $arrData['password_salt']);
                }
            }

            if ($arrData['user_first_name'] == '') {
                $this->view->arrErrors[] = 'Missing First Name';
            }

            if ($arrData['user_last_name'] == '') {
                $this->view->arrErrors[] = 'Missing Last Name';
            }


            /**
             * Insert / Update Record
             */

            if (count($this->view->arrErrors) == 0) {

                $objUsers = new Users();

                if ($intId == 0) {
                    $intId = $objUsers->insert($arrData);
                    $this->_helper->FlashMessenger(array('success' => 'The user "' . $arrData['user_first_name'] . ' ' . $arrData['user_last_name'] . '" has been added successfully.'));
                } else {
                    $strWhere = $objUsers->getAdapter()->quoteInto('id = ?', $intId);
                    $objUsers->update($arrData, $strWhere);
                    $this->_helper->FlashMessenger(array('success' => 'The user "' . $arrData['user_first_name'] . ' ' . $arrData['user_last_name'] . '" has been updated successfully.'));
                }
            }
        }


        /**
         * Create or Find Row
         */

        if (count($this->view->arrErrors) > 0) {

            $arrData['old_username'] = trim($this->getRequest()->getPost('old_username'));
            $objUser = (object) $arrData;

        } else {

            $objUsers = new Users();

            if ($intId > 0) {
                $objUser = $objUsers->find($intId)->current();

                if (!is_object($objUser)) {
                    $this->_helper->FlashMessenger(array('danger' => 'The user you requested could not be found.'));
                    $this->_redirect('/admin/users');
                }
            } else {
                $objUser = $objUsers->createRow();
            }

        }


        /**
         * Assign View Variables
         */

        $this->view->intId      = $intId;
        $this->view->strReferer = $strReferer;
        $this->view->objUser    = $objUser;
        $this->view->arrStates  = array('AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'DC' => 'District Of Columbia', 'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland', 'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina', 'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin');
    }

    public function deleteAction()
    {
        if (!$this->boolIsAdmin) {
            $this->_helper->FlashMessenger(array('warning' => 'Only administrators are allowed to delete users.'));
            $this->_redirect(getenv('HTTP_REFERER'));
        }

        $intId = (int) $this->_getParam('id', 0);
        $arrChecked = $this->_getParam('checked', array());

        if ($intId > 0) {
            $arrChecked[] = $intId;
        }

        $objUsers = new Users();

        $this->_helper->FlashMessenger(array('success' => $objUsers->deleteChecked($arrChecked, 'user')));

        $this->_redirect(getenv('HTTP_REFERER'));
    }
}
