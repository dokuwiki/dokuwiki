/**
 * JavaScript functionalitiy for the media management popup
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
var media_manager = {
    keepopen: false,
    hide: false,

    /**
     * Attach event handlers to all "folders" below the given element
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    treeattach: function(obj){
        if(!obj) return;

        var items = obj.getElementsByTagName('li');
        for(var i=0; i<items.length; i++){
            var elem = items[i];

            // attach action to make the +/- clickable
            var clicky = elem.getElementsByTagName('img')[0];
            clicky.style.cursor = 'pointer';
            addEvent(clicky,'click',function(event){ return media_manager.toggle(event,this); });

            // attach action load folder list via AJAX
            var link = elem.getElementsByTagName('a')[0];
            link.style.cursor = 'pointer';
            addEvent(link,'click',function(event){ return media_manager.list(event,this); });
        }
    },

    /**
     * Attach the image selector action to all links below the given element
     * also add the action to autofill the "upload as" field
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    selectorattach: function(obj){
        if(!obj) return;

        var items = getElementsByClass('select',obj,'a');
        for(var i=0; i<items.length; i++){
            var elem = items[i];
            elem.style.cursor = 'pointer';
            addEvent(elem,'click',function(event){ return media_manager.select(event,this); });
        }

        // hide syntax example
        items = getElementsByClass('example',obj,'div');
        for(var i=0; i<items.length; i++){
            elem = items[i];
            elem.style.display = 'none';
        }

        var file = $('upload__file');
        if(!file) return;
        addEvent(file,'change',media_manager.suggest);
    },

    /**
     * Attach deletion confirmation dialog to the delete buttons.
     *
     * Michael Klier <chi@chimeric.de>
     */
    confirmattach: function(obj){
        if(!obj) return;

        items = getElementsByClass('btn_media_delete',obj,'a');
        for(var i=0; i<items.length; i++){
            var elem = items[i];
            addEvent(elem,'click',function(e){
                if(e.target.tagName == 'IMG'){
                    var name = e.target.parentNode.title;
                }else{
                    var name = e.target.title;
                }
                if(!confirm(LANG['del_confirm'] + "\n" + name)) {
                    e.preventDefault();
                    return false;
                } else {
                    return true;
                }
            });
        }
    },

    /**
     * Creates checkboxes for additional options
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    attachoptions: function(obj){
        if(!obj) return;

        // keep open
        if(opener){
            var kobox  = document.createElement('input');
            kobox.type = 'checkbox';
            kobox.id   = 'media__keepopen';
            if(DokuCookie.getValue('keepopen')){
                kobox.checked  = true;
                kobox.defaultChecked = true; //IE wants this
                media_manager.keepopen = true;
            }
            addEvent(kobox,'click',function(event){ return media_manager.togglekeepopen(event,this); });

            var kolbl  = document.createElement('label');
            kolbl.htmlFor   = 'media__keepopen';
            kolbl.innerHTML = LANG['keepopen'];

            var kobr = document.createElement('br');

            obj.appendChild(kobox);
            obj.appendChild(kolbl);
            obj.appendChild(kobr);
        }

        // hide details
        var hdbox  = document.createElement('input');
        hdbox.type = 'checkbox';
        hdbox.id   = 'media__hide';
        if(DokuCookie.getValue('hide')){
            hdbox.checked = true;
            hdbox.defaultChecked = true; //IE wants this
            media_manager.hide    = true;
        }
        addEvent(hdbox,'click',function(event){ return media_manager.togglehide(event,this); });

        var hdlbl  = document.createElement('label');
        hdlbl.htmlFor   = 'media__hide';
        hdlbl.innerHTML = LANG['hidedetails'];

        var hdbr = document.createElement('br');

        obj.appendChild(hdbox);
        obj.appendChild(hdlbl);
        obj.appendChild(hdbr);
        media_manager.updatehide();
    },

    /**
     * Opens the searchfield
     *
     * @author Tobias Sarnowski <sarnowski@cosmocode.de>
     */
    showsearchfield: function(event,link){
        // prepare an AJAX call to fetch the search
        var ajax = new sack(DOKU_BASE + 'lib/exe/ajax.php');
        ajax.AjaxFailedAlert = '';
        ajax.encodeURIString = false;
        if(ajax.failed) return true;

        cleanMsgArea();

        var content = $('media__content');
        content.innerHTML = '<img src="'+DOKU_BASE+'lib/images/loading.gif" alt="..." class="load" />';

        ajax.elementObj = content;
        ajax.afterCompletion = function(){
            media_manager.selectorattach(content);
            media_manager.confirmattach(content);
            media_manager.updatehide();
        };
        ajax.runAJAX(link.search.substr(1)+'&call=mediasearchlist');
        return false;
    },

    /**
     * Toggles the keep open state
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    togglekeepopen: function(event,cb){
        if(cb.checked){
            DokuCookie.setValue('keepopen',1);
            media_manager.keepopen = true;
        }else{
            DokuCookie.setValue('keepopen','');
            media_manager.keepopen = false;
        }
    },

    /**
     * Toggles the hide details state
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    togglehide: function(event,cb){
        if(cb.checked){
            DokuCookie.setValue('hide',1);
            media_manager.hide = true;
        }else{
            DokuCookie.setValue('hide','');
            media_manager.hide = false;
        }
        media_manager.updatehide();
    },

    /**
     * Sets the visibility of the image details accordingly to the
     * chosen hide state
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    updatehide: function(){
        var obj = $('media__content');
        if(!obj) return;
        var details = getElementsByClass('detail',obj,'div');
        for(var i=0; i<details.length; i++){
            if(media_manager.hide){
                details[i].style.display = 'none';
            }else{
                details[i].style.display = '';
            }
        }
    },

    /**
     * Insert the clicked image into the opener's textarea
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    select: function(event,link){
        var id = link.name.substr(2);

        if(!opener){
            // if we don't run in popup display example
            var ex = $('ex_'+id.replace(/:/g,'_'));
            if(ex.style.display == ''){
                ex.style.display = 'none';
            }else{
                ex.style.display = '';
            }
            return false;
        }
        opener.insertTags('wiki__text','{{'+id+'|','}}','');

        if(!media_manager.keepopen) window.close();
        opener.focus();
        return false;
    },

    /**
     * list the content of a namespace using AJAX
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    list: function(event,link){
        // prepare an AJAX call to fetch the subtree
        var ajax = new sack(DOKU_BASE + 'lib/exe/ajax.php');
        ajax.AjaxFailedAlert = '';
        ajax.encodeURIString = false;
        if(ajax.failed) return true;

        cleanMsgArea();

        var content = $('media__content');
        content.innerHTML = '<img src="'+DOKU_BASE+'lib/images/loading.gif" alt="..." class="load" />';

        ajax.elementObj = content;
        ajax.afterCompletion = function(){
            media_manager.selectorattach(content);
            media_manager.confirmattach(content);
            media_manager.updatehide();
            media_manager.initFlashUpload();
        };
        ajax.runAJAX(link.search.substr(1)+'&call=medialist');
        return false;
    },


    /**
     * Open or close a subtree using AJAX
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    toggle: function(event,clicky){
        var listitem = clicky.parentNode;

        // if already open, close by removing the sublist
        var sublists = listitem.getElementsByTagName('ul');
        if(sublists.length){
            listitem.removeChild(sublists[0]);
            clicky.src = DOKU_BASE+'lib/images/plus.gif';
            return false;
        }

        // get the enclosed link (is always the first one)
        var link = listitem.getElementsByTagName('a')[0];

        // prepare an AJAX call to fetch the subtree
        var ajax = new sack(DOKU_BASE + 'lib/exe/ajax.php');
        ajax.AjaxFailedAlert = '';
        ajax.encodeURIString = false;
        if(ajax.failed) return true;

        //prepare the new ul
        var ul = document.createElement('ul');
        //fixme add classname here
        listitem.appendChild(ul);
        ajax.elementObj = ul;
        ajax.afterCompletion = function(){ media_manager.treeattach(ul); };
        ajax.runAJAX(link.search.substr(1)+'&call=medians');
        clicky.src = DOKU_BASE+'lib/images/minus.gif';
        return false;
    },

    /**
     * Prefills the wikiname.
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    suggest: function(){
        var file = $('upload__file');
        var name = $('upload__name');
        if(!file || !name) return;

        var text = file.value;
        text = text.substr(text.lastIndexOf('/')+1);
        text = text.substr(text.lastIndexOf('\\')+1);
        name.value = text;
    },


    initFlashUpload: function(){
        if(!hasFlash(8)) return;
        var oform  = $('dw__upload');
        var oflash = $('dw__flashupload');
        if(!oform || !oflash) return;

        var clicky = document.createElement('img');
        clicky.src     = DOKU_BASE+'lib/images/multiupload.png';
        clicky.title   = LANG['mu_btn'];
        clicky.alt     = LANG['mu_btn'];
        clicky.style.cursor = 'pointer';
        clicky.onclick = function(){
                            oform.style.display  = 'none';
                            oflash.style.display = '';
                         };
        oform.appendChild(clicky);
    }
};

addInitEvent(function(){
    media_manager.treeattach($('media__tree'));
    media_manager.selectorattach($('media__content'));
    media_manager.confirmattach($('media__content'));
    media_manager.attachoptions($('media__opts'));
    media_manager.initFlashUpload();
});
