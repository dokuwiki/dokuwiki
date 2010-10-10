/*jslint white: true, onevar: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, regexp: false, strict: true, newcap: true, immed: true */
/*global jQuery, window, DOKU_BASE*/
"use strict";

// TODO
// * fix the css to have pointers on the +/- images in the tree when JS is enabled
// * fix the css to have pointers on a.select when JS is enabled
// * remame all the variables starting with $ once the port is over

/**
 * JavaScript functionality for the media management popup
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Pierre Spring <pierre.spring@caillou.ch>
 */
(function ($) {
    var toggle, list, prepare_content, insert, confirmattach, attachoptions, initpopup;
    
    /**
     * build the popup window
     *
     * @author Dominik Eckelmann <eckelmann@cosmocode.de>
     */
    initpopup = function() {
        var popup;
        
        popup = document.createElement('div');
        popup.setAttribute('id','media__popup');
        
        var root = document.getElementById('media__manager');
        if (root == null) return;
        root.appendChild(popup);

        var headline    = document.createElement('h1');
        headline.innerHTML = LANG.mediatitle;
        var headlineimg = document.createElement('img');
        headlineimg.src = DOKU_BASE + 'lib/images/close.png';
        headlineimg.id  = 'media__closeimg';
        $(headlineimg).click(function () {$(popup).hide()});
        headline.insertBefore(headlineimg, headline.firstChild);
        popup.appendChild(headline);
        drag.attach(popup,headline); // Pierre: TODO

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
            $(linkbtn).click(function (event) { return media_manager.setlink(event,this); });

            var linkimg = document.createElement('img');
            linkimg.src = DOKU_BASE + 'lib/images/media_link_' + linkbtns[i] + '.png';

            linkbtn.appendChild(linkimg);
            linkp.appendChild(linkbtn);
        }

        popup.appendChild(linkp);

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
            $(alignbtn).click(function (event) { return media_manager.setalign(event,this); });

            alignp.appendChild(alignbtn);
        }

        popup.appendChild(alignp);

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
            $(sizebtn).click(function (event) { return media_manager.setsize(event,this); });
        }

        popup.appendChild(sizep);

        // send and close button

        var btnp = document.createElement('p');
        popup.appendChild(btnp);
        btnp.setAttribute('class','btnlbl');

        var btn  = document.createElement('input');
        btn.type = 'button';
        btn.id   = 'media__sendbtn';
        btn.setAttribute('class','button');
        btn.value = LANG['mediainsert'];
        btnp.appendChild(btn);
    };

    /**
     * Insert the clicked image into the opener's textarea
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Dominik Eckelmann <eckelmann@cosmocode.de>
     * @author Pierre Spring <pierre.spring@caillou.ch>
     */
    insert = function (id) {
        var opts, optsstart, alignleft, alignright;

        // set syntax options
        $('#media__popup').hide();

        opts = '';
        optsstart = '';
        alignleft = '';
        alignright = '';

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
    };

    /**
     * Prefills the wikiname.
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    suggest = function(){
        var file, name, text;

        file = $(this);
        name = $('#upload__name');
        if(!file.size() || !name.size()) return;

        text = file.val();
        text = text.substr(text.lastIndexOf('/')+1);
        text = text.substr(text.lastIndexOf('\\')+1);
        name.val(text);
    };

    /**
     * Open or close a subtree using AJAX
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Pierre Spring <pierre.spring@caillou.ch>
     */
    toggle = function (event) {
        var clicky, listitem, sublist, link, ul;

        event.preventDefault(); // TODO: really here?

        var clicky = $(this);
        var listitem = clicky.parent();

        // if already open, close by removing the sublist
        sublist = listitem.find('ul').first();
        if(sublist.size()){
            sublist.remove(); // TODO: really? we could just hide it, right?
            clicky.attr('src', DOKU_BASE + 'lib/images/plus.gif');
            return;
        }

        // get the enclosed link (is always the first one)
        link = listitem.find('a').first();

        //prepare the new ul
        ul = $('<ul/>');

        //fixme add classname here

        $.post(
            DOKU_BASE + 'lib/exe/ajax.php',
            link.attr('search').substr(1) + '&call=medians',
            function (data) {
                ul.html(data)
                listitem.append(ul);
            },
            'html'
        );

        clicky.attr('src', DOKU_BASE + 'lib/images/minus.gif');
    };

    /**
     * list the content of a namespace using AJAX
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Pierre Spring <pierre.spring@caillou.ch>
     */
    list = function (event) {
        var link, content;
        link = $(this);

        event.preventDefault();

        cleanMsgArea();
        content = $('#media__content');
        content.html('<img src="' + DOKU_BASE + 'lib/images/loading.gif" alt="..." class="load" />');

        // fetch the subtree
        $.post(
            DOKU_BASE + 'lib/exe/ajax.php',
            link.attr('search').substr(1)+'&call=medialist',
            function (data) {
                content.html(data);
                prepare_content(content);
                media_manager.updatehide();
            },
            'html'
        );

    };

    prepare_content = function (content) {
        // hide syntax example
        content.find('div.example:visible').hide();
        initFlashUpload();
    };

    /**
         * shows the popup for a image link
         */
        select = function(event){
            var link, id, dot, ext;

            event.preventDefault();

            link = $(this);
            id = link.attr('name').substr(2);

            if(!opener){
                // if we don't run in popup display example
                // the id's are a bit wired and $('#ex_wiki_dokuwiki-128.png') will not be found
                // by Sizzle (the CSS Selector Engine used by jQuery),
                // hence the document.getElementById() call
                $(document.getElementById('ex_'+id.replace(/:/g,'_').replace(/^_/,''))).toggle();
                return;
            }

            link = link[0];

            media_manager.ext = false;
            dot = id.lastIndexOf(".");

            if (-1 === dot) {
                insert(id);
                return;
            }

            ext = id.substr(dot);

            if (ext != '.jpg' && ext != '.jpeg' && ext != '.png' && ext != '.gif' && ext != '.swf') {
                insert(id);
                return;
            }

            // remove old callback from the insert button and set the new one.
            $('#media__sendbtn').unbind().click(function () {insert(id)});

            $('#media__popup').show()
                .css('left', event.pageX + 'px')
                .css('top', event.pageY + 'px');

            $('#media__popup button.button').each(function (index, element) { media_manager.outSet(element) } );


            if (ext == '.swf') {
                media_manager.ext = 'swf';

                // disable display buttons for detail and linked image
                $('#media__linkbtn1').hide();
                $('#media__linkbtn2').hide();

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
                $('#media__sizebtn4').hide();

            } else {
                media_manager.ext = 'img';

                // ensure that the display buttons are there
                $('#media__linkbtn1').show();
                $('#media__linkbtn2').show();
                $('#media__sizebtn4').show();

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
                $('#media__align').hide();
                $('#media__size').hide();
            } else {
                $('#media__align').show();
                $('#media__size').show();

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

                $('#media__sendbtn').focus();
            }

           return;
        };

    /**
     * Deletion confirmation dialog to the delete buttons.
     *
     * @author Michael Klier <chi@chimeric.de>
     * @author Pierre Spring <pierre.spring@caillou.ch>
     */
    confirmattach = function(e){
        if(!confirm(LANG['del_confirm'] + "\n" + jQuery(this).attr('title'))) {
            e.preventDefault();
        }
    };

    /**
     * Creates checkboxes for additional options
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Pierre Spring <pierre.spring@caillou.ch>
     */
    attachoptions = function(){
        obj = $('#media__opts')[0]
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
            
            $(kobox).click(
                function () {
                    toggleOption(this, 'keepopen');
                }
            );
            
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
        $(hdbox).click(
            function () {
                toggleOption(this, 'hide');
                media_manager.updatehide();
            }
        );

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
     * Generalized toggler
     *
     * @author Pierre Spring <pierre.spring@caillou.ch>
     */    
    toggleOption = function (checkbox, variable) {
        if (checkbox.checked) {
            DokuCookie.setValue(variable, 1);
            media_manager[variable] = true;
        } else {
            DokuCookie.setValue(variable, '');
            media_manager[variable] = false;
        }
    }

    initFlashUpload = function () {
        var oform, oflash, title;
        if(!hasFlash(8)) return;
        
        oform  = $('#dw__upload');
        oflash = $('#dw__flashupload');
        
        if(!oform.size() || !oflash.size()) return;
        
        title = LANG['mu_btn'];

        $('<img/>').attr('src', DOKU_BASE+'lib/images/multiupload.png')
            .attr('title', title)
            .attr('alt', title)
            .css('cursor', 'pointer')
            .click(
                function () {
                    oform.hide();
                    oflash.show();
                }
            )
            .appendTo(oform);
    };

    $(function () {
        var content = $('#media__content');
        prepare_content(content);

        attachoptions();
        initpopup();

        // add the action to autofill the "upload as" field
        content.delegate('#upload__file', 'change', suggest)
            // Attach the image selector action to all links
            .delegate('a.select', 'click', select)
            // Attach deletion confirmation dialog to the delete buttons
            .delegate('#media__content a.btn_media_delete', 'click', confirmattach);


        $('#media__tree').delegate('img', 'click', toggle)
            .delegate('a', 'click', list);
    });
}(jQuery));

var media_manager = {
    keepopen: false,
    hide: false,
    align: false,
    popup: false,
    display: false,
    link: false,
    size: false,
    ext: false,

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
    outSet: function(element) {
        if ('string' === typeof element) {
            element = '#' + element;
        }
        jQuery(element).css('border-style', 'outset');
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