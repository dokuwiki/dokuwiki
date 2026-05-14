<?php
// phpcs:ignorefile

/* @deprecated 2023-11 used in ajax.php, which is not used anymore */
class repo_indexmenu_plugin
{
    /**
     * Send a zipped theme
     *
     * @author Samuele Tognini <samuele@samuele.netsons.org>
     */

    public function sendTheme($file)
    {
        require_once(DOKU_PLUGIN . 'indexmenu/syntax/indexmenu.php');
        $idxm = new syntax_plugin_indexmenu_indexmenu();
        //clean the file name
        $file = cleanID($file);
        //check config
        if (!$idxm->getConf('be_repo') || empty($file)) return false;
        $repodir    = DOKU_PLUGIN . "indexmenu/images/repository";
        $zipfile    = $repodir . "/$file.zip";
        $localtheme = DOKU_PLUGIN . "indexmenu/images/$file/";
        //theme does not exists
        if (!file_exists($localtheme)) return false;
        if (!io_mkdir_p($repodir)) return false;
        $lm = @filemtime($zipfile);
        //no cached zip or older than 1 day
        if ($lm < time() - (60 * 60 * 24)) {
            //create the zip
            require_once(DOKU_PLUGIN . "indexmenu/inc/pclzip.lib.php");
            @unlink($zipfile);
            $zip    = new PclZip($zipfile);
            $status = $zip->add($localtheme, PCLZIP_OPT_REMOVE_ALL_PATH);
            //error
            if ($status == 0) return false;
        }
        $len = (int) filesize($zipfile);
        //don't send large zips
        if ($len > 2 * 1024 * 1024) return false;
        //headers
        header('Cache-Control: must-revalidate, no-transform, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipfile) . '";');
        header("Content-Transfer-Encoding: binary");
        //send zip
        $fp = @fopen($zipfile, 'rb');
        if ($fp) {
            $ct = @fread($fp, $len);
            echo $ct;
        }
        @fclose($fp);
        return true;
    }
}
