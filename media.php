<?php
  if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__)).'/');
  require_once(DOKU_INC.'inc/init.php');
  require_once(DOKU_INC.'inc/common.php');
  require_once(DOKU_INC.'lang/en/lang.php');
  require_once(DOKU_INC.'lang/'.$conf['lang'].'/lang.php');
  require_once(DOKU_INC.'inc/html.php');
  require_once(DOKU_INC.'inc/search.php');
  require_once(DOKU_INC.'inc/template.php');
  require_once(DOKU_INC.'inc/auth.php');

  header('Content-Type: text/html; charset='.$lang['encoding']);

  $NS = $_REQUEST['ns'];
  $NS = cleanID($NS);

  //check upload permissions
  if(auth_quickaclcheck("$NS:*") >= AUTH_UPLOAD){
    $UPLOADOK = true;
    //create the given namespace (just for beautification)
    $mdir = $conf['mediadir'].'/'.utf8_encodeFN(str_replace(':','/',$NS));
    io_makeFileDir("$mdir/xxx");
  }else{
    $UPLOADOK = false;
  }

  if($_FILES['upload']['tmp_name'] && $UPLOADOK){
    media_upload($NS);
  }

  //start output and load template
  header('Content-Type: text/html; charset=utf-8');
  include(DOKU_INC.'tpl/'.$conf['template'].'/media.php');

  //restore old umask
  umask($conf['oldumask']);

/**********************************************/

/**
 * Handles Mediafile uploads
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function media_upload($NS){
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

  // we set the umask here but this doesn't really help
  // because a temp file was created already
  umask($conf['umask']);
  if(preg_match('/\.('.$regex.')$/i',$fn)){
  	// prepare directory
  	io_makeFileDir($fn);
    if (move_uploaded_file($file['tmp_name'], $fn)) {
			// set the correct permission here
			chmod($fn, 0777 - $conf['umask']);
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
  $ret .= '<a href="'.DOKU_BASE.'media.php?ns='.idfilter($item['id']).'" class="idx_dir">';
  $ret .= $item['id'];
  $ret .= '</a>';
  return $ret;
}

?>
