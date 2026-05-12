<?php


namespace splitbrain\slika;

/**
 * Lightweight, metadata-only inspection of an image.
 *
 * Uses only getimagesize() and EXIF parsing; never loads pixels or execs
 * ImageMagick. Mirrors Adapter's fluent API (autorotate/rotate/resize/crop)
 * at the dimension level, so callers can predict the dimensions an Adapter
 * chain would produce and emit correct width/height HTML attributes
 * without actually processing the image.
 */
class ImageInfo
{
    /** @var string path to the image */
    protected $imagepath;
    /** @var int raw width as stored on disk */
    protected $rawWidth;
    /** @var int raw height as stored on disk */
    protected $rawHeight;
    /** @var string image format as returned by image_type_to_extension (e.g. 'jpeg', 'png') */
    protected $extension;
    /** @var int EXIF orientation 1..8; always 1 for non-JPEG */
    protected $orientation;
    /** @var int currently tracked width (reflects chain operations) */
    protected $width;
    /** @var int currently tracked height (reflects chain operations) */
    protected $height;

    /**
     * @param string $imagepath
     * @throws Exception when the file cannot be read or is not an image
     */
    public function __construct($imagepath)
    {
        if (!file_exists($imagepath)) {
            throw new Exception('image file does not exist');
        }
        if (!is_readable($imagepath)) {
            throw new Exception('image file is not readable');
        }

        $info = @getimagesize($imagepath);
        if ($info === false) {
            throw new Exception('Failed to read image information');
        }

        $this->imagepath = $imagepath;
        $this->rawWidth = (int)$info[0];
        $this->rawHeight = (int)$info[1];
        $this->extension = image_type_to_extension($info[2], false);

        $this->width = $this->rawWidth;
        $this->height = $this->rawHeight;

        if ($this->extension === 'jpeg') {
            $this->orientation = self::readExifOrientation($imagepath);
        } else {
            $this->orientation = 1;
        }
    }

    /**
     * @return int width as stored on disk (stable regardless of chain ops)
     */
    public function getRawWidth()
    {
        return $this->rawWidth;
    }

    /**
     * @return int height as stored on disk (stable regardless of chain ops)
     */
    public function getRawHeight()
    {
        return $this->rawHeight;
    }

    /**
     * @return string 'jpeg', 'png', 'gif', 'webp', ...
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @return int EXIF orientation 1..8, defaults to 1 for non-JPEG or missing tag
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * @return int currently tracked width (after any chain operations)
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int currently tracked height (after any chain operations)
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return array [width, height] currently tracked
     */
    public function getDimensions()
    {
        return [$this->width, $this->height];
    }

    /**
     * Simulate Adapter::autorotate() at the dimension level.
     *
     * For JPEGs with EXIF orientation 5/6/7/8 the tracked width and height
     * are swapped; all other cases are no-ops.
     *
     * @return $this
     * @throws Exception
     */
    public function autorotate()
    {
        if ($this->extension !== 'jpeg') {
            return $this;
        }
        return $this->rotate($this->orientation);
    }

    /**
     * Simulate Adapter::rotate() at the dimension level.
     *
     * @param int $orientation EXIF rotation flag 0..8
     * @return $this
     * @throws Exception on invalid orientation
     */
    public function rotate($orientation)
    {
        $orientation = (int)$orientation;
        if ($orientation < 0 || $orientation > 8) {
            throw new Exception('Unknown rotation given');
        }
        if (in_array($orientation, [5, 6, 7, 8])) {
            list($this->width, $this->height) = [$this->height, $this->width];
        }
        return $this;
    }

    /**
     * Simulate Adapter::resize() at the dimension level.
     *
     * Fits the image into the given bounding box while preserving the
     * aspect ratio. Omitting one dimension (0 or empty) auto-calculates it.
     *
     * @param int|string $width in pixels or %
     * @param int|string $height in pixels or %
     * @return $this
     * @throws Exception when both dimensions are zero
     */
    public function resize($width, $height)
    {
        list($w, $h) = self::boundingBox($this->width, $this->height, $width, $height);
        $this->width = (int)$w;
        $this->height = (int)$h;
        return $this;
    }

    /**
     * Simulate Adapter::crop() at the dimension level.
     *
     * Result equals the output size of Adapter::crop(): exactly ($w, $h)
     * when both are given, or a ($w, $w) / ($h, $h) square when only one is.
     *
     * @param int|string $width in pixels or %
     * @param int|string $height in pixels or %
     * @return $this
     * @throws Exception when both dimensions are zero
     */
    public function crop($width, $height)
    {
        $width = self::cleanDimension($width, $this->width);
        $height = self::cleanDimension($height, $this->height);

        if ($width == 0 && $height == 0) {
            throw new Exception('You can not crop to 0x0');
        }

        if (!$height) {
            $height = $width;
        }
        if (!$width) {
            $width = $height;
        }

        $this->width = (int)$width;
        $this->height = (int)$height;
        return $this;
    }

    /**
     * Read the EXIF orientation tag of a JPEG file.
     *
     * Prefers exif_read_data() when available; otherwise falls back to a
     * raw-byte scan of the first 70 KB of the file. Returns 1 when no
     * orientation tag is found.
     *
     * @param string $path
     * @return int 1..8
     */
    public static function readExifOrientation($path)
    {
        if (function_exists('exif_read_data')) {
            $exif = exif_read_data($path);
            if (!empty($exif['Orientation'])) {
                return (int)$exif['Orientation'];
            }
            return 1;
        }
        return self::readExifOrientationFromBytes($path);
    }

    /**
     * Raw-byte fallback for reading the EXIF orientation tag.
     *
     * Exposed so the fallback path can be tested even on systems with the
     * exif extension installed.
     *
     * @param string $path
     * @return int 1..8
     * @link https://gist.github.com/EionRobb/8e0c76178522bc963c75caa6a77d3d37#file-imagecreatefromstring_autorotate-php-L15
     */
    public static function readExifOrientationFromBytes($path)
    {
        $data = @file_get_contents($path, false, null, 0, 70000);
        if ($data === false) {
            return 1;
        }
        if (preg_match('@\x12\x01\x03\x00\x01\x00\x00\x00(.)\x00\x00\x00@', $data, $matches)) {
            // little endian EXIF
            return ord($matches[1]);
        }
        if (preg_match('@\x01\x12\x00\x03\x00\x00\x00\x01\x00(.)\x00\x00@', $data, $matches)) {
            // big endian EXIF
            return ord($matches[1]);
        }
        return 1;
    }

    /**
     * Calculate new size to fit into a bounding box, preserving aspect ratio.
     *
     * If width and height are given, the result is scaled to fit inside the
     * bounding box. If only one dimension is given, the other is calculated
     * from the aspect ratio.
     *
     * @param int $origW current width
     * @param int $origH current height
     * @param int|string $width target width (pixels or %)
     * @param int|string $height target height (pixels or %)
     * @return array [width, height]
     * @throws Exception
     */
    public static function boundingBox($origW, $origH, $width, $height)
    {
        $width = self::cleanDimension($width, $origW);
        $height = self::cleanDimension($height, $origH);

        if ($width == 0 && $height == 0) {
            throw new Exception('You can not resize to 0x0');
        }

        if (!$height) {
            // adjust to match width
            $height = round(($width * $origH) / $origW);
        } else if (!$width) {
            // adjust to match height
            $width = round(($height * $origW) / $origH);
        } else {
            // fit into bounding box
            $scale = min($width / $origW, $height / $origH);
            $width = $origW * $scale;
            $height = $origH * $scale;
        }

        return [$width, $height];
    }

    /**
     * Normalize a dimension value to a pixel count.
     *
     * Accepts an int or a percentage string ("50%"). The percentage is
     * resolved against the given original dimension.
     *
     * @param int|string $dim
     * @param int $orig
     * @return int
     */
    public static function cleanDimension($dim, $orig)
    {
        if ($dim && substr($dim, -1) == '%') {
            $dim = round($orig * ((float)$dim / 100));
        } else {
            $dim = (int)$dim;
        }
        return $dim;
    }
}
