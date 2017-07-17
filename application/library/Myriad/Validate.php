<?php

/**
 * Myriad/Validate.php
 *
 * Provides static methods for validating and filtering input data
 *
 * @author Myriad Interactive, LLC.
 */

require_once 'Zend/Registry.php';
require_once 'Zend/Validate.php';
require_once 'Zend/Validate/StringLength.php';
require_once 'Zend/Validate/Alnum.php';
require_once 'Zend/Validate/EmailAddress.php';
require_once 'Zend/Filter.php';
require_once 'Zend/Exception.php';
require_once 'Myriad/Log.php';

class Myriad_Validate {

    const USERNAME_MIN_LENGTH = 4;
    const USERNAME_MAX_LENGTH = 64;
    const PASSWORD_MIN_LENGTH = 4;
    const PASSWORD_MAX_LENGTH = 32;

    public function __construct()
    {
        // construct
    }


    /**
     * Validates the input username
     * Checks for string length and alphanumneric
     *
     * @param string $username
     * @return boolean
     */

    public static function isValidUsername($username)
    {
        try {
            $validatorChain = new Zend_Validate();
            $validatorChain->addValidator(new Zend_Validate_StringLength(self::USERNAME_MIN_LENGTH , self::USERNAME_MAX_LENGTH));
            $validatorChain->addValidator(new Zend_Validate_Alnum());

            if ($validatorChain->isValid($username)) {
                return true;
            }
        } catch (Zend_Exception $e) {
            Myriad_Log::getInstance()->writeLog($e->getMessage(), Zend_Log::ERR);
        }

        return false;
    }


    /**
     * Validates valid password format
     * Checks string length and alphanumeric chars
     *
     * @param string $password
     * @return boolean
     */

    public static function isValidPassword($password)
    {
        try {
            $validatorChain = new Zend_Validate();
            $validatorChain->addValidator(new Zend_Validate_StringLength(self::PASSWORD_MIN_LENGTH, self::PASSWORD_MAX_LENGTH));
            $validatorChain->addValidator(new Zend_Validate_Alnum());

            if ($validatorChain->isValid($password)) {
                return true;
            }
        } catch (Zend_Exception $e) {
            Myriad_Log::getInstance()->writeLog($e->getMessage(), Zend_Log::ERR);
        }

        return false;
    }


    /**
     * Filters input for the title field.
     * Returns a string with all html and php tags filtered out
     *
     * @param string $title
     * @return string
     */

    public static function filterTitle($title)
    {
        $filteredTitle = null;

        try {
            $filteredTitle = Zend_Filter::get($title, 'StripTags');
        } catch (Zend_Exception $e) {
            Myriad_Log::getInstance()->writeLog($e->getMessage(), Zend_Log::ERR);
        }

        return $filteredTitle;
    }


    /**
     * Filters input content converting characters to their corresponding HTML entity
     * equivalents where they exist
     *
     * @param string $content
     * @return string
     */

    public static function filterContent($content)
    {
        $filteredContent = null;

        try {
            $filteredContent = Zend_Filter::get($content, 'HtmlEntities');
        } catch (Zend_Exception $e) {
            Myriad_Log::getInstance()->writeLog($e->getMessage(), Zend_Log::ERR);
        }
        return $filteredContent;
    }


    /**
     * Checks for valid title length
     *
     * @param string $title
     * @return boolean
     */

    public static function isValidTitle($title)
    {
        $validator = new Zend_Validate_StringLength(self::TITLE_MIN_LENGTH, self::TITLE_MAX_LENGTH);

        if ($validator->isValid($title)) {
            return true;
        }

        return false;
    }


    /**
     * Checks valid length for comment
     *
     * @param string $comment
     * @return boolean
     */

    public static function isValidComment($comment)
    {
        $validator = new Zend_Validate_StringLength(self::COMMENT_MIN_LENGTH, self::COMMENT_MAX_LENGTH);

        if ($validator->isValid($comment)) {
            return true;
        }

        return false;
    }


    /**
     * Checks if valid email address input
     *
     * @param string $email
     * @return boolean
     */

    public static function isValidEmail($email)
    {
        $validator = new Zend_Validate_EmailAddress();

        if ($validator->isValid($email)) {
            return true;
        }

        return false;
    }
}
