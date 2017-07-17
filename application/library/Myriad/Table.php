<?php

/**
 * Myriad/Table.php
 *
 * @author Myriad Interactive, LLC.
 * @version 1.2 - added compatibility for PHP 1.3.0
 * @version 1.3 - added lookup search functionality
 * @updated 12/8/2010
 */

require_once 'Zend/Auth/Adapter/DbTable.php';
require_once 'Myriad/Table/Exception.php';
require_once 'Myriad/Table/Search.php';
require_once 'Myriad/Utils.php';

class Myriad_Table extends Zend_Db_Table_Abstract
{
    private $_strKeywords = '';
    private $_strField    = '';
    private $_strSort     = '';
    private $_strDir      = 'ASC';
    private $_intPage     = 0;
    private $_intLimit    = 0;
    private $_arrFilters  = array();

    protected $_strLookupColumn = '';


    /**
     * Set Methods
     */

    public function setKeywords($strKeywords = '')
    {
        $this->_strKeywords = (is_array($strKeywords)) ? $strKeywords : trim($strKeywords);
    }

    public function setField($strField = '')
    {
        $this->_strField = (is_array($strField)) ? $strField : trim($strField);
    }

    public function setSort($strSort = '')
    {
        $this->_strSort = trim($strSort);
    }

    public function setDir($strDir = 'ASC')
    {
        $strDir = strtoupper($strDir);
        $this->_strDir = ($strDir == 'DESC') ? 'DESC' : 'ASC';
    }

    public function setPage($intPage = 1)
    {
        $this->_intPage = (int) $intPage;
    }

    public function setLimit($intLimit = 10)
    {
        $this->_intLimit = (int) $intLimit;
    }

    public function setFilters($arrFilters)
    {
        $this->_arrFilters = $arrFilters;
    }

    public function addFilter($strField, $strValue)
    {
        $this->_arrFilters[$strField] = $strValue;
    }


    /**
     * Get Methods
     */

    public function getKeywords()
    {
        return Myriad_Utils::stripSlashes($this->_strKeywords);
    }

    public function getField()
    {
        return Myriad_Utils::stripSlashes($this->_strField);
    }

    public function getSort()
    {
        return $this->_strSort;
    }

    public function getDir()
    {
        return $this->_strDir;
    }

    public function getPage()
    {
        return $this->_intPage;
    }

    public function getLimit()
    {
        return $this->_intLimit;
    }

    public function getFilters()
    {
        return $this->_arrFilters;
    }

    public function getFilter($strFilterName)
    {
        return (exists($this->_arrFilters[$strFilterName])) ? $this->_arrFilters[$strFilterName] : null;
    }


    /**
     * Add Search to Select
     */

    public function addSearchToSelect($objSelect, $strKeywords = '', $strField = '')
    {
        if ($strKeywords == '' || $strField == '') {
            $strKeywords = $this->_strKeywords;
            $strField    = $this->_strField;
        }

        return Myriad_Table_Search::addKeywordFilter($objSelect, $strKeywords, $strField);
    }


    /**
     * Add Lookup Search to Select
     */

    public function addLookupSearchToSelect($objSelect, $strTable, $strLookupTable, $strLookupId, $strFilterField, $strFilterValue)
    {
        return Myriad_Table_Search::addLookupFilter($objSelect, $strTable, $strLookupTable, $strLookupId, $strFilterField, $strFilterValue);
    }


    /**
     * Add Order to Select
     */

    public function addOrderToSelect($objSelect)
    {
        if ($this->_strSort) {
            $objSelect->order("$this->_strSort $this->_strDir");
        }

        return $objSelect;
    }


    /**
     * Add Limit to Select
     */

    public function addLimitToSelect($objSelect)
    {
        if (($this->_intPage > 0) && ($this->_intLimit > 0)) {
            $objSelect->limitPage($this->_intPage, $this->_intLimit);
        }

        return $objSelect;
    }


    /**
     * Delete Checked
     */

    public function deleteChecked($arrChecked = array(), $strObject = 'record')
    {
        $strMessage = '';
        $intCount   = 0;

        if (is_array($arrChecked) && (count($arrChecked) > 0)) {
            $objRowset = $this->find($arrChecked);

            foreach ($objRowset as $objRow) {
                if (!empty($objRow->account_id) && ($objRow->account_id != Zend_Auth::getInstance()->getIdentity()->account_id)) {
                    // unauthorized
                } else {
                    $objRow->delete();
                    $intCount++;
                }
            }

            if ($intCount > 1) {
                $strMessage = $intCount . ' ' . $strObject . 's were deleted.';
            } else if ($intCount == 1) {
                $strMessage = '1 ' . $strObject . ' was deleted.';
            }
        } else {
            $strMessage = 'Nothing was deleted. You must select at least one record.';
        }

        return $strMessage;
    }
}
