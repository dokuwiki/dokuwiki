<?php
/**
 * DokuWiki search functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',fullpath(dirname(__FILE__).'/../').'/');
  require_once(DOKU_INC.'inc/common.php');

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
function search(&$data,$base,$func,$opts,$dir='',$lvl=1){
  $dirs   = array();
  $files  = array();

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
  }
  closedir($dh);
  sort($files);
  sort($dirs);

  //give directories to userfunction then recurse
  foreach($dirs as $dir){
    if (search_callback($func,$data,$base,$dir,'d',$lvl,$opts)){
      search($data,$base,$func,$opts,$dir,$lvl+1);
    }
  }
  //now handle the files
  foreach($files as $file){
    search_callback($func,$data,$base,$file,'f',$lvl,$opts);
  }
}

/**
 * Used to run a user callback
 *
 * Makes sure the $data array is passed by reference (unlike when using
 * call_user_func())
 *
 * @todo If this can be generalized it may be useful elsewhere in the code
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function search_callback($func,&$data,$base,$file,$type,$lvl,$opts){
  if(is_array($func)){
    if(is_object($func[0])){
      // instanciated object
      return $func[0]->$func[1]($data,$base,$file,$type,$lvl,$opts);
    }else{
      // static call
      $f = $func[0].'::'.$func[1];
      return $f($data,$base,$file,$type,$lvl,$opts);
    }
  }
  // simple function call
  return $func($data,$base,$file,$type,$lvl,$opts);
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
 * namespaces (directories) are NOT checked as this would break
 * the recursion (You can have an nonreadable dir over a readable
 * one deeper nested) also make sure to check the file type (for example
 * in case of lockfiles).
 */

/**
 * Searches for pages beginning with the given query
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function search_qsearch(&$data,$base,$file,$type,$lvl,$opts){
  $item = array();

  if($type == 'd'){
    return false; //no handling yet
  }

  //only search txt files
  if(substr($file,-4) != '.txt') return false;

  //get id
  $id = pathID($file);

  //check if it matches the query
  if(!preg_match('/^'.preg_quote($opts['query'],'/').'/u',$id)){
    return false;
  }

  //check ACL
  if(auth_quickaclcheck($id) < AUTH_READ){
    return false;
  }

  $data[]=array( 'id'    => $id,
                 'type'  => $type,
                 'level' => 1,
                 'open'  => true);
  return true;
}

/**
 * Build the browsable index of pages
 *
 * $opts['ns'] is the current namespace
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function search_index(&$data,$base,$file,$type,$lvl,$opts){
  global $conf;
  $return = true;

  $item = array();

  if($type == 'd' && !preg_match('#^'.$file.'(/|$)#','/'.$opts['ns'])){
    //add but don't recurse
    $return = false;
  }elseif($type == 'f' && ($opts['nofiles'] || substr($file,-4) != '.txt')){
    //don't add
    return false;
  }

  $id = pathID($file);

  if($type=='d' && $conf['sneaky_index'] && auth_quickaclcheck($id.':') < AUTH_READ){
    return false;
  }

  //check hidden
  if(isHiddenPage($id)){
    return false;
  }

  //check ACL
  if($type=='f' && auth_quickaclcheck($id) < AUTH_READ){
    return false;
  }

  $data[]=array( 'id'    => $id,
                 'type'  => $type,
                 'level' => $lvl,
                 'open'  => $return );
  return $return;
}

/**
 * List all namespaces
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function search_namespaces(&$data,$base,$file,$type,$lvl,$opts){
  if($type == 'f') return true; //nothing to do on files

  $id = pathID($file);
  $data[]=array( 'id'    => $id,
                 'type'  => $type,
                 'level' => $lvl );
  return true;
}

/**
 * List all mediafiles in a namespace
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function search_media(&$data,$base,$file,$type,$lvl,$opts){
  //we do nothing with directories
  if($type == 'd') return false;

  $info         = array();
  $info['id']   = pathID($file,true);
  if($info['id'] != cleanID($info['id'])){
    if($opts['showmsg'])
      msg(hsc($info['id']).' is not a valid file name for DokuWiki - skipped',-1);
    return false; // skip non-valid files
  }

  //check ACL for namespace (we have no ACL for mediafiles)
  if(auth_quickaclcheck(getNS($info['id']).':*') < AUTH_READ){
    return false;
  }

  $info['file'] = basename($file);
  $info['size'] = filesize($base.'/'.$file);
  $info['mtime'] = filemtime($base.'/'.$file);
  $info['writable'] = is_writable($base.'/'.$file);
  if(preg_match("/\.(jpe?g|gif|png)$/",$file)){
    $info['isimg'] = true;
    require_once(DOKU_INC.'inc/JpegMeta.php');
    $info['meta']  = new JpegMeta($base.'/'.$file);
  }else{
    $info['isimg'] = false;
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
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function search_allpages(&$data,$base,$file,$type,$lvl,$opts){
  //we do nothing with directories
  if($type == 'd') return true;
  //only search txt files
  if(substr($file,-4) != '.txt') return true;

  $data[]['id'] = pathID($file);
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
  require_once(DOKU_INC.'inc/parserutils.php');
  $instructions = p_cached_instructions($base.$file,true);
  if(is_null($instructions)) return false;

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
  $id = preg_replace('#^:+#','',$id);
  $id = preg_replace('#:+$#','',$id);
  return $id;
}


//Setup VIM: ex: et ts=2 enc=utf-8 :
