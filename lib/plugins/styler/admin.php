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
     * Should carry out any processing required by the plugin.
     */
    public function handle() {
        set_doku_pref('styler_plugin', 1);
    }

    /**
     * Render HTML output, e.g. helpful text and a form
     */
    public function html() {
        global $conf;
        $tpl = $conf['template'];
        define('SIMPLE_TEST',1); // hack, ideally certain functions should be moved out of css.php
        require_once(DOKU_INC.'lib/exe/css.php');
        $styleini = css_styleini($conf['template'], true);
        $replacements = $styleini['replacements'];

        ptln('<h1>'.$this->getLang('menu').'</h1>');

        if (empty($replacements)) {
            echo '<p class="error">Sorry, this template does not support this functionality.</p>';
        } else {
            echo '<p>Intro blah... for the currently active template ("'.$tpl.'")... not all variables preview...</p>';

            echo '<form class="styler" id="plugin__styler" method="post">';
            echo '<h2>Template variables</h2>';
            echo '<table>';
            foreach($replacements as $key => $value){
                echo '<tr>';
                echo '<td>'.$key.'</td>';
                echo '<td><input name="tpl['.hsc($key).']" value="'.hsc($value).'" />';
                echo '</tr>';
            }
            echo '</table>';
            echo '<input type="submit" name="do[styler_plugin_preview]" value="preview">';
            echo '<input type="submit" name="do[styler_plugin_reset]" value="reset current">'; #FIXME only if preview.ini exists
            echo '<input type="submit" name="do[styler_plugin_revert]" value="revert to original">'; #FIXME only if local.ini exists
            echo '<input type="submit" name="do[styler_plugin_save]" value="save">';
            echo '</form>';
        }



    }


}

// vim:ts=4:sw=4:et: