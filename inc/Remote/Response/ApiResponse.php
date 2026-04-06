<?php

namespace dokuwiki\Remote\Response;

/**
 * These are simple data objects that hold the response data API calls
 *
 * They are transmitted as associative arrays automatically created by
 * converting the object to an array using all public properties.
 */
abstract class ApiResponse implements \Stringable
{
    /**
     * A string representation of this object
     *
     * Used for sorting and debugging
     *
     * @return string
     */
    abstract public function __toString(): string;
}
