<?php
/**
 * Admin Plugin Prototype
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class DokuWiki_Admin_Plugin {

  var $localised = false;
  var $lang = array();

  /**
   * General Info
   *
   * Needs to return a associative array with the following values:
   *
   * author - Author of the plugin
   * email  - Email address to contact the author
   * date   - Last modified date of the plugin in YYYY-MM-DD format
   * name   - Name of the plugin
   * desc   - Short description of the plugin (Text only)
   * url    - Website with more information on the plugin (eg. syntax description)
   */
  function getInfo(){
    trigger_error('getInfo() not implemented in '.get_class($this), E_USER_WARNING);
  }

  function getMenuText($language) {
      return $this->getLang('menu');
  }

  function getMenuSort() {
    return 1000;
  }

  function handle() {
    trigger_error('handle() not implemented in '.get_class($this), E_USER_WARNING); 
  }

  function html() {
    trigger_error('html() not implemented in '.get_class($this), E_USER_WARNING); 
  }
  
  // private methods (maybe a dokuwiki plugin base class is required for these)
  
  // plugin introspection methods
  // extract from class name, format = <plugin type>_plugin_<name>[_<component name>]
  function getPluginType() { list($t) = explode('_', get_class($this), 2); return $t;  }
  function getPluginName() { list($t, $p, $n) = explode('_', get_class($this), 4); return $n; }
  function getPluginComponent() { list($t, $p, $n, $c) = explode('_', get_class($this), 4); return (isset($c)?$c:''); }
  
  function setupLocale() {
    global $conf;            // definitely don't invoke "global $lang"
    $path = DOKU_PLUGIN.$this->getPluginName().'/lang/';
    
    // don't include once, in case several plugin components require the same language file
    @include($path.'en/lang.php');    
    if ($conf['lang'] != 'en') @include($path.$conf['lang'].'/lang.php');
    
    $this->lang = $lang;
    $this->localised = true;
  }
  
  // plugin equivalent of localFN()
  function plugin_localFN($id) {
    global $conf;
    $plugin = $this->getPluginName();
    $file = DOKU_PLUGIN.$plugin.'/lang/'.$conf['lang'].'/'.$id.'.txt';
    if(!@file_exists($file)){
      //fall back to english
      $file = DOKU_PLUGIN.$plugin.'inc/lang/en/'.$id.'.txt';
    }
  return $file;
  }
  
  // use this function to access plugin language strings
  // to try to minimise unnecessary loading of the strings when the plugin doesn't require them
  // e.g. when info plugin is querying plugins for information about themselves.
  function getLang($id) {
    if (!$this->localised) $this->setupLocale();
    
    return (isset($this->lang[$id]) ? $this->lang[$id] : '');
  }
  
  // plugin equivalent of p_locale_xhtml()
  function plugin_locale_xhtml($id) {
    return p_cached_xhtml($this->plugin_localFN($id));
  }
  
  // standard functions for outputing email addresses and links
  // use these to avoid having to duplicate code to produce links in line with the installation configuration
  function plugin_email($email, $name='', $class='', $more='') {
    if (!$email) return $name;
    $email = $this->obfuscate($email);
    if (!$name) $name = $email;
    $class = "class='".($class ? $class : 'mail')."'";
    return "<a href='mailto:$email' $class title='$email' $more>$name</a>";
  }
  
  function plugin_link($link, $title='', $class='', $target='', $more='') {
    global $conf;
    
    $link = htmlentities($link);
    if (!$title) $title = $link;
    if (!$target) $target = $conf['target']['extern'];
    if ($conf['relnofollow']) $more .= ' rel="nofollow"';
    
    if ($class) $class = " class='$class'";
    if ($target) $target = " target='$target'";
    if ($more) $more = " ".trim($more);
                
    return "<a href='$link'$class$target$more>$title</a>";
  }
                
  // output text string through the parser, allows dokuwiki markup to be used
  // very ineffecient for small pieces of data - try not to use
  function plugin_render($text, $format='xhtml') {
    return p_render($format, p_get_instructions($text),$info); 
  }
    
  // return an obfuscated email address in line with $conf['mailguard'] setting
  // FIXME?? this should really be a common function, used by the renderer as well - no point maintaining two!
  function obfuscate($email) {
    global $conf;
    
    switch ($conf['mailguard']) {
        case 'visible' :
            $obfuscate = array('@' => '[at]', '.' => '[dot]', '-' => '[dash]');
            return strtr($email, $obfuscate);
            
        case 'hex' :
            $encode = '';
            for ($x=0; $x < strlen($email); $x++) $encode .= '&#x' . bin2hex($email{$x}).';';
            return $encode;
            
        case 'none' :
        default :
            return $email;
    }            
  }

}
//Setup VIM: ex: et ts=4 enc=utf-8 :
