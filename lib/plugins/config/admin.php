<?php
/**
 * Configuration Manager admin plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 * @author     Ben Coburn <btcoburn@silicodon.net>
 */

use dokuwiki\plugin\config\core\Configuration;
use dokuwiki\plugin\config\core\Setting\Setting;
use dokuwiki\plugin\config\core\Setting\SettingFieldset;
use dokuwiki\plugin\config\core\Setting\SettingHidden;

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_config extends DokuWiki_Admin_Plugin {

    const IMGDIR = DOKU_BASE . 'lib/plugins/config/images/';

    /** @var Configuration */
    protected $configuration;

    /** @var bool were there any errors in the submitted data? */
    protected $hasErrors = false;

    /** @var bool have the settings translations been loaded? */
    protected $promptsLocalized = false;


    /**
     * handle user request
     */
    public function handle() {
        global $ID, $INPUT;

        // always initialize the configuration
        $this->configuration = new Configuration();

        if(!$INPUT->bool('save') || !checkSecurityToken()) {
            return;
        }

        // don't go any further if the configuration is locked
        if($this->configuration->isLocked()) return;

        // update settings and redirect of successful
        $ok = $this->configuration->updateSettings($INPUT->arr('config'));
        if($ok) { // no errors
            try {
                if($this->configuration->hasChanged()) {
                    $this->configuration->save();
                } else {
                    $this->configuration->touch();
                }
                msg($this->getLang('updated'), 1);
            } catch(Exception $e) {
                msg($this->getLang('error'), -1);
            }
            send_redirect(wl($ID, array('do' => 'admin', 'page' => 'config'), true, '&'));
        } else {
            $this->hasErrors = true;
        }
    }

    /**
     * output appropriate html
     */
    public function html() {
        $allow_debug = $GLOBALS['conf']['allowdebug']; // avoid global $conf; here.
        global $lang;
        global $ID;

        $this->setupLocale(true);

        echo $this->locale_xhtml('intro');

        echo '<div id="config__manager">';

        if($this->configuration->isLocked()) {
            echo '<div class="info">' . $this->getLang('locked') . '</div>';
        }

        // POST to script() instead of wl($ID) so config manager still works if
        // rewrite config is broken. Add $ID as hidden field to remember
        // current ID in most cases.
        echo '<form id="dw__configform" action="' . script() . '" method="post">';
        echo '<div class="no"><input type="hidden" name="id" value="' . $ID . '" /></div>';
        formSecurityToken();
        $this->printH1('dokuwiki_settings', $this->getLang('_header_dokuwiki'));

        $in_fieldset = false;
        $first_plugin_fieldset = true;
        $first_template_fieldset = true;
        foreach($this->configuration->getSettings() as $setting) {
            if(is_a($setting, SettingHidden::class)) {
                continue;
            } else if(is_a($setting, settingFieldset::class)) {
                // config setting group
                if($in_fieldset) {
                    echo '</table>';
                    echo '</div>';
                    echo '</fieldset>';
                } else {
                    $in_fieldset = true;
                }
                if($first_plugin_fieldset && $setting->getType() == 'plugin') {
                    $this->printH1('plugin_settings', $this->getLang('_header_plugin'));
                    $first_plugin_fieldset = false;
                } else if($first_template_fieldset && $setting->getType() == 'template') {
                    $this->printH1('template_settings', $this->getLang('_header_template'));
                    $first_template_fieldset = false;
                }
                echo '<fieldset id="' . $setting->getKey() . '">';
                echo '<legend>' . $setting->prompt($this) . '</legend>';
                echo '<div class="table">';
                echo '<table class="inline">';
            } else {
                // config settings
                list($label, $input) = $setting->html($this, $this->hasErrors);

                $class = $setting->isDefault()
                    ? ' class="default"'
                    : ($setting->isProtected() ? ' class="protected"' : '');
                $error = $setting->hasError()
                    ? ' class="value error"'
                    : ' class="value"';
                $icon = $setting->caution()
                    ? '<img src="' . self::IMGDIR . $setting->caution() . '.png" ' .
                    'alt="' . $setting->caution() . '" title="' . $this->getLang($setting->caution()) . '" />'
                    : '';

                echo '<tr' . $class . '>';
                echo '<td class="label">';
                echo '<span class="outkey">' . $setting->getPrettyKey() . '</span>';
                echo $icon . $label;
                echo '</td>';
                echo '<td' . $error . '>' . $input . '</td>';
                echo '</tr>';
            }
        }

        echo '</table>';
        echo '</div>';
        if($in_fieldset) {
            echo '</fieldset>';
        }

        // show undefined settings list
        $undefined_settings = $this->configuration->getUndefined();
        if($allow_debug && !empty($undefined_settings)) {
            /**
             * Callback for sorting settings
             *
             * @param Setting $a
             * @param Setting $b
             * @return int if $a is lower/equal/higher than $b
             */
            function settingNaturalComparison($a, $b) {
                return strnatcmp($a->getKey(), $b->getKey());
            }

            usort($undefined_settings, 'settingNaturalComparison');
            $this->printH1('undefined_settings', $this->getLang('_header_undefined'));
            echo '<fieldset>';
            echo '<div class="table">';
            echo '<table class="inline">';
            foreach($undefined_settings as $setting) {
                list($label, $input) = $setting->html($this);
                echo '<tr>';
                echo '<td class="label">' . $label . '</td>';
                echo '<td>' . $input . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';
            echo '</fieldset>';
        }

        // finish up form
        echo '<p>';
        echo '<input type="hidden" name="do"     value="admin" />';
        echo '<input type="hidden" name="page"   value="config" />';

        if(!$this->configuration->isLocked()) {
            echo '<input type="hidden" name="save"   value="1" />';
            echo '<button type="submit" name="submit" accesskey="s">' . $lang['btn_save'] . '</button>';
            echo '<button type="reset">' . $lang['btn_reset'] . '</button>';
        }

        echo '</p>';

        echo '</form>';
        echo '</div>';
    }

    /**
     * @param bool $prompts
     */
    public function setupLocale($prompts = false) {
        parent::setupLocale();
        if(!$prompts || $this->promptsLocalized) return;
        $this->lang = array_merge($this->lang, $this->configuration->getLangs());
        $this->promptsLocalized = true;
    }

    /**
     * Generates a two-level table of contents for the config plugin.
     *
     * @author Ben Coburn <btcoburn@silicodon.net>
     *
     * @return array
     */
    public function getTOC() {
        $this->setupLocale(true);

        $allow_debug = $GLOBALS['conf']['allowdebug']; // avoid global $conf; here.
        $toc = array();
        $check = false;

        // gather settings data into three sub arrays
        $labels = ['dokuwiki' => [], 'plugin' => [], 'template' => []];
        foreach($this->configuration->getSettings() as $setting) {
            if(is_a($setting, SettingFieldset::class)) {
                $labels[$setting->getType()][] = $setting;
            }
        }

        // top header
        $title = $this->getLang('_configuration_manager');
        $toc[] = html_mktocitem(sectionID($title, $check), $title, 1);

        // main entries
        foreach(['dokuwiki', 'plugin', 'template'] as $section) {
            if(empty($labels[$section])) continue; // no entries, skip

            // create main header
            $toc[] = html_mktocitem(
                $section . '_settings',
                $this->getLang('_header_' . $section),
                1
            );

            // create sub headers
            foreach($labels[$section] as $setting) {
                /** @var SettingFieldset $setting */
                $name = $setting->prompt($this);
                $toc[] = html_mktocitem($setting->getKey(), $name, 2);
            }
        }

        // undefined settings if allowed
        if(count($this->configuration->getUndefined()) && $allow_debug) {
            $toc[] = html_mktocitem('undefined_settings', $this->getLang('_header_undefined'), 1);
        }

        return $toc;
    }

    /**
     * @param string $id
     * @param string $text
     */
    protected function printH1($id, $text) {
        echo '<h1 id="' . $id . '">' . $text . '</h1>';
    }

    /**
     * Adds a translation to this plugin's language array
     *
     * Used by some settings to set up dynamic translations
     *
     * @param string $key
     * @param string $value
     */
    public function addLang($key, $value) {
        if(!$this->localised) $this->setupLocale();
        $this->lang[$key] = $value;
    }
}
