/**
 * selection prototype
 *
 * Object that capsulates the selection in a textarea. Returned by getSelection.
 */
function selection_class(){
    this.start     = 0;
    this.end       = 0;
    this.obj       = null;
    this.rangeCopy = null;

    this.getLength = function(){
        return this.end - this.start;
    };

    this.getText = function(){
        if(!this.obj) return '';
        return this.obj.value.substring(this.start,this.end);
    }
}

/**
 * Get current selection/cursor position in a given textArea
 *
 * @link http://groups.drupal.org/node/1210
 * @returns object - a selection object
 */
function getSelection(textArea) {
    var sel = new selection_class();

    sel.obj   = textArea;
    sel.start = textArea.value.length;
    sel.end   = textArea.value.length;

    textArea.focus();
    if(document.getSelection) {          // Mozilla et al.
        sel.start = textArea.selectionStart;
        sel.end = textArea.selectionEnd;
    } else if(document.selection) {      // MSIE
        // The current selection
        var range = document.selection.createRange();
        sel.rangeCopy = range.duplicate();
        // Select all text
        sel.rangeCopy.moveToElementText(textArea);
        // Now move 'dummy' end point to end point of original range
        sel.rangeCopy.setEndPoint( 'EndToEnd', range );
        // Now we can calculate start and end points
        sel.start = sel.rangeCopy.text.length - range.text.length;
        sel.end = sel.start + range.text.length;
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
 * @param object selection - a selection object as returned by getSelection()
 */
function setSelection(selection){
    if(document.getSelection){ // FF
        // what a pleasure in FF ;)
        selection.obj.setSelectionRange(selection.start,selection.end);
    } else if(document.selection) { // IE
        // count number of newlines in str to work around stupid IE selection bug
        var countNL = function(str) {
            var m = str.split("\n");
            if (!m || !m.length) return 0;
            return m.length-1;
        };
        var fix = countNL(selection.obj.value.substring(0,selection.start));

        selection.rangeCopy.collapse(true);
        selection.rangeCopy.moveStart('character',selection.start - fix);
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
 * @param int    opts.endofs   - number of charcters at the end to skip from new selection
 * @param bool   opts.ofs      - set tru if new text should not be selected
 */
function pasteText(selection,text,opts){
    if(!opts) opts = {};
    // replace the content
    selection.obj.value =
        selection.obj.value.substring(0, selection.start) + text +
        selection.obj.value.substring(selection.end, selection.obj.value.length);

    // set new selection
    selection.end = selection.start + text.length;

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
    var txtarea = document.getElementById(textAreaID);

    var selection = getSelection(txtarea);
    var text = selection.getText();

    // don't include trailing space in selection
    if(text.charAt(text.length - 1) == ' '){
        selection.end--;
        text = selection.getText();
    }

    // nothing selected, use the sample text
    if(!text) text = sampleText;

    // surround with tags
    text = tagOpen + text + tagClose;

    // prepare options
    var opts = {
        startofs: tagOpen.length,
        endofs: tagClose.length
    };

    // do it
    pasteText(selection,text,opts);
}

/**
 * Wraps around pasteText() for backward compatibility
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function insertAtCarret(textAreaID, text){
    var txtarea = document.getElementById(textAreaID);
    var selection = getSelection(txtarea);
    pasteText(selection,text,{nosel: true});
}

