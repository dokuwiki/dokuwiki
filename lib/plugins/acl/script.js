acl = {
    init: function(){
        this.ctl = $('acl_manager');
        if(!this.ctl) return;

        var sel = $('acl__user').getElementsByTagName('select')[0];

        addEvent(sel,'change',acl.userselhandler);
        addEvent($('acl__tree'),'click',acl.treehandler);
        addEvent($('acl__user').getElementsByTagName('input')[1],'click',acl.loadinfo);
        addEvent($('acl__user').getElementsByTagName('input')[1],'keypress',acl.loadinfo);
    },


    /**
     * Handle user dropdown
     */
    userselhandler: function(e){
        // make entry field visible/invisible
        if(this.value == '__g__' || this.value == '__u__'){
            $('acl__user').getElementsByTagName('input')[0].style.display = ''; //acl_w
            $('acl__user').getElementsByTagName('input')[1].style.display = ''; //submit
        }else{
            $('acl__user').getElementsByTagName('input')[0].style.display = 'none';
            $('acl__user').getElementsByTagName('input')[1].style.display = 'none';
        }

        acl.loadinfo();
    },

    /**
     * Load the current permission info and edit form
     *
     * @param frm - Form element with needed data
     */
    loadinfo: function(){
        // get form
        var frm = $('acl__detail').getElementsByTagName('form')[0];

        // prepare an AJAX call
        var ajax = new sack(DOKU_BASE + 'lib/plugins/acl/ajax.php');
        ajax.AjaxFailedAlert = '';
        ajax.encodeURIString = false;
        if(ajax.failed) return true;

        // prepare data
        var data = Array();
        data[0] = ajax.encVar('ns',frm.elements['ns'].value);
        data[1] = ajax.encVar('id',frm.elements['id'].value);
        data[2] = ajax.encVar('acl_t',frm.elements['acl_t'].value);
        data[3] = ajax.encVar('acl_w',frm.elements['acl_w'].value);
        data[4] = ajax.encVar('ajax','info');

        ajax.elementObj = $('acl__info');

        ajax.runAJAX(data.join('&'));
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
     */
    treetoggle: function(clicky){
        var listitem = clicky.parentNode.parentNode;

        // if already open, close by removing the sublist
        var sublists = listitem.getElementsByTagName('ul');
        if(sublists.length){
            listitem.removeChild(sublists[0]);
            clicky.src = DOKU_BASE+'lib/images/plus.gif';
            clicky.alt = '+';
            return false;
        }

        // get the enclosed link (is always the first one)
        var link = listitem.getElementsByTagName('a')[0];

        // prepare an AJAX call to fetch the subtree
        var ajax = new sack(DOKU_BASE + 'lib/plugins/acl/ajax.php');
        ajax.AjaxFailedAlert = '';
        ajax.encodeURIString = false;
        if(ajax.failed) return true;

        //prepare the new ul
        var ul = document.createElement('ul');
        listitem.appendChild(ul);
        ajax.elementObj = ul;
        ajax.runAJAX(link.search.substr(1)+'&ajax=tree');
        clicky.src = DOKU_BASE+'lib/images/minus.gif';
        return false;
    },

    /**
     * Handles all clicks in the tree, dispatching the right action based on the
     * clicked element
     */
    treehandler: function(e){
        if(e.target.src){ // is it an image?
            acl.treetoggle(e.target);
        } else if(e.target.href){ // is it a link?
            // remove highlighting
            var obj = getElementsByClass('cur',$('acl__tree'),'a')[0];
            if(obj) obj.className = obj.className.replace(/ cur/,'');

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
