<?php
$meta['server']      = array('string');
$meta['port']        = array('numeric');
$meta['usertree']    = array('string');
$meta['grouptree']   = array('string');
$meta['userfilter']  = array('string');
$meta['groupfilter'] = array('string');
$meta['version']     = array('numeric');
$meta['starttls']    = array('onoff');
$meta['referrals']   = array('onoff');
$meta['deref']       = array('multichoice','_choices' => array(0,1,2,3));
$meta['binddn']      = array('string');
$meta['bindpw']      = array('password');
//$meta['mapping']['name']  unsupported in config manager
//$meta['mapping']['grps']  unsupported in config manager
$meta['userscope']   = array('multichoice','_choices' => array('sub','one','base'));
$meta['groupscope']  = array('multichoice','_choices' => array('sub','one','base'));
$meta['groupkey']    = array('string');
$meta['debug']       = array('onoff');