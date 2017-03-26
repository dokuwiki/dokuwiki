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

    // File list options
    view_opts: {list: false, sort: false},

    layout_width: 0,

    // The minimum height of the full-screen mediamanager in px
    minHeights: {thumbs: 200, rows: 100},

    init: function () {
        var $content, $tree;
        $content = jQuery('#media__content');
        $tree = jQuery('#media__tree');
        if (!$tree.length) return;

        dw_mediamanager.prepare_content($content);

        dw_mediamanager.attachoptions();
        dw_mediamanager.initpopup();

        // add the action to autofill the "upload as" field
        $content
            .on('change', '#upload__file', dw_mediamanager.suggest)
            // Attach the image selector action to all links
            .on('click', 'a.select', dw_mediamanager.select)
            // Attach deletion confirmation dialog to the delete buttons
            .on('click', '#media__content a.btn_media_delete', dw_mediamanager.confirmattach)
            .on('submit', '#mediamanager__done_form', dw_mediamanager.list);

        $tree.dw_tree({
            toggle_selector: 'img',
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
                $clicky.attr('src', DOKU_BASE + 'lib/images/' + (opening ? 'minus' : 'plus') + '.gif');
            }
        });
        $tree.on('click', 'a', dw_mediamanager.list);

        // Init view property
        dw_mediamanager.set_fileview_list();

        dw_mediamanager.init_options();

        dw_mediamanager.image_diff();
        dw_mediamanager.init_ajax_uploader();

        // changing opened tab in the file list panel
        var $page = jQuery('#mediamanager__page');
        $page.find('div.filelist')
            .on('click', 'ul.tabs a', dw_mediamanager.list)
            // loading file details
            .on('click', 'div.panelContent a', dw_mediamanager.details)
            // search form
            .on('submit', '#dw__mediasearch', dw_mediamanager.list)
            // "upload as" field autofill
            .on('change', '#upload__file', dw_mediamanager.suggest)
            // uploaded images
            .on('click', '.qq-upload-file a', dw_mediamanager.details);

        // changing opened tab in the file details panel
        $page.find('div.file')
            .on('click', 'ul.tabs a', dw_mediamanager.details)
            // "update new version" button
            .on('submit', '#mediamanager__btn_update', dw_mediamanager.list)
            // revisions form
            .on('submit', '#page__revisions', dw_mediamanager.details)
            .on('click', '#page__revisions a', dw_mediamanager.details)
            // meta edit form
            .on('submit', '#mediamanager__save_meta', dw_mediamanager.details)
            // delete button
            .on('submit', '#mediamanager__btn_delete', dw_mediamanager.details)
            // "restore this version" button
            .on('submit', '#mediamanager__btn_restore', dw_mediamanager.details)
            // less/more recent buttons in media revisions form
            .on('submit', '.btn_newer, .btn_older', dw_mediamanager.details);

        dw_mediamanager.update_resizable();
        dw_mediamanager.layout_width = $page.width();
        jQuery(window).resize(dw_mediamanager.window_resize);
    },

    init_options: function () {
        var $options = jQuery('div.filelist div.panelHeader form.options'),
            $listType, $sortBy, $both;
        if ($options.length === 0) {
            return;
        }

        $listType = $options.find('li.listType');
        $sortBy = $options.find('li.sortBy');
        $both = $listType.add($sortBy);

        // Remove the submit button
        $options.find('button[type=submit]').parent().hide();

        // Prepare HTML for jQuery UI buttonset
        $both.find('label').each(function () {
            var $this = jQuery(this);
            $this.children('input').appendTo($this.parent());
        });

        // Init buttonset
        $both.find("input[type='radio']").checkboxradio({icon: false});
        $both.controlgroup();

        // Change handlers
        $listType.children('input').change(function () {
            dw_mediamanager.set_fileview_list();
        });
        $sortBy.children('input').change(function (event) {
            dw_mediamanager.set_fileview_sort();
            dw_mediamanager.list.call(jQuery('#dw__mediasearch')[0] || this, event);
        });
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
            .dialog({
                autoOpen: false, width: 280, modal: true,
                draggable: true, title: LANG.mediatitle,
                resizable: false
            });

        opts = [
            {
                id: 'link', label: LANG.mediatarget,
                btns: ['lnk', 'direct', 'nolnk', 'displaylnk']
            },
            {
                id: 'align', label: LANG.mediaalign,
                btns: ['noalign', 'left', 'center', 'right']
            },
            {
                id: 'size', label: LANG.mediasize,
                btns: ['small', 'medium', 'large', 'original']
            }
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
                    .attr('src', DOKU_BASE + 'lib/images/media_' + opt.id + '_' + text + '.png');

                $btn.append($img);
                $p.append($btn);
            });

            dw_mediamanager.$popup.append($p);
        });

        // insert button
        $insp = jQuery(document.createElement('p'));
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
    insert: function (id) {
        var opts, cb, edid, s;

        // set syntax options
        dw_mediamanager.$popup.dialog('close');

        opts = '';

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
                var size = s * 200;

                if (s && s >= 1 && s < 4) {
                    opts += (opts.length) ? '&' : '?';
                    opts += size;
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
            }
        }
        edid = String.prototype.match.call(document.location, /&edid=([^&]+)/);
        edid = edid ? edid[1] : 'wiki__text';
        cb = String.prototype.match.call(document.location, /&onselect=([^&]+)/);
        cb = cb ? cb[1].replace(/[^\w]+/, '') : 'dw_mediamanager_item_select';

        opener[cb](edid, id, opts, dw_mediamanager.align);
        if (!dw_mediamanager.keepopen) {
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
    suggest: function () {
        var $file, $name, text;

        $file = jQuery(this);
        $name = jQuery('#upload__name');

        if ($name.val() != '') return;

        if (!$file.length || !$name.length) {
            return;
        }

        text = $file.val();
        text = text.substr(text.lastIndexOf('/') + 1);
        text = text.substr(text.lastIndexOf('\\') + 1);
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

        if (event) {
            event.preventDefault();
        }

        jQuery('div.success, div.info, div.error, div.notify').remove();

        $link = jQuery(this);

        //popup
        $content = jQuery('#media__content');

        if ($content.length === 0) {
            //fullscreen media manager
            $content = jQuery('div.filelist');

            if ($link.hasClass('idx_dir')) {
                //changing namespace
                jQuery('div.file').empty();
                jQuery('div.namespaces .selected').removeClass('selected');
                $link.addClass('selected');
            }
        }

        params = 'call=medialist&';

        if ($link[0].search) {
            params += $link[0].search.substr(1);
        } else if ($link.is('form')) {
            params += dw_mediamanager.form_params($link);
        } else if ($link.closest('form').length > 0) {
            params += dw_mediamanager.form_params($link.closest('form'));
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
        if (!$form.length) return;

        var action = '';
        var i = $form[0].action.indexOf('?');
        if (i >= 0) {
            action = $form[0].action.substr(i + 1);
        }
        return action + '&' + $form.serialize();
    },

    set_fileview_list: function (new_type) {
        dw_mediamanager.set_fileview_opt(['list', 'listType', function (new_type) {
            jQuery('div.filelist div.panelContent ul')
                .toggleClass('rows', new_type === 'rows')
                .toggleClass('thumbs', new_type === 'thumbs');
        }], new_type);

        // FIXME: Move to onchange handler (opt[2])?
        dw_mediamanager.resize();
    },

    set_fileview_sort: function (new_sort) {
        dw_mediamanager.set_fileview_opt(['sort', 'sortBy', function (new_sort) {
            // FIXME
        }], new_sort);
    },

    set_fileview_opt: function (opt, new_val) {
        if (typeof new_val === 'undefined') {
            new_val = jQuery('form.options li.' + opt[1] + ' input')
                .filter(':checked').val();
            // if new_val is still undefined (because form.options is not in active tab), set to most spacious option
            if (typeof new_val === 'undefined') {
                new_val = 'thumbs';
            }
        }

        if (new_val !== dw_mediamanager.view_opts[opt[0]]) {
            opt[2](new_val);

            DokuCookie.setValue(opt[0], new_val);

            dw_mediamanager.view_opts[opt[0]] = new_val;
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

        if ($link[0].id == 'mediamanager__btn_delete' && !confirm(LANG.del_confirm)) {
            return false;
        }
        if ($link[0].id == 'mediamanager__btn_restore' && !confirm(LANG.restore_confirm)) {
            return false;
        }

        $content = jQuery('div.file');
        params = 'call=mediadetails&';

        if ($link[0].search) {
            params += $link[0].search.substr(1);
        } else if ($link.is('form')) {
            params += dw_mediamanager.form_params($link);
        } else if ($link.closest('form').length > 0) {
            params += dw_mediamanager.form_params($link.closest('form'));
        }

        update_list = ($link[0].id == 'mediamanager__btn_delete' ||
        $link[0].id == 'mediamanager__btn_restore');

        dw_mediamanager.update_content($content, params, update_list);
    },

    update_content: function ($content, params, update_list) {
        var $container;

        jQuery.post(
            DOKU_BASE + 'lib/exe/ajax.php',
            params,
            function (data) {
                dw_mediamanager.$resizables().resizable('destroy');

                if (update_list) {
                    dw_mediamanager.list.call(jQuery('#mediamanager__page').find('form.options button[type="submit"]')[0]);
                }

                $content.html(data);

                dw_mediamanager.prepare_content($content);
                dw_mediamanager.updatehide();

                dw_mediamanager.update_resizable();
                dw_behaviour.revisionBoxHandler();

                // Make sure that the list view style stays the same
                dw_mediamanager.set_fileview_list(dw_mediamanager.view_opts.list);

                dw_mediamanager.image_diff();
                dw_mediamanager.init_ajax_uploader();
                dw_mediamanager.init_options();

            },
            'html'
        );
        $container = $content.find('div.panelContent');
        if ($container.length === 0) {
            $container = $content;
        }
        $container.html('<img src="' + DOKU_BASE + 'lib/images/loading.gif" alt="..." class="load" />');
    },

    window_resize: function () {
        dw_mediamanager.resize();

        dw_mediamanager.opacity_slider();
        dw_mediamanager.portions_slider();
    },

    $resizables: function () {
        return jQuery('#mediamanager__page').find('div.namespaces, div.filelist');
    },

    /**
     * Updates mediamanager layout
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     */
    update_resizable: function () {
        var $resizables = dw_mediamanager.$resizables();

        $resizables.resizable({
            handles: (jQuery('html[dir=rtl]').length ? 'w' : 'e'),
            resize: function (event, ui) {
                var $page = jQuery('#mediamanager__page');
                var widthFull = $page.width();
                var widthResizables = 0;
                $resizables.each(function () {
                    widthResizables += jQuery(this).width();
                });
                var $filePanel = $page.find('div.panel.file');

                // set max width of resizable column
                var widthOtherResizable = widthResizables - jQuery(this).width();
                var minWidthNonResizable = parseFloat($filePanel.css("min-width"));
                var maxWidth = widthFull - (widthOtherResizable + minWidthNonResizable) - 1;
                $resizables.resizable("option", "maxWidth", maxWidth);

                // width of file panel in % = 100% - width of resizables in %
                // this calculates with 99.9 and not 100 to overcome rounding errors
                var relWidthNonResizable = 99.9 - (100 * widthResizables / widthFull);
                // set width of file panel
                $filePanel.width(relWidthNonResizable + '%');

                dw_mediamanager.resize();

                dw_mediamanager.opacity_slider();
                dw_mediamanager.portions_slider();
            }
        });

        dw_mediamanager.resize();
    },

    resize: function () {
        var $contents = jQuery('#mediamanager__page').find('div.panelContent'),
            height = jQuery(window).height() - jQuery(document.body).height() +
                Math.max.apply(null, jQuery.map($contents, function (v) {
                    return jQuery(v).height();
                }));

        // If the screen is too small, don’t try to resize
        if (height < dw_mediamanager.minHeights[dw_mediamanager.view_opts.list]) {
            $contents.add(dw_mediamanager.$resizables()).height('auto');
        } else {
            $contents.height(height);
            dw_mediamanager.$resizables().each(function () {
                var $this = jQuery(this);
                $this.height(height + $this.find('div.panelContent').offset().top - $this.offset().top);
            });
        }
    },

    /**
     * Prints 'select' for image difference representation type
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     */
    image_diff: function () {
        if (jQuery('#mediamanager__difftype').length) return;

        var $form = jQuery('#mediamanager__form_diffview');
        if (!$form.length) return;

        var $label = jQuery(document.createElement('label'));
        $label.append('<span>' + LANG.media_diff + '</span> ');
        var $select = jQuery(document.createElement('select'))
            .attr('id', 'mediamanager__difftype')
            .attr('name', 'difftype')
            .change(dw_mediamanager.change_diff_type);
        $select.append(new Option(LANG.media_diff_both, "both"));
        $select.append(new Option(LANG.media_diff_opacity, "opacity"));
        $select.append(new Option(LANG.media_diff_portions, "portions"));
        $label.append($select);
        $form.append($label);

        // for IE
        var select = document.getElementById('mediamanager__difftype');
        select.options[0].text = LANG.media_diff_both;
        select.options[1].text = LANG.media_diff_opacity;
        select.options[2].text = LANG.media_diff_portions;
    },

    /**
     * Handles selection of image difference representation type
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     */
    change_diff_type: function () {
        var $select = jQuery('#mediamanager__difftype');
        var $content = jQuery('#mediamanager__diff');

        var params = dw_mediamanager.form_params($select.closest('form')) + '&call=mediadiff';
        jQuery.post(
            DOKU_BASE + 'lib/exe/ajax.php',
            params,
            function (data) {
                $content.html(data);
                dw_mediamanager.portions_slider();
                dw_mediamanager.opacity_slider();
            },
            'html'
        );
    },

    /**
     * Sets options for opacity diff slider
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     */
    opacity_slider: function () {
        var $diff = jQuery("#mediamanager__diff");
        var $slider = $diff.find("div.slider");
        if (!$slider.length) return;

        var $image = $diff.find('div.imageDiff.opacity div.image1 img');
        if (!$image.length) return;
        $slider.width($image.width() - 20);

        $slider.slider();
        $slider.slider("option", "min", 0);
        $slider.slider("option", "max", 0.999);
        $slider.slider("option", "step", 0.001);
        $slider.slider("option", "value", 0.5);
        $slider.on("slide", function (event, ui) {
            jQuery('#mediamanager__diff').find('div.imageDiff.opacity div.image2 img').css({opacity: $slider.slider("option", "value")});
        });
    },

    /**
     * Sets options for red line diff slider
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     */
    portions_slider: function () {
        var $diff = jQuery("#mediamanager__diff");
        if (!$diff.length) return;

        var $image1 = $diff.find('div.imageDiff.portions div.image1 img');
        var $image2 = $diff.find('div.imageDiff.portions div.image2 img');
        if (!$image1.length || !$image2.length) return;

        $diff.width('100%');
        $image2.parent().width('97%');
        $image1.width('100%');
        $image2.width('100%');

        if ($image1.width() < $diff.width()) {
            $diff.width($image1.width());
        }

        $image2.parent().width('50%');
        $image2.width($image1.width());
        $image1.width($image1.width());

        var $slider = $diff.find("div.slider");
        if (!$slider.length) return;
        $slider.width($image1.width() - 20);

        $slider.slider();
        $slider.slider("option", "min", 0);
        $slider.slider("option", "max", 97);
        $slider.slider("option", "step", 1);
        $slider.slider("option", "value", 50);
        $slider.on("slide", function (event, ui) {
            jQuery('#mediamanager__diff').find('div.imageDiff.portions div.image2').css({width: $slider.slider("option", "value") + '%'});
        });
    },

    /**
     * Parse a URI query string to an associative array
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     */
    params_toarray: function (str) {
        var vars = [], hash;
        var hashes = str.split('&');
        for (var i = 0; i < hashes.length; i++) {
            hash = hashes[i].split('=');
            vars[decodeURIComponent(hash[0])] = decodeURIComponent(hash[1]);
        }
        return vars;
    },

    init_ajax_uploader: function () {
        if (!jQuery('#mediamanager__uploader').length) return;
        if (jQuery('.qq-upload-list').length) return;

        var params = dw_mediamanager.form_params(jQuery('#dw__upload')) + '&call=mediaupload';
        params = dw_mediamanager.params_toarray(params);

        var uploader = new qq.FileUploaderExtended({
            element: document.getElementById('mediamanager__uploader'),
            action: DOKU_BASE + 'lib/exe/ajax.php',
            params: params
        });
    },

    prepare_content: function ($content) {
        // hide syntax example
        $content.find('div.example:visible').hide();
    },

    /**
     * shows the popup for a image link
     */
    select: function (event) {
        var $link, id, dot, ext;

        event.preventDefault();

        $link = jQuery(this);
        id = $link.attr('id').substr(2);

        if (!opener) {
            // if we don't run in popup display example
            // the id's are a bit wierd and jQuery('#ex_wiki_dokuwiki-128.png')
            // will not be found by Sizzle (the CSS Selector Engine
            // used by jQuery), hence the document.getElementById() call
            jQuery(document.getElementById('ex_' + id.replace(/:/g, '_').replace(/^_/, ''))).dw_toggle();
            return;
        }

        dw_mediamanager.ext = false;
        dot = id.lastIndexOf(".");

        if (-1 === dot) {
            dw_mediamanager.insert(id);
            return;
        }

        ext = id.substr(dot);

        if ({'.jpg': 1, '.jpeg': 1, '.png': 1, '.gif': 1, '.swf': 1}[ext] !== 1) {
            dw_mediamanager.insert(id);
            return;
        }

        // remove old callback from the insert button and set the new one.
        var $sendbtn = jQuery('#media__sendbtn');
        $sendbtn.off().click(bind(dw_mediamanager.insert, id));

        dw_mediamanager.unforbid('ext');
        if (ext === '.swf') {
            dw_mediamanager.ext = 'swf';
            dw_mediamanager.forbid('ext', {
                link: ['1', '2'],
                size: ['4']
            });
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

        $sendbtn.focus();
    },

    /**
     * Deletion confirmation dialog to the delete buttons.
     *
     * @author Michael Klier <chi@chimeric.de>
     * @author Pierre Spring <pierre.spring@caillou.ch>
     */
    confirmattach: function (e) {
        if (!confirm(LANG.del_confirm + "\n" + jQuery(this).attr('title'))) {
            e.preventDefault();
        }
    },

    /**
     * Creates checkboxes for additional options
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Pierre Spring <pierre.spring@caillou.ch>
     */
    attachoptions: function () {
        var $obj, opts;

        $obj = jQuery('#media__opts');
        if ($obj.length === 0) {
            return;
        }

        opts = [];
        // keep open
        if (opener) {
            opts.push(['keepopen', 'keepopen']);
        }
        opts.push(['hide', 'hidedetails']);

        jQuery.each(opts,
            function (_, opt) {
                var $box, $lbl;
                $box = jQuery(document.createElement('input'))
                    .attr('type', 'checkbox')
                    .attr('id', 'media__' + opt[0])
                    .click(bind(dw_mediamanager.toggleOption, opt[0]));

                if (DokuCookie.getValue(opt[0])) {
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

    /**
     * Sets the visibility of the image details accordingly to the
     * chosen hide state
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    updatehide: function () {
        jQuery('#media__content').find('div.detail').dw_toggle(!dw_mediamanager.hide);
    },

    /**
     * set media insertion option
     *
     * @author Dominik Eckelmann <eckelmann@cosmocode.de>
     */
    setOpt: function (opt, e) {
        var val, i;
        if (typeof e !== 'undefined') {
            val = this.id.substring(this.id.length - 1);
        } else {
            val = dw_mediamanager.getOpt(opt);
        }

        if (val === false) {
            DokuCookie.setValue(opt, '');
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

/**
 * Default implementation for the media manager's select action
 *
 * Can be overriden with the onselect URL parameter. Is called on the opener context
 *
 * @param {string} edid
 * @param {string} mediaid
 * @param {string} opts
 * @param {string} align [none, left, center, right]
 */
function dw_mediamanager_item_select(edid, mediaid, opts, align) {
    var alignleft = '';
    var alignright = '';
    if (align !== '1') {
        alignleft = align === '2' ? '' : ' ';
        alignright = align === '4' ? '' : ' ';
    }

    insertTags(edid, '{{' + alignleft + mediaid + opts + alignright + '|', '}}', '');
}

jQuery(dw_mediamanager.init);
