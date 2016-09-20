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
     * @param \string[] $rawvalues
     * @return string
     */
    public function multiValueEditor($name, $rawvalues) {
        $value = join(', ', $rawvalues);

        return
            '<div class="multiwrap">' .
            $this->valueEditor($name, $value) .
            '</div>' .
            '<small>' .
            $this->getLang('multi') .
            '</small>';
    }

}
