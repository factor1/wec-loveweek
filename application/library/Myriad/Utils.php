<?php

/**
 * Myriad/Utils.php
 *
 * @author Myriad Interactive, LLC.
 * @version 1.2 - added compatibility for PHP 1.3.0
 * @updated 6/3/2010
 */

require_once 'Myriad/Utils/Exception.php';

class Myriad_Utils
{
    /**
     * Validation
     */

    public static function isValidEmail($str)
    {
        return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$/i", $str);
    }


    public static function isValidUrl($str)
    {
        // NOTE: DONT FORGET TO TEST
        return preg_match('#^http\\:\\/\\/[a-z0-9\-]+\.([a-z0-9\-]+\.)?[a-z]+#i', $str, $matches);
    }


    public static function isAlpha($str)
    {
        return eregi("^[a-z]", $str);
    }


    public static function isNumeric($str)
    {
        return eregi("^[0-9]", $str);
    }


    public static function isInteger($str)
    {
        return eregi("^([0-9]+)$", $str);
    }


    public static function isAlphaNumeric($str)
    {
        return eregi("^[a-z0-9]", $str);
    }


    public static function convertSmartQuotes($str, $bln_addslashes = 0)
    {
        $str = stripslashes($str);

        $arr_search = array(
            chr(96),    // apostrophe, curly left single quote
            chr(132),   // quote, lower right curly quote
            chr(133),   // ellipsis
            chr(145),   // apostrophe, curly left single quote
            chr(146),   // apostrophe, curly right single quote
            chr(147),   // quote, left curly quote
            chr(148),   // quote, right curly quote
            chr(150),   // en dash (not sure which is which)
            chr(151),   // em dash (not sure which is which)
            '&#8230;',  // ellipsis
            '&#8216;',  // apostrophe, curly left single quote
            '&#8217;',  // apostrophe, curly right single quote
            '&#8220;',  // quote, left curly quote
            '&#8221;',  // quote, right curly quote
            '&#8211;',  // en dash
            '&#8212;',  // em dash
        );

        $arr_replace = array(
            "'",
            '"',
            '...',
            "'",
            "'",
            '"',
            '"',
            '-',
            '-',
            '...',
            "'",
            "'",
            '"',
            '"',
            '-',
            '-',
        );

        if ($bln_addslashes == 1) {
            return addslashes(str_replace($arr_search, $arr_replace, $str));
        } else {
            return str_replace($arr_search, $arr_replace, $str);
        }
    }


    public static function stripSlashes($var = '')
    {
        if (is_array($var)) {
            foreach($var as $key => $value) {
                if (is_array($value)) {
                    $var[$key] = $value;
                } else {
                    $var[$key] = stripslashes($value);
                }
            }
        } else {
            $var = stripslashes($var);
        }

        return $var;
    }


    /**
     * Date Functions
     */

    public static function getGmtDate($format = 'Y-m-d', $gmepoch, $timezone)
    {
        return @gmdate($format, $gmepoch + (3600 * $timezone));
    }

    public static function getTeaser($strContent, $intWords = 50, $strEllipsis = '...')
    {
        $arrWords = explode(' ', $strContent);

        if (count($arrWords) > $intWords) {
            array_splice($arrWords, $intWords);
            $strContent = implode(' ', $arrWords);
            if ($strEllipsis) {
                $strContent .= $strEllipsis;
            }
        }

        return $strContent;
    }

    public static function isMySqlDate($str)
    {
        return checkdate(substr($str, 5, 2), substr($str, 8, 2), substr($str, 0, 4));
    }


    public static function calculateDateDifference($sql_date1, $sql_date2 = 'now')
    {
        if ($sql_date1 == "now") { $sql_date1 = date ("Y/m/d", time ()); }
        if ($sql_date2 == "now") { $sql_date2 = date ("Y/m/d", time ()); }

        $thn1 = substr($sql_date1, 0, 4);
        $bln1 = substr($sql_date1, 5, 2);
        $tgl1 = substr($sql_date1, 8, 2);

        $thn2 = substr($sql_date2, 0, 4);
        $bln2 = substr($sql_date2, 5, 2);
        $tgl2 = substr($sql_date2, 8, 2);

        $tanggal1 = mktime(0, 0, 0, $bln1, $tgl1, $thn1);
        $tanggal2 = mktime(0, 0, 0, $bln2, $tgl2, $thn2);

        $tanggal = ($tanggal2 - $tanggal1) / 86400;
        return ($tanggal);
    }

    public static function calculateAge($strBirthdate)
    {
        list($strYear, $strMonth, $strDay) = explode('-', $strBirthdate);

        $intYearDiff  = date('Y') - $strYear;
        $intMonthDiff = date('m') - $strMonth;
        $intDayDiff   = date('d') - $strDay;

        if ($intDayDiff < 0 || $intMonthDiff < 0) {
            $intYearDiff--;
        }

        return $intYearDiff;
    }


    /**
     * Formatting
     */

    public static function addCommas($str_number, $hideDecimal = 0)
    {

        // check to see if number is negative...
        if (preg_match("/^\-/", $str_number)) {
            $is_negative = 1;
            $str_number = str_replace('-', '', $str_number);
        } else {
            $is_negative = 0;
        }

        // remove commas...
        $str_number = str_replace(',', '', $str_number);

        // split the string into two parts...
        if (preg_match("/^\-/", $str_number)) {
            $arr_number = preg_split('/[\.]/', $str_number);
            $first      = $arr_number[0];
            $second     = $arr_number[1];
        } else {
            $first      = $str_number;
            $second     = '';
        }

        // format decimal portion...
        if (strlen($second) < 1) {
            $second = "00";
        } else if (strlen($second) < 2) {
            // add an extra zero to the end
            $second = $second . "0";
        } else if (strlen($second) > 2) {
            // just display to two decimal places (no rounding in this version)...
            $second = substr($second, 0, 2);
        }

        // format integer portion...
        $length = strlen($first);

        if ($length > 3) {
            $loop_count = intval(($length / 3));
            $section_length = -3;

            for ($i = 0; $i < $loop_count; $i++) {
                $sections[$i]   = substr($first, $section_length, 3);
                $section_length = $section_length - 3;
            }

            $stub = ($length % 3);

            if ($stub != 0) {
                  $sections[$i] = substr($first, 0, $stub);
            }

            $first = implode(',', array_reverse($sections));
        }

        // return a value...
        $str_number = ($hideDecimal == 1) ? "$first" : "$first.$second";

        if ($is_negative) {
            $str_number = "-$str_number";
        }

        return $str_number;
    }


    public static function removeCommas($str)
    {
        return str_replace(',', '', $str);
    }


    public static function formatDate($str_date, $str_format = 'F j, Y')
    {
        if (strlen($str_date) > 10) {
            $str_date = substr($str_date, 0, 10);
        }

        list($str_year, $str_month, $str_day) = explode('-', $str_date);

        if (checkdate($str_month, $str_day, $str_year)) {
            return (date($str_format, mktime(0, 0, 0, $str_month, $str_day, $str_year)));
        } else {
            return 'Invalid Date';
        }
    }


    public static function formatDateTime($str_datetime, $str_format = 'M j, Y  g:i A')
    {
        $str_year   = substr($str_datetime, 0, 4);
        $str_month  = substr($str_datetime, 5, 2);
        $str_day    = substr($str_datetime, 8, 2);
        $str_hour   = substr($str_datetime, 11, 2);
        $str_minute = substr($str_datetime, 14, 2);
        $str_second = substr($str_datetime, 17, 2);

        return (date($str_format, mktime($str_hour, $str_minute, $str_second, $str_month, $str_day, $str_year)));
    }


    public static function formatCurrency($str_number) {
        // check to see if number is negative...
        if (preg_match("/^\-/", $str_number)) {
            $is_negative = 1;
            $str_number = preg_replace("|\-|", "", $str_number);
        } else {
            $is_negative = 0;
        }

        // remove commas...
        $str_number = preg_replace("|\,|", "", $str_number);

        // split the string into two parts...

        $arr_number = preg_split('/[\.]/', $str_number);
        $first      = $arr_number[0];
        $second     = isset($arr_number[1]) ? $arr_number[1] : 0;

        // format decimal portion...
        if (strlen($second) < 1) {
            $second = "00";
        } else if (strlen($second) < 2) {
            // add an extra zero to the end
            $second = $second . "0";
        } else if (strlen($second) > 2) {
            // just display to two decimal places (no rounding in this version)...
            $second = substr($second, 0, 2);
        }

        // format integer portion...
        $length = strlen($first);

        if ($length > 3) {
            $loop_count = intval($length / 3);
            $section_length = -3;

            for ($i = 0; $i < $loop_count; $i++) {
                $sections[$i]   = substr($first, $section_length, 3);
                $section_length = $section_length - 3;
            }

            $stub = ($length % 3);

            if ($stub != 0) {
                $sections[$i] = substr($first, 0, $stub);
            }

            $first = implode(',', array_reverse($sections));
        }

        // return a value...
        $str_number = '$' . $first . '.' . $second;

        if ($is_negative) {
            $str_number = '(' . $str_number . ')';
        }

        return $str_number;
    }


    public static function formatPhone($str)
    {
        if (strlen($str) == 7) {
            $str =  substr($str, 0, 3) . '-' .  substr($str, 3, 4);
        } else if (strlen($str) == 10) {
            $str = '(' . substr($str, 0, 3) . ') ' .  substr($str, 3, 3) . '-' . substr($str, 6, 4);
        }

        return $str;
    }


    public static function createHyperlinks($str)
    {
    	$str = ' ' . $str;
    	$str = preg_replace("#([\t\r\n ])([a-z0-9]+?){1}://([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i", '\1<a href="\2://\3">\2://\3</a>', $str);
    	$str = preg_replace("#([\t\r\n ])(www|ftp)\.(([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i", '\1<a href="http://\2.\3">\2.\3</a>', $str);
    	$str = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $str);
    	$str = substr($str, 1);

    	return $str;
    }


    public static function createHyperlinksAlternateVersion($str)
    {
        // NOTE DONT FORGET TO TEST
        $str = " " . $str;
        $str = preg_replace("#([\n ])([a-z]+?)://([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]+)#i", "\\1<a href=\"\\2://\\3\" target=\"_blank\">\\2://\\3</a>", $str);
        $str = preg_replace("#([\n ])www\.([a-z0-9\-]+)\.([a-z0-9\-.\~]+)((?:/[a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]*)?)#i", "\\1<a href=\"http://www.\\2.\\3\\4\" target=\"_blank\">www.\\2.\\3\\4</a>", $str);
        $str = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)?[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $str);
        $str = substr($str, 1);
        return $str;
    }


    public static function highlightKeywords($str, $arr_keywords)
    {
        // NOTE DONT FORGET TO TEST
        $regex = '#(?!<.*?)(%s)(?![^<>]*?>)#si';
        $arr_colors = array('#FFF000', '#FF0000', '#FFFF00', '#0000FF', '#000FFF', '#00FF00', '#00FFF0', '#0FFF00');
        $i = 0;

        foreach ($arr_keywords as $str_keyword) {
            if ($i > count($arr_colors) - 1) {
                $i = 0;
            }
            $pattern     = sprintf($regex, $str_keyword);
            $replacement = '<span style="background: "' . $arr_colors[$i] . '">' . '\1' . '</span>';
            $str         = preg_replace($pattern, $replacement, $str);
            $i++;
        }

        return $text;
    }


    public static function addHyperlinks($strText, $intOpenInWindow=0) {
      // simple check for text...
      if (trim($strText) == "") return false;

      // decide whether or not we add target=window to the href tag...
      if ($intOpenInWindow == 1) $strTarget = "target=popup";
      else $strTarget = "";

      // prepare text...
      $strText = stripslashes($strText);

      // split text into words...
      $arrText = explode(" ", $strText);

      // define regular expression patterns and replace strings...
      $pattern1 = "#(http:\/\/[^ ]+)#i";
      $replace1 = '<a href="$1">$1</a>';

      $pattern2 = "/@([A-Za-z0-9_]+)/is";
      $replace2 = " <a href='http://twitter.com/$1'>@$1</a>";

      $pattern3 = "/#([A-Aa-z0-9_-]+)/is";
      $replace3 = " <a href='http://hashtags.org/$1'>#$1</a>";

      $pattern4 = "/(((http[s]?:\/\/)|(www\.))?(([a-z][-a-z0-9]+\.)?[a-z][-a-z0-9]+\.[a-z]+(\.[a-z]{2,2})?)\/?[a-z0-9._\/~#&=;%+?-]+[a-z0-9\/#=?]{1,1})/is";
      $replace4 = " <a href='$1'>$1</a>";

      // make replacements...
      for($i=0; $i<count($arrText); $i++) {
        $strWord = $arrText[$i];
        $arrWords = explode("\n", $strWord);

        if (count($arrWords) > 1) {
          for($j=0; $j<count($arrWords); $j++) {
            $strWordTemp = $arrWords[$j];
            $strWordTemp = preg_replace($pattern1, $replace1, $strWordTemp);
            $strWordTemp = preg_replace($pattern2, $replace2, $strWordTemp);
            $strWordTemp = preg_replace($pattern3, $replace3, $strWordTemp);
            $strWordTemp = preg_replace($pattern4, $replace4, $strWordTemp);
            $arrWords[$j] = $strWordTemp;
          }
          $strWord = implode("\n", $arrWords);
        } else {
          $strWord = preg_replace($pattern1, $replace1, $strWord);
          $strWord = preg_replace($pattern2, $replace2, $strWord);
          $strWord = preg_replace($pattern3, $replace3, $strWord);
          $strWord = preg_replace($pattern4, $replace4, $strWord);
        }

        $arrText[$i] = $strWord;
      }

      // rebuild text...
      $strText = implode(" ", $arrText);

      return $strText;
    }


    public static function isValidZip($str_state, $str_zip5)
    {
        $str_state = strtoupper(trim($str_state));

        $arr_states = array (
            "AK" => array("9950099929"),
            "AL" => array("3500036999"),
            "AR" => array("7160072999", "7550275505"),
            "AZ" => array("8500086599"),
            "CA" => array("9000096199"),
            "CO" => array("8000081699"),
            "CT" => array("0600006999"),
            "DC" => array("2000020099", "2020020599"),
            "DE" => array("1970019999"),
            "FL" => array("3200033999", "3410034999"),
            "GA" => array("3000031999"),
            "HI" => array("9670096798", "9680096899"),
            "IA" => array("5000052999"),
            "ID" => array("8320083899"),
            "IL" => array("6000062999"),
            "IN" => array("4600047999"),
            "KS" => array("6600067999"),
            "KY" => array("4000042799", "4527545275"),
            "LA" => array("7000071499", "7174971749"),
            "MA" => array("0100002799"),
            "MD" => array("2033120331", "2060021999"),
            "ME" => array("0380103801", "0380403804", "0390004999"),
            "MI" => array("4800049999"),
            "MN" => array("5500056799"),
            "MO" => array("6300065899"),
            "MS" => array("3860039799"),
            "MT" => array("5900059999"),
            "NC" => array("2700028999"),
            "ND" => array("5800058899"),
            "NE" => array("6800069399"),
            "NH" => array("0300003803", "0380903899"),
            "NJ" => array("0700008999"),
            "NM" => array("8700088499"),
            "NV" => array("8900089899"),
            "NY" => array("0040000599", "0639006390", "0900014999"),
            "OH" => array("4300045999"),
            "OK" => array("7300073199", "7340074999"),
            "OR" => array("9700097999"),
            "PA" => array("1500019699"),
            "RI" => array("0280002999", "0637906379"),
            "SC" => array("2900029999"),
            "SD" => array("5700057799"),
            "TN" => array("3700038599", "7239572395"),
            "TX" => array("7330073399", "7394973949", "7500079999", "8850188599"),
            "UT" => array("8400084799"),
            "VA" => array("2010520199", "2030120301", "2037020370", "2200024699"),
            "VT" => array("0500005999"),
            "WA" => array("9800099499"),
            "WI" => array("4993649936", "5300054999"),
            "WV" => array("2470026899"),
            "WY" => array("8200083199")
        );

        // make sure zipcode is 5-digits

        if (!preg_match("([0-9]{5})", $str_zip5)) {
            return false;
        }

        // make sure zipcode falls within state range

        if (isset($arr_states[$str_state])) {
            foreach ($arr_states[$str_state] AS $str_range) {
                if (($str_zip5 >= substr($str_range, 0, 5)) && ($str_zip5 <= substr($str_range, 5))) {
                    return true;
                }
            }
        } else {
            return true;
        }

        return false;
    }


    public static function buildSearchSql($varKeywords, $varFields)
    {
        if (!$varKeywords || !$varFields) {
            return '';
        }

        if (is_array($varKeywords)) {
            $arrKeywords = $varKeywords;
        } else {
            $arrKeywords = split(' ', $varKeywords);
        }

        if (is_array($varFields)) {
            $arrFields = $varFields;
        } else {
            $arrFields[] = $varFields;
        }

        $strSearch = '';
        $blnQuotedString = false;

        $intTokenNum = 0;
        $arrTokens = array();

        for ($i = 0; $i < count($arrKeywords); $i++) {
            if (!isset($arrTokens[$intTokenNum])) {
                $arrTokens[$intTokenNum] = '';
            }

            $strKeyword = $arrKeywords[$i];

            if (ereg("^\"", $strKeyword) || ereg("^[+-]\"", $strKeyword)) {
                $blnQuotedString = true;
            }

            if ($blnQuotedString) {
                $arrTokens[$intTokenNum] .= ereg_replace("\"", "", $strKeyword) . " ";
            } else {
                $arrTokens[$intTokenNum++] = $strKeyword;
            }

            if (ereg("\"$", $strKeyword)) {
                $blnQuotedString = false;
                $arrTokens[$intTokenNum] = trim($arrTokens[$intTokenNum]);
                $intTokenNum++;
            }
        }

        $strSearch .= " AND (";

        for ($i = 0; $i < count($arrTokens); $i++) {
            for ($x = 0; $x < count($arrFields); $x++) {
                $str_token = ereg_replace(" $", "", $arrTokens[$i]);

                if (ereg("^\\+", $str_token)) {
                    $str_token = ereg_replace("^\\+", "", $str_token);
                    $strSearch .= "$arrFields[$x] LIKE '%$str_token%'";
                    if ($x < count($arrFields) - 1) {
                        $strSearch .= " OR ";
                    }
                } else if (ereg("^\\-", $str_token)) {
                    $str_token = ereg_replace("^\\-", "", $str_token);
                    $strSearch .= "$arrFields[$x] NOT LIKE '%$str_token%'";
                    if ($x < count($arrFields) - 1) {
                        $strSearch .= " AND ";
                    }
                } else {
                    $strSearch .= "$arrFields[$x] LIKE '%$str_token%'";
                    if ($x < count($arrFields) - 1) {
                        $strSearch .= " OR ";
                    }
                }
            }

            if ($i < count($arrTokens) - 1) {
                $strSearch .= ") AND (";
            } else {
                $strSearch .= ")";
            }
        }

        return $strSearch;
    }


    public static function formatFriendlyURI($str)
    {
        $str = preg_replace("/[^ a-zA-Z0-9]/", '', $str);  // remove special chars
        $str = str_replace(' ', '-', $str);                // convert spaces to dashes
        $str = str_replace('_', '-', $str);                // convert underscores to dashes
        $str = str_replace('--', '-', $str);               // no double dashes
        $str = trim(strtolower($str));                     // all lowercase and trimmed
        return $str;
    }


    /**
     * File System Functions
     */

    public static function getDirectory($str_path)
    {
        $obj_dir = opendir($str_path);
        $arr_files = array();

        while ($str_file = readdir($obj_dir)) {
            if ((($str_file != '.') && ($str_file != '..'))) {
                $arr_files[count($arr_files)] = $str_file;
            }
        }

        closedir($obj_dir);
        sort($arr_files);

        return $arr_files;
    }


    public static function formatFileSize($int)
    {
        $arr_units = array(
            'YB' => 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024,
            'ZB' => 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024,
            'EB' => 1024 * 1024 * 1024 * 1024 * 1024 * 1024,
            'PB' => 1024 * 1024 * 1024 * 1024 * 1024,
            'TB' => 1024 * 1024 * 1024 * 1024,
            'GB' => 1024 * 1024 * 1024,
            'MB' => 1024 * 1024,
            'KB' => 1024,
       );

       if ($int < 1024) {
           return $int . " Bytes";
       } else {
           foreach ($arr_units AS $str_unit => $int_size) {
               if ($int >= $int_size) {
                   return round((round($int / $int_size * 100) / 100), 2) . ' ' . $str_unit;
               }
           }
       }

       return $int;
    }


    /**
     * Authentication Functions
     */

    public static function getUniqueId()
    {
        $now = (string) microtime();
        $now = explode(' ', $now);
        $unique_id = $now[1] . str_replace('.', '', $now[0]);
        return $unique_id;
    }


    public static function createRandomPassword($int_length = 8)
    {
        $str_password   = '';
        $str_characters = 'abcefghijklmnopqrstuvwxyz1234567890';
        $arr_characters = array();

        for ($i = 0; $i < strlen($str_characters); $i++) {
            $arr_characters[] = $str_characters[$i];
        }

        shuffle($arr_characters);

        for ($i = 0; $i < $int_length; $i++) {
            $str_password .= $arr_characters[$i];
        }

        return $str_password;
    }


	public static function formatText($inputText = null)
	{
		if ($inputText != null) {
			try {

				//handle bold text
				$inputText = preg_replace('/{{{(.*?)}}}/','<span class="bold">$1</span>', $inputText);

				//handle H2 text
				$inputText = preg_replace('/{{(.*?)}}/','<span class="header2">$1</span>', $inputText);

				//handle H1 text
				$inputText = preg_replace('/{(.*?)}/','<span class="header1">$1</span>', $inputText);

				//handle url links
				$inputText = preg_replace('/\[(.*?)\|(.*?)\]/', '<a href="$2" target="_blank">$1</a>', $inputText);

				//handle internal url links
				//Applies more to the WIKI context - since it uses the page title in internal urls
				//$urlHome = Zend_Registry::get('appConfigs')->url->home;
				//$inputText = preg_replace('/\[(.*?)\]/', '<a href="' . $urlHome . '/article/view/title/$1">$1</a>', $inputText);

				//Handle linebreaks
				$inputText = nl2br($inputText);

			} catch (MyBlog_Utils_Exception $e) {
				MyBlog_Logger::getInstance()->writeLog($e->getMessage(), Zend_Log::INFO);
			}
		}
		return $inputText;
	}

    public static function generateSalt()
    {
        $strSalt = '';
        for ($i = 0; $i < 32; $i++) {
            $strSalt .= chr(rand(33, 126));
        }
        return $strSalt;
    }



	public static function generateRssArray( $method, $title, $link, $data = null) {
		$rssArray = array();

		$finalArray = array();
		if ($data != null) {
			$i = 0;
			foreach ($data as $tmp) {
				$intArray = array();
				$intArray['title'] = $tmp['title'];
				$intArray['link'] = 'http://localhost/article/view/id/' . $tmp['entry_id'];

				if ($method != 'recentposts') {
					$intArray['description'] = $tmp['content'];
				} else {
					$intArray['description'] = $tmp['title'];
				}
				$finalArray[$i] = $intArray;
				$i++;
			}

			$rssArray = array (
			'title' 	=> $title,
			'link'		=> $link,
			'author' => 'Zend Technologies Inc',
			'charset'	=> 'UTF-8',
			'entries' 	=> $finalArray,
			);
		}

		return $rssArray;
	}


	public static function mailNewComment($commentTitle)
    {
		// SOME TEST DATA
		$subject = 'Someone just commented on one of your blog posting!';
		$fromEmail = 'system@myblog';
		$from = 'System Administrator';
		$toEmail   = 'administrator@myblog';
		$to   = 'Blog Administrator';
		$body = '<p>You just received this comment on one of your blog entries</p><p>' . $commentTitle . '</p><p>Pl login to your account to approve or delete it.</p>';

		$mail = new Zend_Mail();

		//Send mail to administrator
		$mail->setBodyText( $commentTitle)
			->setFrom($fromEmail, $from)
			->addTo($toEmail, $to)
			->setSubject($subject);


		/** The Mail Send statement below has been intentionally commented out */
		//$mail->send();

		return true;
	}


    public static function highlightSearchKeywords($str = '', $keywords = '', $html = 0)
    {
        if (!is_array($keywords)) {
            $keywords = explode(' ', $keywords);
        }

        foreach ($keywords as $keyword) {
            $keyword = trim($keyword);

            $keyword = str_replace("'", "", $keyword);

            if ($keyword != '') {
                if ($html) {
                    $keyword = htmlspecialchars($keyword);
                }
                /*** FOR EXACT WORD MATCHES USE: $str = str_replace('\"', '"', substr(preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "preg_replace('#\b(" . $keyword . ")\b#i', '<span class=\"highlight\">\\\\1</span>', '\\0')", '>' . $str . '<'), 1, -1)); ***/
                $str = str_replace('\"', '"', substr(preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "preg_replace('#(" . $keyword . ")#i', '<span class=\"highlight\">\\\\1</span>', '\\0')", '>' . $str . '<'), 1, -1));
            }
        }

        return $str;
    }


    public static function getCurrentURI()
    {
        $strRequestUri = htmlentities(substr($_SERVER['REQUEST_URI'], 0, strcspn($_SERVER['REQUEST_URI'], "\n\r")), ENT_QUOTES);
        $strProtocol = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? 'https://' : 'http://';
        $strHost = $_SERVER['HTTP_HOST'];

        if (($_SERVER['SERVER_PORT'] != '') && (($strProtocol == 'http://' && $_SERVER['SERVER_PORT'] != '80') || (($strProtocol == 'https://') && ($_SERVER['SERVER_PORT'] != '443')))) {
            $strPort = ':' . $_SERVER['SERVER_PORT'];
        } else {
            $strPort = '';
        }

        return $strProtocol . $strHost . $strPort . $strRequestUri;
    }


    public static function isMobile()
    {
        $strUserAgent = $_SERVER['HTTP_USER_AGENT'];

        return preg_match('/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile|o2|opera m(ob|in)i|palm( os)?|p(ixi|re)\/|plucker|pocket|psp|smartphone|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce; (iemobile|ppc)|xiino/i',$strUserAgent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i', substr($strUserAgent, 0, 4));
    }

    public static function convertSecondsToDuration($intSeconds = 0)
    {
        $intSeconds = (int) $intSeconds;

 		$intCalculatedSeconds = $intSeconds % 60;
 		$intCalculatedMinutes = floor($intSeconds / 60) % 60;
 		$intCalculatedHours   = floor($intSeconds / 3600);

        $arrDuration = array();

 		if ($intCalculatedHours > 0) {
            $arrDuration[] = str_pad($intCalculatedHours, 2, '0', STR_PAD_LEFT);  // hours
 		}
        $arrDuration[] = str_pad($intCalculatedMinutes, 2, '0', STR_PAD_LEFT);    // minutes
        $arrDuration[] = str_pad($intCalculatedSeconds, 2, '0', STR_PAD_LEFT);    // seconds

        return implode(':', $arrDuration);
    }


    /**
     * Get Time Difference
     */

    public static function getTimeDifference($strStart, $strEnd)
    {
        $intStart = strtotime($strStart);
        $intEnd   = strtotime($strEnd);

        if (($intStart !== -1) && ($intEnd !== -1)) {

            if ($intEnd >= $intStart) {

                $intDifference = $intEnd - $intStart;

                if ($intDays = intval((floor($intDifference / 86400)))) {
                    $intDifference = $intDifference % 86400;
                }

                if ($intHours = intval((floor($intDifference / 3600)))) {
                    $intDifference = $intDifference % 3600;
                }

                if ($intMinutes = intval((floor($intDifference / 60)))) {
                    $intDifference = $intDifference % 60;
                }

                $intSeconds = intval($intDifference);

                return array('days' => $intDays, 'hours' => $intHours, 'minutes' => $intMinutes, 'seconds' => $intSeconds);

            } else {
                trigger_error('Ending date/time is earlier than the start date/time', E_USER_WARNING);
            }

        } else {
            trigger_error('Invalid date/time data detected', E_USER_WARNING);
        }

        return false;
    }


    /**
     * Get Formatted Time Difference
     */

    public static function getFormattedTimeDifference($strStart)
    {
        $strTimeDifference = '';
        $arrTimeDifference = Myriad_Utils::getTimeDifference($strStart, date('Y-m-d H:i:s'));

        $intDays    = $arrTimeDifference['days'];
        $intHours   = $arrTimeDifference['hours'];
        $intMinutes = $arrTimeDifference['minutes'];
        $intSeconds = $arrTimeDifference['seconds'];

        if ($intDays > 0) {
            $strTimeDifference = ($intDays == 1) ? 'Yesterday' : $intDays . ' days';
        } else if ($intHours > 0) {
            $strTimeDifference = ($intHours == 1) ? '1 hour' : $intHours . ' hours';
        } else if ($intMinutes > 0) {
            $strTimeDifference = ($intMinutes == 1) ? '1 minute' : $intMinutes . ' minutes';
        } else if ($intSeconds > 0) {
            $strTimeDifference = ($intSeconds == 1) ? '1 second' : $intSeconds . ' seconds';
        } else {
            $strTimeDifference = 'Moments';
        }

        if ($strTimeDifference != 'Yesterday') {
            $strTimeDifference .= ' ago';
        }

        return $strTimeDifference;
    }


    public static function getStatesArray() {
		$arrStates = array(
            '' => '',
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'CA' => 'California',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'DE' => 'Delaware',
            'DC' => 'District of Columbia',
            'FL' => 'Florida',
            'GA' => 'Georgia',
            'HI' => 'Hawaii',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'Iowa',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'LA' => 'Louisiana',
            'ME' => 'Maine',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MS' => 'Mississippi',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'NC' => 'North Carolina',
            'ND' => 'North Dakota',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PA' => 'Pennsylvania',
            'PR' => 'Puerto Rico',
            'RI' => 'Rhode Island',
            'SC' => 'South Carolina',
            'SD' => 'South Dakota',
            'TN' => 'Tennessee',
            'TX' => 'Texas',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VA' => 'Virginia',
            'WA' => 'Washington',
            'WV' => 'West Virginia',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming',
        );

        return $arrStates;
    }

    public static function getDomain($strUrl = '')
    {
        if ($strUrl == '') {
            $strUrl = $_SERVER['HTTP_HOST'];
        }

        $arrUrlInfo = parse_url($strUrl);
        $strDomain = isset($arrUrlInfo['host']) ? $arrUrlInfo['host'] : $arrUrlInfo['path'];

        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $strDomain, $arrRegs)) {
            return $arrRegs['domain'];
        }

        return false;
    }

    public static function getNumberWithSuffix($intNumber = 0)
    {
        $arrSuffix = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');

        if ((($intNumber %100) >= 11) && (($intNumber % 100) <= 13)) {
           return $intNumber . 'th';
        } else {
           return $intNumber . $arrSuffix[$intNumber % 10];
        }
    }
}
