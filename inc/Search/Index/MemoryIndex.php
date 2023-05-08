<?php

namespace dokuwiki\Search\Index;

use dokuwiki\Search\Exception\IndexWriteException;

/**
 * Access to a single index file
 *
 * Access using this class always happens by loading the full index into memory.
 * All modifications need to be explicitly made permanent using the save() method.
 * Should be used for small indexes that receive many changes at once.
 */
class MemoryIndex extends AbstractIndex
{
    /** @var string the raw data lines of the index, no newlines */
    protected $data;

    /** @var bool has the index been modified? */
    protected $dirty = false;

    /**
     * Loads the full contents of the index into memory
     *
     * @inheritdoc
     */
    public function __construct($idx, $suffix = '')
    {
        parent::__construct($idx, $suffix);

        $this->data = [];
        if (!file_exists($this->filename)) {
            return;
        }
        $this->data = file($this->filename, FILE_IGNORE_NEW_LINES);

    }

    /** @inheritdoc */
    public function changeRow($rid, $value)
    {
        if ($rid > count($this->data)) {
            $this->data = array_pad($this->data, $rid, '');
        }
        $this->data[$rid] = $value;
        $this->dirty = true;
    }

    /** @inheritdoc */
    public function retrieveRow($rid)
    {
        if (isset($this->data[$rid])) {
            return $this->data[$rid];
        }
        $this->changeRow($rid, ''); // add to index
        return '';
    }

    /** @inheritdoc */
    public function retrieveRows($rids)
    {
        $result = [];
        foreach ($rids as $rid) {
            if (isset($this->data[$rid])) $result[$rid] = $this->data[$rid];
        }

        return $result;
    }

    /** @inheritdoc */
    public function getRowIDs($values)
    {
        $values = array_map('trim', $values);
        $values = array_fill_keys($values, 1); // easier access as associative array

        $result = [];
        $count = count($this->data);
        for ($ln = 0; $ln < $count; $ln++) {
            $line = $this->data[$ln];
            if (isset($values[$line])) {
                $result[$line] = $ln;
                unset($values[$line]);
            }
        }

        // if there are still values, they have not been found and will be appended
        foreach (array_keys($values) as $value) {
            $this->data[] = $value;
            $result[$value] = $ln++;
            $this->dirty = true;
        }

        return $result;
    }

    /** @inheritdoc */
    public function search($re)
    {
        return preg_grep($re, $this->data);
    }

    /**
     * Save the changed index back to its file
     *
     * The method will check the internal dirty state and will only write when the index has actually been changed
     *
     * @throws IndexWriteException
     */
    public function save()
    {
        global $conf;

        if (!$this->isDirty()) {
            return;
        }

        $tempname = $this->filename . '.tmp';

        $fh = @fopen($tempname, 'w');
        if (!$fh) {
            throw new IndexWriteException("Failed to write $tempname");
        }
        fwrite($fh, implode("\n", $this->data));
        if (count($this->data)) {
            fwrite($fh, "\n");
        }
        fclose($fh);

        if ($conf['fperm']) {
            chmod($tempname, $conf['fperm']);
        }

        if (!io_rename($tempname, $this->filename)) {
            throw new IndexWriteException("Failed to write {$this->filename}");
        }

        $this->dirty = false;
    }

    /**
     * Check if the index has been modified and needs to be saved
     * @return bool
     */
    public function isDirty()
    {
        return $this->dirty;
    }
}
