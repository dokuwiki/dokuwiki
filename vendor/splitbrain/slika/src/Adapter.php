<?php


namespace splitbrain\slika;

/**
 * Base class for image adapters. Defines what image processing adapters need to provide
 */
abstract class Adapter
{
    /** @var string path to the image */
    protected $imagepath;

    /** @var array Adapter Options */
    protected $options;

    /**
     * New Slika Adapter
     *
     * @param string $imagepath path to the original image
     * @param array $options set options
     * @throws Exception
     */
    public function __construct($imagepath, $options = [])
    {
        if (!file_exists($imagepath)) {
            throw new Exception('image file does not exist');
        }

        if (!is_readable($imagepath)) {
            throw new Exception('image file is not readable');
        }

        $this->imagepath = $imagepath;
        $this->options = array_merge(Slika::DEFAULT_OPTIONS, $options);
    }

    /**
     * Rote the image based on the rotation exif tag
     *
     * @return Adapter
     */
    abstract public function autorotate();

    /**
     * Rotate and/or flip the image
     *
     * This expects an orientation flag as stored in EXIF data. For typical operations,
     * Slika::ROTATE_* constants are defined.
     *
     * @param int $orientation Exif rotation flags
     * @return Adapter
     * @see https://stackoverflow.com/a/53697440 for info on the rotation constants
     */
    abstract public function rotate($orientation);

    /**
     * Resize to make image fit the given dimension (maintaining the aspect ratio)
     *
     * You may omit one of the dimensions to auto calculate it based on the aspect ratio
     *
     * @param int|string $width in pixels or %
     * @param int|string $height in pixels or %
     * @return Adapter
     */
    abstract public function resize($width, $height);


    /**
     * Resize to the given dimension, cropping the image as needed
     *
     * You may omit one of the dimensions to use a square area
     *
     * @param int|string $width in pixels or %
     * @param int|string $height in pixels or %
     * @return Adapter
     */
    abstract public function crop($width, $height);

    /**
     * Save the new file
     *
     * @param string $path
     * @param string $extension The type of image to save, empty for original
     * @return void
     */
    abstract public function save($path, $extension = '');

}
