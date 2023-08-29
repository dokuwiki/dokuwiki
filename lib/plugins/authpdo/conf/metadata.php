<?php
/**
 * Options for the authpdo plugin
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

$meta['debug']              = ['onoff', '_caution' => 'security'];
$meta['dsn']                = ['string', '_caution' => 'danger'];
$meta['user']               = ['string', '_caution' => 'danger'];
$meta['pass']               = ['password', '_caution' => 'danger', '_code' => 'base64'];
$meta['select-user']        = ['', '_caution' => 'danger'];
$meta['check-pass']         = ['', '_caution' => 'danger'];
$meta['select-user-groups'] = ['', '_caution' => 'danger'];
$meta['select-groups']      = ['', '_caution' => 'danger'];
$meta['insert-user']        = ['', '_caution' => 'danger'];
$meta['delete-user']        = ['', '_caution' => 'danger'];
$meta['list-users']         = ['', '_caution' => 'danger'];
$meta['count-users']        = ['', '_caution' => 'danger'];
$meta['update-user-info']   = ['', '_caution' => 'danger'];
$meta['update-user-login']  = ['', '_caution' => 'danger'];
$meta['update-user-pass']   = ['', '_caution' => 'danger'];
$meta['insert-group']       = ['', '_caution' => 'danger'];
$meta['join-group']         = ['', '_caution' => 'danger'];
$meta['leave-group']        = ['', '_caution' => 'danger'];
