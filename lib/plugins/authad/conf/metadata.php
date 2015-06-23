<?php

$meta['account_suffix']     = array('string','_caution' => 'danger');
$meta['base_dn']            = array('string','_caution' => 'danger');
$meta['domain_controllers'] = array('string','_caution' => 'danger');
$meta['sso']                = array('onoff','_caution' => 'danger');
$meta['sso_charset']        = array('string','_caution' => 'danger');
$meta['admin_username']     = array('string','_caution' => 'danger');
$meta['admin_password']     = array('password','_caution' => 'danger');
$meta['real_primarygroup']  = array('onoff','_caution' => 'danger');
$meta['use_ssl']            = array('onoff','_caution' => 'danger');
$meta['use_tls']            = array('onoff','_caution' => 'danger');
$meta['debug']              = array('onoff','_caution' => 'security');
$meta['expirywarn']         = array('numeric', '_min'=>0,'_caution' => 'danger');
$meta['additional']         = array('string','_caution' => 'danger');
$meta['admin_features'] 	= array('string','_caution' => 'danger');
$meta['doku_signin']      	= array('onoff','_caution' => 'danger');
$meta['user_caching']      	= array('onoff');
$meta['user_caching_ttl']   = array('numeric', '_min'=>0,'_caution' => 'danger');
$meta['user_map']         	= array('string');
