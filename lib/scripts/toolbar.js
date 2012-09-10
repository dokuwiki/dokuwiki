// used to identify pickers
var pickercounter=0;

/**
 * Create a toolbar
 *
 * @param  string tbid       ID of the element where to insert the toolbar
 * @param  string edid       ID of the editor textarea
 * @param  array  tb         Associative array defining the buttons
 * @param  bool   allowblock Allow buttons creating multiline content
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function initToolbar(tbid,edid,tb, allowblock){
    var $toolbar, $edit;
    if (typeof tbid == 'string') {
        $toolbar = jQuery('#' + tbid);
    } else {
        $toolbar = jQuery(tbid);
    }

    $edit = jQuery('#' + edid);

    if ($toolbar.length == 0 || $edit.length == 0 || $edit.attr('readOnly')) {
        return;
    }

    if (typeof allowblock === 'undefined') {
        allowblock = true;
    }

    //empty the toolbar area:
    $toolbar.html('');

    jQuery.each(tb, function (k, val) {
        if (!tb.hasOwnProperty(k) || (!allowblock && val.block === true)) {
            return;
        }
        var actionFunc, $btn;

        // create new button (jQuery object)
        $btn = jQuery(createToolButton(val.icon, val.title, val.key, val.id,
                                       val['class']));

        // type is a tb function -> assign it as onclick
        actionFunc = 'tb_'+val.type;
        if( jQuery.isFunction(window[actionFunc]) ){
            $btn.bind('click', bind(window[actionFunc],$btn,val,edid) );
            $toolbar.append($btn);
            return;
        }

        // type is a init function -> execute it
        actionFunc = 'addBtnAction'+val.type.charAt(0).toUpperCase()+val.type.substring(1);
        if( jQuery.isFunction(window[actionFunc]) ){
            if(window[actionFunc]($btn, val, edid)){
                $toolbar.append($btn);
            }
            return;
        }

        alert('unknown toolbar type: '+val.type+'  '+actionFunc);
    });
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
    var sample = props.title || props.sample;
    insertTags(edid,
               fixtxt(props.open),
               fixtxt(props.close),
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
    var sample = props.title || props.sample,
        opts,
        selection = getSelection(jQuery('#'+edid)[0]);

    sample = fixtxt(sample);
    props.open  = fixtxt(props.open);
    props.close = fixtxt(props.close);

    // is something selected?
    if(selection.getLength()){
        sample = selection.getText();
        opts = {nosel: true};
    }else{
        opts = {
            startofs: props.open.length,
            endofs: props.close.length
        };
    }

    sample = sample.split("\n").join(props.close+"\n"+props.open);
    sample = props.open+sample+props.close;

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
    insertAtCarret(edid,fixtxt(props.insert));
    pickerClose();
    return false;
}

/**
 * Button action for the media popup
 *
 * @param  DOMElement btn   Button element to add the action to
 * @param  array      props Associative array of button properties
 * @param  string     edid  ID of the editor textarea
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tb_mediapopup(btn, props, edid) {
    window.open(
        DOKU_BASE+props.url+encodeURIComponent(NS)+'&edid='+encodeURIComponent(edid),
        props.name,
        props.options);
    return false;
}

/**
 * Button action for automatic headlines
 *
 * Insert a new headline based on the current section level
 *
 * @param  DOMElement btn   Button element to add the action to
 * @param  array      props Associative array of button properties
 * @param  string     edid  ID of the editor textarea
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function tb_autohead(btn, props, edid){
    var lvl = currentHeadlineLevel(edid),
        tags;

    // determine new level
    lvl += props.mod;
    if(lvl < 1) lvl = 1;
    if(lvl > 5) lvl = 5;

    tags = (new Array(8 - lvl)).join('=');
    insertTags(edid, tags+' ', ' '+tags+"\n", props.text);
    pickerClose();
    return false;
}


/**
 * Add button action for picker buttons and create picker element
 *
 * @param  jQuery      btn   Button element to add the action to
 * @param  array      props Associative array of button properties
 * @param  string     edid  ID of the editor textarea
 * @return boolean    If button should be appended
 * @author Gabriel Birke <birke@d-scribe.de>
 */
function addBtnActionPicker($btn, props, edid) {
    var pickerid = 'picker'+(pickercounter++);
    createPicker(pickerid, props, edid);

    $btn.click(
        function() {
            pickerToggle(pickerid,$btn);
            return false;
        }
    );

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
    dw_linkwiz.init(jQuery('#'+edid));
    jQuery(btn).click(function(){
        dw_linkwiz.toggle();
        return false;
    });
    return true;
}


/**
 * Show/Hide a previosly created picker window
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function pickerToggle(pickerid,$btn){
    var $picker = jQuery('#' + pickerid),
        pos = $btn.offset();
    $picker.toggleClass('a11y')
           .offset({left: pos.left+3, top: pos.top+$btn[0].offsetHeight+3});
}

/**
 * Close all open pickers
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function pickerClose(){
    jQuery('.picker').addClass('a11y');
}


/**
 * Replaces \n with linebreaks
 */
function fixtxt(str){
    return str.replace(/\\n/g,"\n");
}

jQuery(function () {
    initToolbar('tool__bar','wiki__text',toolbar);
});
