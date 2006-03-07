<?php
  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
  require_once(DOKU_INC.'inc/init.php');
  require_once(DOKU_INC.'inc/common.php');
  require_once(DOKU_INC.'inc/lang/en/lang.php');
  require_once(DOKU_INC.'inc/lang/'.$conf['lang'].'/lang.php');
  require_once(DOKU_INC.'inc/html.php');
  require_once(DOKU_INC.'inc/JpegMeta.php');
  require_once(DOKU_INC.'inc/search.php');
  require_once(DOKU_INC.'inc/template.php');
  require_once(DOKU_INC.'inc/auth.php');
  //close sesseion
  session_write_close();

  //get namespace to display (either direct or from deletion order)
  if($_REQUEST['delete']){
    $DEL = cleanID($_REQUEST['delete']);
    $NS  = getNS($DEL);
  }elseif($_REQUEST['edit']){
    $IMG = cleanID($_REQUEST['edit']);
    $SRC = mediaFN($IMG);
    $NS  = getNS($IMG);
  }else{
    $NS = $_REQUEST['ns'];
    $NS = cleanID($NS);
  }

  //check upload permissions
  $AUTH = auth_quickaclcheck("$NS:*");
  if($AUTH >= AUTH_UPLOAD){
    $UPLOADOK = true;
    //create the given namespace (just for beautification)
    $mdir = $conf['mediadir'].'/'.utf8_encodeFN(str_replace(':','/',$NS));
    io_makeFileDir("$mdir/xxx");
  }else{
    $UPLOADOK = false;
  }

  //handle deletion
  $mediareferences = array();
  if($DEL && $AUTH >= AUTH_DELETE){
    if($conf['refcheck']){
      search($mediareferences,$conf['datadir'],'search_reference',array('query' => $DEL));
    }
    if(!count($mediareferences)){
      media_delete($DEL);
    }elseif(!$conf['refshow']){
      msg(str_replace('%s',noNS($DEL),$lang['mediainuse']),0);
    }
  }

  //handle metadatasaving
  if($UPLOADOK && $SRC && $_REQUEST['save']){
    media_metasave($SRC,$_REQUEST['meta']);
  }

  //handle upload
  if($_FILES['upload']['tmp_name'] && $UPLOADOK){
    media_upload($NS,$AUTH);
  }

  //start output and load template
  header('Content-Type: text/html; charset=utf-8');
  if($conf['refshow'] && count($mediareferences)){
    include(template('mediaref.php'));
  }elseif($IMG){
    include(template('mediaedit.php'));
  }else{
    include(template('media.php'));
  }

/**********************************************/

/**
 * Deletes mediafiles - Auth is not handled here!
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function media_delete($delid){
  global $lang;

  $file = mediaFN($delid);
  if(@unlink($file)){
    msg(str_replace('%s',noNS($delid),$lang['deletesucc']),1);
    io_sweepNS($delid,'mediadir');
    return true;
  }
  //something went wrong
  msg(str_replace('%s',$file,$lang['deletefail']),-1);
  return false;
}

/**
 * Handles Mediafile uploads
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function media_upload($NS,$AUTH){
  require_once(DOKU_INC.'inc/confutils.php');
  global $lang;
  global $conf;

  // get file
  $id   = $_POST['id'];
  $file = $_FILES['upload'];
  // get id
  if(empty($id)) $id = $file['name'];
  $id   = cleanID($NS.':'.$id);
  // get filename
  $fn   = mediaFN($id);

  // get filetype regexp
  $types = array_keys(getMimeTypes());
  $types = array_map(create_function('$q','return preg_quote($q,"/");'),$types);
  $regex = join('|',$types);

  // because a temp file was created already
  if(preg_match('/\.('.$regex.')$/i',$fn)){
    //check for overwrite
    if(@file_exists($fn) && (!$_POST['ow'] || $AUTH < AUTH_DELETE)){
      msg($lang['uploadexist'],0);
      return false;
    }
    // prepare directory
    io_makeFileDir($fn);
    if(move_uploaded_file($file['tmp_name'], $fn)) {
      // set the correct permission here
      if($conf['fperm']) chmod($fn, $conf['fperm']);
      msg($lang['uploadsucc'],1);
      return true;
    }else{
      msg($lang['uploadfail'],-1);
    }
  }else{
    msg($lang['uploadwrong'],-1);
  }
  return false;
}

/**
 * Userfunction for html_buildlist
 *
 * Prints available media namespaces
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function media_html_list_namespaces($item){
  $ret  = '';
  $ret .= '<a href="'.DOKU_BASE.'lib/exe/media.php?ns='.idfilter($item['id']).'" class="idx_dir">';
  $pos = strrpos($item['id'], ':');
  $ret .= substr($item['id'], $pos > 0 ? $pos + 1 : 0);
  $ret .= '</a>';
  return $ret;
}

/**
 * Saves image meta data
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function media_metasave($src,$data){
	global $lang;

  $meta = new JpegMeta($src);
  $meta->_parseAll();

	foreach($data as $key => $val){
		$val=trim($val);
		if(empty($val)){
			$meta->deleteField($key);
		}else{
			$meta->setField($key,$val);
		}
	}

	if($meta->save()){
		msg($lang['metasaveok'],1);
	}else{
		msg($lang['metasaveerr'],-1);
	}
}

?>
