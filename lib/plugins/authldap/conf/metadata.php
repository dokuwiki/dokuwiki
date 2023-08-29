<?php
$meta['server']      = ['string', '_caution' => 'danger'];
$meta['port']        = ['numeric', '_caution' => 'danger'];
$meta['usertree']    = ['string', '_caution' => 'danger'];
$meta['grouptree']   = ['string', '_caution' => 'danger'];
$meta['userfilter']  = ['string', '_caution' => 'danger'];
$meta['groupfilter'] = ['string', '_caution' => 'danger'];
$meta['version']     = ['numeric', '_caution' => 'danger'];
$meta['starttls']    = ['onoff', '_caution' => 'danger'];
$meta['referrals']   = ['multichoice', '_choices' => [-1, 0, 1], '_caution' => 'danger'];
$meta['deref']       = ['multichoice', '_choices' => [0, 1, 2, 3], '_caution' => 'danger'];
$meta['binddn']      = ['string', '_caution' => 'danger'];
$meta['bindpw']      = ['password', '_caution' => 'danger', '_code'=>'base64'];
$meta['attributes']  = ['array'];
//$meta['mapping']['name']  unsupported in config manager
//$meta['mapping']['grps']  unsupported in config manager
$meta['userscope']   = ['multichoice', '_choices' => ['sub', 'one', 'base'], '_caution' => 'danger'];
$meta['groupscope']  = ['multichoice', '_choices' => ['sub', 'one', 'base'], '_caution' => 'danger'];
$meta['userkey']     = ['string', '_caution' => 'danger'];
$meta['groupkey']    = ['string', '_caution' => 'danger'];
$meta['debug']       = ['onoff', '_caution' => 'security'];
$meta['modPass']     = ['onoff'];
