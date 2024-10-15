/* global jQuery, DOKU_BASE, DOKU_UHC, JSINFO, LANG, DWgetSelection, pasteText */

/**
 * The Link Wizard
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 * @author Pierre Spring <pierre.spring@caillou.ch>
 */
class LinkWizard {

    /** @var {jQuery} $wiz The wizard dialog */
    $wiz = null;
    /** @var {jQuery} $entry The input field to interact with the wizard*/
    $entry = null;
    /** @var {HTMLDivElement} result The result output div */
    result = null;
    /** @var {TimerHandler} timer Used to debounce the autocompletion */
    timer = null;
    /** @var {HTMLTextAreaElement} textArea The text area of the editor into which links are inserted */
    textArea = null;
    /** @var {int} selected The index of the currently selected object in the result list */
    selected = -1;
    /** @var {Object} selection A DokuWiki selection object holding text positions in the editor */
    selection = null;
    /** @var {Object} val Mechanism to modify the resulting links. See 935ecb0ef751ac1d658932316e06410e70c483e0 */
    val = null;

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
        if (a.title === '' || a.title.charAt(a.title.length - 1) === ':') {
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
        if (sel.start === 0 && sel.end === 0) {
            sel = this.selection;
        }

        stxt = sel.getText();

        // don't include trailing space in selection
        if (stxt.charAt(stxt.length - 1) === ' ') {
            sel.end--;
            stxt = sel.getText();
        }

        if (!stxt && !DOKU_UHC) {
            stxt = title;
        }

        // prepend colon inside namespaces for non namespace pages
        if (this.textArea.form.id.value.indexOf(':') !== -1 &&
            link.indexOf(':') === -1) {
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
        if (this.$wiz.css('display') === 'none') {
            this.show();
        } else {
            this.hide();
        }
    }

    /**
     * Create a relative ID from a given reference ID and a full ID to link to
     *
     * Both IDs are expected to be clean, (eg. the result of cleanID()). No relative paths,
     * leading colons or similar things are alowed. As long as pages have a common prefix,
     * a relative link is constructed.
     *
     * This method is static and meant to be reused by other scripts if needed.
     *
     * @todo does currently not create page relative links using ~
     * @param {string} ref The ID of a page the link is used on
     * @param {string} id The ID to link to
     */
    static createRelativeID(ref, id) {
        const sourceNs = ref.split(':');
        [/*sourcePage*/] = sourceNs.pop();
        const targetNs = id.split(':');
        const targetPage = targetNs.pop()
        const relativeID = [];

        // Find the common prefix length
        let commonPrefixLength = 0;
        while (
            commonPrefixLength < sourceNs.length &&
            commonPrefixLength < targetNs.length &&
            sourceNs[commonPrefixLength] === targetNs[commonPrefixLength]
            ) {
            commonPrefixLength++;
        }


        if (sourceNs.length) {
            // special treatment is only needed when the reference is a namespaced page
            if (commonPrefixLength) {
                if (commonPrefixLength === sourceNs.length && commonPrefixLength === targetNs.length) {
                    // both pages are in the same namespace
                    // link consists of simple page only
                    // namespaces are irrelevant
                } else if (commonPrefixLength < sourceNs.length) {
                    // add .. for each missing namespace from common to the target
                    relativeID.push('..'.repeat(sourceNs.length - commonPrefixLength));
                } else {
                    // target is below common prefix, add .
                    relativeID.push('.');
                }
            } else if (targetNs.length === 0) {
                // target is in the root namespace, but source is not, make it absolute
                relativeID.push('');
            }
            // add any remaining parts of targetNS
            relativeID.push(...targetNs.slice(commonPrefixLength));
        } else {
            // source is in the root namespace, just use target as is
            relativeID.push(...targetNs);
        }

        // add targetPage
        relativeID.push(targetPage);

        return relativeID.join(':');
    }

}

const dw_linkwiz = new LinkWizard();

