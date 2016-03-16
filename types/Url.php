<?php

namespace plugin\struct\types;

use plugin\struct\meta\ValidationException;

class Url extends Text {

    protected $config = array(
        'autoscheme' => 'https',
        'prefix' => '',
        'postfix' => '',
    );

    /**
     * The final string should be an URL
     *
     * @param string $value
     * @return int|string|void
     */
    public function validate($value) {
        $value = parent::validate($value);

        $url = $this->buildURL($value);

        $schemes = getSchemes();
        $regex = '^(' . join('|', $schemes) . '):\/\/.+';
        if(!preg_match("/$regex/i", $url)) {
            throw new ValidationException('Url invalid', $url);
        }

        return $value;
    }

    /**
     * @param string $value
     * @param \Doku_Renderer $R
     * @param string $mode
     * @return bool
     */
    public function renderValue($value, \Doku_Renderer $R, $mode) {
        $url = $this->buildURL($value);
        $R->externallink($url);
        return true;
    }

    /**
     * Creates the full URL and applies the autoscheme if needed
     *
     * @param string $value
     * @return string
     */
    protected function buildURL($value) {
        $url = $this->config['prefix'] . trim($value) . $this->config['postfix'];

        if(!preg_match('/\w+:\/\//', $url)) {
            $url = $this->config['autoscheme'] . '://' . $url;
        }

        return $url;
    }

}
