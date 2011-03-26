/**
 * Handles the cookie used by several JavaScript functions
 *
 * Only a single cookie is written and read. You may only save
 * sime name-value pairs - no complex types!
 *
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Michal Rezler <m.rezler@centrum.cz> 
 */

var setDokuCookie, getDokuCookie;

(function ($) {
    var init, setCookie, fixDate;  
    
    var data = Array();
    var name = 'DOKU_PREFS';

    /**
     * Save a value to the cookie
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    setDokuCookie = function(key,val){
        init();
        data[key] = val;

        // prepare expire date
        var now = new Date();
        fixDate(now);
        now.setTime(now.getTime() + 365 * 24 * 60 * 60 * 1000); //expire in a year

        //save the whole data array
        var text = '';
        for(var key in data){
            if (!data.hasOwnProperty(key)) continue;
            text += '#'+escape(key)+'#'+data[key];
        }
        setCookie(name,text.substr(1),now,DOKU_BASE);
    };

    /**
     * Get a Value from the Cookie
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    getDokuCookie = function(key){
        init();
        return data[key];
    };

    /**
     * Loads the current set cookie
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    init = function(){
        if(data.length) return;
        var text  = $.cookie(name);

        if(text){
            var parts = text.split('#');
            for(var i=0; i<parts.length; i+=2){
                data[unescape(parts[i])] = unescape(parts[i+1]);
            }
        }
    };

    /**
     * This sets a cookie
     *
     */
    setCookie = function(name, value, expires_, path_, domain_, secure_) {
        var params = { 
            expires: expires_,
            path: path_,
            domain: domain_,
            secure: secure_, 
        };
        $.cookie(name, value, params);
    };

    /**
     * This is needed for the cookie functions
     *
     * @link http://www.webreference.com/js/column8/functions.html
     */
    fixDate = function(date) {
        var base = new Date(0);
        var skew = base.getTime();
        if (skew > 0){
            date.setTime(date.getTime() - skew);
        }
    };
    
}(jQuery));
