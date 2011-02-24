<?php
/**
 * EmailAddressValidator Class
 *
 * @author  Dave Child <dave@addedbytes.com>
 * @link    http://code.google.com/p/php-email-address-validation/
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @version SVN r10 + Issue 15 fix + Issue 12 fix
 */
class EmailAddressValidator {
    /**
     * Set true to allow addresses like me@localhost
     */
    public $allowLocalAddresses = false;

    /**
     * Check email address validity
     * @param   strEmailAddress     Email address to be checked
     * @return  True if email is valid, false if not
     */
    public function check_email_address($strEmailAddress) {

        // If magic quotes is "on", email addresses with quote marks will
        // fail validation because of added escape characters. Uncommenting
        // the next three lines will allow for this issue.
        //if (get_magic_quotes_gpc()) {
        //    $strEmailAddress = stripslashes($strEmailAddress);
        //}

        // Control characters are not allowed
        if (preg_match('/[\x00-\x1F\x7F-\xFF]/', $strEmailAddress)) {
            return false;
        }

        // Check email length - min 3 (a@a), max 256
        if (!$this->check_text_length($strEmailAddress, 3, 256)) {
            return false;
        }

        // Split it into sections using last instance of "@"
        $intAtSymbol = strrpos($strEmailAddress, '@');
        if ($intAtSymbol === false) {
            // No "@" symbol in email.
            return false;
        }
        $arrEmailAddress[0] = substr($strEmailAddress, 0, $intAtSymbol);
        $arrEmailAddress[1] = substr($strEmailAddress, $intAtSymbol + 1);

        // Count the "@" symbols. Only one is allowed, except where
        // contained in quote marks in the local part. Quickest way to
        // check this is to remove anything in quotes. We also remove
        // characters escaped with backslash, and the backslash
        // character.
        $arrTempAddress[0] = preg_replace('/\./'
                                         ,''
                                         ,$arrEmailAddress[0]);
        $arrTempAddress[0] = preg_replace('/"[^"]+"/'
                                         ,''
                                         ,$arrTempAddress[0]);
        $arrTempAddress[1] = $arrEmailAddress[1];
        $strTempAddress = $arrTempAddress[0] . $arrTempAddress[1];
        // Then check - should be no "@" symbols.
        if (strrpos($strTempAddress, '@') !== false) {
            // "@" symbol found
            return false;
        }

        // Check local portion
        if (!$this->check_local_portion($arrEmailAddress[0])) {
            return false;
        }

        // Check domain portion
        if (!$this->check_domain_portion($arrEmailAddress[1])) {
            return false;
        }

        // If we're still here, all checks above passed. Email is valid.
        return true;

    }

    /**
     * Checks email section before "@" symbol for validity
     * @param   strLocalPortion     Text to be checked
     * @return  True if local portion is valid, false if not
     */
    protected function check_local_portion($strLocalPortion) {
        // Local portion can only be from 1 to 64 characters, inclusive.
        // Please note that servers are encouraged to accept longer local
        // parts than 64 characters.
        if (!$this->check_text_length($strLocalPortion, 1, 64)) {
            return false;
        }
        // Local portion must be:
        // 1) a dot-atom (strings separated by periods)
        // 2) a quoted string
        // 3) an obsolete format string (combination of the above)
        $arrLocalPortion = explode('.', $strLocalPortion);
        for ($i = 0, $max = sizeof($arrLocalPortion); $i < $max; $i++) {
             if (!preg_match('.^('
                            .    '([A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]'
                            .    '[A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]{0,63})'
                            .'|'
                            .    '("[^\\\"]{0,62}")'
                            .')$.'
                            ,$arrLocalPortion[$i])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks email section after "@" symbol for validity
     * @param   strDomainPortion     Text to be checked
     * @return  True if domain portion is valid, false if not
     */
    protected function check_domain_portion($strDomainPortion) {
        // Total domain can only be from 1 to 255 characters, inclusive
        if (!$this->check_text_length($strDomainPortion, 1, 255)) {
            return false;
        }

        // some IPv4/v6 regexps borrowed from Feyd
        // see: http://forums.devnetwork.net/viewtopic.php?f=38&t=53479
        $dec_octet = '(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|[0-9])';
        $hex_digit = '[A-Fa-f0-9]';
        $h16 = "{$hex_digit}{1,4}";
        $IPv4Address = "$dec_octet\\.$dec_octet\\.$dec_octet\\.$dec_octet";
        $ls32 = "(?:$h16:$h16|$IPv4Address)";
        $IPv6Address =
            "(?:(?:{$IPv4Address})|(?:".
            "(?:$h16:){6}$ls32" .
            "|::(?:$h16:){5}$ls32" .
            "|(?:$h16)?::(?:$h16:){4}$ls32" .
            "|(?:(?:$h16:){0,1}$h16)?::(?:$h16:){3}$ls32" .
            "|(?:(?:$h16:){0,2}$h16)?::(?:$h16:){2}$ls32" .
            "|(?:(?:$h16:){0,3}$h16)?::(?:$h16:){1}$ls32" .
            "|(?:(?:$h16:){0,4}$h16)?::$ls32" .
            "|(?:(?:$h16:){0,5}$h16)?::$h16" .
            "|(?:(?:$h16:){0,6}$h16)?::" .
            ")(?:\\/(?:12[0-8]|1[0-1][0-9]|[1-9][0-9]|[0-9]))?)";

        // Check if domain is IP, possibly enclosed in square brackets.
        if (preg_match("/^($IPv4Address|\[$IPv4Address\]|\[$IPv6Address\])$/",
                        $strDomainPortion)){
            return true;
        } else {
            $arrDomainPortion = explode('.', $strDomainPortion);
            if (!$this->allowLocalAddresses && sizeof($arrDomainPortion) < 2) {
                return false; // Not enough parts to domain
            }
            for ($i = 0, $max = sizeof($arrDomainPortion); $i < $max; $i++) {
                // Each portion must be between 1 and 63 characters, inclusive
                if (!$this->check_text_length($arrDomainPortion[$i], 1, 63)) {
                    return false;
                }
                if (!preg_match('/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|'
                   .'([A-Za-z0-9]+))$/', $arrDomainPortion[$i])) {
                    return false;
                }
                if ($i == $max - 1) { // TLD cannot be only numbers
                    if (strlen(preg_replace('/[0-9]/', '', $arrDomainPortion[$i])) <= 0) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Check given text length is between defined bounds
     * @param   strText     Text to be checked
     * @param   intMinimum  Minimum acceptable length
     * @param   intMaximum  Maximum acceptable length
     * @return  True if string is within bounds (inclusive), false if not
     */
    protected function check_text_length($strText, $intMinimum, $intMaximum) {
        // Minimum and maximum are both inclusive
        $intTextLength = strlen($strText);
        if (($intTextLength < $intMinimum) || ($intTextLength > $intMaximum)) {
            return false;
        } else {
            return true;
        }
    }

}

