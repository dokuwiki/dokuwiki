<?php
/**
 * $ID is pagename, reads matching lines from $AUTH_ACL,
 * also reads acls from namespace
 * returns multi-array with key=pagename and value=array(user, acl)
 *
 * @author	Frank Schubert <frank@schokilade.de>
 */
function get_acl_config($ID){
  global $AUTH_ACL;
  
  $acl_config=array();
  
  // match exact name
  $matches = preg_grep('/^'.$ID.'\s+.*/',$AUTH_ACL);
  if(count($matches)){
    foreach($matches as $match){
      $match = preg_replace('/#.*$/','',$match); //ignore comments
      $acl   = preg_split('/\s+/',$match);
      //0 is pagename, 1 is user, 2 is acl
      $acl_config["$acl[0]"][]=array($acl[1],$acl[2]);
    }
  }
  
  $specific_found=array();
  // match ns
  if(($ID=getNS($ID)) !== false){
    $matches = preg_grep('/^'.$ID.':\*\s+.*/',$AUTH_ACL);
    if(count($matches)){
      foreach($matches as $match){
	$match = preg_replace('/#.*$/','',$match); //ignore comments
	$acl   = preg_split('/\s+/',$match);
	//0 is pagename, 1 is user, 2 is acl
	$acl_config["$acl[0]"][]=array($acl[1],$acl[2]);
	$specific_found[]=$acl[1];
      }
    }
  }
  
  //include *-config
  $matches = preg_grep('/^\*\s+.*/',$AUTH_ACL);
  if(count($matches)){
    foreach($matches as $match){
      $match = preg_replace('/#.*$/','',$match); //ignore comments
      $acl   = preg_split('/\s+/',$match);
      // only include * for this user if not already found in ns
      if(!in_array($acl[1], $specific_found)){
        //0 is pagename, 1 is user, 2 is acl
        $acl_config["$acl[0]"][]=array($acl[1],$acl[2]);
      }
    }
  }
  
  //sort
  //FIXME: better sort algo: first sort by key, then sort by first value
  krsort($acl_config, SORT_STRING);
  
  return($acl_config);
}

/**
 * adds new acl-entry to conf/acl.auth
 *
 * @author	Frank Schubert <frank@schokilade.de>
 */
function acl_admin_add($acl_scope, $acl_user, $acl_level){
  if($acl_scope === '' || $acl_user === '' || $acl_level === '') { return false; }
  
  $acl_config = join("",file('conf/acl.auth'));
  
  // max level for pagenames is 2
  if(strpos("*", $acl_scope) === false) {
    if($acl_level > 2) { $acl_level = 2; }
  }
  
  $new_acl = "$acl_scope\t$acl_user\t$acl_level\n";
  
  $new_config = $acl_config.$new_acl;
  
  return io_saveFile("conf/acl.auth", $new_config);
}

/**
 * remove acl-entry from conf/acl.auth
 *
 * @author	Frank Schubert <frank@schokilade.de>
 */
function acl_admin_del($acl_scope, $acl_user, $acl_level){
  if($acl_scope === '' || $acl_user === '' || $acl_level === '') { return false; }
  
  $acl_pattern = preg_quote($acl_scope)."\s+".$acl_user."\s+".$acl_level."\n";
  
  $acl_config = file('conf/acl.auth');
  
  // save all non!-matching
  $new_config = preg_grep("/$acl_pattern/", $acl_config, PREG_GREP_INVERT);
  
  return io_saveFile("conf/acl.auth", join("",$new_config));
}

/**
 * change existing acl entries
 *
 * @author	Frank Schubert <frank@schokilade.de>
 */
function acl_admin_change($acl_scope, $acl_user, $acl_level, $acl_checkbox){
  
  $new_level = 0;
  if(is_array($acl_checkbox)) {
    foreach($acl_checkbox as $acl_num => $value){
      if( ($value == "on") && 
          ($acl_num > $new_level)) {
	$new_level = $acl_num;
      }
    }
  }
  
  acl_admin_del($acl_scope, $acl_user, $acl_level);
  acl_admin_add($acl_scope, $acl_user, $new_level);
}
?>
