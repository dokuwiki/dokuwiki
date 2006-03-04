<?php
/**
 * Configuration Manager admin plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
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

      print $this->locale_xhtml('intro');

      ptln('<div id="config__manager">');

      if ($this->_config->locked)
        ptln('<div class="info">'.$this->getLang('locked').'</div>');
      elseif ($this->_error)
        ptln('<div class="error">'.$this->getLang('error').'</div>');
      elseif ($this->_changed)
        ptln('<div class="success">'.$this->getLang('updated').'</div>');

      ptln('<form action="'.wl($ID).'" method="post">');
      ptln('  <table class="inline">');

      foreach($this->_config->setting as $setting) {

        list($label,$input) = $setting->html($this, $this->_error);

        $class = $setting->is_default() ? ' class="default"' : ($setting->is_protected() ? ' class="protected"' : '');
        $error = $setting->error() ? ' class="error"' : '';

        ptln('    <tr'.$class.'>');
        ptln('      <td>'.$label.'</td>');
        ptln('      <td'.$error.'>'.$input.'</td>');
        ptln('    </tr>');
      }

      ptln('  </table>');

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

            $lang = array();

      if ($dh = opendir(DOKU_PLUGIN)) {
        while (false !== ($plugin = readdir($dh))) {
          if ($plugin == '.' || $plugin == '..' || $plugin == 'tmp' || $plugin == 'config') continue;
          if (is_file(DOKU_PLUGIN.$plugin)) continue;

          if (@file_exists(DOKU_PLUGIN.$plugin.$enlangfile)){
            @include(DOKU_PLUGIN.$plugin.$enlangfile);
            if ($conf['lang'] != 'en') @include(DOKU_PLUGIN.$plugin.$langfile);
          }
        }
        closedir($dh);

        $this->lang = array_merge($lang, $this->lang);
      }

      return true;
    }

}
