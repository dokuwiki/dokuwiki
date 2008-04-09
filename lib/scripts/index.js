/**
 * Javascript for index view
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

index = {

     /**
     * Delay in ms before showing the throbber.
     * Used to skip the throbber for fast AJAX calls.
     */
    throbber_delay: 500,

    /**
     * Attach event handlers to all "folders" below the given element
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    treeattach: function(obj){
        if(!obj) return;

        var items = getElementsByClass('idx_dir',obj,'a');
        for(var i=0; i<items.length; i++){
            var elem = items[i];

            // attach action to make the link clickable by AJAX
            addEvent(elem,'click',function(e){ return index.toggle(e,this); });
        }
    },

    /**
     * Open or close a subtree using AJAX
     * The contents of subtrees are "cached" untill the page is reloaded.
     * A "loading" indicator is shown only when the AJAX call is slow.
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Ben Coburn <btcoburn@silicodon.net>
     */
    toggle: function(e,clicky){
        var listitem = clicky.parentNode.parentNode;

        // if already open, close by removing the sublist
        var sublists = listitem.getElementsByTagName('ul');
        if(sublists.length && listitem.className=='open'){
            sublists[0].style.display='none';
            listitem.className='closed';
            e.preventDefault();
            return false;
        }

        // just show if already loaded
        if(sublists.length && listitem.className=='closed'){
            sublists[0].style.display='';
            listitem.className='open';
            e.preventDefault();
            return false;
        }

        // prepare an AJAX call to fetch the subtree
        var ajax = new sack(DOKU_BASE + 'lib/exe/ajax.php');
        ajax.AjaxFailedAlert = '';
        ajax.encodeURIString = false;
        if(ajax.failed) return true;

        //prepare the new ul
        var ul = document.createElement('ul');
        ul.className = 'idx';
        timeout = window.setTimeout(function(){
            // show the throbber as needed
            ul.innerHTML = '<li><img src="'+DOKU_BASE+'lib/images/throbber.gif" alt="loading..." title="loading..." /></li>';
            listitem.appendChild(ul);
            listitem.className='open';
        }, this.throbber_delay);
        ajax.elementObj = ul;
        ajax.afterCompletion = function(){
            window.clearTimeout(timeout);
            index.treeattach(ul);
            if (listitem.className!='open') {
                listitem.appendChild(ul);
                listitem.className='open';
            }
        };
        ajax.runAJAX(clicky.search.substr(1)+'&call=index');
        e.preventDefault();
        return false;
    }
};


addInitEvent(function(){
    index.treeattach($('index__tree'));
});
