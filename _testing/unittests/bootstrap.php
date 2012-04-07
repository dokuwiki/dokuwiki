<?php

define('DOKU_UNITTEST', true);
define('SIMPLE_TEST', true);
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
define('DOKU_CONF',realpath(dirname(__FILE__).'/../../conf').'/');

error_reporting(E_ALL);
set_time_limit(0);
ini_set('memory_limit','2048M');
