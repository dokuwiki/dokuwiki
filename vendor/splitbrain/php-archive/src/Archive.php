<?php

namespace splitbrain\PHPArchive;

abstract class Archive
{

    const COMPRESS_AUTO = -1;
    const COMPRESS_NONE = 0;
    const COMPRESS_GZIP = 1;
    const COMPRESS_BZIP = 2;

    /**
     * Set the compression level and type
     *
     * @param int $level Compression level (0 to 9)
     * @param int $type  Type of compression to use (use COMPRESS_* constants)
     * @return mixed
     */
    abstract public function setCompression($level = 9, $type = Archive::COMPRESS_AUTO);

    /**
     * Open an existing archive file for reading
     *
     * @param string $file
     * @throws ArchiveIOException
     */
    abstract public function open($file);

    /**
     * Read the contents of an archive
     *
     * This function lists the files stored in the archive, and returns an indexed array of FileInfo objects
     *
     * The archive is closed afer reading the contents, because rewinding is not possible in bzip2 streams.
     * Reopen the file with open() again if you want to do additional operations
     *
     * @return FileInfo[]
     */
    abstract public function contents();

    /**
     * Extract an existing archive
     *
     * The $strip parameter allows you to strip a certain number of path components from the filenames
     * found in the archive file, similar to the --strip-components feature of GNU tar. This is triggered when
     * an integer is passed as $strip.
     * Alternatively a fixed string prefix may be passed in $strip. If the filename matches this prefix,
     * the prefix will be stripped. It is recommended to give prefixes with a trailing slash.
     *
     * By default this will extract all files found in the archive. You can restrict the output using the $include
     * and $exclude parameter. Both expect a full regular expression (including delimiters and modifiers). If
     * $include is set, only files that match this expression will be extracted. Files that match the $exclude
     * expression will never be extracted. Both parameters can be used in combination. Expressions are matched against
     * stripped filenames as described above.
     *
     * The archive is closed afterwards. Reopen the file with open() again if you want to do additional operations
     *
     * @param string     $outdir  the target directory for extracting
     * @param int|string $strip   either the number of path components or a fixed prefix to strip
     * @param string     $exclude a regular expression of files to exclude
     * @param string     $include a regular expression of files to include
     * @throws ArchiveIOException
     * @return array
     */
    abstract public function extract($outdir, $strip = '', $exclude = '', $include = '');

    /**
     * Create a new archive file
     *
     * If $file is empty, the archive file will be created in memory
     *
     * @param string $file
     */
    abstract public function create($file = '');

    /**
     * Add a file to the current archive using an existing file in the filesystem
     *
     * @param string          $file     path to the original file
     * @param string|FileInfo $fileinfo either the name to us in archive (string) or a FileInfo oject with all meta data, empty to take from original
     * @throws ArchiveIOException
     */
    abstract public function addFile($file, $fileinfo = '');

    /**
     * Add a file to the current archive using the given $data as content
     *
     * @param string|FileInfo $fileinfo either the name to us in archive (string) or a FileInfo oject with all meta data
     * @param string          $data     binary content of the file to add
     * @throws ArchiveIOException
     */
    abstract public function addData($fileinfo, $data);

    /**
     * Close the archive, close all file handles
     *
     * After a call to this function no more data can be added to the archive, for
     * read access no reading is allowed anymore
     */
    abstract public function close();

    /**
     * Returns the created in-memory archive data
     *
     * This implicitly calls close() on the Archive
     */
    abstract public function getArchive();

    /**
     * Save the created in-memory archive data
     *
     * Note: It is more memory effective to specify the filename in the create() function and
     * let the library work on the new file directly.
     *
     * @param string $file
     */
    abstract public function save($file);

}

class ArchiveIOException extends \Exception
{
}

class ArchiveIllegalCompressionException extends \Exception
{
}
