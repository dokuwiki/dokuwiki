<?php

namespace dokuwiki\Search\Index;

use dokuwiki\Search\Exception\IndexAccessException;
use dokuwiki\Search\Exception\IndexLockException;
use dokuwiki\Search\Exception\IndexWriteException;

/**
 * Access to a single index file
 *
 * Access using this class always happens on a line-by-line basis. It is usually not read in full.
 * All modifications are implicitly saved
 * Should be used for large indexes that receive only few changes at once.
 */
class FileIndex extends AbstractIndex
{
    /** @var array RID cache for faster access */
    protected array $ridCache = [];

    /**
     * @inheritdoc
     * @throws IndexWriteException
     * @throws IndexLockException
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    public function changeRow(int $rid, string $value): void
    {
        global $conf;

        if (!$this->isWritable) throw new IndexLockException();

        if (!str_ends_with($value, "\n")) {
            $value .= "\n";
        }

        $tempname = $this->filename . '.tmp';
        $fh = @fopen($tempname, 'w');
        if (!$fh) {
            throw new IndexWriteException("Failed to write $tempname");
        }
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
     *
     * When writable and the requested RID is beyond the end of the file,
     * the file is padded with empty lines up to that RID. This avoids
     * a more expensive line-by-line copy in a subsequent changeRow() call.
     *
     * @throws IndexWriteException
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    public function retrieveRow(int $rid): string
    {
        if (!file_exists($this->filename)) {
            return '';
        }
        $fh = @fopen($this->filename, 'r');
        if (!$fh) {
            return '';
        }
        $ln = -1;
        while (($line = fgets($fh)) !== false) {
            if (++$ln == $rid) {
                fclose($fh);
                return rtrim($line);
            }
        }
        fclose($fh);

        if (!$this->isWritable) return '';

        // still here? pad the index for the given ID
        // we do not simply call changeRow() here because appending is faster than line-by-line copying
        if (!file_put_contents($this->filename, implode("\n", array_fill(0, $rid - $ln + 1, '')), FILE_APPEND)) {
            throw new IndexWriteException("Failed to write $this->filename");
        }

        return '';
    }

    /** @inheritdoc */
    public function retrieveRows(array $rids): array
    {
        $result = [];
        sort($rids);
        $next = array_shift($rids);

        if (!file_exists($this->filename)) {
            return $result;
        }
        $fh = @fopen($this->filename, 'r');
        if (!$fh) {
            return $result;
        }
        $ln = -1;
        while (($line = fgets($fh)) !== false) {
            if (++$ln === $next) {
                $result[$ln] = rtrim($line);
                $next = array_shift($rids);
                if ($next === false) break;
            }
        }
        fclose($fh);
        return $result;
    }


    /**
     * @inheritdoc
     * @throws IndexAccessException
     * @throws IndexWriteException
     */
    public function getRowIDs(array $values): array
    {
        $values = array_map(trim(...), $values);
        $values = array_fill_keys($values, 1); // easier access as associative array

        // search for the values
        $result = [];
        $ln = 0;
        if (file_exists($this->filename)) {
            $fh = @fopen($this->filename, 'r');
            if (!$fh) {
                throw new IndexAccessException("Failed to read $this->filename");
            }
            while (($line = fgets($fh)) !== false && $values) {
                $line = trim($line);
                if (isset($values[$line])) {
                    $result[$line] = $ln;
                    unset($values[$line]);
                }
                $ln++;
            }
            fclose($fh);
        }

        if (!$this->isWritable) return $result;

        // if there are still values, they have not been found and will be appended
        foreach (array_keys($values) as $value) {
            if (!file_put_contents($this->filename, "$value\n", FILE_APPEND)) {
                throw new IndexWriteException("Failed to write $this->filename");
            }
            $result[$value] = $ln++;
        }

        return $result;
    }

    /** @inheritdoc */
    public function search(string $re): array
    {
        $result = [];
        $ln = 0;
        if (file_exists($this->filename)) {
            $fh = @fopen($this->filename, 'r');
            if (!$fh) {
                throw new IndexAccessException("Failed to read $this->filename");
            }
            while (($line = fgets($fh)) !== false) {
                $line = trim($line);
                if (preg_match($re, $line)) {
                    $result[$ln] = $line;
                }
                $ln++;
            }
            fclose($fh);
        }
        return $result;
    }

    /**
     * Cached mechanism to retrieve a single value
     *
     * @param string $value
     * @return int the RID of the entry
     * @see getRowID()
     */
    public function accessCachedValue(string $value): int
    {
        if (isset($this->ridCache[$value])) {
            return $this->ridCache[$value];
        }

        // limit cache to 10 entries by discarding the oldest element
        // as in DokuWiki usually only the most recently
        // added item will be requested again
        if (count($this->ridCache) > 10) {
            array_shift($this->ridCache);
        }
        $this->ridCache[$value] = $this->getRowID($value);
        return $this->ridCache[$value];
    }

    /** @inheritdoc */
    public function count(): int
    {
        if (!file_exists($this->filename)) return 0;
        $fh = @fopen($this->filename, 'r');
        if (!$fh) return 0;
        $count = 0;
        while (fgets($fh) !== false) $count++;
        fclose($fh);
        return $count;
    }

    /** @inheritdoc */
    public function getIterator(): \Generator
    {
        if (!file_exists($this->filename)) return;
        $fh = @fopen($this->filename, 'r');
        if (!$fh) return;
        $ln = 0;
        while (($line = fgets($fh)) !== false) {
            yield $ln++ => rtrim($line);
        }
        fclose($fh);
    }
}
