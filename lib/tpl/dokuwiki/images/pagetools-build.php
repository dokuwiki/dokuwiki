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
$OPTIPNG = '/usr/bin/optipng';
$OPACITY = 70;

// load input images
$input = glob('pagetools/*.png');
sort($input);
$cnt   = count($input);
if(!$cnt){
    die("No input images found. This script needs to be called from within the image directory!\n");
}

$WIDTH=30;
$BORDER=3;


// create destination image
$DST = imagecreatetruecolor(30,$cnt*45*2);
imagesavealpha($DST, true);
$C_trans = imagecolorallocatealpha($DST, 0, 0, 0, 127);
imagefill($DST, 0, 0, $C_trans);

// load highlight color from style.ini
// $ini = parse_ini_file('../style.ini',true);
// $COLOR = hex2rgb($ini['replacements']['__link__']);

$ACTIVE = hex2rgb('#2b73b7');
$INACTIVE = hex2rgb('#999999');



// add all the icons to the sprite image
for($i=0; $i<$cnt; $i++){
    $base = $i*$WIDTH*3;

    echo '.';

    // inactive version
    $IN = imagecreatefrompng($input[$i]);
    imagesavealpha($IN, true);
    imagefilter($IN, IMG_FILTER_COLORIZE, $INACTIVE['r'],$INACTIVE['g'],$INACTIVE['b']);
    list($w, $h) = getimagesize($input[$i]);
    imagecopyresampled($DST,$IN, $BORDER, $base+$BORDER, 0,0, $WIDTH-$BORDER*2, $WIDTH-$BORDER*2, $w,$h);
    imagedestroy($IN);


    $base = $base + $WIDTH + $WIDTH/2;

    // active version
    $IN = imagecreatefrompng($input[$i]);
    imagesavealpha($IN, true);
    imagefilter($IN, IMG_FILTER_COLORIZE, $ACTIVE['r'],$ACTIVE['g'],$ACTIVE['b']);
    list($w, $h) = getimagesize($input[$i]);
    imagecopyresampled($DST,$IN, $BORDER, $base+$BORDER, 0,0, $WIDTH-$BORDER*2, $WIDTH-$BORDER*2, $w,$h);
    imagedestroy($IN);

}

// set opacity
filter_opacity($DST, $OPACITY);


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


function filter_opacity( &$img, $opacity ) //params: image resource id, opacity in percentage (eg. 80)
        {
            if( !isset( $opacity ) )
                { return false; }
            $opacity /= 100;

            //get image width and height
            $w = imagesx( $img );
            $h = imagesy( $img );

            //turn alpha blending off
            imagealphablending( $img, false );

            //find the most opaque pixel in the image (the one with the smallest alpha value)
            $minalpha = 127;
            for( $x = 0; $x < $w; $x++ )
                for( $y = 0; $y < $h; $y++ )
                    {
                        $alpha = ( imagecolorat( $img, $x, $y ) >> 24 ) & 0xFF;
                        if( $alpha < $minalpha )
                            { $minalpha = $alpha; }
                    }

            //loop through image pixels and modify alpha for each
            for( $x = 0; $x < $w; $x++ )
                {
                    for( $y = 0; $y < $h; $y++ )
                        {
                            //get current alpha value (represents the TANSPARENCY!)
                            $colorxy = imagecolorat( $img, $x, $y );
                            $alpha = ( $colorxy >> 24 ) & 0xFF;
                            //calculate new alpha
                            if( $minalpha !== 127 )
                                { $alpha = 127 + 127 * $opacity * ( $alpha - 127 ) / ( 127 - $minalpha ); }
                            else
                                { $alpha += 127 * $opacity; }
                            //get the color index with new alpha
                            $alphacolorxy = imagecolorallocatealpha( $img, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha );
                            //set pixel with the new color + opacity
                            if( !imagesetpixel( $img, $x, $y, $alphacolorxy ) )
                                { return false; }
                        }
                }
            return true;
        }

/**
 * Scale (darken/lighten) a given image
 *
 * @param ressource $img    The truetype GD image to work on
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

