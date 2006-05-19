/**
 * JavaScript functionalitiy for the media management popup
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
media = {
    keepopen: false,

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
            addEvent(clicky,'click',function(event){ return media.toggle(event,this); });

            // attach action load folder list via AJAX
            var link = elem.getElementsByTagName('a')[0];
            link.style.cursor = 'pointer';
            addEvent(link,'click',function(event){ return media.list(event,this); });
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
            addEvent(elem,'click',function(event){ return media.select(event,this); });
        }

        var file = $('upload__file');
        if(!file) return;
        addEvent(file,'change',media.suggest);
    },

    /**
     * Creates a checkbox for keeping the popup on selection
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    attachkeepopen: function(obj){
        if(!obj) return;

        var cbox  = document.createElement('input');
        cbox.type = 'checkbox';
        cbox.id   = 'media__keepopen';
        if(DokuCookie.getValue('keepopen')){
            cbox.checked = true;
        }
        addEvent(cbox,'change',function(event){ return media.togglekeepopen(event,this); });

        var clbl  = document.createElement('label');
        clbl.htmlFor   = 'media__keepopen';
        clbl.innerHTML = LANG['keepopen'];

        obj.appendChild(cbox);
        obj.appendChild(clbl);
    },

    /**
     * Toggles the keep open state
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    togglekeepopen: function(event,cb){
        if(cb.checked){
            DokuCookie.setValue('keepopen',1);
            media.keepopen = true;
        }else{
            DokuCookie.setValue('keepopen','');
            media.keepopen = false;
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
            alert(LANG['idtouse']+"\n:"+id);
            return false;
        }
        opener.insertTags('wiki__text','{{'+id+'|','}}','');

        if(!media.keepopen) window.close();
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
        ajax.afterCompletion = function(){ media.selectorattach(content); };
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
        ajax.afterCompletion = function(){ media.treeattach(ul); };
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
    }

}

addInitEvent(function(){media.treeattach($('media__tree'));});
addInitEvent(function(){media.selectorattach($('media__content'));});
addInitEvent(function(){media.attachkeepopen($('media__opts'));});
