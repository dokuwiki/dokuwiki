<?php

$conf['server']      = '';
$conf['port']        = 389;
$conf['usertree']    = '';
$conf['grouptree']   = '';
$conf['userfilter']  = '';
$conf['groupfilter'] = '';
$conf['version']     = 2;
$conf['starttls']    = 0;
$conf['referrals']   = -1;
$conf['deref']       = 0;
$conf['binddn']      = '';
$conf['bindpw']      = '';
//$conf['mapping']['name']  unsupported in config manager
//$conf['mapping']['grps']  unsupported in config manager
$conf['userscope']  = 'sub';
$conf['groupscope'] = 'sub';
$conf['userkey']    = 'uid';
$conf['groupkey']   = 'cn';
$conf['debug']      = 0;
$conf['modPass']    = 1;
$conf['modPassPlain'] = 0;
$conf['attributes'] = array();
