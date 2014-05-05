<?php
/**
 * This class allows the extraction of existing and the creation of new Unix TAR archives.
 * To keep things simple, the modification of existing archives is not supported. It handles
 * uncompressed, gzip and bzip2 compressed tar files.
 *
 * Long pathnames (>100 chars) are supported in POSIX ustar and GNU longlink formats.
 *
 * To list the contents of an existing TAR archive, open() it and use contents() on it:
 *
 *     $tar = new Tar();
 *     $tar->open('myfile.tgz');
 *     $toc = $tar->contents();
 *     print_r($toc);
 *
 * To extract the contents of an existing TAR archive, open() it and use extract() on it:
 *
 *     $tar = new Tar();
 *     $tar->open('myfile.tgz');
 *     $tar->extract('/tmp');
 *
 * To create a new TAR archive directly on the filesystem (low memory requirements), create() it,
 * add*() files and close() it:
 *
 *      $tar = new Tar();
 *      $tar->create('myfile.tgz');
 *      $tar->addFile(...);
 *      $tar->addData(...);
 *      ...
 *      $tar->close();
 *
 * To create a TAR archive directly in memory, create() it, add*() files and then either save()
 * or getData() it:
 *
 *      $tar = new Tar();
 *      $tar->create();
 *      $tar->addFile(...);
 *      $tar->addData(...);
 *      ...
 *      $tar->save('myfile.tgz'); // compresses and saves it
 *      echo $tar->getArchive(Tar::COMPRESS_GZIP); // compresses and returns it
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Bouchon <tarlib@bouchon.org> (Maxg)
 * @license GPL 2
 */
class Tar {

    const COMPRESS_AUTO = 0;
    const COMPRESS_NONE = 1;
    const COMPRESS_GZIP = 2;
    const COMPRESS_BZIP = 3;

    protected $file = '';
    protected $comptype = Tar::COMPRESS_AUTO;
    protected $fh;
    protected $memory = '';
    protected $closed = true;
    protected $writeaccess = false;

    /**
     * Open an existing TAR file for reading
     *
     * @param string $file
     * @param int    $comptype
     * @throws TarIOException
     */
    public function open($file, $comptype = Tar::COMPRESS_AUTO) {
        // determine compression
        if($comptype == Tar::COMPRESS_AUTO) $comptype = $this->filetype($file);
        $this->compressioncheck($comptype);

        $this->comptype = $comptype;
        $this->file     = $file;

        if($this->comptype === Tar::COMPRESS_GZIP) {
            $this->fh = @gzopen($this->file, 'rb');
        } elseif($this->comptype === Tar::COMPRESS_BZIP) {
            $this->fh = @bzopen($this->file, 'r');
        } else {
            $this->fh = @fopen($this->file, 'rb');
        }

        if(!$this->fh) throw new TarIOException('Could not open file for reading: '.$this->file);
        $this->closed = false;
    }

    /**
     * Read the contents of a TAR archive
     *
     * This function lists the files stored in the archive, and returns an indexed array of associative
     * arrays containing for each file the following information:
     *
     * checksum    Tar Checksum of the file
     * filename    The full name of the stored file (up to 100 c.)
     * mode        UNIX permissions in DECIMAL, not octal
     * uid         The Owner ID
     * gid         The Group ID
     * size        Uncompressed filesize
     * mtime       Timestamp of last modification
     * typeflag    Empty for files, set for folders
     * link        Is it a symlink?
     * uname       Owner name
     * gname       Group name
     *
     * The archive is closed afer reading the contents, because rewinding is not possible in bzip2 streams.
     * Reopen the file with open() again if you want to do additional operations
     */
    public function contents() {
        if($this->closed || !$this->file) throw new TarIOException('Can not read from a closed archive');

        $result = array();
        while($read = $this->readbytes(512)) {
            $header = $this->parseHeader($read);
            if(!is_array($header)) continue;

            $this->skipbytes(ceil($header['size'] / 512) * 512);
            $result[] = $header;
        }

        $this->close();
        return $result;
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
     * @param string     $outdir  the target directory for extracting
     * @param int|string $strip   either the number of path components or a fixed prefix to strip
     * @param string     $exclude a regular expression of files to exclude
     * @param string     $include a regular expression of files to include
     * @throws TarIOException
     * @return array
     */
    function extract($outdir, $strip = '', $exclude = '', $include = '') {
        if($this->closed || !$this->file) throw new TarIOException('Can not read from a closed archive');

        $outdir = rtrim($outdir, '/');
        io_mkdir_p($outdir);
        $striplen = strlen($strip);

        $extracted = array();

        while($dat = $this->readbytes(512)) {
            // read the file header
            $header = $this->parseHeader($dat);
            if(!is_array($header)) continue;
            if(!$header['filename']) continue;

            // strip prefix
            $filename = $this->cleanPath($header['filename']);
            if(is_int($strip)) {
                // if $strip is an integer we strip this many path components
                $parts = explode('/', $filename);
                if(!$header['typeflag']) {
                    $base = array_pop($parts); // keep filename itself
                } else {
                    $base = '';
                }
                $filename = join('/', array_slice($parts, $strip));
                if($base) $filename .= "/$base";
            } else {
                // ifstrip is a string, we strip a prefix here
                if(substr($filename, 0, $striplen) == $strip) $filename = substr($filename, $striplen);
            }

            // check if this should be extracted
            $extract = true;
            if(!$filename) {
                $extract = false;
            } else {
                if($include) {
                    if(preg_match($include, $filename)) {
                        $extract = true;
                    } else {
                        $extract = false;
                    }
                }
                if($exclude && preg_match($exclude, $filename)) {
                    $extract = false;
                }
            }

            // Now do the extraction (or not)
            if($extract) {
                $extracted[] = $header;

                $output    = "$outdir/$filename";
                $directory = ($header['typeflag']) ? $output : dirname($output);
                io_mkdir_p($directory);

                // is this a file?
                if(!$header['typeflag']) {
                    $fp = fopen($output, "wb");
                    if(!$fp) throw new TarIOException('Could not open file for writing: '.$output);

                    $size = floor($header['size'] / 512);
                    for($i = 0; $i < $size; $i++) {
                        fwrite($fp, $this->readbytes(512), 512);
                    }
                    if(($header['size'] % 512) != 0) fwrite($fp, $this->readbytes(512), $header['size'] % 512);

                    fclose($fp);
                    touch($output, $header['mtime']);
                    chmod($output, $header['perm']);
                } else {
                    $this->skipbytes(ceil($header['size'] / 512) * 512); // the size is usually 0 for directories
                }
            } else {
                $this->skipbytes(ceil($header['size'] / 512) * 512);
            }
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
     * @param int    $comptype
     * @param int    $complevel
     * @throws TarIOException
     * @throws TarIllegalCompressionException
     */
    public function create($file = '', $comptype = Tar::COMPRESS_AUTO, $complevel = 9) {
        // determine compression
        if($comptype == Tar::COMPRESS_AUTO) $comptype = $this->filetype($file);
        $this->compressioncheck($comptype);

        $this->comptype = $comptype;
        $this->file     = $file;
        $this->memory   = '';
        $this->fh       = 0;

        if($this->file) {
            if($this->comptype === Tar::COMPRESS_GZIP) {
                $this->fh = @gzopen($this->file, 'wb'.$complevel);
            } elseif($this->comptype === Tar::COMPRESS_BZIP) {
                $this->fh = @bzopen($this->file, 'w');
            } else {
                $this->fh = @fopen($this->file, 'wb');
            }

            if(!$this->fh) throw new TarIOException('Could not open file for writing: '.$this->file);
        }
        $this->writeaccess = true;
        $this->closed      = false;
    }

    /**
     * Add a file to the current TAR archive using an existing file in the filesystem
     *
     * @todo handle directory adding
     * @param string $file the original file
     * @param string $name the name to use for the file in the archive
     * @throws TarIOException
     */
    public function addFile($file, $name = '') {
        if($this->closed) throw new TarIOException('Archive has been closed, files can no longer be added');

        if(!$name) $name = $file;
        $name = $this->cleanPath($name);

        $fp = fopen($file, 'rb');
        if(!$fp) throw new TarIOException('Could not open file for reading: '.$file);

        // create file header and copy all stat info from the original file
        clearstatcache(false, $file);
        $stat = stat($file);
        $this->writeFileHeader(
            $name,
            $stat[4],
            $stat[5],
            fileperms($file),
            filesize($file),
            filemtime($file)
        );

        while(!feof($fp)) {
            $data = fread($fp, 512);
            if($data === false) break;
            if($data === '') break;
            $packed = pack("a512", $data);
            $this->writebytes($packed);
        }
        fclose($fp);
    }

    /**
     * Add a file to the current TAR archive using the given $data as content
     *
     * @param string $name
     * @param string $data
     * @param int    $uid
     * @param int    $gid
     * @param int    $perm
     * @param int    $mtime
     * @throws TarIOException
     */
    public function addData($name, $data, $uid = 0, $gid = 0, $perm = 0666, $mtime = 0) {
        if($this->closed) throw new TarIOException('Archive has been closed, files can no longer be added');

        $name = $this->cleanPath($name);
        $len  = strlen($data);

        $this->writeFileHeader(
            $name,
            $uid,
            $gid,
            $perm,
            $len,
            ($mtime) ? $mtime : time()
        );

        for($s = 0; $s < $len; $s += 512) {
            $this->writebytes(pack("a512", substr($data, $s, 512)));
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
     */
    public function close() {
        if($this->closed) return; // we did this already

        // write footer
        if($this->writeaccess) {
            $this->writebytes(pack("a512", ""));
            $this->writebytes(pack("a512", ""));
        }

        // close file handles
        if($this->file) {
            if($this->comptype === Tar::COMPRESS_GZIP) {
                gzclose($this->fh);
            } elseif($this->comptype === Tar::COMPRESS_BZIP) {
                bzclose($this->fh);
            } else {
                fclose($this->fh);
            }

            $this->file = '';
            $this->fh   = 0;
        }

        $this->closed = true;
    }

    /**
     * Returns the created in-memory archive data
     *
     * This implicitly calls close() on the Archive
     */
    public function getArchive($comptype = Tar::COMPRESS_AUTO, $complevel = 9) {
        $this->close();

        if($comptype === Tar::COMPRESS_AUTO) $comptype = $this->comptype;
        $this->compressioncheck($comptype);

        if($comptype === Tar::COMPRESS_GZIP) return gzcompress($this->memory, $complevel);
        if($comptype === Tar::COMPRESS_BZIP) return bzcompress($this->memory);
        return $this->memory;
    }

    /**
     * Save the created in-memory archive data
     *
     * Note: It more memory effective to specify the filename in the create() function and
     * let the library work on the new file directly.
     *
     * @param     $file
     * @param int $comptype
     * @param int $complevel
     * @throws TarIOException
     */
    public function save($file, $comptype = Tar::COMPRESS_AUTO, $complevel = 9) {
        if($comptype === Tar::COMPRESS_AUTO) $comptype = $this->filetype($file);

        if(!file_put_contents($file, $this->getArchive($comptype, $complevel))) {
            throw new TarIOException('Could not write to file: '.$file);
        }
    }

    /**
     * Read from the open file pointer
     *
     * @param int $length bytes to read
     * @return string
     */
    protected function readbytes($length) {
        if($this->comptype === Tar::COMPRESS_GZIP) {
            return @gzread($this->fh, $length);
        } elseif($this->comptype === Tar::COMPRESS_BZIP) {
            return @bzread($this->fh, $length);
        } else {
            return @fread($this->fh, $length);
        }
    }

    /**
     * Write to the open filepointer or memory
     *
     * @param string $data
     * @throws TarIOException
     * @return int number of bytes written
     */
    protected function writebytes($data) {
        if(!$this->file) {
            $this->memory .= $data;
            $written = strlen($data);
        } elseif($this->comptype === Tar::COMPRESS_GZIP) {
            $written = @gzwrite($this->fh, $data);
        } elseif($this->comptype === Tar::COMPRESS_BZIP) {
            $written = @bzwrite($this->fh, $data);
        } else {
            $written = @fwrite($this->fh, $data);
        }
        if($written === false) throw new TarIOException('Failed to write to archive stream');
        return $written;
    }

    /**
     * Skip forward in the open file pointer
     *
     * This is basically a wrapper around seek() (and a workaround for bzip2)
     *
     * @param int  $bytes seek to this position
     */
    function skipbytes($bytes) {
        if($this->comptype === Tar::COMPRESS_GZIP) {
            @gzseek($this->fh, $bytes, SEEK_CUR);
        } elseif($this->comptype === Tar::COMPRESS_BZIP) {
            // there is no seek in bzip2, we simply read on
            @bzread($this->fh, $bytes);
        } else {
            @fseek($this->fh, $bytes, SEEK_CUR);
        }
    }

    /**
     * Write a file header
     *
     * @param string $name
     * @param int    $uid
     * @param int    $gid
     * @param int    $perm
     * @param int    $size
     * @param int    $mtime
     * @param string $typeflag Set to '5' for directories
     */
    protected function writeFileHeader($name, $uid, $gid, $perm, $size, $mtime, $typeflag = '') {
        // handle filename length restrictions
        $prefix  = '';
        $namelen = strlen($name);
        if($namelen > 100) {
            $file = basename($name);
            $dir  = dirname($name);
            if(strlen($file) > 100 || strlen($dir) > 155) {
                // we're still too large, let's use GNU longlink
                $this->writeFileHeader('././@LongLink', 0, 0, 0, $namelen, 0, 'L');
                for($s = 0; $s < $namelen; $s += 512) {
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
        $size  = sprintf("%11s ", decoct($size));
        $mtime = sprintf("%11s", decoct($mtime));

        $data_first = pack("a100a8a8a8a12A12", $name, $perm, $uid, $gid, $size, $mtime);
        $data_last  = pack("a1a100a6a2a32a32a8a8a155a12", $typeflag, '', 'ustar', '', '', '', '', '', $prefix, "");

        for($i = 0, $chks = 0; $i < 148; $i++)
            $chks += ord($data_first[$i]);

        for($i = 156, $chks += 256, $j = 0; $i < 512; $i++, $j++)
            $chks += ord($data_last[$j]);

        $this->writebytes($data_first);

        $chks = pack("a8", sprintf("%6s ", decoct($chks)));
        $this->writebytes($chks.$data_last);
    }

    /**
     * Decode the given tar file header
     *
     * @param string $block a 512 byte block containign the header data
     * @return array|bool
     */
    protected function parseHeader($block) {
        if(!$block || strlen($block) != 512) return false;

        for($i = 0, $chks = 0; $i < 148; $i++)
            $chks += ord($block[$i]);

        for($i = 156, $chks += 256; $i < 512; $i++)
            $chks += ord($block[$i]);

        $header = @unpack("a100filename/a8perm/a8uid/a8gid/a12size/a12mtime/a8checksum/a1typeflag/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor/a155prefix", $block);
        if(!$header) return false;

        $return['checksum'] = OctDec(trim($header['checksum']));
        if($return['checksum'] != $chks) return false;

        $return['filename'] = trim($header['filename']);
        $return['perm']     = OctDec(trim($header['perm']));
        $return['uid']      = OctDec(trim($header['uid']));
        $return['gid']      = OctDec(trim($header['gid']));
        $return['size']     = OctDec(trim($header['size']));
        $return['mtime']    = OctDec(trim($header['mtime']));
        $return['typeflag'] = $header['typeflag'];
        $return['link']     = trim($header['link']);
        $return['uname']    = trim($header['uname']);
        $return['gname']    = trim($header['gname']);

        // Handle ustar Posix compliant path prefixes
        if(trim($header['prefix'])) $return['filename'] = trim($header['prefix']).'/'.$return['filename'];

        // Handle Long-Link entries from GNU Tar
        if($return['typeflag'] == 'L') {
            // following data block(s) is the filename
            $filename = trim($this->readbytes(ceil($header['size'] / 512) * 512));
            // next block is the real header
            $block  = $this->readbytes(512);
            $return = $this->parseHeader($block);
            // overwrite the filename
            $return['filename'] = $filename;
        }

        return $return;
    }

    /**
     * Cleans up a path and removes relative parts, also strips leading slashes
     *
     * @param string $p_dir
     * @return string
     */
    public function cleanPath($path) {
        $path=explode('/', $path);
        $newpath=array();
        foreach($path as $p) {
            if ($p === '' || $p === '.') continue;
            if ($p==='..') {
                array_pop($newpath);
                continue;
            }
            array_push($newpath, $p);
        }
        return trim(implode('/', $newpath), '/');
    }

    /**
     * Checks if the given compression type is available and throws an exception if not
     *
     * @param $comptype
     * @throws TarIllegalCompressionException
     */
    protected function compressioncheck($comptype) {
        if($comptype === Tar::COMPRESS_GZIP && !function_exists('gzopen')) {
            throw new TarIllegalCompressionException('No gzip support available');
        }

        if($comptype === Tar::COMPRESS_BZIP && !function_exists('bzopen')) {
            throw new TarIllegalCompressionException('No bzip2 support available');
        }
    }

    /**
     * Guesses the wanted compression from the given filename extension
     *
     * You don't need to call this yourself. It's used when you pass Tar::COMPRESS_AUTO somewhere
     *
     * @param string $file
     * @return int
     */
    public function filetype($file) {
        $file = strtolower($file);
        if(substr($file, -3) == '.gz' || substr($file, -4) == '.tgz') {
            $comptype = Tar::COMPRESS_GZIP;
        } elseif(substr($file, -4) == '.bz2' || substr($file, -4) == '.tbz') {
            $comptype = Tar::COMPRESS_BZIP;
        } else {
            $comptype = Tar::COMPRESS_NONE;
        }
        return $comptype;
    }
}

class TarIOException extends Exception {
}

class TarIllegalCompressionException extends Exception {
}
