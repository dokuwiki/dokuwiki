<?php
/**
 *  Configuration Class and generic setting classes
 *
 *  @author  Chris Smith <chris@jalakai.co.uk>
 *  @author  Ben Coburn <btcoburn@silicodon.net>
 */

if (!class_exists('configuration')) {

  class configuration {

    var $_name = 'conf';           // name of the config variable found in the files (overridden by $config['varname'])
    var $_format = 'php';          // format of the config file, supported formats - php (overridden by $config['format'])
    var $_heading = '';            // heading string written at top of config file - don't include comment indicators
    var $_loaded = false;          // set to true after configuration files are loaded
    var $_metadata = array();      // holds metadata describing the settings
    var $setting = array();        // array of setting objects
    var $locked = false;           // configuration is considered locked if it can't be updated

    // filenames, these will be eval()'d prior to use so maintain any constants in output
    var $_default_file  = '';
    var $_local_file = '';
    var $_protected_file = '';

    /**
     *  constructor
     */
    function configuration($datafile) {
        global $conf;

        if (!@file_exists($datafile)) {
          msg('No configuration metadata found at - '.htmlspecialchars($datafile),-1);
          return;
        }
        include($datafile);

        if (isset($config['varname'])) $this->_name = $config['varname'];
        if (isset($config['format'])) $this->_format = $config['format'];
        if (isset($config['heading'])) $this->_heading = $config['heading'];

        if (isset($file['default'])) $this->_default_file = $file['default'];
        if (isset($file['local'])) $this->_local_file = $file['local'];
        if (isset($file['protected'])) $this->_protected_file = $file['protected'];

        $this->locked = $this->_is_locked();

        $this->_metadata = array_merge($meta, $this->get_plugintpl_metadata($conf['template']));

        $this->retrieve_settings();
    }

    function retrieve_settings() {
        global $conf;
        $no_default_check = array('setting_fieldset', 'setting_undefined', 'setting_no_class');

        if (!$this->_loaded) {
          $default = array_merge($this->_read_config($this->_default_file), $this->get_plugintpl_default($conf['template']));
          $local = $this->_read_config($this->_local_file);
          $protected = $this->_read_config($this->_protected_file);

          $keys = array_merge(array_keys($this->_metadata),array_keys($default), array_keys($local), array_keys($protected));
          $keys = array_unique($keys);

          foreach ($keys as $key) {
            if (isset($this->_metadata[$key])) {
              $class = $this->_metadata[$key][0];
              $class = ($class && class_exists('setting_'.$class)) ? 'setting_'.$class : 'setting';
              if ($class=='setting') {
                $this->setting[] = new setting_no_class($key,$param);
              }

              $param = $this->_metadata[$key];
              array_shift($param);
            } else {
              $class = 'setting_undefined';
              $param = NULL;
            }

            if (!in_array($class, $no_default_check) && !isset($default[$key])) {
              $this->setting[] = new setting_no_default($key,$param);
            }

            $this->setting[$key] = new $class($key,$param);
            $this->setting[$key]->initialize($default[$key],$local[$key],$protected[$key]);
          }

          $this->_loaded = true;
        }
    }

    function save_settings($id, $header='', $backup=true) {

      if ($this->locked) return false;

      $file = eval('return '.$this->_local_file.';');

      // backup current file (remove any existing backup)
      if (@file_exists($file) && $backup) {
        if (@file_exists($file.'.bak')) @unlink($file.'.bak');
        if (!io_rename($file, $file.'.bak')) return false;
      }

      if (!$fh = @fopen($file, 'wb')) {
        io_rename($file.'.bak', $file);     // problem opening, restore the backup
        return false;
      }

      if (empty($header)) $header = $this->_heading;

      $out = $this->_out_header($id,$header);

      foreach ($this->setting as $setting) {
        $out .= $setting->out($this->_name, $this->_format);
      }

      $out .= $this->_out_footer();

      @fwrite($fh, $out);
      fclose($fh);
      return true;
    }

    /**
     * return an array of config settings
     */
    function _read_config($file) {

      if (!$file) return array();

      $config = array();
      $file = eval('return '.$file.';');

      if ($this->_format == 'php') {

        if(@file_exists($file)){
            $contents = @php_strip_whitespace($file);
        }else{
            $contents = '';
        }
        $pattern = '/\$'.$this->_name.'\[[\'"]([^=]+)[\'"]\] ?= ?(.*?);(?=[^;]*(?:\$'.$this->_name.'|@include|$))/s';
        $matches=array();
        preg_match_all($pattern,$contents,$matches,PREG_SET_ORDER);

        for ($i=0; $i<count($matches); $i++) {

          // correct issues with the incoming data
          // FIXME ... for now merge multi-dimensional array indices using ____
          $key = preg_replace('/.\]\[./',CM_KEYMARKER,$matches[$i][1]);

          // remove quotes from quoted strings & unescape escaped data
          $value = preg_replace('/^(\'|")(.*)(?<!\\\\)\1$/s','$2',$matches[$i][2]);
          $value = strtr($value, array('\\\\'=>'\\','\\\''=>'\'','\\"'=>'"'));

          $config[$key] = $value;
        }
      }

      return $config;
    }

    function _out_header($id, $header) {
      $out = '';
      if ($this->_format == 'php') {
          $out .= '<'.'?php'."\n".
                "/*\n".
                " * ".$header." \n".
                " * Auto-generated by ".$id." plugin \n".
                " * Run for user: ".$_SERVER['REMOTE_USER']."\n".
                " * Date: ".date('r')."\n".
                " */\n\n";
      }

      return $out;
    }

    function _out_footer() {
      $out = '';
      if ($this->_format == 'php') {
          if ($this->_protected_file) {
            $out .= "\n@include(".$this->_protected_file.");\n";
          }
          $out .= "\n// end auto-generated content\n";
      }

      return $out;
    }

    // configuration is considered locked if there is no local settings filename
    // or the directory its in is not writable or the file exists and is not writable
    function _is_locked() {
      if (!$this->_local_file) return true;

      $local = eval('return '.$this->_local_file.';');

      if (!is_writable(dirname($local))) return true;
      if (@file_exists($local) && !is_writable($local)) return true;

      return false;
    }

    /**
     *  not used ... conf's contents are an array!
     *  reduce any multidimensional settings to one dimension using CM_KEYMARKER
     */
    function _flatten($conf,$prefix='') {

        $out = array();

        foreach($conf as $key => $value) {
          if (!is_array($value)) {
            $out[$prefix.$key] = $value;
            continue;
          }

          $tmp = $this->_flatten($value,$prefix.$key.CM_KEYMARKER);
          $out = array_merge($out,$tmp);
        }

        return $out;
    }

    /**
     * load metadata for plugin and template settings
     */
    function get_plugintpl_metadata($tpl){
      $file     = '/conf/metadata.php';
      $class    = '/conf/settings.class.php';
      $metadata = array();

      if ($dh = opendir(DOKU_PLUGIN)) {
        while (false !== ($plugin = readdir($dh))) {
          if ($plugin == '.' || $plugin == '..' || $plugin == 'tmp' || $plugin == 'config') continue;
          if (is_file(DOKU_PLUGIN.$plugin)) continue;

          if (@file_exists(DOKU_PLUGIN.$plugin.$file)){
            $meta = array();
            @include(DOKU_PLUGIN.$plugin.$file);
            @include(DOKU_PLUGIN.$plugin.$class);
            if (!empty($meta)) {
              $metadata['plugin'.CM_KEYMARKER.$plugin.CM_KEYMARKER.'plugin_settings_name'] = array('fieldset');
            }
            foreach ($meta as $key => $value){
              if ($value[0]=='fieldset') { continue; } //plugins only get one fieldset
              $metadata['plugin'.CM_KEYMARKER.$plugin.CM_KEYMARKER.$key] = $value;
            }
          }
        }
        closedir($dh);
      }

      // the same for the active template
      if (@file_exists(DOKU_TPLINC.$file)){
        $meta = array();
        @include(DOKU_TPLINC.$file);
        @include(DOKU_TPLINC.$class);
        if (!empty($meta)) {
          $metadata['tpl'.CM_KEYMARKER.$tpl.CM_KEYMARKER.'template_settings_name'] = array('fieldset');
        }
        foreach ($meta as $key => $value){
          if ($value[0]=='fieldset') { continue; } //template only gets one fieldset
          $metadata['tpl'.CM_KEYMARKER.$tpl.CM_KEYMARKER.$key] = $value;
        }
      }

      return $metadata;
    }

    /**
     * load default settings for plugins and templates
     */
    function get_plugintpl_default($tpl){
      $file    = '/conf/default.php';
      $default = array();

      if ($dh = opendir(DOKU_PLUGIN)) {
        while (false !== ($plugin = readdir($dh))) {
          if (@file_exists(DOKU_PLUGIN.$plugin.$file)){
            $conf = array();
            @include(DOKU_PLUGIN.$plugin.$file);
            foreach ($conf as $key => $value){
              $default['plugin'.CM_KEYMARKER.$plugin.CM_KEYMARKER.$key] = $value;
            }
          }
        }
        closedir($dh);
      }

      // the same for the active template
      if (@file_exists(DOKU_TPLINC.$file)){
        $conf = array();
        @include(DOKU_TPLINC.$file);
        foreach ($conf as $key => $value){
          $default['tpl'.CM_KEYMARKER.$tpl.CM_KEYMARKER.$key] = $value;
        }
      }

      return $default;
    }

  }
}

if (!class_exists('setting')) {
  class setting {

    var $_key = '';
    var $_default = NULL;
    var $_local = NULL;
    var $_protected = NULL;

    var $_pattern = '';
    var $_error = false;            // only used by those classes which error check
    var $_input = NULL;             // only used by those classes which error check

    function setting($key, $params=NULL) {
        $this->_key = $key;

        if (is_array($params)) {
          foreach($params as $property => $value) {
            $this->$property = $value;
          }
        }
    }

    /**
     *  receives current values for the setting $key
     */
    function initialize($default, $local, $protected) {
        if (isset($default)) $this->_default = $default;
        if (isset($local)) $this->_local = $local;
        if (isset($protected)) $this->_protected = $protected;
    }

    /**
     *  update setting with user provided value $input
     *  if value fails error check, save it
     *
     *  @return true if changed, false otherwise (incl. on error)
     */
    function update($input) {
        if (is_null($input)) return false;
        if ($this->is_protected()) return false;

        $value = is_null($this->_local) ? $this->_default : $this->_local;
        if ($value == $input) return false;

        if ($this->_pattern && !preg_match($this->_pattern,$input)) {
          $this->_error = true;
          $this->_input = $input;
          return false;
        }

        $this->_local = $input;
        return true;
    }

    /**
     *  @return   array(string $label_html, string $input_html)
     */
    function html(&$plugin, $echo=false) {
        $value = '';
        $disable = '';

        if ($this->is_protected()) {
          $value = $this->_protected;
          $disable = 'disabled="disabled"';
        } else {
          if ($echo && $this->_error) {
            $value = $this->_input;
          } else {
            $value = is_null($this->_local) ? $this->_default : $this->_local;
          }
        }

        $key = htmlspecialchars($this->_key);
        $value = htmlspecialchars($value);

        $label = '<label for="config___'.$key.'">'.$this->prompt($plugin).'</label>';
        $input = '<textarea rows="3" cols="40" id="config___'.$key.'" name="config['.$key.']" class="edit" '.$disable.'>'.$value.'</textarea>';
        return array($label,$input);
    }

    /**
     *  generate string to save setting value to file according to $fmt
     */
    function out($var, $fmt='php') {

      if ($this->is_protected()) return '';
      if (is_null($this->_local) || ($this->_default == $this->_local)) return '';

      $out = '';

      if ($fmt=='php') {
        // translation string needs to be improved FIXME
        $tr = array("\n"=>'\n', "\r"=>'\r', "\t"=>'\t', "\\" => '\\\\', "'" => '\\\'');
        $tr = array("\\" => '\\\\', "'" => '\\\'');

        $out =  '$'.$var."['".$this->_out_key()."'] = '".strtr($this->_local, $tr)."';\n";
      }

      return $out;
    }

    function prompt(&$plugin) {
      $prompt = $plugin->getLang($this->_key);
      if (!$prompt) $prompt = htmlspecialchars(str_replace(array('____','_'),' ',$this->_key));
      return $prompt;
    }

    function is_protected() { return !is_null($this->_protected); }
    function is_default() { return !$this->is_protected() && is_null($this->_local); }
    function error() { return $this->_error; }

    function _out_key($pretty=false) {
        if($pretty){
            return str_replace(CM_KEYMARKER,"&raquo;",$this->_key);
        }else{
            return str_replace(CM_KEYMARKER,"']['",$this->_key);
        }
    }
  }
}

if (!class_exists('setting_string')) {
  class setting_string extends setting {
    function html(&$plugin, $echo=false) {
        $value = '';
        $disable = '';

        if ($this->is_protected()) {
          $value = $this->_protected;
          $disable = 'disabled="disabled"';
        } else {
          if ($echo && $this->_error) {
            $value = $this->_input;
          } else {
            $value = is_null($this->_local) ? $this->_default : $this->_local;
          }
        }

        $key = htmlspecialchars($this->_key);
        $value = htmlspecialchars($value);

        $label = '<label for="config___'.$key.'">'.$this->prompt($plugin).'</label>';
        $input = '<input id="config___'.$key.'" name="config['.$key.']" type="text" class="edit" value="'.$value.'" '.$disable.'/>';
        return array($label,$input);
    }
  }
}

if (!class_exists('setting_password')) {
  class setting_password extends setting_string {

    function update($input) {
        if ($this->is_protected()) return false;
        if (!$input) return false;

        if ($this->_pattern && !preg_match($this->_pattern,$input)) {
          $this->_error = true;
          $this->_input = $input;
          return false;
        }

        $this->_local = $input;
        return true;
    }

    function html(&$plugin, $echo=false) {

        $value = '';
        $disable = $this->is_protected() ? 'disabled="disabled"' : '';

        $key = htmlspecialchars($this->_key);

        $label = '<label for="config___'.$key.'">'.$this->prompt($plugin).'</label>';
        $input = '<input id="config___'.$key.'" name="config['.$key.']" type="password" class="edit" value="" '.$disable.'/>';
        return array($label,$input);
    }
  }
}

if (!class_exists('setting_email')) {
  class setting_email extends setting_string {
    var $_pattern = '#^\s*(([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)(,\s*([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+))*)?\s*$#i';
  }
}

if (!class_exists('setting_richemail')) {
  class setting_richemail extends setting_email {

    /**
     *  update setting with user provided value $input
     *  if value fails error check, save it
     *
     *  @return true if changed, false otherwise (incl. on error)
     */
    function update($input) {
        if (is_null($input)) return false;
        if ($this->is_protected()) return false;

        $value = is_null($this->_local) ? $this->_default : $this->_local;
        if ($value == $input) return false;

        // replace variables with pseudo values
        $test = $input;
        $test = str_replace('@USER@','joe',$test);
        $test = str_replace('@NAME@','Joe Schmoe',$test);
        $test = str_replace('@MAIL@','joe@example.com',$test);

        // now only check the address part
        if(preg_match('#(.*?)<(.*?)>#',$test,$matches)){
          $text = trim($matches[1]);
          $addr = $matches[2];
        }else{
          $addr = $test;
        }

        if ($this->_pattern && !preg_match($this->_pattern,$addr)) {
          $this->_error = true;
          $this->_input = $input;
          return false;
        }

        $this->_local = $input;
        return true;
    }

  }
}


if (!class_exists('setting_numeric')) {
  class setting_numeric extends setting_string {
    // This allows for many PHP syntax errors...
    // var $_pattern = '/^[-+\/*0-9 ]*$/';
    // much more restrictive, but should eliminate syntax errors.
    var $_pattern = '/^[-]?[0-9]+(?:[-+*][0-9]+)*$/';
    //FIXME - make the numeric error checking better.

    function out($var, $fmt='php') {

      if ($this->is_protected()) return '';
      if (is_null($this->_local) || ($this->_default == $this->_local)) return '';

      $out = '';

      if ($fmt=='php') {
        $out .=  '$'.$var."['".$this->_out_key()."'] = ".$this->_local.";\n";
      }

    return $out;
    }
  }
}

if (!class_exists('setting_numericopt')) {
    class setting_numericopt extends setting_numeric {
        // just allow an empty config
        var $_pattern = '/^(|[-]?[0-9]+(?:[-+*][0-9]+)*)$/';
    }
}

if (!class_exists('setting_onoff')) {
  class setting_onoff extends setting_numeric {

    function html(&$plugin) {
        $value = '';
        $disable = '';

        if ($this->is_protected()) {
          $value = $this->_protected;
          $disable = ' disabled="disabled"';
        } else {
          $value = is_null($this->_local) ? $this->_default : $this->_local;
        }

        $key = htmlspecialchars($this->_key);
        $checked = ($value) ? ' checked="checked"' : '';

        $label = '<label for="config___'.$key.'">'.$this->prompt($plugin).'</label>';
        $input = '<div class="input"><input id="config___'.$key.'" name="config['.$key.']" type="checkbox" class="checkbox" value="1"'.$checked.$disable.'/></div>';
        return array($label,$input);
    }

    function update($input) {
        if ($this->is_protected()) return false;

        $input = ($input) ? 1 : 0;
        $value = is_null($this->_local) ? $this->_default : $this->_local;
        if ($value == $input) return false;

        $this->_local = $input;
        return true;
    }
  }
}

if (!class_exists('setting_multichoice')) {
  class setting_multichoice extends setting_string {
    var $_choices = array();

    function html(&$plugin) {
        $value = '';
        $disable = '';
        $nochoice = '';

        if ($this->is_protected()) {
          $value = $this->_protected;
          $disable = ' disabled="disabled"';
        } else {
          $value = is_null($this->_local) ? $this->_default : $this->_local;
        }

        // ensure current value is included
        if (!in_array($value, $this->_choices)) {
            $this->_choices[] = $value;
        }
        // disable if no other choices
        if (!$this->is_protected() && count($this->_choices) <= 1) {
          $disable = ' disabled="disabled"';
          $nochoice = $plugin->getLang('nochoice');
        }

        $key = htmlspecialchars($this->_key);

        $label = '<label for="config___'.$key.'">'.$this->prompt($plugin).'</label>';

        $input = "<div class=\"input\">\n";
        $input .= '<select class="edit" id="config___'.$key.'" name="config['.$key.']"'.$disable.'>'."\n";
        foreach ($this->_choices as $choice) {
            $selected = ($value == $choice) ? ' selected="selected"' : '';
            $option = $plugin->getLang($this->_key.'_o_'.$choice);
            if (!$option) $option = $choice;

            $choice = htmlspecialchars($choice);
            $option = htmlspecialchars($option);
            $input .= '  <option value="'.$choice.'"'.$selected.' >'.$option.'</option>'."\n";
        }
        $input .= "</select> $nochoice \n";
        $input .= "</div>\n";

        return array($label,$input);
    }

    function update($input) {
        if (is_null($input)) return false;
        if ($this->is_protected()) return false;

        $value = is_null($this->_local) ? $this->_default : $this->_local;
        if ($value == $input) return false;

        if (!in_array($input, $this->_choices)) return false;

        $this->_local = $input;
        return true;
    }
  }
}


if (!class_exists('setting_dirchoice')) {
  class setting_dirchoice extends setting_multichoice {

    var $_dir = '';

    function initialize($default,$local,$protected) {

      // populate $this->_choices with a list of directories
      $list = array();

      if ($dh = @opendir($this->_dir)) {
        while (false !== ($entry = readdir($dh))) {
          if ($entry == '.' || $entry == '..') continue;
          if ($this->_pattern && !preg_match($this->_pattern,$entry)) continue;

          $file = (is_link($this->_dir.$entry)) ? readlink($this->_dir.$entry) : $entry;
          if (is_dir($this->_dir.$file)) $list[] = $entry;
        }
        closedir($dh);
      }
      sort($list);
      $this->_choices = $list;

      parent::initialize($default,$local,$protected);
    }
  }
}


if (!class_exists('setting_hidden')) {
  class setting_hidden extends setting {
      // Used to explicitly ignore a setting in the configuration manager.
  }
}

if (!class_exists('setting_fieldset')) {
  class setting_fieldset extends setting {
      // A do-nothing class used to detect the 'fieldset' type.
      // Used to start a new settings "display-group".
  }
}

if (!class_exists('setting_undefined')) {
  class setting_undefined extends setting_hidden {
      // A do-nothing class used to detect settings with no metadata entry.
      // Used internaly to hide undefined settings, and generate the undefined settings list.
  }
}

if (!class_exists('setting_no_class')) {
  class setting_no_class extends setting_undefined {
      // A do-nothing class used to detect settings with a missing setting class.
      // Used internaly to hide undefined settings, and generate the undefined settings list.
  }
}

if (!class_exists('setting_no_default')) {
  class setting_no_default extends setting_undefined {
      // A do-nothing class used to detect settings with no default value.
      // Used internaly to hide undefined settings, and generate the undefined settings list.
  }
}

if (!class_exists('setting_multicheckbox')) {
  class setting_multicheckbox extends setting_string {

    var $_choices = array();
    var $_combine = array();

    function update($input) {
        if ($this->is_protected()) return false;

        // split any combined values + convert from array to comma separated string
        $input = ($input) ? $input : array();
        $input = $this->_array2str($input);

        $value = is_null($this->_local) ? $this->_default : $this->_local;
        if ($value == $input) return false;

        if ($this->_pattern && !preg_match($this->_pattern,$input)) {
          $this->_error = true;
          $this->_input = $input;
          return false;
        }

        $this->_local = $input;
        return true;
    }

    function html(&$plugin, $echo=false) {

        $value = '';
        $disable = '';

        if ($this->is_protected()) {
          $value = $this->_protected;
          $disable = 'disabled="disabled"';
        } else {
          if ($echo && $this->_error) {
            $value = $this->_input;
          } else {
            $value = is_null($this->_local) ? $this->_default : $this->_local;
          }
        }

        $key = htmlspecialchars($this->_key);

        // convert from comma separated list into array + combine complimentary actions
        $value = $this->_str2array($value);
        $default = $this->_str2array($this->_default);

        $input = '';
        foreach ($this->_choices as $choice) {
          $idx = array_search($choice, $value);
          $idx_default = array_search($choice,$default);

          $checked = ($idx !== false) ? 'checked="checked"' : '';

          // ideally this would be handled using a second class of "default", however IE6 does not
          // correctly support CSS selectors referencing multiple class names on the same element
          // (e.g. .default.selection).
          $class = (($idx !== false) == (false !== $idx_default)) ? " selectiondefault" : "";

          $prompt = ($plugin->getLang($this->_key.'_'.$choice) ?
                          $plugin->getLang($this->_key.'_'.$choice) : htmlspecialchars($choice));

          $input .= '<div class="selection'.$class.'">'."\n";
          $input .= '<label for="config___'.$key.'_'.$choice.'">'.$prompt."</label>\n";
          $input .= '<input id="config___'.$key.'_'.$choice.'" name="config['.$key.'][]" type="checkbox" class="checkbox" value="'.$choice.'" '.$disable.' '.$checked."/>\n";
          $input .= "</div>\n";

          // remove this action from the disabledactions array
          if ($idx !== false) unset($value[$idx]);
          if ($idx_default !== false) unset($default[$idx_default]);
        }

        // handle any remaining values
        $other = join(',',$value);

        $class = (count($default == count($value)) && (count($value) == count(array_intersect($value,$default)))) ?
                        " selectiondefault" : "";

        $input .= '<div class="other'.$class.'">'."\n";
        $input .= '<label for="config___'.$key.'_other">'.$plugin->getLang($key.'_other')."</label>\n";
        $input .= '<input id="config___'.$key.'_other" name="config['.$key.'][other]" type="text" class="edit" value="'.htmlspecialchars($other).'" '.$disable." />\n";
        $input .= "</div>\n";

//        $label = '<label for="config___'.$key.'">'.$this->prompt($plugin).'</label>';
        $label = $this->prompt($plugin);
        return array($label,$input);
    }

    /**
     * convert comma separated list to an array and combine any complimentary values
     */
    function _str2array($str) {
      $array = explode(',',$str);

      if (!empty($this->_combine)) {
        foreach ($this->_combine as $key => $combinators) {
          $idx = array();
          foreach ($combinators as $val) {
            if  (($idx[] = array_search($val, $array)) === false) break;
          }

          if (count($idx) && $idx[count($idx)-1] !== false) {
            foreach ($idx as $i) unset($array[$i]);
            $array[] = $key;
          }
        }
      }

      return $array;
    }

    /**
     * convert array of values + other back to a comma separated list, incl. splitting any combined values
     */
    function _array2str($input) {

      // handle other
      $other = trim($input['other']);
      $other = !empty($other) ? explode(',',str_replace(' ','',$input['other'])) : array();
      unset($input['other']);

      $array = array_unique(array_merge($input, $other));

      // deconstruct any combinations
      if (!empty($this->_combine)) {
       foreach ($this->_combine as $key => $combinators) {

          $idx = array_search($key,$array);
          if ($idx !== false) {
            unset($array[$idx]);
            $array = array_merge($array, $combinators);
          }
        }
      }

      return join(',',array_unique($array));
    }
  }
}


/**
 *  Provide php_strip_whitespace (php5 function) functionality
 *
 *  @author   Chris Smith <chris@jalakai.co.uk>
 */
if (!function_exists('php_strip_whitespace'))  {

  if (function_exists('token_get_all')) {

    if (!defined('T_ML_COMMENT')) {
      define('T_ML_COMMENT', T_COMMENT);
    } else {
      define('T_DOC_COMMENT', T_ML_COMMENT);
    }

    /**
     * modified from original
     * source Google Groups, php.general, by David Otton
     */
    function php_strip_whitespace($file) {
        if (!@is_readable($file)) return '';

        $in = join('',@file($file));
        $out = '';

        $tokens = token_get_all($in);

        foreach ($tokens as $token) {
          if (is_string ($token)) {
            $out .= $token;
          } else {
            list ($id, $text) = $token;
            switch ($id) {
              case T_COMMENT : // fall thru
              case T_ML_COMMENT : // fall thru
              case T_DOC_COMMENT : // fall thru
              case T_WHITESPACE :
                break;
              default : $out .= $text; break;
            }
          }
        }
        return ($out);
    }

  } else {

    function is_whitespace($c) { return (strpos("\t\n\r ",$c) !== false); }
    function is_quote($c) { return (strpos("\"'",$c) !== false); }
    function is_escaped($s,$i) {
        $idx = $i-1;
        while(($idx>=0) && ($s{$idx} == '\\')) $idx--;
        return (($i - $idx + 1) % 2);
    }

    function is_commentopen($str, $i) {
        if ($str{$i} == '#') return "\n";
        if ($str{$i} == '/') {
          if ($str{$i+1} == '/') return "\n";
          if ($str{$i+1} == '*') return "*/";
        }

        return false;
    }

    function php_strip_whitespace($file) {

        if (!@is_readable($file)) return '';

        $contents = join('',@file($file));
        $out = '';

        $state = 0;
        for ($i=0; $i<strlen($contents); $i++) {
          if (!$state && is_whitespace($contents{$i})) continue;

          if (!$state && ($c_close = is_commentopen($contents, $i))) {
            $c_open_len = ($contents{$i} == '/') ? 2 : 1;
            $i = strpos($contents, $c_close, $i+$c_open_len)+strlen($c_close)-1;
            continue;
          }

          $out .= $contents{$i};
          if (is_quote($contents{$i})) {
              if (($state == $contents{$i}) && !is_escaped($contents, $i)) { $state = 0; continue; }
            if (!$state) {$state = $contents{$i}; continue; }
          }
        }

        return $out;
    }
  }
}
