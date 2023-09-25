<?php

/**
 * Changelog handling functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

use dokuwiki\ChangeLog\MediaChangeLog;
use dokuwiki\ChangeLog\ChangeLog;
use dokuwiki\ChangeLog\RevisionInfo;
use dokuwiki\File\PageFile;

/**
 * parses a changelog line into it's components
 *
 * @param string $line changelog line
 * @return array|bool parsed line or false
 *
 * @author Ben Coburn <btcoburn@silicodon.net>
 *
 * @deprecated 2023-09-25
 */
function parseChangelogLine($line)
{
    dbg_deprecated('see ' . ChangeLog::class . '::parseLogLine()');
    return ChangeLog::parseLogLine($line);
}

/**
 * Adds an entry to the changelog and saves the metadata for the page
 *
 * Note: timestamp of the change might not be unique especially after very quick
 *       repeated edits (e.g. change checkbox via do plugin)
 *
 * @param int    $date      Timestamp of the change
 * @param String $id        Name of the affected page
 * @param String $type      Type of the change see DOKU_CHANGE_TYPE_*
 * @param String $summary   Summary of the change
 * @param mixed  $extra     In case of a revert the revision (timestamp) of the reverted page
 * @param array  $flags     Additional flags in a key value array.
 *                             Available flags:
 *                             - ExternalEdit - mark as an external edit.
 * @param null|int $sizechange Change of filesize
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Esther Brunner <wikidesign@gmail.com>
 * @author Ben Coburn <btcoburn@silicodon.net>
 * @deprecated 2021-11-28
 */
function addLogEntry(
    $date,
    $id,
    $type = DOKU_CHANGE_TYPE_EDIT,
    $summary = '',
    $extra = '',
    $flags = null,
    $sizechange = null
) {
    // no more used in DokuWiki core, but left for third-party plugins
    dbg_deprecated('see ' . PageFile::class . '::saveWikiText()');

    /** @var Input $INPUT */
    global $INPUT;

    // check for special flags as keys
    if (!is_array($flags)) $flags = [];
    $flagExternalEdit = isset($flags['ExternalEdit']);

    $id = cleanid($id);

    if (!$date) $date = time(); //use current time if none supplied
    $remote = ($flagExternalEdit) ? '127.0.0.1' : clientIP(true);
    $user   = ($flagExternalEdit) ? '' : $INPUT->server->str('REMOTE_USER');
    $sizechange = ($sizechange === null) ? '' : (int)$sizechange;

    // update changelog file and get the added entry that is also to be stored in metadata
    $pageFile = new PageFile($id);
    $logEntry = $pageFile->changelog->addLogEntry([
        'date'       => $date,
        'ip'         => $remote,
        'type'       => $type,
        'id'         => $id,
        'user'       => $user,
        'sum'        => $summary,
        'extra'      => $extra,
        'sizechange' => $sizechange,
    ]);

    // update metadata
    $pageFile->updateMetadata($logEntry);
}

/**
 * Adds an entry to the media changelog
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
 * @param mixed  $extra     In case of a revert the revision (timestamp) of the reverted page
 * @param array  $flags     Additional flags in a key value array.
 *                             Available flags:
 *                             - (none, so far)
 * @param null|int $sizechange Change of filesize
 */
function addMediaLogEntry(
    $date,
    $id,
    $type = DOKU_CHANGE_TYPE_EDIT,
    $summary = '',
    $extra = '',
    $flags = null,
    $sizechange = null
) {
    /** @var Input $INPUT */
    global $INPUT;

    // check for special flags as keys
    if (!is_array($flags)) $flags = [];
    $flagExternalEdit = isset($flags['ExternalEdit']);

    $id = cleanid($id);

    if (!$date) $date = time(); //use current time if none supplied
    $remote = ($flagExternalEdit) ? '127.0.0.1' : clientIP(true);
    $user   = ($flagExternalEdit) ? '' : $INPUT->server->str('REMOTE_USER');
    $sizechange = ($sizechange === null) ? '' : (int)$sizechange;

    // update changelog file and get the added entry
    (new MediaChangeLog($id, 1024))->addLogEntry([
        'date'       => $date,
        'ip'         => $remote,
        'type'       => $type,
        'id'         => $id,
        'user'       => $user,
        'sum'        => $summary,
        'extra'      => $extra,
        'sizechange' => $sizechange,
    ]);
}

/**
 * returns an array of recently changed files using the changelog
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
function getRecents($first, $num, $ns = '', $flags = 0)
{
    global $conf;
    $recent = [];
    $count  = 0;

    if (!$num) {
        return $recent;
    }

    // read all recent changes. (kept short)
    if ($flags & RECENTS_MEDIA_CHANGES) {
        $lines = @file($conf['media_changelog']) ?: [];
    } else {
        $lines = @file($conf['changelog']) ?: [];
    }
    if (!is_array($lines)) {
        $lines = [];
    }
    $lines_position = count($lines) - 1;
    $media_lines_position = 0;
    $media_lines = [];

    if ($flags & RECENTS_MEDIA_PAGES_MIXED) {
        $media_lines = @file($conf['media_changelog']) ?: [];
        if (!is_array($media_lines)) {
            $media_lines = [];
        }
        $media_lines_position = count($media_lines) - 1;
    }

    $seen = []; // caches seen lines, _handleRecentLogLine() skips them

    // handle lines
    while ($lines_position >= 0 || (($flags & RECENTS_MEDIA_PAGES_MIXED) && $media_lines_position >= 0)) {
        if (empty($rec) && $lines_position >= 0) {
            $rec = _handleRecentLogLine(@$lines[$lines_position], $ns, $flags, $seen);
            if (!$rec) {
                $lines_position--;
                continue;
            }
        }
        if (($flags & RECENTS_MEDIA_PAGES_MIXED) && empty($media_rec) && $media_lines_position >= 0) {
            $media_rec = _handleRecentLogLine(
                @$media_lines[$media_lines_position],
                $ns,
                $flags | RECENTS_MEDIA_CHANGES,
                $seen
            );
            if (!$media_rec) {
                $media_lines_position--;
                continue;
            }
        }
        if (($flags & RECENTS_MEDIA_PAGES_MIXED) && @$media_rec['date'] >= @$rec['date']) {
            $media_lines_position--;
            $x = $media_rec;
            $x['mode'] = RevisionInfo::MODE_MEDIA;
            $media_rec = false;
        } else {
            $lines_position--;
            $x = $rec;
            if ($flags & RECENTS_MEDIA_CHANGES) {
                $x['mode'] = RevisionInfo::MODE_MEDIA;
            } else {
                $x['mode'] = RevisionInfo::MODE_PAGE;
            }
            $rec = false;
        }
        if (--$first >= 0) continue; // skip first entries
        $recent[] = $x;
        $count++;
        // break when we have enough entries
        if ($count >= $num) {
            break;
        }
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
function getRecentsSince($from, $to = null, $ns = '', $flags = 0)
{
    global $conf;
    $recent = [];

    if ($to && $to < $from) {
        return $recent;
    }

    // read all recent changes. (kept short)
    if ($flags & RECENTS_MEDIA_CHANGES) {
        $lines = @file($conf['media_changelog']);
    } else {
        $lines = @file($conf['changelog']);
    }
    if (!$lines) return $recent;

    // we start searching at the end of the list
    $lines = array_reverse($lines);

    // handle lines
    $seen = []; // caches seen lines, _handleRecentLogLine() skips them

    foreach ($lines as $line) {
        $rec = _handleRecentLogLine($line, $ns, $flags, $seen);
        if ($rec !== false) {
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
 * Parse a line and checks whether it should be included
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
function _handleRecentLogLine($line, $ns, $flags, &$seen)
{
    if (empty($line)) return false;   //skip empty lines

    // split the line into parts
    $recent = ChangeLog::parseLogLine($line);
    if ($recent === false) return false;

    // skip seen ones
    if (isset($seen[$recent['id']])) return false;

    // skip changes, of only new items are requested
    if ($recent['type'] !== DOKU_CHANGE_TYPE_CREATE && ($flags & RECENTS_ONLY_CREATION)) return false;

    // skip minors
    if ($recent['type'] === DOKU_CHANGE_TYPE_MINOR_EDIT && ($flags & RECENTS_SKIP_MINORS)) return false;

    // remember in seen to skip additional sights
    $seen[$recent['id']] = 1;

    // check if it's a hidden page
    if (isHiddenPage($recent['id'])) return false;

    // filter namespace
    if (($ns) && (strpos($recent['id'], $ns . ':') !== 0)) return false;

    // exclude subnamespaces
    if (($flags & RECENTS_SKIP_SUBSPACES) && (getNS($recent['id']) != $ns)) return false;

    // check ACL
    if ($flags & RECENTS_MEDIA_CHANGES) {
        $recent['perms'] = auth_quickaclcheck(getNS($recent['id']) . ':*');
    } else {
        $recent['perms'] = auth_quickaclcheck($recent['id']);
    }
    if ($recent['perms'] < AUTH_READ) return false;

    // check existence
    if ($flags & RECENTS_SKIP_DELETED) {
        $fn = (($flags & RECENTS_MEDIA_CHANGES) ? mediaFN($recent['id']) : wikiFN($recent['id']));
        if (!file_exists($fn)) return false;
    }

    return $recent;
}
