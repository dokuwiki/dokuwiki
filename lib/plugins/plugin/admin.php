<?php
/**
 * Plugin management functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

// todo
// - maintain a history of file modified
// - allow a plugin to contain extras to be copied to the current template (extra/tpl/)
// - to images (lib/images/) [ not needed, should go in lib/plugin/images/ ]

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'admin.php');

    //--------------------------[ GLOBALS ]------------------------------------------------
    // note: probably should be dokuwiki wide globals, where they can be accessed by pluginutils.php
    // global $plugin_types;
    // $plugin_types = array('syntax', 'admin');

    // plugins that are an integral part of dokuwiki, they shouldn't be disabled or deleted
    global $plugin_protected;
    $plugin_protected = array('acl','plugin','config','info','usermanager','revert');

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_plugin extends DokuWiki_Admin_Plugin {

    var $disabled = 0;
    var $plugin = '';
    var $cmd = '';
    var $handler = NULL;

    var $functions = array('delete','update',/*'settings',*/'info');  // require a plugin name
    var $commands = array('manage','download','enable');              // don't require a plugin name
    var $plugin_list = array();

    var $msg = '';
    var $error = '';

    function admin_plugin_plugin() {
      global $conf;
      $this->disabled = (isset($conf['pluginmanager']) && ($conf['pluginmanager'] == 0));
    }

    /**
     * return some info
     */
    function getInfo(){
      $disabled = ($this->disabled) ? '(disabled)' : '';

      return array(
        'author' => 'Christopher Smith',
        'email'  => 'chris@jalakai.co.uk',
        'date'   => '2005-08-10',
        'name'   => 'Plugin Manager',
        'desc'   => "Manage Plugins, including automated plugin installer $disabled",
        'url'    => 'http://wiki.splitbrain.org/plugin:plugin',
      );
    }

    /**
     * return prompt for admin menu
     */
    function getMenuText($language) {

        if (!$this->disabled)
          return parent::getMenuText($language);

        return '';
    }

    /**
     * return sort order for position in admin menu
     */
    function getMenuSort() {
      return 20;
    }

    /**
     * handle user request
     */
    function handle() {

      if ($this->disabled) return;

      // enable direct access to language strings
      $this->setupLocale();

//      $this->plugin = $_REQUEST['plugin'];
//      $this->cmd = $_REQUEST['fn'];
//      if (is_array($this->cmd)) $this->cmd = key($this->cmd);

      $fn = $_REQUEST['fn'];
      if (is_array($fn)) {
          $this->cmd = key($fn);
          $this->plugin = is_array($fn[$this->cmd]) ? key($fn[$this->cmd]) : null;
      } else {
          $this->cmd = $fn;
          $this->plugin = null;
      }

      $this->plugin_list = plugin_list('', true);
      sort($this->plugin_list);

      // verify $_REQUEST vars
      if (in_array($this->cmd, $this->commands)) {
        $this->plugin = '';
      } else if (!in_array($this->cmd, $this->functions) || !in_array($this->plugin, $this->plugin_list)) {
        $this->cmd = 'manage';
        $this->plugin = '';
      }

      if(($this->cmd != 'manage' || $this->plugin != '') && !checkSecurityToken()){
        $this->cmd = 'manage';
        $this->plugin = '';
      }

      // create object to handle the command
      $class = "ap_".$this->cmd;
      if (!class_exists($class)) $class = 'ap_manage';

      $this->handler = & new $class($this, $this->plugin);
      $this->msg = $this->handler->process();
    }

    /**
     * output appropriate html
     */
    function html() {

      if ($this->disabled) return;

      // enable direct access to language strings
      $this->setupLocale();

      if ($this->handler === NULL) $this->handler = & new ap_manage($this, $this->plugin);
      if (!$this->plugin_list) {
        $this->plugin_list = plugin_list('',true);
        sort($this->plugin_list);
      }

      ptln('<div id="plugin__manager">');
      $this->handler->html();
      ptln('</div><!-- #plugin_manager -->');
    }

}

class ap_manage {

        var $manager = NULL;
        var $lang = array();
        var $plugin = '';
        var $downloaded = array();

        function ap_manage(&$manager, $plugin) {
            $this->manager = & $manager;
            $this->plugin = $plugin;
            $this->lang = & $manager->lang;
        }

        function process() {
            return '';
        }

        function html() {
          print $this->manager->locale_xhtml('admin_plugin');
          $this->html_menu();
        }

        // build our standard menu
        function html_menu($listPlugins = true) {
          global $ID;

          ptln('<div class="pm_menu">');

          ptln('<div class="common">');
          ptln('  <h2>'.$this->lang['download'].'</h2>');
          ptln('  <form action="'.wl($ID).'" method="post">');
          ptln('    <fieldset class="hidden">',4);
          ptln('      <input type="hidden" name="do"   value="admin" />');
          ptln('      <input type="hidden" name="page" value="plugin" />');
          formSecurityToken();
          ptln('    </fieldset>');
          ptln('    <fieldset>');
          ptln('      <legend>'.$this->lang['download'].'</legend>');
          ptln('      <label for="dw__url">'.$this->lang['url'].'<input name="url" id="dw__url" class="edit" type="text" maxlength="200" /></label>');
          ptln('      <input type="submit" class="button" name="fn[download]" value="'.$this->lang['btn_download'].'" />');
          ptln('    </fieldset>');
          ptln('  </form>');
          ptln('</div>');

          if ($listPlugins) {
            ptln('<h2>'.$this->lang['manage'].'</h2>');

            ptln('<form action="'.wl($ID).'" method="post" class="plugins">');
//            ptln('  <div class="plugins">');

            ptln('  <fieldset class="hidden">');
            ptln('    <input type="hidden" name="do"     value="admin" />');
            ptln('    <input type="hidden" name="page"   value="plugin" />');
            formSecurityToken();
            ptln('  </fieldset>');

            $this->html_pluginlist();

            ptln('  <fieldset class="buttons">');
            ptln('    <input type="submit" class="button" name="fn[enable]" value="'.$this->lang['btn_enable'].'" />');
            ptln('  </fieldset>');

//            ptln('  </div>');
            ptln('</form>');
          }

          ptln('</div>');
        }

        function html_pluginlist() {
          global $ID;
          global $plugin_protected;

          foreach ($this->manager->plugin_list as $plugin) {

            $disabled = plugin_isdisabled($plugin);
            $protected = in_array($plugin,$plugin_protected);

            $checked = ($disabled) ? '' : ' checked="checked"';
            $check_disabled = ($protected) ? ' disabled="disabled"' : '';

            // determine display class(es)
            $class = array();
            if (in_array($plugin, $this->downloaded)) $class[] = 'new';
            if ($disabled) $class[] = 'disabled';
            if ($protected) $class[] = 'protected';

            $class = count($class) ? ' class="'.join(' ', $class).'"' : '';

            ptln('    <fieldset'.$class.'>');
            ptln('      <legend>'.$plugin.'</legend>');
            ptln('      <input type="checkbox" class="enable" name="enabled[]" value="'.$plugin.'"'.$checked.$check_disabled.' />');
            ptln('      <h3 class="legend">'.$plugin.'</h3>');

            $this->html_button($plugin, 'info', false, 6);
            if (in_array('settings', $this->manager->functions)) {
              $this->html_button($plugin, 'settings', !@file_exists(DOKU_PLUGIN.$plugin.'/settings.php'), 6);
            }
            $this->html_button($plugin, 'update', !$this->plugin_readlog($plugin, 'url'), 6);
            $this->html_button($plugin, 'delete', $protected, 6);

            ptln('    </fieldset>');
            }
        }

        function html_button($plugin, $btn, $disabled=false, $indent=0) {
            $disabled = ($disabled) ? 'disabled="disabled"' : '';
            ptln('<input type="submit" class="button" '.$disabled.' name="fn['.$btn.']['.$plugin.']" value="'.$this->lang['btn_'.$btn].'" />',$indent);
        }

        /**
         *  Refresh plugin list
         */
        function refresh() {

            $this->manager->plugin_list = plugin_list('',true);
            sort($this->manager->plugin_list);

            // expire dokuwiki caches
            // touching local.php expires wiki page, JS and CSS caches
            @touch(DOKU_CONF.'local.php');

            // update latest plugin date - FIXME
            return (!$this->manager->error);
        }

        function download($url, $overwrite=false) {
          global $lang;

          // check the url
          $matches = array();
          if (!preg_match("/[^\/]*$/", $url, $matches) || !$matches[0]) {
            $this->manager->error = $this->lang['error_badurl']."\n";
            return false;
          }

          $file = $matches[0];

          if (!($tmp = io_mktmpdir())) {
            $this->manager->error = $this->lang['error_dircreate']."\n";
            return false;
          }

          if (!$file = io_download($url, "$tmp/", true, $file)) {
            $this->manager->error = sprintf($this->lang['error_download'],$url)."\n";
          }

          if (!$this->manager->error && !ap_decompress("$tmp/$file", $tmp)) {
            $this->manager->error = sprintf($this->lang['error_decompress'],$file)."\n";
          }

          // search $tmp for the folder(s) that has been created
          // move the folder(s) to lib/plugins/
          if (!$this->manager->error) {
            if ($dh = @opendir("$tmp/")) {
              while (false !== ($f = readdir($dh))) {
                if ($f == '.' || $f == '..' || $f == 'tmp') continue;
                if (!is_dir("$tmp/$f")) continue;

                // check to make sure we aren't overwriting anything
                if (!$overwrite && @file_exists(DOKU_PLUGIN.$f)) {
                   // remember our settings, ask the user to confirm overwrite, FIXME
                   continue;
                }

                $instruction = @file_exists(DOKU_PLUGIN.$f) ? 'update' : 'install';

                if (ap_copy("$tmp/$f", DOKU_PLUGIN.$f)) {
                  $this->downloaded[] = $f;
                  $this->plugin_writelog($f, $instruction, array($url));
                } else {
                  $this->manager->error .= sprintf($this->lang['error_copy']."\n", $f);
                }
              }
              closedir($dh);
            } else {
              $this->manager->error = $this->lang['error']."\n";
            }
          }

          // cleanup
          if ($tmp) ap_delete($tmp);

          if (!$this->manager->error) {
              $this->refresh();
              return true;
          }

          return false;
        }

        // log
        function plugin_writelog($plugin, $cmd, $data) {

            $file = DOKU_PLUGIN.$plugin.'/manager.dat';

            switch ($cmd) {
              case 'install' :
                $url = $data[0];
                $date = date('r');
                if (!$fp = @fopen($file, 'w')) return;
                fwrite($fp, "installed=$date\nurl=$url\n");
                fclose($fp);
                break;

              case 'update' :
                $date = date('r');
                if (!$fp = @fopen($file, 'a')) return;
                fwrite($fp, "updated=$date\n");
                fclose($fp);
                break;
            }
        }

        function plugin_readlog($plugin, $field) {
            static $log = array();
            $file = DOKU_PLUGIN.$plugin.'/manager.dat';

            if (!isset($log[$plugin])) {
                $tmp = @file_get_contents($file);
                if (!$tmp) return '';
                $log[$plugin] = & $tmp;
            }

            if ($field == 'ALL') {
                return $log[$plugin];
            }

                        $match = array();
            if (preg_match_all('/'.$field.'=(.*)$/m',$log[$plugin], $match))
                return implode("\n", $match[1]);

            return '';
        }
    }

    class ap_download extends ap_manage {

        var $overwrite = false;

        function process() {
          global $lang;

          $plugin_url = $_REQUEST['url'];
          $this->download($plugin_url, $this->overwrite);
          return '';
        }

        function html() {
            parent::html();

            ptln('<div class="pm_info">');
            ptln('<h2>'.$this->lang['downloading'].'</h2>');

            if ($this->manager->error) {
                ptln('<div class="error">'.str_replace("\n","<br />",$this->manager->error).'</div>');
            } else if (count($this->downloaded) == 1) {
                ptln('<p>'.sprintf($this->lang['downloaded'],$this->downloaded[0]).'</p>');
            } else if (count($this->downloaded)) {   // more than one plugin in the download
                ptln('<p>'.$this->lang['downloads'].'</p>');
                ptln('<ul>');
                foreach ($this->downloaded as $plugin) {
                    ptln('<li><div class="li">'.$plugin.'</div></li>',2);
                }
                ptln('</ul>');
            } else {        // none found in download
                ptln('<p>'.$this->lang['download_none'].'</p>');
            }
            ptln('</div>');
        }

    }

    class ap_delete extends ap_manage {

        function process() {

            if (!ap_delete(DOKU_PLUGIN.$this->manager->plugin)) {
              $this->manager->error = sprintf($this->lang['error_delete'],$this->manager->plugin);
            } else {
              $this->refresh();
            }
        }

        function html() {
            parent::html();

            ptln('<div class="pm_info">');
            ptln('<h2>'.$this->lang['deleting'].'</h2>');

            if ($this->manager->error) {
              ptln('<div class="error">'.str_replace("\n","<br />",$this->manager->error).'</div>');
            } else {
              ptln('<p>'.sprintf($this->lang['deleted'],$this->plugin).'</p>');
            }
            ptln('</div>');
        }
    }

    class ap_info extends ap_manage {

        var $plugin_info = array();        // the plugin itself
        var $details = array();            // any component plugins

        function process() {

          // sanity check
          if (!$this->manager->plugin) { return; }

          $component_list = ap_plugin_components($this->manager->plugin);
          usort($component_list, 'ap_component_sort');

          foreach ($component_list as $component) {
              if ($obj = & plugin_load($component['type'],$component['name']) === NULL) continue;

            $this->details[] = array_merge($obj->getInfo(), array('type' => $component['type']));
            unset($obj);
          }

          // review details to simplify things
          foreach($this->details as $info) {
            foreach($info as $item => $value) {
              if (!isset($this->plugin_info[$item])) { $this->plugin_info[$item] = $value; continue; }
              if ($this->plugin_info[$item] != $value) $this->plugin_info[$item] = '';
            }
          }
        }

        function html() {

          // output the standard menu stuff
          parent::html();

          // sanity check
          if (!$this->manager->plugin) { return; }

          ptln('<div class="pm_info">');
          ptln("<h2>".$this->manager->getLang('plugin')." {$this->manager->plugin}</h2>");

          // collect pertinent information from the log
          $installed = $this->plugin_readlog($this->manager->plugin, 'installed');
          $source = $this->plugin_readlog($this->manager->plugin, 'url');
          $updated = $this->plugin_readlog($this->manager->plugin, 'updated');
          if (strrpos($updated, "\n") !== false) $updated = substr($updated, strrpos($updated, "\n")+1);

          ptln("<dl>",2);
          ptln("<dt>".$this->manager->getLang('source').'</dt><dd>'.($source ? $source : $this->manager->getLang('unknown'))."</dd>",4);
          ptln("<dt>".$this->manager->getLang('installed').'</dt><dd>'.($installed ? $installed : $this->manager->getLang('unknown'))."</dd>",4);
          if ($updated) ptln("<dt>".$this->manager->getLang('lastupdate').'</dt><dd>'.$updated."</dd>",4);
          ptln("</dl>",2);

          if (count($this->details) == 0) {
              ptln("<p>".$this->manager->getLang('noinfo')."</p>",2);
          } else {

            ptln("<dl>",2);
            if ($this->plugin_info['name']) ptln("<dt>".$this->manager->getLang('name')."</dt><dd>".$this->out($this->plugin_info['name'])."</dd>",4);
            if ($this->plugin_info['date']) ptln("<dt>".$this->manager->getLang('date')."</dt><dd>".$this->out($this->plugin_info['date'])."</dd>",4);
            if ($this->plugin_info['type']) ptln("<dt>".$this->manager->getLang('type')."</dt><dd>".$this->out($this->plugin_info['type'])."</dd>",4);
            if ($this->plugin_info['desc']) ptln("<dt>".$this->manager->getLang('desc')."</dt><dd>".$this->out($this->plugin_info['desc'])."</dd>",4);
            if ($this->plugin_info['author']) ptln("<dt>".$this->manager->getLang('author')."</dt><dd>".$this->manager->email($this->plugin_info['email'], $this->plugin_info['author'])."</dd>",4);
            if ($this->plugin_info['url']) ptln("<dt>".$this->manager->getLang('www')."</dt><dd>".$this->manager->external_link($this->plugin_info['url'], '', 'urlextern')."</dd>",4);
            ptln("</dl>",2);

            if (count($this->details) > 1) {
              ptln("<h3>".$this->manager->getLang('components')."</h3>",2);
              ptln("<div>",2);

              foreach ($this->details as $info) {

                ptln("<dl>",4);
                ptln("<dt>".$this->manager->getLang('name')."</dt><dd>".$this->out($info['name'])."</dd>",6);
                if (!$this->plugin_info['date']) ptln("<dt>".$this->manager->getLang('date')."</dt><dd>".$this->out($info['date'])."</dd>",6);
                if (!$this->plugin_info['type']) ptln("<dt>".$this->manager->getLang('type')."</dt><dd>".$this->out($info['type'])."</dd>",6);
                if (!$this->plugin_info['desc']) ptln("<dt>".$this->manager->getLang('desc')."</dt><dd>".$this->out($info['desc'])."</dd>",6);
                if (!$this->plugin_info['author']) ptln("<dt>".$this->manager->getLang('author')."</dt><dd>".$this->manager->email($info['email'], $info['author'])."</dd>",6);
                if (!$this->plugin_info['url']) ptln("<dt>".$this->manager->getLang('www')."</dt><dd>".$this->manager->external_link($info['url'], '', 'urlextern')."</dd>",6);
                ptln("</dl>",4);

              }
              ptln("</div>",2);
            }
          }
          ptln("</div>");
        }

        // simple output filter, make html entities safe and convert new lines to <br />
        function out($text) {
            return str_replace("\n",'<br />',htmlspecialchars($text));
        }

    }

    //--------------[ to do ]---------------------------------------
    class ap_update extends ap_manage {

        var $overwrite = true;

        function process() {
          global $lang;

          $plugin_url = $this->plugin_readlog($this->plugin, 'url');
          $this->download($plugin_url, $this->overwrite);
          return '';
        }

        function html() {
            parent::html();

            ptln('<div class="pm_info">');
            ptln('<h2>'.$this->lang['updating'].'</h2>');

            if ($this->manager->error) {
                ptln('<div class="error">'.str_replace("\n","<br />", $this->manager->error).'</div>');
            } else if (count($this->downloaded) == 1) {
                ptln('<p>'.sprintf($this->lang['updated'],$this->downloaded[0]).'</p>');
            } else if (count($this->downloaded)) {   // more than one plugin in the download
                ptln('<p>'.$this->lang['updates'].'</p>');
                ptln('<ul>');
                foreach ($this->downloaded as $plugin) {
                    ptln('<li><div class="li">'.$plugin.'</div></li>',2);
                }
                ptln('</ul>');
            } else {        // none found in download
                ptln('<p>'.$this->lang['update_none'].'</p>');
            }
            ptln('</div>');
        }
    }
    class ap_settings extends ap_manage {}

    class ap_enable extends ap_manage {

      var $enabled = array();

      function process() {
        global $plugin_protected;

        $this->enabled = isset($_REQUEST['enabled']) ? $_REQUEST['enabled'] : array();

        foreach ($this->manager->plugin_list as $plugin) {
          if (in_array($plugin, $plugin_protected)) continue;

          $new = in_array($plugin, $this->enabled);
          $old = !plugin_isdisabled($plugin);

          if ($new != $old) {
            switch ($new) {
              // enable plugin
              case true : plugin_enable($plugin); break;
              case false: plugin_disable($plugin); break;
            }
          }
        }

        // refresh plugins, including expiring any dokuwiki cache(s)
        $this->refresh();
      }

    }

    //--------------[ utilities ]-----------------------------------

    function is_css($f) { return (substr($f, -4) == '.css'); }

    // generate an admin plugin href
    function apl($pl, $fn) {
      global $ID;
      return wl($ID,"do=admin&amp;page=plugin".($pl?"&amp;plugin=$pl":"").($fn?"&amp;fn=$fn":""));
    }

    // decompress wrapper
    function ap_decompress($file, $target) {

        // decompression library doesn't like target folders ending in "/"
        if (substr($target, -1) == "/") $target = substr($target, 0, -1);
        $ext = substr($file, strrpos($file,'.')+1);

        // .tar, .tar.bz, .tar.gz, .tgz
        if (in_array($ext, array('tar','bz','bz2','gz','tgz'))) {

          require_once(DOKU_INC."inc/TarLib.class.php");

          if (strpos($ext, 'bz') !== false) $compress_type = COMPRESS_BZIP;
          else if (strpos($ext,'gz') !== false) $compress_type = COMPRESS_GZIP;
          else $compress_type = COMPRESS_NONE;

          $tar = new TarLib($file, $compress_type);
          $ok = $tar->Extract(FULL_ARCHIVE, $target, '', 0777);

          // FIXME sort something out for handling tar error messages meaningfully
          return ($ok<0?false:true);

        } else if ($ext == 'zip') {

          require_once(DOKU_INC."inc/ZipLib.class.php");

          $zip = new ZipLib();
          $ok = $zip->Extract($file, $target);

          // FIXME sort something out for handling zip error messages meaningfully
          return ($ok==-1?false:true);

        }  else if ($ext == "rar") {
          // not yet supported -- fix me
          return false;
        }

        // unsupported file type
        return false;
    }

    // copy with recursive sub-directory support
    function ap_copy($src, $dst) {
        global $conf;

        if (is_dir($src)) {
          if (!$dh = @opendir($src)) return false;

          if ($ok = io_mkdir_p($dst)) {
            while ($ok && (false !== ($f = readdir($dh)))) {
              if ($f == '..' || $f == '.') continue;
              $ok = ap_copy("$src/$f", "$dst/$f");
            }
          }

          closedir($dh);
          return $ok;

        } else {
            $exists = @file_exists($dst);

            if (!@copy($src,$dst)) return false;
            if (!$exists && !empty($conf['fperm'])) chmod($dst, $conf['fperm']);
            @touch($dst,filemtime($src));
        }

        return true;
    }

    // delete, with recursive sub-directory support
    function ap_delete($path) {

        if (!is_string($path) || $path == "") return false;

        if (is_dir($path)) {
          if (!$dh = @opendir($path)) return false;

          while ($f = readdir($dh)) {
            if ($f == '..' || $f == '.') continue;
            ap_delete("$path/$f");
          }

          closedir($dh);
          return @rmdir($path);
        } else {
          return @unlink($path);
        }

        return false;
    }

    // return a list (name & type) of all the component plugins that make up this plugin
    // can this move to pluginutils?
    function ap_plugin_components($plugin) {

      global $plugin_types;

      $components = array();
      $path = DOKU_PLUGIN.$plugin.'/';

      foreach ($plugin_types as $type) {
        if (@file_exists($path.$type.'.php')) { $components[] = array('name'=>$plugin, 'type'=>$type); continue; }

        if ($dh = @opendir($path.$type.'/')) {
          while (false !== ($cp = readdir($dh))) {
            if ($cp == '.' || $cp == '..' || strtolower(substr($cp,-4)) != '.php') continue;
            $components[] = array('name'=>$plugin.'_'.substr($cp, 0, -4), 'type'=>$type);
          }
          closedir($dh);
        }
      }
      return $components;
    }

    function ap_component_sort($a, $b) {
        if ($a['name'] == $b['name']) return 0;
        return ($a['name'] < $b['name']) ? -1 : 1;
    }

