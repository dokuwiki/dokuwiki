<?php

$meta['account_suffix']     = array('string');
$meta['base_dn']            = array('string');
$meta['domain_controllers'] = array('string');
$meta['sso']                = array('onoff');
$meta['admin_username']        = array('string');
$meta['admin_password']        = array('password');
$meta['real_primarygroup']  = array('onoff');
$meta['use_ssl']            = array('onoff');
$meta['use_tls']            = array('onoff');
$meta['debug']              = array('onoff');
$meta['expirywarn']         = array('numeric', '_min'=>0);
$meta['additional']         = array('string');
