<?php
/**
 * Configuration Class
 *
 * @author  Chris Smith <chris@jalakai.co.uk>
 * @author  Ben Coburn <btcoburn@silicodon.net>
 */

namespace dokuwiki\plugin\config\core;

/**
 * Class configuration
 */
class Configuration {

    const KEYMARKER = '____';

    /** @var ConfigSettings FIXME better name? */
    protected $confset;


    /**
     * constructor
     */
    public function __construct() {
        $this->confset = new ConfigSettings();
    }

    /**
     * Stores setting[] array to file
     *
     * @return bool succesful?
     */
    public function save_settings() {
        $writer = new Writer();
        try {
            $writer->save($this->confset->getSettings());
        } catch(\Exception $e) {
            // fixme show message
            return false;
        }
        return true;
    }


}

