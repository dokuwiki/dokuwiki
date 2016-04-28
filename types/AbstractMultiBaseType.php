<?php

namespace dokuwiki\plugin\struct\types;

/**
 * Class AbstractBaseType
 *
 * This class implements a standard multi editor that can be reused by user types. The multi-
 * edit simply joins all values with commas
 *
 * @package dokuwiki\plugin\struct\types
 * @see Column
 */
abstract class AbstractMultiBaseType extends AbstractBaseType {

    /**
     * @param string $name
     * @param \string[] $values
     * @return string
     */
    public function multiValueEditor($name, $values) {
        $value = join(', ', $values);

        return
            '<div class="multiwrap">' .
            $this->valueEditor($name, $value) .
            '</div>' .
            '<small>' .
            $this->getLang('multi') .
            '</small>';
    }

}
