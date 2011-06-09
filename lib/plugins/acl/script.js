/**
 * ACL Manager AJAX enhancements
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
acl = {
    /**
     * Initialize the object and attach the event handlers
     *
     * @todo move to jQuery
     */
    init: function(){
        this.ctl = $('acl_manager');
        if(!this.ctl) return;

        var sel = $('acl__user').getElementsByTagName('select')[0];

        addEvent(sel,'change',acl.userselhandler);
        addEvent($('acl__tree'),'click',acl.treehandler);
        addEvent($('acl__user').getElementsByTagName('input')[1],'click',acl.loadinfo);
    },

    /**
     * Handle user dropdown
     *
     * Hides or shows the user/group entry box depending on wht was selected in the
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
     * htmlspecialchars equivalent
     *
     * @todo put in gloabl scripts lib?
     */
    hsc: function(str) {
        str = str.replace(/&/g,"&amp;");
        str = str.replace(/\"/g,"&quot;");
        str = str.replace(/\'/g,"&#039;");
        str = str.replace(/</g,"&lt;");
        str = str.replace(/>/g,"&gt;");
        return str;
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
     * @todo move to jQuery
     */
    treehandler: function(e){
        if(e.target.src){ // is it an image?
            acl.treetoggle(e.target);
        } else if(e.target.href){ // is it a link?
            // remove highlighting
            var obj = getElementsByClass('cur',$('acl__tree'),'a');
            for(var i=0; i<obj.length; i++){
                obj[i].className = obj[i].className.replace(/ cur/,'');
            }

            // add new highlighting
            e.target.className += ' cur';

            // set new page to detail form
            var frm = $('acl__detail').getElementsByTagName('form')[0];
            if(e.target.className.search(/wikilink1/) > -1){
                frm.elements['ns'].value = '';
                frm.elements['id'].value = acl.hsc(acl.parseatt(e.target.search)['id']);
            }else if(e.target.className.search(/idx_dir/) > -1){
                frm.elements['ns'].value = acl.hsc(acl.parseatt(e.target.search)['ns']);
                frm.elements['id'].value = '';
            }

            acl.loadinfo();
        }

        e.stopPropagation();
        e.preventDefault();
        return false;
    }

};

addInitEvent(acl.init);
