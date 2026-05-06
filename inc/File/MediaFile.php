<?php

namespace dokuwiki\File;

use Exception;
use JpegMeta;
use splitbrain\slika\ImageInfo;

class MediaFile
{
    protected $id;
    protected $rev;
    protected $path;

    protected $mime;
    protected $ext;
    protected $downloadable;

    protected $width;
    protected $height;
    protected $meta;

    /**
     * MediaFile constructor.
     * @param string $id
     * @param string|int $rev optional revision
     */
    public function __construct($id, $rev = '')
    {
        $this->id = $id; //FIXME should it be cleaned?
        $this->path = mediaFN($id, $rev);
        $this->rev = $rev;

        [$this->ext, $this->mime, $this->downloadable] = mimetype($this->path, false);
    }

    /** @return string */
    public function getId()
    {
        return $this->id;
    }

    /** @return string|int Empty string for current version */
    public function getRev()
    {
        return $this->rev;
    }

    /** @return string */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * The ID without namespace, used for display purposes
     *
     * @return string
     */
    public function getDisplayName()
    {
        return noNS($this->id);
    }

    /** @return string */
    public function getMime()
    {
        if (!$this->mime) return 'application/octet-stream';
        return $this->mime;
    }

    /** @return string */
    public function getExtension()
    {
        return (string)$this->ext;
    }

    /**
     * Similar to the extesion but does some clean up
     *
     * @return string
     */
    public function getIcoClass()
    {
        $ext = $this->getExtension();
        if ($ext === '') $ext = 'file';
        return preg_replace('/[^_\-a-z0-9]+/i', '_', $ext);
    }

    /**
     * Should this file be downloaded instead being displayed inline?
     *
     * @return bool
     */
    public function isDownloadable()
    {
        return $this->downloadable;
    }

    /** @return int */
    public function getFileSize()
    {
        return filesize($this->path);
    }

    /** @return int */
    public function getLastModified()
    {
        return filemtime($this->path);
    }

    /** @return bool */
    public function isWritable()
    {
        return is_writable($this->path);
    }

    /** @return bool */
    public function isImage()
    {
        return (str_starts_with($this->mime, 'image/'));
    }

    /**
     * initializes width and height for images when requested
     */
    protected function initSizes()
    {
        $this->width = 0;
        $this->height = 0;
        if (!$this->isImage()) return;
        $info = getimagesize($this->path);
        if ($info === false) return;
        [$this->width, $this->height] = $info;
    }

    /**
     * Returns the width if this is a supported image, 0 otherwise
     *
     * @return int
     */
    public function getWidth()
    {
        if ($this->width === null) $this->initSizes();
        return $this->width;
    }

    /**
     * Returns the height if this is a supported image, 0 otherwise
     *
     * @return int
     */
    public function getHeight()
    {
        if ($this->height === null) $this->initSizes();
        return $this->height;
    }

    /**
     * Returns the final display dimensions a fetch.php URL with the given
     * parameters would produce, including EXIF auto-rotation
     *
     * @param int $w requested width (0 = no constraint)
     * @param int $h requested height (0 = no constraint)
     * @param bool $crop true for center-crop, false for bounding-box fit
     * @return array [width, height]; [0, 0] for non-images/unreadable files
     */
    public function getDisplayDimensions($w = 0, $h = 0, $crop = false)
    {
        try {
            $info = (new ImageInfo($this->path))->autorotate();
        } catch (Exception $e) {
            return [0, 0];
        }
        if ($w === 0 && $h === 0) return $info->getDimensions();
        if ($crop) return $info->crop($w, $h)->getDimensions();
        return $info->resize($w, $h)->getDimensions();
    }

    /**
     * Returns the permissions the current user has on the file
     *
     * @todo doing this for each file within a namespace is a waste, we need to cache this somehow
     * @return int
     */
    public function userPermission()
    {
        return auth_quickaclcheck(getNS($this->id) . ':*');
    }

    /** @return JpegMeta */
    public function getMeta()
    {
        if ($this->meta === null) $this->meta = new JpegMeta($this->path);
        return $this->meta;
    }
}
