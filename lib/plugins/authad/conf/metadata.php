<?php

$meta['account_suffix']     = ['string', '_caution' => 'danger'];
$meta['base_dn']            = ['string', '_caution' => 'danger'];
$meta['domain_controllers'] = ['string', '_caution' => 'danger'];
$meta['sso']                = ['onoff', '_caution' => 'danger'];
$meta['sso_charset']        = ['string', '_caution' => 'danger'];
$meta['admin_username']     = ['string', '_caution' => 'danger'];
$meta['admin_password']     = ['password', '_caution' => 'danger', '_code' => 'base64'];
$meta['real_primarygroup']  = ['onoff', '_caution' => 'danger'];
$meta['use_ssl']            = ['onoff', '_caution' => 'danger'];
$meta['use_tls']            = ['onoff', '_caution' => 'danger'];
$meta['debug']              = ['onoff', '_caution' => 'security'];
$meta['expirywarn']         = ['numeric', '_min'=>0, '_caution' => 'danger'];
$meta['additional']         = ['string', '_caution' => 'danger'];
$meta['update_name']        = ['onoff', '_caution' => 'danger'];
$meta['update_mail']        = ['onoff', '_caution' => 'danger'];
$meta['update_pass']        = ['onoff', '_caution' => 'danger'];
$meta['recursive_groups']   = ['onoff', '_caution' => 'danger'];
