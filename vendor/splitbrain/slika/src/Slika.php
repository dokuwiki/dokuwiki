<?php


namespace splitbrain\slika;

/**
 * Factory to process an image using an available Adapter
 */
class Slika
{
    /** rotate an image counter clock wise */
    const ROTATE_CCW = 8;
    /** rotate an image clock wise */
    const ROTATE_CW = 6;
    /** rotate on it's head */
    const ROTATE_TOPDOWN = 3;

    /** these can be overwritten using the options array in run() */
    const DEFAULT_OPTIONS = [
        'quality' => 92,
        'imconvert' => '/usr/bin/convert',
    ];

    /**
     * This is a factory only, thus the constructor is private
     */
    private function __construct()
    {
        // there is no constructor.
    }

    /**
     * Start processing the image
     *
     * @param string $imagePath
     * @param array $options
     * @return Adapter
     * @throws Exception
     */
    public static function run($imagePath, $options = [])
    {
        $options = array_merge(self::DEFAULT_OPTIONS, $options);

        if (is_executable($options['imconvert'])) {
            return new ImageMagickAdapter($imagePath, $options);
        }

        if (function_exists('gd_info')) {
            return new GdAdapter($imagePath, $options);
        }

        throw new Exception('No suitable Adapter found');
    }

}
