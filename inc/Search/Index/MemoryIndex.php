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

    /** @inheritdoc */
    public function accessValues($values)
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
        }

        return $result;
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

}
