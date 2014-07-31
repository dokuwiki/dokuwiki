<?php
/**
 * This file configures the enabled/disabled status of plugins, which are also protected
 * from changes by the extention manager. These settings will override any local settings.
 * It is not recommended to change this file, as it is overwritten on DokuWiki upgrades.
 */
$plugins['acl']               = 1;
$plugins['authplain']         = 1;
$plugins['extension']         = 1;
$plugins['config']            = 1;
$plugins['usermanager']       = 1;
$plugins['template:dokuwiki'] = 1; // not a plugin, but this should not be uninstalled either
