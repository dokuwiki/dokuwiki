<?php

/**
 * Handler for action check
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Check extends Doku_Action
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "check";
    }

    /**
     * Specifies the required permissions for check action.
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_READ;
    }

    /**
     * handle the check action.
     * Was check() by
     * @author Andreas Gohr <andi@splitbrain.org>
     * 
     * @global array $conf
     * @global array $INFO
     * @global array $config_cascade
     * @return string the next action
     */
    public function handle() {
        global $conf;
        global $INFO;

        if ($INFO['isadmin'] || $INFO['ismanager']){
            msg('DokuWiki version: '.getVersion(),1);
        }

        if(version_compare(phpversion(),'5.2.0','<')){
            msg('Your PHP version is too old ('.phpversion().' vs. 5.2.0+ needed)',-1);
        }else{
            msg('PHP version '.phpversion(),1);
        }

        $mem = (int) php_to_byte(ini_get('memory_limit'));
        if($mem){
            if($mem < 16777216){
                msg('PHP is limited to less than 16MB RAM ('.$mem.' bytes). Increase memory_limit in php.ini',-1);
            }elseif($mem < 20971520){
                msg('PHP is limited to less than 20MB RAM ('.$mem.' bytes), you might encounter problems with bigger pages. Increase memory_limit in php.ini',-1);
            }elseif($mem < 33554432){
                msg('PHP is limited to less than 32MB RAM ('.$mem.' bytes), but that should be enough in most cases. If not, increase memory_limit in php.ini',0);
            }else{
                msg('More than 32MB RAM ('.$mem.' bytes) available.',1);
            }
        }

        if(is_writable($conf['changelog'])){
            msg('Changelog is writable',1);
        }else{
            if (@file_exists($conf['changelog'])) {
                msg('Changelog is not writable',-1);
            }
        }

        if (isset($conf['changelog_old']) && @file_exists($conf['changelog_old'])) {
            msg('Old changelog exists', 0);
        }

        if (@file_exists($conf['changelog'].'_failed')) {
            msg('Importing old changelog failed', -1);
        } else if (@file_exists($conf['changelog'].'_importing')) {
            msg('Importing old changelog now.', 0);
        } else if (@file_exists($conf['changelog'].'_import_ok')) {
            msg('Old changelog imported', 1);
            if (!plugin_isdisabled('importoldchangelog')) {
                msg('Importoldchangelog plugin not disabled after import', -1);
            }
        }

        if(is_writable(DOKU_CONF)){
            msg('conf directory is writable',1);
        }else{
            msg('conf directory is not writable',-1);
        }

        if($conf['authtype'] == 'plain'){
            global $config_cascade;
            if(is_writable($config_cascade['plainauth.users']['default'])){
                msg('conf/users.auth.php is writable',1);
            }else{
                msg('conf/users.auth.php is not writable',0);
            }
        }

        if(function_exists('mb_strpos')){
            if(defined('UTF8_NOMBSTRING')){
                msg('mb_string extension is available but will not be used',0);
            }else{
                msg('mb_string extension is available and will be used',1);
                if(ini_get('mbstring.func_overload') != 0){
                    msg('mb_string function overloading is enabled, this will cause problems and should be disabled',-1);
                }
            }
        }else{
            msg('mb_string extension not available - PHP only replacements will be used',0);
        }

        if (!UTF8_PREGSUPPORT) {
            msg('PHP is missing UTF-8 support in Perl-Compatible Regular Expressions (PCRE)', -1);
        }
        if (!UTF8_PROPERTYSUPPORT) {
            msg('PHP is missing Unicode properties support in Perl-Compatible Regular Expressions (PCRE)', -1);
        }

        $loc = setlocale(LC_ALL, 0);
        if(!$loc){
            msg('No valid locale is set for your PHP setup. You should fix this',-1);
        }elseif(stripos($loc,'utf') === false){
            msg('Your locale <code>'.hsc($loc).'</code> seems not to be a UTF-8 locale, you should fix this if you encounter problems.',0);
        }else{
            msg('Valid locale '.hsc($loc).' found.', 1);
        }

        if($conf['allowdebug']){
            msg('Debugging support is enabled. If you don\'t need it you should set $conf[\'allowdebug\'] = 0',-1);
        }else{
            msg('Debugging support is disabled',1);
        }

        if($INFO['userinfo']['name']){
            msg('You are currently logged in as '.$_SERVER['REMOTE_USER'].' ('.$INFO['userinfo']['name'].')',0);
            msg('You are part of the groups '.join($INFO['userinfo']['grps'],', '),0);
        }else{
            msg('You are currently not logged in',0);
        }

        msg('Your current permission for this page is '.$INFO['perm'],0);

        if(is_writable($INFO['filepath'])){
            msg('The current page is writable by the webserver',0);
        }else{
            msg('The current page is not writable by the webserver',0);
        }

        if($INFO['writable']){
            msg('The current page is writable by you',0);
        }else{
            msg('The current page is not writable by you',0);
        }

        // Check for corrupted search index
        $lengths = idx_listIndexLengths();
        $index_corrupted = false;
        foreach ($lengths as $length) {
            if (count(idx_getIndex('w', $length)) != count(idx_getIndex('i', $length))) {
                $index_corrupted = true;
                break;
            }
        }

        foreach (idx_getIndex('metadata', '') as $index) {
            if (count(idx_getIndex($index.'_w', '')) != count(idx_getIndex($index.'_i', ''))) {
                $index_corrupted = true;
                break;
            }
        }

        if ($index_corrupted)
            msg('The search index is corrupted. It might produce wrong results and most
                    probably needs to be rebuilt. See
                    <a href="http://www.dokuwiki.org/faq:searchindex">faq:searchindex</a>
                    for ways to rebuild the search index.', -1);
        elseif (!empty($lengths))
            msg('The search index seems to be working', 1);
        else
            msg('The search index is empty. See
                    <a href="http://www.dokuwiki.org/faq:searchindex">faq:searchindex</a>
                    for help on how to fix the search index. If the default indexer
                    isn\'t used or the wiki is actually empty this is normal.');

        // display the original page
        return "show";
    }
}
