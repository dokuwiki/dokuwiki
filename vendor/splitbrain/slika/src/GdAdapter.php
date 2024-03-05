<?php /** @noinspection PhpComposerExtensionStubsInspection */


namespace splitbrain\slika;

/**
 * Image processing adapter for PHP's libGD
 */
class GdAdapter extends Adapter
{
    /** @var resource libGD image */
    protected $image;
    /** @var int width of the current image */
    protected $width = 0;
    /** @var int height of the current image */
    protected $height = 0;
    /** @var string the extension of the file we're working with */
    protected $extension;


    /** @inheritDoc */
    public function __construct($imagepath, $options = [])
    {
        parent::__construct($imagepath, $options);
        $this->image = $this->loadImage($imagepath);
    }

    /**
     * Clean up
     */
    public function __destruct()
    {
        if (is_resource($this->image)) {
            imagedestroy($this->image);
        }
    }

    /** @inheritDoc
     * @throws Exception
     * @link https://gist.github.com/EionRobb/8e0c76178522bc963c75caa6a77d3d37#file-imagecreatefromstring_autorotate-php-L15
     */
    public function autorotate()
    {
        if ($this->extension !== 'jpeg') {
            return $this;
        }

        $orientation = 1;

        if (function_exists('exif_read_data')) {
            // use PHP's exif capablities
            $exif = exif_read_data($this->imagepath);
            if (!empty($exif['Orientation'])) {
                $orientation = $exif['Orientation'];
            }
        } else {
            // grep the exif info from the raw contents
            // we read only the first 70k bytes
            $data = file_get_contents($this->imagepath, false, null, 0, 70000);
            if (preg_match('@\x12\x01\x03\x00\x01\x00\x00\x00(.)\x00\x00\x00@', $data, $matches)) {
                // Little endian EXIF
                $orientation = ord($matches[1]);
            } else if (preg_match('@\x01\x12\x00\x03\x00\x00\x00\x01\x00(.)\x00\x00@', $data, $matches)) {
                // Big endian EXIF
                $orientation = ord($matches[1]);
            }
        }

        return $this->rotate($orientation);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function rotate($orientation)
    {
        $orientation = (int)$orientation;
        if ($orientation < 0 || $orientation > 8) {
            throw new Exception('Unknown rotation given');
        }

        if ($orientation <= 1) {
            // no rotation wanted
            return $this;
        }

        // fill color
        $transparency = imagecolorallocatealpha($this->image, 0, 0, 0, 127);

        // rotate
        if (in_array($orientation, [3, 4])) {
            $image = imagerotate($this->image, 180, $transparency);
        }
        if (in_array($orientation, [5, 6])) {
            $image = imagerotate($this->image, -90, $transparency);
            list($this->width, $this->height) = [$this->height, $this->width];
        } elseif (in_array($orientation, [7, 8])) {
            $image = imagerotate($this->image, 90, $transparency);
            list($this->width, $this->height) = [$this->height, $this->width];
        }
        /** @var resource $image is now defined */

        // additionally flip
        if (in_array($orientation, [2, 5, 7, 4])) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        }

        imagedestroy($this->image);
        $this->image = $image;

        //keep png alpha channel if possible
        if ($this->extension == 'png' && function_exists('imagesavealpha')) {
            imagealphablending($this->image, false);
            imagesavealpha($this->image, true);
        }

        return $this;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function resize($width, $height)
    {
        list($width, $height) = $this->boundingBox($width, $height);
        $this->resizeOperation($width, $height);
        return $this;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function crop($width, $height)
    {
        list($this->width, $this->height, $offsetX, $offsetY) = $this->cropPosition($width, $height);
        $this->resizeOperation($width, $height, $offsetX, $offsetY);
        return $this;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function save($path, $extension = '')
    {
        if ($extension === 'jpg') {
            $extension = 'jpeg';
        }
        if ($extension === '') {
            $extension = $this->extension;
        }
        $saver = 'image' . $extension;
        if (!function_exists($saver)) {
            throw new Exception('Can not save image format ' . $extension);
        }

        if ($extension == 'jpeg') {
            imagejpeg($this->image, $path, $this->options['quality']);
        } else {
            $saver($this->image, $path);
        }

        imagedestroy($this->image);
    }

    /**
     * Initialize libGD on the given image
     *
     * @param string $path
     * @return resource
     * @throws Exception
     */
    protected function loadImage($path)
    {
        // Figure out the file info
        $info = getimagesize($path);
        if ($info === false) {
            throw new Exception('Failed to read image information');
        }
        $this->width = $info[0];
        $this->height = $info[1];

        // what type of image is it?
        $this->extension = image_type_to_extension($info[2], false);
        $creator = 'imagecreatefrom' . $this->extension;
        if (!function_exists($creator)) {
            throw new Exception('Can not work with image format ' . $this->extension);
        }

        // create the GD instance
        $image = @$creator($path);

        if ($image === false) {
            throw new Exception('Failed to load image wiht libGD');
        }

        return $image;
    }

    /**
     * Creates a new blank image to which we can copy
     *
     * Tries to set up alpha/transparency stuff correctly
     *
     * @param int $width
     * @param int $height
     * @return resource
     * @throws Exception
     */
    protected function createImage($width, $height)
    {
        // create a canvas to copy to, use truecolor if possible (except for gif)
        $canvas = false;
        if (function_exists('imagecreatetruecolor') && $this->extension != 'gif') {
            $canvas = @imagecreatetruecolor($width, $height);
        }
        if (!$canvas) {
            $canvas = @imagecreate($width, $height);
        }
        if (!$canvas) {
            throw new Exception('Failed to create new canvas');
        }

        //keep png alpha channel if possible
        if ($this->extension == 'png' && function_exists('imagesavealpha')) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
        }

        //keep gif transparent color if possible
        if ($this->extension == 'gif') {
            $this->keepGifTransparency($this->image, $canvas);
        }

        return $canvas;
    }

    /**
     * Copy transparency from gif to gif
     *
     * If no transparency is found or the PHP does not support it, the canvas is filled with white
     *
     * @param resource $image Original image
     * @param resource $canvas New, empty image
     * @return void
     */
    protected function keepGifTransparency($image, $canvas)
    {
        if (!function_exists('imagefill') || !function_exists('imagecolorallocate')) {
            return;
        }

        try {
            if (!function_exists('imagecolorsforindex') || !function_exists('imagecolortransparent')) {
                throw new \Exception('missing alpha methods');
            }

            $transcolorindex = @imagecolortransparent($image);
            $transcolor = @imagecolorsforindex($image, $transcolorindex);
            if (!$transcolor) {
                // pre-PHP8 false is returned, in PHP8 an exception is thrown
                throw new \ValueError('no valid alpha color');
            }

            $transcolorindex = @imagecolorallocate(
                $canvas,
                $transcolor['red'],
                $transcolor['green'],
                $transcolor['blue']
            );
            @imagefill($canvas, 0, 0, $transcolorindex);
            @imagecolortransparent($canvas, $transcolorindex);

        } catch (\Throwable $ignored) {
            //filling with white
            $whitecolorindex = @imagecolorallocate($canvas, 255, 255, 255);
            @imagefill($canvas, 0, 0, $whitecolorindex);
        }
    }

    /**
     * Calculate new size
     *
     * If widht and height are given, the new size will be fit within this bounding box.
     * If only one value is given the other is adjusted to match according to the aspect ratio
     *
     * @param int $width width of the bounding box
     * @param int $height height of the bounding box
     * @return array (width, height)
     * @throws Exception
     */
    protected function boundingBox($width, $height)
    {
        $width = $this->cleanDimension($width, $this->width);
        $height = $this->cleanDimension($height, $this->height);

        if ($width == 0 && $height == 0) {
            throw new Exception('You can not resize to 0x0');
        }

        if (!$height) {
            // adjust to match width
            $height = round(($width * $this->height) / $this->width);
        } else if (!$width) {
            // adjust to match height
            $width = round(($height * $this->width) / $this->height);
        } else {
            // fit into bounding box
            $scale = min($width / $this->width, $height / $this->height);
            $width = $this->width * $scale;
            $height = $this->height * $scale;
        }

        return [$width, $height];
    }

    /**
     * Ensure the given Dimension is a proper pixel value
     *
     * When a percentage is given, the value is calculated based on the given original dimension
     *
     * @param int|string $dim New Dimension
     * @param int $orig Original dimension
     * @return int
     */
    protected function cleanDimension($dim, $orig)
    {
        if ($dim && substr($dim, -1) == '%') {
            $dim = round($orig * ((float)$dim / 100));
        } else {
            $dim = (int)$dim;
        }

        return $dim;
    }

    /**
     * Calculates crop position
     *
     * Given the wanted final size, this calculates which exact area needs to be cut
     * from the original image to be then resized to the wanted dimensions.
     *
     * @param int $width
     * @param int $height
     * @return array (cropWidth, cropHeight, offsetX, offsetY)
     * @throws Exception
     */
    protected function cropPosition($width, $height)
    {
        if ($width == 0 && $height == 0) {
            throw new Exception('You can not crop to 0x0');
        }

        if (!$height) {
            $height = $width;
        }

        if (!$width) {
            $width = $height;
        }

        // calculate ratios
        $oldRatio = $this->width / $this->height;
        $newRatio = $width / $height;

        // calulate new size
        if ($newRatio >= 1) {
            if ($newRatio > $oldRatio) {
                $cropWidth = $this->width;
                $cropHeight = (int)($this->width / $newRatio);
            } else {
                $cropWidth = (int)($this->height * $newRatio);
                $cropHeight = $this->height;
            }
        } else {
            if ($newRatio < $oldRatio) {
                $cropWidth = (int)($this->height * $newRatio);
                $cropHeight = $this->height;
            } else {
                $cropWidth = $this->width;
                $cropHeight = (int)($this->width / $newRatio);
            }
        }

        // calculate crop offset
        $offsetX = (int)(($this->width - $cropWidth) / 2);
        $offsetY = (int)(($this->height - $cropHeight) / 2);

        return [$cropWidth, $cropHeight, $offsetX, $offsetY];
    }

    /**
     * resize or crop images using PHP's libGD support
     *
     * @param int $toWidth desired width
     * @param int $toHeight desired height
     * @param int $offsetX offset of crop centre
     * @param int $offsetY offset of crop centre
     * @throws Exception
     */
    protected function resizeOperation($toWidth, $toHeight, $offsetX = 0, $offsetY = 0)
    {
        $newimg = $this->createImage($toWidth, $toHeight);

        //try resampling first, fall back to resizing
        if (
            !function_exists('imagecopyresampled') ||
            !@imagecopyresampled(
                $newimg,
                $this->image,
                0,
                0,
                $offsetX,
                $offsetY,
                $toWidth,
                $toHeight,
                $this->width,
                $this->height
            )
        ) {
            imagecopyresized(
                $newimg,
                $this->image,
                0,
                0,
                $offsetX,
                $offsetY,
                $toWidth,
                $toHeight,
                $this->width,
                $this->height
            );
        }

        // destroy original GD image ressource and replace with new one
        imagedestroy($this->image);
        $this->image = $newimg;
        $this->width = $toWidth;
        $this->height = $toHeight;
    }

}
