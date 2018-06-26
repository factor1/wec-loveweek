<?php

require_once 'Zend/Mail.php';
require_once 'ControllerAbstract.php';

class LoginController extends ControllerAbstract
{
    public function init()
    {
        parent::init();

        $this->config = Zend_Registry::get('config');

        if (Zend_Auth::getInstance()->hasIdentity()) {
            $this->_redirect('/');
        }
    }

    public function indexAction()
    {
        $this->view->headTitle('Login');
        $this->view->strPrimary = 'Login';

        if ($this->getRequest()->getPost()) {

            // request variables

            $strUsername = trim($this->getRequest()->getPost('username'));
            $strPassword = trim($this->getRequest()->getPost('password'));
            $blnRemember = (int) $this->getRequest()->getPost('remember', 0);
            $intAttempts = (int) $this->getRequest()->getPost('attempts', 0);
            $userExists  = Users::isActiveUser($strUsername);

            // assign view variables
            $this->view->strUsername = $strUsername;
            $this->view->intAttempts = $intAttempts + 1;

            // validate username and password

            if ($strUsername && $strPassword) {

                $objResult = Users::login($strUsername, $strPassword);
                $intResult = $objResult->getCode();

                if ($intResult == 1) { // 1 indicates valid cred & active user

                    if ($blnRemember == 1) {
                        Zend_Session::rememberMe();
                    } else {
                        Zend_Session::regenerateId();
                    }

                    // redirect to requested page

                    $this->_helper->FlashMessenger(array('success' => 'You are now signed in as ' . $this->objAuth->getIdentity()->user_first_name . ' ' . $this->objAuth->getIdentity()->user_last_name .  '.'));
                    Zend_Registry::get('session')->redirect = '';  // forget redirect page
                    $this->_redirect('/');

                } else if ($intResult == -3) {
                    if ($userExists) {
                        $this->_helper->FlashMessenger(array('danger' => 'Sorry, those credentials were not valid. Try again or reset your password.'));
                    } else {
                        $this->_helper->FlashMessenger(array('danger' => 'Your account is not active yet. A confirmation email with an activation link has been sent.'));
                    }
                } else {
                    $this->_helper->FlashMessenger(array('danger' => 'Login failed. Please try again.'));
                }

            } else {
                $this->_helper->FlashMessenger(array('danger' => 'Enter your username and password.'));
            }
        }
    }

    public function assistanceAction()
    {
        $this->view->headTitle('Login Assistance');
        $this->view->strPrimary = 'Login Assistance';

        if ($this->getRequest()->getPost()) {

            $strEmail = strtolower(trim($this->getRequest()->getPost('email')));

            if (Myriad_Utils::isValidEmail($strEmail) == false) {

                $this->_helper->FlashMessenger(array('danger' => 'Please enter a valid email address.'));

            } else if (Users::isRegisteredUser($strEmail) == false) {

                $this->_helper->FlashMessenger(array('danger' => 'There is no registered user with the email address ' . $strEmail . '.'));

            } else {

                $objUsers = new Users();
                $objSelect = $objUsers->select()->where('username = ?', $strEmail);
                $objUser = $objUsers->fetchRow($objSelect);

                // set body text for confirmation email

                $config = $this->config->appSettings;
                $strBodyText  = "Hello " . ucfirst(strtolower($objUser->user_first_name)) . ",\n\n";
                $strBodyText .= "Somebody (hopefully you) requested password assistance to your Love Week account. Click on the following link to reset your password. Please note that once your password has been updated, the link below will no longer work.\n\n";
                $strBodyText .= $config->url->base."/login/reset-password/" . md5($objUser->password . $objUser->id) . "\n\n";
                $strBodyText .= "Best regards,\n";
                $strBodyText .= "Love Week\n\n\n";
                $strBodyText .= "PS: If you didn't request password assistance, then it's possible that another user may have entered your information while trying to reset their password. If that's the case, no action is required on your part, just delete this email.\n";

                $config = $this->config->resources->mail->userRegister;
                $objMail = new Zend_Mail();
                $objMail->setBodyText(utf8_decode($strBodyText));
                $objMail->setBodyHtml(utf8_decode($strBodyText));
                $objMail->addTo($config->From->email,$config->From->name);
                $objMail->addTo($strEmail);
                $objMail->setSubject('Love Week - Password Assistance');

                if ($objMail->send()) {
                    $this->_helper->FlashMessenger(array('info' => 'Instructions to reset your password have been emailed to ' . $strEmail . '.'));
                    $this->_redirect('/login');
                } else {
                    $this->_helper->FlashMessenger(array('danger' => 'We experienced an unexpected error. Please try again later.'));
                }
            }
        }
    }

    public function newAction()
    {
        $this->view->headTitle('Sign Up');
        $this->view->strPrimary = 'Sign Up';

        $this->view->arrStates = array('AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'DC' => 'District Of Columbia', 'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland', 'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina', 'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin');

        if ($this->getRequest()->getPost()) {

            $arrData = array();

            $arrData['user_first_name']         = trim($this->getRequest()->getPost('user_first_name'));
            $arrData['user_last_name']          = trim($this->getRequest()->getPost('user_last_name'));
            $arrData['user_phone']              = trim($this->getRequest()->getPost('user_phone'));
            $arrData['user_address']            = trim($this->getRequest()->getPost('user_address'));
            $arrData['user_city']               = trim($this->getRequest()->getPost('user_city'));
            $arrData['user_state']              = trim($this->getRequest()->getPost('user_state'));
            $arrData['user_zip']                = trim($this->getRequest()->getPost('user_zip'));
            $strEmail    = $arrData['username'] = trim($this->getRequest()->getPost('username'));
            $strPassword = $arrData['password'] = trim($this->getRequest()->getPost('password'));
            $arrData['user_is_active']          = 1;

            $arrData = Myriad_Utils::stripSlashes($arrData);
            $this->view->arrData = $arrData;

            /**
             * Error Check before entering data
             */

            $arrErrors = [];

            if ($arrData['user_first_name'] == '') {
                $arrErrors[] = 'Missing First Name';
            }

            if ($arrData['user_last_name'] == '') {
                $arrErrors[] = 'Missing Last Name';
            }

            if ($arrData['username'] == '') {
                $arrErrors[] = 'Missing Email Address';
            } else if (Users::isCurrentUser($strEmail)) {
                $arrErrors[] = 'Email Address Already Registered (' . $strEmail . ')';
            }

            if ($arrData['password'] == '') {
                $arrErrors[] = 'Missing Password';
            } else {
                $arrData['password_salt'] = Myriad_Utils::generateSalt();
                $arrData['password'] = md5($arrData['password'] . $arrData['password_salt']);
                $arrData['user_hash'] = md5($arrData['username'] . $arrData['password_salt']);
            }

            if ($arrData['user_phone'] == '') {
                $arrErrors[] = 'Missing Phone Number';
            }

            if ($arrData['user_city'] == '') {
                $arrErrors[] = 'Missing City';
            }

            if ($arrData['user_state'] == '') {
                $arrErrors[] = 'Missing State';
            }

            if ($arrData['user_zip'] == '') {
                $arrErrors[] = 'Missing Zip Code';
            }

            /**
             * Insert User
             */

            if (count($arrErrors) == 0) {

                $objUsers = new Users();

                $intId = $objUsers->insert($arrData); // add inactive user
                $this->_helper->FlashMessenger(array('success' => 'You have successfully registered for Love Week! Use your email and password to login below.'));

                /* Send Confirmation Email */
                $config = $this->config->appSettings;
                $strMessage  = "Dear " . $arrData['user_first_name'] . ",\n\n";
                $strMessage .= "Thanks for signing up for Love Week! Click the link below your credentials to activate your account.\n\n";
                $strMessage .= "Email address: " . $strEmail . "\n";
                $strMessage .= "Password: " . $strPassword . "\n\n";
                $strMessage .= $config->url->base."/login/verify?email=" . $strEmail . "&hash=" . $arrData['user_hash'];

                $config = $this->config->resources->mail->userRegister;
                $objMail = new Zend_Mail();
                $objMail->setBodyTextutf8_decode(($strMessage));
                $objMail->addTo($config->From->email,$config->From->name);
                $objMail->addTo($strEmail);
                $objMail->setSubject('Love Week Account Activation');

                try {
                    $objMail->send();
                    $this->_helper->FlashMessenger(array('success' => 'You have successfully registered for Love Week! Check your email for an account confirmation link.'));
                } catch (Zend_Exception $e) {
                    $this->_helper->FlashMessenger(array('danger' => 'Something went wrong. ' . $e->getMessage()));
                }

                $this->_redirect('/login');

            } else {
                $this->_helper->FlashMessenger(array('danger' => $arrErrors[0]));
            }
        }
    }

    public function resetPasswordAction()
    {
        $this->view->headTitle('Reset Password');

        $objUsers = new Users();
        $objSelect = $objUsers->select()->where('MD5(CONCAT(password, id)) = ?', $this->getRequest()->getParam('hash'));
        $objUser = $objUsers->fetchRow($objSelect);

        if (is_object($objUser)) {
            $this->view->objUser = $objUser;
        } else {
            $this->_helper->FlashMessenger(array('danger' => 'The link that you followed is no longer valid.'));
            $this->_redirect('/login');
        }

        if ($this->getRequest()->getPost()) {

            $strPassword = trim($this->getRequest()->getPost('password'));
            $strPasswordConfirmation = trim($this->getRequest()->getPost('password_confirmation'));

            if (empty($strPassword)) {
                $this->_helper->FlashMessenger(array('danger' => 'You did not enter a password. Please try again.'));
            } else if (strlen($strPassword) < 6) {
                $this->_helper->FlashMessenger(array('danger' => 'The password provided was invalid, it must be at least 6 characters long.'));
            } else if ($strPassword != $strPasswordConfirmation) {
                $this->_helper->FlashMessenger(array('danger' => 'The passwords provided did not match. Please try again.'));
            } else {
                $objUser->password_salt = Myriad_Utils::generateSalt();
                $objUser->password = md5($strPassword . $objUser->password_salt);
                $objUser->save();
                $this->_forward('index');
            }
        }
    }

    public function verifyAction()
    {
        $strEmail = $this->_getParam('email');
        $strHash  = $this->_getParam('hash');

        if (isset($strEmail) && isset($strHash)) {

            if (Users::isCurrentUser($strEmail)) {

                $intId = Users::getUserIdByActivationHash($strHash);

                $objUsers = new Users();
                $objUser = $objUsers->find($intId)->current();

                if ($objUser) {
                    $strWhere = $objUsers->getAdapter()->quoteInto('id = ?', $intId);
                    $objUsers->update(array('user_is_active' => 1), $strWhere);
                    $this->_helper->FlashMessenger(array('success' => 'Your account has been successfully activated!'));
                    $this->_redirect('/events');
                }
            }
        }

        $this->_redirect('/'); // redirect home regardless
    }
}
