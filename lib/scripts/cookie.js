/**
* Handles the cookie used by several JavaScript functions
*
* Only a single cookie is written and read. You may only save
* sime name-value pairs - no complex types!
*
* You should only use the getValue and setValue methods
*
* @author Andreas Gohr <andi@splitbrain.org>
* @author Michal Rezler <m.rezler@centrum.cz>
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
        this.init();
        this.data[key] = val;

        // prepare expire date
        var now = new Date();
        this.fixDate(now);
        now.setTime(now.getTime() + 365 * 24 * 60 * 60 * 1000); //expire in a year

        //save the whole data array
        var text = '';
        for(var key in this.data){
            if (!this.data.hasOwnProperty(key)) continue;
            text += '#'+escape(key)+'#'+this.data[key];
        }
        this.setCookie(this.name,text.substr(1),now,DOKU_BASE);
    },

    /**
     * Get a Value from the Cookie
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    getValue: function(key){
        this.init();
        return this.data[key];
    },

    /**
     * Loads the current set cookie
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    init: function(){
        if(this.data.length) return;
        var text = this.getCookie(this.name);
        if(text){
            var parts = text.split('#');
            for(var i=0; i<parts.length; i+=2){
                this.data[unescape(parts[i])] = unescape(parts[i+1]);
            }
        }
    },

    /**
     * This sets a cookie by JavaScript
     *
     * @link http://www.webreference.com/js/column8/functions.html
     */
    setCookie: function(name, value, expires_, path_, domain_, secure_) {
        var params = {
            expires: expires_,
            path: path_,
            domain: domain_,
            secure: secure_
        };

        jQuery.cookie(name, value, params);
    },

    /**
     * This reads a cookie by JavaScript
     *
     * @link http://www.webreference.com/js/column8/functions.html
     */
    getCookie: function(name) {
        return unescape(jQuery.cookie(name));
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
