<?php
// phpcs:ignorefile

/**
 * AJAX Backend for indexmenu
 *
 * @author Samuele Tognini <samuele@samuele.netsons.org>
 * @license     GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

//fix for Opera XMLHttpRequests
if ($_POST === [] && @$HTTP_RAW_POST_DATA) {
    parse_str($HTTP_RAW_POST_DATA, $_POST);
}

require_once(DOKU_INC . 'inc/init.php');
require_once(DOKU_INC . 'inc/auth.php');

//close session
session_write_close();

$ajax_indexmenu = new ajax_indexmenu_plugin();
$ajax_indexmenu->render();

/**
 * Class ajax_indexmenu_plugin
 * @deprecated 2023-11 not used anymore
 */
class ajax_indexmenu_plugin
{
    /**
     * Output
     *
     * @author Samuele Tognini <samuele@samuele.netsons.org>
     */
    public function render()
    {
        $req  = $_REQUEST['req'];
        $succ = false;
        //send the zip
        if ($req == 'send' && isset($_REQUEST['t'])) {
            include(DOKU_PLUGIN . 'indexmenu/inc/repo.class.php');
            $repo = new repo_indexmenu_plugin();
            $succ = $repo->sendTheme($_REQUEST['t']);
        }
        if ($succ) return;

        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        header('Pragma: public');
        if ($req === 'local') {
            //required for admin.php
            //list themes
            echo $this->localThemes();
        }
    }

    /**
     * Print a list of local themes
     * TODO: delete this funstion; copy of this function is already in action.php
     * @author Samuele Tognini <samuele@samuele.netsons.org>
     */
    public function localThemes()
    {
        $list   = 'indexmenu,' . DOKU_URL . ",lib/plugins/indexmenu/images,";
        $data   = [];
        $handle = @opendir(DOKU_PLUGIN . "indexmenu/images");
        while (false !== ($file = readdir($handle))) {
            if (
                is_dir(DOKU_PLUGIN . 'indexmenu/images/' . $file)
                && $file != "."
                && $file != ".."
                && $file != "repository"
                && $file != "tmp"
                && $file != ".svn"
            ) {
                $data[] = $file;
            }
        }
        closedir($handle);
        sort($data);
        $list .= implode(",", $data);
        return $list;
    }
}
