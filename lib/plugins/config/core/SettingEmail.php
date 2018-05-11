<?php

namespace dokuwiki\plugin\config\core;

/**
 * Class setting_email
 */
class SettingEmail extends SettingString {
    protected $_multiple = false;
    protected $_placeholders = false;

    /**
     * update setting with user provided value $input
     * if value fails error check, save it
     *
     * @param mixed $input
     * @return boolean true if changed, false otherwise (incl. on error)
     */
    public function update($input) {
        if(is_null($input)) return false;
        if($this->is_protected()) return false;

        $value = is_null($this->_local) ? $this->_default : $this->_local;
        if($value == $input) return false;
        if($input === '') {
            $this->_local = $input;
            return true;
        }
        $mail = $input;

        if($this->_placeholders) {
            // replace variables with pseudo values
            $mail = str_replace('@USER@', 'joe', $mail);
            $mail = str_replace('@NAME@', 'Joe Schmoe', $mail);
            $mail = str_replace('@MAIL@', 'joe@example.com', $mail);
        }

        // multiple mail addresses?
        if($this->_multiple) {
            $mails = array_filter(array_map('trim', explode(',', $mail)));
        } else {
            $mails = array($mail);
        }

        // check them all
        foreach($mails as $mail) {
            // only check the address part
            if(preg_match('#(.*?)<(.*?)>#', $mail, $matches)) {
                $addr = $matches[2];
            } else {
                $addr = $mail;
            }

            if(!mail_isvalid($addr)) {
                $this->_error = true;
                $this->_input = $input;
                return false;
            }
        }

        $this->_local = $input;
        return true;
    }
}
