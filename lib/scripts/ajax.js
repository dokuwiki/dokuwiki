/**
 * AJAX functions for the pagename quicksearch
 *
 * @license  GPL2 (http://www.gnu.org/licenses/gpl.html)
 * @author   Andreas Gohr <andi@splitbrain.org>
 * @author   Adrian Lang <lang@cosmocode.de>
 */
addInitEvent(function () {

    var inID  = 'qsearch__in';
    var outID = 'qsearch__out';

    var inObj  = document.getElementById(inID);
    var outObj = document.getElementById(outID);

    // objects found?
    if (inObj === null){ return; }
    if (outObj === null){ return; }

    function clear_results(){
        outObj.style.display = 'none';
        outObj.innerHTML = '';
    }

    var sack_obj = new sack(DOKU_BASE + 'lib/exe/ajax.php');
    sack_obj.AjaxFailedAlert = '';
    sack_obj.encodeURIString = false;
    sack_obj.onCompletion = function () {
        var data = sack_obj.response;
        if (data === '') { return; }

        outObj.innerHTML = data;
        outObj.style.display = 'block';
        outObj.style['white-space'] = 'nowrap';

        // shorten namespaces if too long
        var width = outObj.clientWidth;
        var links = outObj.getElementsByTagName('a');
        for(var i=0; i<links.length; i++){
            // maximum allowed width:
            var max = width - links[i].offsetLeft; //FIXME use offsetRight for RTL!
            if(links[i].offsetWidth < max) continue;

            var nsL = links[i].innerText.indexOf('(');
            var nsR = links[i].innerText.indexOf(')');
            var eli = 0;
            var runaway = 0;

            while( (nsR - nsL > 3) && links[i].offsetWidth > max ){
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

    };

    // attach eventhandler to search field
    var delay = new Delay(function () {
        clear_results();
        var value = inObj.value;
        if(value === ''){ return; }
        sack_obj.runAJAX('call=qsearch&q=' + encodeURI(value));
    });

    addEvent(inObj, 'keyup', function () {clear_results(); delay.start(); });

    // attach eventhandler to output field
    addEvent(outObj, 'click', function () {outObj.style.display = 'none'; });
});
