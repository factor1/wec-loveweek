<?php

/**
 * Myriad/Mailer.php
 *
 * Simple Form Mail Script (Version 1.1 :: Created 2008-07-03)
 * Updated 6/4/2010 for PHP 5.3.2 compatibility
 * @author Myriad Interactive, LLC.
 */

require_once 'Zend/Mail.php';
require_once 'Zend/Registry.php';
require_once 'Myriad/Mailer/Exception.php';
require_once 'Myriad/Log.php';

class Myriad_Mailer
{
	private $_strRecipient;
	private $_strBcc;
	private $_strSubject;
    private $_strEmail;
	private $_arrData;
	private $_arrAttachments;
	private $_blnCaptcha = true;
	private $_arrErrors = array();

	public function __construct($arrData = '')
	{
        if (is_array($arrData)) {
	        $this->_arrData = $arrData;
        }
	}

    public function ignoreCaptcha()
    {
        $this->_blnCaptcha = false;
    }

    public function setRecipient($strRecipient)
    {
        $this->_strRecipient = strtolower(trim($strRecipient));
    }

    public function setBcc($strBcc)
    {
        $this->_strBcc = strtolower(trim($strBcc));
    }

    public function setSubject($strSubject, $strSecondarySubject)
    {
        if ($strSubject == '') {
            $this->_strSubject = trim($strSecondarySubject);
        } else {            
            $this->_strSubject = trim($strSubject);
        }
    }

    public function setEmail($strEmail)
    {
        $this->_strEmail = strtolower(trim($strEmail));
    }

    public function addAttachment($strLocation, $strFilename, $strType = 'application/msword')
    {
    	$this->_arrAttachments[] = array(
    	    'type'     => $strType,
    	    'location' => $strLocation,
    	    'filename' => $strFilename
    	);
    }

    public function getErrors()
    {
        return $this->_arrErrors;
    }

    public function send()
    {
        if (!is_array($this->_arrData) || (count($this->_arrData) == 0)) {
            $this->_arrErrors[] = 'No data was found';
            return false;
        }

        if ($this->_isValidReferer() == false) {
            $this->_arrErrors[] = 'Invalid Host';
        }

        if ($this->_isValidEmail($this->_arrData['email']) == false) {
            $this->_arrErrors[] = 'Invalid Email Address';
        }

        if ($this->_blnCaptcha && ($this->_isValidCaptcha($this->_arrData['captcha']) == false)) {
            $this->_arrErrors[] = 'Invalid Security Image';
        }

        $strMessage = '';

        foreach ($this->_arrData as $key => $value) {
            if (($key == 'email') && ($value != '')) {
                $this->setEmail($value);
            } else if (($key == 'subject') && ($value != '')) {
                $this->setSubject($value);
            } else if ((strtolower($key) == 'captcha') || (strtolower($key) == 'submit') || (strtolower($key) == 'redirect') || (strtolower($key) == 'x') || (strtolower($key) == 'y')) {
                // ignore these keys
            } else {
                if (is_array($value)) {
                    $strMessage .= strtoupper($key) . ":\n";
                    for ($i = 0; $i < count($value); $i++) {
                        $strMessage .= " - " . stripslashes($value[$i]) . "\n";
                    }
                    $strMessage .= "\n";
                } else if ($value != '') {
                    $key = str_replace('_', ' ', $key);
                    $key = str_replace('-', ' ', $key);
                    $strMessage .= "" . strtoupper($key) . ":\n" . stripslashes($value) . "\n\n";
                }
            }
        }

        $strMessage = 'The following information was sent by ' . $this->_strEmail . ' on ' . date('F jS, Y') . ' at ' . date('g:ia') . "\n\n" . $strMessage;

        $objMail = new Zend_Mail();
        $objMail->setBodyText($strMessage);
        $objMail->setFrom($this->_strEmail, $this->_strEmail);

        $arrRecipients = explode(',', $this->_strRecipient);
        foreach ($arrRecipients as $strRecipient) {
            if ($this->_isValidEmail(trim($strRecipient))) {
                $objMail->addTo(trim($strRecipient));
            }
        }

        $arrBcc = explode(',', $this->_strBcc);
        foreach ($arrBcc as $strBcc) {
            if ($this->_isValidEmail(trim($strBcc))) {
                $objMail->addBcc(trim($strBcc));
            }
        }

        $objMail->setSubject($this->_strSubject);

        if (count($this->_arrAttachments)) {
        	foreach($this->_arrAttachments as $arrAttachment) {
        		$strContents = file_get_contents($arrAttachment['location']);
        		$attachment = $objMail->createAttachment($strContents);
        		$attachment->type = $arrAttachment['type'];
        		$attachment->filename = $arrAttachment['filename'];
        	}
        }

        if (count($this->_arrErrors) == 0) {
            try {
                $objMail->send();
                return true;
            } catch (Zend_Exception $e) {
                $this->_arrErrors[] = $e->getMessage();
            }
        }

        return false;
    }

    private function _isValidReferer()
    {
        $strHost    = getenv('HTTP_HOST');
        $strReferer = getenv('HTTP_REFERER');

        if (substr($strReferer, 0, 7) == 'http://') {
            $strReferer = substr($strReferer, 7);
        }

        if (substr($strReferer, 0, 8) == 'https://') {
            $strReferer = substr($strReferer, 8);
        }

        if (substr($strReferer, 0, strlen($strHost)) == $strHost) {
            return true;
        }

        return false;
    }

    private function _isValidCaptcha($str)
    {
        $objSession = Zend_Registry::get('session');

        if (isset($objSession->captcha) && ($objSession->captcha == $str)) {
            unset($objSession->captcha);
            return true;
        }

        return false;
    }

    private function _isValidEmail($strEmail)
    {
        return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$/i", $strEmail);
    }
}
