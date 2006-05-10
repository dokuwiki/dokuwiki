<?php
/**
 * Configuration Manager admin plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
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
        'date'   => '2006-01-24',
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
      global $lang;
      global $ID;

      if (is_null($this->_config)) { $this->_config = new configuration($this->_file); }
      $this->setupLocale(true);

      $this->_print_config_toc();
      print $this->locale_xhtml('intro');

      ptln('<div id="config__manager">');

      if ($this->_config->locked)
        ptln('<div class="info">'.$this->getLang('locked').'</div>');
      elseif ($this->_error)
        ptln('<div class="error">'.$this->getLang('error').'</div>');
      elseif ($this->_changed)
        ptln('<div class="success">'.$this->getLang('updated').'</div>');

      ptln('<form action="'.wl($ID).'" method="post">');
      $this->_print_h1('dokuwiki_settings', $this->getLang('_header_dokuwiki'));

      $in_fieldset = false;
      $first_plugin_fieldset = true;
      $first_template_fieldset = true;
      foreach($this->_config->setting as $setting) {
        if (is_a($setting, 'setting_fieldset')) {
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
          ptln('  <fieldset name="'.$setting->_key.'" id="'.$setting->_key.'">');
          ptln('  <legend>'.$setting->prompt($this).'</legend>');
          ptln('  <table class="inline">');
        } else {
          // config settings
          list($label,$input) = $setting->html($this, $this->_error);

          $class = $setting->is_default() ? ' class="default"' : ($setting->is_protected() ? ' class="protected"' : '');
          $error = $setting->error() ? ' class="value error"' : ' class="value"';

          ptln('    <tr'.$class.'>');
          ptln('      <td><a class="nolink" title="$'.$this->_config->_name.'[\''.$setting->_out_key().'\']">'.$label.'</a></td>');
          ptln('      <td'.$error.'>'.$input.'</td>');
          ptln('    </tr>');
        }
      }

      ptln('  </table>');
      if ($in_fieldset) {
        ptln('  </fieldset>');
      }

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

      $langfile   = '/lang/'.$conf[lang].'/settings.php';
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
    * Uses inc/parser/xhtml.php#render_TOC to format the output.
    * Relies on internal data structures in the Doku_Renderer_xhtml class.
    *
    * @author Ben Coburn <btcoburn@silicodon.net>
    */
    function _print_config_toc() {
      // gather toc data
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
        }
      }

      // build toc list
      $xhtml_toc = array();
      $xhtml_toc[] = array('hid' => 'configuration_manager',
          'title' => $this->getLang('_configuration_manager'),
          'type'  => 'ul',
          'level' => 1);
      $xhtml_toc[] = array('hid' => 'dokuwiki_settings',
          'title' => $this->getLang('_header_dokuwiki'),
          'type'  => 'ul',
          'level' => 1);
      foreach($toc['conf'] as $setting) {
        $name = $setting->prompt($this);
        $xhtml_toc[] = array('hid' => $setting->_key,
            'title' => $name,
            'type'  => 'ul',
            'level' => 2);
      }
      if (!empty($toc['plugin'])) {
        $xhtml_toc[] = array('hid' => 'plugin_settings',
            'title' => $this->getLang('_header_plugin'),
            'type'  => 'ul',
            'level' => 1);
      }
      foreach($toc['plugin'] as $setting) {
        $name = $setting->prompt($this);
        $xhtml_toc[] = array('hid' => $setting->_key,
            'title' => $name,
            'type'  => 'ul',
            'level' => 2);
      }
      if (isset($toc['template'])) {
        $xhtml_toc[] = array('hid' => 'template_settings',
            'title' => $this->getLang('_header_template'),
            'type'  => 'ul',
            'level' => 1);
        $setting = $toc['template'];
        $name = $setting->prompt($this);
        $xhtml_toc[] = array('hid' => $setting->_key,
            'title' => $name,
            'type'  => 'ul',
            'level' => 2);
      }

      // use the xhtml renderer to make the toc
      require_once(DOKU_INC.'inc/parser/xhtml.php');
      $r = new Doku_Renderer_xhtml;
      $r->toc = $xhtml_toc;
      print $r->render_TOC();
    }

    function _print_h1($id, $text) {
      ptln('<h1><a name="'.$id.'" id="'.$id.'">'.$text.'</a></h1>');
    }


}
