<?php

namespace dokuwiki\Search\Index;

use dokuwiki\Search\Exception\IndexLockException;

/**
 * Basic building block to access individual index files
 *
 * To be able to write to an index, a lock must be acquired.
 *
 * Indexes are iterable, yielding RID => value pairs.
 */
abstract class AbstractIndex implements \IteratorAggregate, \Countable
{
    /** @var string name of the index */
    protected $idx;

    /** @var string suffix of the index */
    protected $suffix;

    /** @var string full filename to the index */
    protected $filename;

    /** @var bool has this instance acquired a lock? */
    protected $isWritable = false;

    /**
     * Initialize the index
     *
     * The $suffix argument is for an index that is split into multiple parts.
     * Different index files should use different base names.
     *
     * When $isWritable is true, a lock is acquired immediately
     *
     * @param string $idx name of the index
     * @param string $suffix subpart identifier
     * @param bool $isWritable acquire a lock immediately?
     * @throws IndexLockException
     */
    public function __construct($idx, $suffix = '', $isWritable = false)
    {
        global $conf;
        $this->filename = $conf['indexdir'] . '/' . $idx . $suffix . '.idx';
        $this->idx = $idx;
        $this->suffix = $suffix;
        if ($isWritable) $this->lock();
    }

    /**
     * Make this index writable by acquiring the lock
     *
     * @throws IndexLockException
     */
    public function lock()
    {
        if ($this->isWritable) return;
        Lock::acquire($this->idx);
        $this->isWritable = true;
    }

    /**
     * Make this index read-only by releasing the lock
     *
     * Decrements the reference count in the Lock registry. The filesystem
     * lock is only removed when the count reaches zero.
     */
    public function unlock()
    {
        if (!$this->isWritable) return;
        Lock::release($this->idx);
        $this->isWritable = false;
    }

    /**
     * Whether this index instance is writable
     *
     * @return bool
     */
    public function isWritable()
    {
        return $this->isWritable;
    }

    /**
     * Ensure lock is released when the index is destroyed
     */
    public function __destruct()
    {
        $this->unlock();
    }

    /**
     * @return string the full path to the underlying file
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Does this index exist, yet?
     *
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->getFilename());
    }

    /**
     * Return the largest numeric suffix for the current index
     *
     * This is only useful for indexes that use integer based suffixes (like the wordlength indexes)
     *
     * @return int 0 if no numeric suffix indexes are found
     */
    public function max()
    {
        global $conf;
        $result = 0;
        $files = glob($conf['indexdir'] . '/' . $this->idx . '*.idx');
        foreach ($files as $file) {
            if (preg_match('/(\d)+\.idx$/', $file, $match)) {
                $num = (int)$match[1];
                if ($num > $result) $result = $num;
            }
        }

        return $result;
    }

    /**
     * Change a line in the index
     *
     * If the line doesn't exist, it will be added, creating empty
     * lines inbetween as necessary
     *
     * @param int $rid the line number, count starting at 0
     * @param string $value line content to write
     */
    abstract public function changeRow($rid, $value);

    /**
     * Retrieve a line from the index
     *
     * Returns an empty string for non-existing lines
     *
     * @param int $rid the line number
     * @return string a line with trailing whitespace removed
     */
    abstract public function retrieveRow($rid);

    /**
     * Retrieve multiple lines from the index
     *
     * Ignores non-existing lines, eg the result array may be smaller than the input $rids
     *
     * @param int[] $rids
     * @return array [rid => value]
     */
    abstract public function retrieveRows($rids);

    /**
     * Searches the Index for a given value
     *
     * If the index is writable and the value is not found it will be added. Otherwise null is returned.
     *
     * Note the existence of an entry in the index does not say anything about the existence
     * of the real world object (eg. a page)
     *
     * You should preferably use accessCachedValue() instead.
     *
     * @param string $value
     *
     * @return int|null the RID of the entry, null if not found and not added
     */
    public function getRowID($value)
    {
        $result = $this->getRowIDs([$value]);
        return $result[$value] ?? null;
    }

    /**
     * Searches the Index for all given values
     *
     * If the index is writable, not found values are added
     *
     * @param string[] $values
     * @return array the RIDs of the entries (value => rid)
     */
    abstract public function getRowIDs($values);

    /**
     * Find all RIDs matching a regular expression
     *
     * A full regular expression including delimiters and modifiers is expected.
     *
     * For searching across collections, prefer using CollectionSearch which scans each
     * index only once for all terms instead of once per term.
     *
     * @param string $re the regular expression to match against
     * @return array (rid => value)
     */
    abstract public function search($re);

    /**
     * Clears the index by deleting its file
     *
     * @return void
     */
    public function clear()
    {
        @unlink($this->filename);
    }

    /**
     * Return the number of lines in the index
     *
     * @return int
     */
    abstract public function count(): int;

    /**
     * Saves the index if needed
     *
     * The default implementation does nothing and is only for streamlining the API of
     * the different index classes
     *
     * @return void
     */
    public function save()
    {
    }
}
