<?php

require_once 'models/users.phpm';

class Zend_View_Helper_ParseSelectUser
{
	public $view;

	public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

	public function parseSelectUser($intValue = 0)
	{
        $objUsers = Users::getUsersForSelect();

		if (count($objUsers)) {
            $arrAttribs = array();

            $arrOptions[0] = '';

            foreach ($objUsers AS $objUser) {
               $arrOptions[$objUser->id] = trim($objUser->user_first_name . ' ' . $objUser->user_last_name);
            }

            return $this->view->formSelect('user_id', $intValue, $arrAttribs, $arrOptions);
		} else{
		    return 'There are no clients at this time.';
		}
    }
}
