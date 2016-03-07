<?php

namespace plugin\struct\types;

use plugin\struct\meta\ValidationException;

class Url extends Text {

    /**
     * The final string should be an URL
     *
     * @param string $value
     */
    public function validate($value) {
        $url = $this->config['prefix'] . trim($value) . $this->config['postfix'];

        $schemes = getSchemes();
        $regex = '^(' . join('|', $schemes) . '):\/\/.+';
        if(!preg_match("/$regex/i", $url)) {
            throw new ValidationException('Url invalid', $url);
        }
    }

    /**
     * @param string $value
     * @param \Doku_Renderer $R
     * @param string $mode
     * @return bool
     */
    public function renderValue($value, \Doku_Renderer $R, $mode) {
        $url = $this->config['prefix'] . trim($value) . $this->config['postfix'];
        $R->externallink($url);
        return true;
    }

}
