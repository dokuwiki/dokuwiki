<?php
namespace plugin\struct\types;

use plugin\struct\meta\ValidationException;

class Mail extends Text {

    protected $config = array(
        'prefix' => '',
        'postfix' => '',
    );

    /**
     * Output the stored data
     *
     * @param string|int $value the value stored in the database
     * @param \Doku_Renderer $R the renderer currently used to render the data
     * @param string $mode The mode the output is rendered in (eg. XHTML)
     * @return bool true if $mode could be satisfied
     */
    public function renderValue($value, \Doku_Renderer $R, $mode) {
        $mail = $this->config['prefix'] . $value . $this->config['postfix'];
        $R->emaillink($mail);
        return true;
    }

    /**
     * Validate
     *
     * @param int|string $value
     */
    public function validate($value) {
        $mail = $this->config['prefix'] . $value . $this->config['postfix'];
        if(!mail_isvalid($mail)) {
            throw new ValidationException('Mail invalid', $mail);
        }
    }

}
