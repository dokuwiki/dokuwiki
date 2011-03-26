/**
 * AJAX functions for the pagename quicksearch
 *
 * @license  GPL2 (http://www.gnu.org/licenses/gpl.html)
 * @author   Andreas Gohr <andi@splitbrain.org>
 * @author   Adrian Lang <lang@cosmocode.de>
 * @author   Michal Rezler <m.rezler@centrum.cz> 
 */
 
 (function ($) {
    var init, clear_results, onCompletion;
     
    var ajax_quicksearch = {
        inObj: null,
        outObj: null,
        delay: null,
    };

    init = function(inID, outID) {

        ajax_quicksearch.inObj  = $(inID);
        ajax_quicksearch.outObj = $(outID);

        // objects found?
        if (ajax_quicksearch.inObj  === null) return;
        if (ajax_quicksearch.outObj === null) return;

        // attach eventhandler to search field
        ajax_quicksearch.delay = new Delay(function () {
            ajax_quicksearch.clear_results();
            var value = ajax_quicksearch.inObj.value;
            if(value === ''){ return; }
            $.post(
                DOKU_BASE + 'lib/exe/ajax.php',
                {
                    call: 'qsearch',
                    q: encodeURI(value)
                },
                function (data) {
                    onCompletion(data);
                },
                'html'
            );
        });

        $(ajax_quicksearch.inObj).keyup(
            function() {
                ajax_quicksearch.clear_results();
                ajax_quicksearch.delay.start();
            }
        );

        // attach eventhandler to output field
        $(ajax_quicksearch.outObj).click(
            function() {
                ajax_quicksearch.outObj.hide();
            }
        );
        
    };

    clear_results = function(){
        ajax_quicksearch.outObj.hide();
        ajax_quicksearch.outObj.text('');
    };

    onCompletion = function(data) {
        if (data === '') { return; }

        var outObj = ajax_quicksearch.outObj;

        outObj.text(data);
        outObj.show();
        outObj.css('white-space', 'nowrap');

        // shorten namespaces if too long
        var width = outObj.clientWidth;
        var links = $('ajax_quicksearch outObj a');
        
        for (var i=0; i<links.length; i++) {
            var content = links[i].text();
            
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
    };
    
    $(function () {
        init('qsearch__in','qsearch__out');
    });

}(jQuery));