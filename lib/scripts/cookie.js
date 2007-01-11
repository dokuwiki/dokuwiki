/**
 * Handles the cookie used by several JavaScript functions
 *
 * Only a single cookie is written and read. You may only save
 * sime name-value pairs - no complex types!
 *
 * You should only use the getValue and setValue methods
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
DokuCookie = {
    data: Array(),
    name: 'DOKU_PREFS',

    /**
     * Save a value to the cookie
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    setValue: function(key,val){
        DokuCookie.init();
        DokuCookie.data[key] = val;

        // prepare expire date
        var now = new Date();
        DokuCookie.fixDate(now);
        now.setTime(now.getTime() + 365 * 24 * 60 * 60 * 1000); //expire in a year

        //save the whole data array
        var text = '';
        for(var key in DokuCookie.data){
            if (!DokuCookie.data.hasOwnProperty(key)) continue;
            text += '#'+escape(key)+'#'+DokuCookie.data[key];
        }
        DokuCookie.setCookie(DokuCookie.name,text.substr(1),now,DOKU_BASE);
    },

    /**
     * Get a Value from the Cookie
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    getValue: function(key){
        DokuCookie.init();
        return DokuCookie.data[key];
    },

    /**
     * Loads the current set cookie
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    init: function(){
        if(DokuCookie.data.length) return;
        var text  = DokuCookie.getCookie(DokuCookie.name);
        if(text){
            var parts = text.split('#');
            for(var i=0; i<parts.length; i+=2){
                DokuCookie.data[unescape(parts[i])] = unescape(parts[i+1]);
            }
        }
    },

    /**
     * This sets a cookie by JavaScript
     *
     * @link http://www.webreference.com/js/column8/functions.html
     */
    setCookie: function(name, value, expires, path, domain, secure) {
        var curCookie = name + "=" + escape(value) +
            ((expires) ? "; expires=" + expires.toGMTString() : "") +
            ((path) ? "; path=" + path : "") +
            ((domain) ? "; domain=" + domain : "") +
            ((secure) ? "; secure" : "");
        document.cookie = curCookie;
    },

    /**
     * This reads a cookie by JavaScript
     *
     * @link http://www.webreference.com/js/column8/functions.html
     */
    getCookie: function(name) {
        var dc = document.cookie;
        var prefix = name + "=";
        var begin = dc.indexOf("; " + prefix);
        if (begin == -1) {
            begin = dc.indexOf(prefix);
            if (begin !== 0){ return null; }
        } else {
            begin += 2;
        }
        var end = document.cookie.indexOf(";", begin);
        if (end == -1){
            end = dc.length;
        }
        return unescape(dc.substring(begin + prefix.length, end));
    },

    /**
     * This is needed for the cookie functions
     *
     * @link http://www.webreference.com/js/column8/functions.html
     */
    fixDate: function(date) {
        var base = new Date(0);
        var skew = base.getTime();
        if (skew > 0){
            date.setTime(date.getTime() - skew);
        }
    }
};
