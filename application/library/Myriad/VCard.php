<?php

require_once 'Myriad/Log.php';

class Myriad_VCard
{
    var $properties;
    var $filename;


    /**
     * setPhone
     * type may be PREF | WORK | HOME | VOICE | FAX | MSG | CELL | PAGER | BBS | CAR | MODEM | ISDN | VIDEO or any senseful combination, e.g. "PREF;WORK;VOICE"
     */

    function setPhoneNumber($number, $type = '')
    {
        $key = 'TEL';

        if ($type != '') {
            $key .= ';' . $type;
        }

        $key.= ';ENCODING=QUOTED-PRINTABLE';
        $this->properties[$key] = $this->quoted_printable_encode($number);
    }


    /**
     * setPhoto
     * $type = "GIF" | "JPEG"
     */

    function setPhoto($type, $photo)
    {
        $this->properties["PHOTO;TYPE=$type;ENCODING=BASE64"] = base64_encode($photo);
    }


    /**
     * setFormattedName
     */

    function setFormattedName($name)
    {
        $this->properties['FN'] = $this->quoted_printable_encode($name);
    }


    /**
     * setName
     */

    function setName($family = '', $first = '', $additional = '', $prefix = '', $suffix = '')
    {
        $this->properties['N'] = $family . ';' . $first . ';' . $additional . ';' . $prefix . ';' . $suffix;
        $this->filename = "$first%20$family.vcf";
        if (empty($this->properties['FN']) || $this->properties['FN'] == '') {
            $strFormattedName = trim("$prefix $first $additional $family $suffix");
            $strFormattedName = str_replace('  ', ' ', $strFormattedName);
            $this->setFormattedName($strFormattedName);
        }
    }


    /**
     * setBirthday
     * format: YYYY-MM-DD
     */

    function setBirthday($date)
    {
        $this->properties['BDAY'] = $date;
    }


    /**
     * setAddress
     * $type may be DOM | INTL | POSTAL | PARCEL | HOME | WORK or any combination of these: e.g. "WORK;PARCEL;POSTAL"
     */

    function setAddress($postoffice = '', $extended = '', $street = '', $city = '', $region = '', $zip = '', $country = '', $type = 'HOME;POSTAL')
    {
        $key = 'ADR';

        if ($type != '') {
            $key.= ';' . $type;
        }

        $key.= ';ENCODING=QUOTED-PRINTABLE';
        $this->properties[$key] = $this->encode($postoffice) . ';' . $this->encode($extended) . ';' . $this->encode($street) . ';' . $this->encode($city) . ';' . $this->encode($region) . ';' . $this->encode($zip) . ';' . $this->encode($country);

        /*        
        if ($this->properties["LABEL;$type;ENCODING=QUOTED-PRINTABLE"] == "") {
            //$this->setLabel($postoffice, $extended, $street, $city, $region, $zip, $country, $type);
        }
        */
    }


    /**
     * setLabel
     */

    function setLabel($postoffice = '', $extended = '', $street = '', $city = '', $region = '', $zip = '', $country = '', $type = 'HOME;POSTAL')
    {
        $label = '';

        if ($postoffice != '') $label.= "$postoffice\r\n";
        if ($extended != '') $label.= "$extended\r\n";
        if ($street != '') $label.= "$street\r\n";
        if ($zip != '') $label.= "$zip ";
        if ($city != '') $label.= "$city\r\n";
        if ($region != '') $label.= "$region\r\n";
        if ($country != '') $country.= "$country\r\n";

        $this->properties["LABEL;$type;ENCODING=QUOTED-PRINTABLE"] = $this->quoted_printable_encode($label);
    }


    /**
     * setEmail
     */

    function setEmail($address)
    {
        $this->properties['EMAIL;PREF;INTERNET'] = $address;
    }


    /**
     * setNote
     */

    function setNote($note)
    {
        $this->properties['NOTE;ENCODING=QUOTED-PRINTABLE'] = $this->quoted_printable_encode($note);
    }


    /**
     * setURL
     * $type may be WORK | HOME
     */

    function setURL($url, $type = '')
    {
        $key = 'URL';

        if ($type != '') {
            $key .= ';' . $type;
        }

        $this->properties[$key] = $url;
    }


    /**
     * setField
     */

    function setField($data, $field)
    {
        $this->properties[$field] = $data;
    }


    /**
     * getVCard
     */

    function getVCard()
    {
        $text = "BEGIN:VCARD\r\n";
        $text.= "VERSION:2.1\r\n";
        foreach($this->properties as $key => $value) {
            $text .= $key . ':' . $value . "\r\n";
        }
        $text.= 'REV:' . date('Y-m-d') . 'T' . date('H:i:s') . "Z\r\n";
        $text.= "MAILER:Myriad Interactive PHP vCard class\r\n";
        $text.= "END:VCARD\r\n";

        return $text;
    }


    /**
     * setFileName
     */

    function getFileName()
    {
        return $this->filename;
    }


    /**
     * encode
     */

    function encode($string)
    {
        return $this->escape($this->quoted_printable_encode($string));
    }


    /**
     * escape
     */

    function escape($string)
    {
        return str_replace(';', '\;', $string);
    }


    /**
     * quoted_printable_encode
     */

    function quoted_printable_encode($input, $line_max = 76)
    {
        $hex       = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
        $lines     = preg_split("/(?:\r\n|\r|\n)/", $input);
        $eol       = "\r\n";
        $linebreak = '=0D=0A';
        $escape    = '=';
        $output    = '';
    
        for ($j = 0; $j < count($lines); $j++) {

            $line    = $lines[$j];
            $linlen  = strlen($line);
            $newline = '';

            for ($i = 0; $i < $linlen; $i++) {
                $c = substr($line, $i, 1);
                $dec = ord($c);

                if (($dec == 32) && ($i == ($linlen - 1))) {                    // convert space at eol only
                    $c = '=20';
                } else if (($dec == 61) || ($dec < 32 ) || ($dec > 126) ) {     // always encode "\t", which is *not* required
                    $h2 = floor($dec/16); $h1 = floor($dec%16);
                    $c = $escape.$hex["$h2"].$hex["$h1"];
                }

                if ((strlen($newline) + strlen($c)) >= $line_max ) {            // CRLF is not counted
                    $output .= $newline.$escape.$eol;                           // soft line break; " =\r\n" is okay
                    $newline = "    ";
                }

                $newline .= $c;
            }

            $output .= $newline;

            if ($j < count($lines) - 1) {
                $output .= $linebreak;
            }
        }

        return trim($output);
    }
}
