<?php

namespace splitbrain\PHPArchive;

/**
 * Class Tar
 *
 * Creates or extracts Tar archives. Supports gz and bzip compression
 *
 * Long pathnames (>100 chars) are supported in POSIX ustar and GNU longlink formats. Pax
 * extended headers are understood when reading.
 *
 * File names are expected to be UTF-8 encoded. The format has no field to declare the encoding
 * of a name, so names are stored exactly as they are given and returned exactly as they are
 * stored. For archives written by current tools that means UTF-8, older archives may use any
 * encoding and can not be recognized as such.
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @package splitbrain\PHPArchive
 * @license MIT
 */
class Tar extends Archive
{
    const READ_CHUNK_SIZE = 1048576; // 1MB

    /**
     * Type flags of the archive entries this class understands when reading
     *
     * A NUL byte and '0' mark a regular file, '5' a directory, '7' a contiguous file which is
     * treated like a regular file. Entries flagged differently are skipped while reading.
     */
    const SUPPORTED_TYPEFLAGS = array("\0", '0', '5', '7');

    /**
     * Type flags of the metadata headers that describe the entry following them
     *
     * 'L' and 'K' are the GNU long file name and long link target, 'x' is a pax extended header.
     */
    const METADATA_TYPEFLAGS = array('L', 'K', 'x');

    /**
     * Largest metadata header data that is read into memory
     */
    const MAX_METADATA_SIZE = 1048576; // 1MB

    protected $file = '';
    protected $comptype = Archive::COMPRESS_AUTO;
    protected $complevel = 9;
    protected $fh;
    protected $memory = '';
    protected $closed = true;
    protected $writeaccess = false;
    protected $position = 0;
    protected $contentUntil = 0;
    protected $skipUntil = 0;
    protected $paxGlobal = array();

    /**
     * Sets the compression to use
     *
     * @param int $level Compression level (0 to 9)
     * @param int $type Type of compression to use (use COMPRESS_* constants)
     * @throws ArchiveIllegalCompressionException
     */
    public function setCompression($level = 9, $type = Archive::COMPRESS_AUTO)
    {
        $this->compressioncheck($type);
        if ($level < -1 || $level > 9) {
            throw new ArchiveIllegalCompressionException('Compression level should be between -1 and 9');
        }
        $this->comptype  = $type;
        $this->complevel = $level;
        if($level == 0) $this->comptype = Archive::COMPRESS_NONE;
        if($type == Archive::COMPRESS_NONE) $this->complevel = 0;
    }

    /**
     * Open an existing TAR file for reading
     *
     * @param string $file
     * @throws ArchiveIOException
     * @throws ArchiveIllegalCompressionException
     */
    public function open($file)
    {
        $this->file = $file;

        // update compression to mach file
        if ($this->comptype == Tar::COMPRESS_AUTO) {
            $this->setCompression($this->complevel, $this->filetype($file));
        }

        // open file handles
        if ($this->comptype === Archive::COMPRESS_GZIP) {
            $this->fh = @gzopen($this->file, 'rb');
        } elseif ($this->comptype === Archive::COMPRESS_BZIP) {
            $this->fh = @bzopen($this->file, 'r');
        } else {
            $this->fh = @fopen($this->file, 'rb');
        }

        if (!$this->fh) {
            throw new ArchiveIOException('Could not open file for reading: '.$this->file);
        }
        $this->closed = false;
        $this->position = 0;
        $this->paxGlobal = array();
    }

    /**
     * Read the contents of a TAR archive
     *
     * This function lists the files stored in the archive
     *
     * The archive is closed afer reading the contents, because rewinding is not possible in bzip2 streams.
     * Reopen the file with open() again if you want to do additional operations
     *
     * @throws ArchiveIOException
     * @throws ArchiveCorruptedException
     * @returns FileInfo[]
     */
    public function contents()
    {
        $result = array();

        foreach ($this->yieldContents() as $fileinfo) {
            $result[] = $fileinfo;
        }

        return $result;
    }

    /**
     * Read the contents of a TAR archive and return each entry using yield
     * for memory efficiency.
     *
     * @see contents()
     * @throws ArchiveIOException
     * @throws ArchiveCorruptedException
     * @returns FileInfo[]
     */
    public function yieldContents()
    {
        if ($this->closed || !$this->file) {
            throw new ArchiveIOException('Can not read from a closed archive');
        }

        while ($read = $this->readbytes(512)) {
            $header = $this->parseHeader($read);
            if (!is_array($header)) {
                continue;
            }

            $this->contentUntil = $this->position + $header['size'];
            $this->skipUntil = $this->position + ceil($header['size'] / 512) * 512;

            yield $this->header2fileinfo($header);

            $skip = $this->skipUntil - $this->position;
            if ($skip > 0) {
                $this->skipbytes($skip);
            }
        }

        $this->close();
    }

    /**
     * Reads content of a current archive entry.
     *
     * Works only when iterating trough the archive using the generator returned
     * by the yieldContents().
     *
     * @param int $length maximum number of bytes to read
     *
     * @return string
     */
    public function readCurrentEntry($length = PHP_INT_MAX)
    {
        $length = (int) min($length, $this->contentUntil - $this->position);
        if ($length === 0) {
            return '';
        }
        return $this->readbytes($length);
    }

    /**
     * Extract an existing TAR archive
     *
     * The $strip parameter allows you to strip a certain number of path components from the filenames
     * found in the tar file, similar to the --strip-components feature of GNU tar. This is triggered when
     * an integer is passed as $strip.
     * Alternatively a fixed string prefix may be passed in $strip. If the filename matches this prefix,
     * the prefix will be stripped. It is recommended to give prefixes with a trailing slash.
     *
     * By default this will extract all files found in the archive. You can restrict the output using the $include
     * and $exclude parameter. Both expect a full regular expression (including delimiters and modifiers). If
     * $include is set only files that match this expression will be extracted. Files that match the $exclude
     * expression will never be extracted. Both parameters can be used in combination. Expressions are matched against
     * stripped filenames as described above.
     *
     * The archive is closed afer reading the contents, because rewinding is not possible in bzip2 streams.
     * Reopen the file with open() again if you want to do additional operations
     *
     * @param string $outdir the target directory for extracting
     * @param int|string $strip either the number of path components or a fixed prefix to strip
     * @param string $exclude a regular expression of files to exclude
     * @param string $include a regular expression of files to include
     * @throws ArchiveIOException
     * @throws ArchiveCorruptedException
     * @return FileInfo[]
     */
    public function extract($outdir, $strip = '', $exclude = '', $include = '')
    {
        if ($this->closed || !$this->file) {
            throw new ArchiveIOException('Can not read from a closed archive');
        }

        $outdir = rtrim($outdir, '/');
        @mkdir($outdir, 0777, true);
        if (!is_dir($outdir)) {
            throw new ArchiveIOException("Could not create directory '$outdir'");
        }

        $extracted = array();
        while ($dat = $this->readbytes(512)) {
            // read the file header
            $header = $this->parseHeader($dat);
            if (!is_array($header)) {
                continue;
            }
            $fileinfo = $this->header2fileinfo($header);

            // apply strip rules
            $fileinfo->strip($strip);

            // skip unwanted files
            if (!strlen($fileinfo->getPath()) || !$fileinfo->matchExpression($include, $exclude)) {
                $this->skipbytes(ceil($header['size'] / 512) * 512);
                continue;
            }

            // create output directory
            $output    = $outdir.'/'.$fileinfo->getPath();
            $directory = ($fileinfo->getIsdir()) ? $output : dirname($output);
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }

            // extract data
            if (!$fileinfo->getIsdir()) {
                $fp = @fopen($output, "wb");
                if (!$fp) {
                    throw new ArchiveIOException('Could not open file for writing: '.$output);
                }

                $size = floor($header['size'] / 512);
                for ($i = 0; $i < $size; $i++) {
                    fwrite($fp, $this->readbytes(512), 512);
                }
                if (($header['size'] % 512) != 0) {
                    fwrite($fp, $this->readbytes(512), $header['size'] % 512);
                }

                fclose($fp);
                @touch($output, $fileinfo->getMtime());
                @chmod($output, $fileinfo->getMode());
            } else {
                $this->skipbytes(ceil($header['size'] / 512) * 512); // the size is usually 0 for directories
            }

            if(is_callable($this->callback)) {
                call_user_func($this->callback, $fileinfo);
            }
            $extracted[] = $fileinfo;
        }

        $this->close();
        return $extracted;
    }

    /**
     * Create a new TAR file
     *
     * If $file is empty, the tar file will be created in memory
     *
     * @param string $file
     * @throws ArchiveIOException
     * @throws ArchiveIllegalCompressionException
     */
    public function create($file = '')
    {
        $this->file   = $file;
        $this->memory = '';
        $this->fh     = 0;

        if ($this->file) {
            // determine compression
            if ($this->comptype == Archive::COMPRESS_AUTO) {
                $this->setCompression($this->complevel, $this->filetype($file));
            }

            if ($this->comptype === Archive::COMPRESS_GZIP) {
                $this->fh = @gzopen($this->file, 'wb'.$this->complevel);
            } elseif ($this->comptype === Archive::COMPRESS_BZIP) {
                $this->fh = @bzopen($this->file, 'w');
            } else {
                $this->fh = @fopen($this->file, 'wb');
            }

            if (!$this->fh) {
                throw new ArchiveIOException('Could not open file for writing: '.$this->file);
            }
        }
        $this->writeaccess = true;
        $this->closed      = false;
    }

    /**
     * Add a file to the current TAR archive using an existing file in the filesystem
     *
     * @param string $file path to the original file
     * @param string|FileInfo $fileinfo either the name to us in archive (string) or a FileInfo oject with all meta data, empty to take from original
     * @throws ArchiveCorruptedException when the file changes while reading it, the archive will be corrupt and should be deleted
     * @throws ArchiveIOException there was trouble reading the given file, it was not added
     * @throws FileInfoException trouble reading file info, it was not added
     */
    public function addFile($file, $fileinfo = '')
    {
        if (is_string($fileinfo)) {
            $fileinfo = FileInfo::fromPath($file, $fileinfo);
        }

        if ($this->closed) {
            throw new ArchiveIOException('Archive has been closed, files can no longer be added');
        }

        // create file header
        $this->writeFileHeader($fileinfo);

        // write data, but only if we have data to write.
        // note: on Windows fopen() on a directory will fail, so we prevent
        // errors on Windows by testing if we have data to write.
        if (!$fileinfo->getIsdir() && $fileinfo->getSize() > 0) {
            $read = 0;
            $fp = @fopen($file, 'rb');
            if (!$fp) {
                throw new ArchiveIOException('Could not open file for reading: ' . $file);
            }
            while (!feof($fp)) {
                // for performance reasons read bigger chunks at once
                $data = fread($fp, self::READ_CHUNK_SIZE);
                if ($data === false) {
                    break;
                }
                if ($data === '') {
                    break;
                }
                $dataLen = strlen($data);
                $read += $dataLen;
                // how much of data read fully fills 512-byte blocks?
                $passLen = ($dataLen >> 9) << 9;
                if ($passLen === $dataLen) {
                    // all - just write the data
                    $this->writebytes($data);
                } else {
                    // directly write what fills 512-byte blocks fully
                    $this->writebytes(substr($data, 0, $passLen));
                    // pad the reminder to 512 bytes
                    $this->writebytes(pack("a512", substr($data, $passLen)));
                }
            }
            fclose($fp);

            if ($read != $fileinfo->getSize()) {
                $this->close();
                throw new ArchiveCorruptedException("The size of $file changed while reading, archive corrupted. read $read expected ".$fileinfo->getSize());
            }
        }

        if(is_callable($this->callback)) {
            call_user_func($this->callback, $fileinfo);
        }
    }

    /**
     * Add a file to the current TAR archive using the given $data as content
     *
     * @param string|FileInfo $fileinfo either the name to us in archive (string) or a FileInfo oject with all meta data
     * @param string          $data     binary content of the file to add
     * @throws ArchiveIOException
     */
    public function addData($fileinfo, $data)
    {
        if (is_string($fileinfo)) {
            $fileinfo = new FileInfo($fileinfo);
        }

        if ($this->closed) {
            throw new ArchiveIOException('Archive has been closed, files can no longer be added');
        }

        $len = strlen($data);
        $fileinfo->setSize($len);
        $this->writeFileHeader($fileinfo);

        // write directly everything but the last block which needs padding
        $passLen = ($len >> 9) << 9;
        $this->writebytes(substr($data, 0, $passLen));
        if ($passLen < $len) {
            $this->writebytes(pack("a512", substr($data, $passLen, 512)));
        }

        if (is_callable($this->callback)) {
            call_user_func($this->callback, $fileinfo);
        }
    }

    /**
     * Add the closing footer to the archive if in write mode, close all file handles
     *
     * After a call to this function no more data can be added to the archive, for
     * read access no reading is allowed anymore
     *
     * "Physically, an archive consists of a series of file entries terminated by an end-of-archive entry, which
     * consists of two 512 blocks of zero bytes"
     *
     * @link http://www.gnu.org/software/tar/manual/html_chapter/tar_8.html#SEC134
     * @throws ArchiveIOException
     */
    public function close()
    {
        if ($this->closed) {
            return;
        } // we did this already

        // write footer
        if ($this->writeaccess) {
            $this->writebytes(pack("a512", ""));
            $this->writebytes(pack("a512", ""));
        }

        // close file handles
        if ($this->file) {
            if ($this->comptype === Archive::COMPRESS_GZIP) {
                gzclose($this->fh);
            } elseif ($this->comptype === Archive::COMPRESS_BZIP) {
                bzclose($this->fh);
            } else {
                fclose($this->fh);
            }

            $this->file = '';
            $this->fh   = 0;
        }

        $this->writeaccess = false;
        $this->closed      = true;
    }

    /**
     * Returns the created in-memory archive data
     *
     * This implicitly calls close() on the Archive
     * @throws ArchiveIOException
     */
    public function getArchive()
    {
        $this->close();

        if ($this->comptype === Archive::COMPRESS_AUTO) {
            $this->comptype = Archive::COMPRESS_NONE;
        }

        if ($this->comptype === Archive::COMPRESS_GZIP) {
            return gzencode($this->memory, $this->complevel);
        }
        if ($this->comptype === Archive::COMPRESS_BZIP) {
            return bzcompress($this->memory);
        }
        return $this->memory;
    }

    /**
     * Save the created in-memory archive data
     *
     * Note: It more memory effective to specify the filename in the create() function and
     * let the library work on the new file directly.
     *
     * @param string $file
     * @throws ArchiveIOException
     * @throws ArchiveIllegalCompressionException
     */
    public function save($file)
    {
        if ($this->comptype === Archive::COMPRESS_AUTO) {
            $this->setCompression($this->complevel, $this->filetype($file));
        }

        if (!@file_put_contents($file, $this->getArchive())) {
            throw new ArchiveIOException('Could not write to file: '.$file);
        }
    }

    /**
     * Read from the open file pointer
     *
     * @param int $length bytes to read
     * @return string
     */
    protected function readbytes($length)
    {
        if ($this->comptype === Archive::COMPRESS_GZIP) {
            $ret = @gzread($this->fh, $length);
        } elseif ($this->comptype === Archive::COMPRESS_BZIP) {
            $ret = @bzread($this->fh, $length);
        } else {
            $ret = @fread($this->fh, $length);
        }
        $this->position += strlen($ret);
        return $ret;
    }

    /**
     * Write to the open filepointer or memory
     *
     * @param string $data
     * @throws ArchiveIOException
     * @return int number of bytes written
     */
    protected function writebytes($data)
    {
        if (!$this->file) {
            $this->memory .= $data;
            $written = strlen($data);
        } elseif ($this->comptype === Archive::COMPRESS_GZIP) {
            $written = @gzwrite($this->fh, $data);
        } elseif ($this->comptype === Archive::COMPRESS_BZIP) {
            $written = @bzwrite($this->fh, $data);
        } else {
            $written = @fwrite($this->fh, $data);
        }
        if ($written === false) {
            throw new ArchiveIOException('Failed to write to archive stream');
        }
        return $written;
    }

    /**
     * Skip forward in the open file pointer
     *
     * This is basically a wrapper around seek() (and a workaround for bzip2)
     *
     * @param int $bytes seek to this position
     */
    protected function skipbytes($bytes)
    {
        if ($this->comptype === Archive::COMPRESS_GZIP) {
            @gzseek($this->fh, $bytes, SEEK_CUR);
        } elseif ($this->comptype === Archive::COMPRESS_BZIP) {
            // there is no seek in bzip2, we simply read on
            // bzread allows to read a max of 8kb at once
            while($bytes) {
                $toread = min(8192, $bytes);
                @bzread($this->fh, $toread);
                $bytes -= $toread;
            }
        } else {
            @fseek($this->fh, $bytes, SEEK_CUR);
        }
        $this->position += $bytes;
    }

    /**
     * Write the given file meta data as header
     *
     * @param FileInfo $fileinfo
     * @throws ArchiveIOException
     */
    protected function writeFileHeader(FileInfo $fileinfo)
    {
        $this->writeRawFileHeader(
            $fileinfo->getPath(),
            $fileinfo->getUid(),
            $fileinfo->getGid(),
            $fileinfo->getMode(),
            $fileinfo->getSize(),
            $fileinfo->getMtime(),
            $fileinfo->getIsdir() ? '5' : '0'
        );
    }

    /**
     * Write a file header to the stream
     *
     * @param string $name
     * @param int $uid
     * @param int $gid
     * @param int $perm
     * @param int $size
     * @param int $mtime
     * @param string $typeflag Set to '5' for directories
     * @throws ArchiveIOException
     */
    protected function writeRawFileHeader($name, $uid, $gid, $perm, $size, $mtime, $typeflag = '')
    {
        // handle filename length restrictions
        $prefix  = '';
        $namelen = strlen($name);
        if ($namelen > 100) {
            $file = basename($name);
            $dir  = dirname($name);
            if (strlen($file) > 100 || strlen($dir) > 155) {
                // we're still too large, let's use GNU longlink
                $this->writeRawFileHeader('././@LongLink', 0, 0, 0, $namelen, 0, 'L');
                for ($s = 0; $s < $namelen; $s += 512) {
                    $this->writebytes(pack("a512", substr($name, $s, 512)));
                }
                $name = substr($name, 0, 100); // cut off name
            } else {
                // we're fine when splitting, use POSIX ustar
                $prefix = $dir;
                $name   = $file;
            }
        }

        // values are needed in octal
        $uid   = sprintf("%6s ", decoct($uid));
        $gid   = sprintf("%6s ", decoct($gid));
        $perm  = sprintf("%6s ", decoct($perm));
        $size  = self::numberEncode($size, 12);
        $mtime = self::numberEncode($mtime, 12);

        $data_first = pack("a100a8a8a8a12A12", $name, $perm, $uid, $gid, $size, $mtime);
        $data_last  = pack("a1a100a6a2a32a32a8a8a155a12", $typeflag, '', 'ustar', '', '', '', '', '', $prefix, "");

        for ($i = 0, $chks = 0; $i < 148; $i++) {
            $chks += ord($data_first[$i]);
        }

        for ($i = 156, $chks += 256, $j = 0; $i < 512; $i++, $j++) {
            $chks += ord($data_last[$j]);
        }

        $this->writebytes($data_first);

        $chks = pack("a8", sprintf("%6s ", decoct($chks)));
        $this->writebytes($chks.$data_last);
    }

    /**
     * Parse the header of an archive entry
     *
     * Metadata headers preceding an entry are resolved and applied to the entry they describe.
     * Entries that can not be represented as a FileInfo are consumed and reported as no entry:
     * their type flag either marks pure metadata (like a pax global header) or an entry type
     * that is not supported (like links or sparse files).
     *
     * @param string $block a 512 byte block containing the header data
     * @return array|false returns false when this block held no usable entry
     * @throws ArchiveCorruptedException
     */
    protected function parseHeader($block)
    {
        $header = $this->decodeHeader($block);
        if ($header === false) {
            return false;
        }

        // a pax global header holds defaults for all entries following it
        if ($header['typeflag'] === 'g') {
            $records = $this->parsePaxRecords($this->readMetadata($header['size']));
            $this->paxGlobal = array_merge($this->paxGlobal, $records);
            return false;
        }

        $filename = '';
        $records = array();

        // resolve the metadata headers in front of the entry they describe
        while (in_array($header['typeflag'], self::METADATA_TYPEFLAGS, true)) {
            $data = $this->readMetadata($header['size']);
            if ($header['typeflag'] === 'L') {
                $filename = trim($data);
            } elseif ($header['typeflag'] === 'x') {
                $records = array_merge($records, $this->parsePaxRecords($data));
            } // 'K' holds a long link target, links are not supported anyway

            $header = $this->decodeHeader($this->readbytes(512));
            if ($header === false) {
                return false;
            }
        }

        if ($filename !== '') {
            $header['filename'] = $filename;
        }
        // entry specific records win over the global ones
        $header = $this->applyPaxRecords($header, array_merge($this->paxGlobal, $records));

        if (!in_array($header['typeflag'], self::SUPPORTED_TYPEFLAGS, true)) {
            // the data blocks hold metadata or an unsupported entry, throw them away
            $this->skipbytes(ceil($header['size'] / 512) * 512);
            return false;
        }

        return $header;
    }

    /**
     * Read the data blocks of a metadata header
     *
     * Metadata beyond any sensible size is skipped instead of being loaded into memory, it can
     * only come from a broken or hostile archive. The entry then keeps what its own header says.
     *
     * @param int $size the size as given in the metadata header
     * @return string the data without the padding of the last block, empty when it was skipped
     */
    protected function readMetadata($size)
    {
        if ($size <= 0) {
            return '';
        }
        if ($size > self::MAX_METADATA_SIZE) {
            $this->skipbytes(ceil($size / 512) * 512);
            return '';
        }
        return substr($this->readbytes(ceil($size / 512) * 512), 0, $size);
    }

    /**
     * Split the data of a pax header into its records
     *
     * Each record is stored as "<length> <keyword>=<value>\n" with length being the length of
     * the whole record. Parsing stops at the first malformed record.
     *
     * @param string $data the data of a pax header
     * @return string[] record values indexed by their keyword
     */
    protected function parsePaxRecords($data)
    {
        $records = array();
        $length = strlen($data);
        $position = 0;

        while ($position < $length) {
            $space = strpos($data, ' ', $position);
            if ($space === false) {
                break;
            }

            $digits = $space - $position;
            $reclen = (int)substr($data, $position, $digits);
            if ($reclen <= $digits + 2 || $position + $reclen > $length) {
                break;
            }

            // the record is the keyword and value between the space and the trailing newline
            $record = substr($data, $space + 1, $reclen - $digits - 2);
            $equals = strpos($record, '=');
            if ($equals !== false) {
                $records[substr($record, 0, $equals)] = substr($record, $equals + 1);
            }

            $position += $reclen;
        }

        return $records;
    }

    /**
     * Apply the given pax records to a decoded header
     *
     * A record with an empty value deletes what the entry's own header provides, that is how a
     * global record is suppressed for a single entry. Numbers can not be deleted, values that are
     * no numbers are ignored. Records without a counterpart in the header, like access times or
     * link targets, are ignored as well.
     *
     * @param array $header the decoded header of the entry the records belong to
     * @param string[] $records record values indexed by their keyword
     * @return array the header with the records applied
     */
    protected function applyPaxRecords($header, $records)
    {
        $strings = array_intersect_key($records, array_flip(array('uname', 'gname')));
        if (isset($records['path'])) {
            $strings['filename'] = $records['path']; // the only keyword the header names differently
        }

        $numbers = array_intersect_key($records, array_flip(array('size', 'mtime', 'uid', 'gid')));
        $numbers = array_map('intval', array_filter($numbers, 'is_numeric'));

        return array_merge($header, $strings, $numbers);
    }

    /**
     * Decode the given tar file header
     *
     * @param string $block a 512 byte block containing the header data
     * @return array|false returns false when this was a null block
     * @throws ArchiveCorruptedException
     */
    protected function decodeHeader($block)
    {
        if (!$block || strlen($block) != 512) {
            throw new ArchiveCorruptedException('Unexpected length of header');
        }

        // null byte blocks are ignored
        if(trim($block) === '') return false;

        for ($i = 0, $chks = 0; $i < 148; $i++) {
            $chks += ord($block[$i]);
        }

        for ($i = 156, $chks += 256; $i < 512; $i++) {
            $chks += ord($block[$i]);
        }

        $header = @unpack(
            "a100filename/a8perm/a8uid/a8gid/a12size/a12mtime/a8checksum/a1typeflag/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor/a155prefix",
            $block
        );
        if (!$header) {
            throw new ArchiveCorruptedException('Failed to parse header');
        }

        $return['checksum'] = OctDec(trim($header['checksum']));
        if ($return['checksum'] != $chks) {
            throw new ArchiveCorruptedException('Header does not match its checksum');
        }

        $return['filename'] = trim($header['filename']);
        $return['perm']     = OctDec(trim($header['perm']));
        $return['uid']      = OctDec(trim($header['uid']));
        $return['gid']      = OctDec(trim($header['gid']));
        $return['size']     = self::numberDecode($header['size']);
        $return['mtime']    = self::numberDecode($header['mtime']);
        $return['typeflag'] = $header['typeflag'];
        $return['link']     = trim($header['link']);
        $return['uname']    = trim($header['uname']);
        $return['gname']    = trim($header['gname']);

        // Handle ustar Posix compliant path prefixes
        if (trim($header['prefix'])) {
            $return['filename'] = trim($header['prefix']).'/'.$return['filename'];
        }

        return $return;
    }

    /**
     * Creates a FileInfo object from the given parsed header
     *
     * @param $header
     * @return FileInfo
     */
    protected function header2fileinfo($header)
    {
        $fileinfo = new FileInfo();
        $fileinfo->setPath($header['filename']);
        $fileinfo->setMode($header['perm']);
        $fileinfo->setUid($header['uid']);
        $fileinfo->setGid($header['gid']);
        $fileinfo->setSize($header['size']);
        $fileinfo->setMtime($header['mtime']);
        $fileinfo->setOwner($header['uname']);
        $fileinfo->setGroup($header['gname']);
        $fileinfo->setIsdir($header['typeflag'] === '5');

        return $fileinfo;
    }

    /**
     * Checks if the given compression type is available and throws an exception if not
     *
     * @param $comptype
     * @throws ArchiveIllegalCompressionException
     */
    protected function compressioncheck($comptype)
    {
        if ($comptype === Archive::COMPRESS_GZIP && !function_exists('gzopen')) {
            throw new ArchiveIllegalCompressionException('No gzip support available');
        }

        if ($comptype === Archive::COMPRESS_BZIP && !function_exists('bzopen')) {
            throw new ArchiveIllegalCompressionException('No bzip2 support available');
        }
    }

    /**
     * Guesses the wanted compression from the given file
     *
     * Uses magic bytes for existing files, the file extension otherwise
     *
     * You don't need to call this yourself. It's used when you pass Archive::COMPRESS_AUTO somewhere
     *
     * @param string $file
     * @return int
     */
    public function filetype($file)
    {
        // for existing files, try to read the magic bytes
        if(file_exists($file) && is_readable($file) && filesize($file) > 5) {
            $fh = @fopen($file, 'rb');
            if(!$fh) return false;
            $magic = fread($fh, 5);
            fclose($fh);

            if(strpos($magic, "\x42\x5a") === 0) return Archive::COMPRESS_BZIP;
            if(strpos($magic, "\x1f\x8b") === 0) return Archive::COMPRESS_GZIP;
        }

        // otherwise rely on file name
        $file = strtolower($file);
        if (substr($file, -3) == '.gz' || substr($file, -4) == '.tgz') {
            return Archive::COMPRESS_GZIP;
        } elseif (substr($file, -4) == '.bz2' || substr($file, -4) == '.tbz') {
            return Archive::COMPRESS_BZIP;
        }

        return Archive::COMPRESS_NONE;
    }

    /**
     * Decodes numeric values according to the 
     * https://www.gnu.org/software/tar/manual/html_node/Extensions.html#Extensions
     * (basically with support for big numbers)
     *
     * @param string $field
     * $return int
     */
    static public function numberDecode($field)
    {
        $firstByte = ord(substr($field, 0, 1));
        if ($firstByte === 255) {
            $value = -1 << (8 * strlen($field));
            $shift = 0;
            for ($i = strlen($field) - 1; $i >= 0; $i--) {
                $value += ord(substr($field, $i, 1)) << $shift;
                $shift += 8;
            }
        } elseif ($firstByte === 128) {
            $value = 0;
            $shift = 0;
            for ($i = strlen($field) - 1; $i > 0; $i--) {
                $value += ord(substr($field, $i, 1)) << $shift;
                $shift += 8;
            }
        } else {
            $value = octdec(trim($field));
        }
        return $value;
    }

    /**
     * Encodes numeric values according to the
     * https://www.gnu.org/software/tar/manual/html_node/Extensions.html#Extensions
     * (basically with support for big numbers)
     *
     * @param int $value
     * @param int $length field length
     * @return string
     */
    static public function numberEncode($value, $length)
    {
        // old implementations leave last byte empty
        // octal encoding encodes three bits per byte
        $maxValue = 1 << (($length - 1) * 3);
        if ($value < 0) {
            // PHP already stores integers as 2's complement
            $value = pack(PHP_INT_SIZE === 8 ? 'J' : 'N', (int) $value);
            $encoded = str_repeat(chr(255), max(1, $length - PHP_INT_SIZE));
            $encoded .= substr($value, max(0, PHP_INT_SIZE - $length + 1));
        } elseif ($value >= $maxValue) {
            $value = pack(PHP_INT_SIZE === 8 ? 'J' : 'N', (int) $value);
            $encoded = chr(128) . str_repeat(chr(0), max(0, $length - PHP_INT_SIZE - 1));
            $encoded .= substr($value, max(0, PHP_INT_SIZE - $length + 1));
        } else {
            $encoded = sprintf("%" . ($length - 1) . "s ", decoct($value));
        }
        return $encoded;
    }
}

