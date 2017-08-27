<?php
/**
 * Options for the authpdo plugin
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

$meta['debug']              = array('onoff', '_caution' => 'security');
$meta['dsn']                = array('string', '_caution' => 'danger');
$meta['user']               = array('string', '_caution' => 'danger');
$meta['pass']               = array('password', '_caution' => 'danger', '_code' => 'base64');
$meta['select-user']        = array('', '_caution' => 'danger');
$meta['check-pass']         = array('', '_caution' => 'danger');
$meta['select-user-groups'] = array('', '_caution' => 'danger');
$meta['select-groups']      = array('', '_caution' => 'danger');
$meta['insert-user']        = array('', '_caution' => 'danger');
$meta['delete-user']        = array('', '_caution' => 'danger');
$meta['list-users']         = array('', '_caution' => 'danger');
$meta['count-users']        = array('', '_caution' => 'danger');
$meta['update-user-info']   = array('', '_caution' => 'danger');
$meta['update-user-login']  = array('', '_caution' => 'danger');
$meta['update-user-pass']   = array('', '_caution' => 'danger');
$meta['insert-group']       = array('', '_caution' => 'danger');
$meta['join-group']         = array('', '_caution' => 'danger');
$meta['leave-group']        = array('', '_caution' => 'danger');


