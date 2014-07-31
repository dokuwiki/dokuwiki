<?php
/**
 * Utilities for collecting data from config files
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Harry Fuecks <hfuecks@gmail.com>
 */


/**
 * Returns the (known) extension and mimetype of a given filename
 *
 * If $knownonly is true (the default), then only known extensions
 * are returned.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function mimetype($file, $knownonly=true){
    $mtypes = getMimeTypes();     // known mimetypes
    $ext    = strrpos($file, '.');
    if ($ext === false) {
        return array(false, false, false);
    }
    $ext = strtolower(substr($file, $ext + 1));
    if (!isset($mtypes[$ext])){
        if ($knownonly) {
            return array(false, false, false);
        } else {
            return array($ext, 'application/octet-stream', true);
        }
    }
    if($mtypes[$ext][0] == '!'){
        return array($ext, substr($mtypes[$ext],1), true);
    }else{
        return array($ext, $mtypes[$ext], false);
    }
}

/**
 * returns a hash of mimetypes
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function getMimeTypes() {
    static $mime = null;
    if ( !$mime ) {
        $mime = retrieveConfig('mime','confToHash');
    }
    return $mime;
}

/**
 * returns a hash of acronyms
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
function getAcronyms() {
    static $acronyms = null;
    if ( !$acronyms ) {
        $acronyms = retrieveConfig('acronyms','confToHash');
    }
    return $acronyms;
}

/**
 * returns a hash of smileys
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
function getSmileys() {
    static $smileys = null;
    if ( !$smileys ) {
        $smileys = retrieveConfig('smileys','confToHash');
    }
    return $smileys;
}

/**
 * returns a hash of entities
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
function getEntities() {
    static $entities = null;
    if ( !$entities ) {
        $entities = retrieveConfig('entities','confToHash');
    }
    return $entities;
}

/**
 * returns a hash of interwikilinks
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
function getInterwiki() {
    static $wikis = null;
    if ( !$wikis ) {
        $wikis = retrieveConfig('interwiki','confToHash',array(true));
    }
    //add sepecial case 'this'
    $wikis['this'] = DOKU_URL.'{NAME}';
    return $wikis;
}

/**
 * returns array of wordblock patterns
 *
 */
function getWordblocks() {
    static $wordblocks = null;
    if ( !$wordblocks ) {
        $wordblocks = retrieveConfig('wordblock','file');
    }
    return $wordblocks;
}

/**
 * Gets the list of configured schemes
 *
 * @return array the schemes
 */
function getSchemes() {
    static $schemes = null;
    if ( !$schemes ) {
        $schemes = retrieveConfig('scheme','file');
    }
    $schemes = array_map('trim', $schemes);
    $schemes = preg_replace('/^#.*/', '', $schemes);
    $schemes = array_filter($schemes);
    return $schemes;
}

/**
 * Builds a hash from an array of lines
 *
 * If $lower is set to true all hash keys are converted to
 * lower case.
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Gina Haeussge <gina@foosel.net>
 */
function linesToHash($lines, $lower=false) {
    $conf = array();
    // remove BOM
    if (isset($lines[0]) && substr($lines[0],0,3) == pack('CCC',0xef,0xbb,0xbf))
        $lines[0] = substr($lines[0],3);
    foreach ( $lines as $line ) {
        //ignore comments (except escaped ones)
        $line = preg_replace('/(?<![&\\\\])#.*$/','',$line);
        $line = str_replace('\\#','#',$line);
        $line = trim($line);
        if(empty($line)) continue;
        $line = preg_split('/\s+/',$line,2);
        // Build the associative array
        if($lower){
            $conf[strtolower($line[0])] = $line[1];
        }else{
            $conf[$line[0]] = $line[1];
        }
    }

    return $conf;
}

/**
 * Builds a hash from a configfile
 *
 * If $lower is set to true all hash keys are converted to
 * lower case.
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Gina Haeussge <gina@foosel.net>
 */
function confToHash($file,$lower=false) {
    $conf = array();
    $lines = @file( $file );
    if ( !$lines ) return $conf;

    return linesToHash($lines, $lower);
}

/**
 * Retrieve the requested configuration information
 *
 * @author Chris Smith <chris@jalakai.co.uk>
 *
 * @param  string   $type     the configuration settings to be read, must correspond to a key/array in $config_cascade
 * @param  callback $fn       the function used to process the configuration file into an array
 * @param  array    $params   optional additional params to pass to the callback
 * @return array    configuration values
 */
function retrieveConfig($type,$fn,$params=null) {
    global $config_cascade;

    if(!is_array($params)) $params = array();

    $combined = array();
    if (!is_array($config_cascade[$type])) trigger_error('Missing config cascade for "'.$type.'"',E_USER_WARNING);
    foreach (array('default','local','protected') as $config_group) {
        if (empty($config_cascade[$type][$config_group])) continue;
        foreach ($config_cascade[$type][$config_group] as $file) {
            if (@file_exists($file)) {
                $config = call_user_func_array($fn,array_merge(array($file),$params));
                $combined = array_merge($combined, $config);
            }
        }
    }

    return $combined;
}

/**
 * Include the requested configuration information
 *
 * @author Chris Smith <chris@jalakai.co.uk>
 *
 * @param  string   $type     the configuration settings to be read, must correspond to a key/array in $config_cascade
 * @return array              list of files, default before local before protected
 */
function getConfigFiles($type) {
    global $config_cascade;
    $files = array();

    if (!is_array($config_cascade[$type])) trigger_error('Missing config cascade for "'.$type.'"',E_USER_WARNING);
    foreach (array('default','local','protected') as $config_group) {
        if (empty($config_cascade[$type][$config_group])) continue;
        $files = array_merge($files, $config_cascade[$type][$config_group]);
    }

    return $files;
}

/**
 * check if the given action was disabled in config
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @param string $action
 * @returns boolean true if enabled, false if disabled
 */
function actionOK($action){
    static $disabled = null;
    if(is_null($disabled) || defined('SIMPLE_TEST')){
        global $conf;
        /** @var DokuWiki_Auth_Plugin $auth */
        global $auth;

        // prepare disabled actions array and handle legacy options
        $disabled = explode(',',$conf['disableactions']);
        $disabled = array_map('trim',$disabled);
        if((isset($conf['openregister']) && !$conf['openregister']) || is_null($auth) || !$auth->canDo('addUser')) {
            $disabled[] = 'register';
        }
        if((isset($conf['resendpasswd']) && !$conf['resendpasswd']) || is_null($auth) || !$auth->canDo('modPass')) {
            $disabled[] = 'resendpwd';
        }
        if((isset($conf['subscribers']) && !$conf['subscribers']) || is_null($auth)) {
            $disabled[] = 'subscribe';
        }
        if (is_null($auth) || !$auth->canDo('Profile')) {
            $disabled[] = 'profile';
        }
        if (is_null($auth) || !$auth->canDo('delUser')) {
            $disabled[] = 'profile_delete';
        }
        if (is_null($auth)) {
            $disabled[] = 'login';
        }
        if (is_null($auth) || !$auth->canDo('logout')) {
            $disabled[] = 'logout';
        }
        $disabled = array_unique($disabled);
    }

    return !in_array($action,$disabled);
}

/**
 * check if headings should be used as link text for the specified link type
 *
 * @author Chris Smith <chris@jalakai.co.uk>
 *
 * @param   string  $linktype   'content'|'navigation', content applies to links in wiki text
 *                                                      navigation applies to all other links
 * @return  boolean             true if headings should be used for $linktype, false otherwise
 */
function useHeading($linktype) {
    static $useHeading = null;

    if (is_null($useHeading)) {
        global $conf;

        if (!empty($conf['useheading'])) {
            switch ($conf['useheading']) {
                case 'content':
                    $useHeading['content'] = true;
                    break;

                case 'navigation':
                    $useHeading['navigation'] = true;
                    break;
                default:
                    $useHeading['content'] = true;
                    $useHeading['navigation'] = true;
            }
        } else {
            $useHeading = array();
        }
    }

    return (!empty($useHeading[$linktype]));
}

/**
 * obscure config data so information isn't plain text
 *
 * @param string       $str     data to be encoded
 * @param string       $code    encoding method, values: plain, base64, uuencode.
 * @return string               the encoded value
 */
function conf_encodeString($str,$code) {
    switch ($code) {
        case 'base64'   : return '<b>'.base64_encode($str);
        case 'uuencode' : return '<u>'.convert_uuencode($str);
        case 'plain':
        default:
                          return $str;
    }
}
/**
 * return obscured data as plain text
 *
 * @param  string      $str   encoded data
 * @return string             plain text
 */
function conf_decodeString($str) {
    switch (substr($str,0,3)) {
        case '<b>' : return base64_decode(substr($str,3));
        case '<u>' : return convert_uudecode(substr($str,3));
        default:  // not encode (or unknown)
                     return $str;
    }
}
//Setup VIM: ex: et ts=4 :
