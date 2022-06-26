<?php
/**
 * Changelog handling functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

/**
 * parses a changelog line into it's components
 *
 * @author Ben Coburn <btcoburn@silicodon.net>
 *
 * @param string $line changelog line
 * @return array|bool parsed line or false
 */
function parseChangelogLine($line) {
    $line = rtrim($line, "\n");
    $tmp = explode("\t", $line);
    if ($tmp!==false && count($tmp)>1) {
        $info = array();
        $info['date']  = (int)$tmp[0]; // unix timestamp
        $info['ip']    = $tmp[1]; // IPv4 address (127.0.0.1)
        $info['type']  = $tmp[2]; // log line type
        $info['id']    = $tmp[3]; // page id
        $info['user']  = $tmp[4]; // user name
        $info['sum']   = $tmp[5]; // edit summary (or action reason)
        $info['extra'] = $tmp[6]; // extra data (varies by line type)
        if(isset($tmp[7]) && $tmp[7] !== '') { //last item has line-end||
            $info['sizechange'] = (int) $tmp[7];
        } else {
            $info['sizechange'] = null;
        }
        return $info;
    } else {
        return false;
    }
}

/**
 * Add's an entry to the changelog and saves the metadata for the page
 *
 * @param int    $date      Timestamp of the change
 * @param String $id        Name of the affected page
 * @param String $type      Type of the change see DOKU_CHANGE_TYPE_*
 * @param String $summary   Summary of the change
 * @param mixed  $extra     In case of a revert the revision (timestmp) of the reverted page
 * @param array  $flags     Additional flags in a key value array.
 *                             Available flags:
 *                             - ExternalEdit - mark as an external edit.
 * @param null|int $sizechange Change of filesize
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Esther Brunner <wikidesign@gmail.com>
 * @author Ben Coburn <btcoburn@silicodon.net>
 */
function addLogEntry($date, $id, $type=DOKU_CHANGE_TYPE_EDIT, $summary='', $extra='', $flags=null, $sizechange = null){
    global $conf, $INFO;
    /** @var Input $INPUT */
    global $INPUT;

    // check for special flags as keys
    if (!is_array($flags)) { $flags = array(); }
    $flagExternalEdit = isset($flags['ExternalEdit']);

    $id = cleanid($id);
    $file = wikiFN($id);
    $created = @filectime($file);
    $minor = ($type===DOKU_CHANGE_TYPE_MINOR_EDIT);
    $wasRemoved = ($type===DOKU_CHANGE_TYPE_DELETE);

    if(!$date) $date = time(); //use current time if none supplied
    $remote = (!$flagExternalEdit)?clientIP(true):'127.0.0.1';
    $user   = (!$flagExternalEdit)?$INPUT->server->str('REMOTE_USER'):'';
    if($sizechange === null) {
        $sizechange = '';
    } else {
        $sizechange = (int) $sizechange;
    }

    $strip = array("\t", "\n");
    $logline = array(
        'date'       => $date,
        'ip'         => $remote,
        'type'       => str_replace($strip, '', $type),
        'id'         => $id,
        'user'       => $user,
        'sum'        => \dokuwiki\Utf8\PhpString::substr(str_replace($strip, '', $summary), 0, 255),
        'extra'      => str_replace($strip, '', $extra),
        'sizechange' => $sizechange
    );

    $wasCreated = ($type===DOKU_CHANGE_TYPE_CREATE);
    $wasReverted = ($type===DOKU_CHANGE_TYPE_REVERT);
    // update metadata
    if (!$wasRemoved) {
        $oldmeta = p_read_metadata($id);
        $meta    = array();
        if (
            $wasCreated && (
                empty($oldmeta['persistent']['date']['created']) ||
                $oldmeta['persistent']['date']['created'] === $created
            )
        ){
            // newly created
            $meta['date']['created'] = $created;
            if ($user){
                $meta['creator'] = isset($INFO) ? $INFO['userinfo']['name'] : null;
                $meta['user']    = $user;
            }
        } elseif (($wasCreated || $wasReverted) && !empty($oldmeta['persistent']['date']['created'])) {
            // re-created / restored
            $meta['date']['created']  = $oldmeta['persistent']['date']['created'];
            $meta['date']['modified'] = $created; // use the files ctime here
            $meta['creator'] = $oldmeta['persistent']['creator'];
            if ($user) $meta['contributor'][$user] = isset($INFO) ? $INFO['userinfo']['name'] : null;
        } elseif (!$minor) {   // non-minor modification
            $meta['date']['modified'] = $date;
            if ($user) $meta['contributor'][$user] = isset($INFO) ? $INFO['userinfo']['name'] : null;
        }
        $meta['last_change'] = $logline;
        p_set_metadata($id, $meta);
    }

    // add changelog lines
    $logline = implode("\t", $logline)."\n";
    io_saveFile(metaFN($id,'.changes'),$logline,true); //page changelog
    io_saveFile($conf['changelog'],$logline,true); //global changelog cache
}

/**
 * Add's an entry to the media changelog
 *
 * @author Michael Hamann <michael@content-space.de>
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Esther Brunner <wikidesign@gmail.com>
 * @author Ben Coburn <btcoburn@silicodon.net>
 *
 * @param int    $date      Timestamp of the change
 * @param String $id        Name of the affected page
 * @param String $type      Type of the change see DOKU_CHANGE_TYPE_*
 * @param String $summary   Summary of the change
 * @param mixed  $extra     In case of a revert the revision (timestmp) of the reverted page
 * @param array  $flags     Additional flags in a key value array.
 *                             Available flags:
 *                             - (none, so far)
 * @param null|int $sizechange Change of filesize
 */
function addMediaLogEntry(
    $date,
    $id,
    $type=DOKU_CHANGE_TYPE_EDIT,
    $summary='',
    $extra='',
    $flags=null,
    $sizechange = null)
{
    global $conf;
    /** @var Input $INPUT */
    global $INPUT;

    $id = cleanid($id);

    if(!$date) $date = time(); //use current time if none supplied
    $remote = clientIP(true);
    $user   = $INPUT->server->str('REMOTE_USER');
    if($sizechange === null) {
        $sizechange = '';
    } else {
        $sizechange = (int) $sizechange;
    }

    $strip = array("\t", "\n");
    $logline = array(
        'date'       => $date,
        'ip'         => $remote,
        'type'       => str_replace($strip, '', $type),
        'id'         => $id,
        'user'       => $user,
        'sum'        => \dokuwiki\Utf8\PhpString::substr(str_replace($strip, '', $summary), 0, 255),
        'extra'      => str_replace($strip, '', $extra),
        'sizechange' => $sizechange
    );

    // add changelog lines
    $logline = implode("\t", $logline)."\n";
    io_saveFile($conf['media_changelog'],$logline,true); //global media changelog cache
    io_saveFile(mediaMetaFN($id,'.changes'),$logline,true); //media file's changelog
}

/**
 * returns an array of recently changed files using the
 * changelog
 *
 * The following constants can be used to control which changes are
 * included. Add them together as needed.
 *
 * RECENTS_SKIP_DELETED   - don't include deleted pages
 * RECENTS_SKIP_MINORS    - don't include minor changes
 * RECENTS_ONLY_CREATION  - only include new created pages and media
 * RECENTS_SKIP_SUBSPACES - don't include subspaces
 * RECENTS_MEDIA_CHANGES  - return media changes instead of page changes
 * RECENTS_MEDIA_PAGES_MIXED  - return both media changes and page changes
 *
 * @param int    $first   number of first entry returned (for paginating
 * @param int    $num     return $num entries
 * @param string $ns      restrict to given namespace
 * @param int    $flags   see above
 * @return array recently changed files
 *
 * @author Ben Coburn <btcoburn@silicodon.net>
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function getRecents($first,$num,$ns='',$flags=0){
    global $conf;
    $recent = array();
    $count  = 0;

    if(!$num)
        return $recent;

    // read all recent changes. (kept short)
    if ($flags & RECENTS_MEDIA_CHANGES) {
        $lines = @file($conf['media_changelog']) ?: [];
    } else {
        $lines = @file($conf['changelog']) ?: [];
    }
    if (!is_array($lines)) {
        $lines = array();
    }
    $lines_position = count($lines)-1;
    $media_lines_position = 0;
    $media_lines = array();

    if ($flags & RECENTS_MEDIA_PAGES_MIXED) {
        $media_lines = @file($conf['media_changelog']) ?: [];
        if (!is_array($media_lines)) {
            $media_lines = array();
        }
        $media_lines_position = count($media_lines)-1;
    }

    $seen = array(); // caches seen lines, _handleRecent() skips them

    // handle lines
    while ($lines_position >= 0 || (($flags & RECENTS_MEDIA_PAGES_MIXED) && $media_lines_position >=0)) {
        if (empty($rec) && $lines_position >= 0) {
            $rec = _handleRecent(@$lines[$lines_position], $ns, $flags, $seen);
            if (!$rec) {
                $lines_position --;
                continue;
            }
        }
        if (($flags & RECENTS_MEDIA_PAGES_MIXED) && empty($media_rec) && $media_lines_position >= 0) {
            $media_rec = _handleRecent(
                @$media_lines[$media_lines_position],
                $ns,
                $flags | RECENTS_MEDIA_CHANGES,
                $seen
            );
            if (!$media_rec) {
                $media_lines_position --;
                continue;
            }
        }
        if (($flags & RECENTS_MEDIA_PAGES_MIXED) && @$media_rec['date'] >= @$rec['date']) {
            $media_lines_position--;
            $x = $media_rec;
            $x['media'] = true;
            $media_rec = false;
        } else {
            $lines_position--;
            $x = $rec;
            if ($flags & RECENTS_MEDIA_CHANGES) $x['media'] = true;
            $rec = false;
        }
        if(--$first >= 0) continue; // skip first entries
        $recent[] = $x;
        $count++;
        // break when we have enough entries
        if($count >= $num){ break; }
    }
    return $recent;
}

/**
 * returns an array of files changed since a given time using the
 * changelog
 *
 * The following constants can be used to control which changes are
 * included. Add them together as needed.
 *
 * RECENTS_SKIP_DELETED   - don't include deleted pages
 * RECENTS_SKIP_MINORS    - don't include minor changes
 * RECENTS_ONLY_CREATION  - only include new created pages and media
 * RECENTS_SKIP_SUBSPACES - don't include subspaces
 * RECENTS_MEDIA_CHANGES  - return media changes instead of page changes
 *
 * @param int    $from    date of the oldest entry to return
 * @param int    $to      date of the newest entry to return (for pagination, optional)
 * @param string $ns      restrict to given namespace (optional)
 * @param int    $flags   see above (optional)
 * @return array of files
 *
 * @author Michael Hamann <michael@content-space.de>
 * @author Ben Coburn <btcoburn@silicodon.net>
 */
function getRecentsSince($from,$to=null,$ns='',$flags=0){
    global $conf;
    $recent = array();

    if($to && $to < $from)
        return $recent;

    // read all recent changes. (kept short)
    if ($flags & RECENTS_MEDIA_CHANGES) {
        $lines = @file($conf['media_changelog']);
    } else {
        $lines = @file($conf['changelog']);
    }
    if(!$lines) return $recent;

    // we start searching at the end of the list
    $lines = array_reverse($lines);

    // handle lines
    $seen = array(); // caches seen lines, _handleRecent() skips them

    foreach($lines as $line){
        $rec = _handleRecent($line, $ns, $flags, $seen);
        if($rec !== false) {
            if ($rec['date'] >= $from) {
                if (!$to || $rec['date'] <= $to) {
                    $recent[] = $rec;
                }
            } else {
                break;
            }
        }
    }

    return array_reverse($recent);
}

/**
 * Internal function used by getRecents
 *
 * don't call directly
 *
 * @see getRecents()
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Ben Coburn <btcoburn@silicodon.net>
 *
 * @param string $line   changelog line
 * @param string $ns     restrict to given namespace
 * @param int    $flags  flags to control which changes are included
 * @param array  $seen   listing of seen pages
 * @return array|bool    false or array with info about a change
 */
function _handleRecent($line,$ns,$flags,&$seen){
    if(empty($line)) return false;   //skip empty lines

    // split the line into parts
    $recent = parseChangelogLine($line);
    if ($recent===false) { return false; }

    // skip seen ones
    if(isset($seen[$recent['id']])) return false;

    // skip changes, of only new items are requested
    if($recent['type']!==DOKU_CHANGE_TYPE_CREATE && ($flags & RECENTS_ONLY_CREATION)) return false;

    // skip minors
    if($recent['type']===DOKU_CHANGE_TYPE_MINOR_EDIT && ($flags & RECENTS_SKIP_MINORS)) return false;

    // remember in seen to skip additional sights
    $seen[$recent['id']] = 1;

    // check if it's a hidden page
    if(isHiddenPage($recent['id'])) return false;

    // filter namespace
    if (($ns) && (strpos($recent['id'],$ns.':') !== 0)) return false;

    // exclude subnamespaces
    if (($flags & RECENTS_SKIP_SUBSPACES) && (getNS($recent['id']) != $ns)) return false;

    // check ACL
    if ($flags & RECENTS_MEDIA_CHANGES) {
        $recent['perms'] = auth_quickaclcheck(getNS($recent['id']).':*');
    } else {
        $recent['perms'] = auth_quickaclcheck($recent['id']);
    }
    if ($recent['perms'] < AUTH_READ) return false;

    // check existance
    if($flags & RECENTS_SKIP_DELETED){
        $fn = (($flags & RECENTS_MEDIA_CHANGES) ? mediaFN($recent['id']) : wikiFN($recent['id']));
        if(!file_exists($fn)) return false;
    }

    return $recent;
}
