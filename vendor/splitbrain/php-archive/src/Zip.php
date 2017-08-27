<?php

namespace splitbrain\PHPArchive;

/**
 * Class Zip
 *
 * Creates or extracts Zip archives
 *
 * for specs see http://www.pkware.com/appnote
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @package splitbrain\PHPArchive
 * @license MIT
 */
class Zip extends Archive
{

    protected $file = '';
    protected $fh;
    protected $memory = '';
    protected $closed = true;
    protected $writeaccess = false;
    protected $ctrl_dir;
    protected $complevel = 9;

    /**
     * Set the compression level.
     *
     * Compression Type is ignored for ZIP
     *
     * You can call this function before adding each file to set differen compression levels
     * for each file.
     *
     * @param int $level Compression level (0 to 9)
     * @param int $type  Type of compression to use ignored for ZIP
     * @return mixed
     */
    public function setCompression($level = 9, $type = Archive::COMPRESS_AUTO)
    {
        $this->complevel = $level;
    }

    /**
     * Open an existing ZIP file for reading
     *
     * @param string $file
     * @throws ArchiveIOException
     */
    public function open($file)
    {
        $this->file = $file;
        $this->fh   = @fopen($this->file, 'rb');
        if (!$this->fh) {
            throw new ArchiveIOException('Could not open file for reading: '.$this->file);
        }
        $this->closed = false;
    }

    /**
     * Read the contents of a ZIP archive
     *
     * This function lists the files stored in the archive, and returns an indexed array of FileInfo objects
     *
     * The archive is closed afer reading the contents, for API compatibility with TAR files
     * Reopen the file with open() again if you want to do additional operations
     *
     * @throws ArchiveIOException
     * @return FileInfo[]
     */
    public function contents()
    {
        if ($this->closed || !$this->file) {
            throw new ArchiveIOException('Can not read from a closed archive');
        }

        $result = array();

        $centd = $this->readCentralDir();

        @rewind($this->fh);
        @fseek($this->fh, $centd['offset']);

        for ($i = 0; $i < $centd['entries']; $i++) {
            $result[] = $this->header2fileinfo($this->readCentralFileHeader());
        }

        $this->close();
        return $result;
    }

    /**
     * Extract an existing ZIP archive
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
     * @param string     $outdir  the target directory for extracting
     * @param int|string $strip   either the number of path components or a fixed prefix to strip
     * @param string     $exclude a regular expression of files to exclude
     * @param string     $include a regular expression of files to include
     * @throws ArchiveIOException
     * @return FileInfo[]
     */
    function extract($outdir, $strip = '', $exclude = '', $include = '')
    {
        if ($this->closed || !$this->file) {
            throw new ArchiveIOException('Can not read from a closed archive');
        }

        $outdir = rtrim($outdir, '/');
        @mkdir($outdir, 0777, true);

        $extracted = array();

        $cdir      = $this->readCentralDir();
        $pos_entry = $cdir['offset']; // begin of the central file directory

        for ($i = 0; $i < $cdir['entries']; $i++) {
            // read file header
            @fseek($this->fh, $pos_entry);
            $header          = $this->readCentralFileHeader();
            $header['index'] = $i;
            $pos_entry       = ftell($this->fh); // position of the next file in central file directory
            fseek($this->fh, $header['offset']); // seek to beginning of file header
            $header   = $this->readFileHeader($header);
            $fileinfo = $this->header2fileinfo($header);

            // apply strip rules
            $fileinfo->strip($strip);

            // skip unwanted files
            if (!strlen($fileinfo->getPath()) || !$fileinfo->match($include, $exclude)) {
                continue;
            }

            $extracted[] = $fileinfo;

            // create output directory
            $output    = $outdir.'/'.$fileinfo->getPath();
            $directory = ($header['folder']) ? $output : dirname($output);
            @mkdir($directory, 0777, true);

            // nothing more to do for directories
            if ($fileinfo->getIsdir()) {
                continue;
            }

            // compressed files are written to temporary .gz file first
            if ($header['compression'] == 0) {
                $extractto = $output;
            } else {
                $extractto = $output.'.gz';
            }

            // open file for writing
            $fp = fopen($extractto, "wb");
            if (!$fp) {
                throw new ArchiveIOException('Could not open file for writing: '.$extractto);
            }

            // prepend compression header
            if ($header['compression'] != 0) {
                $binary_data = pack(
                    'va1a1Va1a1',
                    0x8b1f,
                    chr($header['compression']),
                    chr(0x00),
                    time(),
                    chr(0x00),
                    chr(3)
                );
                fwrite($fp, $binary_data, 10);
            }

            // read the file and store it on disk
            $size = $header['compressed_size'];
            while ($size != 0) {
                $read_size   = ($size < 2048 ? $size : 2048);
                $buffer      = fread($this->fh, $read_size);
                $binary_data = pack('a'.$read_size, $buffer);
                fwrite($fp, $binary_data, $read_size);
                $size -= $read_size;
            }

            // finalize compressed file
            if ($header['compression'] != 0) {
                $binary_data = pack('VV', $header['crc'], $header['size']);
                fwrite($fp, $binary_data, 8);
            }

            // close file
            fclose($fp);

            // unpack compressed file
            if ($header['compression'] != 0) {
                $gzp = @gzopen($extractto, 'rb');
                if (!$gzp) {
                    @unlink($extractto);
                    throw new ArchiveIOException('Failed file extracting. gzip support missing?');
                }
                $fp = @fopen($output, 'wb');
                if (!$fp) {
                    throw new ArchiveIOException('Could not open file for writing: '.$extractto);
                }

                $size = $header['size'];
                while ($size != 0) {
                    $read_size   = ($size < 2048 ? $size : 2048);
                    $buffer      = gzread($gzp, $read_size);
                    $binary_data = pack('a'.$read_size, $buffer);
                    @fwrite($fp, $binary_data, $read_size);
                    $size -= $read_size;
                }
                fclose($fp);
                gzclose($gzp);
                unlink($extractto); // remove temporary gz file
            }

            touch($output, $fileinfo->getMtime());
            //FIXME what about permissions?
        }

        $this->close();
        return $extracted;
    }

    /**
     * Create a new ZIP file
     *
     * If $file is empty, the zip file will be created in memory
     *
     * @param string $file
     * @throws ArchiveIOException
     */
    public function create($file = '')
    {
        $this->file   = $file;
        $this->memory = '';
        $this->fh     = 0;

        if ($this->file) {
            $this->fh = @fopen($this->file, 'wb');

            if (!$this->fh) {
                throw new ArchiveIOException('Could not open file for writing: '.$this->file);
            }
        }
        $this->writeaccess = true;
        $this->closed      = false;
        $this->ctrl_dir    = array();
    }

    /**
     * Add a file to the current ZIP archive using an existing file in the filesystem
     *
     * @param string          $file     path to the original file
     * @param string|FileInfo $fileinfo either the name to us in archive (string) or a FileInfo oject with all meta data, empty to take from original
     * @throws ArchiveIOException
     */

    /**
     * Add a file to the current archive using an existing file in the filesystem
     *
     * @param string          $file     path to the original file
     * @param string|FileInfo $fileinfo either the name to use in archive (string) or a FileInfo oject with all meta data, empty to take from original
     * @throws ArchiveIOException
     */
    public function addFile($file, $fileinfo = '')
    {
        if (is_string($fileinfo)) {
            $fileinfo = FileInfo::fromPath($file, $fileinfo);
        }

        if ($this->closed) {
            throw new ArchiveIOException('Archive has been closed, files can no longer be added');
        }

        $data = @file_get_contents($file);
        if ($data === false) {
            throw new ArchiveIOException('Could not open file for reading: '.$file);
        }

        // FIXME could we stream writing compressed data? gzwrite on a fopen handle?
        $this->addData($fileinfo, $data);
    }

    /**
     * Add a file to the current Zip archive using the given $data as content
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

        // prepare info and compress data
        $size     = strlen($data);
        $crc      = crc32($data);
        if ($this->complevel) {
            $data = gzcompress($data, $this->complevel);
            $data = substr($data, 2, -4); // strip compression headers
        }
        $csize  = strlen($data);
        $offset = $this->dataOffset();
        $name   = $fileinfo->getPath();
        $time   = $fileinfo->getMtime();

        // write local file header
        $this->writebytes($this->makeLocalFileHeader(
            $time,
            $crc,
            $size,
            $csize,
            $name,
            (bool) $this->complevel
        ));

        // we store no encryption header

        // write data
        $this->writebytes($data);

        // we store no data descriptor

        // add info to central file directory
        $this->ctrl_dir[] = $this->makeCentralFileRecord(
            $offset,
            $time,
            $crc,
            $size,
            $csize,
            $name,
            (bool) $this->complevel
        );
    }

    /**
     * Add the closing footer to the archive if in write mode, close all file handles
     *
     * After a call to this function no more data can be added to the archive, for
     * read access no reading is allowed anymore
     */
    public function close()
    {
        if ($this->closed) {
            return;
        } // we did this already

        if ($this->writeaccess) {
            // write central directory
            $offset = $this->dataOffset();
            $ctrldir = join('', $this->ctrl_dir);
            $this->writebytes($ctrldir);

            // write end of central directory record
            $this->writebytes("\x50\x4b\x05\x06"); // end of central dir signature
            $this->writebytes(pack('v', 0)); // number of this disk
            $this->writebytes(pack('v', 0)); // number of the disk with the start of the central directory
            $this->writebytes(pack('v',
                count($this->ctrl_dir))); // total number of entries in the central directory on this disk
            $this->writebytes(pack('v', count($this->ctrl_dir))); // total number of entries in the central directory
            $this->writebytes(pack('V', strlen($ctrldir))); // size of the central directory
            $this->writebytes(pack('V',
                $offset)); // offset of start of central directory with respect to the starting disk number
            $this->writebytes(pack('v', 0)); // .ZIP file comment length

            $this->ctrl_dir = array();
        }

        // close file handles
        if ($this->file) {
            fclose($this->fh);
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
     */
    public function getArchive()
    {
        $this->close();

        return $this->memory;
    }

    /**
     * Save the created in-memory archive data
     *
     * Note: It's more memory effective to specify the filename in the create() function and
     * let the library work on the new file directly.
     *
     * @param     $file
     * @throws ArchiveIOException
     */
    public function save($file)
    {
        if (!file_put_contents($file, $this->getArchive())) {
            throw new ArchiveIOException('Could not write to file: '.$file);
        }
    }

    /**
     * Read the central directory
     *
     * This key-value list contains general information about the ZIP file
     *
     * @return array
     */
    protected function readCentralDir()
    {
        $size = filesize($this->file);
        if ($size < 277) {
            $maximum_size = $size;
        } else {
            $maximum_size = 277;
        }

        @fseek($this->fh, $size - $maximum_size);
        $pos   = ftell($this->fh);
        $bytes = 0x00000000;

        while ($pos < $size) {
            $byte  = @fread($this->fh, 1);
            $bytes = (($bytes << 8) & 0xFFFFFFFF) | ord($byte);
            if ($bytes == 0x504b0506) {
                break;
            }
            $pos++;
        }

        $data = unpack(
            'vdisk/vdisk_start/vdisk_entries/ventries/Vsize/Voffset/vcomment_size',
            fread($this->fh, 18)
        );

        if ($data['comment_size'] != 0) {
            $centd['comment'] = fread($this->fh, $data['comment_size']);
        } else {
            $centd['comment'] = '';
        }
        $centd['entries']      = $data['entries'];
        $centd['disk_entries'] = $data['disk_entries'];
        $centd['offset']       = $data['offset'];
        $centd['disk_start']   = $data['disk_start'];
        $centd['size']         = $data['size'];
        $centd['disk']         = $data['disk'];
        return $centd;
    }

    /**
     * Read the next central file header
     *
     * Assumes the current file pointer is pointing at the right position
     *
     * @return array
     */
    protected function readCentralFileHeader()
    {
        $binary_data = fread($this->fh, 46);
        $header      = unpack(
            'vchkid/vid/vversion/vversion_extracted/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len/vcomment_len/vdisk/vinternal/Vexternal/Voffset',
            $binary_data
        );

        if ($header['filename_len'] != 0) {
            $header['filename'] = fread($this->fh, $header['filename_len']);
        } else {
            $header['filename'] = '';
        }

        if ($header['extra_len'] != 0) {
            $header['extra'] = fread($this->fh, $header['extra_len']);
            $header['extradata'] = $this->parseExtra($header['extra']);
        } else {
            $header['extra'] = '';
            $header['extradata'] = array();
        }

        if ($header['comment_len'] != 0) {
            $header['comment'] = fread($this->fh, $header['comment_len']);
        } else {
            $header['comment'] = '';
        }

        $header['mtime']           = $this->makeUnixTime($header['mdate'], $header['mtime']);
        $header['stored_filename'] = $header['filename'];
        $header['status']          = 'ok';
        if (substr($header['filename'], -1) == '/') {
            $header['external'] = 0x41FF0010;
        }
        $header['folder'] = ($header['external'] == 0x41FF0010 || $header['external'] == 16) ? 1 : 0;

        return $header;
    }

    /**
     * Reads the local file header
     *
     * This header precedes each individual file inside the zip file. Assumes the current file pointer is pointing at
     * the right position already. Enhances the given central header with the data found at the local header.
     *
     * @param array $header the central file header read previously (see above)
     * @return array
     */
    protected function readFileHeader($header)
    {
        $binary_data = fread($this->fh, 30);
        $data        = unpack(
            'vchk/vid/vversion/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len',
            $binary_data
        );

        $header['filename'] = fread($this->fh, $data['filename_len']);
        if ($data['extra_len'] != 0) {
            $header['extra'] = fread($this->fh, $data['extra_len']);
            $header['extradata'] = array_merge($header['extradata'],  $this->parseExtra($header['extra']));
        } else {
            $header['extra'] = '';
            $header['extradata'] = array();
        }

        $header['compression'] = $data['compression'];
        foreach (array(
                     'size',
                     'compressed_size',
                     'crc'
                 ) as $hd) { // On ODT files, these headers are 0. Keep the previous value.
            if ($data[$hd] != 0) {
                $header[$hd] = $data[$hd];
            }
        }
        $header['flag']  = $data['flag'];
        $header['mtime'] = $this->makeUnixTime($data['mdate'], $data['mtime']);

        $header['stored_filename'] = $header['filename'];
        $header['status']          = "ok";
        $header['folder']          = ($header['external'] == 0x41FF0010 || $header['external'] == 16) ? 1 : 0;
        return $header;
    }

    /**
     * Parse the extra headers into fields
     *
     * @param string $header
     * @return array
     */
    protected function parseExtra($header)
    {
        $extra = array();
        // parse all extra fields as raw values
        while (strlen($header) !== 0) {
            $set = unpack('vid/vlen', $header);
            $header = substr($header, 4);
            $value = substr($header, 0, $set['len']);
            $header = substr($header, $set['len']);
            $extra[$set['id']] = $value;
        }

        // handle known ones
        if(isset($extra[0x6375])) {
            $extra['utf8comment'] = substr($extra[0x7075], 5); // strip version and crc
        }
        if(isset($extra[0x7075])) {
            $extra['utf8path'] = substr($extra[0x7075], 5); // strip version and crc
        }

        return $extra;
    }

    /**
     * Create fileinfo object from header data
     *
     * @param $header
     * @return FileInfo
     */
    protected function header2fileinfo($header)
    {
        $fileinfo = new FileInfo();
        $fileinfo->setSize($header['size']);
        $fileinfo->setCompressedSize($header['compressed_size']);
        $fileinfo->setMtime($header['mtime']);
        $fileinfo->setComment($header['comment']);
        $fileinfo->setIsdir($header['external'] == 0x41FF0010 || $header['external'] == 16);

        if(isset($header['extradata']['utf8path'])) {
            $fileinfo->setPath($header['extradata']['utf8path']);
        } else {
            $fileinfo->setPath($this->cpToUtf8($header['filename']));
        }

        if(isset($header['extradata']['utf8comment'])) {
            $fileinfo->setComment($header['extradata']['utf8comment']);
        } else {
            $fileinfo->setComment($this->cpToUtf8($header['comment']));
        }

        return $fileinfo;
    }

    /**
     * Convert the given CP437 encoded string to UTF-8
     *
     * Tries iconv with the correct encoding first, falls back to mbstring with CP850 which is
     * similar enough. CP437 seems not to be available in mbstring. Lastly falls back to keeping the
     * string as is, which is still better than nothing.
     *
     * @param $string
     * @return string
     */
    protected function cpToUtf8($string)
    {
        if (function_exists('iconv')) {
            return iconv('CP437', 'UTF-8', $string);
        } elseif (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($string, 'UTF-8', 'CP850');
        } else {
            return $string;
        }
    }

    /**
     * Convert the given UTF-8 encoded string to CP437
     *
     * Same caveats as for cpToUtf8() apply
     *
     * @param $string
     * @return string
     */
    protected function utf8ToCp($string)
    {
        // try iconv first
        if (function_exists('iconv')) {
            $conv = @iconv('UTF-8', 'CP437//IGNORE', $string);
            if($conv) return $conv; // it worked
        }

        // still here? iconv failed to convert the string. Try another method
        // see http://php.net/manual/en/function.iconv.php#108643

        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($string, 'CP850', 'UTF-8');
        } else {
            return $string;
        }
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
        } else {
            $written = @fwrite($this->fh, $data);
        }
        if ($written === false) {
            throw new ArchiveIOException('Failed to write to archive stream');
        }
        return $written;
    }

    /**
     * Current data pointer position
     *
     * @fixme might need a -1
     * @return int
     */
    protected function dataOffset()
    {
        if ($this->file) {
            return ftell($this->fh);
        } else {
            return strlen($this->memory);
        }
    }

    /**
     * Create a DOS timestamp from a UNIX timestamp
     *
     * DOS timestamps start at 1980-01-01, earlier UNIX stamps will be set to this date
     *
     * @param $time
     * @return int
     */
    protected function makeDosTime($time)
    {
        $timearray = getdate($time);
        if ($timearray['year'] < 1980) {
            $timearray['year']    = 1980;
            $timearray['mon']     = 1;
            $timearray['mday']    = 1;
            $timearray['hours']   = 0;
            $timearray['minutes'] = 0;
            $timearray['seconds'] = 0;
        }
        return (($timearray['year'] - 1980) << 25) |
        ($timearray['mon'] << 21) |
        ($timearray['mday'] << 16) |
        ($timearray['hours'] << 11) |
        ($timearray['minutes'] << 5) |
        ($timearray['seconds'] >> 1);
    }

    /**
     * Create a UNIX timestamp from a DOS timestamp
     *
     * @param $mdate
     * @param $mtime
     * @return int
     */
    protected function makeUnixTime($mdate = null, $mtime = null)
    {
        if ($mdate && $mtime) {
            $year = (($mdate & 0xFE00) >> 9) + 1980;
            $month = ($mdate & 0x01E0) >> 5;
            $day = $mdate & 0x001F;

            $hour = ($mtime & 0xF800) >> 11;
            $minute = ($mtime & 0x07E0) >> 5;
            $seconde = ($mtime & 0x001F) << 1;

            $mtime = mktime($hour, $minute, $seconde, $month, $day, $year);
        } else {
            $mtime = time();
        }

        return $mtime;
    }

    /**
     * Returns a local file header for the given data
     *
     * @param int $offset location of the local header
     * @param int $ts unix timestamp
     * @param int $crc CRC32 checksum of the uncompressed data
     * @param int $len length of the uncompressed data
     * @param int $clen length of the compressed data
     * @param string $name file name
     * @param boolean|null $comp if compression is used, if null it's determined from $len != $clen
     * @return string
     */
    protected function makeCentralFileRecord($offset, $ts, $crc, $len, $clen, $name, $comp = null)
    {
        if(is_null($comp)) $comp = $len != $clen;
        $comp = $comp ? 8 : 0;
        $dtime = dechex($this->makeDosTime($ts));

        list($name, $extra) = $this->encodeFilename($name);

        $header = "\x50\x4b\x01\x02"; // central file header signature
        $header .= pack('v', 14); // version made by - VFAT
        $header .= pack('v', 20); // version needed to extract - 2.0
        $header .= pack('v', 0); // general purpose flag - no flags set
        $header .= pack('v', $comp); // compression method - deflate|none
        $header .= pack(
            'H*',
            $dtime[6] . $dtime[7] .
            $dtime[4] . $dtime[5] .
            $dtime[2] . $dtime[3] .
            $dtime[0] . $dtime[1]
        ); //  last mod file time and date
        $header .= pack('V', $crc); // crc-32
        $header .= pack('V', $clen); // compressed size
        $header .= pack('V', $len); // uncompressed size
        $header .= pack('v', strlen($name)); // file name length
        $header .= pack('v', strlen($extra)); // extra field length
        $header .= pack('v', 0); // file comment length
        $header .= pack('v', 0); // disk number start
        $header .= pack('v', 0); // internal file attributes
        $header .= pack('V', 0); // external file attributes  @todo was 0x32!?
        $header .= pack('V', $offset); // relative offset of local header
        $header .= $name; // file name
        $header .= $extra; // extra (utf-8 filename)

        return $header;
    }

    /**
     * Returns a local file header for the given data
     *
     * @param int $ts unix timestamp
     * @param int $crc CRC32 checksum of the uncompressed data
     * @param int $len length of the uncompressed data
     * @param int $clen length of the compressed data
     * @param string $name file name
     * @param boolean|null $comp if compression is used, if null it's determined from $len != $clen
     * @return string
     */
    protected function makeLocalFileHeader($ts, $crc, $len, $clen, $name, $comp = null)
    {
        if(is_null($comp)) $comp = $len != $clen;
        $comp = $comp ? 8 : 0;
        $dtime = dechex($this->makeDosTime($ts));

        list($name, $extra) = $this->encodeFilename($name);

        $header = "\x50\x4b\x03\x04"; //  local file header signature
        $header .= pack('v', 20); // version needed to extract - 2.0
        $header .= pack('v', 0); // general purpose flag - no flags set
        $header .= pack('v', $comp); // compression method - deflate|none
        $header .= pack(
            'H*',
            $dtime[6] . $dtime[7] .
            $dtime[4] . $dtime[5] .
            $dtime[2] . $dtime[3] .
            $dtime[0] . $dtime[1]
        ); //  last mod file time and date
        $header .= pack('V', $crc); // crc-32
        $header .= pack('V', $clen); // compressed size
        $header .= pack('V', $len); // uncompressed size
        $header .= pack('v', strlen($name)); // file name length
        $header .= pack('v', strlen($extra)); // extra field length
        $header .= $name; // file name
        $header .= $extra; // extra (utf-8 filename)
        return $header;
    }

    /**
     * Returns an allowed filename and an extra field header
     *
     * When encoding stuff outside the 7bit ASCII range it needs to be placed in a separate
     * extra field
     *
     * @param $original
     * @return array($filename, $extra)
     */
    protected function encodeFilename($original)
    {
        $cp437 = $this->utf8ToCp($original);
        if ($cp437 === $original) {
            return array($original, '');
        }

        $extra = pack(
            'vvCV',
            0x7075, // tag
            strlen($original) + 5, // length of file + version + crc
            1, // version
            crc32($original) // crc
        );
        $extra .= $original;

        return array($cp437, $extra);
    }
}
