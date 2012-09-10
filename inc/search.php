<?php
/**
 * DokuWiki search functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) die('meh.');

/**
 * recurse direcory
 *
 * This function recurses into a given base directory
 * and calls the supplied function for each file and directory
 *
 * @param   array ref $data The results of the search are stored here
 * @param   string    $base Where to start the search
 * @param   callback  $func Callback (function name or arayy with object,method)
 * @param   string    $dir  Current directory beyond $base
 * @param   int       $lvl  Recursion Level
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function search(&$data,$base,$func,$opts,$dir='',$lvl=1,$sort=false){
    $dirs   = array();
    $files  = array();
    $filepaths = array();

    //read in directories and files
    $dh = @opendir($base.'/'.$dir);
    if(!$dh) return;
    while(($file = readdir($dh)) !== false){
        if(preg_match('/^[\._]/',$file)) continue; //skip hidden files and upper dirs
        if(is_dir($base.'/'.$dir.'/'.$file)){
            $dirs[] = $dir.'/'.$file;
            continue;
        }
        $files[] = $dir.'/'.$file;
        $filepaths[] = $base.'/'.$dir.'/'.$file;
    }
    closedir($dh);
    if ($sort == 'date') {
        @array_multisort(array_map('filemtime', $filepaths), SORT_NUMERIC, SORT_DESC, $files);
    } else {
        sort($files);
    }
    sort($dirs);

    //give directories to userfunction then recurse
    foreach($dirs as $dir){
        if (call_user_func_array($func, array(&$data,$base,$dir,'d',$lvl,$opts))){
            search($data,$base,$func,$opts,$dir,$lvl+1);
        }
    }
    //now handle the files
    foreach($files as $file){
        call_user_func_array($func, array(&$data,$base,$file,'f',$lvl,$opts));
    }
}

/**
 * Wrapper around call_user_func_array.
 *
 * @deprecated
 */
function search_callback($func,&$data,$base,$file,$type,$lvl,$opts){
    return call_user_func_array($func, array(&$data,$base,$file,$type,$lvl,$opts));
}

/**
 * The following functions are userfunctions to use with the search
 * function above. This function is called for every found file or
 * directory. When a directory is given to the function it has to
 * decide if this directory should be traversed (true) or not (false)
 * The function has to accept the following parameters:
 *
 * &$data - Reference to the result data structure
 * $base  - Base usually $conf['datadir']
 * $file  - current file or directory relative to $base
 * $type  - Type either 'd' for directory or 'f' for file
 * $lvl   - Current recursion depht
 * $opts  - option array as given to search()
 *
 * return values for files are ignored
 *
 * All functions should check the ACL for document READ rights
 * namespaces (directories) are NOT checked (when sneaky_index is 0) as this
 * would break the recursion (You can have an nonreadable dir over a readable
 * one deeper nested) also make sure to check the file type (for example
 * in case of lockfiles).
 */

/**
 * Searches for pages beginning with the given query
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function search_qsearch(&$data,$base,$file,$type,$lvl,$opts){
    $opts = array(
            'idmatch'   => '(^|:)'.preg_quote($opts['query'],'/').'/',
            'listfiles' => true,
            'pagesonly' => true,
            );
    return search_universal($data,$base,$file,$type,$lvl,$opts);
}

/**
 * Build the browsable index of pages
 *
 * $opts['ns'] is the currently viewed namespace
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function search_index(&$data,$base,$file,$type,$lvl,$opts){
    global $conf;
    $opts = array(
        'pagesonly' => true,
        'listdirs' => true,
        'listfiles' => !$opts['nofiles'],
        'sneakyacl' => $conf['sneaky_index'],
        // Hacky, should rather use recmatch
        'depth' => preg_match('#^'.$file.'(/|$)#','/'.$opts['ns']) ? 0 : -1
    );

    return search_universal($data, $base, $file, $type, $lvl, $opts);
}

/**
 * List all namespaces
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function search_namespaces(&$data,$base,$file,$type,$lvl,$opts){
    $opts = array(
            'listdirs' => true,
            );
    return search_universal($data,$base,$file,$type,$lvl,$opts);
}

/**
 * List all mediafiles in a namespace
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function search_media(&$data,$base,$file,$type,$lvl,$opts){

    //we do nothing with directories
    if($type == 'd') {
        if(!$opts['depth']) return true; // recurse forever
        $depth = substr_count($file,'/');
        if($depth >= $opts['depth']) return false; // depth reached
        return true;
    }

    $info         = array();
    $info['id']   = pathID($file,true);
    if($info['id'] != cleanID($info['id'])){
        if($opts['showmsg'])
            msg(hsc($info['id']).' is not a valid file name for DokuWiki - skipped',-1);
        return false; // skip non-valid files
    }

    //check ACL for namespace (we have no ACL for mediafiles)
    $info['perm'] = auth_quickaclcheck(getNS($info['id']).':*');
    if(!$opts['skipacl'] && $info['perm'] < AUTH_READ){
        return false;
    }

    //check pattern filter
    if($opts['pattern'] && !@preg_match($opts['pattern'], $info['id'])){
        return false;
    }

    $info['file']     = basename($file);
    $info['size']     = filesize($base.'/'.$file);
    $info['mtime']    = filemtime($base.'/'.$file);
    $info['writable'] = is_writable($base.'/'.$file);
    if(preg_match("/\.(jpe?g|gif|png)$/",$file)){
        $info['isimg'] = true;
        $info['meta']  = new JpegMeta($base.'/'.$file);
    }else{
        $info['isimg'] = false;
    }
    if($opts['hash']){
        $info['hash'] = md5(io_readFile(mediaFN($info['id']),false));
    }

    $data[] = $info;

    return false;
}

/**
 * This function just lists documents (for RSS namespace export)
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function search_list(&$data,$base,$file,$type,$lvl,$opts){
    //we do nothing with directories
    if($type == 'd') return false;
    //only search txt files
    if(substr($file,-4) == '.txt'){
        //check ACL
        $id = pathID($file);
        if(auth_quickaclcheck($id) < AUTH_READ){
            return false;
        }
        $data[]['id'] = $id;
    }
    return false;
}

/**
 * Quicksearch for searching matching pagenames
 *
 * $opts['query'] is the search query
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function search_pagename(&$data,$base,$file,$type,$lvl,$opts){
    //we do nothing with directories
    if($type == 'd') return true;
    //only search txt files
    if(substr($file,-4) != '.txt') return true;

    //simple stringmatching
    if (!empty($opts['query'])){
        if(strpos($file,$opts['query']) !== false){
            //check ACL
            $id = pathID($file);
            if(auth_quickaclcheck($id) < AUTH_READ){
                return false;
            }
            $data[]['id'] = $id;
        }
    }
    return true;
}

/**
 * Just lists all documents
 *
 * $opts['depth']   recursion level, 0 for all
 * $opts['hash']    do md5 sum of content?
 * $opts['skipacl'] list everything regardless of ACL
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function search_allpages(&$data,$base,$file,$type,$lvl,$opts){
    //we do nothing with directories
    if($type == 'd'){
        if(!$opts['depth']) return true; // recurse forever
        $parts = explode('/',ltrim($file,'/'));
        if(count($parts) == $opts['depth']) return false; // depth reached
        return true;
    }

    //only search txt files
    if(substr($file,-4) != '.txt') return true;

    $item['id']   = pathID($file);
    if(!$opts['skipacl'] && auth_quickaclcheck($item['id']) < AUTH_READ){
        return false;
    }

    $item['rev']   = filemtime($base.'/'.$file);
    $item['mtime'] = $item['rev'];
    $item['size']  = filesize($base.'/'.$file);
    if($opts['hash']){
        $item['hash'] = md5(trim(rawWiki($item['id'])));
    }

    $data[] = $item;
    return true;
}

/**
 * Search for backlinks to a given page
 *
 * $opts['ns']    namespace of the page
 * $opts['name']  name of the page without namespace
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @deprecated Replaced by ft_backlinks()
 */
function search_backlinks(&$data,$base,$file,$type,$lvl,$opts){
    //we do nothing with directories
    if($type == 'd') return true;
    //only search txt files
    if(substr($file,-4) != '.txt') return true;

    //absolute search id
    $sid = cleanID($opts['ns'].':'.$opts['name']);

    //current id and namespace
    $cid = pathID($file);
    $cns = getNS($cid);

    //check ACL
    if(auth_quickaclcheck($cid) < AUTH_READ){
        return false;
    }

    //fetch instructions
    $instructions = p_cached_instructions($base.$file,true);
    if(is_null($instructions)) return false;

    global $conf;
    //check all links for match
    foreach($instructions as $ins){
        if($ins[0] == 'internallink' || ($conf['camelcase'] && $ins[0] == 'camelcaselink') ){
            $mid = $ins[1][0];
            resolve_pageid($cns,$mid,$exists); //exists is not used
            if($mid == $sid){
                //we have a match - finish
                $data[]['id'] = $cid;
                break;
            }
        }
    }

    return false;
}

/**
 * Fulltextsearch
 *
 * $opts['query'] is the search query
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @deprecated - fulltext indexer is used instead
 */
function search_fulltext(&$data,$base,$file,$type,$lvl,$opts){
    //we do nothing with directories
    if($type == 'd') return true;
    //only search txt files
    if(substr($file,-4) != '.txt') return true;

    //check ACL
    $id = pathID($file);
    if(auth_quickaclcheck($id) < AUTH_READ){
        return false;
    }

    //create regexp from queries
    $poswords = array();
    $negwords = array();
    $qpreg = preg_split('/\s+/',$opts['query']);

    foreach($qpreg as $word){
        switch(substr($word,0,1)){
            case '-':
                if(strlen($word) > 1){  // catch single '-'
                    array_push($negwords,preg_quote(substr($word,1),'#'));
                }
                break;
            case '+':
                if(strlen($word) > 1){  // catch single '+'
                    array_push($poswords,preg_quote(substr($word,1),'#'));
                }
                break;
            default:
                array_push($poswords,preg_quote($word,'#'));
                break;
        }
    }

    // a search without any posword is useless
    if (!count($poswords)) return true;

    $reg  = '^(?=.*?'.join(')(?=.*?',$poswords).')';
            $reg .= count($negwords) ? '((?!'.join('|',$negwords).').)*$' : '.*$';
            search_regex($data,$base,$file,$reg,$poswords);
            return true;
            }

            /**
             * Reference search
             * This fuction searches for existing references to a given media file
             * and returns an array with the found pages. It doesn't pay any
             * attention to ACL permissions to find every reference. The caller
             * must check if the user has the appropriate rights to see the found
             * page and eventually have to prevent the result from displaying.
             *
             * @param array  $data Reference to the result data structure
             * @param string $base Base usually $conf['datadir']
             * @param string $file current file or directory relative to $base
             * @param char   $type Type either 'd' for directory or 'f' for file
             * @param int    $lvl  Current recursion depht
             * @param mixed  $opts option array as given to search()
             *
             * $opts['query'] is the demanded media file name
             *
             * @author  Andreas Gohr <andi@splitbrain.org>
             * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
             */
function search_reference(&$data,$base,$file,$type,$lvl,$opts){
    global $conf;

    //we do nothing with directories
    if($type == 'd') return true;

    //only search txt files
    if(substr($file,-4) != '.txt') return true;

    //we finish after 'cnt' references found. The return value
    //'false' will skip subdirectories to speed search up.
    $cnt = $conf['refshow'] > 0 ? $conf['refshow'] : 1;
    if(count($data) >= $cnt) return false;

    $reg = '\{\{ *\:?'.$opts['query'].' *(\|.*)?\}\}';
    search_regex($data,$base,$file,$reg,array($opts['query']));
    return true;
}

/* ------------- helper functions below -------------- */

/**
 * fulltext search helper
 * searches a text file with a given regular expression
 * no ACL checks are performed. This have to be done by
 * the caller if necessary.
 *
 * @param array  $data  reference to array for results
 * @param string $base  base directory
 * @param string $file  file name to search in
 * @param string $reg   regular expression to search for
 * @param array  $words words that should be marked in the results
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @author  Matthias Grimm <matthiasgrimm@users.sourceforge.net>
 *
 * @deprecated - fulltext indexer is used instead
 */
function search_regex(&$data,$base,$file,$reg,$words){

    //get text
    $text = io_readfile($base.'/'.$file);
    //lowercase text (u modifier does not help with case)
    $lctext = utf8_strtolower($text);

    //do the fulltext search
    $matches = array();
    if($cnt = preg_match_all('#'.$reg.'#usi',$lctext,$matches)){
        //this is not the best way for snippet generation but the fastest I could find
        $q = $words[0];  //use first word for snippet creation
        $p = utf8_strpos($lctext,$q);
        $f = $p - 100;
        $l = utf8_strlen($q) + 200;
        if($f < 0) $f = 0;
        $snippet = '<span class="search_sep"> ... </span>'.
            htmlspecialchars(utf8_substr($text,$f,$l)).
            '<span class="search_sep"> ... </span>';
        $mark    = '('.join('|', $words).')';
        $snippet = preg_replace('#'.$mark.'#si','<strong class="search_hit">\\1</strong>',$snippet);

        $data[] = array(
                'id'       => pathID($file),
                'count'    => preg_match_all('#'.$mark.'#usi',$lctext,$matches),
                'poswords' => join(' ',$words),
                'snippet'  => $snippet,
                );
    }

    return true;
}


/**
 * fulltext sort
 *
 * Callback sort function for use with usort to sort the data
 * structure created by search_fulltext. Sorts descending by count
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function sort_search_fulltext($a,$b){
    if($a['count'] > $b['count']){
        return -1;
    }elseif($a['count'] < $b['count']){
        return 1;
    }else{
        return strcmp($a['id'],$b['id']);
    }
}

/**
 * translates a document path to an ID
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @todo    move to pageutils
 */
function pathID($path,$keeptxt=false){
    $id = utf8_decodeFN($path);
    $id = str_replace('/',':',$id);
    if(!$keeptxt) $id = preg_replace('#\.txt$#','',$id);
    $id = trim($id, ':');
    return $id;
}


/**
 * This is a very universal callback for the search() function, replacing
 * many of the former individual functions at the cost of a more complex
 * setup.
 *
 * How the function behaves, depends on the options passed in the $opts
 * array, where the following settings can be used.
 *
 * depth      int     recursion depth. 0 for unlimited
 * keeptxt    bool    keep .txt extension for IDs
 * listfiles  bool    include files in listing
 * listdirs   bool    include namespaces in listing
 * pagesonly  bool    restrict files to pages
 * skipacl    bool    do not check for READ permission
 * sneakyacl  bool    don't recurse into nonreadable dirs
 * hash       bool    create MD5 hash for files
 * meta       bool    return file metadata
 * filematch  string  match files against this regexp
 * idmatch    string  match full ID against this regexp
 * dirmatch   string  match directory against this regexp when adding
 * nsmatch    string  match namespace against this regexp when adding
 * recmatch   string  match directory against this regexp when recursing
 * showmsg    bool    warn about non-ID files
 * showhidden bool    show hidden files too
 * firsthead  bool    return first heading for pages
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 */
function search_universal(&$data,$base,$file,$type,$lvl,$opts){
    $item   = array();
    $return = true;

    // get ID and check if it is a valid one
    $item['id'] = pathID($file,($type == 'd' || $opts['keeptxt']));
    if($item['id'] != cleanID($item['id'])){
        if($opts['showmsg'])
            msg(hsc($item['id']).' is not a valid file name for DokuWiki - skipped',-1);
        return false; // skip non-valid files
    }
    $item['ns']  = getNS($item['id']);

    if($type == 'd') {
        // decide if to recursion into this directory is wanted
        if(!$opts['depth']){
            $return = true; // recurse forever
        }else{
            $depth = substr_count($file,'/');
            if($depth >= $opts['depth']){
                $return = false; // depth reached
            }else{
                $return = true;
            }
        }
        if($return && !preg_match('/'.$opts['recmatch'].'/',$file)){
            $return = false; // doesn't match
        }
    }

    // check ACL
    if(!$opts['skipacl']){
        if($type == 'd'){
            $item['perm'] = auth_quickaclcheck($item['id'].':*');
        }else{
            $item['perm'] = auth_quickaclcheck($item['id']); //FIXME check namespace for media files
        }
    }else{
        $item['perm'] = AUTH_DELETE;
    }

    // are we done here maybe?
    if($type == 'd'){
        if(!$opts['listdirs']) return $return;
        if(!$opts['skipacl'] && $opts['sneakyacl'] && $item['perm'] < AUTH_READ) return false; //neither list nor recurse
        if($opts['dirmatch'] && !preg_match('/'.$opts['dirmatch'].'/',$file)) return $return;
        if($opts['nsmatch'] && !preg_match('/'.$opts['nsmatch'].'/',$item['ns'])) return $return;
    }else{
        if(!$opts['listfiles']) return $return;
        if(!$opts['skipacl'] && $item['perm'] < AUTH_READ) return $return;
        if($opts['pagesonly'] && (substr($file,-4) != '.txt')) return $return;
        if(!$opts['showhidden'] && isHiddenPage($item['id'])) return $return;
        if($opts['filematch'] && !preg_match('/'.$opts['filematch'].'/',$file)) return $return;
        if($opts['idmatch'] && !preg_match('/'.$opts['idmatch'].'/',$item['id'])) return $return;
    }

    // still here? prepare the item
    $item['type']  = $type;
    $item['level'] = $lvl;
    $item['open']  = $return;

    if($opts['meta']){
        $item['file']       = basename($file);
        $item['size']       = filesize($base.'/'.$file);
        $item['mtime']      = filemtime($base.'/'.$file);
        $item['rev']        = $item['mtime'];
        $item['writable']   = is_writable($base.'/'.$file);
        $item['executable'] = is_executable($base.'/'.$file);
    }

    if($type == 'f'){
        if($opts['hash']) $item['hash'] = md5(io_readFile($base.'/'.$file,false));
        if($opts['firsthead']) $item['title'] = p_get_first_heading($item['id'],METADATA_DONT_RENDER);
    }

    // finally add the item
    $data[] = $item;
    return $return;
}

//Setup VIM: ex: et ts=4 :
