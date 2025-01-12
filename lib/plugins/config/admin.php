<?php

/**
 * Configuration Manager admin plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 * @author     Ben Coburn <btcoburn@silicodon.net>
 */

use dokuwiki\Extension\AdminPlugin;
use dokuwiki\plugin\config\core\Configuration;
use dokuwiki\plugin\config\core\Setting\Setting;
use dokuwiki\plugin\config\core\Setting\SettingFieldset;
use dokuwiki\plugin\config\core\Setting\SettingHidden;

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_config extends AdminPlugin
{
    protected const IMGDIR = DOKU_BASE . 'lib/plugins/config/images/';

    /** @var Configuration */
    protected $configuration;

    /** @var bool were there any errors in the submitted data? */
    protected $hasErrors = false;

    /** @var bool have the settings translations been loaded? */
    protected $promptsLocalized = false;

    /** @var int level of the headlines outputted **/
    protected const HEADLINELEVEL = 2;

    /**
     * handle user request
     */
    public function handle()
    {
        global $ID, $INPUT;

        // always initialize the configuration
        $this->configuration = new Configuration();

        if (!$INPUT->bool('save') || !checkSecurityToken()) {
            return;
        }

        // don't go any further if the configuration is locked
        if ($this->configuration->isLocked()) return;

        // update settings and redirect of successful
        $ok = $this->configuration->updateSettings($INPUT->arr('config'));
        if ($ok) { // no errors
            try {
                if ($this->configuration->hasChanged()) {
                    $this->configuration->save();
                } else {
                    $this->configuration->touch();
                }
                msg($this->getLang('updated'), 1);
            } catch (Exception $e) {
                msg($this->getLang('error'), -1);
            }
            send_redirect(wl($ID, ['do' => 'admin', 'page' => 'config'], true, '&'));
        } else {
            $this->hasErrors = true;
            msg($this->getLang('error'), -1);
        }
    }

    /**
     * output appropriate html
     */
    public function html()
    {
        $allow_debug = $GLOBALS['conf']['allowdebug']; // avoid global $conf; here.
        global $lang;
        global $ID;

        $this->setupLocale(true);

        echo $this->locale_xhtml('intro');

        echo '<article id="config__manager">';

        if ($this->configuration->isLocked()) {
            echo '<p class="info">' . $this->getLang('locked') . '</p>';
        }

        // POST to script() instead of wl($ID) so config manager still works if
        // rewrite config is broken. Add $ID as hidden field to remember
        // current ID in most cases.
        echo '<form id="dw__configform" action="' . script() . '" method="post" autocomplete="off">';
        echo '<div class="no"><input type="hidden" name="id" value="' . $ID . '"></div>';
        formSecurityToken();
        $this->printHeadline(self::HEADLINELEVEL, 'dokuwiki_settings', $this->getLang('_header_dokuwiki'));

        $in_fieldset = false;
        $first_plugin_fieldset = true;
        $first_template_fieldset = true;
        foreach ($this->configuration->getSettings() as $setting) {
            if ($setting instanceof SettingHidden) {
                continue;
            } elseif ($setting instanceof SettingFieldset) {
                // config setting group
                if ($in_fieldset) {
                    echo '</div>';
                    echo '</section>';
                } else {
                    $in_fieldset = true;
                }
                if ($first_plugin_fieldset && $setting->getType() == 'plugin') {
                    $this->printHeadline(self::HEADLINELEVEL, 'plugin_settings', $this->getLang('_header_plugin'));
                    $first_plugin_fieldset = false;
                } elseif ($first_template_fieldset && $setting->getType() == 'template') {
                    $this->printHeadline(self::HEADLINELEVEL, 'template_settings', $this->getLang('_header_template'));
                    $first_template_fieldset = false;
                }
                echo '<section id="' . $setting->getKey() . '">';
				$this->printHeadline(self::HEADLINELEVEL+1, null, $setting->prompt($this));
                echo '<div class="settings">';
            } else {
                // config settings
                [$label, $input] = $setting->html($this, $this->hasErrors);

                // build the classlist and status text:
                $classlist = [];
                $status = [];
                $status[] = $setting->isDefault() ? 'default' : 'modified';
                if ($setting->isDefault()) $classlist[] = 'default';
                if ($setting->isProtected()) {
                    $classlist[] = 'protected';
                    $status[] = 'protected';
                }
                if ($setting->hasError()) {
                    $classlist[] = 'error';
                    $status[] = 'error';
                }
                if ($setting->caution()) $classlist[] = 'caution';

                // build the HTML code:
                echo '<dl class="' . implode(' ', $classlist) . '">';
                echo '<dt class="outkey">' . $setting->getPrettyKey() . '</dt>';
                echo '<dd class="status"><span class="a11y">' . $this->getLang('a11y_status') . ' </span><ul>'
                  . implode(array_map(function (string $it): string {
                    $txt = $this->getLang('a11y_stat_' . $it);
                    $status = $this->getLang('a11y_status');
                    return "<li class=\"{$it}\" title=\"{$status} {$txt}\"><span class=\"a11y\">{$txt}</span></li>";
                  }, $status)) . '</ul></dd>';
                echo '<dd class="label">' . $label . '</dd>';
                echo '<dd class="value">' . $input . '</dd>';
                if ($setting->caution()) {
                    echo '<dd class="' . $setting->caution() . '">' . $this->getLang($setting->caution()) . '</dd>';
                }
                echo '</dl>';
            }
        }

        echo '</div>';
        echo '</section>';

        // show undefined settings list
        $undefined_settings = $this->configuration->getUndefined();
        if ($allow_debug && !empty($undefined_settings)) {
            /**
             * Callback for sorting settings
             *
             * @param Setting $a
             * @param Setting $b
             * @return int if $a is lower/equal/higher than $b
             */
            function settingNaturalComparison($a, $b)
            {
                return strnatcmp($a->getKey(), $b->getKey());
            }

            usort($undefined_settings, 'settingNaturalComparison');
            $this->printHeadline(self::HEADLINELEVEL, 'undefined_settings', $this->getLang('_header_undefined'));
            echo '<section>';
            echo '<dl>';
            foreach ($undefined_settings as $setting) {
                [$label, $input] = $setting->html($this);
                echo '<dd class="label">' . $label . '</dd>';
                echo '<dd class="value">' . $input . '</dd>';
                echo '</tr>';
            }
            echo '</dl>';
            echo '</section>';
        }

        // finish up form
        echo '<footer>';
        echo '<input type="hidden" name="do" value="admin">';
        echo '<input type="hidden" name="page" value="config">';

        if (!$this->configuration->isLocked()) {
            echo '<input type="hidden" name="save" value="1">';
            echo '<p><button type="submit" name="submit" accesskey="s">' . $lang['btn_save'] . '</button>';
            echo '<button type="reset">' . $lang['btn_reset'] . '</button></p>';
        }

        echo '</footer>';
        echo '</form>';
        echo '</article>';
    }

    /**
     * @param bool $prompts
     */
    public function setupLocale($prompts = false)
    {
        parent::setupLocale();
        if (!$prompts || $this->promptsLocalized) return;
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
    public function getTOC()
    {
        $this->setupLocale(true);

        $allow_debug = $GLOBALS['conf']['allowdebug']; // avoid global $conf; here.
        $toc = [];
        $check = false;

        // gather settings data into three sub arrays
        $labels = ['dokuwiki' => [], 'plugin' => [], 'template' => []];
        foreach ($this->configuration->getSettings() as $setting) {
            if ($setting instanceof SettingFieldset) {
                $labels[$setting->getType()][] = $setting;
            }
        }

        // top header
        $title = $this->getLang('_configuration_manager');
        $toc[] = html_mktocitem(sectionID($title, $check), $title, 1);

        // main entries
        foreach (['dokuwiki', 'plugin', 'template'] as $section) {
            if (empty($labels[$section])) continue; // no entries, skip

            // create main header
            $toc[] = html_mktocitem(
                $section . '_settings',
                $this->getLang('_header_' . $section),
                1
            );

            // create sub headers
            foreach ($labels[$section] as $setting) {
                /** @var SettingFieldset $setting */
                $name = $setting->prompt($this);
                $toc[] = html_mktocitem($setting->getKey(), $name, 2);
            }
        }

        // undefined settings if allowed
        if (count($this->configuration->getUndefined()) && $allow_debug) {
            $toc[] = html_mktocitem('undefined_settings', $this->getLang('_header_undefined'), 1);
        }

        return $toc;
    }

    /**
     * @param int $level
     * @param string $id (null for no ID)
     * @param string $text
     */
    protected function printHeadline($level, $id, $text)
    {
        echo '<h' . $level . ($id !== null ? " id=\"{$id}\"" : '' ) . '>' . $text . '</h' . $level . '>';
    }

    /**
     * Adds a translation to this plugin's language array
     *
     * Used by some settings to set up dynamic translations
     *
     * @param string $key
     * @param string $value
     */
    public function addLang($key, $value)
    {
        if (!$this->localised) $this->setupLocale();
        $this->lang[$key] = $value;
    }
}
