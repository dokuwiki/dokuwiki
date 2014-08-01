<?php

//@todo redefine Exceptioncodes

class MediaFile {
    /** @var string ID of the file */
    protected $id = '';
    /** @var string local (cache) file path */
    protected $file = '';
    /** @var bool is it an external image? */
    protected $isexternal = null;
    /** @var int holds the auth info for current user */
    protected $auth = AUTH_NONE;
    /** @var string file extension */
    protected $extension = 'unknown';
    /** @var string mime type */
    protected $mimetype = 'application/octet-stream';
    /** @var bool true for content-disposition attachment */
    protected $isdownload = true;
    /** @var bool overwrite file on upload? */
    protected $allowoverwrite = false;
    /** @var string allowed mime types */
    protected $mimetyperegex = '';

    /**
     * Initialize the MediaFile
     *
     * @param string $id the media ID or URL
     */
    public function __construct($id) {
        $this->id = stripctl($id); // no cleaning yet

        // initialize mime type and extension info
        list($ext, $mime, $dl) = mimetype($this->id, false);
        if($ext !== false) {
            $this->extension  = $ext;
            $this->mimetype   = $mime;
            $this->isdownload = $dl;
        }

        // check if external file
        if(preg_match('#^(https?)://#i', $this->id)) {
            $this->isexternal = true;
            $this->file       = getCacheName($this->id, '.'.$this->extension);
            $this->auth       = AUTH_READ;
        } else {
            $this->id         = cleanID($this->id);
            $this->isexternal = false;
            $this->file       = mediaFN($this->id);
            $this->auth       = auth_quickaclcheck(getNS($this->id).':*');
        }
    }

    /**
     * Upload/Create a new revision from an open stream
     *
     * When reading STDIN you might want to pass $_SERVER["CONTENT_LENGTH"] as $maxlen
     *
     * @param resource $stream open stream/filehandle (will not be closed automatically)
     * @param int      $maxlen maximum bytes to read from stream
     * @throws MediaUploadException
     */
    public function uploadStream($stream, $maxlen = null) {
        $this->uploadGuard(); // fail early

        // read into temporary file
        $tmpfile  = $this->getTempName();
        $target   = fopen($tmpfile, 'wb');
        $realSize = stream_copy_to_stream($stream, $target, $maxlen);
        fclose($target);
        if($maxlen && ($realSize != $maxlen)) {
            unlink($tmpfile);
            throw new MediaUploadException('Failed to read from stream', 1);
        }
        // pass on
        $this->uploadFile($tmpfile);
    }

    /**
     * Upload/Create a new revision from the given data
     *
     * @param string $data
     */
    public function uploadData($data) {
        $this->uploadGuard(); // fail early

        // read into temporary file
        $tmpfile = $this->getTempName();
        io_saveFile($tmpfile, $data);

        // pass on
        $this->uploadFile($tmpfile);
    }

    /**
     * Upload/Create a new revision from a HTML form upload
     *
     * @param string $formfield Name of the HTML form field
     * @throws MediaUploadException
     */
    public function uploadFormFile($formfield) {
        $this->uploadGuard(); // fail early

        if(!isset($_FILES[$formfield])) throw new MediaUploadException('No upload found', 3);
        $file = $_FILES[$formfield];

        if($file['error']) throw new MediaUploadException('Upload Error', 2, $file['error']);

        // move to local temporary file
        $tmpfile = $this->getTempName();
        if(!move_uploaded_file($file, $tmpfile)) {
            throw new MediaUploadException('Couldn\'t move upload', 3);
        }

        // pass on
        $this->uploadFile($tmpfile);
    }

    /**
     * Upload/Create a new revision from a (server local) file
     *
     * This method is the final step of all other upload methods
     *
     * @param string   $file
     * @param bool     $unlink delete local file after copy?
     * @param int|bool $rev    given file is an old revision of that file from $rev
     *
     * @throws MediaUploadException
     * @triggers MEDIA_UPLOAD_FINISH FIXME
     */
    public function uploadFile($file, $unlink = true, $rev = false) {
        global $conf;
        global $lang;

        $this->uploadGuard();
        $this->contentCheck($file);

        $this->saveOldRevision();
        $overwrite = $this->exists();

        // copy it to the right place
        io_createNamespace($this->id, 'media');
        if(!copy($file, $this->file))
            throw new MediaUploadException('uploadfail', 1);

        @clearstatcache(true, $this->file);
        $time = @filemtime($this->file);

        // Set the correct permission here.
        // Always chmod media because they may be saved with different permissions than expected from the php umask.
        // (Should normally chmod to $conf['fperm'] only if $conf['fperm'] is set.)
        chmod($this->file, $conf['fmode']);

        $this->uploadNotify($this->id, $this->file, $this->mimetype, $rev);

        // add a log entry to the media changelog
        if($rev) {
            addMediaLogEntry($time, $this->id, DOKU_CHANGE_TYPE_REVERT, sprintf($lang['restored'], dformat($rev)), $rev);
        } elseif($overwrite) {
            addMediaLogEntry($time, $this->id, DOKU_CHANGE_TYPE_EDIT);
        } else {
            addMediaLogEntry($time, $this->id, DOKU_CHANGE_TYPE_CREATE, $lang['created']);
        }

        if($unlink) @unlink($file);
    }

    /**
     * Revert media file to the given revision
     *
     * @param int $rev revision number
     *
     * @throws MediaInputException
     */
    public function revertTo($rev) {
        $rev = (int) $rev;
        if(!$rev) throw new MediaInputException('No revision given', 1);

        $revfile = mediaFN($this->id, $rev);
        if(!file_exists($revfile)) throw new MediaInputException('No such revision found', 1);

        $this->uploadFile($revfile, false, $rev);
    }

    /**
     * This function checks if the uploaded content is really what the
     * mimetype says it is. We also do spam checking for text types here.
     *
     * We need to do this stuff because we can not rely on the browser
     * to do this check correctly. Yes, IE is broken as usual.
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @link   http://www.splitbrain.org/blog/2007-02/12-internet_explorer_facilitates_cross_site_scripting
     * @fixme  check all 26 magic IE filetypes here?
     */
    public function contentCheck($file) {
        global $conf;

        if($conf['iexssprotect']) {
            $fh = @fopen($file, 'rb');
            if($fh) {
                $bytes = fread($fh, 256);
                fclose($fh);
                if(preg_match('/<(script|a|img|html|body|iframe)[\s>]/i', $bytes)) {
                    throw new MediaContentException('uploadxss', 1);
                }
            }
        }
        if(substr($this->mimetype, 0, 6) == 'image/') {
            $info = @getimagesize($file);
            if($this->mimetype == 'image/gif' && $info[2] != 1) {
                throw new MediaContentException('uploadbadcontent', 2, 'gif');
            } elseif($this->mimetype == 'image/jpeg' && $info[2] != 2) {
                throw new MediaContentException('uploadbadcontent', 2, 'jpg');
            } elseif($this->mimetype == 'image/png' && $info[2] != 3) {
                throw new MediaContentException('uploadbadcontent', 2, 'png');
            }
            # fixme maybe check other images types as well
        } elseif(substr($this->mimetype, 0, 5) == 'text/') {
            if(checkwordblock(io_readFile($file))) {
                throw new MediaContentException('uploadspam', 3);
            }
        }
    }

    /**
     * Helper function to check if the current file may be uploaded
     *
     * Used to fail early by Exception in all upload mechanisms. Checks for ACL permissions,
     * allowed filetypes and file overwrites. File content is not checked here.
     *
     * @throws MediaException
     */
    protected function uploadGuard() {
        global $conf;

        // sanity
        if($this->isExternal())
            throw new MediaInputException('You can\'t upload to an external file', 2);

        // upload ACL check
        if($this->auth < AUTH_UPLOAD)
            throw new MediaPermissionException('ACL: no permission to upload media', 1);

        // build mimetype regexp
        if(!$this->mimetyperegex) {
            $types               = array_keys(getMimeTypes());
            $types               = array_map('preg_quote_cb', $types);
            $this->mimetyperegex = join('|', $types);
        }
        // check if this is an allowed extension
        if(!preg_match('/^('.$this->mimetyperegex.')$/i', $this->extension)) {
            throw new MediaContentException('This extension is not allowed', 4);
        }

        //check for overwrite
        if($this->exists()) {
            if(!$this->getAllowOverwrite()) throw new MediaPermissionException('uploadexists', 3);
            if(!$conf['mediarevisions'] && $this->auth < AUTH_DELETE) throw new MediaPermissionException('ACL not enough permissions to overwrite', 1);
        }
    }

    /**
     * Handles media file deletions
     *
     * If configured, checks for media references before deletion
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @fixme  needs to trigger MEDIA_DELETE_FILE
     */
    public function delete() {
        global $lang;
        global $conf;

        if($this->isExternal()) throw new MediaInputException('Cannot delete external media', 2);
        if($this->auth < AUTH_DELETE) throw new MediaPermissionException('ACL: no permission to delete media', 1);
        if($conf['refshow'] && $this->usedBy(1)) throw new MediaPermissionException('Media is still in use', 4);
        if(!$this->exists()) return; // we treat deleting non-existant files as success

        // FIXME trigger an event - MEDIA_DELETE_FILE

        // add old revision to the attic
        $this->saveOldRevision();

        // action
        if(unlink($this->file)) {
            addMediaLogEntry(time(), $this->id, DOKU_CHANGE_TYPE_DELETE, $lang['deleted']);
            io_sweepNS($this->id, 'mediadir');
            clearstatcache(true, $this->file);
            return;
        }
        throw new MediaPermissionException('File deletion failed, check file permissions', 5);
    }

    /**
     * Copy the current revision to the attic
     *
     * Does nothing if the attic copy already exists or if media revisions
     * are disabled
     *
     * @author   Kate Arzamastseva <pshns@ukr.net>
     * @return int - revision date, false on error
     */
    protected function saveOldRevision() {
        global $conf, $lang;

        if(!$this->exists()) return false;
        $rev       = filemtime($this->file);
        $atticfile = mediaFN($this->id, $rev);
        if(file_exists($atticfile)) return $rev; // old revision already exists
        if(!$conf['mediarevisions']) return $rev; // no old revision wanted

        // make sure an initial log entry exists
        $changelog = new MediaChangeLog($this->id);
        if(!$changelog->getRevisionInfo($rev)) {
            // there was an external edit,
            // there is no log entry for current version of file
            if(!@file_exists(mediaMetaFN($this->id, '.changes'))) {
                addMediaLogEntry($rev, $this->id, DOKU_CHANGE_TYPE_CREATE, $lang['created']);
            } else {
                addMediaLogEntry($rev, $this->id, DOKU_CHANGE_TYPE_EDIT);
            }
        }

        io_makeFileDir($atticfile);
        if(copy($this->file, $atticfile)) {
            // Set the correct permission
            if($conf['fperm']) chmod($atticfile, $conf['fperm']);
        }
        return $rev;
    }

    /**
     * Send a notify mail on uploads
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    protected function uploadNotify($old_rev = false) {
        global $conf;
        if(empty($conf['notify'])) return true; // notify not enabled, treat as success

        $subscription = new Subscription();
        return $subscription->send_media_diff($conf['notify'], 'uploadmail', $this->id, $old_rev, '');
    }

    /**
     * Check if a media item is public (eg, external URL or readable by @ALL)
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @return bool
     */
    public function isPublic() {
        if(media_isexternal($this->id)) return true;
        if(auth_aclcheck(getNS($this->id).':*', '', array()) >= AUTH_READ) return true;
        return false;
    }

    /**
     * check if the media is external source
     *
     * @return bool
     */
    public function isExternal() {
        return $this->isexternal;
    }

    /**
     * Get a list of pages using this media file
     *
     * @see ft_mediause()
     *
     * @param int $max maximum number of references, 0 = $conf['refshow']
     *
     * @return array
     */
    public function usedBy($max = 0) {
        global $conf;
        if(!$max) $max = $conf['refshow'];
        return ft_mediause($this->id, $max);
    }

    /**
     * Check if the file exists locally
     */
    public function exists() {
        return file_exists($this->file);
    }

    /**
     * @return string the file's mimetype
     */
    public function getMimeType() {
        return $this->mimetype;
    }

    /**
     * @return string the canonical file extension
     */
    public function getExtension() {
        return $this->extension;
    }

    /**
     * @return bool true if the file should not be displayed inline
     */
    public function isDownload() {
        return $this->isdownload;
    }

    /**
     * @return bool may the file be overwritten by new uploads?
     */
    public function getAllowOverwrite() {
        return $this->allowoverwrite;
    }

    /**
     * @param bool $ok allow the file to be overwritten by new uploads
     */
    public function setAllowOverwrite($ok = true) {
        $this->allowoverwrite = (bool) $ok;
    }

    /**
     * Return a temporary file name
     *
     * @todo register the name to be unlinked in deconstructor?
     *
     * @throws MediaUploadException
     * @return string
     */
    protected function getTempName() {
        if(!($tmp = io_mktmpdir())) throw new MediaUploadException('Failed to create temp dir', 1);
        return $tmp.'/'.md5($this->id);
    }

    /**
     * Return the local location of the media file
     *
     * This will return false for uncached external ressources
     *
     * @return bool|string path to file or false if it doesn't exist
     */
    public function getFile() {
        if($this->exists()) return $this->file;
        if(!$this->isExternal()) return false;
        return $this->file;
    }
}

/**
 * Class MediaException
 *
 * General exception, shouldn't be used directly
 */
class MediaException extends Exception {
    /**
     * @param string       $message a message or language key
     * @param int          $code
     * @param string|array $params  sprintf parameters for the message string
     * @param Exception    $previous
     */
    public function __construct($message = "", $code = 0, $params = array(), Exception $previous = null) {
        global $lang;
        if(isset($lang[$message])) {
            $message = sprintf($lang[$message], (array) $params);
        } else if($params) {
            $message .= ' ('.join(', ', (array) $params).')';
        }
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Class MediaContentException
 *
 * Upload contained bad content
 *
 * Code 1 - probable XSS attack
 * Code 2 - not a valid image
 * Code 3 - spam detected
 * Code 4 - disallowed extension
 */
class MediaContentException extends MediaException {
}

/**
 * Class MediaUploadException
 *
 * Errors on uploading
 *
 * Code 1 - IO Error
 * Code 2 - PHP handled form upload error
 * Code 3 - Not an uploaded file at all
 */
class MediaUploadException extends MediaException {
}

/**
 * Class MediaInputException
 *
 * Bad input provided
 *
 * Code 1 - missing or wrong parameter
 * Code 2 - no uploads to external
 */
class MediaInputException extends MediaException {
}

/**
 * Class MediaPermissionException
 *
 * Operation not permitted
 *
 * Code 1 - missing ACL permission
 * Code 3 - file exists, no overwrite
 * Code 4 - media still in use
 */
class MediaPermissionException extends MediaException {
}