<?php
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');

class action_plugin_importoldchangelog extends DokuWiki_Action_Plugin {

	function getInfo(){
		return array(
			'author' => 'Ben Coburn',
			'email'  => 'btcoburn@silicodon.net',
			'date'   => '2006-10-29',
			'name'   => 'Import Old Changelog',
			'desc'   => 'Imports and converts the single file changelog '.
                        'from the 2006-03-09 release to the new format. '.
                        'Also reconstructs missing changelog data from  '.
                        'old revisions kept in the attic.',
            'url'    => 'http://wiki.splitbrain.org/wiki:changelog'
			);
	}

	function register(&$controller) {
        $controller->register_hook('TEMPORARY_CHANGELOG_UPGRADE_EVENT', 'BEFORE', $this, 'run_import');
	}

    function importOldLog($line, &$logs) {
        global $lang;
        /*
        // Note: old log line format
        //$info['date']  = $tmp[0];
        //$info['ip']    = $tmp[1];
        //$info['id']    = $tmp[2];
        //$info['user']  = $tmp[3];
        //$info['sum']   = $tmp[4];
        */
        $oldline = @explode("\t", $line);
        if ($oldline!==false && count($oldline)>1) {
            // trim summary
            $tmp = substr($oldline[4], 0, 1);
            $wasMinor = ($tmp==='*');
            if ($tmp==='*' || $tmp===' ') {
                $sum = rtrim(substr($oldline[4], 1), "\n");
            } else {
                // no is_minor prefix in summary
                $sum = rtrim($oldline[4], "\n");
            }
            // guess line type
            $type = DOKU_CHANGE_TYPE_EDIT;
            if ($wasMinor) { $type = DOKU_CHANGE_TYPE_MINOR_EDIT; }
            if ($sum===$lang['created']) { $type = DOKU_CHANGE_TYPE_CREATE; }
            if ($sum===$lang['deleted']) { $type = DOKU_CHANGE_TYPE_DELETE; }
            // build new log line
            $tmp = array();
            $tmp['date']  = (int)$oldline[0];
            $tmp['ip']    = $oldline[1];
            $tmp['type']  = $type;
            $tmp['id']    = $oldline[2];
            $tmp['user']  = $oldline[3];
            $tmp['sum']   = $sum;
            $tmp['extra'] = '';
            // order line by id
            if (!isset($logs[$tmp['id']])) { $logs[$tmp['id']] = array(); }
            $logs[$tmp['id']][$tmp['date']] = $tmp;
        }
    }

    function importFromAttic(&$logs) {
        global $conf, $lang;
        $base = $conf['olddir'];
        $stack = array('');
        $context = ''; // namespace
        while (count($stack)>0){
            $context = array_pop($stack);
            $dir = dir($base.'/'.str_replace(':', '/', $context));

            while (($file = $dir->read()) !== false) {
                if ($file==='.' || $file==='..') { continue; }
                $matches = array();
                if (preg_match('/([^.]*)\.([^.]*)\..*/', $file, $matches)===1) {
                    $id = (($context=='')?'':$context.':').$matches[1];
                    $date = $matches[2];

                    // check if page & revision are already logged
                    if (!isset($logs[$id])) { $logs[$id] = array(); }
                    if (!isset($logs[$id][$date])) {
                        $tmp = array();
                        $tmp['date']  = (int)$date;
                        $tmp['ip']    = '127.0.0.1'; // original ip lost
                        $tmp['type']  = DOKU_CHANGE_TYPE_EDIT;
                        $tmp['id']    = $id;
                        $tmp['user']  = ''; // original user lost
                        $tmp['sum']   = '('.$lang['restored'].')'; // original summary lost
                        $tmp['extra'] = '';
                        $logs[$id][$date] = $tmp;
                    }

                } else if (is_dir($dir->path.'/'.$file)) {
                    array_push($stack, (($context=='')?'':$context.':').$file);
                }

            }

            $dir->close();
        }

    }

    function savePerPageChanges($id, &$changes, &$recent) {
        ksort($changes); // ensure correct order of changes from attic
        foreach ($changes as $date => $tmp) {
            $changes[$date] = implode("\t", $tmp)."\n";
            $recent[$date] = &$changes[$date];
        }
        io_saveFile(metaFN($id, '.changes'), implode('', $changes));
    }

    function savePerPageMetadata($id, &$changes) {
        global $auth;
        ksort($changes); // order by date
        $meta = array();
        // Run through history and populate the metadata array
        foreach ($changes as $date => $tmp) {
            $user = $tmp['user'];
            if ($tmp['type'] === DOKU_CHANGE_TYPE_CREATE) {
                $meta['date']['created'] = $tmp['date'];
                if ($user) {
                    $userinfo = $auth->getUserData($user);
                    $meta['creator'] = $userinfo['name'];
                }
            } else if ($tmp['type'] === DOKU_CHANGE_TYPE_EDIT) {
                $meta['date']['modified'] = $tmp['date'];
                if ($user) {
                    $userinfo = $auth->getUserData($user);
                    $meta['contributor'][$user] = $userinfo['name'];
                }
            }
        }
        p_set_metadata($id, $meta, true);
    }

    function resetTimer() {
        // Add 5 minutes to the script execution timer...
        // This should be much more than needed.
        @set_time_limit(5*60);
        // Note: Has no effect in safe-mode!
    }

    function run_import(&$event, $args) {
        global $conf;
        register_shutdown_function('importoldchangelog_plugin_shutdown');
        touch($conf['changelog'].'_importing'); // changelog importing lock
        io_saveFile($conf['changelog'], ''); // pre-create changelog
        io_lock($conf['changelog']);  // hold onto the lock
        // load old changelog
        $this->resetTimer();
        $log = array();
        $oldlog = file($conf['changelog_old']);
        foreach ($oldlog as $line) {
            $this->importOldLog($line, $log);
        }
        unset($oldlog); // free memory
        // look in the attic for unlogged revisions
        $this->resetTimer();
        $this->importFromAttic($log);
        // save per-page changelogs
        $this->resetTimer();
        $recent = array();
        foreach ($log as $id => $page) {
            $this->savePerPageMetadata($id, $page);
            $this->savePerPageChanges($id, $page, $recent);
        }
        // save recent changes cache
        $this->resetTimer();
        ksort($recent); // ensure correct order of recent changes
        io_unlock($conf['changelog']); // hand off the lock to io_saveFile
        io_saveFile($conf['changelog'], implode('', $recent));
        @unlink($conf['changelog'].'_importing'); // changelog importing unlock
    }

}

function importoldchangelog_plugin_shutdown() {
    global $conf;
    $path = array();
    $path['changelog'] = $conf['changelog'];
    $path['importing'] = $conf['changelog'].'_importing';
    $path['failed']    = $conf['changelog'].'_failed';
    $path['import_ok'] = $conf['changelog'].'_import_ok';
    io_unlock($path['changelog']); // guarantee unlocking
    if (@file_exists($path['importing'])) {
        // import did not finish
        rename($path['importing'], $path['failed']) or trigger_error('Importing changelog failed.', E_USER_WARNING);
        @unlink($path['import_ok']);
    } else {
        // import successful
        touch($path['import_ok']);
        @unlink($path['failed']);
        plugin_disable('importoldchangelog'); // only needs to run once
    }
}


