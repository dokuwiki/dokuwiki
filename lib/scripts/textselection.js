/**
 * Text selection related functions.
 */

/**
 * selection prototype
 *
 * Object that capsulates the selection in a textarea. Returned by DWgetSelection.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function selection_class(){
    this.start     = 0;
    this.end       = 0;
    this.obj       = null;
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
function DWgetSelection(textArea) {
    var sel = new selection_class();

    textArea.focus();
    sel.obj   = textArea;
    sel.start  = textArea.selectionStart;
    sel.end    = textArea.selectionEnd;
    sel.scroll = textArea.scrollTop;
    return sel;
}

/**
 * Set the selection
 *
 * You need to get a selection object via DWgetSelection() first, then modify the
 * start and end properties and pass it back to this function.
 *
 * @link http://groups.drupal.org/node/1210
 * @author Andreas Gohr <andi@splitbrain.org>
 * @param {selection_class} selection  a selection object as returned by DWgetSelection()
 */
function DWsetSelection(selection){
    selection.obj.setSelectionRange(selection.start, selection.end);
    if(selection.scroll) selection.obj.scrollTop = selection.scroll;
}

/**
 * Inserts the given text at the current cursor position or replaces the current
 * selection
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @param {string}  text           the new text to be pasted
 * @param {selection_class}  selection     selection object returned by DWgetSelection
 * @param {int}     opts.startofs  number of charcters at the start to skip from new selection
 * @param {int}     opts.endofs    number of characters at the end to skip from new selection
 * @param {boolean} opts.nosel     set true if new text should not be selected
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

    DWsetSelection(selection);
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

    var selection = DWgetSelection(txtarea);
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
    var selection = DWgetSelection(txtarea);
    pasteText(selection,text,{nosel: true});
}
