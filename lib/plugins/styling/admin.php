<?php
/**
 * DokuWiki Plugin styling (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class admin_plugin_styling extends DokuWiki_Admin_Plugin {

    public $ispopup = false;

    /**
     * @return int sort number in admin menu
     */
    public function getMenuSort() {
        return 1000;
    }

    /**
     * @return bool true if only access for superuser, false is for superusers and moderators
     */
    public function forAdminOnly() {
        return true;
    }

    /**
     * handle the different actions (also called from ajax)
     */
    public function handle() {
        global $INPUT;
        $run = $INPUT->extract('run')->str('run');
        if(!$run) return;
        $run = "run_$run";
        $this->$run();
    }

    /**
     * Render HTML output, e.g. helpful text and a form
     */
    public function html() {
        $class = 'nopopup';
        if($this->ispopup) $class = 'ispopup page';

        echo '<div id="plugin__styling" class="'.$class.'">';
        ptln('<h1>'.$this->getLang('menu').'</h1>');
        $this->form();
        echo '</div>';
    }

    /**
     * Create the actual editing form
     */
    public function form() {
        global $conf;
        global $ID;
        define('SIMPLE_TEST', 1); // hack, ideally certain functions should be moved out of css.php
        require_once(DOKU_INC.'lib/exe/css.php');
        $styleini     = css_styleini($conf['template'], true);
        $replacements = $styleini['replacements'];

        if($this->ispopup) {
            $target = DOKU_BASE.'lib/plugins/styling/popup.php';
        } else {
            $target = wl($ID, array('do' => 'admin', 'page' => 'styling'));
        }

        if(empty($replacements)) {
            echo '<p class="error">'.$this->getLang('error').'</p>';
        } else {
            echo $this->locale_xhtml('intro');

            echo '<form class="styling" method="post" action="'.$target.'">';

            echo '<table><tbody>';
            foreach($replacements as $key => $value) {
                $name = tpl_getLang($key);
                if(empty($name)) $name = $this->getLang($key);
                if(empty($name)) $name = $key;

                echo '<tr>';
                echo '<td><label for="tpl__'.hsc($key).'">'.$name.'</label></td>';
                echo '<td><input type="text" name="tpl['.hsc($key).']" id="tpl__'.hsc($key).'" value="'.hsc($value).'" '.$this->colorClass($key).' dir="ltr" /></td>';
                echo '</tr>';
            }
            echo '</tbody></table>';

            echo '<p>';
            echo '<button type="submit" name="run[preview]" class="btn_preview primary">'.$this->getLang('btn_preview').'</button> ';
            echo '<button type="submit" name="run[reset]">'.$this->getLang('btn_reset').'</button>'; #FIXME only if preview.ini exists
            echo '</p>';

            echo '<p>';
            echo '<button type="submit" name="run[save]" class="primary">'.$this->getLang('btn_save').'</button>';
            echo '</p>';

            echo '<p>';
            echo '<button type="submit" name="run[revert]">'.$this->getLang('btn_revert').'</button>'; #FIXME only if local.ini exists
            echo '</p>';

            echo '</form>';

            echo tpl_locale_xhtml('style');

        }
    }

    /**
     * set the color class attribute
     */
    protected function colorClass($key) {
        static $colors = array(
            'text',
            'background',
            'text_alt',
            'background_alt',
            'text_neu',
            'background_neu',
            'border',
            'highlight',
            'background_site',
            'link',
            'existing',
            'missing',
        );

        if(preg_match('/colou?r/', $key) || in_array(trim($key,'_'), $colors)) {
            return 'class="color"';
        } else {
            return '';
        }
    }

    /**
     * saves the preview.ini (alos called from ajax directly)
     */
    public function run_preview() {
        global $conf;
        $ini = $conf['cachedir'].'/preview.ini';
        io_saveFile($ini, $this->makeini());
    }

    /**
     * deletes the preview.ini
     */
    protected function run_reset() {
        global $conf;
        $ini = $conf['cachedir'].'/preview.ini';
        io_saveFile($ini, '');
    }

    /**
     * deletes the local style.ini replacements
     */
    protected function run_revert() {
        $this->replaceini('');
        $this->run_reset();
    }

    /**
     * save the local style.ini replacements
     */
    protected function run_save() {
        $this->replaceini($this->makeini());
        $this->run_reset();
    }

    /**
     * create the replacement part of a style.ini from submitted data
     *
     * @return string
     */
    protected function makeini() {
        global $INPUT;

        $ini = "[replacements]\n";
        $ini .= ";These overwrites have been generated from the Template styling Admin interface\n";
        $ini .= ";Any values in this section will be overwritten by that tool again\n";
        foreach($INPUT->arr('tpl') as $key => $val) {
            $ini .= $key.' = "'.addslashes($val).'"'."\n";
        }

        return $ini;
    }

    /**
     * replaces the replacement parts in the local ini
     *
     * @param string $new the new ini contents
     */
    protected function replaceini($new) {
        global $conf;
        $ini = DOKU_CONF."tpl/".$conf['template']."/style.ini";
        if(file_exists($ini)) {
            $old = io_readFile($ini);
            $old = preg_replace('/\[replacements\]\n.*?(\n\[.*]|$)/s', '\\1', $old);
            $old = trim($old);
        } else {
            $old = '';
        }

        io_makeFileDir($ini);
        io_saveFile($ini, "$old\n\n$new");
    }

}

// vim:ts=4:sw=4:et:
