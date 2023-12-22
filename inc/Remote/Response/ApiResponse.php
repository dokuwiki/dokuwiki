<?php

namespace dokuwiki\Remote\Response;

/**
 * These are simple data objects that hold the response data API calls
 *
 * They are transmitted as associative arrays automatically created by
 * converting the object to an array
 */
abstract class ApiResponse
{
    /**
     * Initialize the response object with the given data
     *
     * Each response object has different properties and might get passed different data from
     * various internal methods. The constructor should handle all of that and also fill up
     * missing properties when needed.
     *
     * @param array $data
     */
    abstract public function __construct($data);
}
