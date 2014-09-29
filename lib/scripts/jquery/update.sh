#!/bin/sh
#
# This script loads the latest jQuery and jQuery-UI 1.* versions from jQuery's CDN
#
# It also loads the 'smoothness' jQuery-UI theme and all referenced images.
#
# @author Andreas Gohr <andi@splitbrain.org>
# @author Stefan Grönke <stefan@gronke.net>
# @link   http://code.jquery.com/

# Adjust version for jQuery-UI here - there's no good latest link
JQUI_VERSION='1.11.0'
JQUI_HOST="https://code.jquery.com/ui/$JQUI_VERSION"
JQUI_GIT="https://raw.githubusercontent.com/jquery/jquery-ui/$JQUI_VERSION/ui"

# load jQuery
wget -nv http://code.jquery.com/jquery-latest.min.js      -O jquery.min.js
wget -nv http://code.jquery.com/jquery-latest.js          -O jquery.js

# load jQuery-UI
wget -nv "$JQUI_HOST/jquery-ui.min.js" -O jquery-ui.min.js
wget -nv "$JQUI_HOST/jquery-ui.js"     -O jquery-ui.js

# load the smoothness theme
mkdir -p jquery-ui-theme/images
wget -nv -qO- "$JQUI_HOST/themes/smoothness/jquery-ui.css" | sed "s/font-family:[^;]*;//" > jquery-ui-theme/smoothness.css
images=`gawk 'match($0, /url\("?(images\/[^\)"]+)"?\)/, m) { print m[1] }' jquery-ui-theme/smoothness.css`
for img in $images
do
    wget -nv "$JQUI_HOST/themes/smoothness/$img" -O jquery-ui-theme/$img
done

# load the localization data for jquery ui
for LNG in ../../../inc/lang/*
do
    CODE=`basename $LNG`
    wget -nv "$JQUI_GIT/i18n/datepicker-$CODE.js" -O $LNG/jquery.ui.datepicker.js
    if [ ! -s "$LNG/jquery.ui.datepicker.js" ]; then
        rm -f $LNG/jquery.ui.datepicker.js
    fi
done

# some custom language codes
wget -nv "$JQUI_GIT/i18n/datepicker-de.js" -O ../../../inc/lang/de-informal/jquery.ui.datepicker.js
wget -nv "$JQUI_GIT/i18n/datepicker-pt-BR.js" -O ../../../inc/lang/pt-br/jquery.ui.datepicker.js
wget -nv "$JQUI_GIT/i18n/datepicker-zh-CN.js" -O ../../../inc/lang/zh/jquery.ui.datepicker.js
wget -nv "$JQUI_GIT/i18n/datepicker-zh-TW.js" -O ../../../inc/lang/zh-tw/jquery.ui.datepicker.js

# strip source maps
sed -i '/sourceMappingURL/d' *.min.js
