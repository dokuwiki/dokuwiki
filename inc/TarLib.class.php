<?php

/**
 * This is a compatibility wrapper around the new Tar class
 *
 * Use of this library is strongly discouraged. Only basic extraction is wrapped,
 * everything else will fail.
 *
 * @deprecated 2012-11-06
 */
class TarLib {

    const   COMPRESS_GZIP      = 1;
    const   COMPRESS_BZIP      = 2;
    const   COMPRESS_AUTO      = 3;
    const   COMPRESS_NONE      = 0;
    const   TARLIB_VERSION     = '1.2';
    const   FULL_ARCHIVE       = -1;
    const   ARCHIVE_DYNAMIC    = 0;
    const   ARCHIVE_RENAMECOMP = 5;
    const   COMPRESS_DETECT    = -1;

    private $file = '';
    private $tar;

    public $_result = true;

    function __construct($file, $comptype = TarLib::COMPRESS_AUTO, $complevel = 9) {
        dbg_deprecated('class Tar');

        if(!$file) $this->error('__construct', '$file');

        $this->file = $file;
        switch($comptype) {
            case TarLib::COMPRESS_AUTO:
            case TarLib::COMPRESS_DETECT:
                $comptype = Tar::COMPRESS_AUTO;
                break;
            case TarLib::COMPRESS_GZIP:
                $comptype = Tar::COMPRESS_GZIP;
                break;
            case TarLib::COMPRESS_BZIP:
                $comptype = Tar::COMPRESS_BZIP;
                break;
            default:
                $comptype = Tar::COMPRESS_NONE;
        }

        $this->complevel = $complevel;

        try {
            $this->tar = new Tar();
            $this->tar->open($file, $comptype);
        } catch(Exception $e) {
            $this->_result = false;
        }
    }

    function Extract($p_what = TarLib::FULL_ARCHIVE, $p_to = '.', $p_remdir = '', $p_mode = 0755) {
        if($p_what != TarLib::FULL_ARCHIVE) {
            $this->error('Extract', 'Ep_what');
            return 0;
        }

        try {
            $this->tar->extract($p_to, $p_remdir);
        } catch(Exception $e) {
            return 0;
        }
        return 1;
    }

    function error($func, $param = '') {
        $error = 'TarLib is deprecated and should no longer be used.';

        if($param) {
            $error .= "In this compatibility wrapper, the function '$func' does not accept your value for".
                "the parameter '$param' anymore.";
        } else {
            $error .= "The function '$func' no longer exists in this compatibility wrapper.";
        }

        msg($error, -1);
    }

    function __call($name, $arguments) {
        $this->error($name);
    }
}