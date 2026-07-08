<?php

namespace dokuwiki\ChangeLog;

use dokuwiki\Logger;

/**
 * ChangeLog Prototype; methods for handling changelog
 */
abstract class ChangeLog
{
    use ChangeLogTrait;

    /** @var string */
    protected $id;
    /** @var false|int */
    protected $currentRevision;
    /** @var array */
    protected $cache = [];

    /**
     * Constructor
     *
     * @param string $id page id
     * @param int $chunk_size maximum block size read from file
     */
    public function __construct($id, $chunk_size = 8192)
    {
        global $cache_revinfo;

        $this->cache =& $cache_revinfo;
        if (!isset($this->cache[$id])) {
            $this->cache[$id] = [];
        }

        $this->id = $id;
        $this->setChunkSize($chunk_size);
    }

    /**
     * Returns path to current page/media
     *
     * @param string|int $rev empty string or revision timestamp
     * @return string path to file
     */
    abstract protected function getFilename($rev = '');

    /**
     * Returns mode
     *
     * @return string RevisionInfo::MODE_MEDIA or RevisionInfo::MODE_PAGE
     */
    abstract protected function getMode();

    /**
     * Returns path to the global changelog file (the cross-page recent-changes feed)
     *
     * @return string path to file
     */
    abstract protected function getGlobalChangelogFilename();

    /**
     * Check whether given revision is the current page
     *
     * @param int $rev timestamp of current page
     * @return bool true if $rev is current revision, otherwise false
     */
    public function isCurrentRevision($rev)
    {
        return $rev == $this->currentRevision();
    }

    /**
     * Checks if the revision is last revision
     *
     * @param int $rev revision timestamp
     * @return bool true if $rev is last revision, otherwise false
     */
    public function isLastRevision($rev = null)
    {
        return $rev === $this->lastRevision();
    }

    /**
     * Return the current revision identifier
     *
     * The "current" revision means current version of the page or media file. It is either
     * identical with or newer than the "last" revision, that depends on whether the file
     * has modified, created or deleted outside of DokuWiki.
     * The value of identifier can be determined by timestamp as far as the file exists,
     * otherwise it must be assigned larger than any other revisions to keep them sortable.
     *
     * @return int|false revision timestamp
     */
    public function currentRevision()
    {
        if (!isset($this->currentRevision)) {
            // set ChangeLog::currentRevision property
            $this->getCurrentRevisionInfo();
        }
        return $this->currentRevision;
    }

    /**
     * Return the last revision identifier, date value of the last entry of the changelog
     *
     * @return int|false revision timestamp
     */
    public function lastRevision()
    {
        $revs = $this->getRevisions(-1, 1);
        return empty($revs) ? false : $revs[0];
    }

    /**
     * Parses a changelog line into its components and save revision info to the cache pool
     *
     * @param string $value changelog line
     * @return array|bool parsed line or false
     */
    protected function parseAndCacheLogLine($value)
    {
        $info = static::parseLogLine($value);
        if (is_array($info)) {
            $info['mode'] = $this->getMode();
            $this->cache[$this->id][$info['date']] ??= $info;
            return $info;
        }
        return false;
    }

    /**
     * Get the changelog information for a specific revision (timestamp)
     *
     * Adjacent changelog lines are optimistically parsed and cached to speed up
     * consecutive calls to getRevisionInfo. For large changelog files, only the chunk
     * containing the requested changelog line is read.
     *
     * @param int $rev revision timestamp
     * @param bool $retrieveCurrentRevInfo allows to skip for getting other revision info in the
     *                                     getCurrentRevisionInfo() where $currentRevision is not yet determined
     * @return bool|array false or array with entries:
     *      - date:  unix timestamp
     *      - ip:    IPv4 address (127.0.0.1)
     *      - type:  log line type
     *      - id:    page id
     *      - user:  user name
     *      - sum:   edit summary (or action reason)
     *      - extra: extra data (varies by line type)
     *      - sizechange: change of filesize
     *    additional:
     *      - mode: page or media
     *
     * @author Ben Coburn <btcoburn@silicodon.net>
     * @author Kate Arzamastseva <pshns@ukr.net>
     */
    public function getRevisionInfo($rev, $retrieveCurrentRevInfo = true)
    {
        $rev = max(0, $rev);
        if (!$rev) return false;

        //ensure the external edits are cached as well
        if (!isset($this->currentRevision) && $retrieveCurrentRevInfo) {
            $this->getCurrentRevisionInfo();
        }

        // check if it's already in the memory cache
        if (isset($this->cache[$this->id][$rev])) {
            return $this->cache[$this->id][$rev];
        }

        //read lines from changelog
        $result = $this->readloglines($rev);
        if ($result === false) return false;
        [$fp, $lines] = $result;
        if ($fp) {
            fclose($fp);
        }
        if (empty($lines)) return false;

        // parse and cache changelog lines
        foreach ($lines as $line) {
            $this->parseAndCacheLogLine($line);
        }

        return $this->cache[$this->id][$rev] ?? false;
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
     * lines are received.
     *
     * @param int $first skip the first n changelog lines
     * @param int $num number of revisions to return
     * @return array with the revision timestamps
     *
     * @author Ben Coburn <btcoburn@silicodon.net>
     * @author Kate Arzamastseva <pshns@ukr.net>
     */
    public function getRevisions($first, $num)
    {
        $revs = [];
        $lines = [];
        $count = 0;

        $logfile = $this->getChangelogFilename();
        if (!file_exists($logfile)) return $revs;

        $num = max($num, 0);
        if ($num == 0) {
            return $revs;
        }

        if ($first < 0) {
            $first = 0;
        } else {
            $fileLastMod = $this->getFilename();
            if (file_exists($fileLastMod) && $this->isLastRevision(filemtime($fileLastMod))) {
                // skip last revision if the page exists
                $first = max($first + 1, 0);
            }
        }

        if (filesize($logfile) < $this->chunk_size || $this->chunk_size == 0) {
            // read whole file
            $lines = file($logfile);
            if ($lines === false) {
                return $revs;
            }
        } else {
            // read chunks backwards
            $fp = fopen($logfile, 'rb'); // "file pointer"
            if ($fp === false) {
                return $revs;
            }
            fseek($fp, 0, SEEK_END);
            $tail = ftell($fp);

            // chunk backwards
            $finger = max($tail - $this->chunk_size, 0);
            while ($count < $num + $first) {
                $nl = $this->getNewlinepointer($fp, $finger);

                // was the chunk big enough? if not, take another bite
                if ($nl > 0 && $tail <= $nl) {
                    $finger = max($finger - $this->chunk_size, 0);
                    continue;
                } else {
                    $finger = $nl;
                }

                // read chunk
                $chunk = '';
                $read_size = max($tail - $finger, 0); // found chunk size
                $got = 0;
                while ($got < $read_size && !feof($fp)) {
                    $tmp = @fread($fp, max(min($this->chunk_size, $read_size - $got), 0));
                    if ($tmp === false) {
                        break;
                    } //error state
                    $got += strlen($tmp);
                    $chunk .= $tmp;
                }
                $tmp = explode("\n", $chunk);
                array_pop($tmp); // remove trailing newline

                // combine with previous chunk
                $count += count($tmp);
                $lines = [...$tmp, ...$lines];

                // next chunk
                if ($finger == 0) {
                    break;
                } else { // already read all the lines
                    $tail = $finger;
                    $finger = max($tail - $this->chunk_size, 0);
                }
            }
            fclose($fp);
        }

        // skip parsing extra lines
        $num = max(min(count($lines) - $first, $num), 0);
        if ($first > 0 && $num > 0) {
            $lines = array_slice($lines, max(count($lines) - $first - $num, 0), $num);
        } elseif ($first > 0 && $num == 0) {
            $lines = array_slice($lines, 0, max(count($lines) - $first, 0));
        } elseif ($first == 0 && $num > 0) {
            $lines = array_slice($lines, max(count($lines) - $num, 0));
        }

        // handle lines in reverse order
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            $info = $this->parseAndCacheLogLine($lines[$i]);
            if (is_array($info)) {
                $revs[] = $info['date'];
            }
        }

        return $revs;
    }

    /**
     * Get the nth revision left or right-hand side  for a specific page id and revision (timestamp)
     *
     * For large changelog files, only the chunk containing the
     * reference revision $rev is read and sometimes a next chunk.
     *
     * Adjacent changelog lines are optimistically parsed and cached to speed up
     * consecutive calls to getRevisionInfo.
     *
     * @param int $rev revision timestamp used as start date
     *    (doesn't need to be exact revision number)
     * @param int $direction give position of returned revision with respect to $rev;
          positive=next, negative=prev
     * @return bool|int
     *      timestamp of the requested revision
     *      otherwise false
     */
    public function getRelativeRevision($rev, $direction)
    {
        $rev = max($rev, 0);
        $direction = (int)$direction;

        //no direction given or last rev, so no follow-up
        if (!$direction || ($direction > 0 && $this->isCurrentRevision($rev))) {
            return false;
        }

        //get lines from changelog
        $result = $this->readloglines($rev);
        if ($result === false) return false;
        [$fp, $lines, $head, $tail, $eof] = $result;
        if (empty($lines)) return false;

        // look for revisions later/earlier than $rev, when founded count till the wanted revision is reached
        // also parse and cache changelog lines for getRevisionInfo().
        $revCounter = 0;
        $relativeRev = false;
        $checkOtherChunk = true; //always runs once
        while (!$relativeRev && $checkOtherChunk) {
            $info = [];
            //parse in normal or reverse order
            $count = count($lines);
            if ($direction > 0) {
                $start = 0;
                $step = 1;
            } else {
                $start = $count - 1;
                $step = -1;
            }
            for ($i = $start; $i >= 0 && $i < $count; $i += $step) {
                $info = $this->parseAndCacheLogLine($lines[$i]);
                if (is_array($info)) {
                    //look for revs older/earlier then reference $rev and select $direction-th one
                    if (($direction > 0 && $info['date'] > $rev) || ($direction < 0 && $info['date'] < $rev)) {
                        $revCounter++;
                        if ($revCounter == abs($direction)) {
                            $relativeRev = $info['date'];
                        }
                    }
                }
            }

            //true when $rev is found, but not the wanted follow-up.
            $checkOtherChunk = $fp
                && ($info['date'] == $rev || ($revCounter > 0 && !$relativeRev))
                && (!($tail == $eof && $direction > 0) && !($head == 0 && $direction < 0));

            if ($checkOtherChunk) {
                [$lines, $head, $tail] = $this->readAdjacentChunk($fp, $head, $tail, $direction);

                if (empty($lines)) break;
            }
        }
        if ($fp) {
            fclose($fp);
        }

        return $relativeRev;
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
    public function getRevisionsAround($rev1, $rev2, $max = 50)
    {
        $max = (int) (abs($max) / 2) * 2 + 1;
        $rev1 = max($rev1, 0);
        $rev2 = max($rev2, 0);

        if ($rev2) {
            if ($rev2 < $rev1) {
                $rev = $rev2;
                $rev2 = $rev1;
                $rev1 = $rev;
            }
        } else {
            //empty right side means a removed page. Look up last revision.
            $rev2 = $this->currentRevision();
        }
        //collect revisions around rev2
        $result2 = $this->retrieveRevisionsAround($rev2, $max);
        if ($result2 === false) return [[], []];
        [$revs2, $allRevs, $fp, $lines, $head, $tail] = $result2;

        if (empty($revs2)) return [[], []];

        //collect revisions around rev1
        $index = array_search($rev1, $allRevs);
        if ($index === false) {
            //no overlapping revisions
            $result1 = $this->retrieveRevisionsAround($rev1, $max);
            if ($result1 === false) {
                $revs1 = [];
            } else {
                [$revs1, , , , , ] = $result1;
                if (empty($revs1)) $revs1 = [];
            }
        } else {
            //revisions overlaps, reuse revisions around rev2
            $lastRev = array_pop($allRevs); //keep last entry that could be external edit
            $revs1 = $allRevs;
            while ($head > 0) {
                for ($i = count($lines) - 1; $i >= 0; $i--) {
                    $info = $this->parseAndCacheLogLine($lines[$i]);
                    if (is_array($info)) {
                        $revs1[] = $info['date'];
                        $index++;

                        if ($index > (int) ($max / 2)) {
                            break 2;
                        }
                    }
                }

                [$lines, $head, $tail] = $this->readAdjacentChunk($fp, $head, $tail, -1);
            }
            sort($revs1);
            $revs1[] = $lastRev; //push back last entry

            //return wanted selection
            $revs1 = array_slice($revs1, max($index - (int) ($max / 2), 0), $max);
        }

        return [array_reverse($revs1), array_reverse($revs2)];
    }

    /**
     * Return an existing revision for a specific date which is
     * the current one or younger or equal then the date
     *
     * @param number $date_at timestamp
     * @return string revision ('' for current)
     */
    public function getLastRevisionAt($date_at)
    {
        $fileLastMod = $this->getFilename();
        //requested date_at(timestamp) younger or equal then modified_time($this->id) => load current
        if (file_exists($fileLastMod) && $date_at >= @filemtime($fileLastMod)) {
            return '';
        } elseif ($rev = $this->getRelativeRevision($date_at + 1, -1)) {
            //+1 to get also the requested date revision
            return $rev;
        } else {
            return false;
        }
    }

    /**
     * Collect the $max revisions near to the timestamp $rev
     *
     * Ideally, half of retrieved timestamps are older than $rev, another half are newer.
     * The returned array $requestedRevs may not contain the reference timestamp $rev
     * when it does not match any revision value recorded in changelog.
     *
     * @param int $rev revision timestamp
     * @param int $max maximum number of revisions to be returned
     * @return bool|array
     *     return array with entries:
     *       - $requestedRevs: array of with $max revision timestamps
     *       - $revs: all parsed revision timestamps
     *       - $fp: file pointer only defined for chuck reading, needs closing.
     *       - $lines: non-parsed changelog lines before the parsed revisions
     *       - $head: position of first read changelog line
     *       - $lastTail: position of end of last read changelog line
     *     otherwise false
     */
    protected function retrieveRevisionsAround($rev, $max)
    {
        $revs = [];
        $afterCount = 0;
        $beforeCount = 0;

        //get lines from changelog
        $result = $this->readloglines($rev);
        if ($result === false) return false;
        [$fp, $lines, $startHead, $startTail, $eof] = $result;
        if (empty($lines)) return false;

        //parse changelog lines in chunk, and read forward more chunks until $max/2 is reached
        $head = $startHead;
        $tail = $startTail;
        while (count($lines) > 0) {
            foreach ($lines as $line) {
                $info = $this->parseAndCacheLogLine($line);
                if (is_array($info)) {
                    $revs[] = $info['date'];
                    if ($info['date'] >= $rev) {
                        //count revs after reference $rev
                        $afterCount++;
                        if ($afterCount == 1) {
                            $beforeCount = count($revs);
                        }
                    }
                    //enough revs after reference $rev?
                    if ($afterCount > (int) ($max / 2)) {
                        break 2;
                    }
                }
            }
            //retrieve next chunk
            [$lines, $head, $tail] = $this->readAdjacentChunk($fp, $head, $tail, 1);
        }
        $lastTail = $tail;

        // add a possible revision of external edit, create or deletion
        if (
            $lastTail == $eof && $afterCount <= (int) ($max / 2) &&
            count($revs) && !$this->isCurrentRevision($revs[count($revs) - 1])
        ) {
            $revs[] = $this->currentRevision;
            $afterCount++;
        }

        if ($afterCount == 0) {
            //given timestamp $rev is newer than the most recent line in chunk
            return false; //FIXME: or proceed to collect older revisions?
        }

        //read more chunks backward until $max/2 is reached and total number of revs is equal to $max
        $lines = [];
        $i = 0;
        $head = $startHead;
        $tail = $startTail;
        while ($head > 0) {
            [$lines, $head, $tail] = $this->readAdjacentChunk($fp, $head, $tail, -1);

            for ($i = count($lines) - 1; $i >= 0; $i--) {
                $info = $this->parseAndCacheLogLine($lines[$i]);
                if (is_array($info)) {
                    $revs[] = $info['date'];
                    $beforeCount++;
                    //enough revs before reference $rev?
                    if ($beforeCount > max((int) ($max / 2), $max - $afterCount)) {
                        break 2;
                    }
                }
            }
        }
        //keep only non-parsed lines
        $lines = array_slice($lines, 0, $i);

        sort($revs);

        //trunk desired selection
        $requestedRevs = array_slice($revs, -$max, $max);

        return [$requestedRevs, $revs, $fp, $lines, $head, $lastTail];
    }

    /**
     * Get the current revision information, considering external edit, create or deletion
     *
     * When the file has not modified since its last revision, the information of the last
     * change that had already recorded in the changelog is returned as current change info.
     * Otherwise, the change information since the last revision caused outside DokuWiki
     * should be returned, which is referred as "external revision".
     *
     * External revisions are persisted to the changelog on first detection so subsequent reads
     * see one canonical entry instead of recomputing a synthesized one (and, where the revision
     * scheme keeps it, the content is snapshotted to the attic). If persistence fails (e.g. the
     * data dir is not writable in the current process context), the in-memory synthesized entry
     * is still returned so the read path keeps working.
     *
     * @return bool|array false when page had never existed or array with entries:
     *      - date:  revision identifier (timestamp or last revision +1)
     *      - ip:    IPv4 address (127.0.0.1)
     *      - type:  log line type
     *      - id:    id of page or media
     *      - user:  user name
     *      - sum:   edit summary (or action reason)
     *      - extra: extra data (varies by line type)
     *      - sizechange: change of filesize
     *      - timestamp: unix timestamp or false (key set only for external edit occurred)
     *   additional:
     *      - mode:  page or media
     *
     * @author  Satoshi Sahara <sahara.satoshi@gmail.com>
     */
    public function getCurrentRevisionInfo()
    {
        if (isset($this->currentRevision)) {
            return $this->getRevisionInfo($this->currentRevision);
        }

        // the current revision id is the item file's mtime; reconcile it against the changelog
        $filename = $this->getFilename();
        $fileRev = @filemtime($filename); // false when the file does not exist
        $recordedRev = $this->lastRevision(); // false when there is no changelog

        // file and changelog agree (or the item never existed): the recorded state is current
        if ($fileRev === $recordedRev) {
            $this->currentRevision = $recordedRev;
            return $recordedRev === false ? false : $this->getRevisionInfo($recordedRev);
        }

        // they disagree, so an external change happened. Classify it and synthesize the missing entry.
        if (!$fileRev) {
            // the file is gone: external deletion
            $revInfo = $this->synthesizeExternalDeletion($recordedRev);
        } elseif ($recordedRev === false || $this->getRevisionInfo($recordedRev, false)['type'] == DOKU_CHANGE_TYPE_DELETE) {
            // no changelog, or it logged a delete: (re)creation
            $revInfo = $this->synthesizeExternalCreate($filename, $fileRev, $recordedRev);
        } else {
            // the file changed against a recorded live page: external edit
            $revInfo = $this->synthesizeExternalEdit($filename, $fileRev, $recordedRev);
        }
        if ($revInfo === null) {
            // null means the external change was a no-op (content unchanged), so keep the recorded revision as current
            $this->currentRevision = $recordedRev;
            return $this->getRevisionInfo($recordedRev);
        }

        // persist the synthesized entry so subsequent reads see it as a real changelog entry
        $this->persistCurrentRevisionInfo($revInfo);
        $this->currentRevision = $revInfo['date'];
        $this->cache[$this->id][$this->currentRevision] = $revInfo;
        return $this->getRevisionInfo($this->currentRevision);
    }

    /**
     * Synthesize the changelog entry for an external deletion: the item file is gone while the
     * changelog still holds entries.
     *
     * @param int $recordedRev date of the newest recorded changelog revision
     * @return array|null revision info to persist, or null when the deletion is already recorded
     *                    at $recordedRev (that revision stays current, nothing to synthesize)
     */
    protected function synthesizeExternalDeletion($recordedRev)
    {
        global $lang;

        if ($this->getRevisionInfo($recordedRev, false)['type'] == DOKU_CHANGE_TYPE_DELETE) {
            return null;
        }

        // date the deletion as late as possible: 1 sec before now, or newest revision +1
        return [
            'date' => max($recordedRev + 1, time() - 1),
            'ip'   => '127.0.0.1',
            'type' => DOKU_CHANGE_TYPE_DELETE,
            'id'   => $this->id,
            'user' => '',
            'sum'  => $lang['deleted'] . ' - ' . $lang['external_edit'] . ' (' . $lang['unknowndate'] . ')',
            'extra' => '',
            'sizechange' => -$this->lastRevisionSize($recordedRev),
            'timestamp' => false,
            'mode' => $this->getMode()
        ];
    }

    /**
     * Synthesize the changelog entry for an external edit: the item file exists and its mtime
     * differs from the newest recorded revision, which is a live (non-deleted) page.
     *
     * @param string $filename path to the current item file
     * @param int    $fileRev  mtime of the current item file
     * @param int    $recordedRev  date of the newest recorded changelog revision
     * @return array|null revision info to persist, or null when the file was only touched (content
     *                    unchanged): the mtime is reset to $recordedRev and that revision stays current
     */
    protected function synthesizeExternalEdit($filename, $fileRev, $recordedRev)
    {
        global $lang;

        // A file mtime can move without the content changing (backup restore, git checkout, ...).
        // When the content still matches $recordedRev nothing was really edited: reset the mtime to the
        // recorded date and keep that revision.
        if ($this->currentContentMatchesRevision($recordedRev)) {
            @touch($filename, $recordedRev);
            clearstatcache(false, $filename);
            return null;
        }

        if ($fileRev > $recordedRev) {
            $timestamp = $fileRev;
            $sum = $lang['external_edit'];
        } else {
            // $fileRev is older than $recordedRev, that is an erroneous/incorrect occurrence
            $msg = "Warning: current file modification time is older than last revision date";
            $details = 'File revision: ' . $fileRev . ' ' . dformat($fileRev, "%Y-%m-%d %H:%M:%S") . "\n"
                      . 'Last revision: ' . $recordedRev . ' ' . dformat($recordedRev, "%Y-%m-%d %H:%M:%S");
            Logger::error($msg, $details, $filename);
            $timestamp = false;
            $sum = $lang['external_edit'] . ' (' . $lang['unknowndate'] . ')';
        }

        return [
            'date' => $timestamp ?: $recordedRev + 1,
            'ip'   => '127.0.0.1',
            'type' => DOKU_CHANGE_TYPE_EDIT,
            'id'   => $this->id,
            'user' => '',
            'sum'  => $sum,
            'extra' => '',
            'sizechange' => filesize($filename) - $this->lastRevisionSize($recordedRev),
            'timestamp' => $timestamp,
            'mode' => $this->getMode()
        ];
    }

    /**
     * Synthesize the changelog entry for an external creation: the item file exists but nothing
     * live was recorded at $recordedRev (no changelog at all, or the newest revision is a DELETE), so
     * the file is a fresh (re)creation.
     *
     * @param string    $filename path to the current item file
     * @param int       $fileRev  mtime of the current item file
     * @param int|false $recordedRev  date of the newest recorded changelog revision, or false when none
     * @return array revision info to persist
     */
    protected function synthesizeExternalCreate($filename, $fileRev, $recordedRev)
    {
        global $lang;

        // trust the file mtime as the creation date only when it postdates any prior delete; a
        // backup restored with an older mtime (cp -p) can't date the creation before the deletion,
        // so record it just after, with an unknown date.
        $datedByFile = $recordedRev === false || $fileRev > $recordedRev;
        $timestamp = $datedByFile ? $fileRev : false;
        $sum = $lang['created'] . ' - ' . $lang['external_edit']
             . ($datedByFile ? '' : ' (' . $lang['unknowndate'] . ')');

        return [
            'date' => $timestamp ?: $recordedRev + 1,
            'ip'   => '127.0.0.1',
            'type' => DOKU_CHANGE_TYPE_CREATE,
            'id'   => $this->id,
            'user' => '',
            'sum'  => $sum,
            'extra' => '',
            'sizechange' => filesize($filename),
            'timestamp' => $timestamp,
            'mode' => $this->getMode()
        ];
    }

    /**
     * Adds an entry to the changelog
     *
     * Locks the local changelog file for the duration of the write so concurrent writers
     * serialize through the same key. Subclasses provide the actual append logic via
     * writeLogEntry() so persistCurrentRevisionInfo() can append while already holding
     * the lock without re-entering it.
     *
     * Best-effort: if writeLogEntry() throws, surfaces the error via msg() and still
     * returns the info dict so existing callers (saveWikiText etc.) keep working.
     *
     * @param array $info    Revision info structure of a page or media file
     * @param int $timestamp log line date (optional)
     * @return array revision info of added log line
     */
    public function addLogEntry(array $info, $timestamp = null)
    {
        $logfile = $this->getChangelogFilename();
        io_lock($logfile);
        try {
            return $this->writeLogEntry($info, $timestamp);
        } catch (\RuntimeException $e) {
            msg($e->getMessage(), -1);
            $info['mode'] = $this->getMode();
            return $info;
        } finally {
            io_unlock($logfile);
        }
    }

    /**
     * Append a log entry to the local and global changelog and update the in-memory cache.
     *
     * Locking is the caller's responsibility: this method appends to the local changelog
     * directly and assumes the caller already holds io_lock() on it.
     *
     * This method is currently used by addLogEntry() for normal edits and by persistCurrentRevisionInfo()
     * for detected external edits, both of which hold the local changelog lock around the call.
     *
     * Writing the global changelog is triggered from here via writeGlobalLogEntry(); being a separate
     * file it has its own locking, which is handled by io_saveFile() rather than by the
     * local lock above.
     *
     * @param array $info    Revision info structure
     * @param int $timestamp log line date (optional)
     * @param bool $external entry is a detected external edit (kept out of the global feed when out of order)
     * @return array revision info of added log line
     * @throws \RuntimeException if the local changelog write fails
     */
    protected function writeLogEntry(array $info, $timestamp = null, $external = false)
    {
        global $conf;

        if (isset($timestamp)) unset($this->cache[$this->id][$info['date']]);

        $logline = static::buildLogLine($info, $timestamp);

        // append to local changelog without re-locking (caller holds the lock)
        $localFile = $this->getChangelogFilename();
        io_makeFileDir($localFile);
        $fileexists = file_exists($localFile);
        $fh = @fopen($localFile, 'ab');
        if (!$fh || @fwrite($fh, $logline) === false) {
            if ($fh) @fclose($fh);
            throw new \RuntimeException("Writing $localFile failed");
        }
        fclose($fh);
        if (!$fileexists && !empty($conf['fperm'])) chmod($localFile, $conf['fperm']);

        $this->writeGlobalLogEntry($logline, $info['date'], $external);

        $this->currentRevision = $info['date'];
        $info['mode'] = $this->getMode();
        $this->cache[$this->id][$this->currentRevision] = $info;
        return $info;
    }

    /**
     * Append a log line to the global changelog (the cross-page recent-changes feed).
     *
     * The write goes through io_saveFile(), which locks the global changelog and reports
     * errors via msg(). That locking is independent of the local changelog lock the caller
     * holds, as the global changelog is a separate file.
     *
     * A detected external edit ($external) is skipped here when its date is older than the
     * feed's most recent change: appending it would place it at the top of recent changes
     * with an old date (issue #4634). Skipping does not lose the entry — writeLogEntry() has
     * already recorded it in the page's own changelog; it is only kept out of the cross-page
     * feed. Normal edits are always appended.
     *
     * @param string $logline the changelog line to append
     * @param int $date revision date of the entry, compared against the feed's last change
     * @param bool $external entry is a detected external edit
     */
    protected function writeGlobalLogEntry($logline, $date, $external)
    {
        $globalFile = $this->getGlobalChangelogFilename();

        // skip an out-of-order external edit
        if ($external) {
            clearstatcache(false, $globalFile);
            $globalMtime = @filemtime($globalFile);
            if ($globalMtime !== false && $date < $globalMtime) return;
        }

        io_saveFile($globalFile, $logline, true);
    }

    /**
     * Persist a synthesized external-revision entry to the changelog
     *
     * Holds the local changelog lock around the entire detect-and-write critical section
     * (idempotency check, mtime repair, optional attic snapshot, log append) so it serializes
     * against any other writer that goes through addLogEntry(). The append uses writeLogEntry()
     * to avoid re-entering the lock we already hold.
     *
     * Returns false (without raising) when the attic write fails or another request
     * already persisted the entry. The caller falls back to the in-memory synthesized
     * entry.
     *
     * @param array $revInfo synthesized revision info
     * @return bool true if newly persisted, false otherwise
     */
    protected function persistCurrentRevisionInfo(array $revInfo)
    {
        // only the synthesized branches carry the 'timestamp' key
        if (!array_key_exists('timestamp', $revInfo)) return false;

        $logfile = $this->getChangelogFilename();
        io_lock($logfile);
        try {
            // re-read lastRev under the lock — another request may have just persisted
            $lastRev = $this->lastRevision();
            if ($lastRev !== false && $lastRev >= $revInfo['date']) {
                return false;
            }

            if ($revInfo['type'] !== DOKU_CHANGE_TYPE_DELETE) {
                if (!$this->repairExternalMtime($revInfo)) return false;
                if (!$this->saveExternalAttic($revInfo)) return false;
            }

            $this->writeLogEntry($revInfo, null, true);
            return true;
        } catch (\RuntimeException) {
            // silent fallback to in-memory synthesis
            return false;
        } finally {
            io_unlock($logfile);
        }
    }

    /**
     * Move the current file's modification time forward to the synthesized revision date when
     * the detected external change had an unreliable date (its file mtime was older than the
     * last revision, so it was dated just after that revision instead). Without this the file
     * mtime would still disagree with the changelog on the next read and the same external
     * change would be re-detected on every request.
     *
     * Only relevant for non-delete changes (a deletion has no file); the persist flow calls it
     * only in that case.
     *
     * @param array $revInfo synthesized revision info with 'date' and 'timestamp' set
     * @return bool false only if the file exists but could not be touched
     */
    protected function repairExternalMtime(array $revInfo)
    {
        // a set timestamp means the date is the real file mtime — nothing to repair
        if (!empty($revInfo['timestamp'])) return true;

        $file = $this->getFilename();
        if (!file_exists($file)) return true;

        if (!@touch($file, $revInfo['date'])) return false;
        clearstatcache(false, $file);
        return true;
    }

    /**
     * Snapshot the externally-modified content to the attic before the synthesized log entry
     * is persisted, so the revision stays retrievable. Called only for non-delete changes.
     *
     * Whether this is done depends on the item's revision scheme: pages archive every
     * revision, so they snapshot; media never archive the current revision, so they do not.
     *
     * @param array $revInfo synthesized revision info with 'date' set
     * @return bool true on success or no-op, false to abort persistence
     */
    abstract protected function saveExternalAttic(array $revInfo);

    /**
     * Byte size of the last recorded revision, used as the "before" size when computing the
     * size change of a synthesized external edit or deletion. Reads the size from that
     * revision's attic copy.
     *
     * @param int $recordedRev timestamp of the last recorded revision
     * @return int size in bytes (0 when it cannot be determined)
     */
    protected function lastRevisionSize($recordedRev)
    {
        return io_getSizeFile($this->getFilename($recordedRev));
    }

    /**
     * Whether the current item file's content is byte-identical to the stored content
     * of the given revision.
     *
     * Used to tell a real external edit apart from a mere mtime bump: when the content
     * is unchanged the file was only touched, not edited. Returns false when either file
     * is missing so detection falls back to treating the change as external.
     *
     * @param int $rev revision timestamp to compare the current file against
     * @return bool true if the content is identical
     */
    abstract protected function currentContentMatchesRevision($rev);

    /**
     * Mechanism to trace no-actual external current revision
     * @param int $rev
     */
    public function traceCurrentRevision($rev)
    {
        if ($rev > $this->lastRevision()) {
            $rev = $this->currentRevision();
        }
        return $rev;
    }
}
