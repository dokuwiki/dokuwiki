<?php
/**
 * Plugin management functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */
      
// todo
// - maintain a history of file modified
// - allow a plugin to contain extras to be copied to the current template (extra/tpl/)
// - to images (lib/images/) [ not needed, should go in lib/plugin/images/ ]
     
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'admin.php');
     
    // language stuff here for now ... move to language files when complete
    
//    global $lang;
    
    //--------------------------[ GLOBALS ]------------------------------------------------
    // note: probably should be dokuwiki wide globals, where they can be accessed by pluginutils.php
    global $common_plugin_files, $common_plugin_types;
    $common_plugin_types = array('syntax', 'admin');
    $common_plugin_files = array("style.css", "screen.css", "print.css", "script.js");

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_plugin extends DokuWiki_Admin_Plugin {

    var $disabled = 0;
    var $plugin = '';
    var $cmd = '';
    var $handler = NULL;
    
    var $functions = array('delete','update','settings','info');  // require a plugin name
    var $commands = array('manage','refresh','download');         // don't require a plugin name
    var $plugin_list = array();
    
    var $msg = '';
    var $error = '';
    
    function admin_plugin_plugin() {
      global $conf;
      $this->disabled = (!isset($conf['pluginmanager']) || ($conf['pluginmanager'] == 0));
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
        'url'    => 'http://wiki.splitbrain.org/plugin:adminplugin',
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
         global $ID, $lang;
      
      if ($this->disabled) return;
      
      $this->plugin = $_REQUEST['plugin'];
      $this->cmd = $_REQUEST['fn'];
      if (is_array($this->cmd)) $this->cmd = key($this->cmd);
      
      sort($this->plugin_list = plugin_list());
      
      // verify $_REQUEST vars
      if (in_array($this->cmd, $this->commands)) {
        $this->plugin = '';
      } else if (!in_array($this->cmd, $this->functions) || !in_array($this->plugin, $this->plugin_list)) {
        $this->cmd = 'manage';
        $this->plugin = '';
      }
      
      // create object to handle the command
      $class = "ap_".$this->cmd;
      if (!class_exists($class)) $class = 'ap_manage';
      
      $this->handler = & new $class($this, $plugin);
      $this->msg = $this->handler->process();
    }
 
    /**
     * output appropriate html
     */
    function html() {
      
      if ($this->disabled) return;

      // enable direct access to language strings
      $this->setupLocale();
      
      if ($this->handler === NULL) $this->handler = & new ap_manage();
      if (!$this->plugin_list) sort($this->plugin_list = plugin_list());
      
      ptln('<div id="plugin_manager">');
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
          
          print $this->manager->plugin_locale_xhtml('admin_plugin');
          
          // FIXME, these probably shouldn't be here any more!
          if (!$this->manager->msg) $this->manager->msg = '&nbsp;';
          ptln("<p>{$manager->msg}</p>");
          
          if ($this->manager->error) {
            ptln("<p class='error'>".str_replace("\n","<br>",$this->manager->error)."</p>");
          }
          
          $this->html_menu();          
        }
        
        // build our standard menu
        function html_menu($listPlugins = true) {
          global $ID;
        
          ptln('<div class="pm_menu">');
          
          ptln('<div class="common">');
          ptln('  <form action="'.wl($ID).'" method="post">');
          ptln('    <fieldset class="hidden">',4);
          ptln('      <input type="hidden" name="do"   value="admin" />');
          ptln('      <input type="hidden" name="page" value="plugin" />');
          ptln('    </fieldset>');
          ptln('    <fieldset>');
          ptln('      <legend>'.$this->lang['refresh'].'</legend>');
          ptln('      <h3 class="legend">'.$this->lang['refresh'].'</h3>');
          ptln('      <input type="submit" class="button" name="fn[refresh]" value="'.$this->lang['btn_refresh'].'" />');
          ptln('      <p>'.$this->lang['refresh_x'].'</p>');
          ptln('    </fieldset>');
          ptln('    <fieldset>');
          ptln('      <legend>'.$this->lang['download'].'</legend>');
          ptln('      <h3 class="legend">'.$this->lang['download'].'</h3>');
          ptln('      <input type="submit" class="button" name="fn[download]" value="'.$this->lang['btn_download'].'" />');
          ptln('      <label for="url">'.$this->lang['url'].'<input name="url" id="url" class="field" type="text" maxlength="200" /></label>');
          ptln('    </fieldset>');
          ptln('  </form>');
          ptln('</div>');
          
          if ($listPlugins) {
            ptln('<h2>'.$this->lang['manage'].'</h2>');
            ptln('<div class="plugins">');
            $this->html_pluginlist();
            ptln('</div>');
          }
                              
          ptln('</div>');
        }
            
        function html_pluginlist() {

          foreach ($this->manager->plugin_list as $plugin) {
          
            $new = (in_array($plugin, $this->downloaded)) ? ' class="new"' : '';
            
            ptln('  <form action="'.wl($ID).'" method="post" '.$new.'>');
            ptln('    <fieldset>');
            ptln('      <legend>'.$plugin.'</legend>');
            ptln('      <h3 class="legend">'.$plugin.'</h3>');
            ptln('      <input type="hidden" name="do"     value="admin" />');
            ptln('      <input type="hidden" name="page"   value="plugin" />');
            ptln('      <input type="hidden" name="plugin" value="'.$plugin.'" />');
            
            $this->html_button('delete', false, 6);
            $this->html_button('update', !$this->plugin_readlog($plugin, 'url'), 6);
            $this->html_button('settings', !@file_exists(DOKU_PLUGIN.$plugin.'/settings.php'), 6);
            $this->html_button('info', false, 6);
            
            ptln('    </fieldset>');
            ptln('  </form>');
            }
        }
        
        function html_button($btn, $disabled=false, $indent=0) {
            $disabled = ($disabled) ? 'disabled="disabled"' : '';
            ptln('<input type="submit" class="button" '.$disabled.' name="fn['.$btn.']" value="'.$this->lang['btn_'.$btn].'" />',$indent);
        }
        
        /**
         *  Rebuild aggregated files & update latest plugin date
         */
        function refresh() {
            global $lang;
            global $common_plugin_files;
            
            sort($this->manager->plugin_list = plugin_list());
            
            foreach ($common_plugin_files as $file) {
              $aggregate = '';
              
              // could replace with an class/object based aggregator, 
              // that way special files could have their own aggregator
              foreach ($this->manager->plugin_list as $plugin) {
                if (@file_exists(DOKU_PLUGIN."$plugin/$file")) {
                    $contents = @file_get_contents(DOKU_PLUGIN."$plugin/$file")."\n";
                    
                    // url conversion for css files
                    if (is_css($file)) {
                      $contents = preg_replace('/(url\([\'\"]?)([^\/](?![a-zA-Z0-9]+:\/\/).*?)([\'\"]?\))/','$1'.$plugin.'/$2$3',$contents); 
                    }
                    
                    $aggregate .= $contents;
                }
              }
              
              if (trim($aggregate)) {
                if (!io_savefile(DOKU_PLUGIN."plugin_$file", $aggregate)) {
                  $this->manager->error .= sprintf($this->lang['error_write'],$file);
                }
              }
            }
            
            // update latest plugin date - FIXME            
            return (!$this->manager->error);
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
                if (!$fp = @fopen($file, 'w+')) return;
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
            
            if (preg_match_all('/'.$field.'=(.*)$/m',$log[$plugin], $match=array()))
                return implode("\n", $match[1]);
            
            return '';
        }
    }
    
    class ap_refresh extends ap_manage {
    
        function process() {
            $this->refresh();
            
            if (!$this->manager->error) return $this->lang['refreshed'];
        }
        
        function html() {
        
            parent::html();
            
            ptln('<div class="pm_info">');
            ptln('<h2>'.$this->lang['refreshing'].'</h2>');
            ptln('<p>'.$this->lang['refreshed'].'</p>');
            ptln('</div>');
        }
        
    }
    
    class ap_download extends ap_manage {
    
        var $overwrite = false;
        
        function process() {
          global $lang, $conf;
          
          $plugin_url = $_REQUEST['url'];
          if (!preg_match("/[^\/]*$/", $plugin_url, $matches = array()) || !$matches[0]) {
            $this->manager->error = $this->lang['error_badurl'].'\n';
            return '';
          }
          
          $file = $matches[0];
          $folder = "p".md5($file.date('r'));     // tmp folder name - will be empty (should really make sure it doesn't already exist)
          $tmp = DOKU_PLUGIN."tmp/$folder";
          
          if (!$this->manager->error && !ap_mkdir($tmp)) {
            $this->manager->error = $this->lang['error_dir_create'].'\n';
            $folder = '';
          }
          
          if (!$this->manager->error && !io_download($plugin_url, "$tmp/$file")) {
            $this->manager->error = sprintf($this->lang['error_download'],$url)."\n";
          }
    
          ap_decompress("$tmp/$file", $tmp);
          
          // search tmp/$folder for the folder(s) that has been created
          // move that folder(s) to lib/plugins/
          if ($dh = @opendir("$tmp/")) {
              while (false !== ($f = readdir($dh))) {
                if ($f == '.' || $f == '..' || $f == 'tmp') continue;
                if (!is_dir("$tmp/$f")) continue;
                
                // check to make sure we aren't overwriting anything
                if (file_exists(DOKU_PLUGIN."/$f")) {
                   // remember our settings, ask the user to confirm overwrite, FIXME
                   continue;
                } 
                
                ap_copy("$tmp/$f", DOKU_PLUGIN.$f);
                $this->downloaded[] = $f;
                $this->plugin_writelog($f, 'install', array($plugin_url));
              }        
            closedir($dh);
          }
          
          // cleanup
          if ($folder && is_dir(DOKU_PLUGIN."tmp/$folder")) ap_delete(DOKU_PLUGIN."tmp/$folder");
          
          if (!$this->manager->error) {
              $this->refresh();
          }

          return '';
        }
        
        function html() {
            parent::html();
            
            ptln('<div class="pm_info">');
            ptln('<h2>'.$this->lang['downloading'].'</h2>');
            
            if ($this->manager->error) {
                ptln('<p class="error">'.$this->manager->error.'</p>');
            } else if (count($this->downloaded) == 1) {
                ptln('<p>'.sprintf($this->lang['downloaded'],$this->downloaded[0]).'</p>');
            } else if (count($this->downloaded)) {   // more than one plugin in the download
                ptln('<p>'.$this->lang['downloads'].'</p>');
                ptln('<ul>');
                foreach ($this->downloaded as $plugin) {
                    ptln('<li>'.$plugin.'</li>',2);
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
        
            $deleted = $this->manager->plugin;
            ap_delete(DOKU_PLUGIN.$deleted);
            $this->plugin = '';
            
            $this->refresh();
            return "Plugin $deleted deleted";
        }
    }
    
    class ap_info extends ap_manage {
    
        var $plugin_info = array();        // the plugin itself
        var $details = array();            // any component plugins

        function process() { 
        
          // sanity check
          if (!$this->manager->plugin) { return; }
          
          $component_list = ap_plugin_components($this->manager->plugin);
          usort($component_list, ap_component_sort);
          
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
          ptln("<h2>Plugin: {$this->manager->plugin}</h2>");

          // collect pertinent information from the log
          $installed = $this->plugin_readlog($this->manager->plugin, 'installed');
          $source = $this->plugin_readlog($this->manager->plugin, 'url');          
          $updated = substr(strrchr("\n".$this->plugin_readlog($this->manager->plugin, 'updated'), '\n'), 1);
          
          ptln("<dl>",2);
          ptln("<dt>".$this->manager->getLang('installed').'</dt><dd>'.($installed ? $installed : $this->manager->getLang('unknown'))."</dd>",4);
          if ($updated) ptln("<dt>".$this->manager->getLang('lastupdate').'</dt><dd>'.$updated."</dd>",4);
          ptln("<dt>".$this->manager->getLang('source').'</dt><dd>'.($source ? $source : $this->manager->getLang('unknown'))."</dd>",4);
          ptln("</dl>",2);
                    
          if (count($this->details) == 0) {
              ptln("<p>This plugin returned no information, it may be invalid.</p>",2);
          } else {
          
            ptln("<dl>",2);
            if ($this->plugin_info['name']) ptln("<dt>Name</dt><dd>".$this->out($this->plugin_info['name'])."</dd>",4);
            if ($this->plugin_info['type']) ptln("<dt>Type</dt><dd>".$this->out($this->plugin_info['type'])."</dd>",4);
            if ($this->plugin_info['desc']) ptln("<dt>Description</dt><dd>".$this->out($this->plugin_info['desc'])."</dd>",4);
            if ($this->plugin_info['author']) ptln("<dt>Author</dt><dd>".$this->manager->plugin_email($this->plugin_info['email'], $this->plugin_info['author'])."</dd>",4);
            if ($this->plugin_info['url']) ptln("<dt>Web</dt><dd>".$this->manager->plugin_link($this->plugin_info['url'], '', 'urlextern')."</dd>",4);
            ptln("</dl>",2);
          
            if (count($this->details) > 1) {
              ptln("<h3>Components</h3>",2);
              ptln("<div>",2);
          
              foreach ($this->details as $info) {
            
                ptln("<dl>",4);
                if (!$this->plugin_info['name']) ptln("<dt>Name</dt><dd>".$this->out($info['name'])."</dd>",6);            
                if (!$this->plugin_info['type']) ptln("<dt>Type</dt><dd>".$this->out($info['type'])."</dd>",6);
                if (!$this->plugin_info['desc']) ptln("<dt>Description</dt><dd>".$this->out($info['desc'])."</dd>",6);
                if (!$this->plugin_info['author']) ptln("<dt>Author</dt><dd>".$this->manager->plugin_email($info['email'], $info['author'])."</dd>",6);
                if (!$this->plugin_info['url']) ptln("<dt>Web</dt><dd>".$this->manager->plugin_link($info['url'], '', 'urlextern')."</dd>",6);
                ptln("</dl>",4);
          
              }
              ptln("</div>",2);
            }
          }
          ptln("</div>");
        }
        
        // simple output filter, make html entities safe and convert new lines to <br />
        function out($text) {
            return str_replace("\n",'<br />',htmlentities($text));
        }
        
    }
    
    //--------------[ to do ]---------------------------------------
    class ap_update extends ap_manage {
    
        function html() {
            parent::html();
            
            ptln('<div class="pm_info">');
            ptln('<h2>'.$this->lang['updating'].'</h2>');
            
            if ($this->manager->error) {
                ptln('<p class="error">'.$this->manager->error.'</p>');
            } else if (count($this->downloaded) == 1) {
                ptln('<p>'.sprintf($this->lang['downloaded'],$this->downloaded[0]).'</p>');
            } else if (count($this->downloaded)) {   // more than one plugin in the download
                ptln('<p>'.$this->lang['downloads'].'</p>');
                ptln('<ul>');
                foreach ($this->downloaded as $plugin) {
                    ptln('<li>'.$plugin.'</li>',2);
                }
                ptln('</ul>');
            } else {        // none found in download
                ptln('<p>'.$this->lang['download_none'].'</p>');
            }
            ptln('<p>Under Construction</p>');
            ptln('</div>');
        }
    }
    class ap_settings extends ap_manage {}
    
    //--------------[ utilities ]-----------------------------------
    
    function is_css($f) { return (substr($f, -4) == '.css'); }
    
    // generate an admin plugin href 
    function apl($pl, $fn) { return wl($ID,"do=admin&amp;page=plugin".($pl?"&amp;plugin=$pl":"").($fn?"&amp;fn=$fn":"")); }
    
    // decompress wrapper
    function ap_decompress($file, $target) {
    
        // decompression library doesn't like target folders ending in "/"
        if (substr($target, -1) == "/") $target = substr($target, 0, -1);
        
        // .tar, .tar.bz, .tar.gz
        if (preg_match("/\.tar(\.bz2?|\.gz)?$/", $file)) {
          
          require_once(DOKU_PLUGIN."plugin/inc/tarlib.class.php");
          
          $tar = new CompTar($file, COMPRESS_DETECT);
          $ok = $tar->Extract(FULL_ARCHIVE, $target, '', 0777);
        
          // sort something out for handling tar error messages meaningfully  
          if ($ok<0) ptln("<p>tar error:".$tar->TarErrorStr($ok)."</p>");
          return ($ok<0?false:true);
        }
        
        if (substr($file, -4) == ".zip") {    
    
          require_once(DOKU_PLUGIN."plugin/inc/zip.lib.php");
          
          $zip = new zip();
          $ok = $zip->Extract($file, $target);
          
          // sort something out for handling zip error messages meaningfully  
          if ($ok==-1) ptln("<p>zip error:</p>");            
          return ($ok==-1?false:true);
        }
        
        if (substr($file, -4) == ".rar") {
          // not yet supported -- fix me
          return false;
        }
        
        // unsupported file type
        return false;
    }
    
    // possibly should use io_MakeFileDir, not sure about using its method of error handling
    function ap_mkdir($d) {
        global $conf;
        
        umask($conf['dmask']);
        $ok = io_mkdir_p($d);
        umask($conf['umask']);
        return $ok;
    }
    
    // copy with recursive sub-directory support
    function ap_copy($src, $dst) {
    
        if (is_dir($src)) {
          if (!$dh = @opendir($src)) return false;
    
          if ($ok = ap_mkdir($dst)) {      
            while ($ok && $f = readdir($dh)) {
              if ($f == '..' || $f == '.') continue;
              $ok = ap_copy("$src/$f", "$dst/$f");
            }
          }        
          
          closedir($dh);
          return $ok;
          
        } else {
            if (!@copy($src,$dst)) return false;
            touch($dst,filemtime($src));
        }
        
        return true;
    }
    
    // delete, with recursive sub-directory support
    function ap_delete($path) {
    
        if (!is_string($path) || $path == "") return;
    
        if (is_dir($path)) {
          if (!$dh = @opendir($path)) return;
    
          while ($f = readdir($dh)) {
            if ($f == '..' || $f == '.') continue;
            ap_delete("$path/$f");
          }
          
          closedir($dh);
          rmdir($path);
          return;
          
        } else {
          unlink($path);
        }
    } 
    
    // return a list (name & type) of all the component plugins that make up this plugin
    // can this move to pluginutils?
    function ap_plugin_components($plugin) {

      global $common_plugin_types;
      
      $components = array();
      $path = DOKU_PLUGIN.$plugin.'/';
      
      foreach ($common_plugin_types as $type) {
          if (file_exists($path.$type.'.php')) { $components[] = array('name'=>$plugin, 'type'=>$type); continue; }
        
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

