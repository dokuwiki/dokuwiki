/**
 * The Link Wizard
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 * @author Pierre Spring <pierre.spring@caillou.ch>
 */
var dw_linkwiz = {
    $wiz: null,
    $entry: null,
    result: null,
    timer: null,
    textArea: null,
    selected: null,
    selection: null,

    /**
     * Initialize the dw_linkwizard by creating the needed HTML
     * and attaching the eventhandlers
     */
    init: function($editor){
        // position relative to the text area
        var pos = $editor.position();
		var editor_id = $editor.attr('id');

        // create HTML Structure
        if(this.$wiz)
            return;

		//#link__wiz_result
        this.result = jQuery('<div></div>').addClass('link__wiz_result');
		//#link__wiz_entry
        this.$entry = jQuery('<input type="text" class="edit" autocomplete="off" />').addClass("link__wiz_entry");

		//HTML:
		//'<div>'+LANG.linkto+' <input type="text" class="edit" id="link__wiz_entry" autocomplete="off" /></div>'+
		//'<div id="link__wiz_result"></div>'
		
        this.$wiz = jQuery(document.createElement('div'))
               .dialog({
                   autoOpen: false,
                   draggable: true,
                   title: LANG.linkwiz,
                   resizable: false
               })
			   .append(jQuery('<div>'+LANG.linkto+'</div>').append(this.$entry))
			   .append(this.result)
               .parent()
			   .addClass("link__wiz")
               .css({
                    'position':    'absolute',
                    'top':         (pos.top+20)+'px',
                    'left':        (pos.left+80)+'px'
                   })
               .hide()
               .appendTo('.dokuwiki:first');

        this.textArea = $editor[0];

        // scrollview correction on arrow up/down gets easier
        jQuery(this.result).css('position', 'relative');

        if(JSINFO.namespace){
            this.$entry.val(JSINFO.namespace+':');
        }

        // attach event handlers
		var that = this;
        this.$wiz.find('.ui-dialog-titlebar-close').click(function () {
			dw_linkwiz.hide.call(that);
		});

		var that = this;
        this.$entry.keyup(function() {
			dw_linkwiz.onEntry.call(that);
		});

		var that = this;
        jQuery(this.result).delegate('a', 'click', function (e) {
			dw_linkwiz.onResultClick.call(this, e, that);
		});
    },

    /**
     * handle all keyup events in the entry field
     */
    onEntry: function(e){
        if(e.keyCode == 37 || e.keyCode == 39){ //left/right
            return true; //ignore
        }
        if(e.keyCode == 27){ //Escape
            dw_linkwiz.hide.call(this);
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        if(e.keyCode == 38){ //Up
            dw_linkwiz.select.call(this, this.selected -1);
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        if(e.keyCode == 40){ //Down
            dw_linkwiz.select.call(this, this.selected +1);
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        if(e.keyCode == 13){ //Enter
            if(this.selected > -1){
                var $obj = dw_linkwiz.$getResult.call(this, this.selected);
                if($obj.length > 0){
                    dw_likwiz.resultClick.call(this, $obj.find('a')[0]);
                }
            }else if(this.$entry.val()){
                dw_linkwiz.insertLink.call(this, this.$entry.val());
            }

            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        dw_linkwiz.autocomplete.call(this);
    },

    /**
     * Get one of the results by index
     *
     * @param   num int result div to return
     * @returns DOMObject or null
     */
    getResult: function(num){
        DEPRECATED('use this.$getResult()[0] instead');
        return dw_linkwiz.$getResult.call(this)[0] || null;
    },

    /**
     * Get one of the results by index
     *
     * @param   num int result div to return
     * @returns jQuery object
     */
    $getResult: function(num) {
        return jQuery(this.result).find('div').eq(num);
    },

    /**
     * Select the given result
     */
    select: function(num){
        if(num < 0){
            dw_linkwiz.deselect.call(this);
            return;
        }

        var $obj = dw_linkwiz.$getResult.call(this, num);
        if ($obj.length === 0) {
            return;
        }

        dw_linkwiz.deselect.call(this);
        $obj.addClass('selected');

        // make sure the item is viewable in the scroll view

        //getting child position within the parent
        var childPos = $obj.position().top;
        //getting difference between the childs top and parents viewable area
        var yDiff = childPos + $obj.outerHeight() - jQuery(this.result).innerHeight();

        if (childPos < 0) {
            //if childPos is above viewable area (that's why it goes negative)
            jQuery(this.result)[0].scrollTop += childPos;
        } else if(yDiff > 0) {
            // if difference between childs top and parents viewable area is
            // greater than the height of a childDiv
            jQuery(this.result)[0].scrollTop += yDiff;
        }

        this.selected = num;
    },

    /**
     * deselect a result if any is selected
     */
    deselect: function(){
        if(this.selected > -1){
            dw_linkwiz.$getResult.call(this, this.selected).removeClass('selected');
        }
        this.selected = -1;
    },

    /**
     * Handle clicks in the result set an dispatch them to
     * resultClick()
     */
    onResultClick: function(e, that){
        if(!jQuery(this).is('a')) {
            return;
        }
        e.stopPropagation();
        e.preventDefault();
        dw_linkwiz.resultClick.call(that, this);
        return false;
    },

    /**
     * Handles the "click" on a given result anchor
     */
    resultClick: function(a){
        this.$entry.val(a.title);
        if(a.title == '' || a.title.substr(a.title.length-1) == ':'){
            dw_linkwiz.autocomplete_exec.call(this);
        }else{
            if (jQuery(a.nextSibling).is('span')) {
                dw_linkwiz.insertLink.call(this, a.nextSibling.innerHTML);
            }else{
                dw_linkwiz.insertLink.call(this, '');
            }
        }
    },

    /**
     * Insert the id currently in the entry box to the textarea,
     * replacing the current selection or at the cursor position.
     * When no selection is available the given title will be used
     * as link title instead
     */
    insertLink: function(title){
        var link = this.$entry.val(),
            sel, stxt;
        if(!link) {
            return;
        }

        sel = getSelection(this.textArea);
        if(sel.start == 0 && sel.end == 0) {
            sel = this.selection;
        }

        stxt = sel.getText();

        // don't include trailing space in selection
        if(stxt.charAt(stxt.length - 1) == ' '){
            sel.end--;
            stxt = sel.getText();
        }

        if(!stxt && !DOKU_UHC) {
            stxt=title;
        }

        // prepend colon inside namespaces for non namespace pages
        if(this.textArea.form.id.value.indexOf(':') != -1 &&
           link.indexOf(':') == -1){
           link = ':' + link;
        }

        var so = link.length;
        var eo = 0;
        if(this.val){
            if(this.val.open) {
                so += this.val.open.length;
                link = this.val.open+link;
            }
            if(stxt) {
                link += '|'+stxt;
                so += 1;
            }
            if(this.val.close) {
                link += this.val.close;
                eo = this.val.close.length;
            }
        }

        pasteText(sel,link,{startofs: so, endofs: eo});
        dw_linkwiz.hide.call(this);

        // reset the entry to the parent namespace
        this.$entry.val(this.$entry.val().replace(/[^:]*$/, ''));
    },

    /**
     * Start the page/namespace lookup timer
     *
     * Calls autocomplete_exec when the timer runs out
     */
    autocomplete: function(){
        if(this.timer !== null){
            window.clearTimeout(this.timer);
            this.timer = null;
        }

		var that = this;
        this.timer = window.setTimeout(function () {
			dw_linkwiz.autocomplete_exec.call(that)
		},350);
    },

    /**
     * Executes the AJAX call for the page/namespace lookup
     */
    autocomplete_exec: function(){
        var $res = jQuery(this.result);
        dw_linkwiz.deselect.call(this);
        $res.html('<img src="'+DOKU_BASE+'lib/images/throbber.gif" alt="" width="16" height="16" />')
            .load(
            DOKU_BASE + 'lib/exe/ajax.php',
            {
                call: 'linkwiz',
                q: this.$entry.val()
            }
        );
    },

    /**
     * Show the link wizard
     */
    show: function(){
        this.selection  = getSelection(this.textArea);
        this.$wiz.show();
        this.$entry.focus();
        dw_linkwiz.autocomplete.call(this);
    },

    /**
     * Hide the link wizard
     */
    hide: function(){
        this.$wiz.hide();
        this.textArea.focus();
    },

    /**
     * Toggle the link wizard
     */
    toggle: function(){
        if(this.$wiz.css('display') == 'none'){
            dw_linkwiz.show.call(this);
        }else{
            dw_linkwiz.hide.call(this);
        }
    }
};
