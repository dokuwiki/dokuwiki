<?php

$meta['account_suffix']     = array('string','_caution' => 'danger');
$meta['base_dn']            = array('string','_caution' => 'danger');
$meta['domain_controllers'] = array('string','_caution' => 'danger');
$meta['sso']                = array('onoff','_caution' => 'danger');
$meta['sso_charset']        = array('string','_caution' => 'danger');
$meta['admin_username']     = array('string','_caution' => 'danger');
$meta['admin_password']     = array('password','_caution' => 'danger','_code' => 'base64');
$meta['real_primarygroup']  = array('onoff','_caution' => 'danger');
$meta['use_ssl']            = array('onoff','_caution' => 'danger');
$meta['use_tls']            = array('onoff','_caution' => 'danger');
$meta['debug']              = array('onoff','_caution' => 'security');
$meta['expirywarn']         = array('numeric', '_min'=>0,'_caution' => 'danger');
$meta['additional']         = array('string','_caution' => 'danger');
$meta['update_name']        = array('onoff','_caution' => 'danger');
$meta['update_mail']        = array('onoff','_caution' => 'danger');
