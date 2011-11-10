/**
 * AJAX functions for the pagename quicksearch
 *
 * @license  GPL2 (http://www.gnu.org/licenses/gpl.html)
 * @author   Andreas Gohr <andi@splitbrain.org>
 * @author   Adrian Lang <lang@cosmocode.de>
 */
var ajax_quicksearch = {

    inObj:   null,
    outObj:  null,
    sackObj: null,
    delay:   null,

    init: function(inID, outID) {

        this.inObj  = $(inID);
        this.outObj = $(outID);

        // objects found?
        if (this.inObj  === null) return;
        if (this.outObj === null) return;

        // prepare AJAX
        this.sackObj = new sack(DOKU_BASE + 'lib/exe/ajax.php');
        this.sackObj.AjaxFailedAlert = '';
        this.sackObj.encodeURIString = false;
        this.sackObj.onCompletion = ajax_quicksearch.onCompletion;

        // attach eventhandler to search field
        this.delay = new Delay(function () {
            ajax_quicksearch.clear_results();
            var value = ajax_quicksearch.inObj.value;
            if(value === ''){ return; }
            ajax_quicksearch.sackObj.runAJAX('call=qsearch&q=' + encodeURI(value));
        });

        addEvent(this.inObj, 'keyup', function () {
            ajax_quicksearch.clear_results();
            ajax_quicksearch.delay.start();
        });

        // attach eventhandler to output field
        addEvent(this.outObj, 'click', function () {
            ajax_quicksearch.outObj.style.display = 'none';
        });
    },

    clear_results: function(){
        ajax_quicksearch.outObj.style.display = 'none';
        ajax_quicksearch.outObj.innerHTML = '';
    },

    onCompletion: function() {
        var data = this.response; // 'this' is sack context
        if (data === '') { return; }

        var outObj = ajax_quicksearch.outObj;

        outObj.innerHTML = data;
        outObj.style.display = 'block';
        outObj.style['white-space'] = 'nowrap';

        // shorten namespaces if too long
        var width = outObj.clientWidth;
        var links = outObj.getElementsByTagName('a');
        for(var i=0; i<links.length; i++){
            // maximum allowed width:
            var max = width - links[i].offsetLeft;
            var isRTL = (document.documentElement.dir == 'rtl');

            if(!isRTL && links[i].offsetWidth < max) continue;
            if(isRTL && links[i].offsetLeft > 0) continue;

            var nsL = links[i].innerText.indexOf('(');
            var nsR = links[i].innerText.indexOf(')');
            var eli = 0;
            var runaway = 0;

            while( (nsR - nsL > 3) &&
                    (
                       (!isRTL && links[i].offsetWidth > max) ||
                       (isRTL && links[i].offsetLeft < 0)
                    )
                 ){
                if(runaway++ > 500) return; // just in case something went wrong

                if(eli){
                    // elipsis already inserted
                    if( (eli - nsL) > (nsR - eli) ){
                        // cut left
                        links[i].innerText = links[i].innerText.substring(0,eli-2)+
                                             links[i].innerText.substring(eli);
                    }else{
                        // cut right
                        links[i].innerText = links[i].innerText.substring(0,eli+1)+
                                             links[i].innerText.substring(eli+2);
                    }
                }else{
                    // replace middle with ellipsis
                    var mid = Math.floor( nsL + ((nsR-nsL)/2) );
                    links[i].innerText = links[i].innerText.substring(0,mid)+'…'+
                                         links[i].innerText.substring(mid+1);
                }
                eli = links[i].innerText.indexOf('…');
                nsL = links[i].innerText.indexOf('(');
                nsR = links[i].innerText.indexOf(')');
            }
        }
    }

};


addInitEvent(function(){
    ajax_quicksearch.init('qsearch__in','qsearch__out');
});

