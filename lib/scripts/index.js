/**
 * Javascript for index view
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */

var index = {

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

            // get the listitem the elem belongs to
            var listitem = elem.parentNode;
            while (listitem.tagName != 'LI') {
              listitem = listitem.parentNode;
            }
            //when there are uls under this listitem mark this listitem as opened
            if (listitem.getElementsByTagName('ul').length) {
              listitem.open = true;
            }
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

        listitem.open = !listitem.open;
        // listitem.open represents now the action to be done

        // if already open, close by removing the sublist
        var sublists = listitem.getElementsByTagName('ul');
        if(!listitem.open){
            if (sublists.length) {
              sublists[0].style.display='none';
            }
            listitem.className='closed';
            e.preventDefault();
            return false;
        }

        // just show if already loaded
        if(sublists.length && listitem.open){
            sublists[0].style.display='';
            listitem.className='open';
            e.preventDefault();
            return false;
        }

        //prepare the new ul
        var ul = jQuery('<ul class="idx"/>');

        var timeout = window.setTimeout(function(){
            // show the throbber as needed
            if (listitem.open) {
              ul.html('<li><img src="'+DOKU_BASE+'lib/images/throbber.gif" alt="loading..." title="loading..." /></li>')
                .appendTo(listitem);
              listitem.className='open';
            }
        }, this.throbber_delay);

        ul.load(
            DOKU_BASE + 'lib/exe/ajax.php',
            clicky.search.substr(1)+'&call=index',
            function () {
                window.clearTimeout(timeout);
                index.treeattach(this);
                if (listitem.className!='open') {
                  if (!listitem.open) {
                    this.style.display='none';
                  }
                  listitem.appendChild(this);
                  if (listitem.open) {
                    listitem.className='open';
                  }
                }
            }
        );
        e.preventDefault();
        return;

    }
};


addInitEvent(function(){
    index.treeattach($('index__tree'));
});
