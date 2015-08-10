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
 *
 * @param string $line changelog line
 * @return array|bool parsed line or false
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
 *                             Available flags:
 *                             - ExternalEdit - mark as an external edit.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Esther Brunner <wikidesign@gmail.com>
 * @author Ben Coburn <btcoburn@silicodon.net>
 */
function addLogEntry($date, $id, $type=DOKU_CHANGE_TYPE_EDIT, $summary='', $extra='', $flags=null){
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
 *
 * @param int    $date      Timestamp of the change
 * @param String $id        Name of the affected page
 * @param String $type      Type of the change see DOKU_CHANGE_TYPE_*
 * @param String $summary   Summary of the change
 * @param mixed  $extra     In case of a revert the revision (timestmp) of the reverted page
 * @param array  $flags     Additional flags in a key value array.
 *                             Available flags:
 *                             - (none, so far)
 */
function addMediaLogEntry($date, $id, $type=DOKU_CHANGE_TYPE_EDIT, $summary='', $extra='', $flags=null){
    global $conf;
    /** @var Input $INPUT */
    global $INPUT;

    $id = cleanid($id);

    if(!$date) $date = time(); //use current time if none supplied
    $remote = clientIP(true);
    $user   = $INPUT->server->str('REMOTE_USER');

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
        $lines = @file($conf['media_changelog']);
    } else {
        $lines = @file($conf['changelog']);
    }
    $lines_position = count($lines)-1;
    $media_lines_position = 0;
    $media_lines = array();

    if ($flags & RECENTS_MEDIA_PAGES_MIXED) {
        $media_lines = @file($conf['media_changelog']);
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
        if(!@file_exists($fn)) return false;
    }

    return $recent;
}

/**
 * Class ChangeLog
 * methods for handling of changelog of pages or media files
 */
abstract class ChangeLog {

    /** @var string */
    protected $id;
    /** @var int */
    protected $chunk_size;
    /** @var array */
    protected $cache;

    /**
     * Constructor
     *
     * @param string $id         page id
     * @param int $chunk_size maximum block size read from file
     */
    public function __construct($id, $chunk_size = 8192) {
        global $cache_revinfo;

        $this->cache =& $cache_revinfo;
        if(!isset($this->cache[$id])) {
            $this->cache[$id] = array();
        }

        $this->id = $id;
        $this->setChunkSize($chunk_size);

    }

    /**
     * Set chunk size for file reading
     * Chunk size zero let read whole file at once
     *
     * @param int $chunk_size maximum block size read from file
     */
    public function setChunkSize($chunk_size) {
        if(!is_numeric($chunk_size)) $chunk_size = 0;

        $this->chunk_size = (int) max($chunk_size, 0);
    }

    /**
     * Returns path to changelog
     *
     * @return string path to file
     */
    abstract protected function getChangelogFilename();

    /**
     * Returns path to current page/media
     *
     * @return string path to file
     */
    abstract protected function getFilename();

    /**
     * Get the changelog information for a specific page id and revision (timestamp)
     *
     * Adjacent changelog lines are optimistically parsed and cached to speed up
     * consecutive calls to getRevisionInfo. For large changelog files, only the chunk
     * containing the requested changelog line is read.
     *
     * @param int $rev        revision timestamp
     * @return bool|array false or array with entries:
     *      - date:  unix timestamp
     *      - ip:    IPv4 address (127.0.0.1)
     *      - type:  log line type
     *      - id:    page id
     *      - user:  user name
     *      - sum:   edit summary (or action reason)
     *      - extra: extra data (varies by line type)
     *
     * @author Ben Coburn <btcoburn@silicodon.net>
     * @author Kate Arzamastseva <pshns@ukr.net>
     */
    public function getRevisionInfo($rev) {
        $rev = max($rev, 0);

        // check if it's already in the memory cache
        if(isset($this->cache[$this->id]) && isset($this->cache[$this->id][$rev])) {
            return $this->cache[$this->id][$rev];
        }

        //read lines from changelog
        list($fp, $lines) = $this->readloglines($rev);
        if($fp) {
            fclose($fp);
        }
        if(empty($lines)) return false;

        // parse and cache changelog lines
        foreach($lines as $value) {
            $tmp = parseChangelogLine($value);
            if($tmp !== false) {
                $this->cache[$this->id][$tmp['date']] = $tmp;
            }
        }
        if(!isset($this->cache[$this->id][$rev])) {
            return false;
        }
        return $this->cache[$this->id][$rev];
    }

    /**
     * Return a list of page revisions numbers
     *
     * Does not guarantee that the revision exists in the attic,
     * only that a line with the date exists in the changelog.
     * By default the current revision is skipped.
     *
     * The current revision is automatically skipped when the page exists.
     * See $INFO['meta']['last_change'] for the current revision.
     * A negative $first let read the current revision too.
     *
     * For efficiency, the log lines are parsed and cached for later
     * calls to getRevisionInfo. Large changelog files are read
     * backwards in chunks until the requested number of changelog
     * lines are recieved.
     *
     * @param int $first      skip the first n changelog lines
     * @param int $num        number of revisions to return
     * @return array with the revision timestamps
     *
     * @author Ben Coburn <btcoburn@silicodon.net>
     * @author Kate Arzamastseva <pshns@ukr.net>
     */
    public function getRevisions($first, $num) {
        $revs = array();
        $lines = array();
        $count = 0;

        $num = max($num, 0);
        if($num == 0) {
            return $revs;
        }

        if($first < 0) {
            $first = 0;
        } else if(@file_exists($this->getFilename())) {
            // skip current revision if the page exists
            $first = max($first + 1, 0);
        }

        $file = $this->getChangelogFilename();

        if(!@file_exists($file)) {
            return $revs;
        }
        if(filesize($file) < $this->chunk_size || $this->chunk_size == 0) {
            // read whole file
            $lines = file($file);
            if($lines === false) {
                return $revs;
            }
        } else {
            // read chunks backwards
            $fp = fopen($file, 'rb'); // "file pointer"
            if($fp === false) {
                return $revs;
            }
            fseek($fp, 0, SEEK_END);
            $tail = ftell($fp);

            // chunk backwards
            $finger = max($tail - $this->chunk_size, 0);
            while($count < $num + $first) {
                $nl = $this->getNewlinepointer($fp, $finger);

                // was the chunk big enough? if not, take another bite
                if($nl > 0 && $tail <= $nl) {
                    $finger = max($finger - $this->chunk_size, 0);
                    continue;
                } else {
                    $finger = $nl;
                }

                // read chunk
                $chunk = '';
                $read_size = max($tail - $finger, 0); // found chunk size
                $got = 0;
                while($got < $read_size && !feof($fp)) {
                    $tmp = @fread($fp, max(min($this->chunk_size, $read_size - $got), 0));
                    if($tmp === false) {
                        break;
                    } //error state
                    $got += strlen($tmp);
                    $chunk .= $tmp;
                }
                $tmp = explode("\n", $chunk);
                array_pop($tmp); // remove trailing newline

                // combine with previous chunk
                $count += count($tmp);
                $lines = array_merge($tmp, $lines);

                // next chunk
                if($finger == 0) {
                    break;
                } // already read all the lines
                else {
                    $tail = $finger;
                    $finger = max($tail - $this->chunk_size, 0);
                }
            }
            fclose($fp);
        }

        // skip parsing extra lines
        $num = max(min(count($lines) - $first, $num), 0);
        if     ($first > 0 && $num > 0)  { $lines = array_slice($lines, max(count($lines) - $first - $num, 0), $num); }
        else if($first > 0 && $num == 0) { $lines = array_slice($lines, 0, max(count($lines) - $first, 0)); }
        else if($first == 0 && $num > 0) { $lines = array_slice($lines, max(count($lines) - $num, 0)); }

        // handle lines in reverse order
        for($i = count($lines) - 1; $i >= 0; $i--) {
            $tmp = parseChangelogLine($lines[$i]);
            if($tmp !== false) {
                $this->cache[$this->id][$tmp['date']] = $tmp;
                $revs[] = $tmp['date'];
            }
        }

        return $revs;
    }

    /**
     * Get the nth revision left or right handside  for a specific page id and revision (timestamp)
     *
     * For large changelog files, only the chunk containing the
     * reference revision $rev is read and sometimes a next chunck.
     *
     * Adjacent changelog lines are optimistically parsed and cached to speed up
     * consecutive calls to getRevisionInfo.
     *
     * @param int $rev        revision timestamp used as startdate (doesn't need to be revisionnumber)
     * @param int $direction  give position of returned revision with respect to $rev; positive=next, negative=prev
     * @return bool|int
     *      timestamp of the requested revision
     *      otherwise false
     */
    public function getRelativeRevision($rev, $direction) {
        $rev = max($rev, 0);
        $direction = (int) $direction;

        //no direction given or last rev, so no follow-up
        if(!$direction || ($direction > 0 && $this->isCurrentRevision($rev))) {
            return false;
        }

        //get lines from changelog
        list($fp, $lines, $head, $tail, $eof) = $this->readloglines($rev);
        if(empty($lines)) return false;

        // look for revisions later/earlier then $rev, when founded count till the wanted revision is reached
        // also parse and cache changelog lines for getRevisionInfo().
        $revcounter = 0;
        $relativerev = false;
        $checkotherchunck = true; //always runs once
        while(!$relativerev && $checkotherchunck) {
            $tmp = array();
            //parse in normal or reverse order
            $count = count($lines);
            if($direction > 0) {
                $start = 0;
                $step = 1;
            } else {
                $start = $count - 1;
                $step = -1;
            }
            for($i = $start; $i >= 0 && $i < $count; $i = $i + $step) {
                $tmp = parseChangelogLine($lines[$i]);
                if($tmp !== false) {
                    $this->cache[$this->id][$tmp['date']] = $tmp;
                    //look for revs older/earlier then reference $rev and select $direction-th one
                    if(($direction > 0 && $tmp['date'] > $rev) || ($direction < 0 && $tmp['date'] < $rev)) {
                        $revcounter++;
                        if($revcounter == abs($direction)) {
                            $relativerev = $tmp['date'];
                        }
                    }
                }
            }

            //true when $rev is found, but not the wanted follow-up.
            $checkotherchunck = $fp
                && ($tmp['date'] == $rev || ($revcounter > 0 && !$relativerev))
                && !(($tail == $eof && $direction > 0) || ($head == 0 && $direction < 0));

            if($checkotherchunck) {
                list($lines, $head, $tail) = $this->readAdjacentChunk($fp, $head, $tail, $direction);

                if(empty($lines)) break;
            }
        }
        if($fp) {
            fclose($fp);
        }

        return $relativerev;
    }

    /**
     * Returns revisions around rev1 and rev2
     * When available it returns $max entries for each revision
     *
     * @param int $rev1 oldest revision timestamp
     * @param int $rev2 newest revision timestamp (0 looks up last revision)
     * @param int $max maximum number of revisions returned
     * @return array with two arrays with revisions surrounding rev1 respectively rev2
     */
    public function getRevisionsAround($rev1, $rev2, $max = 50) {
        $max = floor(abs($max) / 2)*2 + 1;
        $rev1 = max($rev1, 0);
        $rev2 = max($rev2, 0);

        if($rev2) {
            if($rev2 < $rev1) {
                $rev = $rev2;
                $rev2 = $rev1;
                $rev1 = $rev;
            }
        } else {
            //empty right side means a removed page. Look up last revision.
            $revs = $this->getRevisions(-1, 1);
            $rev2 = $revs[0];
        }
        //collect revisions around rev2
        list($revs2, $allrevs, $fp, $lines, $head, $tail) = $this->retrieveRevisionsAround($rev2, $max);

        if(empty($revs2)) return array(array(), array());

        //collect revisions around rev1
        $index = array_search($rev1, $allrevs);
        if($index === false) {
            //no overlapping revisions
            list($revs1,,,,,) = $this->retrieveRevisionsAround($rev1, $max);
            if(empty($revs1)) $revs1 = array();
        } else {
            //revisions overlaps, reuse revisions around rev2
            $revs1 = $allrevs;
            while($head > 0) {
                for($i = count($lines) - 1; $i >= 0; $i--) {
                    $tmp = parseChangelogLine($lines[$i]);
                    if($tmp !== false) {
                        $this->cache[$this->id][$tmp['date']] = $tmp;
                        $revs1[] = $tmp['date'];
                        $index++;

                        if($index > floor($max / 2)) break 2;
                    }
                }

                list($lines, $head, $tail) = $this->readAdjacentChunk($fp, $head, $tail, -1);
            }
            sort($revs1);
            //return wanted selection
            $revs1 = array_slice($revs1, max($index - floor($max/2), 0), $max);
        }

        return array(array_reverse($revs1), array_reverse($revs2));
    }

    /**
     * Returns lines from changelog.
     * If file larger than $chuncksize, only chunck is read that could contain $rev.
     *
     * @param int $rev   revision timestamp
     * @return array(fp, array(changeloglines), $head, $tail, $eof)|bool
     *     returns false when not succeed. fp only defined for chuck reading, needs closing.
     */
    protected function readloglines($rev) {
        $file = $this->getChangelogFilename();

        if(!@file_exists($file)) {
            return false;
        }

        $fp = null;
        $head = 0;
        $tail = 0;
        $eof = 0;

        if(filesize($file) < $this->chunk_size || $this->chunk_size == 0) {
            // read whole file
            $lines = file($file);
            if($lines === false) {
                return false;
            }
        } else {
            // read by chunk
            $fp = fopen($file, 'rb'); // "file pointer"
            if($fp === false) {
                return false;
            }
            $head = 0;
            fseek($fp, 0, SEEK_END);
            $eof = ftell($fp);
            $tail = $eof;

            // find chunk
            while($tail - $head > $this->chunk_size) {
                $finger = $head + floor(($tail - $head) / 2.0);
                $finger = $this->getNewlinepointer($fp, $finger);
                $tmp = fgets($fp);
                if($finger == $head || $finger == $tail) {
                    break;
                }
                $tmp = parseChangelogLine($tmp);
                $finger_rev = $tmp['date'];

                if($finger_rev > $rev) {
                    $tail = $finger;
                } else {
                    $head = $finger;
                }
            }

            if($tail - $head < 1) {
                // cound not find chunk, assume requested rev is missing
                fclose($fp);
                return false;
            }

            $lines = $this->readChunk($fp, $head, $tail);
        }
        return array(
            $fp,
            $lines,
            $head,
            $tail,
            $eof
        );
    }

    /**
     * Read chunk and return array with lines of given chunck.
     * Has no check if $head and $tail are really at a new line
     *
     * @param resource $fp    resource filepointer
     * @param int      $head  start point chunck
     * @param int      $tail  end point chunck
     * @return array lines read from chunck
     */
    protected function readChunk($fp, $head, $tail) {
        $chunk = '';
        $chunk_size = max($tail - $head, 0); // found chunk size
        $got = 0;
        fseek($fp, $head);
        while($got < $chunk_size && !feof($fp)) {
            $tmp = @fread($fp, max(min($this->chunk_size, $chunk_size - $got), 0));
            if($tmp === false) { //error state
                break;
            }
            $got += strlen($tmp);
            $chunk .= $tmp;
        }
        $lines = explode("\n", $chunk);
        array_pop($lines); // remove trailing newline
        return $lines;
    }

    /**
     * Set pointer to first new line after $finger and return its position
     *
     * @param resource $fp      filepointer
     * @param int      $finger  a pointer
     * @return int pointer
     */
    protected function getNewlinepointer($fp, $finger) {
        fseek($fp, $finger);
        $nl = $finger;
        if($finger > 0) {
            fgets($fp); // slip the finger forward to a new line
            $nl = ftell($fp);
        }
        return $nl;
    }

    /**
     * Check whether given revision is the current page
     *
     * @param int $rev   timestamp of current page
     * @return bool true if $rev is current revision, otherwise false
     */
    public function isCurrentRevision($rev) {
        return $rev == @filemtime($this->getFilename());
    }
    
    /**
    * Return an existing revision for a specific date which is 
    * the current one or younger or equal then the date
    *
    * @param string $id 
    * @param number $date_at timestamp
    * @return string revision ('' for current)
    */
    function getLastRevisionAt($date_at){
        //requested date_at(timestamp) younger or equal then modified_time($this->id) => load current
        if($date_at >= @filemtime($this->getFilename())) { 
            return '';
        } else if ($rev = $this->getRelativeRevision($date_at+1, -1)) { //+1 to get also the requested date revision
            return $rev;
        } else {
            return false;
        }
    }

    /**
     * Returns the next lines of the changelog  of the chunck before head or after tail
     *
     * @param resource $fp filepointer
     * @param int $head position head of last chunk
     * @param int $tail position tail of last chunk
     * @param int $direction positive forward, negative backward
     * @return array with entries:
     *    - $lines: changelog lines of readed chunk
     *    - $head: head of chunk
     *    - $tail: tail of chunk
     */
    protected function readAdjacentChunk($fp, $head, $tail, $direction) {
        if(!$fp) return array(array(), $head, $tail);

        if($direction > 0) {
            //read forward
            $head = $tail;
            $tail = $head + floor($this->chunk_size * (2 / 3));
            $tail = $this->getNewlinepointer($fp, $tail);
        } else {
            //read backward
            $tail = $head;
            $head = max($tail - $this->chunk_size, 0);
            while(true) {
                $nl = $this->getNewlinepointer($fp, $head);
                // was the chunk big enough? if not, take another bite
                if($nl > 0 && $tail <= $nl) {
                    $head = max($head - $this->chunk_size, 0);
                } else {
                    $head = $nl;
                    break;
                }
            }
        }

        //load next chunck
        $lines = $this->readChunk($fp, $head, $tail);
        return array($lines, $head, $tail);
    }

    /**
     * Collect the $max revisions near to the timestamp $rev
     *
     * @param int $rev revision timestamp
     * @param int $max maximum number of revisions to be returned
     * @return bool|array
     *     return array with entries:
     *       - $requestedrevs: array of with $max revision timestamps
     *       - $revs: all parsed revision timestamps
     *       - $fp: filepointer only defined for chuck reading, needs closing.
     *       - $lines: non-parsed changelog lines before the parsed revisions
     *       - $head: position of first readed changelogline
     *       - $lasttail: position of end of last readed changelogline
     *     otherwise false
     */
    protected function retrieveRevisionsAround($rev, $max) {
        //get lines from changelog
        list($fp, $lines, $starthead, $starttail, /* $eof */) = $this->readloglines($rev);
        if(empty($lines)) return false;

        //parse chunk containing $rev, and read forward more chunks until $max/2 is reached
        $head = $starthead;
        $tail = $starttail;
        $revs = array();
        $aftercount = $beforecount = 0;
        while(count($lines) > 0) {
            foreach($lines as $line) {
                $tmp = parseChangelogLine($line);
                if($tmp !== false) {
                    $this->cache[$this->id][$tmp['date']] = $tmp;
                    $revs[] = $tmp['date'];
                    if($tmp['date'] >= $rev) {
                        //count revs after reference $rev
                        $aftercount++;
                        if($aftercount == 1) $beforecount = count($revs);
                    }
                    //enough revs after reference $rev?
                    if($aftercount > floor($max / 2)) break 2;
                }
            }
            //retrieve next chunk
            list($lines, $head, $tail) = $this->readAdjacentChunk($fp, $head, $tail, 1);
        }
        if($aftercount == 0) return false;

        $lasttail = $tail;

        //read additional chuncks backward until $max/2 is reached and total number of revs is equal to $max
        $lines = array();
        $i = 0;
        if($aftercount > 0) {
            $head = $starthead;
            $tail = $starttail;
            while($head > 0) {
                list($lines, $head, $tail) = $this->readAdjacentChunk($fp, $head, $tail, -1);

                for($i = count($lines) - 1; $i >= 0; $i--) {
                    $tmp = parseChangelogLine($lines[$i]);
                    if($tmp !== false) {
                        $this->cache[$this->id][$tmp['date']] = $tmp;
                        $revs[] = $tmp['date'];
                        $beforecount++;
                        //enough revs before reference $rev?
                        if($beforecount > max(floor($max / 2), $max - $aftercount)) break 2;
                    }
                }
            }
        }
        sort($revs);

        //keep only non-parsed lines
        $lines = array_slice($lines, 0, $i);
        //trunk desired selection
        $requestedrevs = array_slice($revs, -$max, $max);

        return array($requestedrevs, $revs, $fp, $lines, $head, $lasttail);
    }
}

/**
 * Class PageChangelog handles changelog of a wiki page
 */
class PageChangelog extends ChangeLog {

    /**
     * Returns path to changelog
     *
     * @return string path to file
     */
    protected function getChangelogFilename() {
        return metaFN($this->id, '.changes');
    }

    /**
     * Returns path to current page/media
     *
     * @return string path to file
     */
    protected function getFilename() {
        return wikiFN($this->id);
    }
}

/**
 * Class MediaChangelog handles changelog of a media file
 */
class MediaChangelog extends ChangeLog {

    /**
     * Returns path to changelog
     *
     * @return string path to file
     */
    protected function getChangelogFilename() {
        return mediaMetaFN($this->id, '.changes');
    }

    /**
     * Returns path to current page/media
     *
     * @return string path to file
     */
    protected function getFilename() {
        return mediaFN($this->id);
    }
}

/**
 * Get the changelog information for a specific page id
 * and revision (timestamp). Adjacent changelog lines
 * are optimistically parsed and cached to speed up
 * consecutive calls to getRevisionInfo. For large
 * changelog files, only the chunk containing the
 * requested changelog line is read.
 *
 * @deprecated 2013-11-20
 *
 * @author Ben Coburn <btcoburn@silicodon.net>
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function getRevisionInfo($id, $rev, $chunk_size = 8192, $media = false) {
    dbg_deprecated('class PageChangeLog or class MediaChangelog');
    if($media) {
        $changelog = new MediaChangeLog($id, $chunk_size);
    } else {
        $changelog = new PageChangeLog($id, $chunk_size);
    }
    return $changelog->getRevisionInfo($rev);
}

/**
 * Return a list of page revisions numbers
 * Does not guarantee that the revision exists in the attic,
 * only that a line with the date exists in the changelog.
 * By default the current revision is skipped.
 *
 * The current revision is automatically skipped when the page exists.
 * See $INFO['meta']['last_change'] for the current revision.
 *
 * For efficiency, the log lines are parsed and cached for later
 * calls to getRevisionInfo. Large changelog files are read
 * backwards in chunks until the requested number of changelog
 * lines are recieved.
 *
 * @deprecated 2013-11-20
 *
 * @author Ben Coburn <btcoburn@silicodon.net>
 * @author Kate Arzamastseva <pshns@ukr.net>
 *
 * @param string $id          the page of interest
 * @param int    $first       skip the first n changelog lines
 * @param int    $num         number of revisions to return
 * @param int    $chunk_size
 * @param bool   $media
 * @return array
 */
function getRevisions($id, $first, $num, $chunk_size = 8192, $media = false) {
    dbg_deprecated('class PageChangeLog or class MediaChangelog');
    if($media) {
        $changelog = new MediaChangeLog($id, $chunk_size);
    } else {
        $changelog = new PageChangeLog($id, $chunk_size);
    }
    return $changelog->getRevisions($first, $num);
}

