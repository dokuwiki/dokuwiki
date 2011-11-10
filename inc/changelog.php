<?php
/**
 * Changelog handling functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

// Constants for known core changelog line types.
// Use these in place of string literals for more readable code.
define('DOKU_CHANGE_TYPE_CREATE',       'C');
define('DOKU_CHANGE_TYPE_EDIT',         'E');
define('DOKU_CHANGE_TYPE_MINOR_EDIT',   'e');
define('DOKU_CHANGE_TYPE_DELETE',       'D');
define('DOKU_CHANGE_TYPE_REVERT',       'R');

/**
 * parses a changelog line into it's components
 *
 * @author Ben Coburn <btcoburn@silicodon.net>
 */
function parseChangelogLine($line) {
    $tmp = explode("\t", $line);
    if ($tmp!==false && count($tmp)>1) {
        $info = array();
        $info['date']  = (int)$tmp[0]; // unix timestamp
        $info['ip']    = $tmp[1]; // IPv4 address (127.0.0.1)
        $info['type']  = $tmp[2]; // log line type
        $info['id']    = $tmp[3]; // page id
        $info['user']  = $tmp[4]; // user name
        $info['sum']   = $tmp[5]; // edit summary (or action reason)
        $info['extra'] = rtrim($tmp[6], "\n"); // extra data (varies by line type)
        return $info;
    } else { return false; }
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
 *                             Availible flags:
 *                             - ExternalEdit - mark as an external edit.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Esther Brunner <wikidesign@gmail.com>
 * @author Ben Coburn <btcoburn@silicodon.net>
 */
function addLogEntry($date, $id, $type=DOKU_CHANGE_TYPE_EDIT, $summary='', $extra='', $flags=null){
    global $conf, $INFO;

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
    $user   = (!$flagExternalEdit)?$_SERVER['REMOTE_USER']:'';

    $strip = array("\t", "\n");
    $logline = array(
            'date'  => $date,
            'ip'    => $remote,
            'type'  => str_replace($strip, '', $type),
            'id'    => $id,
            'user'  => $user,
            'sum'   => utf8_substr(str_replace($strip, '', $summary),0,255),
            'extra' => str_replace($strip, '', $extra)
            );

    // update metadata
    if (!$wasRemoved) {
        $oldmeta = p_read_metadata($id);
        $meta    = array();
        if (!$INFO['exists'] && empty($oldmeta['persistent']['date']['created'])){ // newly created
            $meta['date']['created'] = $created;
            if ($user){
                $meta['creator'] = $INFO['userinfo']['name'];
                $meta['user']    = $user;
            }
        } elseif (!$INFO['exists'] && !empty($oldmeta['persistent']['date']['created'])) { // re-created / restored
            $meta['date']['created']  = $oldmeta['persistent']['date']['created'];
            $meta['date']['modified'] = $created; // use the files ctime here
            $meta['creator'] = $oldmeta['persistent']['creator'];
            if ($user) $meta['contributor'][$user] = $INFO['userinfo']['name'];
        } elseif (!$minor) {   // non-minor modification
            $meta['date']['modified'] = $date;
            if ($user) $meta['contributor'][$user] = $INFO['userinfo']['name'];
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
 */
function addMediaLogEntry($date, $id, $type=DOKU_CHANGE_TYPE_EDIT, $summary='', $extra='', $flags=null){
    global $conf;

    $id = cleanid($id);

    if(!$date) $date = time(); //use current time if none supplied
    $remote = clientIP(true);
    $user   = $_SERVER['REMOTE_USER'];

    $strip = array("\t", "\n");
    $logline = array(
            'date'  => $date,
            'ip'    => $remote,
            'type'  => str_replace($strip, '', $type),
            'id'    => $id,
            'user'  => $user,
            'sum'   => utf8_substr(str_replace($strip, '', $summary),0,255),
            'extra' => str_replace($strip, '', $extra)
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
 * RECENTS_SKIP_SUBSPACES - don't include subspaces
 * RECENTS_MEDIA_CHANGES  - return media changes instead of page changes
 * RECENTS_MEDIA_PAGES_MIXED  - return both media changes and page changes
 *
 * @param int    $first   number of first entry returned (for paginating
 * @param int    $num     return $num entries
 * @param string $ns      restrict to given namespace
 * @param bool   $flags   see above
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
        $lines = @file($conf['media_changelog']);
    } else {
        $lines = @file($conf['changelog']);
    }
    $lines_position = count($lines)-1;

    if ($flags & RECENTS_MEDIA_PAGES_MIXED) {
        $media_lines = @file($conf['media_changelog']);
        $media_lines_position = count($media_lines)-1;
    }

    $seen = array(); // caches seen lines, _handleRecent() skips them

    // handle lines
    while ($lines_position >= 0 || (($flags & RECENTS_MEDIA_PAGES_MIXED) && $media_lines_position >=0)) {
        if (empty($rec) && $lines_position >= 0) {
            $rec = _handleRecent(@$lines[$lines_position], $ns, $flags & ~RECENTS_MEDIA_CHANGES, $seen);
            if (!$rec) {
                $lines_position --;
                continue;
            }
        }
        if (($flags & RECENTS_MEDIA_PAGES_MIXED) && empty($media_rec) && $media_lines_position >= 0) {
            $media_rec = _handleRecent(@$media_lines[$media_lines_position], $ns, $flags | RECENTS_MEDIA_CHANGES, $seen);
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
 * RECENTS_SKIP_SUBSPACES - don't include subspaces
 * RECENTS_MEDIA_CHANGES  - return media changes instead of page changes
 *
 * @param int    $from    date of the oldest entry to return
 * @param int    $to      date of the newest entry to return (for pagination, optional)
 * @param string $ns      restrict to given namespace (optional)
 * @param bool   $flags   see above (optional)
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
 */
function _handleRecent($line,$ns,$flags,&$seen){
    if(empty($line)) return false;   //skip empty lines

    // split the line into parts
    $recent = parseChangelogLine($line);
    if ($recent===false) { return false; }

    // skip seen ones
    if(isset($seen[$recent['id']])) return false;

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
    $fn = (($flags & RECENTS_MEDIA_CHANGES) ? mediaFN($recent['id']) : wikiFN($recent['id']));
    if((!@file_exists($fn)) && ($flags & RECENTS_SKIP_DELETED)) return false;

    return $recent;
}

/**
 * Get the changelog information for a specific page id
 * and revision (timestamp). Adjacent changelog lines
 * are optimistically parsed and cached to speed up
 * consecutive calls to getRevisionInfo. For large
 * changelog files, only the chunk containing the
 * requested changelog line is read.
 *
 * @author Ben Coburn <btcoburn@silicodon.net>
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function getRevisionInfo($id, $rev, $chunk_size=8192, $media=false) {
    global $cache_revinfo;
    $cache =& $cache_revinfo;
    if (!isset($cache[$id])) { $cache[$id] = array(); }
    $rev = max($rev, 0);

    // check if it's already in the memory cache
    if (isset($cache[$id]) && isset($cache[$id][$rev])) {
        return $cache[$id][$rev];
    }

    if ($media) {
        $file = mediaMetaFN($id, '.changes');
    } else {
        $file = metaFN($id, '.changes');
    }
    if (!@file_exists($file)) { return false; }
    if (filesize($file)<$chunk_size || $chunk_size==0) {
        // read whole file
        $lines = file($file);
        if ($lines===false) { return false; }
    } else {
        // read by chunk
        $fp = fopen($file, 'rb'); // "file pointer"
        if ($fp===false) { return false; }
        $head = 0;
        fseek($fp, 0, SEEK_END);
        $tail = ftell($fp);
        $finger = 0;
        $finger_rev = 0;

        // find chunk
        while ($tail-$head>$chunk_size) {
            $finger = $head+floor(($tail-$head)/2.0);
            fseek($fp, $finger);
            fgets($fp); // slip the finger forward to a new line
            $finger = ftell($fp);
            $tmp = fgets($fp); // then read at that location
            $tmp = parseChangelogLine($tmp);
            $finger_rev = $tmp['date'];
            if ($finger==$head || $finger==$tail) { break; }
            if ($finger_rev>$rev) {
                $tail = $finger;
            } else {
                $head = $finger;
            }
        }

        if ($tail-$head<1) {
            // cound not find chunk, assume requested rev is missing
            fclose($fp);
            return false;
        }

        // read chunk
        $chunk = '';
        $chunk_size = max($tail-$head, 0); // found chunk size
        $got = 0;
        fseek($fp, $head);
        while ($got<$chunk_size && !feof($fp)) {
            $tmp = @fread($fp, max($chunk_size-$got, 0));
            if ($tmp===false) { break; } //error state
            $got += strlen($tmp);
            $chunk .= $tmp;
        }
        $lines = explode("\n", $chunk);
        array_pop($lines); // remove trailing newline
        fclose($fp);
    }

    // parse and cache changelog lines
    foreach ($lines as $value) {
        $tmp = parseChangelogLine($value);
        if ($tmp!==false) {
            $cache[$id][$tmp['date']] = $tmp;
        }
    }
    if (!isset($cache[$id][$rev])) { return false; }
    return $cache[$id][$rev];
}

/**
 * Return a list of page revisions numbers
 * Does not guarantee that the revision exists in the attic,
 * only that a line with the date exists in the changelog.
 * By default the current revision is skipped.
 *
 * id:    the page of interest
 * first: skip the first n changelog lines
 * num:   number of revisions to return
 *
 * The current revision is automatically skipped when the page exists.
 * See $INFO['meta']['last_change'] for the current revision.
 *
 * For efficiency, the log lines are parsed and cached for later
 * calls to getRevisionInfo. Large changelog files are read
 * backwards in chunks until the requested number of changelog
 * lines are recieved.
 *
 * @author Ben Coburn <btcoburn@silicodon.net>
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function getRevisions($id, $first, $num, $chunk_size=8192, $media=false) {
    global $cache_revinfo;
    $cache =& $cache_revinfo;
    if (!isset($cache[$id])) { $cache[$id] = array(); }

    $revs = array();
    $lines = array();
    $count  = 0;
    if ($media) {
        $file = mediaMetaFN($id, '.changes');
    } else {
        $file = metaFN($id, '.changes');
    }
    $num = max($num, 0);
    $chunk_size = max($chunk_size, 0);
    if ($first<0) {
        $first = 0;
    } else if (!$media && @file_exists(wikiFN($id)) || $media && @file_exists(mediaFN($id))) {
        // skip current revision if the page exists
        $first = max($first+1, 0);
    }

    if (!@file_exists($file)) { return $revs; }
    if (filesize($file)<$chunk_size || $chunk_size==0) {
        // read whole file
        $lines = file($file);
        if ($lines===false) { return $revs; }
    } else {
        // read chunks backwards
        $fp = fopen($file, 'rb'); // "file pointer"
        if ($fp===false) { return $revs; }
        fseek($fp, 0, SEEK_END);
        $tail = ftell($fp);

        // chunk backwards
        $finger = max($tail-$chunk_size, 0);
        while ($count<$num+$first) {
            fseek($fp, $finger);
            $nl = $finger;
            if ($finger>0) {
                fgets($fp); // slip the finger forward to a new line
                $nl = ftell($fp);
            }

            // was the chunk big enough? if not, take another bite
            if($nl > 0 && $tail <= $nl){
                $finger = max($finger-$chunk_size, 0);
                continue;
            }else{
                $finger = $nl;
            }

            // read chunk
            $chunk = '';
            $read_size = max($tail-$finger, 0); // found chunk size
            $got = 0;
            while ($got<$read_size && !feof($fp)) {
                $tmp = @fread($fp, max($read_size-$got, 0));
                if ($tmp===false) { break; } //error state
                $got += strlen($tmp);
                $chunk .= $tmp;
            }
            $tmp = explode("\n", $chunk);
            array_pop($tmp); // remove trailing newline

            // combine with previous chunk
            $count += count($tmp);
            $lines = array_merge($tmp, $lines);

            // next chunk
            if ($finger==0) { break; } // already read all the lines
            else {
                $tail = $finger;
                $finger = max($tail-$chunk_size, 0);
            }
        }
        fclose($fp);
    }

    // skip parsing extra lines
    $num = max(min(count($lines)-$first, $num), 0);
    if      ($first>0 && $num>0)  { $lines = array_slice($lines, max(count($lines)-$first-$num, 0), $num); }
    else if ($first>0 && $num==0) { $lines = array_slice($lines, 0, max(count($lines)-$first, 0)); }
    else if ($first==0 && $num>0) { $lines = array_slice($lines, max(count($lines)-$num, 0)); }

    // handle lines in reverse order
    for ($i = count($lines)-1; $i >= 0; $i--) {
        $tmp = parseChangelogLine($lines[$i]);
        if ($tmp!==false) {
            $cache[$id][$tmp['date']] = $tmp;
            $revs[] = $tmp['date'];
        }
    }

    return $revs;
}


