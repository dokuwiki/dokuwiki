<?php
namespace plugin\struct\types;

use dokuwiki\Form\Form;

/**
 * Class Page
 *
 * Represents a single page in the wiki. Will be linked in output.
 *
 * @package plugin\struct\types
 */
class Page extends Text {

    /**
     * Output the stored data
     *
     * @param string|int $value the value stored in the database
     * @param \Doku_Renderer $R the renderer currently used to render the data
     * @param string $mode The mode the output is rendered in (eg. XHTML)
     * @return bool true if $mode could be satisfied
     */
    public function renderValue($value, \Doku_Renderer $R, $mode) {
        $link = cleanID($this->config['prefix'] . $value . $this->config['postfix']);
        if(!$link) return true;

        $R->internallink(":$link");
        return true;
    }

}
