<?php

namespace dokuwiki\Search\Index;

/**
 * Basic Building block to access individual index files
 */
abstract class AbstractIndex
{
    /** @var string name of the index */
    protected $idx;

    /** @var string $suffix of the index */
    protected $suffix;

    /** @var string full filename to the index */
    protected $filename;

    /**
     * Initialize the index
     *
     * The $suffix argument is for an index that is split into multiple parts.
     * Different index files should use different base names.
     *
     * @param string $idx name of the index
     * @param string $suffix subpart identifier
     */
    public function __construct($idx, $suffix = '')
    {
        global $conf;
        $this->filename = $conf['indexdir'] . '/' . $idx . $suffix . '.idx';
        $this->idx = $idx;
        $this->suffix = $suffix;
    }

    /**
     * @return string the full path to the underlying file
     */
    public function getFilename()
    {
        return $this->filename;
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
     * Searches the Index for a given value and adds it if not found
     *
     * Entries previously marked as deleted will be restored.
     *
     * Note the existance of an entry in the index does not say anything about the exististance
     * of the real world object (eg. a page)
     *
     * You should preferable use accessCachedValue() instead.
     *
     * @param string $value
     * @return int the RID of the entry
     */
    public function accessValue($value)
    {
        $result = $this->accessValues([$value]);
        return $result[$value];
    }

    /**
     * Searches the Index for all given values and adds them if not found
     *
     * @param string[] $values
     * @return array the RIDs of the entries
     */
    abstract public function accessValues($values);

    /**
     * Clears the index by deleting its file
     * @return void
     */
    public function clear()
    {
        @unlink($this->filename);
    }

}
