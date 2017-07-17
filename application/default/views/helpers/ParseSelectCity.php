<?php

require_once 'models/venues.phpm';

class Zend_View_Helper_ParseSelectCity
{
	public $view;

	public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

	public function parseSelectCity($strValue = '')
	{
        $sql = "SELECT DISTINCT venue_city FROM tbl_venues WHERE venue_city != '' ORDER BY venue_city ASC";

        $objRowset = Myriad_Data::getAdapter()->query($sql)->fetchAll(Zend_Db::FETCH_OBJ);

		if (count($objRowset)) {
            $arrAttribs = array();

            $arrOptions[''] = '';

            foreach ($objRowset as $objRow) {
               $arrOptions[$objRow->venue_city] = $objRow->venue_city;
            }

            return $this->view->formSelect('city', $strValue, $arrAttribs, $arrOptions);
		} else{
		    return 'There are no cities at this time.';
		}
    }
}
