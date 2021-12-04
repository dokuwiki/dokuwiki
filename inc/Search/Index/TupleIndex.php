<?php

namespace dokuwiki\Search\Index;

use dokuwiki\Search\Exception\IndexWriteException;

/**
 * A single index file storing lines containing a list of tuples
 *
 * Tuples consist of a key (typically a RID from another Index) and a count.
 * Used to store page <-> word counts for example
 *
 * Access to these files always happens by loading the full index into memory.
 * All modifications need to be explicitly made permanent using the save() method.
 */
class TupleIndex extends AbstractIndex
{

    /** @var string the raw data lines of the index, no newlines */
    protected $data;

    /**
     * Loads the full contents of the index into memory
     *
     * @inheritdoc
     */
    public function __construct($idx, $suffix = '')
    {
        parent::__construct($idx, $suffix);

        $this->data = [];
        if (!file_exists($this->filename)) return;
        $this->data = file($this->filename, FILE_IGNORE_NEW_LINES);

    }

    /** @inheritdoc */
    public function changeRow($rid, $value)
    {
        if ($rid > count($this->data)) {
            $this->data = array_pad($this->data, $rid, '');
        }
        $this->data[$rid] = $value;
    }

    /** @inheritdoc */
    public function retrieveRow($rid)
    {
        if (isset($this->data[$rid])) return $this->data[$rid];
        return '';
    }

    /**
     * Save the changed index back to its file
     *
     * @throws IndexWriteException
     */
    public function save()
    {
        global $conf;

        $tempname = $this->filename . '.tmp';

        $fh = @fopen($tempname, 'w');
        if (!$fh) {
            throw new IndexWriteException("Failed to write $tempname");
        }
        fwrite($fh, implode("\n", $this->data));
        if (!empty($lines)) {
            fwrite($fh, "\n");
        }
        fclose($fh);

        if ($conf['fperm']) {
            chmod($tempname, $conf['fperm']);
        }

        if (!io_rename($tempname, $this->filename)) {
            throw new IndexWriteException("Failed to write {$this->filename}");
        }
    }

    /**
     * Insert or replace a tuple in a line
     *
     * @param string $record This is the current row value to be modified
     * @param int|string $key The foreign rid or identifier
     * @param int $count The count to store
     * @return string A new row value
     * @author Tom N Harris <tnharris@whoopdedo.org>
     *
     */
    protected function updateTuple($record, $key, $count)
    {
        if ($record != '') {
            // remove any current version of the tuple
            $record = preg_replace('/(^|:)' . preg_quote($key, '/') . '\*\d*/', '', $record);
        }
        $record = trim($record, ':');
        if ($count) {
            if ($record) {
                return "{$key}*{$count}:" . $record;
            } else {
                return "{$key}*{$count}";
            }
        }
        return $record;
    }

    /**
     * Sum the counts in a list of tuples
     *
     * @param string $record The row value to parse
     * @return int sum of all counts
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    protected function aggregateTupleCounts($record)
    {
        $freq = 0;
        $parts = explode(':', $record);
        foreach ($parts as $tuple) {
            if ($tuple === '') continue;
            list(/* $key */, $cnt) = explode('*', $tuple);
            $freq += (int)$cnt;
        }
        return $freq;
    }

    /**
     * Split a line into an array of tuples
     *
     * The given key of the given $filtermap defines which tuples to extract, the value
     * gives the name in the output array. This basically allows to map RIDs to their
     * respective real values. The result will contain the counts associated with the
     * mapped keys.
     *
     * @param string $record The row value to parse
     * @param array $filtermap Associative array of ($key => $mapping)
     * @return array mapped counts
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    protected function parseTuples($record, $filtermap)
    {
        $result = array();
        if ($record == '') return $result;
        $parts = explode(':', $record);
        foreach ($parts as $tuple) {
            if ($tuple === '') continue;
            list($key, $cnt) = explode('*', $tuple);
            if (!$cnt) continue;
            if (empty($filtermap[$key])) continue;
            $mapped = $filtermap[$key];
            $result[$mapped] = $cnt;
        }
        return $result;
    }
}
