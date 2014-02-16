<?php

// this is not provided by DokuWiki and needs to checked out separately from
// https://github.com/splitbrain/file-icon-generator
require '/home/andi/projects/fileiconbuilder/FileIconBuilder.php';

if('cli' != php_sapi_name()) die('This has to be run from command line');
if(!defined('DOKU_INC')) define('DOKU_INC', realpath(dirname(__FILE__).'/../../../').'/');
define('NOSESSION', 1);
require_once(DOKU_INC.'inc/init.php');


/**
 * Class DokuFileIconBuilder
 *
 * overwrite mime type loading with loading DokuWiki's mime type config instead
 */
class DokuFileIconBuilder extends FileIconBuilder {

    protected function loadmimetypes(){
        $this->mimetypes = getMimeTypes();
        foreach(array_keys($this->mimetypes) as $ext) {
            $this->mimetypes[$ext] = ltrim($this->mimetypes[$ext], '!');
        }
    }
}


echo "Important: you should enable the commented file types in mime.conf to make sure the icon are generated!\n";

// generate all the icons
$DFIB = new DokuFileIconBuilder();
$DFIB->createAll(__DIR__);

echo "generation done\n";
