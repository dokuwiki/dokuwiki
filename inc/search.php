<?php
/**
 * DokuWiki search functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
  require_once(DOKU_INC.'inc/common.php');

/**
 * recurse direcory
 *
 * This function recurses into a given base directory
 * and calls the supplied function for each file and directory
 *
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
    if ($func($data,$base,$dir,'d',$lvl,$opts)){
      search($data,$base,$func,$opts,$dir,$lvl+1);
    }
  }
  //now handle the files
  foreach($files as $file){
    $func($data,$base,$file,'f',$lvl,$opts);
  }
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
 * one deeper nested)
 */

/**
 * Build the browsable index of pages
 *
 * $opts['ns'] is the current namespace
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function search_index(&$data,$base,$file,$type,$lvl,$opts){
  $return = true;

  $item = array();

  if($type == 'd' && !preg_match('#^'.$file.'(/|$)#','/'.$opts['ns'])){
    //add but don't recurse
    $return = false;
  }elseif($type == 'f' && !preg_match('#\.txt$#',$file)){
    //don't add
    return false;
  }

  //check ACL
  $id = pathID($file);
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
  $info['id']   = pathID($file);

  //check ACL for namespace (we have no ACL for mediafiles)
  if(auth_quickaclcheck(getNS($info['id']).':*') < AUTH_READ){
    return false;
  }

  $info['file'] = basename($file);
  $info['size'] = filesize($base.'/'.$file);
  if(preg_match("/\.(jpe?g|gif|png)$/",$file)){
    $info['isimg'] = true;
    $info['info']  = getimagesize($base.'/'.$file);
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
  if(preg_match('#\.txt$#',$file)){
    //check ACL
    $id = pathID($file);
    if(auth_quickaclcheck($id) < AUTH_READ){
      return false;
    }
    $data[]['id'] = $id;;
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
  if(!preg_match('#\.txt$#',$file)) return true;

  //simple stringmatching 
  if(strpos($file,$opts['query']) !== false){
    //check ACL
    $id = pathID($file);
    if(auth_quickaclcheck($id) < AUTH_READ){
      return false;
    } 
    $data[]['id'] = $id;
  }

  return true;
}

/**
 * Search for backlinks to a given page
 *
 * $opts['ns']    namespace of the page
 * $opts['name']  name of the page without namespace
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function search_backlinks(&$data,$base,$file,$type,$lvl,$opts){
  //we do nothing with directories
  if($type == 'd') return true;;
  //only search txt files
  if(!preg_match('#\.txt$#',$file)) return true;;

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
 */
function search_fulltext(&$data,$base,$file,$type,$lvl,$opts){
  //we do nothing with directories
  if($type == 'd') return true;;
  //only search txt files
  if(!preg_match('#\.txt$#',$file)) return true;;

  //check ACL
  $id = pathID($file);
  if(auth_quickaclcheck($id) < AUTH_READ){
    return false;
  }

  //get text
  $text = io_readfile($base.'/'.$file);
  //lowercase text (u modifier does not help with case)
  $lctext = utf8_strtolower($text);

  //create regexp from queries  
  $qpreg = preg_split('/\s+/',preg_quote($opts['query'],'#'));
  $qpreg = '('.join('|',$qpreg).')';

  //do the fulltext search
  $matches = array();
  if($cnt = preg_match_all('#'.$qpreg.'#usi',$lctext,$matches)){
    //this is not the best way for snippet generation but the fastest I could find
    //split query and only use the first token
    $q = preg_split('/\s+/',$opts['query'],2);
    $q = $q[0];
    $p = utf8_strpos($lctext,$q);
    $f = $p - 100;
    $l = utf8_strlen($q) + 200;
    if($f < 0) $f = 0;
    $snippet = '<span class="search_sep"> ... </span>'.
               htmlspecialchars(utf8_substr($text,$f,$l)).
               '<span class="search_sep"> ... </span>';
    $snippet = preg_replace('#'.$qpreg.'#si','<span class="search_hit">\\1</span>',$snippet);

    $data[] = array(
      'id'      => $id,
      'count'   => $cnt,
      'snippet' => $snippet,
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
function pathID($path){
  $id = utf8_decodeFN($path);
  $id = str_replace('/',':',$id);
  $id = preg_replace('#\.txt$#','',$id);
  $id = preg_replace('#^:+#','',$id);
  $id = preg_replace('#:+$#','',$id);
  return $id;
}


//Setup VIM: ex: et ts=2 enc=utf-8 :
