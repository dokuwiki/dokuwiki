<?php
/**
 * DokuWiki Plugin styling (Admin Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
class admin_plugin_styling extends DokuWiki_Admin_Plugin
{

    public $ispopup = false;

    /**
     * @return int sort number in admin menu
     */
    public function getMenuSort()
    {
        return 1000;
    }

    /**
     * @return bool true if only access for superuser, false is for superusers and moderators
     */
    public function forAdminOnly()
    {
        return true;
    }

    /**
     * handle the different actions (also called from ajax)
     */
    public function handle()
    {
        global $INPUT;
        $run = $INPUT->extract('run')->str('run');
        if (!$run) return;
        $run = 'run'.ucfirst($run);
        $this->$run();
    }

    /**
     * Render HTML output, e.g. helpful text and a form
     */
    public function html()
    {
        $class = 'nopopup';
        if ($this->ispopup) $class = 'ispopup page';

        echo '<div id="plugin__styling" class="'.$class.'">';
        ptln('<h1>'.$this->getLang('menu').'</h1>');
        $this->form();
        echo '</div>';
    }

    /**
     * Create the actual editing form
     */
    public function form()
    {
        global $conf;
        global $ID;

        $styleUtil = new \dokuwiki\StyleUtils($conf['template'], true, true);
        $styleini     = $styleUtil->cssStyleini();
        $replacements = $styleini['replacements'];

        if ($this->ispopup) {
            $target = DOKU_BASE.'lib/plugins/styling/popup.php';
        } else {
            $target = wl($ID, array('do' => 'admin', 'page' => 'styling'));
        }

        if (empty($replacements)) {
            echo '<p class="error">'.$this->getLang('error').'</p>';
        } else {
            echo $this->locale_xhtml('intro');

            echo '<form class="styling" method="post" action="'.$target.'">';

            echo '<table><tbody>';
            foreach ($replacements as $key => $value) {
                $name = tpl_getLang($key);
                if (empty($name)) $name = $this->getLang($key);
                if (empty($name)) $name = $key;

                echo '<tr>';
                echo '<td><label for="tpl__'.hsc($key).'">'.$name.'</label></td>';
                echo '<td><input type="'.$this->colorType($value).'" name="tpl['.hsc($key).']" id="tpl__'.hsc($key).'"
                    value="'.hsc($this->colorValue($value)).'" dir="ltr" /></td>';
                echo '</tr>';
            }
            echo '</tbody></table>';

            echo '<p>';
            echo '<button type="submit" name="run[preview]" class="btn_preview primary">'.
                $this->getLang('btn_preview').'</button> ';
            #FIXME only if preview.ini exists:
            echo '<button type="submit" name="run[reset]">'.$this->getLang('btn_reset').'</button>';
            echo '</p>';

            echo '<p>';
            echo '<button type="submit" name="run[save]" class="primary">'.$this->getLang('btn_save').'</button>';
            echo '</p>';

            echo '<p>';
            #FIXME only if local.ini exists:
            echo '<button type="submit" name="run[revert]">'.$this->getLang('btn_revert').'</button>';
            echo '</p>';

            echo '</form>';

            echo tpl_locale_xhtml('style');
        }
    }

    /**
     * Adjust three char color codes to the 6 char one supported by browser's color input
     *
     * @param string $value
     * @return string
     */
    protected function colorValue($value)
    {
        if (preg_match('/^#([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])$/', $value, $match)) {
            return '#' . $match[1] . $match[1] . $match[2] . $match[2] . $match[3] . $match[3];
        }
        return $value;
    }

    /**
     * Decide the input type based on the value
     *
     * @param string $value
     * @return string color|text
     */
    protected function colorType($value)
    {
        if (preg_match('/^#([0-9a-fA-F]{3}){1,2}$/', $value)) {
            return 'color';
        } else {
            return 'text';
        }
    }

    /**
     * saves the preview.ini (alos called from ajax directly)
     */
    public function runPreview()
    {
        global $conf;
        $ini = $conf['cachedir'].'/preview.ini';
        io_saveFile($ini, $this->makeini());
    }

    /**
     * deletes the preview.ini
     */
    protected function runReset()
    {
        global $conf;
        $ini = $conf['cachedir'].'/preview.ini';
        io_saveFile($ini, '');
    }

    /**
     * deletes the local style.ini replacements
     */
    protected function runRevert()
    {
        $this->replaceIni('');
        $this->runReset();
    }

    /**
     * save the local style.ini replacements
     */
    protected function runSave()
    {
        $this->replaceIni($this->makeini());
        $this->runReset();
    }

    /**
     * create the replacement part of a style.ini from submitted data
     *
     * @return string
     */
    protected function makeini()
    {
        global $INPUT;

        $ini = "[replacements]\n";
        $ini .= ";These overwrites have been generated from the Template styling Admin interface\n";
        $ini .= ";Any values in this section will be overwritten by that tool again\n";
        foreach ($INPUT->arr('tpl') as $key => $val) {
            $ini .= $key.' = "'.addslashes($val).'"'."\n";
        }

        return $ini;
    }

    /**
     * replaces the replacement parts in the local ini
     *
     * @param string $new the new ini contents
     */
    protected function replaceIni($new)
    {
        global $conf;
        $ini = DOKU_CONF."tpl/".$conf['template']."/style.ini";
        if (file_exists($ini)) {
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
