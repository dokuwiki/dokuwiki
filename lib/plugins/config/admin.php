<?php
/**
 * Configuration Manager admin plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 * @author     Ben Coburn <btcoburn@silicodon.net>
 */

use dokuwiki\plugin\config\core\Configuration;
use dokuwiki\plugin\config\core\Setting;
use dokuwiki\plugin\config\core\SettingFieldset;
use dokuwiki\plugin\config\core\SettingHidden;

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_config extends DokuWiki_Admin_Plugin {

    const METADATA = __DIR__ . 'settings/config.metadata.php';
    const IMGDIR = DOKU_BASE . 'lib/plugins/config/images/';

    protected $_localised_prompts = false;

    /** @var Configuration */
    protected $configuration;

    /**
     * admin_plugin_config constructor.
     */
    public function __construct() {
        $this->configuration = new Configuration();
    }

    /**
     * handle user request
     */
    public function handle() {
        global $ID, $INPUT;

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
                msg($this->getLang('updated'), -1);
            } catch(Exception $e) {
                msg($this->getLang('error'), -1);
            }
            send_redirect(wl($ID, array('do' => 'admin', 'page' => 'config'), true, '&'));
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

        print $this->locale_xhtml('intro');

        ptln('<div id="config__manager">');

        if($this->configuration->isLocked()) {
            ptln('<div class="info">' . $this->getLang('locked') . '</div>');
        }

        // POST to script() instead of wl($ID) so config manager still works if
        // rewrite config is broken. Add $ID as hidden field to remember
        // current ID in most cases.
        ptln('<form action="' . script() . '" method="post">');
        ptln('<div class="no"><input type="hidden" name="id" value="' . $ID . '" /></div>');
        formSecurityToken();
        $this->_print_h1('dokuwiki_settings', $this->getLang('_header_dokuwiki'));

        $in_fieldset = false;
        $first_plugin_fieldset = true;
        $first_template_fieldset = true;
        foreach($this->configuration->getSettings() as $setting) {
            if(is_a($setting, SettingHidden::class)) {
                continue;
            } else if(is_a($setting, settingFieldset::class)) {
                // config setting group
                if($in_fieldset) {
                    ptln('  </table>');
                    ptln('  </div>');
                    ptln('  </fieldset>');
                } else {
                    $in_fieldset = true;
                }
                // fixme this should probably be a function in setting:
                if($first_plugin_fieldset && substr($setting->getKey(), 0, 10) == 'plugin' . Configuration::KEYMARKER) {
                    $this->_print_h1('plugin_settings', $this->getLang('_header_plugin'));
                    $first_plugin_fieldset = false;
                } else if($first_template_fieldset && substr($setting->getKey(), 0, 7) == 'tpl' . Configuration::KEYMARKER) {
                    $this->_print_h1('template_settings', $this->getLang('_header_template'));
                    $first_template_fieldset = false;
                }
                ptln('  <fieldset id="' . $setting->getKey() . '">');
                ptln('  <legend>' . $setting->prompt($this) . '</legend>');
                ptln('  <div class="table">');
                ptln('  <table class="inline">');
            } else {
                // config settings
                list($label, $input) = $setting->html($this, $this->_error);

                $class = $setting->is_default()
                    ? ' class="default"'
                    : ($setting->is_protected() ? ' class="protected"' : '');
                $error = $setting->error()
                    ? ' class="value error"'
                    : ' class="value"';
                $icon = $setting->caution()
                    ? '<img src="' . self::IMGDIR . $setting->caution() . '.png" ' .
                    'alt="' . $setting->caution() . '" title="' . $this->getLang($setting->caution()) . '" />'
                    : '';

                ptln('    <tr' . $class . '>');
                ptln('      <td class="label">');
                ptln('        <span class="outkey">' . $setting->_out_key(true, true) . '</span>');
                ptln('        ' . $icon . $label);
                ptln('      </td>');
                ptln('      <td' . $error . '>' . $input . '</td>');
                ptln('    </tr>');
            }
        }

        ptln('  </table>');
        ptln('  </div>');
        if($in_fieldset) {
            ptln('  </fieldset>');
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
            function _setting_natural_comparison($a, $b) {
                return strnatcmp($a->getKey(), $b->getKey());
            }

            usort($undefined_settings, '_setting_natural_comparison');
            $this->_print_h1('undefined_settings', $this->getLang('_header_undefined'));
            ptln('<fieldset>');
            ptln('<div class="table">');
            ptln('<table class="inline">');
            $undefined_setting_match = array();
            foreach($undefined_settings as $setting) {
                if(
                preg_match(
                    '/^(?:plugin|tpl)' . Configuration::KEYMARKER . '.*?' . Configuration::KEYMARKER . '(.*)$/',
                    $setting->getKey(),
                    $undefined_setting_match
                )
                ) {
                    $undefined_setting_key = $undefined_setting_match[1];
                } else {
                    $undefined_setting_key = $setting->getKey();
                }
                ptln('  <tr>');
                ptln(
                    '    <td class="label"><span title="$meta[\'' . $undefined_setting_key . '\']">$' .
                    'conf' . '[\'' . $setting->_out_key() . '\']</span></td>'
                );
                ptln('    <td>' . $this->getLang('_msg_' . get_class($setting)) . '</td>');
                ptln('  </tr>');
            }
            ptln('</table>');
            ptln('</div>');
            ptln('</fieldset>');
        }

        // finish up form
        ptln('<p>');
        ptln('  <input type="hidden" name="do"     value="admin" />');
        ptln('  <input type="hidden" name="page"   value="config" />');

        if(!$this->configuration->isLocked()) {
            ptln('  <input type="hidden" name="save"   value="1" />');
            ptln('  <button type="submit" name="submit" accesskey="s">' . $lang['btn_save'] . '</button>');
            ptln('  <button type="reset">' . $lang['btn_reset'] . '</button>');
        }

        ptln('</p>');

        ptln('</form>');
        ptln('</div>');
    }

    /**
     * @param bool $prompts
     */
    public function setupLocale($prompts = false) {
        parent::setupLocale();
        if(!$prompts || $this->_localised_prompts) return;
        $this->configuration->getLangs();
        $this->_localised_prompts = true;
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

        // gather toc data
        $toc = array('conf' => array(), 'plugin' => array(), 'template' => null);
        foreach($this->configuration->getSettings() as $setting) {
            if(is_a($setting, 'setting_fieldset')) {
                // FIXME as above this should go into Setting class
                if(substr($setting->getKey(), 0, 10) == 'plugin' . Configuration::KEYMARKER) {
                    $toc['plugin'][] = $setting;
                } else if(substr($setting->getKey(), 0, 7) == 'tpl' . Configuration::KEYMARKER) {
                    $toc['template'] = $setting;
                } else {
                    $toc['conf'][] = $setting;
                }
            }
        }

        // build toc
        $t = array();

        $check = false;
        $title = $this->getLang('_configuration_manager');
        $t[] = html_mktocitem(sectionID($title, $check), $title, 1);
        $t[] = html_mktocitem('dokuwiki_settings', $this->getLang('_header_dokuwiki'), 1);
        /** @var setting $setting */
        foreach($toc['conf'] as $setting) {
            $name = $setting->prompt($this);
            $t[] = html_mktocitem($setting->getKey(), $name, 2);
        }
        if(!empty($toc['plugin'])) {
            $t[] = html_mktocitem('plugin_settings', $this->getLang('_header_plugin'), 1);
        }
        foreach($toc['plugin'] as $setting) {
            $name = $setting->prompt($this);
            $t[] = html_mktocitem($setting->getKey(), $name, 2);
        }
        if(isset($toc['template'])) {
            $t[] = html_mktocitem('template_settings', $this->getLang('_header_template'), 1);
            $setting = $toc['template'];
            $name = $setting->prompt($this);
            $t[] = html_mktocitem($setting->getKey(), $name, 2);
        }
        if(count($this->configuration->getUndefined()) && $allow_debug) {
            $t[] = html_mktocitem('undefined_settings', $this->getLang('_header_undefined'), 1);
        }

        return $t;
    }

    /**
     * @param string $id
     * @param string $text
     */
    protected function _print_h1($id, $text) {
        ptln('<h1 id="' . $id . '">' . $text . '</h1>');
    }

    /**
     * Adds a translation to this plugin's language array
     *
     * @param string $key
     * @param string $value
     */
    public function addLang($key, $value) {
        if(!$this->localised) $this->setupLocale();
        $this->lang[$key] = $value;
    }
}
