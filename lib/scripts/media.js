/**
 * JavaScript functionality for the media management popup
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
var media_manager = {
    keepopen: false,
    hide: false,
    align: false,
    popup: false,
    id: false,
    display: false,
    link: false,
    size: false,
    ext: false,

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
     * shows the popup for a image link
     */
    select: function(event,link){
        var id = link.name.substr(2);

        media_manager.id = id;
        if(!opener){
            // if we don't run in popup display example
            var ex = $('ex_'+id.replace(/:/g,'_'));
            if(ex.style.display == ''){
                ex.style.display = 'none';
            } else {
                ex.style.display = '';
            }
            return false;
        }

        // FIXME these lines deactivate the media options dialog and restore
        // the old behavior according to FS#2047
        opener.insertTags('wiki__text','{{'+id+'|','}}','');
        if(!media_manager.keepopen) window.close();
        opener.focus();
        return false;


        media_manager.ext = false;
        var dot = id.lastIndexOf(".");
        if (dot != -1) {
            var ext = id.substr(dot,id.length);

            if (ext != '.jpg' && ext != '.jpeg' && ext != '.png' && ext != '.gif' && ext != '.swf') {
                media_manager.insert(null);
                return false;
            }
        } else {
            media_manager.insert(null);
            return false;
        }

        media_manager.popup.style.display = 'inline';
        media_manager.popup.style.left = event.pageX + 'px';
        media_manager.popup.style.top = event.pageY + 'px';

        // set all buttons to outset
        media_manager.outSet('media__linkbtn1');
        media_manager.outSet('media__linkbtn2');
        media_manager.outSet('media__linkbtn3');
        media_manager.outSet('media__linkbtn4');

        media_manager.outSet('media__alignbtn0');
        media_manager.outSet('media__alignbtn1');
        media_manager.outSet('media__alignbtn2');
        media_manager.outSet('media__alignbtn3');

        media_manager.outSet('media__sizebtn1');
        media_manager.outSet('media__sizebtn2');
        media_manager.outSet('media__sizebtn3');
        media_manager.outSet('media__sizebtn4');


        if (ext == '.swf') {
            media_manager.ext = 'swf';

            // disable display buttons for detail and linked image
            $('media__linkbtn1').style.display = 'none';
            $('media__linkbtn2').style.display = 'none';

            // set the link button to default
            if (media_manager.link != false) {
                if ( media_manager.link == '2' || media_manager.link == '1')  {
                    media_manager.inSet('media__linkbtn3');
                    media_manager.link = '3';
                    DokuCookie.setValue('link','3');
                } else {
                    media_manager.inSet('media__linkbtn'+media_manager.link);
                }
            } else if (DokuCookie.getValue('link')) {
                if ( DokuCookie.getValue('link') == '2' ||  DokuCookie.getValue('link') == '1')  {
                    // this options are not availible
                    media_manager.inSet('media__linkbtn3');
                    media_manager.link = '3';
                    DokuCookie.setValue('link','3');
                } else {
                    media_manager.inSet('media__linkbtn'+DokuCookie.getValue('link'));
                    media_manager.link = DokuCookie.getValue('link');
                }
            } else {
                // default case
                media_manager.link = '3';
                media_manager.inSet('media__linkbtn3');
                DokuCookie.setValue('link','3');
            }

            // disable button for original size
            $('media__sizebtn4').style.display = 'none';

        } else {
            media_manager.ext = 'img';

            // ensure that the display buttons are there
            $('media__linkbtn1').style.display = 'inline';
            $('media__linkbtn2').style.display = 'inline';
            $('media__sizebtn4').style.display = 'inline';

            // set the link button to default
            if (DokuCookie.getValue('link')) {
                media_manager.link = DokuCookie.getValue('link');
            }
            if (media_manager.link == false) {
                // default case
                media_manager.link = '1';
                DokuCookie.setValue('link','1');
            }
            media_manager.inSet('media__linkbtn'+media_manager.link);
        }

        if (media_manager.link == '4') {
            media_manager.align = false;
            media_manager.size = false;
            $('media__align').style.display = 'none';
            $('media__size').style.display = 'none';
        } else {
            $('media__align').style.display = 'block';
            $('media__size').style.display = 'block';

            // set the align button to default
            if (media_manager.align != false) {
                media_manager.inSet('media__alignbtn'+media_manager.align);
            } else if (DokuCookie.getValue('align')) {
                media_manager.inSet('media__alignbtn'+DokuCookie.getValue('align'));
                media_manager.align = DokuCookie.getValue('align');
            } else {
                // default case
                media_manager.align = '0';
                media_manager.inSet('media__alignbtn0');
                DokuCookie.setValue('align','0');
            }

            // set the size button to default
            if (DokuCookie.getValue('size')) {
                media_manager.size = DokuCookie.getValue('size');
            }
            if (media_manager.size == false || (media_manager.size === '4' && ext === '.swf')) {
                // default case
                media_manager.size = '2';
                DokuCookie.setValue('size','2');
            }
            media_manager.inSet('media__sizebtn'+media_manager.size);

            $('media__sendbtn').focus();
        }

       return false;
    },

    /**
     * build the popup window
     *
     * @author Dominik Eckelmann <eckelmann@cosmocode.de>
     */
    initpopup: function() {

        media_manager.popup = document.createElement('div');
        media_manager.popup.setAttribute('id','media__popup');

        var root = document.getElementById('media__manager');
        if (root == null) return;
        root.appendChild(media_manager.popup);

        var headline    = document.createElement('h1');
        headline.innerHTML = LANG.mediatitle;
        var headlineimg = document.createElement('img');
        headlineimg.src = DOKU_BASE + 'lib/images/close.png';
        headlineimg.id  = 'media__closeimg';
        addEvent(headlineimg,'click',function(event){ return media_manager.closePopup(event,this); });
        headline.insertBefore(headlineimg, headline.firstChild);
        media_manager.popup.appendChild(headline);
        drag.attach(media_manager.popup,headline);

        // link

        var linkp = document.createElement('p');

        linkp.id = "media__linkstyle";
        if (media_manager.display == "2") {
            linkp.style.display = "none";
        }

        var linkl = document.createElement('label');
        linkl.innerHTML = LANG.mediatarget;
        linkp.appendChild(linkl);

        var linkbtns = ['lnk', 'direct', 'nolnk', 'displaylnk'];
        for (var i = 0 ; i < linkbtns.length ; ++i) {
            var linkbtn = document.createElement('button');
            linkbtn.className = 'button';
            linkbtn.value = i + 1;
            linkbtn.id    = "media__linkbtn" + (i + 1);
            linkbtn.title = LANG['media' + linkbtns[i]];
            linkbtn.style.borderStyle = 'outset';
            addEvent(linkbtn,'click',function(event){ return media_manager.setlink(event,this); });

            var linkimg = document.createElement('img');
            linkimg.src = DOKU_BASE + 'lib/images/media_link_' + linkbtns[i] + '.png';

            linkbtn.appendChild(linkimg);
            linkp.appendChild(linkbtn);
        }

        media_manager.popup.appendChild(linkp);

        // align

        var alignp    = document.createElement('p');
        var alignl    = document.createElement('label');

        alignp.appendChild(alignl);
        alignp.id = 'media__align';
        if (media_manager.display == "2") {
            alignp.style.display = "none";
        }
        alignl.innerHTML = LANG['mediaalign'];

        var alignbtns = ['noalign', 'left', 'center', 'right'];
        for (var n = 0 ; n < alignbtns.length ; ++n) {
            var alignbtn = document.createElement('button');
            var alignimg = document.createElement('img');
            alignimg.src = DOKU_BASE + 'lib/images/media_align_' + alignbtns[n] + '.png';

            alignbtn.id    = "media__alignbtn" + n;
            alignbtn.value = n;
            alignbtn.title = LANG['media' + alignbtns[n]];
            alignbtn.className = 'button';
            alignbtn.appendChild(alignimg);
            alignbtn.style.borderStyle = 'outset';
            addEvent(alignbtn,'click',function(event){ return media_manager.setalign(event,this); });

            alignp.appendChild(alignbtn);
        }

        media_manager.popup.appendChild(alignp);

        // size

        var sizep    = document.createElement('p');
        var sizel    = document.createElement('label');

        sizep.id = 'media__size';
        if (media_manager.display == "2") {
            sizep.style.display = "none";
        }
        sizep.appendChild(sizel);
        sizel.innerHTML = LANG['mediasize'];

        var sizebtns = ['small', 'medium', 'large', 'original'];
        for (var size = 0 ; size < sizebtns.length ; ++size) {
            var sizebtn = document.createElement('button');
            var sizeimg = document.createElement('img');

            sizep.appendChild(sizebtn);
            sizeimg.src = DOKU_BASE + 'lib/images/media_size_' + sizebtns[size] + '.png';

            sizebtn.className = 'button';
            sizebtn.appendChild(sizeimg);
            sizebtn.value = size + 1;
            sizebtn.id    = 'media__sizebtn' + (size + 1);
            sizebtn.title = LANG['media' + sizebtns[size]];
            sizebtn.style.borderStyle = 'outset';
            addEvent(sizebtn,'click',function(event){ return media_manager.setsize(event,this); });
        }

        media_manager.popup.appendChild(sizep);

        // send and close button

        var btnp = document.createElement('p');
        media_manager.popup.appendChild(btnp);
        btnp.setAttribute('class','btnlbl');

        var btn  = document.createElement('input');
        btn.type = 'button';
        btn.id   = 'media__sendbtn';
        btn.setAttribute('class','button');
        btn.value = LANG['mediainsert'];
        btnp.appendChild(btn);
        addEvent(btn,'click',function(event){ return media_manager.insert(event); });
    },

    /**
     * Insert the clicked image into the opener's textarea
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Dominik Eckelmann <eckelmann@cosmocode.de>
     */
    insert: function(event){
        var id = media_manager.id;
        // set syntax options
        $('media__popup').style.display = 'none';

        var opts       = '';
        var optsstart  = '';
        var alignleft  = '';
        var alignright = '';

        if (media_manager.ext == 'img' || media_manager.ext == 'swf') {

            if (media_manager.link == '4') {
                    opts = '?linkonly';
            } else {

                if (media_manager.link == "3" && media_manager.ext == 'img') {
                    opts = '?nolink';
                    optsstart = true;
                } else if (media_manager.link == "2" && media_manager.ext == 'img') {
                    opts = '?direct';
                    optsstart = true;
                }

                var s = parseInt(media_manager.size);

                if (s && s >= 1) {
                    opts += (optsstart)?'&':'?';
                    if (s=="1") {
                        opts += '100';
                        if (media_manager.ext == 'swf') {
                            opts += 'x62';
                        }
                    } else if (s=="2") {
                        opts += '200';
                        if (media_manager.ext == 'swf') {
                            opts += 'x123';
                        }
                    } else if (s=="3"){
                        opts += '300';
                        if (media_manager.ext == 'swf') {
                            opts += 'x185';
                        }
                    }
                }
                if (media_manager.align == '1') {
                    alignleft = '';
                    alignright = ' ';
                }
                if (media_manager.align == '2') {
                    alignleft = ' ';
                    alignright = ' ';
                }
                if (media_manager.align == '3') {
                    alignleft = ' ';
                    alignright = '';
                }
            }
        }
        opener.insertTags('wiki__text','{{'+alignleft+id+opts+alignright+'|','}}','');

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
    },

    /**
     * closes the link type popup
     */
    closePopup: function(event) {
        $('media__popup').style.display = 'none';
    },

    /**
     * set the align
     *
     * @author Dominik Eckelmann <eckelmann@cosmocode.de>
     */
    setalign: function(event,cb){
        if(cb.value){
            DokuCookie.setValue('align',cb.value);
            media_manager.align = cb.value;
            media_manager.outSet("media__alignbtn0");
            media_manager.outSet("media__alignbtn1");
            media_manager.outSet("media__alignbtn2");
            media_manager.outSet("media__alignbtn3");
            media_manager.inSet("media__alignbtn"+cb.value);
        }else{
            DokuCookie.setValue('align','');
            media_manager.align = false;
        }
    },
    /**
     * set the link type
     *
     * @author Dominik Eckelmann <eckelmann@cosmocode.de>
     */
    setlink: function(event,cb){
        if(cb.value){
            DokuCookie.setValue('link',cb.value);
            media_manager.link = cb.value;
            media_manager.outSet("media__linkbtn1");
            media_manager.outSet("media__linkbtn2");
            media_manager.outSet("media__linkbtn3");
            media_manager.outSet("media__linkbtn4");
            media_manager.inSet("media__linkbtn"+cb.value);
            var size = document.getElementById("media__size");
            var align = document.getElementById("media__align");
            if (cb.value != '4') {
                size.style.display  = "block";
                align.style.display = "block";
            } else {
                size.style.display  = "none";
                align.style.display = "none";
            }
        }else{
            DokuCookie.setValue('link','');
            media_manager.link = false;
        }
    },

    /**
     * set the display type
     *
     * @author Dominik Eckelmann <eckelmann@cosmocode.de>
     */
    setdisplay: function(event,cb){
        if(cb.value){
            DokuCookie.setValue('display',cb.value);
            media_manager.display = cb.value;
            media_manager.outSet("media__displaybtn1");
            media_manager.outSet("media__displaybtn2");
            media_manager.inSet("media__displaybtn"+cb.value);

        }else{
            DokuCookie.setValue('display','');
            media_manager.align = false;
        }
    },

    /**
     * sets the border to outset
     */
    outSet: function(id) {
        var ele = document.getElementById(id);
        if (ele == null) return;
        ele.style.borderStyle = "outset";
    },
    /**
     * sets the border to inset
     */
    inSet: function(id) {
        var ele = document.getElementById(id);
        if (ele == null) return;
        ele.style.borderStyle = "inset";
    },

    /**
     * set the image size
     *
     * @author Dominik Eckelmann <eckelmann@cosmocode.de>
     */
    setsize: function(event,cb){
        if (cb.value) {
            DokuCookie.setValue('size',cb.value);
            media_manager.size = cb.value;
            for (var i = 1 ; i <= 4 ; ++i) {
                media_manager.outSet("media__sizebtn" + i);
            }
            media_manager.inSet("media__sizebtn"+cb.value);
        } else {
            DokuCookie.setValue('size','');
            media_manager.width = false;
        }
    }
};

addInitEvent(function(){
    media_manager.treeattach($('media__tree'));
    media_manager.selectorattach($('media__content'));
    media_manager.confirmattach($('media__content'));
    media_manager.attachoptions($('media__opts'));
    media_manager.initpopup();
    media_manager.initFlashUpload();
});
