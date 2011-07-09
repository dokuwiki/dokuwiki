/*jslint white: true, onevar: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, regexp: false, strict: true, newcap: true, immed: true */
/*global jQuery, window, DOKU_BASE*/
"use strict";

// * refactor once the jQuery port is over ;)

/**
 * JavaScript functionality for the media management popup
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Pierre Spring <pierre.spring@caillou.ch>
 */
(function ($) {
    var toggle, list, prepare_content, insert, confirmattach, attachoptions, initpopup, updatehide, setalign, setsize, inSet, outSet, media_manager, hasFlash, form_params, list_view, details, update_content;

    var media_manager = {
        keepopen: false,
        hide: false,
        align: false,
        popup: false,
        display: false,
        link: false,
        size: false,
        ext: false,
    };




    /**
     * build the popup window
     *
     * @author Dominik Eckelmann <eckelmann@cosmocode.de>
     */
    initpopup = function() {
        var popup;

        popup = document.createElement('div');
        popup.setAttribute('id','media__popup');
        popup.style.display = "none";

        var root = document.getElementById('media__manager');
        if (root === null) return;
        root.appendChild(popup);

        var headline    = document.createElement('h1');
        headline.innerHTML = LANG.mediatitle;
        var headlineimg = document.createElement('img');
        headlineimg.src = DOKU_BASE + 'lib/images/close.png';
        headlineimg.id  = 'media__closeimg';
        $(headlineimg).click(function () {$(popup).hide()});
        headline.insertBefore(headlineimg, headline.firstChild);
        popup.appendChild(headline);
        $(popup).draggable({handle: headline});

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
            linkbtn.id    = "media__linkbtn" + (i+1);
            linkbtn.title = LANG['media' + linkbtns[i]];
            linkbtn.style.borderStyle = 'outset';
            $(linkbtn).click(function (event) { return setlink(event,this); });

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

            alignbtn.id    = "media__alignbtn" + (n+1);
            alignbtn.title = LANG['media' + alignbtns[n]];
            alignbtn.className = 'button';
            alignbtn.appendChild(alignimg);
            alignbtn.style.borderStyle = 'outset';
            $(alignbtn).click(function (event) { return setalign(event,this); });

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
            sizebtn.id    = 'media__sizebtn' + (size + 1);
            sizebtn.title = LANG['media' + sizebtns[size]];
            sizebtn.style.borderStyle = 'outset';
            $(sizebtn).click(function (event) { return setsize(event,this); });
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

    // moved from helpers.js temporarily here
    /**
     * Very simplistic Flash plugin check, probably works for Flash 8 and higher only
     *
     */
    hasFlash = function(version){
        var ver = 0;
        try{
            if(navigator.plugins != null && navigator.plugins.length > 0){
               ver = navigator.plugins["Shockwave Flash"].description.split(' ')[2].split('.')[0];
            }else{
               var axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
               ver = axo.GetVariable("$version").split(' ')[1].split(',')[0];
            }
        }catch(e){ }

        if(ver >= version) return true;
        return false;
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

                var s = parseInt(media_manager.size, 10);

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
                if (media_manager.align == '2') {
                    alignleft = '';
                    alignright = ' ';
                }
                if (media_manager.align == '3') {
                    alignleft = ' ';
                    alignright = ' ';
                }
                if (media_manager.align == '4') {
                    alignleft = ' ';
                    alignright = '';
                }
            }
        }
        var edid = String.prototype.match.call(document.location, /&edid=([^&]+)/);
        edid = edid ? edid[1] : 'wiki__text';
        opener.insertTags(edid,'{{'+alignleft+id+opts+alignright+'|','}}','');

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
            link[0].search.substr(1) + '&call=medians',
            function (data) {
                ul.html(data);
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
        var $link, $content, params;
        $link = jQuery(this);

        event.preventDefault();

        jQuery('div.success, div.info, div.error, div.notify').remove();

        if (document.getElementById('media__content')) {
            //popup
            $content = jQuery('#media__content');
            $content.html('<img src="' + DOKU_BASE + 'lib/images/loading.gif" alt="..." class="load" />');

        } else {
            //fullscreen media manager
            $content = jQuery('#mediamanager__layout_list');

            if ($link.hasClass('idx_dir')) {
                //changing namespace
                jQuery('#mediamanager__layout_detail').empty();
                jQuery('#media__tree .selected').each(function(){
                    $(this).removeClass('selected');
                });
                $link.addClass('selected');
            }

            jQuery('.scroll-container', $content).html('<img src="' + DOKU_BASE + 'lib/images/loading.gif" alt="..." class="load" />');
        }

        params = '';

        if ($link[0].search) {
            params = $link[0].search.substr(1)+'&call=medialist';
        } else if ($link[0].action) {
            params = form_params($link)+'&call=medialist';
        }

        // fetch the subtree
        update_content($content, params);

    };

     /**
     * Returns form parameters
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     */
    form_params = function ($form) {
        var elements = $form.serialize();
        var action = '';
        var i = $form[0].action.indexOf('?');
        if (i >= 0) action = $form[0].action.substr(i+1);
        return elements+'&'+action;
    };

     /**
     * Changes view of media files list
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     */
    list_view  = function (event) {
        var $link, $content;
        $link = jQuery(this);

        event.preventDefault();

        $content = jQuery('#mediamanager__file_list');
        if ($link.hasClass('mediamanager-link-thumbnails')) {
            $content.removeClass('mediamanager-list');
            $content.addClass('mediamanager-thumbs');
        } else if ($link.hasClass('mediamanager-link-list')) {
            $content.removeClass('mediamanager-thumbs');
            $content.addClass('mediamanager-list');
        }
    };

     /**
     * Lists the content of the right column (image details) using AJAX
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     */
    details = function (event) {
        var $link, $content, params, update_list;
        $link = jQuery(this);

        event.preventDefault();

        jQuery('div.success, div.info, div.error, div.notify').remove();

        if ($link[0].id == 'mediamanager__btn_delete' && !confirm(LANG['del_confirm'])) return false;
        if ($link[0].id == 'mediamanager__btn_restore' && !confirm(LANG['restore_confirm'])) return false;

        $content = $('#mediamanager__layout_detail');
        jQuery('.scroll-container', $content).html('<img src="' + DOKU_BASE + 'lib/images/loading.gif" alt="..." class="load" />');

        params = '';

        if ($link[0].search) {
            params = $link[0].search.substr(1)+'&call=mediadetails';
        } else {
            params = form_params($link)+'&call=mediadetails';
        }

        update_content($content, params);

        update_list = ($link[0].id == 'mediamanager__btn_delete' || $link[0].id == 'mediamanager__btn_restore');
        if (update_list) {
            var $link1, $content1, params1;
            $link1 = jQuery('a.files');
            params1 = $link1[0].search.substr(1)+'&call=medialist';
            $content1 = jQuery('#mediamanager__layout_list');
            jQuery('.scroll-container', $content1).html('<img src="' + DOKU_BASE + 'lib/images/loading.gif" alt="..." class="load" />');

            update_content($content1, params1);
        }
    };

    update_content = function ($content, params) {
        jQuery.post(
            DOKU_BASE + 'lib/exe/ajax.php',
            params,
            function (data) {
                content.html(data);
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

            $('#media__popup button.button').each(function (index, element) { outSet(element) } );


            if (ext == '.swf') {
                media_manager.ext = 'swf';

                // disable display buttons for detail and linked image
                $('#media__linkbtn1').hide();
                $('#media__linkbtn2').hide();

                // set the link button to default
                if (media_manager.link != false) {
                    if ( media_manager.link == '2' || media_manager.link == '1')  {
                        inSet('media__linkbtn3');
                        media_manager.link = '3';
                        DokuCookie.setValue('link','3');
                    } else {
                        inSet('media__linkbtn'+media_manager.link);
                    }
                } else if (DokuCookie.getValue('link')) {
                    if ( DokuCookie.getValue('link') == '2' ||  DokuCookie.getValue('link') == '1')  {
                        // this options are not availible
                        inSet('media__linkbtn3');
                        media_manager.link = '3';
                        DokuCookie.setValue('link','3');
                    } else {
                        inSet('media__linkbtn'+DokuCookie.getValue('link'));
                        media_manager.link = DokuCookie.getValue('link');
                    }
                } else {
                    // default case
                    media_manager.link = '3';
                    inSet('media__linkbtn3');
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
                inSet('media__linkbtn'+media_manager.link);
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
                    inSet('media__alignbtn'+media_manager.align);
                } else if (DokuCookie.getValue('align')) {
                    inSet('media__alignbtn'+DokuCookie.getValue('align'));
                    media_manager.align = DokuCookie.getValue('align');
                } else {
                    // default case
                    media_manager.align = '0';
                    inSet('media__alignbtn0');
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
                inSet('media__sizebtn'+media_manager.size);

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
        obj = $('#media__opts')[0];
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
                updatehide();
            }
        );

        var hdlbl  = document.createElement('label');
        hdlbl.htmlFor   = 'media__hide';
        hdlbl.innerHTML = LANG['hidedetails'];

        var hdbr = document.createElement('br');

        obj.appendChild(hdbox);
        obj.appendChild(hdlbl);
        obj.appendChild(hdbr);
        updatehide();
    };

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
    };

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

    /**
     * Sets the visibility of the image details accordingly to the
     * chosen hide state
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    updatehide = function(){
        var content = $('#media__content');
        if(0 === content.size()) {
            return;
        }
        content.find('div.detail').each(
            function (index, element) {
                if(media_manager.hide){
                    element.style.display = 'none';
                }else{
                    element.style.display = '';
                }
            }

        );
    };

    /**
     * set the align
     *
     * @author Dominik Eckelmann <eckelmann@cosmocode.de>
     */
    setalign = function(event,cb){

        var id = cb.id.substring(cb.id.length -1);
        if(id){
            DokuCookie.setValue('align',id);
            media_manager.align = id;
            for (var i = 1; i<=4; i++) {
                outSet("media__alignbtn" + i);
            }
            inSet("media__alignbtn"+id);
        }else{
            DokuCookie.setValue('align','');
            media_manager.align = false;
        }
    };

    /**
     * set the link type
     *
     * @author Dominik Eckelmann <eckelmann@cosmocode.de>
     */
    setlink = function(event,cb){
        var id = cb.id.substring(cb.id.length -1);
        if(id){
            DokuCookie.setValue('link',id);
            for (var i = 1; i<=4; i++) {
                outSet("media__linkbtn"+i);
            }
            inSet("media__linkbtn"+id);

            var size = $("#media__size");
            var align = $("#media__align");
            if (id != '4') {
                size.show();
                align.show();
                if (media_manager.link == '4') {
                    media_manager.align = '1';
                    DokuCookie.setValue('align', '1');
                    inSet('media__alignbtn1');

                    media_manager.size = '2';
                    DokuCookie.setValue('size', '2');
                    inSet('media__sizebtn2');
                }

            } else {
                size.hide();
                align.hide();
            }
            media_manager.link = id;
        }else{
            DokuCookie.setValue('link','');
            media_manager.link = false;
        }
    };

    /**
     * set the image size
     *
     * @author Dominik Eckelmann <eckelmann@cosmocode.de>
     */
    setsize = function(event,cb){
        var id = cb.id.substring(cb.id.length -1);
        if (id) {
            DokuCookie.setValue('size',id);
            media_manager.size = id;
            for (var i = 1 ; i <=4 ; ++i) {
                outSet("media__sizebtn" + i);
            }
            inSet("media__sizebtn"+id);
        } else {
            DokuCookie.setValue('size','');
            media_manager.width = false;
        }
    };

    /**
     * sets the border to inset
     */
    inSet = function(id) {
        var ele = $('#' + id).css('border-style', 'inset');
    };

    /**
     * sets the border to outset
     */
    outSet = function(element) {
        if ('string' === typeof element) {
            element = '#' + element;
        }
        $(element).css('border-style', 'outset');
    };

    $(function () {
        var $content = jQuery('#media__content');
        prepare_content($content);

        attachoptions();
        initpopup();

        // add the action to autofill the "upload as" field
        $content.delegate('#upload__file', 'change', suggest)
            // Attach the image selector action to all links
            .delegate('a.select', 'click', select)
            // Attach deletion confirmation dialog to the delete buttons
            .delegate('#media__content a.btn_media_delete', 'click', confirmattach);


        jQuery('#media__tree').delegate('img', 'click', toggle)
            .delegate('a', 'click', list);

        jQuery('#mediamanager__layout_list').delegate('#mediamanager__tabs_files a', 'click', list)
            .delegate('#mediamanager__tabs_list a', 'click', list_view)
            .delegate('#mediamanager__file_list a', 'click', details)
            .delegate('#dw__mediasearch', 'submit', list);

        jQuery('#mediamanager__layout_detail').delegate('#mediamanager__tabs_details a', 'click', details)
            .delegate('#mediamanager__btn_update', 'submit', list)
            .delegate('#page__revisions', 'submit', details)
            .delegate('#page__revisions a', 'click', details)
            .delegate('#mediamanager__save_meta', 'submit', details)
            .delegate('#mediamanager__btn_delete', 'submit', details)
            .delegate('#mediamanager__btn_restore', 'submit', details);
    });
}(jQuery));
