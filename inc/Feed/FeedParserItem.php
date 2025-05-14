<?php

namespace dokuwiki\Feed;

use SimplePie\Item;
use SimplePie\SimplePie;

use function PHP81_BC\strftime;

class FeedParserItem extends Item
{
    /**
     * replace strftime with PHP81_BC\strftime
     * @inheritdoc
     * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
     */
    public function get_local_date($date_format = '%c')
    {
        if (!$date_format) {
            return $this->sanitize($this->get_date(''), SimplePie::CONSTRUCT_TEXT);
        } elseif (($date = $this->get_date('U')) !== null && $date !== false) {
            return strftime($date_format, $date);
        }

        return null;
    }
}
