/**
 * Create a toolbar
 *
 * @param  string tbid ID of the element where to insert the toolbar
 * @param  string edid ID of the editor textarea
 * @param  array  tb   Associative array defining the buttons
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function initToolbar(tbid,edid,tb){
    var toolbar = $(tbid);
    if(!toolbar) return;

    //empty the toolbar area:
    toolbar.innerHTML='';

    var cnt = tb.length;
    for(var i=0; i<cnt; i++){
        var actionFunc;

        // create new button
        var btn = createToolButton(tb[i]['icon'],
                                   tb[i]['title'],
                                   tb[i]['key']);


        // type is a tb function -> assign it as onclick
        actionFunc = 'tb_'+tb[i]['type'];
        if( isFunction(window[actionFunc]) ){
            addEvent(btn,'click', function(func,btn, props, edid){
                return function(){
                    window[func](btn, props, edid);
                    return false;
                }
            }(actionFunc,btn,tb[i],edid) );
            //above fixes the scope problem as descried at http://www.mennovanslooten.nl/blog/post/62
            toolbar.appendChild(btn);
            continue;
        }

        // type is a init function -> execute it
        actionFunc = 'addBtnAction'+tb[i]['type'].charAt(0).toUpperCase()+tb[i]['type'].substring(1);
        if( isFunction(window[actionFunc]) ){
            if(window[actionFunc](btn, tb[i], edid, i)){
                toolbar.appendChild(btn);
            }
            continue;
        }

        console.log('unknown toolbar type: '+tb[i]['type']+'  '+actionFunc); //FIXME make alert
    } // end for

}

/**
 * Button action for format buttons
 *
 * @param  DOMElement btn   Button element to add the action to
 * @param  array      props Associative array of button properties
 * @param  string     edid  ID of the editor textarea
 * @author Gabriel Birke <birke@d-scribe.de>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tb_format(btn, props, edid) {
    var sample = props['title'];
    if(props['sample']){
        sample = props['sample'];
    }
    insertTags(edid,
               fixtxt(props['open']),
               fixtxt(props['close']),
               fixtxt(sample));
    return false;
}

/**
 * Button action for insert buttons
 *
 * @param  DOMElement btn   Button element to add the action to
 * @param  array      props Associative array of button properties
 * @param  string     edid  ID of the editor textarea
 * @author Gabriel Birke <birke@d-scribe.de>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tb_insert(btn, props, edid) {
    insertAtCarret(edid,fixtxt(props['insert']));
}






/**
 * Replaces \n with linebreaks
 */
function fixtxt(str){
    return str.replace(/\\n/g,"\n");
}

