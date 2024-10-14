/**
 * The Link Wizard
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 * @author Pierre Spring <pierre.spring@caillou.ch>
 */
class LinkWizard {
    constructor() {
        this.$wiz = null;
        this.$entry = null;
        this.result = null;
        this.timer = null;
        this.textArea = null;
        this.selected = null;
        this.selection = null;
    }

    /**
     * Initialize the LinkWizard by creating the needed HTML
     * and attaching the event handlers
     */
    init($editor) {
        // position relative to the text area
        const pos = $editor.position();

        // create HTML Structure
        if (this.$wiz) return;
        this.$wiz = jQuery(document.createElement('div'))
            .dialog({
                autoOpen: false,
                draggable: true,
                title: LANG.linkwiz,
                resizable: false
            })
            .html(
                '<div>' + LANG.linkto + ' <input type="text" class="edit" id="link__wiz_entry" autocomplete="off" /></div>' +
                '<div id="link__wiz_result"></div>'
            )
            .parent()
            .attr('id', 'link__wiz')
            .css({
                'position': 'absolute',
                'top': (pos.top + 20) + 'px',
                'left': (pos.left + 80) + 'px'
            })
            .hide()
            .appendTo('.dokuwiki:first');

        this.textArea = $editor[0];
        this.result = jQuery('#link__wiz_result')[0];

        // scrollview correction on arrow up/down gets easier
        jQuery(this.result).css('position', 'relative');

        this.$entry = jQuery('#link__wiz_entry');
        if (JSINFO.namespace) {
            this.$entry.val(JSINFO.namespace + ':');
        }

        // attach event handlers
        jQuery('#link__wiz .ui-dialog-titlebar-close').on('click', () => this.hide());
        this.$entry.keyup((e) => this.onEntry(e));
        jQuery(this.result).on('click', 'a', (e) => this.onResultClick(e));
    }

    /**
     * Handle all keyup events in the entry field
     */
    onEntry(e) {
        if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') { //left/right
            return true; //ignore
        }
        if (e.key === 'Escape') { //Escape
            this.hide();
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        if (e.key === 'ArrowUp') { //Up
            this.select(this.selected - 1);
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        if (e.key === 'ArrowDown') { //Down
            this.select(this.selected + 1);
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        if (e.key === 'Enter') { //Enter
            if (this.selected > -1) {
                const $obj = this.$getResult(this.selected);
                if ($obj.length > 0) {
                    this.resultClick($obj.find('a')[0]);
                }
            } else if (this.$entry.val()) {
                this.insertLink(this.$entry.val());
            }

            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        this.autocomplete();
    }

    /**
     * Get one of the results by index
     *
     * @param   num int result div to return
     * @returns jQuery object
     */
    $getResult(num) {
        return jQuery(this.result).find('div').eq(num);
    }

    /**
     * Select the given result
     */
    select(num) {
        if (num < 0) {
            this.deselect();
            return;
        }

        const $obj = this.$getResult(num);
        if ($obj.length === 0) {
            return;
        }

        this.deselect();
        $obj.addClass('selected');

        // make sure the item is viewable in the scroll view

        //getting child position within the parent
        const childPos = $obj.position().top;
        //getting difference between the childs top and parents viewable area
        const yDiff = childPos + $obj.outerHeight() - jQuery(this.result).innerHeight();

        if (childPos < 0) {
            //if childPos is above viewable area (that's why it goes negative)
            jQuery(this.result)[0].scrollTop += childPos;
        } else if (yDiff > 0) {
            // if difference between childs top and parents viewable area is
            // greater than the height of a childDiv
            jQuery(this.result)[0].scrollTop += yDiff;
        }

        this.selected = num;
    }

    /**
     * Deselect a result if any is selected
     */
    deselect() {
        if (this.selected > -1) {
            this.$getResult(this.selected).removeClass('selected');
        }
        this.selected = -1;
    }

    /**
     * Handle clicks in the result set and dispatch them to
     * resultClick()
     */
    onResultClick(e) {
        if (!jQuery(e.target).is('a')) {
            return;
        }
        e.stopPropagation();
        e.preventDefault();
        this.resultClick(e.target);
        return false;
    }

    /**
     * Handles the "click" on a given result anchor
     */
    resultClick(a) {
        this.$entry.val(a.title);
        if (a.title == '' || a.title.substr(a.title.length - 1) == ':') {
            this.autocomplete_exec();
        } else {
            if (jQuery(a.nextSibling).is('span')) {
                this.insertLink(a.nextSibling.innerHTML);
            } else {
                this.insertLink('');
            }
        }
    }

    /**
     * Insert the id currently in the entry box to the textarea,
     * replacing the current selection or at the cursor position.
     * When no selection is available the given title will be used
     * as link title instead
     */
    insertLink(title) {
        let link = this.$entry.val(),
            sel, stxt;
        if (!link) {
            return;
        }

        sel = DWgetSelection(this.textArea);
        if (sel.start == 0 && sel.end == 0) {
            sel = this.selection;
        }

        stxt = sel.getText();

        // don't include trailing space in selection
        if (stxt.charAt(stxt.length - 1) == ' ') {
            sel.end--;
            stxt = sel.getText();
        }

        if (!stxt && !DOKU_UHC) {
            stxt = title;
        }

        // prepend colon inside namespaces for non namespace pages
        if (this.textArea.form.id.value.indexOf(':') != -1 &&
            link.indexOf(':') == -1) {
            link = ':' + link;
        }

        let so = link.length;
        let eo = 0;
        if (this.val) {
            if (this.val.open) {
                so += this.val.open.length;
                link = this.val.open + link;
            }
            link += '|';
            so += 1;
            if (stxt) {
                link += stxt;
            }
            if (this.val.close) {
                link += this.val.close;
                eo = this.val.close.length;
            }
        }

        pasteText(sel, link, {startofs: so, endofs: eo});
        this.hide();

        // reset the entry to the parent namespace
        const externallinkpattern = new RegExp('^((f|ht)tps?:)?//', 'i');
        let entry_value;
        if (externallinkpattern.test(this.$entry.val())) {
            if (JSINFO.namespace) {
                entry_value = JSINFO.namespace + ':';
            } else {
                entry_value = ''; //reset whole external links
            }
        } else {
            entry_value = this.$entry.val().replace(/[^:]*$/, '')
        }
        this.$entry.val(entry_value);
    }

    /**
     * Start the page/namespace lookup timer
     *
     * Calls autocomplete_exec when the timer runs out
     */
    autocomplete() {
        if (this.timer !== null) {
            window.clearTimeout(this.timer);
            this.timer = null;
        }

        this.timer = window.setTimeout(() => this.autocomplete_exec(), 350);
    }

    /**
     * Executes the AJAX call for the page/namespace lookup
     */
    autocomplete_exec() {
        const $res = jQuery(this.result);
        this.deselect();
        $res.html('<img src="' + DOKU_BASE + 'lib/images/throbber.gif" alt="" width="16" height="16" />')
            .load(
                DOKU_BASE + 'lib/exe/ajax.php',
                {
                    call: 'linkwiz',
                    q: this.$entry.val()
                }
            );
    }

    /**
     * Show the link wizard
     */
    show() {
        this.selection = DWgetSelection(this.textArea);
        this.$wiz.show();
        this.$entry.focus();
        this.autocomplete();

        // Move the cursor to the end of the input
        const temp = this.$entry.val();
        this.$entry.val('');
        this.$entry.val(temp);
    }

    /**
     * Hide the link wizard
     */
    hide() {
        this.$wiz.hide();
        this.textArea.focus();
    }

    /**
     * Toggle the link wizard
     */
    toggle() {
        if (this.$wiz.css('display') == 'none') {
            this.show();
        } else {
            this.hide();
        }
    }
}

const dw_linkwiz = new LinkWizard();

