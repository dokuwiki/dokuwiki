<?php

namespace dokuwiki\ChangeLog;

use dokuwiki\Utf8\PhpString;

/**
 * Provides methods for handling of changelog
 */
trait ChangeLogTrait
{
    /**
     * Adds an entry to the changelog file
     *
     * @return array added log line as revision info
     */
    abstract public function addLogEntry(array $info, $timestamp = null);

    /**
     * Parses a changelog line into it's components
     *
     * @author Ben Coburn <btcoburn@silicodon.net>
     *
     * @param string $line changelog line
     * @return array|bool parsed line or false
     */
    public static function parseLogLine($line)
    {
        $info = explode("\t", rtrim($line, "\n"));
        if ($info !== false && count($info) > 1) {
            return [
                'date'  => (int)$info[0], // unix timestamp
                'ip'    => $info[1], // IPv4 address (127.0.0.1)
                'type'  => $info[2], // log line type
                'id'    => $info[3], // page id
                'user'  => $info[4], // user name
                'sum'   => $info[5], // edit summary (or action reason)
                'extra' => $info[6], // extra data (varies by line type)
                'sizechange' => (isset($info[7]) && $info[7] !== '') ? (int)$info[7] : null, //
            ];
        } else {
            return false;
        }
    }

    /**
     * Build a changelog line from it's components
     *
     * @param array $info Revision info structure
     * @param int $timestamp log line date (optional)
     * @return string changelog line
     */
    public static function buildLogLine(array &$info, $timestamp = null)
    {
        $strip = ["\t", "\n"];
        $entry = array(
            'date'  => $timestamp ?? $info['date'],
            'ip'    => $info['ip'],
            'type'  => str_replace($strip, '', $info['type']),
            'id'    => $info['id'],
            'user'  => $info['user'],
            'sum'   => PhpString::substr(str_replace($strip, '', $info['sum']), 0, 255),
            'extra' => str_replace($strip, '', $info['extra']),
            'sizechange' => $info['sizechange'],
        );
        $info = $entry;
        return implode("\t", $entry) ."\n";
    }

    /**
     * Returns path to changelog
     *
     * @return string path to file
     */
    abstract protected function getChangelogFilename();

    /**
     * Checks if the ID has old revisions
     * @return boolean
     */
    public function hasRevisions()
    {
        $logfile = $this->getChangelogFilename();
        return file_exists($logfile);
    }


    /** @var int */
    protected $chunk_size;

    /**
     * Set chunk size for file reading
     * Chunk size zero let read whole file at once
     *
     * @param int $chunk_size maximum block size read from file
     */
    public function setChunkSize($chunk_size)
    {
        if (!is_numeric($chunk_size)) $chunk_size = 0;

        $this->chunk_size = (int)max($chunk_size, 0);
    }

    /**
     * Returns lines from changelog.
     * If file larger than $chunk_size, only chunk is read that could contain $rev.
     *
     * When reference timestamp $rev is outside time range of changelog, readloglines() will return
     * lines in first or last chunk, but they obviously does not contain $rev.
     *
     * @param int $rev revision timestamp
     * @return array|false
     *     if success returns array(fp, array(changeloglines), $head, $tail, $eof)
     *     where fp only defined for chuck reading, needs closing.
     *     otherwise false
     */
    protected function readloglines($rev)
    {
        $file = $this->getChangelogFilename();

        if (!file_exists($file)) {
            return false;
        }

        $fp = null;
        $head = 0;
        $tail = 0;
        $eof = 0;

        if (filesize($file) < $this->chunk_size || $this->chunk_size == 0) {
            // read whole file
            $lines = file($file);
            if ($lines === false) {
                return false;
            }
        } else {
            // read by chunk
            $fp = fopen($file, 'rb'); // "file pointer"
            if ($fp === false) {
                return false;
            }
            fseek($fp, 0, SEEK_END);
            $eof = ftell($fp);
            $tail = $eof;

            // find chunk
            while ($tail - $head > $this->chunk_size) {
                $finger = $head + intval(($tail - $head) / 2);
                $finger = $this->getNewlinepointer($fp, $finger);
                $tmp = fgets($fp);
                if ($finger == $head || $finger == $tail) {
                    break;
                }
                $info = $this->parseLogLine($tmp);
                $finger_rev = $info['date'];

                if ($finger_rev > $rev) {
                    $tail = $finger;
                } else {
                    $head = $finger;
                }
            }

            if ($tail - $head < 1) {
                // could not find chunk, assume requested rev is missing
                fclose($fp);
                return false;
            }

            $lines = $this->readChunk($fp, $head, $tail);
        }
        return array(
            $fp,
            $lines,
            $head,
            $tail,
            $eof,
        );
    }

    /**
     * Read chunk and return array with lines of given chunk.
     * Has no check if $head and $tail are really at a new line
     *
     * @param resource $fp resource file pointer
     * @param int $head start point chunk
     * @param int $tail end point chunk
     * @return array lines read from chunk
     */
    protected function readChunk($fp, $head, $tail)
    {
        $chunk = '';
        $chunk_size = max($tail - $head, 0); // found chunk size
        $got = 0;
        fseek($fp, $head);
        while ($got < $chunk_size && !feof($fp)) {
            $tmp = @fread($fp, max(min($this->chunk_size, $chunk_size - $got), 0));
            if ($tmp === false) { //error state
                break;
            }
            $got += strlen($tmp);
            $chunk .= $tmp;
        }
        $lines = explode("\n", $chunk);
        array_pop($lines); // remove trailing newline
        return $lines;
    }

    /**
     * Set pointer to first new line after $finger and return its position
     *
     * @param resource $fp file pointer
     * @param int $finger a pointer
     * @return int pointer
     */
    protected function getNewlinepointer($fp, $finger)
    {
        fseek($fp, $finger);
        $nl = $finger;
        if ($finger > 0) {
            fgets($fp); // slip the finger forward to a new line
            $nl = ftell($fp);
        }
        return $nl;
    }

    /**
     * Returns the next lines of the changelog  of the chunk before head or after tail
     *
     * @param resource $fp file pointer
     * @param int $head position head of last chunk
     * @param int $tail position tail of last chunk
     * @param int $direction positive forward, negative backward
     * @return array with entries:
     *    - $lines: changelog lines of read chunk
     *    - $head: head of chunk
     *    - $tail: tail of chunk
     */
    protected function readAdjacentChunk($fp, $head, $tail, $direction)
    {
        if (!$fp) return array(array(), $head, $tail);

        if ($direction > 0) {
            //read forward
            $head = $tail;
            $tail = $head + intval($this->chunk_size * (2 / 3));
            $tail = $this->getNewlinepointer($fp, $tail);
        } else {
            //read backward
            $tail = $head;
            $head = max($tail - $this->chunk_size, 0);
            while (true) {
                $nl = $this->getNewlinepointer($fp, $head);
                // was the chunk big enough? if not, take another bite
                if ($nl > 0 && $tail <= $nl) {
                    $head = max($head - $this->chunk_size, 0);
                } else {
                    $head = $nl;
                    break;
                }
            }
        }

        //load next chunk
        $lines = $this->readChunk($fp, $head, $tail);
        return array($lines, $head, $tail);
    }

}
