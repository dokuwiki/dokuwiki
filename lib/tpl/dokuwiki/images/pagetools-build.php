#!/usr/bin/php
<?php
if(!defined('DOKU_INC')) define('DOKU_INC', realpath(dirname(__FILE__).'/../../../../').'/');
define('NOSESSION', 1);
require_once(DOKU_INC.'inc/init.php');

/**
 * Class PagetoolBuilder
 *
 * Create the pagetools-sprite
 */
class PagetoolBuilder extends DokuCLI {
    /**
     * @var float opacity of the sprite
     */
    protected $opacity = 0.7;

    /**
     * @var int size of single sprite in pixels
     */
    protected $width = 30;

    /**
     * @var int border to use on each sprite in pixels
     */
    protected $border = 0;

    /**
     * @var int spacing between sprites in pixels
     */
    protected $space = 15;

    /**
     * @var string the output file
     */
    protected $output = 'pagetools-sprite.png';

    /**
     * @var string default location of optipng
     */
    protected $optipng = '/usr/bin/optipng';

    /**
     * @var string primary color
     */
    protected $primary = null;

    /**
     * @var string secondary color
     */
    protected $secondary = null;

    /**
     * Constructor
     *
     * initializes the colors
     */
    public function __construct() {
        parent::__construct();

        $ini             = parse_ini_file(__DIR__.'/../style.ini', true);
        $this->primary   = $ini['replacements']['__link__'];
        $this->secondary = $ini['replacements']['__text_alt__'];
    }

    /**
     * Register options and arguments on the given $options object
     *
     * @param DokuCLI_Options $options
     * @return void
     */
    protected function setup(DokuCLI_Options $options) {
        $options->registerOption(
            'primary',
            'The primary color (HTML hex) to use, (defaults to template\'s __link__ replacement)',
            'p',
            'color'
        );
        $options->registerOption(
            'secondary',
            'The secondary color (HTML hex) to use, (defaults to template\'s __text_alt__ replacement)',
            's',
            'color'
        );
        $options->registerOption(
            'output',
            'Name of the sprite to generate, defaults to pagetools-sprite.png',
            'o',
            'file'
        );
        $options->registerOption(
            'optipng',
            'Path to the optipng binary. Defaults to /usr/bin/optipng',
            '',
            'path'
        );
        $options->registerOption(
            'size',
            'Width and height of the indvidual images (square!). Defaults to 32',
            'z',
            'size'
        );
        $options->registerOption(
            'border',
            'Border to add around each image in pixels. Border is added within width. Defaults to 0',
            'b',
            'border'
        );
        $options->setHelp('Creates a sprite image to use in the floating page tools');
        $options->registerArgument(
            'dir|files...',
            'The input images in correct order or a directory to read',
            true
        );
    }

    /**
     * Your main program
     *
     * Arguments and options have been parsed when this is run
     *
     * @param DokuCLI_Options $options
     * @return void
     */
    protected function main(DokuCLI_Options $options) {
        // handle options
        $this->primary   = $options->getOpt('primary', $this->primary);
        $this->secondary = $options->getOpt('secondary', $this->secondary);
        $this->optipng   = $options->getOpt('optipng', $this->optipng);
        $this->output    = $options->getOpt('output', $this->output);
        $this->width     = $options->getOpt('size', $this->width);
        $this->border    = $options->getOpt('border',$this->border);

        // first argument may be a directory
        $args = $options->args;
        if(is_dir($args[0])) {
            $args = glob($args[0].'/*.png');
            sort($args);
        }
        $cnt = count($args);
        if(!$cnt) $this->fatal('No images found');

        // create destination image
        $sprite = imagecreatetruecolor($this->width, $cnt * ($this->width + $this->space) * 2);
        imagesavealpha($sprite, true);
        $C_trans = imagecolorallocatealpha($sprite, 0, 0, 0, 127);
        imagefill($sprite, 0, 0, $C_trans);

        // add all the icons to the sprite image
        for($i = 0; $i < $cnt; $i++) {
            $this->info($args[$i]);

            // inactive version
            $offset = $i * ($this->width * 2 + $this->space * 2); // y-offset for top of the pair
            $this->copyColored($sprite, $args[$i], $this->hex2rgb($this->secondary), $offset);

            // active version
            $offset = $offset + $this->width + $this->space; // y-offset for bottom of the pair
            $this->copyColored($sprite, $args[$i], $this->hex2rgb($this->primary), $offset);
        }

        // set opacity
        $this->info('adjusting opacity');
        $this->filter_opacity($sprite, $this->opacity);

        // output sprite
        if(imagepng($sprite, $this->output)) {
            $this->success($this->output.' created.');
            $this->optimize();
        }
        imagedestroy($sprite);
    }

    /**
     * Optimize the output file if possible
     */
    protected function optimize() {
        if(!file_exists($this->output)) return;

        if(!is_executable($this->optipng)) {
            $this->error($this->optipng.' not found, not optimizing the sprite');
            return;
        }

        $file = escapeshellarg($this->output);
        system($this->optipng.' -o5 '.$file);
        $this->success($this->output.' optimized.');
    }

    /**
     * Copy the icon file to the sprite at the given offet in a given color
     *
     * @param resource $sprite   The target sprite
     * @param string   $iconfile Path to the icon file
     * @param array    $color    RGB color to color the copied icon in
     * @param int      $offset   Vertical offset to place the icon at
     */
    protected function copyColored(&$sprite, $iconfile, $color, $offset) {
        list($icon_w, $icon_h) = getimagesize($iconfile);
        $icon = imagecreatefrompng($iconfile);
        imagesavealpha($icon, true);
        imagefilter($icon, IMG_FILTER_COLORIZE, $color['r'], $color['g'], $color['b']);

        imagecopyresampled(
            $sprite, // dst_img
            $icon, // src_img
            $this->border, // dst_x
            $offset + $this->border, // dst_y
            0, // src_x
            0, // src_y
            $this->width - $this->border * 2, // dst_w
            $this->width - $this->border * 2, // dst_h
            $icon_w, // src_w
            $icon_h // src_h
        );

        imagedestroy($icon);
    }

    /**
     * Convert a hex color code to an rgb array
     *
     * @param string $hex
     * @return array
     */
    protected function hex2rgb($hex) {
        // strip hash
        $hex = str_replace('#', '', $hex);

        // normalize short codes
        if(strlen($hex) == 3) {
            $hex = substr($hex, 0, 1).
                substr($hex, 0, 1).
                substr($hex, 1, 1).
                substr($hex, 1, 1).
                substr($hex, 2, 1).
                substr($hex, 2, 1);
        }

        // calc rgb
        return array(
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        );
    }

    /**
     * Adjust opacity of the whole image
     *
     * @link     http://de1.php.net/manual/en/function.imagefilter.php#82162
     * @author   aiden dot mail at freemail dot hu
     *
     * @param resource $img image resource
     * @param float    $opacity
     * @return bool
     */
    function filter_opacity(&$img, $opacity) {
        //get image width and height
        $w = imagesx($img);
        $h = imagesy($img);

        //turn alpha blending off
        imagealphablending($img, false);

        //find the most opaque pixel in the image (the one with the smallest alpha value)
        $minalpha = 127;
        for($x = 0; $x < $w; $x++)
            for($y = 0; $y < $h; $y++) {
                $alpha = (imagecolorat($img, $x, $y) >> 24) & 0xFF;
                if($alpha < $minalpha) {
                    $minalpha = $alpha;
                }
            }

        //loop through image pixels and modify alpha for each
        for($x = 0; $x < $w; $x++) {
            for($y = 0; $y < $h; $y++) {
                //get current alpha value (represents the TANSPARENCY!)
                $colorxy = imagecolorat($img, $x, $y);
                $alpha   = ($colorxy >> 24) & 0xFF;
                //calculate new alpha
                if($minalpha !== 127) {
                    $alpha = 127 + 127 * $opacity * ($alpha - 127) / (127 - $minalpha);
                } else {
                    $alpha += 127 * $opacity;
                }
                //get the color index with new alpha
                $alphacolorxy = imagecolorallocatealpha($img, ($colorxy >> 16) & 0xFF, ($colorxy >> 8) & 0xFF, $colorxy & 0xFF, $alpha);
                //set pixel with the new color + opacity
                if(!imagesetpixel($img, $x, $y, $alphacolorxy)) {
                    return false;
                }
            }
        }
        return true;
    }
}

// Main
$cli = new PagetoolBuilder();
$cli->run();
