/**
 * AJAX functions for the pagename quicksearch
 *
 * @license  GPL2 (http://www.gnu.org/licenses/gpl.html)
 * @author   Andreas Gohr <andi@splitbrain.org>
 * @author   Adrian Lang <lang@cosmocode.de>
 * @author   Michal Rezler <m.rezler@centrum.cz>
 */

var ajax_quicksearch = {

    inObj: null, // jquery object
    outObj: null, // jquery object
    delay: null,

    /**
     * initialize the quick search
     *
     * Attaches the event handlers
     *
     * @param input element (JQuery selector/DOM obj)
     * @param output element (JQuery selector/DOM obj)
     */
    init: function(input, output) {
        ajax_quicksearch.inObj  = jQuery(input);
        ajax_quicksearch.outObj = jQuery(output);

        // objects found?
        if (ajax_quicksearch.inObj  === []) return;
        if (ajax_quicksearch.outObj === []) return;

        // attach eventhandler to search field
        ajax_quicksearch.delay = new Delay(function () {
            ajax_quicksearch.clear_results();
            var value = ajax_quicksearch.inObj.val();
            if(value === ''){ return; }
            jQuery.post(
                DOKU_BASE + 'lib/exe/ajax.php',
                {
                    call: 'qsearch',
                    q: encodeURI(value)
                },
                ajax_quicksearch.onCompletion,
                'html'
            );
        });

        ajax_quicksearch.inObj.keyup(
            function() {
                ajax_quicksearch.clear_results();
                ajax_quicksearch.delay.start();
            }
        );

        // attach eventhandler to output field
        ajax_quicksearch.outObj.click(
             ajax_quicksearch.outObj.hide
        );

    },

    /**
     * Empty and hide the output div
     */
    clear_results: function(){
        ajax_quicksearch.outObj.hide();
        ajax_quicksearch.outObj.text('');
    },

    /**
     * Callback. Reformat and display the results.
     *
     * Namespaces are shortened here to keep the results from overflowing
     * or wrapping
     *
     * @param data The result HTML
     */
    onCompletion: function(data) {
        if (data === '') { return; }

        var outObj = ajax_quicksearch.outObj;

        outObj.html(data);
        outObj.show();
        outObj.css('white-space', 'nowrap');

        // shorten namespaces if too long
        var width = outObj.clientWidth;
        var links = outObj.find('a');

        for (var i=0; i<links.length; i++) {
            var content = links[i].text;

            // maximum allowed width:
            var max = width - links[i].offsetLeft;
            var isRTL = (document.documentElement.dir == 'rtl');

            if(!isRTL && links[i].offsetWidth < max) continue;
            if(isRTL && links[i].offsetLeft > 0) continue;

            var nsL = content.indexOf('(');
            var nsR = content.indexOf(')');
            var eli = 0;
            var runaway = 0;

            while((nsR - nsL > 3) &&
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
                        content = content.substring(0,eli-2) + content.substring(eli);
                    }else{
                        // cut right
                        content = content.substring(0,eli+1) + content.substring(eli+2);
                    }
                }else{
                    // replace middle with ellipsis
                    var mid = Math.floor( nsL + ((nsR-nsL)/2) );
                    content = content.substring(0,mid)+'…' + content.substring(mid+1);
                }

                eli = content.indexOf('…');
                nsL = content.indexOf('(');
                nsR = content.indexOf(')');
            }
        }
    }
};

jQuery(function () {
    ajax_quicksearch.init('#qsearch__in','#qsearch__out');
});

