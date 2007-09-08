<?php
/**
 * Configuration Manager admin plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 * @author     Ben Coburn <btcoburn@silicodon.net>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'admin.php');

define('CM_KEYMARKER','____');            // used for settings with multiple dimensions of array indices

define('PLUGIN_SELF',dirname(__FILE__).'/');
define('PLUGIN_METADATA',PLUGIN_SELF.'settings/config.metadata.php');

require_once(PLUGIN_SELF.'settings/config.class.php');  // main configuration class and generic settings classes
require_once(PLUGIN_SELF.'settings/extra.class.php');   // settings classes specific to these settings

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_config extends DokuWiki_Admin_Plugin {

    var $_file = PLUGIN_METADATA;
    var $_config = null;
    var $_input = null;
    var $_changed = false;          // set to true if configuration has altered
    var $_error = false;
    var $_session_started = false;
    var $_localised_prompts = false;

    /**
     * return some info
     */
    function getInfo(){

      return array(
        'author' => 'Christopher Smith',
        'email'  => 'chris@jalakai.co.uk',
        'date'   => '2007-08-05',
        'name'   => 'Configuration Manager',
        'desc'   => "Manage Dokuwiki's Configuration Settings",
        'url'    => 'http://wiki.splitbrain.org/plugin:config',
      );
    }

    function getMenuSort() { return 100; }

    /**
     * handle user request
     */
    function handle() {
      global $ID;

      if (!$this->_restore_session()) return $this->_close_session();
      if (!isset($_REQUEST['save']) || ($_REQUEST['save'] != 1)) return $this->_close_session();
      if (!checkSecurityToken()) return $this->_close_session();

      if (is_null($this->_config)) { $this->_config = new configuration($this->_file); }

      // don't go any further if the configuration is locked
      if ($this->_config->_locked) return $this->_close_session();

      $this->_input = $_REQUEST['config'];

      while (list($key) = each($this->_config->setting)) {
        $input = isset($this->_input[$key]) ? $this->_input[$key] : NULL;
        if ($this->_config->setting[$key]->update($input)) {
          $this->_changed = true;
        }
        if ($this->_config->setting[$key]->error()) $this->_error = true;
      }

      if ($this->_changed  && !$this->_error) {
        $this->_config->save_settings($this->getPluginName());

        // save state & force a page reload to get the new settings to take effect
        $_SESSION['PLUGIN_CONFIG'] = array('state' => 'updated', 'time' => time());
        $this->_close_session();
        header("Location: ".wl($ID,array('do'=>'admin','page'=>'config'),true,'&'));
        exit();
      }

      $this->_close_session();
    }

    /**
     * output appropriate html
     */
    function html() {
      $allow_debug = $GLOBALS['conf']['allowdebug']; // avoid global $conf; here.
      global $lang;
      global $ID;

      if (is_null($this->_config)) { $this->_config = new configuration($this->_file); }
      $this->setupLocale(true);

      print $this->locale_xhtml('intro');

      ptln('<div id="config__manager">');

      if ($this->_config->locked)
        ptln('<div class="info">'.$this->getLang('locked').'</div>');
      elseif ($this->_error)
        ptln('<div class="error">'.$this->getLang('error').'</div>');
      elseif ($this->_changed)
        ptln('<div class="success">'.$this->getLang('updated').'</div>');

      ptln('<form action="'.wl($ID).'" method="post">');
      formSecurityToken();
      $this->_print_h1('dokuwiki_settings', $this->getLang('_header_dokuwiki'));

      $undefined_settings = array();
      $in_fieldset = false;
      $first_plugin_fieldset = true;
      $first_template_fieldset = true;
      foreach($this->_config->setting as $setting) {
        if (is_a($setting, 'setting_hidden')) {
          // skip hidden (and undefined) settings
          if ($allow_debug && is_a($setting, 'setting_undefined')) {
            $undefined_settings[] = $setting;
          } else {
            continue;
          }
        } else if (is_a($setting, 'setting_fieldset')) {
          // config setting group
          if ($in_fieldset) {
            ptln('  </table>');
            ptln('  </fieldset>');
          } else {
            $in_fieldset = true;
          }
          if ($first_plugin_fieldset && substr($setting->_key, 0, 10)=='plugin'.CM_KEYMARKER) {
            $this->_print_h1('plugin_settings', $this->getLang('_header_plugin'));
            $first_plugin_fieldset = false;
          } else if ($first_template_fieldset && substr($setting->_key, 0, 7)=='tpl'.CM_KEYMARKER) {
            $this->_print_h1('template_settings', $this->getLang('_header_template'));
            $first_template_fieldset = false;
          }
          ptln('  <fieldset id="'.$setting->_key.'">');
          ptln('  <legend>'.$setting->prompt($this).'</legend>');
          ptln('  <table class="inline">');
        } else {
          // config settings
          list($label,$input) = $setting->html($this, $this->_error);

          $class = $setting->is_default() ? ' class="default"' : ($setting->is_protected() ? ' class="protected"' : '');
          $error = $setting->error() ? ' class="value error"' : ' class="value"';

          ptln('    <tr'.$class.'>');
          ptln('      <td class="label">');
          ptln('        <span class="outkey">'.$setting->_out_key(true).'</span>');
          ptln('        '.$label);
          ptln('      </td>');
          ptln('      <td'.$error.'>'.$input.'</td>');
          ptln('    </tr>');
        }
      }

      ptln('  </table>');
      if ($in_fieldset) {
        ptln('  </fieldset>');
      }

      // show undefined settings list
      if ($allow_debug && !empty($undefined_settings)) {
        function _setting_natural_comparison($a, $b) { return strnatcmp($a->_key, $b->_key); }
        usort($undefined_settings, '_setting_natural_comparison');
        $this->_print_h1('undefined_settings', $this->getLang('_header_undefined'));
        ptln('<fieldset>');
        ptln('<table class="inline">');
        $undefined_setting_match = array();
        foreach($undefined_settings as $setting) {
          if (preg_match('/^(?:plugin|tpl)'.CM_KEYMARKER.'.*?'.CM_KEYMARKER.'(.*)$/', $setting->_key, $undefined_setting_match)) {
            $undefined_setting_key = $undefined_setting_match[1];
          } else {
            $undefined_setting_key = $setting->_key;
          }
          ptln('  <tr>');
          ptln('    <td class="label"><span title="$meta[\''.$undefined_setting_key.'\']">$'.$this->_config->_name.'[\''.$setting->_out_key().'\']</span></td>');
          ptln('    <td>'.$this->getLang('_msg_'.get_class($setting)).'</td>');
          ptln('  </tr>');
        }
        ptln('</table>');
        ptln('</fieldset>');
      }

      // finish up form
      ptln('<p>');
      ptln('  <input type="hidden" name="do"     value="admin" />');
      ptln('  <input type="hidden" name="page"   value="config" />');

      if (!$this->_config->locked) {
        ptln('  <input type="hidden" name="save"   value="1" />');
        ptln('  <input type="submit" name="submit" class="button" value="'.$lang['btn_save'].'" accesskey="s" />');
        ptln('  <input type="reset" class="button" value="'.$lang['btn_reset'].'" />');
      }

      ptln('</p>');

      ptln('</form>');
      ptln('</div>');
    }

    /**
     * @return boolean   true - proceed with handle, false - don't proceed
     */
    function _restore_session() {

      // dokuwiki closes the session before act_dispatch. $_SESSION variables are all set,
      // however they can't be changed without starting the session again
      if (!headers_sent()) {
        session_start();
        $this->_session_started = true;
      }

      if (!isset($_SESSION['PLUGIN_CONFIG'])) return true;

      $session = $_SESSION['PLUGIN_CONFIG'];
      unset($_SESSION['PLUGIN_CONFIG']);

      // still valid?
      if (time() - $session['time'] > 120) return true;

      switch ($session['state']) {
        case 'updated' :
          $this->_changed = true;
          return false;
      }

      return true;
    }

    function _close_session() {
      if ($this->_session_started) session_write_close();
    }

    function setupLocale($prompts=false) {

      parent::setupLocale();
      if (!$prompts || $this->_localised_prompts) return;

      $this->_setup_localised_plugin_prompts();
      $this->_localised_prompts = true;

    }

    function _setup_localised_plugin_prompts() {
      global $conf;

      $langfile   = '/lang/'.$conf['lang'].'/settings.php';
      $enlangfile = '/lang/en/settings.php';

      if ($dh = opendir(DOKU_PLUGIN)) {
        while (false !== ($plugin = readdir($dh))) {
          if ($plugin == '.' || $plugin == '..' || $plugin == 'tmp' || $plugin == 'config') continue;
          if (is_file(DOKU_PLUGIN.$plugin)) continue;

          if (@file_exists(DOKU_PLUGIN.$plugin.$enlangfile)){
            $lang = array();
            @include(DOKU_PLUGIN.$plugin.$enlangfile);
            if ($conf['lang'] != 'en') @include(DOKU_PLUGIN.$plugin.$langfile);
            foreach ($lang as $key => $value){
              $this->lang['plugin'.CM_KEYMARKER.$plugin.CM_KEYMARKER.$key] = $value;
            }
          }

          // fill in the plugin name if missing (should exist for plugins with settings)
          if (!isset($this->lang['plugin'.CM_KEYMARKER.$plugin.CM_KEYMARKER.'plugin_settings_name'])) {
            $this->lang['plugin'.CM_KEYMARKER.$plugin.CM_KEYMARKER.'plugin_settings_name'] =
              ucwords(str_replace('_', ' ', $plugin)).' '.$this->getLang('_plugin_sufix');
          }
        }
        closedir($dh);
      }

      // the same for the active template
      $tpl = $conf['template'];

      if (@file_exists(DOKU_TPLINC.$enlangfile)){
        $lang = array();
        @include(DOKU_TPLINC.$enlangfile);
        if ($conf['lang'] != 'en') @include(DOKU_TPLINC.$langfile);
        foreach ($lang as $key => $value){
          $this->lang['tpl'.CM_KEYMARKER.$tpl.CM_KEYMARKER.$key] = $value;
        }
      }

      // fill in the template name if missing (should exist for templates with settings)
      if (!isset($this->lang['tpl'.CM_KEYMARKER.$tpl.CM_KEYMARKER.'template_settings_name'])) {
        $this->lang['tpl'.CM_KEYMARKER.$tpl.CM_KEYMARKER.'template_settings_name'] =
          ucwords(str_replace('_', ' ', $tpl)).' '.$this->getLang('_template_sufix');
      }

      return true;
    }

    /**
     * Generates a two-level table of contents for the config plugin.
     *
     * @author Ben Coburn <btcoburn@silicodon.net>
     */
    function getTOC() {
      if (is_null($this->_config)) { $this->_config = new configuration($this->_file); }
      $this->setupLocale(true);

      $allow_debug = $GLOBALS['conf']['allowdebug']; // avoid global $conf; here.

      // gather toc data
      $has_undefined = false;
      $toc = array('conf'=>array(), 'plugin'=>array(), 'template'=>null);
      foreach($this->_config->setting as $setting) {
        if (is_a($setting, 'setting_fieldset')) {
          if (substr($setting->_key, 0, 10)=='plugin'.CM_KEYMARKER) {
            $toc['plugin'][] = $setting;
          } else if (substr($setting->_key, 0, 7)=='tpl'.CM_KEYMARKER) {
            $toc['template'] = $setting;
          } else {
            $toc['conf'][] = $setting;
          }
        } else if (!$has_undefined && is_a($setting, 'setting_undefined')) {
          $has_undefined = true;
        }
      }

      // build toc
      $t = array();

      $t[] = html_mktocitem('configuration_manager', $this->getLang('_configuration_manager'), 1);
      $t[] = html_mktocitem('dokuwiki_settings', $this->getLang('_header_dokuwiki'), 1);
      foreach($toc['conf'] as $setting) {
        $name = $setting->prompt($this);
        $t[] = html_mktocitem($setting->_key, $name, 2);
      }
      if (!empty($toc['plugin'])) {
        $t[] = html_mktocitem('plugin_settings', $this->getLang('_header_plugin'), 1);
      }
      foreach($toc['plugin'] as $setting) {
        $name = $setting->prompt($this);
        $t[] = html_mktocitem($setting->_key, $name, 2);
      }
      if (isset($toc['template'])) {
        $t[] = html_mktocitem('template_settings', $this->getLang('_header_template'), 1);
        $setting = $toc['template'];
        $name = $setting->prompt($this);
        $t[] = html_mktocitem($setting->_key, $name, 2);
      }
      if ($has_undefined && $allow_debug) {
        $t[] = html_mktocitem('undefined_settings', $this->getLang('_header_undefined'), 1);
      }

      return $t;
    }

    function _print_h1($id, $text) {
      ptln('<h1><a name="'.$id.'" id="'.$id.'">'.$text.'</a></h1>');
    }


}
