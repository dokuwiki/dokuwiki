<?php

namespace plugin\struct\test\mock;

use \plugin\struct\meta;

class Search extends meta\Search {
    public $schemas = array();
    /** @var  meta\Column[] */
    public $columns = array();

    public $sortby = array();
}
