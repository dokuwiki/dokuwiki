<?php

namespace dokuwiki\Search\Index;

use dokuwiki\Search\Exception\IndexAccessException;
use dokuwiki\Search\Exception\IndexWriteException;

/**
 * A single index file containing one key per line
 *
 * Access to this index happens only on a line-by-line basis. It is usually not read in full.
 */
class RowIndex extends AbstractIndex
{
    /* entries will be marked as deleted in index */
    const INDEX_MARK_DELETED = '#deleted:';

    /** @var array RID cache for faster access */
    protected static $ridCache = [];

    /**
     * @inheritdoc
     * @throws IndexWriteException
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    public function changeRow($rid, $value)
    {
        global $conf;

        if (substr($value, -1) !== "\n") {
            $value .= "\n";
        }

        $tempname = $this->filename . '.tmp';
        $fh = @fopen($tempname, 'w');
        if (!$fh) throw new IndexWriteException("Failed to write {$tempname}");
        $ih = @fopen($this->filename, 'r');

        $ln = -1; // line counter
        // copy previous index lines line-by-line, replacing the wanted line
        if ($ih) {
            while (($curline = fgets($ih)) !== false) {
                fwrite($fh, (++$ln == $rid) ? $value : $curline);
            }
            fclose($ih);
        }
        // if wanted line is beyond the current line count, insert empty lines inbetween
        if ($rid > $ln) {
            while ($rid > ++$ln) {
                fwrite($fh, "\n");
            }
            fwrite($fh, $value);
        }
        fclose($fh);

        if ($conf['fperm']) {
            chmod($tempname, $conf['fperm']);
        }
        io_rename($tempname, $this->filename);
    }

    /**
     * @inheritdoc
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    public function retrieveRow($rid)
    {
        if (!file_exists($this->filename)) return '';
        $fh = @fopen($this->filename, 'r');
        if (!$fh) return '';
        $ln = -1;
        while (($line = fgets($fh)) !== false) {
            if (++$ln == $rid) break;
        }
        fclose($fh);
        return rtrim((string)$line);
    }

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
     * @throws IndexAccessException
     * @throws IndexWriteException
     */
    public function accessValue($value)
    {
        $value = trim($value);
        $deleted = self::INDEX_MARK_DELETED . $value;

        // search for the value
        $ln = 0;
        if (file_exists($this->filename)) {
            $fh = @fopen($this->filename, 'r');
            if (!$fh) throw new IndexAccessException("Failed to read {$this->filename}");
            while (($line = fgets($fh)) !== false) {
                $line = trim($line);
                if ($line === $value) {
                    fclose($fh);
                    return $ln; // value has been found
                }
                if ($line === $deleted) {
                    fclose($fh);
                    $this->changeRow($ln, $value); // undelete row
                    return $ln;
                }
                $ln++;
            }
            fclose($fh);
        }

        // if we're still here, the value has not been found and will be appended
        file_put_contents($this->filename, "$value\n", FILE_APPEND);
        return $ln;
    }

    /**
     * Cached version of accessCachedValue()
     *
     * @param string $value
     * @return int the RID of the entry
     * @throws IndexAccessException
     * @throws IndexWriteException
     */
    public function accessCachedValue($value)
    {
        if (isset(static::$ridCache['value'])) return static::$ridCache['value'];

        // limit cache to 10 entries by discarding the oldest element
        // as in DokuWiki usually only the most recently
        // added item will be requested again
        if (count(static::$ridCache) > 10) array_shift(static::$ridCache);
        static::$ridCache[$value] = $this->accessValue($value);
        return static::$ridCache[$value];
    }
}
