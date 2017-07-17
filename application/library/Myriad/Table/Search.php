<?php

/**
 * Myriad/Table/Search.php
 *
 * @author Myriad Interactive, LLC.
 * @version 1.0
 * @updated 7/15/2010
 */

require_once 'Zend/Auth/Adapter/DbTable.php';
require_once 'Myriad/Table/Search/Exception.php';
require_once 'Myriad/Utils.php';

class Myriad_Table_Search
{
    public static function addKeywordFilter($objSelect, $strKeywords, $strField)
    {
        if (!$strKeywords || !$strField) {
            return $objSelect;
        }

        if (is_array($strKeywords)) {
            $arrKeywords = $strKeywords;
        } else {
            $arrKeywords = explode(' ', $strKeywords);
        }

        if (is_array($strField)) {
            $arrFields = $strField;
        } else {
            $arrFields = array($strField);
        }

        $blnQuotedString = false;
        $intTokenNum = 0;
        $arrTokens = array();

        // first we loop through the keywords array to create tokens

        for ($i = 0; $i < count($arrKeywords); $i++) {
            if (!isset($arrTokens[$intTokenNum])) {
                $arrTokens[$intTokenNum] = '';
            }

            $strKeyword = stripslashes($arrKeywords[$i]);

            if (preg_match('/^\"/', $strKeyword) || preg_match('/^[+-]\"/', $strKeyword)) {
                $blnQuotedString = true;
            }

            if ($blnQuotedString) {
                $arrTokens[$intTokenNum] .= str_replace('"', '', $strKeyword) . ' ';
            } else {
                $arrTokens[$intTokenNum++] = $strKeyword;
            }

            if (preg_match('/\"$/', $strKeyword)) {
                $blnQuotedString = false;
                $arrTokens[$intTokenNum] = trim($arrTokens[$intTokenNum]);
                $intTokenNum++;
            }
        }

        // now we add to the select object using the tokens

        for ($i = 0; $i < count($arrTokens); $i++) {

            $strWhere = '';

            for ($x = 0; $x < count($arrFields); $x++) {
                $strToken = str_replace(' $', '', $arrTokens[$i]);
                $strField = $arrFields[$x];

                if (preg_match('/^\\+/', $strToken)) {
                    $strToken = preg_replace('/^\\+/', '', $strToken);
                    $strWhere .= Myriad_Data::getAdapter()->quoteInto("$strField LIKE ?", "%$strToken%");
                    if ($x < count($arrFields) - 1) {
                        $strWhere .= ' OR ';
                    }
                } else if (preg_match('/^\\-/', $strToken)) {
                    $strToken = preg_replace('/^\\-/', '', $strToken);
                    $strWhere .= Myriad_Data::getAdapter()->quoteInto("$strField NOT LIKE ?", "%$strToken%");
                    if ($x < count($arrFields) - 1) {
                        $strWhere .= ' OR ';
                    }
                } else {
                    $strWhere .= Myriad_Data::getAdapter()->quoteInto("$strField LIKE ?", "%$strToken%");
                    if ($x < count($arrFields) - 1) {
                        $strWhere .= ' OR ';
                    }
                }
            }

            $objSelect->where($strWhere);
        }

        return $objSelect;
    }

    public static function addLookupFilter($objSelect, $strTable, $strLookupTable, $strLookupId, $strFilterField, $strFilterValue, $blnOr = false)
    {
        // Set the alias of the table name to a random MD5 hash so we can easily reference that in our filter
        // 3-18-2010 - Had to add "Z" to the beginning in case the name of the table alias starts with numbers (lol 10 out of 16 times)

        $strAlias = 'Z' . md5(time() . rand(1,43534543) . $strTable . $objSelect . $strFilterValue);

        /*
        $objSelect->joinInner(array($strAlias=>$strLookupTable), "$strTable.id = $strAlias.$strLookupId", null);
        $objSelect->orWhere("$strAlias.$strFilterField = $strFilterValue");
        */

        if ($blnOr != false) {
            echo '$blnOr = true is not supported yet!';
        } else {
            $objSelect->joinInner(array($strAlias=>$strLookupTable), "$strTable.id = $strAlias.$strLookupId AND $strAlias.$strFilterField = $strFilterValue", null);
    	}

        return $objSelect;
    }
}
