<?php
namespace dokuwiki\Search;

use dokuwiki\Utf8;


/**
 * Abstract Class DokuWiki Index
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
abstract class AbstractIndex
{
    /** @var instance of an extended AbstractIndex class */
    protected static $instance = null;

    /** @var array $pidCache Cache for getPID() */
    protected $pidCache = array();

    /**
     * AbstractIndex constructor
     * extended classes should be Singleton, prevent direct object creation
     */
    protected function __construct() {}

    /**
     * Get new or existing singleton instance of the extended AbstractIndex
     *
     * @return instance of an extended AbstractIndex class
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Clean a name of a key for use as a file name.
     *
     * Romanizes non-latin characters, then strips away anything that's
     * not a letter, number, or underscore.
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     *
     * @param string $name
     * @return string
     */
    protected function cleanName($name)
    {
        $name = Utf8\Clean::romanize(trim((string)$name));
        $name = preg_replace('#[ \./\\:-]+#', '_', $name);
        $name = preg_replace('/[^A-Za-z0-9_]/', '', $name);
        return strtolower($name);
    }

    /**
     * Get the numeric PID of a page
     *
     * @param string $page The page to get the PID for
     * @return int|bool The page id on success, false on error
     */
    public function getPID($page)
    {
        // return PID without locking when it is in the cache
        if (isset($this->pidCache[$page])) return $this->pidCache[$page];

        if (!$this->lock()) return false;

        // load known documents
        $pid = $this->getPIDNoLock($page);
        if ($pid === false) {
            $this->unlock();
            return false;
        }

        $this->unlock();
        return $pid;
    }

    /**
     * Get the numeric PID of a page without locking the index.
     * Only use this function when the index is already locked.
     *
     * @param string $page The page to get the PID for
     * @return int|bool The page id on success, false on error
     */
    public function getPIDNoLock($page)
    {
        // avoid expensive addIndexKey operation for the most recently
        // requested pages by using a cache
        if (isset($this->pidCache[$page])) return $this->pidCache[$page];
        $pid = $this->addIndexKey('page', '', $page);
        // limit cache to 10 entries by discarding the oldest element
        // as in DokuWiki usually only the most recently
        // added item will be requested again
        if (count($this->pidCache) > 10) array_shift($this->pidCache);
        $this->pidCache[$page] = $pid;
        return $pid;
    }

    /**
     * Reset pidCache
     */
    public function resetPIDCache()
    {
        $this->pidCache = array();
    }

    /**
     * Get the page id of a numeric PID
     *
     * @param int $pid The PID to get the page id for
     * @return string The page id
     */
    public function getPageFromPID($pid)
    {
        return $this->getIndexKey('page', '', $pid);
    }

    /**
     * Lock the indexer
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     *
     * @return bool|string
     */
    protected function lock()
    {
        global $conf;
        $status = true;
        $run = 0;
        $lock = $conf['lockdir'].'/_indexer.lock';
        while (!@mkdir($lock, $conf['dmode'])) {
            usleep(50);
            if (is_dir($lock) && time() - @filemtime($lock) > 60*5) {
                // looks like a stale lock - remove it
                if (!@rmdir($lock)) {
                    $status = "removing the stale lock failed";
                    return false;
                } else {
                    $status = "stale lock removed";
                }
            } elseif ($run++ == 1000) {
                // we waited 5 seconds for that lock
                return false;
            }
        }
        if (!empty($conf['dperm'])) {
            chmod($lock, $conf['dperm']);
        }
        return $status;
    }

    /**
     * Release the indexer lock
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     *
     * @return bool
     */
    protected function unlock()
    {
        global $conf;
        @rmdir($conf['lockdir'].'/_indexer.lock');
        return true;
    }

    /**
     * Retrieve the entire index
     *
     * The $suffix argument is for an index that is split into multiple parts.
     * Different index files should use different base names.
     *
     * @param string    $idx    name of the index
     * @param string    $suffix subpart identifier
     * @return array            list of lines without CR or LF
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    protected function getIndex($idx, $suffix)
    {
        global $conf;
        $fn = $conf['indexdir'].'/'.$idx.$suffix.'.idx';
        if (!file_exists($fn)) return array();
        return file($fn, FILE_IGNORE_NEW_LINES);
    }

    /**
     * Replace the contents of the index with an array
     *
     * @param string    $idx    name of the index
     * @param string    $suffix subpart identifier
     * @param array     $lines  list of lines without LF
     * @return bool             If saving succeeded
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    protected function saveIndex($idx, $suffix, $lines)
    {
        global $conf;
        $fn = $conf['indexdir'].'/'.$idx.$suffix;
        $fh = @fopen($fn.'.tmp', 'w');
        if (!$fh) return false;
        fwrite($fh, implode("\n", $lines));
        if (!empty($lines)) {
            fwrite($fh, "\n");
        }
        fclose($fh);
        if (isset($conf['fperm'])) {
            chmod($fn.'.tmp', $conf['fperm']);
        }
        io_rename($fn.'.tmp', $fn.'.idx');
        return true;
    }

    /**
     * Retrieve or insert a value in the index
     *
     * @param string    $idx    name of the index
     * @param string    $suffix subpart identifier
     * @param string    $value  line to find in the index
     * @return int|bool          line number of the value in the index
     *                           or false if writing the index failed
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    protected function addIndexKey($idx, $suffix, $value)
    {
        $index = $this->getIndex($idx, $suffix);
        $id = array_search($value, $index, true);
        if ($id === false) {
            $id = count($index);
            $index[$id] = $value;
            if (!$this->saveIndex($idx, $suffix, $index)) {
                trigger_error("Failed to write $idx index", E_USER_ERROR);
                return false;
            }
        }
        return $id;
    }

    /**
     * Write a line into the index
     *
     * @param string    $idx    name of the index
     * @param string    $suffix subpart identifier
     * @param int       $id     the line number
     * @param string    $line   line to write
     * @return bool             If saving succeeded
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    protected function saveIndexKey($idx, $suffix, $id, $line)
    {
        global $conf;
        if (substr($line, -1) !== "\n") {
            $line .= "\n";
        }
        $fn = $conf['indexdir'].'/'.$idx.$suffix;
        $fh = @fopen($fn.'.tmp', 'w');
        if (!$fh) return false;
        $ih = @fopen($fn.'.idx', 'r');
        if ($ih) {
            $ln = -1;
            while (($curline = fgets($ih)) !== false) {
                fwrite($fh, (++$ln == $id) ? $line : $curline);
            }
            if ($id > $ln) {
                while ($id > ++$ln) {
                    fwrite($fh, "\n");
                }
                fwrite($fh, $line);
            }
            fclose($ih);
        } else {
            $ln = -1;
            while ($id > ++$ln) {
                fwrite($fh, "\n");
            }
            fwrite($fh, $line);
        }
        fclose($fh);
        if (isset($conf['fperm'])) {
            chmod($fn.'.tmp', $conf['fperm']);
        }
        io_rename($fn.'.tmp', $fn.'.idx');
        return true;
    }

    /**
     * Retrieve a line from the index
     *
     * @param string    $idx    name of the index
     * @param string    $suffix subpart identifier
     * @param int       $id     the line number
     * @return string           a line with trailing whitespace removed
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    protected function getIndexKey($idx, $suffix, $id)
    {
        global $conf;
        $fn = $conf['indexdir'].'/'.$idx.$suffix.'.idx';
        if (!file_exists($fn)) return '';
        $fh = @fopen($fn, 'r');
        if (!$fh) return '';
        $ln = -1;
        while (($line = fgets($fh)) !== false) {
            if (++$ln == $id) break;
        }
        fclose($fh);
        return rtrim((string)$line);
    }

    /**
     * Insert or replace a tuple in a line
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     *
     * @param string     $line
     * @param int|string $id
     * @param int        $count
     * @return string
     */
    protected function updateTuple($line, $id, $count)
    {
        if ($line != '') {
            $line = preg_replace('/(^|:)'.preg_quote($id,'/').'\*\d*/', '', $line);
        }
        $line = trim($line, ':');
        if ($count) {
            if ($line) {
                return "$id*$count:".$line;
            } else {
                return "$id*$count";
            }
        }
        return $line;
    }

    /**
     * Split a line into an array of tuples
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param array      $keys
     * @param string     $line
     * @return array
     */
    protected function parseTuples($keys, $line)
    {
        $result = array();
        if ($line == '') return $result;
        $parts = explode(':', $line);
        foreach ($parts as $tuple) {
            if ($tuple === '') continue;
            list($key, $cnt) = explode('*', $tuple);
            if (!$cnt) continue;
            $key = $keys[$key];
            if ($key === false || is_null($key)) continue;
            $result[$key] = $cnt;
        }
        return $result;
    }

    /**
     * Sum the counts in a list of tuples
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     *
     * @param string     $line
     * @return int
     */
    protected function countTuples($line)
    {
        $freq = 0;
        $parts = explode(':', $line);
        foreach ($parts as $tuple) {
            if ($tuple === '') continue;
            list(/* $pid */, $cnt) = explode('*', $tuple);
            $freq += (int)$cnt;
        }
        return $freq;
    }


    /**
     * Return a list of words sorted by number of times used
     *
     * @param int       $min    bottom frequency threshold
     * @param int       $max    upper frequency limit. No limit if $max<$min
     * @param int       $minlen minimum length of words to count
     * @param string    $key    metadata key to list. Uses the fulltext index if not given
     * @return array            list of words as the keys and frequency as values
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    public function histogram($min=1, $max=0, $minlen=3, $key=null)
    {
        if ($min < 1)    $min = 1;
        if ($max < $min) $max = 0;

        $result = array();

        if ($key == 'title') {
            $index = $this->getIndex('title', '');
            $index = array_count_values($index);
            foreach ($index as $val => $cnt) {
                if ($cnt >= $min && (!$max || $cnt <= $max) && strlen($val) >= $minlen) {
                    $result[$val] = $cnt;
                }
            }
        } elseif (!is_null($key)) {
            $metaname = $this->cleanName($key);
            $index = $this->getIndex($metaname.'_i', '');
            $val_idx = array();
            foreach ($index as $wid => $line) {
                $freq = $this->countTuples($line);
                if ($freq >= $min && (!$max || $freq <= $max)) {
                    $val_idx[$wid] = $freq;
                }
            }
            if (!empty($val_idx)) {
                $words = $this->getIndex($metaname.'_w', '');
                foreach ($val_idx as $wid => $freq) {
                    if (strlen($words[$wid]) >= $minlen) {
                        $result[$words[$wid]] = $freq;
                    }
                }
            }
        } else {
            $lengths = $this->listIndexLengths();
            foreach ($lengths as $length) {
                if ($length < $minlen) continue;
                $index = $this->getIndex('i', $length);
                $words = null;
                foreach ($index as $wid => $line) {
                    $freq = $this->countTuples($line);
                    if ($freq >= $min && (!$max || $freq <= $max)) {
                        if ($words === null) {
                            $words = $this->getIndex('w', $length);
                        }
                        $result[$words[$wid]] = $freq;
                    }
                }
            }
        }

        arsort($result);
        return $result;
    }

    /**
     * Clear the whole index
     *
     * @return bool If the index has been cleared successfully
     */
    public function clear()
    {
        global $conf;

        if (!$this->lock()) return false;

        @unlink($conf['indexdir'].'/page.idx');
        @unlink($conf['indexdir'].'/title.idx');
        @unlink($conf['indexdir'].'/pageword.idx');
        @unlink($conf['indexdir'].'/metadata.idx');
        $dir = @opendir($conf['indexdir']);
        if ($dir !== false) {
            while (($f = readdir($dir)) !== false) {
                if (in_array($f[0], ['i', 'w']) && substr($f, -4) == '.idx') {
                    // fulltext index
                    @unlink($conf['indexdir']."/$f");
                } elseif (in_array(substr($f, -6), ['_w.idx','_i.idx','_p.idx'])) {
                    // metadata index
                    @unlink($conf['indexdir']."/$f");
                }
            }
        }
        @unlink($conf['indexdir'].'/lengths.idx');

        // clear the pid cache
        $this->resetPIDCache();

        $this->unlock();
        return true;
    }

}
