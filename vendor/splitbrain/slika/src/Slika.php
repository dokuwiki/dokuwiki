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
        'imlimits' => [
            'memory' => '256MiB',
            'map' => '512MiB',
            'disk' => '1GiB',
        ],
    ];

    /**
     * This is a factory only, thus the constructor is private
     */
    private function __construct()
    {
        // there is no constructor.
    }

    /**
     * Apply the given options on top of the defaults
     *
     * Options holding an array of their own are merged key by key, so overriding a single
     * entry keeps the remaining defaults in place.
     *
     * @param array $options
     * @return array
     */
    public static function mergeOptions($options)
    {
        $merged = array_merge(self::DEFAULT_OPTIONS, $options);

        foreach (self::DEFAULT_OPTIONS as $option => $default) {
            if (is_array($default) && isset($options[$option]) && is_array($options[$option])) {
                $merged[$option] = array_merge($default, $options[$option]);
            }
        }

        return $merged;
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
        $options = self::mergeOptions($options);

        if (is_executable($options['imconvert'])) {
            return new ImageMagickAdapter($imagePath, $options);
        }

        if (function_exists('gd_info')) {
            return new GdAdapter($imagePath, $options);
        }

        throw new Exception('No suitable Adapter found');
    }

}
