#!/bin/sh
#
# This script generates a sprite from the unprocessed toolbar icons by combining them
# and overlaying a color layer for the active state
#
# The final sprite is optimized with optipng
#
# The script currently expects a Linux system with current versions of the imagemagick
# and optipng binaries in the path
#
# @author Andreas Gohr <andi@splitbrain.org>

OUT=`dirname $0`
IN="$OUT/pagetools"

if [ ! -d "$IN" ]; then
    echo "Input folder $IN not found"
    exit 1
fi

if [ -z "$TEMP" ]; then
    TEMP="/tmp"
fi

if [ ! -d "$TEMP" ]; then
    echo "No temp directory available"
    exit 1
fi

# add up all images twice
montage `for X in $IN/*.png; do echo -n "$X $X "; done`  -background transparent -tile 1x -geometry 30x45 -gravity north $TEMP/icons.png

# darken the image
convert $TEMP/icons.png \( +clone -fill '#cccccc' -colorize 100% \) -compose multiply -composite $TEMP/darkicons.png

# create the mask to highlight an active icon
convert -size 30x30 xc:'#2b73b7' -gamma 0.7 $TEMP/active.png

# create the full mask
montage `for X in $IN/*.png; do echo -n "$TEMP/active.png "; done` -background transparent -tile 1x -geometry 30x60+0+15 -gravity south $TEMP/activemask.png

# apply mask
convert $TEMP/darkicons.png $TEMP/activemask.png  \
\( -clone 0 -alpha extract \) \
\( -clone 0 -clone 1 -compose overlay -composite \) \
-delete 0,1 +swap -alpha off -compose copy_opacity -composite $OUT/pagetools-sprite.png

# optimize final sprite
optipng -o5 $OUT/pagetools-sprite.png

# remove temporary images
rm -f $TEMP/icons.png
rm -f $TEMP/darkicons.png
rm -f $TEMP/active.png
rm -f $TEMP/activemask.png

