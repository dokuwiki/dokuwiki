<?php

namespace dokuwiki\plugin\config\core\Setting;

/**
 * Class setting_email
 */
class SettingEmail extends SettingString {
    protected $multiple = false;
    protected $placeholders = false;

    /** @inheritdoc */
    public function update($input) {
        if(is_null($input)) return false;
        if($this->isProtected()) return false;

        $value = is_null($this->local) ? $this->default : $this->local;
        if($value == $input) return false;
        if($input === '') {
            $this->local = $input;
            return true;
        }
        $mail = $input;

        if($this->placeholders) {
            // replace variables with pseudo values
            $mail = str_replace('@USER@', 'joe', $mail);
            $mail = str_replace('@NAME@', 'Joe Schmoe', $mail);
            $mail = str_replace('@MAIL@', 'joe@example.com', $mail);
        }

        // multiple mail addresses?
        if($this->multiple) {
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
                $this->error = true;
                $this->input = $input;
                return false;
            }
        }

        $this->local = $input;
        return true;
    }
}
