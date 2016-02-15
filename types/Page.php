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
class Page extends AbstractBaseType {

    // FIXME we will probably want to have some prefix/postfix configuration here later
    protected $config = array(
    );

    /**
     * Output the stored data
     *
     * @param int|string $value
     * @return string the HTML to represent this data
     */
    public function getDisplayData($value) {
        return html_wikilink(":$value");
    }

}
