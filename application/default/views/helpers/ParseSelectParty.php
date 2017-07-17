<?php

class Zend_View_Helper_ParseSelectParty
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function parseSelectParty($strValue = '')
    {
        $strName = 'party';

        // attributes
        $arrAttribs = array();
        $arrAttribs['id'] = $strName;

        // options
        $arrOptions = array();

        $arrOptions[0] = '';

		$arrOptions = array_merge($arrOptions, $this->getPartiesArray());

        return $this->view->formSelect($strName, $strValue, $arrAttribs, $arrOptions);
    }

    private function getPartiesArray() {
		$arrParties = array(
            'D' => 'Democrat',
            'R' => 'Republican',
            'I' => 'Independent',
        );

        return $arrParties;
    }
    
}
