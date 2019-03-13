/**
* Handles the cookie used by several JavaScript functions
*
* Only a single cookie is written and read. You may only save
* simple name-value pairs - no complex types!
*
* You should only use the getValue and setValue methods
*
* @author Andreas Gohr <andi@splitbrain.org>
* @author Michal Rezler <m.rezler@centrum.cz>
*/
var DokuCookie = {
    data: {},
    name: 'DOKU_PREFS',

    /**
     * Save a value to the cookie
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    setValue: function(key,val){
        var text = [],
            _this = this;
        this.init();
        if (val === false){
            delete this.data[key];
        }else{
            val = val + "";
            this.data[key] = val;
        }


        //save the whole data array
        jQuery.each(_this.data, function (key, val) {
            if (_this.data.hasOwnProperty(key)) {
                text.push(encodeURIComponent(key)+'#'+encodeURIComponent(val));
            }
        });
        jQuery.cookie(this.name, text.join('#'), {expires: 365, path: DOKU_COOKIE_PARAM.path, secure: DOKU_COOKIE_PARAM.secure});
    },

    /**
     * Get a Value from the Cookie
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @param def default value if key does not exist; if not set, returns undefined by default
     */
    getValue: function(key, def){
        this.init();
        return this.data.hasOwnProperty(key) ? this.data[key] : def;
    },

    /**
     * Loads the current set cookie
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    init: function(){
        var text, parts, i;
        if(!jQuery.isEmptyObject(this.data)) {
            return;
        }
        text = jQuery.cookie(this.name);
        if(text){
            parts = text.split('#');
            for(i = 0; i < parts.length; i += 2){
                this.data[decodeURIComponent(parts[i])] = decodeURIComponent(parts[i+1]);
            }
        }
    }
};
