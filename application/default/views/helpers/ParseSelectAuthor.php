<?php

require_once 'models/users.phpm';

class Zend_View_Helper_ParseSelectAuthor
{
	public $view;

	public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

	public function parseSelectAuthor($intValue = 0)
	{
        $objTable = new Users();
        $objRowset = $objTable->fetchAll($objTable->select()->where('user_is_admin = 1')->order('user_first_name ASC'));

		if (count($objRowset)) {
            $arrAttribs = array();

            $arrOptions[0] = '';

            foreach ($objRowset as $objRow) {
               $arrOptions[$objRow->id] = trim($objRow->user_first_name . ' ' . $objRow->user_last_name);
            }

            return $this->view->formSelect('user_id', $intValue, $arrAttribs, $arrOptions);
		} else{
		    return 'There are no users at this time.';
		}
    }
}
