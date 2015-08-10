<?php
/**
 * This script generates a sprite from the unprocessed pagetool icons by combining them
 * and overlaying a color layer for the active state.
 *
 * This script requires a current libGD to be available.
 *
 * The color for the active state is read from the style.ini's __link__ replacement
 *
 * The final sprite is optimized with optipng if available.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @todo   Maybe add some more error checking
 */
$GAMMA = 0.8;
$OPTIPNG = '/usr/bin/optipng';

if('cli' != php_sapi_name()) die('please run from commandline');

// load input images
$input = glob('pagetools/*.png');
sort($input);
$cnt   = count($input);
if(!$cnt){
    die("No input images found. This script needs to be called from within the image directory!\n");
}

// create destination image
$DST = imagecreatetruecolor(30,$cnt*45*2);
imagesavealpha($DST, true);
$C_trans = imagecolorallocatealpha($DST, 0, 0, 0, 127);
imagefill($DST, 0, 0, $C_trans);

// load highlight color from style.ini
$ini = parse_ini_file('../style.ini',true);
$COLOR = hex2rgb($ini['replacements']['__link__']);
$C_active = imagecolorallocate($DST, $COLOR['r'],$COLOR['g'],$COLOR['b']);

// add all the icons to the sprite image
for($i=0; $i<$cnt; $i++){
    $base = $i*90;

    $IN = imagecreatefrompng($input[$i]);
    imagesavealpha($IN, true);
    imagecolorscale($IN,$GAMMA);
    imagecopy($DST,$IN, 0,$base, 0,0, 30,30);
    imagedestroy($IN);

    $IN = imagecreatefrompng($input[$i]);
    imagesavealpha($IN, true);
    imagecolorscale($IN,$GAMMA);
    imagecopy($DST,$IN, 0,$base+45, 0,0, 30,30);
    imagedestroy($IN);

    imagelayereffect($DST, IMG_EFFECT_OVERLAY);
    imagefilledrectangle($DST, 0,$base+45, 30,$base+45+30, $C_active);
    imagelayereffect($DST, IMG_EFFECT_NORMAL);
}

// output sprite
imagepng($DST,'pagetools-sprite.png');
imagedestroy($DST);

// optimize if possible
if(is_executable($OPTIPNG)){
    system("$OPTIPNG -o5 'pagetools-sprite.png'");
}

/**
 * Convert a hex color code to an rgb array
 */
function hex2rgb($hex) {
    // strip hash
    $hex = str_replace('#', '', $hex);

    // normalize short codes
    if(strlen($hex) == 3){
        $hex = substr($hex,0,1).
               substr($hex,0,1).
               substr($hex,1,1).
               substr($hex,1,1).
               substr($hex,2,1).
               substr($hex,2,1);
    }

    // calc rgb
    return array(
       'r' => hexdec(substr($hex, 0, 2)),
       'g' => hexdec(substr($hex, 2, 2)),
       'b' => hexdec(substr($hex, 4, 2))
    );
}

/**
 * Scale (darken/lighten) a given image
 *
 * @param resource $img    The truetype GD image to work on
 * @param float     $scale  Scale the colors by this value ( <1 darkens, >1 lightens)
 */
function imagecolorscale(&$img, $scale){
    $w = imagesx($img);
    $h = imagesy($img);

    imagealphablending($img, false);
    for($x = 0; $x < $w; $x++){
        for($y = 0; $y < $h; $y++){
            $rgba   = imagecolorat($img, $x, $y);
            $a = ($rgba >> 24) & 0xFF;
            $r = ($rgba >> 16) & 0xFF;
            $g = ($rgba >> 8) & 0xFF;
            $b = $rgba & 0xFF;

            $r = max(min(round($r*$scale),255),0);
            $g = max(min(round($g*$scale),255),0);
            $b = max(min(round($b*$scale),255),0);

            $color = imagecolorallocatealpha($img, $r, $g, $b, $a);
            imagesetpixel($img, $x, $y, $color);
        }
    }
    imagealphablending($img, true);
}

