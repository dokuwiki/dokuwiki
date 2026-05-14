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
        // destroy the GD image resource (only needed on PHP < 8.0)
        if (is_resource($this->image)) {
            imagedestroy($this->image);
        }
    }

    /** @inheritDoc
     * @throws Exception
     */
    public function autorotate()
    {
        if ($this->extension !== 'jpeg') {
            return $this;
        }
        return $this->rotate(ImageInfo::readExifOrientation($this->imagepath));
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

        // rotate (orientation 2 is a flip-only case and keeps $this->image)
        $image = $this->image;
        if (in_array($orientation, [3, 4])) {
            $image = imagerotate($this->image, 180, $transparency);
        } elseif (in_array($orientation, [5, 6])) {
            $image = imagerotate($this->image, -90, $transparency);
            list($this->width, $this->height) = [$this->height, $this->width];
        } elseif (in_array($orientation, [7, 8])) {
            $image = imagerotate($this->image, 90, $transparency);
            list($this->width, $this->height) = [$this->height, $this->width];
        }

        // additionally flip
        if (in_array($orientation, [2, 5, 7, 4])) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        }

        if ($image !== $this->image) {
            $this->__destruct(); // destroy old image
            $this->image = $image;
        }

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
        list($width, $height) = ImageInfo::boundingBox($this->width, $this->height, $width, $height);
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

        $this->__destruct();
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
        $this->__destruct();
        $this->image = $newimg;
        $this->width = $toWidth;
        $this->height = $toHeight;
    }

}
