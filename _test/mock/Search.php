<?php

namespace dokuwiki\plugin\struct\test\mock;

use \dokuwiki\plugin\struct\meta;

class Search extends meta\Search {
    public $schemas = array();
    /** @var  meta\Column[] */
    public $columns = array();

    public $sortby = array();

    public $filter = array();
}
