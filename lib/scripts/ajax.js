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
