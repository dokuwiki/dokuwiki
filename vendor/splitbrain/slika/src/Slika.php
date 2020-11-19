<?php


namespace splitbrain\slika;


class Slika
{
    /** @var int rotate an image counter clock wise */
    const ROTATE_CCW = 8;
    /** @var int rotate an image clock wise */
    const ROTATE_CW = 6;
    /** @var int rotate on it's head */
    const ROTATE_TOPDOWN = 3;


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
