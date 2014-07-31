<?php
/**
 * Configuration Class and generic setting classes
 *
 * @author  Chris Smith <chris@jalakai.co.uk>
 * @author  Ben Coburn <btcoburn@silicodon.net>
 */


if(!defined('CM_KEYMARKER')) define('CM_KEYMARKER','____');

if (!class_exists('configuration')) {

    class configuration {

        var $_name = 'conf';           // name of the config variable found in the files (overridden by $config['varname'])
        var $_format = 'php';          // format of the config file, supported formats - php (overridden by $config['format'])
        var $_heading = '';            // heading string written at top of config file - don't include comment indicators
        var $_loaded = false;          // set to true after configuration files are loaded
        var $_metadata = array();      // holds metadata describing the settings
        /** @var setting[]  */
        var $setting = array();        // array of setting objects
        var $locked = false;           // configuration is considered locked if it can't be updated
        var $show_disabled_plugins = false;

        // configuration filenames
        var $_default_files  = array();
        var $_local_files = array();      // updated configuration is written to the first file
        var $_protected_files = array();

        var $_plugin_list = null;

        /**
         * constructor
         *
         * @param string $datafile path to config metadata file
         */
        public function configuration($datafile) {
            global $conf, $config_cascade;

            if (!@file_exists($datafile)) {
                msg('No configuration metadata found at - '.htmlspecialchars($datafile),-1);
                return;
            }
            $meta = array();
            include($datafile);

            if (isset($config['varname'])) $this->_name = $config['varname'];
            if (isset($config['format'])) $this->_format = $config['format'];
            if (isset($config['heading'])) $this->_heading = $config['heading'];

            $this->_default_files = $config_cascade['main']['default'];
            $this->_local_files = $config_cascade['main']['local'];
            $this->_protected_files = $config_cascade['main']['protected'];

            $this->locked = $this->_is_locked();
            $this->_metadata = array_merge($meta, $this->get_plugintpl_metadata($conf['template']));
            $this->retrieve_settings();
        }

        /**
         * Retrieve and stores settings in setting[] attribute
         */
        public function retrieve_settings() {
            global $conf;
            $no_default_check = array('setting_fieldset', 'setting_undefined', 'setting_no_class');

            if (!$this->_loaded) {
                $default = array_merge($this->get_plugintpl_default($conf['template']), $this->_read_config_group($this->_default_files));
                $local = $this->_read_config_group($this->_local_files);
                $protected = $this->_read_config_group($this->_protected_files);

                $keys = array_merge(array_keys($this->_metadata),array_keys($default), array_keys($local), array_keys($protected));
                $keys = array_unique($keys);

                $param = null;
                foreach ($keys as $key) {
                    if (isset($this->_metadata[$key])) {
                        $class = $this->_metadata[$key][0];

                        if($class && class_exists('setting_'.$class)){
                            $class = 'setting_'.$class;
                        } else {
                            if($class != '') {
                                $this->setting[] = new setting_no_class($key,$param);
                            }
                            $class = 'setting';
                        }

                        $param = $this->_metadata[$key];
                        array_shift($param);
                    } else {
                        $class = 'setting_undefined';
                        $param = null;
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

        /**
         * Stores setting[] array to file
         *
         * @param string $id     Name of plugin, which saves the settings
         * @param string $header Text at the top of the rewritten settings file
         * @param bool $backup   backup current file? (remove any existing backup)
         * @return bool succesful?
         */
        public function save_settings($id, $header='', $backup=true) {
            global $conf;

            if ($this->locked) return false;

            // write back to the last file in the local config cascade
            $file = end($this->_local_files);

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
            if($conf['fperm']) chmod($file, $conf['fperm']);
            return true;
        }

        /**
         * Update last modified time stamp of the config file
         */
        public function touch_settings(){
            if ($this->locked) return false;
            $file = end($this->_local_files);
            return @touch($file);
        }

        /**
         * Read and merge given config files
         *
         * @param array $files file paths
         * @return array config settings
         */
        protected function _read_config_group($files) {
            $config = array();
            foreach ($files as $file) {
                $config = array_merge($config, $this->_read_config($file));
            }

            return $config;
        }

        /**
         * Return an array of config settings
         *
         * @param string $file file path
         * @return array config settings
         */
        function _read_config($file) {

            if (!$file) return array();

            $config = array();

            if ($this->_format == 'php') {

                if(@file_exists($file)){
                    $contents = @php_strip_whitespace($file);
                }else{
                    $contents = '';
                }
                $pattern = '/\$'.$this->_name.'\[[\'"]([^=]+)[\'"]\] ?= ?(.*?);(?=[^;]*(?:\$'.$this->_name.'|$))/s';
                $matches=array();
                preg_match_all($pattern,$contents,$matches,PREG_SET_ORDER);

                for ($i=0; $i<count($matches); $i++) {
                    $value = $matches[$i][2];

                    // correct issues with the incoming data
                    // FIXME ... for now merge multi-dimensional array indices using ____
                    $key = preg_replace('/.\]\[./',CM_KEYMARKER,$matches[$i][1]);

                    // handle arrays
                    if(preg_match('/^array ?\((.*)\)/', $value, $match)){
                        $arr = explode(',', $match[1]);

                        // remove quotes from quoted strings & unescape escaped data
                        $len = count($arr);
                        for($j=0; $j<$len; $j++){
                            $arr[$j] = trim($arr[$j]);
                            $arr[$j] = preg_replace('/^(\'|")(.*)(?<!\\\\)\1$/s','$2',$arr[$j]);
                            $arr[$j] = strtr($arr[$j], array('\\\\'=>'\\','\\\''=>'\'','\\"'=>'"'));
                        }

                        $value = $arr;
                    }else{
                        // remove quotes from quoted strings & unescape escaped data
                        $value = preg_replace('/^(\'|")(.*)(?<!\\\\)\1$/s','$2',$value);
                        $value = strtr($value, array('\\\\'=>'\\','\\\''=>'\'','\\"'=>'"'));
                    }

                    $config[$key] = $value;
                }
            }

            return $config;
        }

        /**
         * Returns header of rewritten settings file
         *
         * @param string $id plugin name of which generated this output
         * @param string $header additional text for at top of the file
         * @return string text of header
         */
        protected function _out_header($id, $header) {
            $out = '';
            if ($this->_format == 'php') {
                $out .= '<'.'?php'."\n".
                      "/*\n".
                      " * ".$header."\n".
                      " * Auto-generated by ".$id." plugin\n".
                      " * Run for user: ".$_SERVER['REMOTE_USER']."\n".
                      " * Date: ".date('r')."\n".
                      " */\n\n";
            }

            return $out;
        }

        /**
         * Returns footer of rewritten settings file
         *
         * @return string text of footer
         */
        protected function _out_footer() {
            $out = '';
            if ($this->_format == 'php') {
                $out .= "\n// end auto-generated content\n";
            }

            return $out;
        }

        /**
         * Configuration is considered locked if there is no local settings filename
         * or the directory its in is not writable or the file exists and is not writable
         *
         * @return bool true: locked, false: writable
         */
        protected function _is_locked() {
            if (!$this->_local_files) return true;

            $local = $this->_local_files[0];

            if (!is_writable(dirname($local))) return true;
            if (@file_exists($local) && !is_writable($local)) return true;

            return false;
        }

        /**
         * not used ... conf's contents are an array!
         * reduce any multidimensional settings to one dimension using CM_KEYMARKER
         */
        protected function _flatten($conf,$prefix='') {

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
         * Returns array of plugin names
         *
         * @return array plugin names
         * @triggers PLUGIN_CONFIG_PLUGINLIST event
         */
        function get_plugin_list() {
            if (is_null($this->_plugin_list)) {
                $list = plugin_list('',$this->show_disabled_plugins);

                // remove this plugin from the list
                $idx = array_search('config',$list);
                unset($list[$idx]);

                trigger_event('PLUGIN_CONFIG_PLUGINLIST',$list);
                $this->_plugin_list = $list;
            }

            return $this->_plugin_list;
        }

        /**
         * load metadata for plugin and template settings
         *
         * @param string $tpl name of active template
         * @return array metadata of settings
         */
        function get_plugintpl_metadata($tpl){
            $file     = '/conf/metadata.php';
            $class    = '/conf/settings.class.php';
            $metadata = array();

            foreach ($this->get_plugin_list() as $plugin) {
                $plugin_dir = plugin_directory($plugin);
                if (@file_exists(DOKU_PLUGIN.$plugin_dir.$file)){
                    $meta = array();
                    @include(DOKU_PLUGIN.$plugin_dir.$file);
                    @include(DOKU_PLUGIN.$plugin_dir.$class);
                    if (!empty($meta)) {
                        $metadata['plugin'.CM_KEYMARKER.$plugin.CM_KEYMARKER.'plugin_settings_name'] = array('fieldset');
                    }
                    foreach ($meta as $key => $value){
                        if ($value[0]=='fieldset') { continue; } //plugins only get one fieldset
                        $metadata['plugin'.CM_KEYMARKER.$plugin.CM_KEYMARKER.$key] = $value;
                    }
                }
            }

            // the same for the active template
            if (@file_exists(tpl_incdir().$file)){
                $meta = array();
                @include(tpl_incdir().$file);
                @include(tpl_incdir().$class);
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
         * Load default settings for plugins and templates
         *
         * @param string $tpl name of active template
         * @return array default settings
         */
        function get_plugintpl_default($tpl){
            $file    = '/conf/default.php';
            $default = array();

            foreach ($this->get_plugin_list() as $plugin) {
                $plugin_dir = plugin_directory($plugin);
                if (@file_exists(DOKU_PLUGIN.$plugin_dir.$file)){
                    $conf = $this->_read_config(DOKU_PLUGIN.$plugin_dir.$file);
                    foreach ($conf as $key => $value){
                        $default['plugin'.CM_KEYMARKER.$plugin.CM_KEYMARKER.$key] = $value;
                    }
                }
            }

            // the same for the active template
            if (@file_exists(tpl_incdir().$file)){
                $conf = $this->_read_config(tpl_incdir().$file);
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
        var $_default = null;
        var $_local = null;
        var $_protected = null;

        var $_pattern = '';
        var $_error = false;            // only used by those classes which error check
        var $_input = null;             // only used by those classes which error check
        var $_caution = null;           // used by any setting to provide an alert along with the setting
                                        // valid alerts, 'warning', 'danger', 'security'
                                        // images matching the alerts are in the plugin's images directory

        static protected $_validCautions = array('warning','danger','security');

        /**
         * @param string $key
         * @param array|null $params array with metadata of setting
         */
        public function setting($key, $params=null) {
            $this->_key = $key;

            if (is_array($params)) {
                foreach($params as $property => $value) {
                    $this->$property = $value;
                }
            }
        }

        /**
         * Receives current values for the setting $key
         *
         * @param mixed $default   default setting value
         * @param mixed $local     local setting value
         * @param mixed $protected protected setting value
         */
        public function initialize($default, $local, $protected) {
            if (isset($default)) $this->_default = $default;
            if (isset($local)) $this->_local = $local;
            if (isset($protected)) $this->_protected = $protected;
        }

        /**
         * update changed setting with user provided value $input
         * - if changed value fails error check, save it to $this->_input (to allow echoing later)
         * - if changed value passes error check, set $this->_local to the new value
         *
         * @param  mixed   $input   the new value
         * @return boolean          true if changed, false otherwise (incl. on error)
         */
        public function update($input) {
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
         * Build html for label and input of setting
         *
         * @param DokuWiki_Plugin $plugin object of config plugin
         * @param bool            $echo   true: show inputted value, when error occurred, otherwise the stored setting
         * @return array(string $label_html, string $input_html)
         */
        public function html(&$plugin, $echo=false) {
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
            $value = formText($value);

            $label = '<label for="config___'.$key.'">'.$this->prompt($plugin).'</label>';
            $input = '<textarea rows="3" cols="40" id="config___'.$key.'" name="config['.$key.']" class="edit" '.$disable.'>'.$value.'</textarea>';
            return array($label,$input);
        }

        /**
         * Generate string to save setting value to file according to $fmt
         */
        public function out($var, $fmt='php') {

            if ($this->is_protected()) return '';
            if (is_null($this->_local) || ($this->_default == $this->_local)) return '';

            $out = '';

            if ($fmt=='php') {
                $tr = array("\\" => '\\\\', "'" => '\\\'');

                $out =  '$'.$var."['".$this->_out_key()."'] = '".strtr( cleanText($this->_local), $tr)."';\n";
            }

            return $out;
        }

        /**
         * Returns the localized prompt
         *
         * @param DokuWiki_Plugin $plugin object of config plugin
         * @return string text
         */
        public function prompt(&$plugin) {
            $prompt = $plugin->getLang($this->_key);
            if (!$prompt) $prompt = htmlspecialchars(str_replace(array('____','_'),' ',$this->_key));
            return $prompt;
        }

        /**
         * Is setting protected
         *
         * @return bool
         */
        public function is_protected() { return !is_null($this->_protected); }

        /**
         * Is setting the default?
         *
         * @return bool
         */
        public function is_default() { return !$this->is_protected() && is_null($this->_local); }

        /**
         * Has an error?
         *
         * @return bool
         */
        public function error() { return $this->_error; }

        /**
         * Returns caution
         *
         * @return bool|string caution string, otherwise false for invalid caution
         */
        public function caution() {
            if (!empty($this->_caution)) {
                if (!in_array($this->_caution, setting::$_validCautions)) {
                    trigger_error('Invalid caution string ('.$this->_caution.') in metadata for setting "'.$this->_key.'"', E_USER_WARNING);
                    return false;
                }
                return $this->_caution;
            }
            // compatibility with previous cautionList
            // TODO: check if any plugins use; remove
            if (!empty($this->_cautionList[$this->_key])) {
                $this->_caution = $this->_cautionList[$this->_key];
                unset($this->_cautionList);

                return $this->caution();
            }
            return false;
        }

        /**
         * Returns setting key, eventually with referer to config: namespace at dokuwiki.org
         *
         * @param bool $pretty create nice key
         * @param bool $url    provide url to config: namespace
         * @return string key
         */
        public function _out_key($pretty=false,$url=false) {
            if($pretty){
                $out = str_replace(CM_KEYMARKER,"»",$this->_key);
                if ($url && !strstr($out,'»')) {//provide no urls for plugins, etc.
                    if ($out == 'start') //one exception
                        return '<a href="http://www.dokuwiki.org/config:startpage">'.$out.'</a>';
                    else
                        return '<a href="http://www.dokuwiki.org/config:'.$out.'">'.$out.'</a>';
                }
                return $out;
            }else{
                return str_replace(CM_KEYMARKER,"']['",$this->_key);
            }
        }
    }
}


if (!class_exists('setting_array')) {
    class setting_array extends setting {

        /**
         * Create an array from a string
         *
         * @param $string
         * @return array
         */
        protected function _from_string($string){
            $array = explode(',', $string);
            $array = array_map('trim', $array);
            $array = array_filter($array);
            $array = array_unique($array);
            return $array;
        }

        /**
         * Create a string from an array
         *
         * @param $array
         * @return string
         */
        protected function _from_array($array){
            return join(', ', (array) $array);
        }

        /**
         * update setting with user provided value $input
         * if value fails error check, save it
         *
         * @param string $input
         * @return bool true if changed, false otherwise (incl. on error)
         */
        function update($input) {
            if (is_null($input)) return false;
            if ($this->is_protected()) return false;

            $input = $this->_from_string($input);

            $value = is_null($this->_local) ? $this->_default : $this->_local;
            if ($value == $input) return false;

            foreach($input as $item){
                if ($this->_pattern && !preg_match($this->_pattern,$item)) {
                    $this->_error = true;
                    $this->_input = $input;
                    return false;
                }
            }

            $this->_local = $input;
            return true;
        }

        protected function _escape($string) {
            $tr = array("\\" => '\\\\', "'" => '\\\'');
            return "'".strtr( cleanText($string), $tr)."'";
        }

        /**
         * generate string to save setting value to file according to $fmt
         */
        function out($var, $fmt='php') {

            if ($this->is_protected()) return '';
            if (is_null($this->_local) || ($this->_default == $this->_local)) return '';

            $out = '';

            if ($fmt=='php') {
                $vals = array_map(array($this, '_escape'), $this->_local);
                $out =  '$'.$var."['".$this->_out_key()."'] = array(".join(', ',$vals).");\n";
            }

            return $out;
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
            $value = htmlspecialchars($this->_from_array($value));

            $label = '<label for="config___'.$key.'">'.$this->prompt($plugin).'</label>';
            $input = '<input id="config___'.$key.'" name="config['.$key.']" type="text" class="edit" value="'.$value.'" '.$disable.'/>';
            return array($label,$input);
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

        var $_code = 'plain';  // mechanism to be used to obscure passwords

        function update($input) {
            if ($this->is_protected()) return false;
            if (!$input) return false;

            if ($this->_pattern && !preg_match($this->_pattern,$input)) {
                $this->_error = true;
                $this->_input = $input;
                return false;
            }

            $this->_local = conf_encodeString($input,$this->_code);
            return true;
        }

        function html(&$plugin, $echo=false) {

            $value = '';
            $disable = $this->is_protected() ? 'disabled="disabled"' : '';

            $key = htmlspecialchars($this->_key);

            $label = '<label for="config___'.$key.'">'.$this->prompt($plugin).'</label>';
            $input = '<input id="config___'.$key.'" name="config['.$key.']" autocomplete="off" type="password" class="edit" value="" '.$disable.' />';
            return array($label,$input);
        }
    }
}

if (!class_exists('setting_email')) {

    class setting_email extends setting_string {
        var $_multiple = false;
        var $_placeholders = false;

        /**
         * update setting with user provided value $input
         * if value fails error check, save it
         *
         * @return boolean true if changed, false otherwise (incl. on error)
         */
        function update($input) {
            if (is_null($input)) return false;
            if ($this->is_protected()) return false;

            $value = is_null($this->_local) ? $this->_default : $this->_local;
            if ($value == $input) return false;
            if($input === ''){
                $this->_local = $input;
                return true;
            }
            $mail = $input;

            if($this->_placeholders){
                // replace variables with pseudo values
                $mail = str_replace('@USER@','joe',$mail);
                $mail = str_replace('@NAME@','Joe Schmoe',$mail);
                $mail = str_replace('@MAIL@','joe@example.com',$mail);
            }

            // multiple mail addresses?
            if ($this->_multiple) {
                $mails = array_filter(array_map('trim', explode(',', $mail)));
            } else {
                $mails = array($mail);
            }

            // check them all
            foreach ($mails as $mail) {
                // only check the address part
                if(preg_match('#(.*?)<(.*?)>#', $mail, $matches)){
                    $addr = $matches[2];
                }else{
                    $addr = $mail;
                }

                if (!mail_isvalid($addr)) {
                    $this->_error = true;
                    $this->_input = $input;
                    return false;
                }
            }

            $this->_local = $input;
            return true;
        }
    }
}

/**
 * @deprecated 2013-02-16
 */
if (!class_exists('setting_richemail')) {
    class setting_richemail extends setting_email {
        function update($input) {
            $this->_placeholders = true;
            return parent::update($input);
        }
    }
}


if (!class_exists('setting_numeric')) {
    class setting_numeric extends setting_string {
        // This allows for many PHP syntax errors...
        // var $_pattern = '/^[-+\/*0-9 ]*$/';
        // much more restrictive, but should eliminate syntax errors.
        var $_pattern = '/^[-+]? *[0-9]+ *(?:[-+*] *[0-9]+ *)*$/';
        var $_min = null;
        var $_max = null;

        function update($input) {
            $local = $this->_local;
            $valid = parent::update($input);
            if ($valid && !(is_null($this->_min) && is_null($this->_max))) {
                $numeric_local = (int) eval('return '.$this->_local.';');
                if ((!is_null($this->_min) && $numeric_local < $this->_min) ||
                    (!is_null($this->_max) && $numeric_local > $this->_max)) {
                    $this->_error = true;
                    $this->_input = $input;
                    $this->_local = $local;
                    $valid = false;
                }
            }
            return $valid;
        }

        function out($var, $fmt='php') {

            if ($this->is_protected()) return '';
            if (is_null($this->_local) || ($this->_default == $this->_local)) return '';

            $out = '';

            if ($fmt=='php') {
                $local = $this->_local === '' ? "''" : $this->_local;
                $out .=  '$'.$var."['".$this->_out_key()."'] = ".$local.";\n";
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

        function html(&$plugin, $echo = false) {
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

        function html(&$plugin, $echo = false) {
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
                if (!$option && isset($this->lang[$this->_key.'_o_'.$choice])) $option = $this->lang[$this->_key.'_o_'.$choice];
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

                    $file = (is_link($this->_dir.$entry)) ? readlink($this->_dir.$entry) : $this->_dir.$entry;
                    if (is_dir($file)) $list[] = $entry;
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

            $label = '<label>'.$this->prompt($plugin).'</label>';
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

if (!class_exists('setting_regex')){
    class setting_regex extends setting_string {

        var $_delimiter = '/';    // regex delimiter to be used in testing input
        var $_pregflags = 'ui';   // regex pattern modifiers to be used in testing input

        /**
         * update changed setting with user provided value $input
         * - if changed value fails error check, save it to $this->_input (to allow echoing later)
         * - if changed value passes error check, set $this->_local to the new value
         *
         * @param  mixed   $input   the new value
         * @return boolean          true if changed, false otherwise (incl. on error)
         */
        function update($input) {

            // let parent do basic checks, value, not changed, etc.
            $local = $this->_local;
            if (!parent::update($input)) return false;
            $this->_local = $local;

            // see if the regex compiles and runs (we don't check for effectiveness)
            $regex = $this->_delimiter . $input . $this->_delimiter . $this->_pregflags;
            $lastError = error_get_last();
            $ok = @preg_match($regex,'testdata');
            if (preg_last_error() != PREG_NO_ERROR || error_get_last() != $lastError) {
                $this->_input = $input;
                $this->_error = true;
                return false;
            }

            $this->_local = $input;
            return true;
        }
    }
}
