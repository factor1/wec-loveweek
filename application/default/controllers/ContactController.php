<?php

require_once 'ControllerAbstract.php';

class ContactController extends ControllerAbstract
{
    public function init()
    {
        parent::init();

        $this->config = Zend_Registry::get('config');

        $this->view->strPrimary = 'Contact';
    }

    public function indexAction()
    {
        $this->view->headTitle('Contact');

        $arrData = $this->_request->getPost();

        if (count($arrData) > 0 && empty($arrData['email'])) { // spam protection

            if (empty($arrData['first_name']) || empty($arrData['last_name']) || empty($arrData['email_address']) || empty($arrData['message'])) {

                $this->_helper->FlashMessenger(array('info' => 'Please fill out all required fields.'));
                $this->view->arrData = $arrData;

            } else {

                $strBodyText  = "First Name: " . $arrData['first_name'] . "\n";
                $strBodyText .= "Last Name: " . $arrData['last_name'] . "\n";

                if (!empty($arrData['phone_number'])) {
                    $strBodyText .= "Phone Number: " . $arrData['phone_number'] . "\n";
                }

                $strBodyText .= "\nMessage: " . $arrData['message'];

                $config = $this->config->resources->mail->contactForm;
                $objMail = new Zend_Mail();
                $objMail->setBodyText($strBodyText);
                $objMail->setBodyHtml(stripslashes(nl2br(utf8_decode($strBodyText))));
                $objMail->setFrom($arrData['email_address'], $arrData['first_name'] . ' ' . $arrData['last_name']);
                $objMail->addTo($config->To->email,$config->To->name);
                $objMail->setSubject('Love Week Contact Submission');

                if ($objMail->send()) {
                    $this->_helper->FlashMessenger(array('success' => 'Your message has been sent! Expect a reply shortly.'));
                } else {
                    $this->_helper->FlashMessenger(array('danger' => 'Something went wrong with your contact submission.'));
                }

                $this->view->boolSent = true;
            }
        }
    }
}
