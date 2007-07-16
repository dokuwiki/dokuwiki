/**
 * Java Script for index view
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

index = {

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
            addEvent(elem,'click',function(event){ return index.toggle(event,this); });
        }
    },

    /**
     * Open or close a subtree using AJAX
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    toggle: function(event,clicky){
        var listitem = clicky.parentNode.parentNode;

        // if already open, close by removing the sublist
        var sublists = listitem.getElementsByTagName('ul');
        if(sublists.length){
            listitem.removeChild(sublists[0]);
            listitem.className='closed';
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
        ul.innerHTML = '<li><img src="'+DOKU_BASE+'lib/images/throbber.gif" alt="loading..." title="loading..." /></li>';
        listitem.appendChild(ul);
        ajax.elementObj = ul;
        ajax.afterCompletion = function(){ index.treeattach(ul); };
        ajax.runAJAX(clicky.search.substr(1)+'&call=index');
        listitem.className='open';
        return false;

    },


};


addInitEvent(function(){
    index.treeattach($('index__tree'));
});
