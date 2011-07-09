/**
 * ACL Manager AJAX enhancements
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
acl = {
    /**
     * Initialize the object and attach the event handlers
     */
    init: function(){
        if(!jQuery('#acl_manager').length) return; //FIXME only one underscore!!

        jQuery('#acl__user select').change(acl.userselhandler);
        jQuery('#acl__tree').click(acl.treehandler);
        jQuery('#acl__user input[type=submit]').click(acl.loadinfo);
    },

    /**
     * Handle user dropdown
     *
     * Hides or shows the user/group entry box depending on what was selected in the
     * dropdown element
     */
    userselhandler: function(e){
        // make entry field visible/invisible
        if(this.value == '__g__' || this.value == '__u__'){
            jQuery('#acl__user input').show();
        }else{
            jQuery('#acl__user input').hide();
        }
        acl.loadinfo();
    },

    /**
     * Load the current permission info and edit form
     */
    loadinfo: function(){
        var frm = jQuery('#acl__detail form')[0];

        jQuery('#acl__info').load(
            DOKU_BASE + 'lib/plugins/acl/ajax.php',
            {
                'ns':       frm.elements['ns'].value,
                'id':       frm.elements['id'].value,
                'acl_t':    frm.elements['acl_t'].value,
                'acl_w':    frm.elements['acl_w'].value,
                'sectok':   frm.elements['sectok'].value,
                'ajax':     'info',
            }
        );
        return false;
    },

    /**
     * parse URL attributes into a associative array
     *
     * @todo put into global script lib?
     */
    parseatt: function(str){
        if(str[0] == '?') str = str.substr(1);
        var attributes = {};
        var all = str.split('&');
        for(var i=0; i<all.length; i++){
            var att = all[i].split('=');
            attributes[att[0]] = decodeURIComponent(att[1]);
        }
        return attributes;
    },

    /**
     * Open or close a subtree using AJAX
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @param  DOMElement clicky - the plus/minus icon in front of a namespace
     */
    treetoggle: function(clicky){
        var listitem = jQuery(clicky).parent().parent();

        // if already open, close by removing the sublist
        var sublists = listitem.find('ul');
        if(sublists.length){
            listitem.remove('ul');
            clicky.src = DOKU_BASE+'lib/images/plus.gif';
            clicky.alt = '+';
            return false;
        }

        // prepare new ul to load into it via ajax
        var ul = document.createElement('ul');
        listitem[0].appendChild(ul);

        // get the enclosed link and the edit form
        var link = listitem.find('a')[0];
        var frm  = jQuery('#acl__detail form')[0];

        // prepare ajax data
        var data           = acl.parseatt(link.search);
        data['ajax']       = 'tree';
        data['current_ns'] = frm.elements['ns'].value;
        data['current_id'] = frm.elements['id'].value;

        // run ajax
        jQuery(ul).load(DOKU_BASE + 'lib/plugins/acl/ajax.php', data);

        clicky.src = DOKU_BASE+'lib/images/minus.gif';
        return false;
    },

    /**
     * Handles all clicks in the tree, dispatching the right action based on the
     * clicked element
     *
     * @param Event e The event object that caused the execution
     */
    treehandler: function(e){
        if(e.target.src){ // is it an image?
            acl.treetoggle(e.target);
        } else if(e.target.href){ // is it a link?
            // remove highlighting
            jQuery('#acl__tree a.cur').removeClass('cur');

            var link = jQuery(e.target);

            // add new highlighting
            link.addClass('cur');

            // set new page to detail form
            var frm = jQuery('#acl__detail form')[0];
            if(link.hasClass('wikilink1')){
                jQuery('#acl__detail form input[name=ns]').val('');
                jQuery('#acl__detail form input[name=id]').val(acl.parseatt(link[0].search)['id']);
            }else if(link.hasClass('idx_dir')){
                jQuery('#acl__detail form input[name=ns]').val(acl.parseatt(link[0].search)['ns']);
                jQuery('#acl__detail form input[name=id]').val('');
            }
            acl.loadinfo();
        }

        e.stopPropagation();
        e.preventDefault();
        return false;
    }

};

jQuery(acl.init);
