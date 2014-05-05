/**
 * Text selection related functions.
 */

/**
 * selection prototype
 *
 * Object that capsulates the selection in a textarea. Returned by getSelection.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function selection_class(){
    this.start     = 0;
    this.end       = 0;
    this.obj       = null;
    this.rangeCopy = null;
    this.scroll    = 0;
    this.fix       = 0;

    this.getLength = function(){
        return this.end - this.start;
    };

    this.getText = function(){
        return (!this.obj) ? '' : this.obj.value.substring(this.start,this.end);
    };
}

/**
 * Get current selection/cursor position in a given textArea
 *
 * @link   http://groups.drupal.org/node/1210
 * @author Andreas Gohr <andi@splitbrain.org>
 * @link   http://linebyline.blogspot.com/2006/11/textarea-cursor-position-in-internet.html
 * @returns object - a selection object
 */
function getSelection(textArea) {
    var sel = new selection_class();

    sel.obj   = textArea;
    sel.start = textArea.value.length;
    sel.end   = textArea.value.length;
    textArea.focus();
    if(document.getSelection) {          // Mozilla et al.
        sel.start  = textArea.selectionStart;
        sel.end    = textArea.selectionEnd;
        sel.scroll = textArea.scrollTop;
    } else if(document.selection) {      // MSIE
        /*
         * This huge lump of code is neccessary to work around two MSIE bugs:
         *
         * 1. Selections trim newlines at the end of the code
         * 2. Selections count newlines as two characters
         */

        // The current selection
        sel.rangeCopy = document.selection.createRange().duplicate();
        if (textArea.tagName === 'INPUT')  {
            var before_range = textArea.createTextRange();
            before_range.expand('textedit');                       // Selects all the text
        } else {
            var before_range = document.body.createTextRange();
            before_range.moveToElementText(textArea);              // Selects all the text
        }
        before_range.setEndPoint("EndToStart", sel.rangeCopy);     // Moves the end where we need it

        var before_finished = false, selection_finished = false;
        var before_text, selection_text;
        // Load the text values we need to compare
        before_text  = before_range.text;
        selection_text = sel.rangeCopy.text;

        sel.start = before_text.length;
        sel.end   = sel.start + selection_text.length;

        // Check each range for trimmed newlines by shrinking the range by 1 character and seeing
        // if the text property has changed.  If it has not changed then we know that IE has trimmed
        // a \r\n from the end.
        do {
            if (!before_finished) {
                if (before_range.compareEndPoints("StartToEnd", before_range) == 0) {
                    before_finished = true;
                } else {
                    before_range.moveEnd("character", -1);
                    if (before_range.text == before_text) {
                        sel.start += 2;
                        sel.end += 2;
                    } else {
                        before_finished = true;
                    }
                }
            }
            if (!selection_finished) {
                if (sel.rangeCopy.compareEndPoints("StartToEnd", sel.rangeCopy) == 0) {
                    selection_finished = true;
                } else {
                    sel.rangeCopy.moveEnd("character", -1);
                    if (sel.rangeCopy.text == selection_text) {
                        sel.end += 2;
                    } else {
                        selection_finished = true;
                    }
                }
            }
        } while ((!before_finished || !selection_finished));

        // count number of newlines in str to work around stupid IE selection bug
        var countNL = function(str) {
            var m = str.split("\r\n");
            if (!m || !m.length) return 0;
            return m.length-1;
        };
        sel.fix = countNL(sel.obj.value.substring(0,sel.start));

    }
    return sel;
}

/**
 * Set the selection
 *
 * You need to get a selection object via getSelection() first, then modify the
 * start and end properties and pass it back to this function.
 *
 * @link http://groups.drupal.org/node/1210
 * @author Andreas Gohr <andi@splitbrain.org>
 * @param object selection - a selection object as returned by getSelection()
 */
function setSelection(selection){
    if(document.getSelection){ // FF
        // what a pleasure in FF ;)
        selection.obj.setSelectionRange(selection.start,selection.end);
        if(selection.scroll) selection.obj.scrollTop = selection.scroll;
    } else if(document.selection) { // IE
        selection.rangeCopy.collapse(true);
        selection.rangeCopy.moveStart('character',selection.start - selection.fix);
        selection.rangeCopy.moveEnd('character',selection.end - selection.start);
        selection.rangeCopy.select();
    }
}

/**
 * Inserts the given text at the current cursor position or replaces the current
 * selection
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @param string text          - the new text to be pasted
 * @param objct  selecttion    - selection object returned by getSelection
 * @param int    opts.startofs - number of charcters at the start to skip from new selection
 * @param int    opts.endofs   - number of characters at the end to skip from new selection
 * @param bool   opts.nosel    - set true if new text should not be selected
 */
function pasteText(selection,text,opts){
    if(!opts) opts = {};
    // replace the content

    selection.obj.value =
        selection.obj.value.substring(0, selection.start) + text +
        selection.obj.value.substring(selection.end, selection.obj.value.length);

    // set new selection
    if (is_opera) {
        // Opera replaces \n by \r\n when inserting text.
        selection.end = selection.start + text.replace(/\r?\n/g, '\r\n').length;
    } else {
        selection.end = selection.start + text.length;
    }

    // modify the new selection if wanted
    if(opts.startofs) selection.start += opts.startofs;
    if(opts.endofs)   selection.end   -= opts.endofs;

    // no selection wanted? set cursor to end position
    if(opts.nosel) selection.start = selection.end;

    setSelection(selection);
}


/**
 * Format selection
 *
 * Apply tagOpen/tagClose to selection in textarea, use sampleText instead
 * of selection if there is none.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function insertTags(textAreaID, tagOpen, tagClose, sampleText){
    var txtarea = jQuery('#' + textAreaID)[0];

    var selection = getSelection(txtarea);
    var text = selection.getText();
    var opts;

    // don't include trailing space in selection
    if(text.charAt(text.length - 1) == ' '){
        selection.end--;
        text = selection.getText();
    }

    if(!text){
        // nothing selected, use the sample text and select it
        text = sampleText;
        opts = {
            startofs: tagOpen.length,
            endofs: tagClose.length
        };
    }else{
        // place cursor at the end
        opts = {
            nosel: true
        };
    }

    // surround with tags
    text = tagOpen + text + tagClose;

    // do it
    pasteText(selection,text,opts);
}

/**
 * Wraps around pasteText() for backward compatibility
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function insertAtCarret(textAreaID, text){
    var txtarea = jQuery('#' + textAreaID)[0];
    var selection = getSelection(txtarea);
    pasteText(selection,text,{nosel: true});
}
