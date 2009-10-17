
// used to identify pickers
var pickercounter=0;

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
    var edit = $(edid);
    if(!edit) return;
    if(edit.readonly) return;

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
            if(window[actionFunc](btn, tb[i], edid)){
                toolbar.appendChild(btn);
            }
            continue;
        }

        alert('unknown toolbar type: '+tb[i]['type']+'  '+actionFunc);
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
    pickerClose();
    return false;
}

/**
 * Button action for format buttons
 *
 * This works exactly as tb_format() except that, if multiple lines
 * are selected, each line will be formatted seperately
 *
 * @param  DOMElement btn   Button element to add the action to
 * @param  array      props Associative array of button properties
 * @param  string     edid  ID of the editor textarea
 * @author Gabriel Birke <birke@d-scribe.de>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tb_formatln(btn, props, edid) {
    var sample = props['title'];
    if(props['sample']){
        sample = props['sample'];
    }
    sample = fixtxt(sample);

    props['open']  = fixtxt(props['open']);
    props['close'] = fixtxt(props['close']);

    // is something selected?
    var opts;
    var selection = getSelection($(edid));
    if(selection.getLength()){
        sample = selection.getText();
        opts = {nosel: true};
    }else{
        opts = {
            startofs: props['open'].length,
            endofs: props['close'].length
        };
    }

    sample = sample.split("\n").join(props['close']+"\n"+props['open']);
    sample = props['open']+sample+props['close'];

    pasteText(selection,sample,opts);

    pickerClose();
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
    pickerClose();
}


/**
 * Add button action for picker buttons and create picker element
 *
 * @param  DOMElement btn   Button element to add the action to
 * @param  array      props Associative array of button properties
 * @param  string     edid  ID of the editor textarea
 * @return boolean    If button should be appended
 * @author Gabriel Birke <birke@d-scribe.de>
 */
function addBtnActionPicker(btn, props, edid) {
    var pickerid = 'picker'+(pickercounter++);
    createPicker(pickerid, props, edid);
    addEvent(btn,'click',function(){
        pickerToggle(pickerid,btn);
        return false;
    });
    return true;
}

/**
 * Add button action for the link wizard button
 *
 * @param  DOMElement btn   Button element to add the action to
 * @param  array      props Associative array of button properties
 * @param  string     edid  ID of the editor textarea
 * @return boolean    If button should be appended
 * @author Andreas Gohr <gohr@cosmocode.de>
 */
function addBtnActionLinkwiz(btn, props, edid) {
    linkwiz.init($(edid));
    addEvent(btn,'click',function(){
        linkwiz.toggle();
        return false;
    });
    return true;
}

/**
 * Show/Hide a previosly created picker window
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function pickerToggle(pickerid,btn){
    var picker = $(pickerid);
    if(picker.style.left == '-10000px'){
        var x = findPosX(btn);
        var y = findPosY(btn);
        picker.style.left = (x+3)+'px';
        picker.style.top = (y+btn.offsetHeight+3)+'px';
    }else{
        picker.style.left = '-10000px';
    }
}

/**
 * Close all open pickers
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function pickerClose(){
    var pobjs = getElementsByClass('picker');
    for(var i=0; i<pobjs.length; i++){
        pobjs[i].style.left = '-10000px';
    }
}


/**
 * Replaces \n with linebreaks
 */
function fixtxt(str){
    return str.replace(/\\n/g,"\n");
}

