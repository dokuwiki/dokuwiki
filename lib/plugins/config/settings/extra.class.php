<?php
/**
 * additional setting classes specific to these settings
 *
 * @author    Chris Smith <chris@jalakai.co.uk>
 */

if (!class_exists('setting_sepchar')) {
  class setting_sepchar extends setting_multichoice {

    function setting_sepchar($key,$param=NULL) {
        $str = '_-.';
        for ($i=0;$i<strlen($str);$i++) $this->_choices[] = $str{$i};

        // call foundation class constructor
        $this->setting($key,$param);
    }
  }
}

if (!class_exists('setting_savedir')) {
  class setting_savedir extends setting_string {

    function update($input) {
        if ($this->is_protected()) return false;

        $value = is_null($this->_local) ? $this->_default : $this->_local;
        if ($value == $input) return false;

        if (!init_path($input)) {
          $this->_error = true;
          $this->_input = $input;
          return false;
        }

        $this->_local = $input;
        return true;
    }
  }
}

if (!class_exists('setting_authtype')) {
  class setting_authtype extends setting_multichoice {

    function initialize($default,$local,$protected) {

      // populate $this->_choices with a list of available auth mechanisms
      $authtypes = glob(DOKU_INC.'inc/auth/*.class.php');
      $authtypes = preg_replace('#^.*/([^/]*)\.class\.php$#i','$1', $authtypes);
      $authtypes = array_diff($authtypes, array('basic'));
      sort($authtypes);

      $this->_choices = $authtypes;

      parent::initialize($default,$local,$protected);
    }
  }
}

if (!class_exists('setting_im_convert')) {
  class setting_im_convert extends setting_string {

    function update($input) {
        if ($this->is_protected()) return false;

        $input = trim($input);

        $value = is_null($this->_local) ? $this->_default : $this->_local;
        if ($value == $input) return false;

        if ($input && !@file_exists($input)) {
          $this->_error = true;
          $this->_input = $input;
          return false;
        }

        $this->_local = $input;
        return true;
    }
  }
}

if (!class_exists('setting_disableactions')) {
  class setting_disableactions extends setting_multicheckbox {

    function html(&$plugin, $echo=false) {
        global $lang;

        // make some language adjustments (there must be a better way)
        // transfer some DokuWiki language strings to the plugin
        if (!$plugin->localised) $this->setupLocale();
        $plugin->lang[$this->_key.'_revisions'] = $lang['btn_revs'];

        foreach ($this->_choices as $choice)
          if (isset($lang['btn_'.$choice])) $plugin->lang[$this->_key.'_'.$choice] = $lang['btn_'.$choice];

        return parent::html($plugin, $echo);
    }
  }
}

if (!class_exists('setting_compression')) {
  class setting_compression extends setting_multichoice {

    var $_choices = array('0');      // 0 = no compression, always supported

    function initialize($default,$local,$protected) {

      // populate _choices with the compression methods supported by this php installation
      if (function_exists('gzopen')) $this->_choices[] = 'gz';
      if (function_exists('bzopen')) $this->_choices[] = 'bz2';

      parent::initialize($default,$local,$protected);
    }
  }
}

if (!class_exists('setting_license')) {
  class setting_license extends setting_multichoice {

    var $_choices = array('');      // none choosen

    function initialize($default,$local,$protected) {
      global $license;

      foreach($license as $key => $data){
        $this->_choices[] = $key;
        $this->lang[$this->_key.'_o_'.$key] = $data['name'];
      }

      parent::initialize($default,$local,$protected);
    }
  }
}


if (!class_exists('setting_renderer')) {
  class setting_renderer extends setting_multichoice {
    var $_prompts = array();

    function initialize($default,$local,$protected) {
      $format = $this->_format;

      foreach (plugin_list('renderer') as $plugin) {
        $renderer =& plugin_load('renderer',$plugin);
        if (method_exists($renderer,'canRender') && $renderer->canRender($format)) {
          $this->_choices[] = $plugin;

          $info = $renderer->getInfo();
          $this->_prompts[$plugin] = $info['name'];
        }
      }

      parent::initialize($default,$local,$protected);
    }

    function html(&$plugin, $echo=false) {

      // make some language adjustments (there must be a better way)
      // transfer some plugin names to the config plugin
      if (!$plugin->localised) $this->setupLocale();

      foreach ($this->_choices as $choice) {
        if (!isset($plugin->lang[$this->_key.'_o_'.$choice])) {
          if (!isset($this->_prompts[$choice])) {
            $plugin->lang[$this->_key.'_o_'.$choice] = sprintf($plugin->lang['renderer__core'],$choice);
          } else {
            $plugin->lang[$this->_key.'_o_'.$choice] = sprintf($plugin->lang['renderer__plugin'],$this->_prompts[$choice]);
          }
        }
      }
      return parent::html($plugin, $echo);
    }
  }
}
