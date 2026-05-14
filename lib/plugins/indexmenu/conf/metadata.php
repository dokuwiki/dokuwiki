<?php

/**
 * Configuration-manager metadata for indexmenu plugin
 *
 * @license:    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author:     Samuele Tognini <samuele@samuele.netsons.org>
 */

$meta['defaultoptions'] = array('string');
$meta['only_admins']   = array('onoff','_caution' => 'warning');
$meta['aclcache']      = array('multichoice', '_choices' => array('none', 'user', 'groups'));
$meta['headpage']      = array('multicheckbox', '_choices' => array(':start:', ':same:', ':inside:'));
$meta['hide_headpage'] = array('onoff');
$meta['page_index']    = array('string', '_pattern' => '#^[a-z:]*#');
$meta['empty_msg']     = array('string');
$meta['skip_index']    = array('string', '_pattern' => '/^($|\/.*\/.*$)/');
$meta['skip_file']     = array('string', '_pattern' => '/^($|\/.*\/.*$)/');
$meta['show_sort']     = array('onoff');
//$meta['themes_url']           =       array('string','_pattern' => '/^($|http:\/\/\S+$)/i');
//$meta['be_repo']          =   array('onoff');
