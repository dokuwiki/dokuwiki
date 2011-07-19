/*jslint white: true, onevar: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, regexp: false, strict: true, newcap: true, immed: true, sloppy: true, browser: true */
/*global jQuery, DOKU_BASE, LANG, bind, DokuCookie, opener, confirm*/

/**
 * JavaScript functionality for the media management popup
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Pierre Spring <pierre.spring@caillou.ch>
 */

var dw_mediamanager = {
    keepopen: false,
    hide: false,
    popup: false,
    display: false,
    ext: false,
    $popup: null,

    // Image insertion opts
    align: false,
    link: false,
    size: false,
    forbidden_opts: {},

    init: function () {
        var $content, $tree;
        $content = jQuery('#media__content');
        $tree    = jQuery('#media__tree');

        dw_mediamanager.prepare_content($content);

        dw_mediamanager.attachoptions();
        dw_mediamanager.initpopup();

        // add the action to autofill the "upload as" field
        $content.delegate('#upload__file', 'change', dw_mediamanager.suggest)
                // Attach the image selector action to all links
                .delegate('a.select', 'click', dw_mediamanager.select)
                // Attach deletion confirmation dialog to the delete buttons
                .delegate('#media__content a.btn_media_delete', 'click',
                          dw_mediamanager.confirmattach);

        $tree.dw_tree({toggle_selector: 'img',
                       load_data: function (show_sublist, $clicky) {
                           // get the enclosed link (is always the first one)
                           var $link = $clicky.parent().find('div.li a.idx_dir');

                           jQuery.post(
                               DOKU_BASE + 'lib/exe/ajax.php',
                               $link[0].search.substr(1) + '&call=medians',
                               show_sublist,
                               'html'
                           );
                       },

                       toggle_display: function ($clicky, opening) {
                           $clicky.attr('src',
                                        DOKU_BASE + 'lib/images/' +
                                        (opening ? 'minus' : 'plus') + '.gif');
                       }});
        $tree.delegate('a', 'click', dw_mediamanager.list);

        jQuery('#mediamanager__layout_list').delegate('#mediamanager__tabs_files a', 'click', dw_mediamanager.list)
            .delegate('#mediamanager__tabs_list a', 'click', dw_mediamanager.list_view)
            .delegate('#mediamanager__file_list a', 'click', dw_mediamanager.details)
            .delegate('#dw__mediasearch', 'submit', dw_mediamanager.list)
            .delegate('#upload__file', 'change', dw_mediamanager.suggest);

        jQuery('#mediamanager__layout_detail').delegate('#mediamanager__tabs_details a', 'click', dw_mediamanager.details)
            .delegate('#mediamanager__btn_update', 'submit', dw_mediamanager.list)
            .delegate('#page__revisions', 'submit', dw_mediamanager.details)
            .delegate('#page__revisions a', 'click', dw_mediamanager.details)
            .delegate('#mediamanager__save_meta', 'submit', dw_mediamanager.details)
            .delegate('#mediamanager__btn_delete', 'submit', dw_mediamanager.details)
            .delegate('#mediamanager__btn_restore', 'submit', dw_mediamanager.details);

    },

    /**
     * build the popup window
     *
     * @author Dominik Eckelmann <eckelmann@cosmocode.de>
     */
    initpopup: function () {
        var opts, $insp, $insbtn;

        dw_mediamanager.$popup = jQuery(document.createElement('div'))
                 .attr('id', 'media__popup_content')
                 .dialog({autoOpen: false, width: 280, modal: true,
                          draggable: true, title: LANG.mediatitle,
                          resizable: false});

        opts = [{id: 'link', label: LANG.mediatarget,
                 btns: ['lnk', 'direct', 'nolnk', 'displaylnk']},
                {id: 'align', label: LANG.mediaalign,
                 btns: ['noalign', 'left', 'center', 'right']},
                {id: 'size', label: LANG.mediasize,
                 btns: ['small', 'medium', 'large', 'original']}
               ];

        jQuery.each(opts, function (_, opt) {
            var $p, $l;
            $p = jQuery(document.createElement('p'))
                 .attr('id', 'media__' + opt.id);

            if (dw_mediamanager.display === "2") {
                $p.hide();
            }

            $l = jQuery(document.createElement('label'))
                 .text(opt.label);
            $p.append($l);

            jQuery.each(opt.btns, function (i, text) {
                var $btn, $img;
                $btn = jQuery(document.createElement('button'))
                       .addClass('button')
                       .attr('id', "media__" + opt.id + "btn" + (i + 1))
                       .attr('title', LANG['media' + text])
                       .click(bind(dw_mediamanager.setOpt, opt.id));

                $img = jQuery(document.createElement('img'))
                       .attr('src', DOKU_BASE + 'lib/images/media_' +
                                    opt.id + '_' + text + '.png');

                $btn.append($img);
                $p.append($btn);
            });

            dw_mediamanager.$popup.append($p);
        });

        // insert button
        $insp = jQuery(document.createElement('p'))
                .addClass('btnlbl');
        dw_mediamanager.$popup.append($insp);

        $insbtn = jQuery(document.createElement('input'))
                  .attr('id', 'media__sendbtn')
                  .attr('type', 'button')
                  .addClass('button')
                  .val(LANG.mediainsert);
        $insp.append($insbtn);
    },

    /**
     * Insert the clicked image into the opener's textarea
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Dominik Eckelmann <eckelmann@cosmocode.de>
     * @author Pierre Spring <pierre.spring@caillou.ch>
     */
    insert: function () {
        var opts, alignleft, alignright, edid, s;

        // set syntax options
        dw_mediamanager.$popup.dialog('close');

        opts = '';
        alignleft = '';
        alignright = '';

        if ({img: 1, swf: 1}[dw_mediamanager.ext] === 1) {

            if (dw_mediamanager.link === '4') {
                    opts = '?linkonly';
            } else {

                if (dw_mediamanager.link === "3" && dw_mediamanager.ext === 'img') {
                    opts = '?nolink';
                } else if (dw_mediamanager.link === "2" && dw_mediamanager.ext === 'img') {
                    opts = '?direct';
                }

                s = parseInt(dw_mediamanager.size, 10);

                if (s && s >= 1 && s < 4) {
                    opts += (opts.length)?'&':'?';
                    opts += dw_mediamanager.size + '00';
                    if (dw_mediamanager.ext === 'swf') {
                        switch (s) {
                        case 1:
                            opts += 'x62';
                            break;
                        case 2:
                            opts += 'x123';
                            break;
                        case 3:
                            opts += 'x185';
                            break;
                        }
                    }
                }
                alignleft = dw_mediamanager.align === '2' ? '' : ' ';
                alignright = dw_mediamanager.align === '4' ? '' : ' ';
            }
        }
        edid = String.prototype.match.call(document.location, /&edid=([^&]+)/);
        opener.insertTags(edid ? edid[1] : 'wiki__text',
                          '{{'+alignleft+id+opts+alignright+'|','}}','');

        if(!dw_mediamanager.keepopen) {
            window.close();
        }
        opener.focus();
        return false;
    },

    /**
     * Prefills the wikiname.
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    suggest: function(){
        var $file, $name, text;

        $file = jQuery(this);
        $name = jQuery('#upload__name');

        if ($name.val() != '') return;

        if(!$file.length || !$name.length) {
            return;
        }

        text = $file.val();
        text = text.substr(text.lastIndexOf('/')+1);
        text = text.substr(text.lastIndexOf('\\')+1);
        $name.val(text);
    },

    /**
     * list the content of a namespace using AJAX
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Pierre Spring <pierre.spring@caillou.ch>
     */
    list: function (event) {
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
                    jQuery(this).removeClass('selected');
                });
                $link.addClass('selected');
            }

            jQuery('.scroll-container', $content).html('<img src="' + DOKU_BASE + 'lib/images/loading.gif" alt="..." class="load" />');
        }

        params = '';

        if ($link[0].search) {
            params = $link[0].search.substr(1)+'&call=medialist';
        } else if ($link[0].action) {
            params = dw_mediamanager.form_params($link)+'&call=medialist';
        }

        // fetch the subtree
        dw_mediamanager.update_content($content, params);

    },

     /**
     * Returns form parameters
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     */
    form_params: function ($form) {
        var elements = $form.serialize();
        var action = '';
        var i = $form[0].action.indexOf('?');
        if (i >= 0) action = $form[0].action.substr(i+1);
        return elements+'&'+action;
    },

     /**
     * Changes view of media files list
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     */
    list_view: function (event) {
        var $link, $content;
        $link = jQuery(this);

        event.preventDefault();

        $content = jQuery('#mediamanager__file_list');

        if ($link[0].id == 'mediamanager__link_thumbs') {
            $content.removeClass('mediamanager-list');
            $content.addClass('mediamanager-thumbs');

        } else if ($link[0].id == 'mediamanager__link_list') {
            $content.removeClass('mediamanager-thumbs');
            $content.addClass('mediamanager-list');
        }
    },

     /**
     * Lists the content of the right column (image details) using AJAX
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     */
    details: function (event) {
        var $link, $content, params, update_list;
        $link = jQuery(this);

        event.preventDefault();

        jQuery('div.success, div.info, div.error, div.notify').remove();

        if ($link[0].id == 'mediamanager__btn_delete' && !confirm(LANG['del_confirm'])) return false;
        if ($link[0].id == 'mediamanager__btn_restore' && !confirm(LANG['restore_confirm'])) return false;

        $content = jQuery('#mediamanager__layout_detail');
        if (jQuery('.scroll-container', $content).length) {
            jQuery('.scroll-container', $content).html('<img src="' + DOKU_BASE + 'lib/images/loading.gif" alt="..." class="load" />');
        } else {
            jQuery($content).html('<img src="' + DOKU_BASE + 'lib/images/loading.gif" alt="..." class="load" />');
        }

        params = '';

        if ($link[0].search) {
            params = $link[0].search.substr(1)+'&call=mediadetails';
        } else {
            params = dw_mediamanager.form_params($link)+'&call=mediadetails';
        }

        dw_mediamanager.update_content($content, params);

        update_list = ($link[0].id == 'mediamanager__btn_delete' || $link[0].id == 'mediamanager__btn_restore');
        if (update_list) {
            var $link1, $content1, params1;
            $link1 = jQuery('a.files');
            params1 = $link1[0].search.substr(1)+'&call=medialist';
            $content1 = jQuery('#mediamanager__layout_list');
            jQuery('.scroll-container', $content1).html('<img src="' + DOKU_BASE + 'lib/images/loading.gif" alt="..." class="load" />');

            dw_mediamanager.update_content($content1, params1);
        }
    },

    update_content: function ($content, params) {
        jQuery.post(
            DOKU_BASE + 'lib/exe/ajax.php',
            params,
            function (data) {
                jQuery('.ui-resizable').each(function(){
                    jQuery(this).resizable('destroy');
                });

                $content.html(data);
                dw_mediamanager.prepare_content($content);
                dw_mediamanager.updatehide();
                dw_mediamanager.update_resizable(0);
                dw_mediamanager.opacity_slider();
                dw_mediamanager.portions_slider();
                addInitEvent(revisionsForm);
            },
            'html'
        );
    },

    update_resizable: function (count_width) {
        $resizable = jQuery("#mediamanager__layout .layout-resizable");

        $resizable.resizable({ handles: 'e' ,
            resize: function(event, ui){
                var w = 0;
                $resizable.each(function() {
                    w += jQuery(this).width();
                });
                wSum = w + parseFloat(jQuery('#mediamanager__layout_detail').css("min-width"));

                // max width of resizable column
                var maxWidth = 0.95 * jQuery('#mediamanager__layout').width() - wSum + jQuery(this).width() - 30;
                $resizable.resizable( "option", "maxWidth", maxWidth );

                // percentage width of the first two columns
                var wLeft = ( 100*(w+30) / jQuery('#mediamanager__layout').width() );

                // width of the third column
                var wRight = 95-wLeft;
                wRight += "%";
                jQuery('#mediamanager__layout_detail').width(wRight);
            }
        });

        var windowHeight = jQuery(window).height();
        var height = windowHeight - 300;
        jQuery('#mediamanager__layout .scroll-container').each(function (i) {
            jQuery(this).height(height);
        });
        $resizable.each(function() {
            jQuery(this).height(height+100);
        });
    },

    opacity_slider: function () {
        var $slider = jQuery( "#mediamanager__opacity_slider" );
        $slider.slider();
        $slider.slider("option", "min", 0);
        $slider.slider("option", "max", 0.999);
        $slider.slider("option", "step", 0.001);
        $slider.slider("option", "value", 0.5);
        $slider.bind("slide", function(event, ui) {
            jQuery('#mediamanager__diff_opacity_image2').css({ opacity: $slider.slider("option", "value")});
        });
    },

    portions_slider: function () {
        var $slider = jQuery( "#mediamanager__portions_slider" );
        $slider.slider();
        $slider.slider("option", "min", 0);
        $slider.slider("option", "max", 100);
        $slider.slider("option", "step", 1);
        $slider.slider("option", "value", 50);
        $slider.bind("slide", function(event, ui) {
            jQuery('#mediamanager__diff_portions_image2').css({ width: $slider.slider("option", "value")+'%'});
        });
    },

    prepare_content: function ($content) {
        // hide syntax example
        $content.find('div.example:visible').hide();
        dw_mediamanager.initFlashUpload();
    },

    /**
     * shows the popup for a image link
     */
    select: function(event){
        var $link, id, dot, ext;

        event.preventDefault();

        $link = jQuery(this);
        id = $link.attr('name').substr(2);

        if(!opener){
            // if we don't run in popup display example
            // the id's are a bit wierd and jQuery('#ex_wiki_dokuwiki-128.png')
            // will not be found by Sizzle (the CSS Selector Engine
            // used by jQuery), hence the document.getElementById() call
            jQuery(document.getElementById('ex_'+id.replace(/:/g,'_').replace(/^_/,''))).dw_toggle();
            return;
        }

        dw_mediamanager.ext = false;
        dot = id.lastIndexOf(".");

        if (-1 === dot) {
            dw_mediamanager.insert(id);
            return;
        }

        ext = id.substr(dot);

        if ({'.jpg':1, '.jpeg':1, '.png':1, '.gif':1, '.swf':1}[ext] !== 1) {
            dw_mediamanager.insert(id);
            return;
        }

        // remove old callback from the insert button and set the new one.
        jQuery('#media__sendbtn').unbind().click(bind(dw_mediamanager.insert, id));

        dw_mediamanager.unforbid('ext');
        if (ext === '.swf') {
            dw_mediamanager.ext = 'swf';
            dw_mediamanager.forbid('ext', {link: ['1', '2'],
                                           size: ['4']});
        } else {
            dw_mediamanager.ext = 'img';
        }

        // Set to defaults
        dw_mediamanager.setOpt('link');
        dw_mediamanager.setOpt('align');
        dw_mediamanager.setOpt('size');

        // toggle buttons for detail and linked image, original size
        jQuery('#media__linkbtn1, #media__linkbtn2, #media__sizebtn4')
            .toggle(dw_mediamanager.ext === 'img');

        dw_mediamanager.$popup.dialog('open');

        jQuery('#media__sendbtn').focus();
    },

    /**
     * Deletion confirmation dialog to the delete buttons.
     *
     * @author Michael Klier <chi@chimeric.de>
     * @author Pierre Spring <pierre.spring@caillou.ch>
     */
    confirmattach: function(e){
        if(!confirm(LANG.del_confirm + "\n" + jQuery(this).attr('title'))) {
            e.preventDefault();
        }
    },

    /**
     * Creates checkboxes for additional options
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Pierre Spring <pierre.spring@caillou.ch>
     */
    attachoptions: function(){
        var $obj, opts;

        $obj = jQuery('#media__opts');
        if($obj.length === 0) {
            return;
        }

        opts = [];
        // keep open
        if(opener){
            opts.push(['keepopen', 'keepopen']);
        }
        opts.push(['hide', 'hidedetails']);

        jQuery.each(opts,
                    function(_, opt) {
                        var $box, $lbl;
                        $box = jQuery(document.createElement('input'))
                                 .attr('type', 'checkbox')
                                 .attr('id', 'media__' + opt[0])
                                 .click(bind(dw_mediamanager.toggleOption,
                                             opt[0]));

                        if(DokuCookie.getValue(opt[0])){
                            $box.prop('checked', true);
                            dw_mediamanager[opt[0]] = true;
                        }

                        $lbl = jQuery(document.createElement('label'))
                                 .attr('for', 'media__' + opt[0])
                                 .text(LANG[opt[1]]);

                        $obj.append($box, $lbl, document.createElement('br'));
                    });

        dw_mediamanager.updatehide();
    },

    /**
     * Generalized toggler
     *
     * @author Pierre Spring <pierre.spring@caillou.ch>
     */
    toggleOption: function (variable) {
        if (jQuery(this).prop('checked')) {
            DokuCookie.setValue(variable, 1);
            dw_mediamanager[variable] = true;
        } else {
            DokuCookie.setValue(variable, '');
            dw_mediamanager[variable] = false;
        }
        if (variable === 'hide') {
            dw_mediamanager.updatehide();
        }
    },

    initFlashUpload: function () {
        var $oform, $oflash;
        if(!hasFlash(8)) {
            return;
        }

        $oform  = jQuery('#dw__upload');
        $oflash = jQuery('#dw__flashupload');

        if(!$oform.length || !$oflash.length) {
            return;
        }

        jQuery(document.createElement('img'))
            .attr('src', DOKU_BASE+'lib/images/multiupload.png')
            .attr('title', LANG.mu_btn)
            .attr('alt', LANG.mu_btn)
            .css('cursor', 'pointer')
            .click(
                function () {
                    $oform.hide();
                    $oflash.show();
                }
            )
            .appendTo($oform);
    },

    /**
     * Sets the visibility of the image details accordingly to the
     * chosen hide state
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    updatehide: function(){
        jQuery('#media__content div.detail').dw_toggle(!dw_mediamanager.hide);
    },

    /**
     * set media insertion option
     *
     * @author Dominik Eckelmann <eckelmann@cosmocode.de>
     */
    setOpt: function(opt, e){
        var val, i;
        if (typeof e !== 'undefined') {
            val = this.id.substring(this.id.length - 1);
        } else {
            val = dw_mediamanager.getOpt(opt);
        }

        if (val === false) {
            DokuCookie.setValue(opt,'');
            dw_mediamanager[opt] = false;
            return;
        }

        if (opt === 'link') {
            if (val !== '4' && dw_mediamanager.link === '4') {
                dw_mediamanager.unforbid('linkonly');
                dw_mediamanager.setOpt('align');
                dw_mediamanager.setOpt('size');
            } else if (val === '4') {
                dw_mediamanager.forbid('linkonly', {align: false, size: false});
            }

            jQuery("#media__size, #media__align").dw_toggle(val !== '4');
        }

        DokuCookie.setValue(opt, val);
        dw_mediamanager[opt] = val;

        for (i = 1; i <= 4; i++) {
            jQuery("#media__" + opt + "btn" + i).removeClass('selected');
        }
        jQuery('#media__' + opt + 'btn' + val).addClass('selected');
    },

    unforbid: function (group) {
        delete dw_mediamanager.forbidden_opts[group];
    },

    forbid: function (group, forbids) {
        dw_mediamanager.forbidden_opts[group] = forbids;
    },

    allowedOpt: function (opt, val) {
        var ret = true;
        jQuery.each(dw_mediamanager.forbidden_opts,
                    function (_, forbids) {
                        ret = forbids[opt] !== false &&
                              jQuery.inArray(val, forbids[opt]) === -1;
                        return ret;
                    });
        return ret;
    },

    getOpt: function (opt) {
        var allowed = bind(dw_mediamanager.allowedOpt, opt);

        // Current value
        if (dw_mediamanager[opt] !== false && allowed(dw_mediamanager[opt])) {
            return dw_mediamanager[opt];
        }

        // From cookie
        if (DokuCookie.getValue(opt) && allowed(DokuCookie.getValue(opt))) {
            return DokuCookie.getValue(opt);
        }

        // size default
        if (opt === 'size' && allowed('2')) {
            return '2';
        }

        // Whatever is allowed, and be it false
        return jQuery.grep(['1', '2', '3', '4'], allowed)[0] || false;
    }
};

// moved from helpers.js temporarily here
/**
 * Very simplistic Flash plugin check, probably works for Flash 8 and higher only
 *
 */
function hasFlash(version){
    var ver = 0, axo;
    try{
        if(navigator.plugins !== null && navigator.plugins.length > 0){
           ver = navigator.plugins["Shockwave Flash"].description.split(' ')[2].split('.')[0];
        }else{
           axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
           ver = axo.GetVariable("$version").split(' ')[1].split(',')[0];
        }
    }catch(e){ }

    return ver >= version;
}

jQuery(document).ready(function() {
    dw_mediamanager.update_resizable(1);
    dw_mediamanager.opacity_slider();
    dw_mediamanager.portions_slider();
    jQuery(window).resize(dw_mediamanager.update_resizable);
});

jQuery(dw_mediamanager.init);
