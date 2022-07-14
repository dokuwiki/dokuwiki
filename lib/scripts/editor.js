/**
 * The DokuWiki editor features
 *
 * These are the advanced features of the editor. It does NOT contain any
 * code for the toolbar buttons and its functions. See toolbar.js for that.
 */
class DokuWikiEditor {
    editor;
    textarea;
    summary;
    btnSave;
    btnPreview;
    btnCancel;
    haschanged;

    /**
     * Initialize the Editor behavior
     *
     * @param {HTMLFormElement} form
     */
    constructor(form) {
        this.editor = form;
        this.textarea = form.elements['wikitext'];
        this.summary = form.elements['summary'];
        this.haschanged = form.elements['haschanged'];
        this.btnSave = form.elements['do[save]'];
        this.btnPreview = form.elements['do[preview]'];
        this.btnCancel = form.elements['do[cancel]'];

        // this behavior applies to all editors
        this.setupSize();
        this.setupWrap();

        // the following only to non-readonly ones
        if (this.textarea.readOnly) return;

        this.setupChangeWarning();
        this.setupDraftDeletion();
        this.textarea.addEventListener('keydown', this.handleKeys.bind(this));
        this.summary.addEventListener('input', this.checkSummary.bind(this));
    }

    /**
     * Set and remember text area size using a cookie
     */
    setupSize() {
        const height = Math.max(25, parseInt(DokuCookie.getValue('sizeCtl'), 10));
        this.textarea.style.height = height + 'px';

        new ResizeObserver(() => {
            DokuCookie.setValue('sizeCtl', this.textarea.offsetHeight);
        }).observe(this.textarea);
    }

    /**
     * Allow switching the wrap beahviour and store it in a cookie
     */
    setupWrap() {
        // set stored wrap
        const wrp = DokuCookie.getValue('wrapCtl');
        if (wrp) {
            this.textarea.wrap = wrp;
        }

        // create toggle element
        const toggle = document.createElement('span');
        toggle.className = 'wraptoggle';
        toggle.innerText = '⏎';
        toggle.title = 'FIXME toggle line wrap';
        document.getElementById('size__ctl').append(toggle); // FIXME remove ID reliance

        // add click handler
        toggle.addEventListener('click', () => {
            const current = this.textarea.wrap.toLowerCase();
            this.textarea.wrap = (current === 'off') ? 'soft' : 'off';
            DokuCookie.setValue('wrapCtl', this.textarea.wrap);
        });
    }

    /**
     * Warn about unsaved changes when navigating away
     */
    setupChangeWarning() {
        // notice changes (also trigger summary check)
        this.textarea.addEventListener('input', () => {
            this.haschanged.value = '1';
            this.checkSummary();
        });

        // show unsaved changes warning, when trying to navigate away
        window.onbeforeunload = () => {
            if (this.haschanged.value === '1') {
                return LANG.notsavedyet;
            }
        };

        // prevent warning on some buttons, by removing the handler
        const prevent = () => {
            window.onbeforeunload = null;
        };
        this.btnPreview.addEventListener('click', prevent);
        this.btnSave.addEventListener('click', prevent);
    }

    /**
     * Remove a possibly saved draft using ajax
     *
     * Note: draft saving is currently handled in locktimer.js
     */
    setupDraftDeletion() {
        window.onunload = () => {
            // FIXME replace jQuery dependency
            jQuery.post(DOKU_BASE + 'lib/exe/ajax.php',
                {
                    call: 'draftdel',
                    id: this.editor.elements['id'].value,
                    sectok: this.editor.elements['sectok'].value
                }
            );
        };

        // do not delete the draft on preview
        this.btnPreview.addEventListener('click', () => {
            window.onunload = null;
        });
    }

    /**
     * Set the class of the summary based on it's content and the text change status
     */
    checkSummary() {
        if (this.haschanged.value === '1' && this.summary.value === '') {
            this.summary.classList.add('missing');
        } else {
            this.summary.classList.remove('missing');
        }
    }

    /**
     * Make indented formattings easier to handle
     *
     * Listens to all key inputs and handle indentions of lists and code blocks
     *
     * Handles space, backspace, enter and ctrl-enter presses
     *
     * @param {KeyboardEvent} e - the key press event object
     */
    handleKeys(e) {
        // Save on CTRL+Enter
        if (e.key === 'Enter' && e.ctrlKey) {
            this.btnSave.click();
            e.preventDefault(); // prevent enter key
            return;
        }

        // Handle text transformations below
        const selection = DWgetSelection(this.textarea);
        if (selection.getLength() > 0) {
            return; //there was text selected, keep standard behavior. we're done
        }

        let line = "\n" + this.textarea.value.substring(0, selection.start);
        const linepos = Math.max(
            line.lastIndexOf("\n"),
            line.lastIndexOf("\r")  //IE workaround
        );
        line = line.substring(linepos);

        if (e.key === 'Enter') {
            this.handleKeyEnter(line, linepos, selection) && e.preventDefault();
        } else if (e.key === 'Backspace') {
            this.handleKeyBackspace(line, linepos, selection) && e.preventDefault();
        } else if (e.key === ' ') { // Space
            this.handleKeySpace(line, linepos, selection) && e.preventDefault();
        }
    }

    /**
     * Handle enter presses in the textarea
     *
     * @param {string} line The current line (and following)
     * @param {int} linepos The start position of the current line
     * @param {selection_class} selection A DokuWiki Text Selection object (current cursor)
     * @returns {boolean} true if the event was handled and the default should be cancelled
     */
    handleKeyEnter(line, linepos, selection) {
        // only handle indented lines
        const isIndented = line.match(/(\n  +([*-] ?)?)/);
        if (!isIndented) return false;

        // remember scroll position
        const scroll = this.textarea.scrollHeight;

        // Cancel list if the last item is empty (i.e. two times enter)
        const isEmptyListItem = line.match(/^\n  +[*-]\s*$/);
        if (isEmptyListItem && this.textarea.value.substring(selection.start).match(/^($|\r?\n)/)) {
            this.textarea.value =
                this.textarea.value.substring(0, linepos) + "\n" +
                this.textarea.value.substring(selection.start);
            selection.start = linepos + 1;
            selection.end = linepos + 1;
            DWsetSelection(selection);
        } else {
            insertAtCarret(this.textarea.id, isIndented[1]);
        }

        // restore scroll postion
        this.textarea.scrollTop += (this.textarea.scrollHeight - scroll);
        return true;
    }

    /**
     * Handle backspace presses in the textarea
     *
     * @param {string} line The current line (and following)
     * @param {int} linepos The start position of the current line
     * @param {selection_class} selection A DokuWiki Text Selection object (current cursor)
     * @returns {boolean} true if the event was handled and the default should be cancelled
     */
    handleKeyBackspace(line, linepos, selection) {
        const isListItem = line.match(/(\n  +)([*-] ?)$/);
        if (!isListItem) return false;

        const spaces = isListItem[1].length - 1;

        if (spaces > 3) { // unindent one level
            this.textarea.value =
                this.textarea.value.substring(0, linepos) +
                this.textarea.value.substring(linepos + 2);
            selection.start = selection.start - 2;
            selection.end = selection.start;
        } else { // delete list point
            this.textarea.value =
                this.textarea.value.substring(0, linepos) +
                this.textarea.value.substring(selection.start);
            selection.start = linepos;
            selection.end = linepos;
        }
        DWsetSelection(selection);
        return true;
    }

    /**
     * Handle space presses in the textarea
     *
     * @param {string} line The current line (and following)
     * @param {int} linepos The start position of the current line
     * @param {selection_class} selection A DokuWiki Text Selection object (current cursor)
     * @returns {boolean} true if the event was handled and the default should be cancelled
     */
    handleKeySpace(line, linepos, selection) {
        // intend list item
        const isListItem = line.match(/(\n  +)([*-] )$/);
        if (!isListItem) return false;

        this.textarea.value =
            this.textarea.value.substring(0, linepos) + '  ' +
            this.textarea.value.substring(linepos);
        selection.start = selection.start + 2;
        selection.end = selection.start;
        DWsetSelection(selection);

        return true;
    }
}

// FIXME drop jQuery Dependency
jQuery(function () {
    const $editform = jQuery('#dw__editform');
    if ($editform.length) new DokuWikiEditor($editform[0]);
});

