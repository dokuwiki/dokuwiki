<?php
/**
 * DokuWiki Plugin styler (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class admin_plugin_styler extends DokuWiki_Admin_Plugin {

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
        echo '<div id="plugin__styler">';
        $this->form(false);
        echo '</div>';
    }

    /**
     * Create the actual editing form
     */
    public function form($isajax) {
        global $conf;
        global $ID;
        $tpl = $conf['template'];
        define('SIMPLE_TEST', 1); // hack, ideally certain functions should be moved out of css.php
        require_once(DOKU_INC.'lib/exe/css.php');
        $styleini     = css_styleini($conf['template'], true);
        $replacements = $styleini['replacements'];

        if($isajax) {
            $target = wl($ID, array('do' => 'styler_plugin'));
        } else {
            $target = wl($ID, array('do' => 'admin', 'page' => 'styler'));
        }

        ptln('<h1>'.$this->getLang('menu').'</h1>');

        if(empty($replacements)) {
            echo '<p class="error">Sorry, this template does not support this functionality.</p>';
        } else {
            echo '<p>Intro blah... for the currently active template ("'.$tpl.'")... not all variables preview...</p>';

            echo '<form class="styler" method="post" action="'.$target.'">';
            echo '<h2>Template variables</h2>';
            echo '<table>';
            foreach($replacements as $key => $value) {
                echo '<tr>';
                echo '<td>'.$key.'</td>';
                echo '<td><input name="tpl['.hsc($key).']" value="'.hsc($value).'" />';
                echo '</tr>';
            }
            echo '</table>';
            echo '<input type="submit" name="run[preview]" value="preview">';
            echo '<input type="submit" name="run[reset]" value="reset current">'; #FIXME only if preview.ini exists
            echo '<input type="submit" name="run[revert]" value="revert to original">'; #FIXME only if local.ini exists
            echo '<input type="submit" name="run[save]" value="save">';
            echo '</form>';
        }
    }

    /**
     * saves the preview.ini
     */
    protected function run_preview() {
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
        $ini .= ";These overwrites have been generated from the Template Styler Admin interface\n";
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