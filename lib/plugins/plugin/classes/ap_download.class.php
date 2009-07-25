<?php
class ap_download extends ap_manage {

    var $overwrite = false;

    /**
     * Initiate the plugin download
     */
    function process() {
        global $lang;

        $plugin_url = $_REQUEST['url'];
        $this->download($plugin_url, $this->overwrite);
        return '';
    }

    /**
     * Print results of the download
     */
    function html() {
        parent::html();

        ptln('<div class="pm_info">');
        ptln('<h2>'.$this->lang['downloading'].'</h2>');

        if ($this->manager->error) {
            ptln('<div class="error">'.str_replace("\n","<br />",$this->manager->error).'</div>');
        } else if (count($this->downloaded) == 1) {
            ptln('<p>'.sprintf($this->lang['downloaded'],$this->downloaded[0]).'</p>');
        } else if (count($this->downloaded)) {   // more than one plugin in the download
            ptln('<p>'.$this->lang['downloads'].'</p>');
            ptln('<ul>');
            foreach ($this->downloaded as $plugin) {
                ptln('<li><div class="li">'.$plugin.'</div></li>',2);
            }
            ptln('</ul>');
        } else {        // none found in download
            ptln('<p>'.$this->lang['download_none'].'</p>');
        }
        ptln('</div>');
    }

    /**
     * Process the downloaded file
     */
    function download($url, $overwrite=false) {
        global $lang;

        // check the url
        $matches = array();
        if (!preg_match("/[^\/]*$/", $url, $matches) || !$matches[0]) {
            $this->manager->error = $this->lang['error_badurl']."\n";
            return false;
        }

        $file = $matches[0];

        if (!($tmp = io_mktmpdir())) {
            $this->manager->error = $this->lang['error_dircreate']."\n";
            return false;
        }

        if (!$file = io_download($url, "$tmp/", true, $file)) {
            $this->manager->error = sprintf($this->lang['error_download'],$url)."\n";
        }

        if (!$this->manager->error && !$this->decompress("$tmp/$file", $tmp)) {
            $this->manager->error = sprintf($this->lang['error_decompress'],$file)."\n";
        }

        // search $tmp for the folder(s) that has been created
        // move the folder(s) to lib/plugins/
        if (!$this->manager->error) {
            if ($dh = @opendir("$tmp/")) {
                while (false !== ($f = readdir($dh))) {
                    if ($f == '.' || $f == '..' || $f == 'tmp') continue;
                    if (!is_dir("$tmp/$f")) continue;

                    // check to make sure we aren't overwriting anything
                    if (!$overwrite && @file_exists(DOKU_PLUGIN.$f)) {
                        // remember our settings, ask the user to confirm overwrite, FIXME
                        continue;
                    }

                    $instruction = @file_exists(DOKU_PLUGIN.$f) ? 'update' : 'install';

                    if ($this->dircopy("$tmp/$f", DOKU_PLUGIN.$f)) {
                        $this->downloaded[] = $f;
                        $this->plugin_writelog($f, $instruction, array($url));
                    } else {
                        $this->manager->error .= sprintf($this->lang['error_copy']."\n", $f);
                    }
                }
                closedir($dh);
            } else {
                $this->manager->error = $this->lang['error']."\n";
            }
        }

        // cleanup
        if ($tmp) $this->dir_delete($tmp);

        if (!$this->manager->error) {
            msg('Plugin package ('.count($this->downloaded).' plugin'.(count($this->downloaded) != 1?'s':'').': '.join(',',$this->downloaded).') successfully installed.',1);
            $this->refresh();
            return true;
        }

        return false;
    }


    /**
     * Decompress a given file to the given target directory
     *
     * Determines the compression type from the file extension
     */
    function decompress($file, $target) {
        global $conf;

        // decompression library doesn't like target folders ending in "/"
        if (substr($target, -1) == "/") $target = substr($target, 0, -1);
        $ext = substr($file, strrpos($file,'.')+1);

        // .tar, .tar.bz, .tar.gz, .tgz
        if (in_array($ext, array('tar','bz','bz2','gz','tgz'))) {

            require_once(DOKU_INC."inc/TarLib.class.php");

            if (strpos($ext, 'bz') !== false) $compress_type = COMPRESS_BZIP;
            else if (strpos($ext,'gz') !== false) $compress_type = COMPRESS_GZIP;
            else $compress_type = COMPRESS_NONE;

            $tar = new TarLib($file, $compress_type);
            if($tar->_initerror < 0){
                if($conf['allowdebug']){
                    msg('TarLib Error: '.$tar->TarErrorStr($tar->_initerror),-1);
                }
                return false;
            }
            $ok = $tar->Extract(FULL_ARCHIVE, $target, '', 0777);

            if($ok<1){
                if($conf['allowdebug']){
                    msg('TarLib Error: '.$tar->TarErrorStr($ok),-1);
                }
                return false;
            }
            return true;
        } else if ($ext == 'zip') {

            require_once(DOKU_INC."inc/ZipLib.class.php");

            $zip = new ZipLib();
            $ok = $zip->Extract($file, $target);

            // FIXME sort something out for handling zip error messages meaningfully
            return ($ok==-1?false:true);

        }  else if ($ext == "rar") {
            // not yet supported -- fix me
            return false;
        }

        // unsupported file type
        return false;
    }

    /**
     * Copy with recursive sub-directory support
     */
    function dircopy($src, $dst) {
        global $conf;

        if (is_dir($src)) {
            if (!$dh = @opendir($src)) return false;

            if ($ok = io_mkdir_p($dst)) {
                while ($ok && (false !== ($f = readdir($dh)))) {
                    if ($f == '..' || $f == '.') continue;
                    $ok = $this->dircopy("$src/$f", "$dst/$f");
                }
            }

            closedir($dh);
            return $ok;

        } else {
            $exists = @file_exists($dst);

            if (!@copy($src,$dst)) return false;
            if (!$exists && !empty($conf['fperm'])) chmod($dst, $conf['fperm']);
            @touch($dst,filemtime($src));
        }

        return true;
    }


}

