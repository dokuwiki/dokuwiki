<?php

namespace dokuwiki\Media;

use dokuwiki\Utf8\PhpString;

class MediaFile
{
    protected $id;
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
        $this->path = mediaFN($id);

        list($this->mime, $this->ext, $this->downloadable) = mimetype($this->path, false);
    }

    /**
     * Factory to create a new MediaFile from a Media Path
     *
     * @param string $path The path of a file, relative to the media base dir
     * @return MediaFile
     */
    static public function fromRelativePath($path)
    {
        if ($path[0] === '/' || $path[1] === ':') {
            throw new \RuntimeException('Only paths relative to the media directory may be used');
        }

        return new MediaFile(pathID($path, true));
    }

    /** @return string */
    public function getId()
    {
        return $this->id;
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
        return (substr($this->mime, 0, 6) === 'image/');
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
        list($this->width, $this->height) = $info;
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

    /** @return \JpegMeta */
    public function getMeta()
    {
        if($this->meta === null) $this->meta = new \JpegMeta($this->path);
        return $this->meta;
    }
}
