#!/bin/sh
#
# This script loads the latest jQuery and jQuery-UI 1.* versions from Google's CDN
#
# It also loads the 'smoothness' jQuery-UI theme and all referenced images.
#
# @author Andreas Gohr <andi@splitbrain.org>
# @link   https://code.google.com/apis/libraries/devguide.html#jquery

# load jQuery
wget -nv https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js      -O jquery.min.js
wget -nv https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js          -O jquery.js

# load jQuery-UI
wget -nv https://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js -O jquery-ui.min.js
wget -nv https://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.js     -O jquery-ui.js

# load the smoothness theme
mkdir -p jquery-ui-theme/images
wget -nv https://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/smoothness/jquery-ui.css -O jquery-ui-theme/smoothness.css
images=`gawk 'match($0, /url\((images\/[^\)]+)\)/, m) { print m[1] }' jquery-ui-theme/smoothness.css`
for img in $images
do
    wget -nv https://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/smoothness/$img -O jquery-ui-theme/$img
done

# remove font family declarations from smoothness CSS
sed -i "s/font-family:[^;]*; \?//" jquery-ui-theme/smoothness.css
