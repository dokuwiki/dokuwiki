<?php
/**
 * Utilities for handling pagenames
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @todo       Combine similar functions like {wiki,media,meta}FN()
 */

/**
 * Fetch the an ID from request
 *
 * Uses either standard $_REQUEST variable or extracts it from
 * the full request URI when userewrite is set to 2
 *
 * For $param='id' $conf['start'] is returned if no id was found.
 * If the second parameter is true (default) the ID is cleaned.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function getID($param='id',$clean=true){
    global $conf;

    $id = isset($_REQUEST[$param]) ? $_REQUEST[$param] : null;

    //construct page id from request URI
    if(empty($id) && $conf['userewrite'] == 2){
        $request = $_SERVER['REQUEST_URI'];
        $script = '';

        //get the script URL
        if($conf['basedir']){
            $relpath = '';
            if($param != 'id') {
                $relpath = 'lib/exe/';
            }
            $script = $conf['basedir'].$relpath.basename($_SERVER['SCRIPT_FILENAME']);

        }elseif($_SERVER['PATH_INFO']){
            $request = $_SERVER['PATH_INFO'];
        }elseif($_SERVER['SCRIPT_NAME']){
            $script = $_SERVER['SCRIPT_NAME'];
        }elseif($_SERVER['DOCUMENT_ROOT'] && $_SERVER['SCRIPT_FILENAME']){
            $script = preg_replace ('/^'.preg_quote($_SERVER['DOCUMENT_ROOT'],'/').'/','',
                    $_SERVER['SCRIPT_FILENAME']);
            $script = '/'.$script;
        }

        //clean script and request (fixes a windows problem)
        $script  = preg_replace('/\/\/+/','/',$script);
        $request = preg_replace('/\/\/+/','/',$request);

        //remove script URL and Querystring to gain the id
        if(preg_match('/^'.preg_quote($script,'/').'(.*)/',$request, $match)){
            $id = preg_replace ('/\?.*/','',$match[1]);
        }
        $id = urldecode($id);
        //strip leading slashes
        $id = preg_replace('!^/+!','',$id);
    }

    // Namespace autolinking from URL
    if(substr($id,-1) == ':' || ($conf['useslash'] && substr($id,-1) == '/')){
        if(page_exists($id.$conf['start'])){
            // start page inside namespace
            $id = $id.$conf['start'];
        }elseif(page_exists($id.noNS(cleanID($id)))){
            // page named like the NS inside the NS
            $id = $id.noNS(cleanID($id));
        }elseif(page_exists($id)){
            // page like namespace exists
            $id = substr($id,0,-1);
        }else{
            // fall back to default
            $id = $id.$conf['start'];
        }
        send_redirect(wl($id,'',true));
    }

    if($clean) $id = cleanID($id);
    if(empty($id) && $param=='id') $id = $conf['start'];

    return $id;
}

/**
 * Remove unwanted chars from ID
 *
 * Cleans a given ID to only use allowed characters. Accented characters are
 * converted to unaccented ones
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @param  string  $raw_id    The pageid to clean
 * @param  boolean $ascii     Force ASCII
 * @param  boolean $media     Allow leading or trailing _ for media files
 */
function cleanID($raw_id,$ascii=false,$media=false){
    global $conf;
    static $sepcharpat = null;

    global $cache_cleanid;
    $cache = & $cache_cleanid;

    // check if it's already in the memory cache
    if (isset($cache[(string)$raw_id])) {
        return $cache[(string)$raw_id];
    }

    $sepchar = $conf['sepchar'];
    if($sepcharpat == null) // build string only once to save clock cycles
        $sepcharpat = '#\\'.$sepchar.'+#';

    $id = trim((string)$raw_id);
    $id = utf8_strtolower($id);

    //alternative namespace seperator
    $id = strtr($id,';',':');
    if($conf['useslash']){
        $id = strtr($id,'/',':');
    }else{
        $id = strtr($id,'/',$sepchar);
    }

    if($conf['deaccent'] == 2 || $ascii) $id = utf8_romanize($id);
    if($conf['deaccent'] || $ascii) $id = utf8_deaccent($id,-1);

    //remove specials
    $id = utf8_stripspecials($id,$sepchar,'\*');

    if($ascii) $id = utf8_strip($id);

    //clean up
    $id = preg_replace($sepcharpat,$sepchar,$id);
    $id = preg_replace('#:+#',':',$id);
    $id = ($media ? trim($id,':.-') : trim($id,':._-'));
    $id = preg_replace('#:[:\._\-]+#',':',$id);
    $id = preg_replace('#[:\._\-]+:#',':',$id);

    $cache[(string)$raw_id] = $id;
    return($id);
}

/**
 * Return namespacepart of a wiki ID
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function getNS($id){
    $pos = strrpos((string)$id,':');
    if($pos!==false){
        return substr((string)$id,0,$pos);
    }
    return false;
}

/**
 * Returns the ID without the namespace
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function noNS($id) {
    $pos = strrpos($id, ':');
    if ($pos!==false) {
        return substr($id, $pos+1);
    } else {
        return $id;
    }
}

/**
 * Returns the current namespace
 *
 * @author Nathan Fritz <fritzn@crown.edu>
 */
function curNS($id) {
    return noNS(getNS($id));
}

/**
 * Returns the ID without the namespace or current namespace for 'start' pages
 *
 * @author Nathan Fritz <fritzn@crown.edu>
 */
function noNSorNS($id) {
    global $conf;

    $p = noNS($id);
    if ($p == $conf['start'] || $p == false) {
        $p = curNS($id);
        if ($p == false) {
            return $conf['start'];
        }
    }
    return $p;
}

/**
 * Creates a XHTML valid linkid from a given headline title
 *
 * @param string  $title   The headline title
 * @param array   $check   Existing IDs (title => number)
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function sectionID($title,&$check) {
    $title = str_replace(array(':','.'),'',cleanID($title));
    $new = ltrim($title,'0123456789_-');
    if(empty($new)){
        $title = 'section'.preg_replace('/[^0-9]+/','',$title); //keep numbers from headline
    }else{
        $title = $new;
    }

    if(is_array($check)){
        // make sure tiles are unique
        if (!array_key_exists ($title,$check)) {
           $check[$title] = 0;
        } else {
           $title .= ++ $check[$title];
        }
    }

    return $title;
}


/**
 * Wiki page existence check
 *
 * parameters as for wikiFN
 *
 * @author Chris Smith <chris@jalakai.co.uk>
 */
function page_exists($id,$rev='',$clean=true) {
    return @file_exists(wikiFN($id,$rev,$clean));
}

/**
 * returns the full path to the datafile specified by ID and optional revision
 *
 * The filename is URL encoded to protect Unicode chars
 *
 * @param  $raw_id  string   id of wikipage
 * @param  $rev     string   page revision, empty string for current
 * @param  $clean   bool     flag indicating that $raw_id should be cleaned.  Only set to false
 *                           when $id is guaranteed to have been cleaned already.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function wikiFN($raw_id,$rev='',$clean=true){
    global $conf;

    global $cache_wikifn;
    $cache = & $cache_wikifn;

    if (isset($cache[$raw_id]) && isset($cache[$raw_id][$rev])) {
        return $cache[$raw_id][$rev];
    }

    $id = $raw_id;

    if ($clean) $id = cleanID($id);
    $id = str_replace(':','/',$id);
    if(empty($rev)){
        $fn = $conf['datadir'].'/'.utf8_encodeFN($id).'.txt';
    }else{
        $fn = $conf['olddir'].'/'.utf8_encodeFN($id).'.'.$rev.'.txt';
        if($conf['compression']){
            //test for extensions here, we want to read both compressions
            if (@file_exists($fn . '.gz')){
                $fn .= '.gz';
            }else if(@file_exists($fn . '.bz2')){
                $fn .= '.bz2';
            }else{
                //file doesnt exist yet, so we take the configured extension
                $fn .= '.' . $conf['compression'];
            }
        }
    }

    if (!isset($cache[$raw_id])) { $cache[$raw_id] = array(); }
    $cache[$raw_id][$rev] = $fn;
    return $fn;
}

/**
 * Returns the full path to the file for locking the page while editing.
 *
 * @author Ben Coburn <btcoburn@silicodon.net>
 */
function wikiLockFN($id) {
    global $conf;
    return $conf['lockdir'].'/'.md5(cleanID($id)).'.lock';
}


/**
 * returns the full path to the meta file specified by ID and extension
 *
 * @author Steven Danz <steven-danz@kc.rr.com>
 */
function metaFN($id,$ext){
    global $conf;
    $id = cleanID($id);
    $id = str_replace(':','/',$id);
    $fn = $conf['metadir'].'/'.utf8_encodeFN($id).$ext;
    return $fn;
}

/**
 * returns the full path to the media's meta file specified by ID and extension
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function mediaMetaFN($id,$ext){
    global $conf;
    $id = cleanID($id);
    $id = str_replace(':','/',$id);
    $fn = $conf['mediametadir'].'/'.utf8_encodeFN($id).$ext;
    return $fn;
}

/**
 * returns an array of full paths to all metafiles of a given ID
 *
 * @author Esther Brunner <esther@kaffeehaus.ch>
 * @author Michael Hamann <michael@content-space.de>
 */
function metaFiles($id){
    $basename = metaFN($id, '');
    $files    = glob($basename.'.*', GLOB_MARK);
    // filter files like foo.bar.meta when $id == 'foo'
    return    $files ? preg_grep('/^'.preg_quote($basename, '/').'\.[^.\/]*$/u', $files) : array();
}

/**
 * returns the full path to the mediafile specified by ID
 *
 * The filename is URL encoded to protect Unicode chars
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function mediaFN($id, $rev=''){
    global $conf;
    $id = cleanID($id);
    $id = str_replace(':','/',$id);
    if(empty($rev)){
        $fn = $conf['mediadir'].'/'.utf8_encodeFN($id);
    }else{
    	$ext = mimetype($id);
    	$name = substr($id,0, -1*strlen($ext[0])-1);
        $fn = $conf['mediaolddir'].'/'.utf8_encodeFN($name .'.'.( (int) $rev ).'.'.$ext[0]);
    }
    return $fn;
}

/**
 * Returns the full filepath to a localized textfile if local
 * version isn't found the english one is returned
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function localeFN($id){
    global $conf;
    $file = DOKU_CONF.'/lang/'.$conf['lang'].'/'.$id.'.txt';
    if(!@file_exists($file)){
        $file = DOKU_INC.'inc/lang/'.$conf['lang'].'/'.$id.'.txt';
        if(!@file_exists($file)){
            //fall back to english
            $file = DOKU_INC.'inc/lang/en/'.$id.'.txt';
        }
    }
    return $file;
}

/**
 * Resolve relative paths in IDs
 *
 * Do not call directly use resolve_mediaid or resolve_pageid
 * instead
 *
 * Partyly based on a cleanPath function found at
 * http://www.php.net/manual/en/function.realpath.php#57016
 *
 * @author <bart at mediawave dot nl>
 */
function resolve_id($ns,$id,$clean=true){
    global $conf;

    // some pre cleaning for useslash:
    if($conf['useslash']) $id = str_replace('/',':',$id);

    // if the id starts with a dot we need to handle the
    // relative stuff
    if($id{0} == '.'){
        // normalize initial dots without a colon
        $id = preg_replace('/^(\.+)(?=[^:\.])/','\1:',$id);
        // prepend the current namespace
        $id = $ns.':'.$id;

        // cleanup relatives
        $result = array();
        $pathA  = explode(':', $id);
        if (!$pathA[0]) $result[] = '';
        foreach ($pathA AS $key => $dir) {
            if ($dir == '..') {
                if (end($result) == '..') {
                    $result[] = '..';
                } elseif (!array_pop($result)) {
                    $result[] = '..';
                }
            } elseif ($dir && $dir != '.') {
                $result[] = $dir;
            }
        }
        if (!end($pathA)) $result[] = '';
        $id = implode(':', $result);
    }elseif($ns !== false && strpos($id,':') === false){
        //if link contains no namespace. add current namespace (if any)
        $id = $ns.':'.$id;
    }

    if($clean) $id = cleanID($id);
    return $id;
}

/**
 * Returns a full media id
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function resolve_mediaid($ns,&$page,&$exists){
    $page   = resolve_id($ns,$page);
    $file   = mediaFN($page);
    $exists = @file_exists($file);
}

/**
 * Returns a full page id
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function resolve_pageid($ns,&$page,&$exists){
    global $conf;
    global $ID;
    $exists = false;

    //empty address should point to current page
    if ($page === "") {
        $page = $ID;
    }

    //keep hashlink if exists then clean both parts
    if (strpos($page,'#')) {
        list($page,$hash) = explode('#',$page,2);
    } else {
        $hash = '';
    }
    $hash = cleanID($hash);
    $page = resolve_id($ns,$page,false); // resolve but don't clean, yet

    // get filename (calls clean itself)
    $file = wikiFN($page);

    // if ends with colon or slash we have a namespace link
    if(in_array(substr($page,-1), array(':', ';')) ||
       ($conf['useslash'] && substr($page,-1) == '/')){
        if(page_exists($page.$conf['start'])){
            // start page inside namespace
            $page = $page.$conf['start'];
            $exists = true;
        }elseif(page_exists($page.noNS(cleanID($page)))){
            // page named like the NS inside the NS
            $page = $page.noNS(cleanID($page));
            $exists = true;
        }elseif(page_exists($page)){
            // page like namespace exists
            $page = $page;
            $exists = true;
        }else{
            // fall back to default
            $page = $page.$conf['start'];
        }
    }else{
        //check alternative plural/nonplural form
        if(!@file_exists($file)){
            if( $conf['autoplural'] ){
                if(substr($page,-1) == 's'){
                    $try = substr($page,0,-1);
                }else{
                    $try = $page.'s';
                }
                if(page_exists($try)){
                    $page   = $try;
                    $exists = true;
                }
            }
        }else{
            $exists = true;
        }
    }

    // now make sure we have a clean page
    $page = cleanID($page);

    //add hash if any
    if(!empty($hash)) $page .= '#'.$hash;
}

/**
 * Returns the name of a cachefile from given data
 *
 * The needed directory is created by this function!
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 *
 * @param string $data  This data is used to create a unique md5 name
 * @param string $ext   This is appended to the filename if given
 * @return string       The filename of the cachefile
 */
function getCacheName($data,$ext=''){
    global $conf;
    $md5  = md5($data);
    $file = $conf['cachedir'].'/'.$md5{0}.'/'.$md5.$ext;
    io_makeFileDir($file);
    return $file;
}

/**
 * Checks a pageid against $conf['hidepages']
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 */
function isHiddenPage($id){
    global $conf;
    global $ACT;
    if(empty($conf['hidepages'])) return false;
    if($ACT == 'admin') return false;

    if(preg_match('/'.$conf['hidepages'].'/ui',':'.$id)){
        return true;
    }
    return false;
}

/**
 * Reverse of isHiddenPage
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 */
function isVisiblePage($id){
    return !isHiddenPage($id);
}

/**
 * Format an id for output to a user
 *
 * Namespaces are denoted by a trailing “:*”. The root namespace is
 * “*”. Output is escaped.
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */

function prettyprint_id($id) {
    if (!$id || $id === ':') {
        return '*';
    }
    if ((substr($id, -1, 1) === ':')) {
        $id .= '*';
    }
    return hsc($id);
}

/**
 * Encode a UTF-8 filename to use on any filesystem
 *
 * Uses the 'fnencode' option to determine encoding
 *
 * When the second parameter is true the string will
 * be encoded only if non ASCII characters are detected -
 * This makes it safe to run it multiple times on the
 * same string (default is true)
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    urlencode
 */
function utf8_encodeFN($file,$safe=true){
    global $conf;
    if($conf['fnencode'] == 'utf-8') return $file;

    if($safe && preg_match('#^[a-zA-Z0-9/_\-\.%]+$#',$file)){
        return $file;
    }

    if($conf['fnencode'] == 'safe'){
        return SafeFN::encode($file);
    }

    $file = urlencode($file);
    $file = str_replace('%2F','/',$file);
    return $file;
}

/**
 * Decode a filename back to UTF-8
 *
 * Uses the 'fnencode' option to determine encoding
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see    urldecode
 */
function utf8_decodeFN($file){
    global $conf;
    if($conf['fnencode'] == 'utf-8') return $file;

    if($conf['fnencode'] == 'safe'){
        return SafeFN::decode($file);
    }

    return urldecode($file);
}

