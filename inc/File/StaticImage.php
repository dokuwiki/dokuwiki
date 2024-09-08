<?php

namespace dokuwiki\File;

/**
 * Access a static file from lib/images with optinal override in conf/images
 */
class StaticImage
{
    protected string $path;

    /**
     * @param string $path the path relative to lib/images
     */
    public function __construct(string $path)
    {
        require_once(DOKU_INC . 'inc/fetch.functions.php'); // late load when needed

        $path = preg_replace('/\.\.+/', '.', $path);
        $path = preg_replace('/\/\/+/', '/', $path);
        $path = trim($path, '/');
        $this->path = $path;
    }

    /**
     * Static convenience method to get the real path to an image
     *
     * @param string $path
     * @return string
     */
    public static function path(string $path): string
    {
        return (new self($path))->getRealPath();
    }

    /**
     * Static convenience method to get the URL to an image
     *
     * @param string $path
     * @return string
     */
    public static function url(string $path): string
    {
        return (new self($path))->getURL();
    }

    /**
     * @return string the mime type of the image
     */
    public function getMimeType()
    {
        [/*ext*/, $mime] = mimetype($this->path, false);
        if ($mime === false) throw new \RuntimeException('Unknown mime type');
        return $mime;
    }

    /**
     * @return string the real path to the image
     */
    public function getRealPath()
    {
        // overridden image
        $path = DOKU_CONF . 'images/' . $this->path;
        if (file_exists($path)) return $path;

        // default image
        $path = DOKU_INC . 'lib/images/' . $this->path;
        if (file_exists($path)) return $path;

        throw new \RuntimeException('Image not found');
    }

    /**
     * @return string the URL to the image
     */
    public function getURL()
    {
        return DOKU_BASE . 'lib/exe/image.php/' . $this->path;
    }

    /**
     * Serve the image to the client
     */
    public function serve()
    {
        $path = $this->getRealPath();
        $mime = $this->getMimeType();
        sendFile($path, $mime, false, -1, true);
    }
}
