<?php

class Myriad_Authnet
{
    // MERCHANT ACCOUNT INFORMATION

    var $x_version;             // optional - 3.1 (allows merchant to use card code), 3.0 (standard)
    var $x_login;               // required - account username
    var $x_tran_key;            // required - transaction key
    var $x_test_request;        // required - TRUE (processes a test transaction), FALSE
    var $x_relay_response;      // required - must be set to FALSE;

    // GATEWAY RESPONSE CONFIGURATION

    var $x_delim_data;          // optional - TRUE (allows a delimited response), FALSE
    var $x_delim_char;          // optional - allows you to set your own delimiter
    var $x_encap_char;          // optional - character that will be used to encapsulate the fields in the transaction response.

    // CUSTOMER NAME AND BILLING ADDRESS

    var $x_first_name;          // optional - 50 chars max
    var $x_last_name;           // optional - 50 chars max
    var $x_company;             // optional - 50 chars max
    var $x_address;             // optional - 60 chars max
    var $x_city;                // optional - 40 chars max
    var $x_state;               // optional - 40 chars max
    var $x_zip;                 // optional - 20 chars max
    var $x_country;             // optional - 60 chars max
    var $x_phone;               // optional - 25 chars max - recommended format (123)123-1234
    var $x_fax;                 // optional - 25 chars max - recommended format (123)123-1234

    // ADDITIONAL CUSTOMER DATA (optional)

    var $x_cust_id;             // optional - 20 chars max
    var $x_customer_ip;         // optional - 15 chars max - required format 255.255.255.255
    var $x_customer_tax_id;     // optional - 9 chars only - tax id or ssn

    // EMAIL SETTINGS

    var $x_email;               // optional - 255 chars max - for customer copy
    var $x_email_customer;      // optional - TRUE (sends a confirmation email), FALSE
    var $x_merchant_email;      // optional - 255 chars max - for merchant copy

    // INVOICE INFORMATION (optional)

    var $x_invoice_num;         // optional - 20 chars max
    var $x_description;         // optional - 255 chars max

    // CUSTOMER SHIPPING ADDRESS (optional)

    var $x_ship_to_first_name;  // optional - 50 chars max
    var $x_ship_to_last_name;   // optional - 50 chars max
    var $x_ship_to_company;     // optional - 50 chars max
    var $x_ship_to_address;     // optional - 60 chars max
    var $x_ship_to_city;        // optional - 40 chars max
    var $x_ship_to_state;       // optional - 40 chars max
    var $x_ship_to_zip;         // optional - 20 chars max
    var $x_ship_to_country;     // optional - 60 chars max

    // TRANSACTION INFORMATION

    var $x_amount;              // required - 15 chars max
    var $x_currency_code;       // optional - 3 chars max
    var $x_method;              // required - CC (default), ECHECK
    var $x_recurring_billing;   // optional - YES, NO
    var $x_echeck_type;         // required if ECHECK - CCD (business checking), PPD, TEL, WEB (checking or savings)
    var $x_bank_aba_code;       // required if ECHECK - valid routing number (9 digits)
    var $x_bank_acct_num;       // required if ECHECK - valid account number (20 digits max)
    var $x_bank_acct_type;      // required if ECHECK - CHECKING (default), BUSINESSCHECKING, SAVINGS
    var $x_bank_name;           // required if ECHECK - valid bank name (50 chars max)
    var $x_bank_acct_name;      // required if ECHECK - customers name as it appears on their bank account
    var $x_card_num;            // required if CC - numeric credit card number (22 chars max)
    var $x_exp_date;            // required if CC - card expiration date - MMYY, MM/YY, MMYYYY, MM-YYYY
    var $x_card_code;           // optional if CC - 3 or 4-digit code found on back of card (front for American Express)
    var $x_trans_id;            // required if x_type is CREDIT, VOID or PRIOR_AUTH_CAPTURE - valid transaction id

    // ERROR VARIABLE

    var $error;                 // error handling
    var $trans_id;              // transaction id

    function __construct($login, $transkey, $test)
    {
        // CURL CHECK

        if (!function_exists('curl_init')) {
            die('FATAL ERROR: You must have Curl compiled into PHP for this class to work.');
        }

        // DEFAULT VALUES

        $this->x_version        = '3.1';                             // do not change (required for AIM)
        $this->x_relay_response = 'FALSE';                           // do not change (required for AIM)
        $this->x_delim_data     = 'TRUE';                            // do not change (required for AIM)
        $this->x_delim_char     = ',';                               // do not change (needed by script)
        $this->x_encap_char     = '';                                // do not change (needed by script)
        $this->x_customer_ip    = $_SERVER['REMOTE_ADDR'];           // do not change (needed by script)

        // USER DEFINED VALUES

        $this->x_login          = $login;                            // required
        $this->x_tran_key       = $transkey;                         // required
        $this->x_test_request   = $test;                             // optional
        $this->x_type           = 'AUTH_CAPTURE';                    // required
        $this->x_method         = 'CC';                              // required
        $this->x_email_customer = 'TRUE';                            // optional
        // $this->x_merchant_email = "peter@myriadinteractive.com";     // optional
    }


    function process()
    {
        $data = $this->getUrlData();

        $ch = curl_init("https://secure.authorize.net/gateway/transact.dll");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_NOPROGRESS, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $buffer = curl_exec($ch);
        curl_close($ch);


        // process information returned by authorize.net

        $arr_return = preg_split('/[,]+/', $buffer);             // splits buffer into an array
        $response_code        = $arr_return[0];                  // 1-approved, 2-declined, 3-error
        $response_reason_text = $arr_return[3];                  // brief description of the result
        $transaction_id       = $arr_return[6];                  // transaction id


        // return transaction id with success or error on failure

        if ($response_code == '1') {
            $this->trans_id = $transaction_id;
            return true;
        } else {
            $this->error = $response_reason_text;
            return false;
        }
    }


    /**
     * Get Error (returns error message - use only after processing)
     */

    function getError()
    {
        return $this->error;
    }


    /**
     * Get Transaction ID (returns the transaction id - use only after processing)
     */

    function getTransID()
    {
        return $this->trans_id;
    }


    /**
     * Get Url Data
     */

    function getUrlData()
    {
        $data  = "";
        $data .= "x_version="               . $this->x_version;
        $data .= "&x_relay_response="       . $this->x_relay_response;
        $data .= "&x_delim_data="           . $this->x_delim_data;
        $data .= "&x_delim_char="           . $this->x_delim_char;
        $data .= "&x_encap_char="           . $this->x_encap_char;
        $data .= "&x_login="                . $this->x_login;
        $data .= "&x_tran_key="             . $this->x_tran_key;
        $data .= "&x_test_request="         . $this->x_test_request;
        $data .= "&x_type="                 . $this->x_type;
        $data .= "&x_first_name="           . $this->x_first_name;
        $data .= "&x_last_name="            . $this->x_last_name;
        $data .= "&x_company="              . $this->x_company;
        $data .= "&x_address="              . $this->x_address;
        $data .= "&x_city="                 . $this->x_city;
        $data .= "&x_state="                . $this->x_state;
        $data .= "&x_zip="                  . $this->x_zip;
        $data .= "&x_country="              . $this->x_country;
        $data .= "&x_phone="                . $this->x_phone;
        $data .= "&x_fax="                  . $this->x_fax;
        $data .= "&x_cust_id="              . $this->x_cust_id;
        $data .= "&x_customer_ip="          . $this->x_customer_ip;
        $data .= "&x_customer_tax_id="      . $this->x_customer_tax_id;
        $data .= "&x_email="                . $this->x_email;
        $data .= "&x_email_customer="       . $this->x_email_customer;
        $data .= "&x_merchant_email="       . $this->x_merchant_email;
        $data .= "&x_invoice_num="          . $this->x_invoice_num;
        $data .= "&x_description="          . $this->x_description;
        $data .= "&x_ship_to_first_name="   . $this->x_ship_to_first_name;
        $data .= "&x_ship_to_last_name="    . $this->x_ship_to_last_name;
        $data .= "&x_ship_to_company="      . $this->x_ship_to_company;
        $data .= "&x_ship_to_address="      . $this->x_ship_to_address;
        $data .= "&x_ship_to_city="         . $this->x_ship_to_city;
        $data .= "&x_ship_to_state="        . $this->x_ship_to_state;
        $data .= "&x_ship_to_zip="          . $this->x_ship_to_zip;
        $data .= "&x_ship_to_country="      . $this->x_ship_to_country;
        $data .= "&x_amount="               . $this->x_amount;
        $data .= "&x_method="               . $this->x_method;

        if ($this->x_currency_code) {
            $data .= "&x_currency_code="      . $this->x_currency_code;
        }

        if (strtoupper($this->x_recurring_billing) == "YES") {
            $data .= "&x_recurring_billing="  . $this->x_recurring_billing;
        }

        if ($this->x_method == "ECHECK") {
            $data .= "&x_echeck_type="        . "WEB";
            $data .= "&x_bank_aba_code="      . $this->x_bank_aba_code;       // valid routing number (9 digits)
            $data .= "&x_bank_acct_num="      . $this->x_bank_acct_num;       // valid account number (20 digits max)
            $data .= "&x_bank_acct_type="     . $this->x_bank_acct_type;      // account type - use CHECKING (default) or SAVINGS
            $data .= "&x_bank_name="          . $this->x_bank_name;           // valid bank name (50 chars max)
            $data .= "&x_bank_acct_name="     . $this->x_bank_acct_name;      // customers name as it appears on their bank account
        } else {
            $data .= "&x_card_num="           . $this->x_card_num;            // numeric credit card number (22 chars max)
            $data .= "&x_exp_date="           . $this->x_exp_date;            // card expiration date - recommended formats: MMYY, MM/YY, MMYYYY, MM-YYYY

            if ($this->x_card_code) {
                $data .= "&x_card_code="        . $this->x_card_code;           // code found on back of card (front for American Express)
            }

            if ($this->x_trans_id) {
                $data .= "&x_trans_id="         . $this->x_trans_id;            // valid transaction id - use for CREDIT, VOID or PRIOR_AUTH_CAPTURE
            }
        }

        return $data;
    }


    // EACH FIELD MUST BE URLENCODED WITH POST...

    function setLogin($input)
    {
        $this->x_login = urlencode($input);
    }

    function setTranKey($input)
    {
        $this->x_tran_key = urlencode($input);
    }

    function setTestRequest($input)
    {
        $this->x_test_request = urlencode($input);
    }

    function setType($input)
    {
        $this->x_type = urlencode($input);
    }

    function setFirstName($input)
    {
        $this->x_first_name = urlencode($input);
    }

    function setLastName($input)
    {
        $this->x_last_name = urlencode($input);
    }

    function setCompany($input)
    {
        $this->x_company = urlencode($input);
    }

    function setAddress($input)
    {
        $this->x_address = urlencode($input);
    }

    function setCity($input)
    {
        $this->x_city = urlencode($input);
    }

    function setState($input)
    {
        $this->x_state = urlencode($input);
    }

    function setZip($input)
    {
        $this->x_zip = urlencode($input);
    }

    function setCountry($input)
    {
        $this->x_country = urlencode($input);
    }

    function setPhone($input)
    {
        $this->x_phone = urlencode($input);
    }

    function setFax($input)
    {
        $this->x_fax = urlencode($input);
    }

    function setCustomerID($input)
    {
        $this->x_cust_id = urlencode($input);
    }

    function setCustomerTaxID($input)
    {
        $this->x_customer_tax_id = urlencode($input);
    }

    function setEmailCustomer($input)
    {
        $this->x_email_customer = urlencode($input);
    }

    function setCustomerEmail($input)
    {
        $this->x_email = urlencode($input);
    }

    function setMerchantEmail($input)
    {
        $this->x_merchant_email = urlencode($input);
    }

    function setInvoiceNumber($input)
    {
        $this->x_invoice_num = urlencode($input);
    }

    function setDescription($input)
    {
        $this->x_description = urlencode($input);
    }

    function setShipToFirstName($input)
    {
        $this->x_ship_to_first_name = urlencode($input);
    }

    function setShipToLastName($input)
    {
        $this->x_ship_to_last_name = urlencode($input);
    }

    function setShipToCompany($input)
    {
        $this->x_ship_to_company = urlencode($input);
    }

    function setShipToAddress($input)
    {
        $this->x_ship_to_address = urlencode($input);
    }

    function setShipToCity($input)
    {
        $this->x_ship_to_city = urlencode($input);
    }

    function setShipToState($input)
    {
        $this->x_ship_to_state = urlencode($input);
    }

    function setShipToZip($input)
    {
        $this->x_ship_to_zip = urlencode($input);
    }

    function setShipToCountry($input)
    {
        $this->x_ship_to_country = urlencode($input);
    }

    function setAmount($input)
    {
        $this->x_amount = urlencode($input);
    }

    function setCurrencyCode($input)
    {
        $this->x_currecy_code = urlencode($input);
    }

    function setMethod($input)
    {
        $this->x_method = urlencode($input);
    }

    function setRecurringbilling($input)
    {
        $this->x_recurring_billing = urlencode($input);
    }

    function setECheckType($input)
    {
        $this->x_echeck_type = urlencode($input);
    }

    function setBankRoutingNumber($input)
    {
        $this->x_bank_aba_code = urlencode($input);
    }

    function setBankAccountNumber($input)
    {
        $this->x_bank_acct_num = urlencode($input);
    }

    function setBankAccountType($input)
    {
        $this->x_bank_acct_type = urlencode($input);
    }

    function setBankAccountName($input)
    {
        $this->x_bank_acct_name = urlencode($input);
    }

    function setBankName($input)
    {
        $this->x_bank_name = urlencode($input);
    }

    function setCardNumber($input)
    {
        $this->x_card_num = urlencode($input);
    }

    function setCardExpDate($input) {
      $this->x_exp_date = urlencode($input);
    }

    function setCardCode($input)
    {
        $this->x_card_code = urlencode($input);
    }

    function setTransID($input)
    {
        $this->x_trans_id = urlencode($input);
    }
}
