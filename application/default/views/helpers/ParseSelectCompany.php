<?php

require_once 'models/companies.phpm';

class Zend_View_Helper_ParseSelectCompany
{
	public $view;

	public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

	public function parseSelectCompany($intValue = 0)
	{
        $objCompanies = Companies::getCompaniesForSelect();

		if (count($objCompanies)) {
            $arrAttribs['class'] = 'userinput';

            $arrOptions[0] = '';

            foreach ($objCompanies AS $objCompany) {
               $arrOptions[$objCompany->id] = $objCompany->company_name;
            }

            return $this->view->formSelect('company_id', $intValue, $arrAttribs, $arrOptions);
		} else{
		    return 'There are no companies at this time.';
		}
    }
}
