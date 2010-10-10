/**
 * The Link Wizard
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 * @author Pierre Spring <pierre.spring@caillou.ch>
 */
(function($){
    $.fn.extend({
        linkwiz: function(editor) {

            var wiz;
            var entry;
            var result
            var timer;
            var textArea;
            var selected;
            var selection;

            /**
             * Initialize the linkwizard by creating the needed HTML
             * and attaching the eventhandlers
             */
            var init = function(textAreaElement){

                // create HTML Structure
                wiz = document.createElement('div');
                wiz.id = 'link__wiz';
                wiz.className     = 'picker';
                wiz.style.top  = (findPosY(textAreaElement)+20)+'px';
                wiz.style.left = (findPosX(textAreaElement)+80)+'px';
                wiz.style.marginLeft = '-10000px';
                wiz.style.marginTop  = '-10000px';

                wiz.innerHTML =
                     '<div id="link__wiz_header">'+
                     '<img src="'+DOKU_BASE+'lib/images/close.png" width="16" height="16" align="right" alt="" id="link__wiz_close" />'+
                     LANG['linkwiz']+'</div>'+
                     '<div>'+LANG['linkto']+' <input type="text" class="edit" id="link__wiz_entry" autocomplete="off" /></div>'+
                     '<div id="link__wiz_result"></div>';
                $('#dw__editform')[0].parentNode.appendChild(wiz);
                textArea = textAreaElement;
                result = $('#link__wiz_result')[0];
                entry = $('#link__wiz_entry')[0];

                // attach event handlers
                var obj;
                var obj = $('#link__wiz_close')[0];
                obj.onclick = hide;

                addEvent(entry,'keyup',onEntry);
                addEvent(result,'click',onResultClick);

                $(wiz).draggable({handle: '#link__wiz_header'});

            };

            /**
             * handle all keyup events in the entry field
             */
            var onEntry = function(e){
                if(e.keyCode == 37 || e.keyCode == 39){ //left/right
                    return true; //ignore
                }
                if(e.keyCode == 27){
                    hide();
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
                if(e.keyCode == 38){ //Up
                    select(selected -1);
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
                if(e.keyCode == 40){ //Down
                    select(selected +1);
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
                if(e.keyCode == 13){ //Enter
                    if(selected > -1){
                        var obj = getResult(selected);
                        if(obj){
                            var a = $(obj).find('a')[0];
                            resultClick(a);
                        }
                    }else if(entry.value){
                        insertLink(entry.value);
                    }

                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
                autocomplete();
            };

            /**
             * Get one of the result by index
             *
             * @param int result div to return
             * @returns DOMObject or null
             */
            var getResult = function(num){
                var obj;
                var childs = $(result).find('div');
                var obj = childs[num];
                if(obj){
                    return obj;
                }else{
                    return null;
                }
            };

            /**
             * Select the given result
             */
            var select = function(num){
                if(num < 0){
                    deselect();
                    return;
                }

                var obj = getResult(num);
                if(obj){
                    deselect();
                    obj.className += ' selected';

                    // make sure the item is viewable in the scroll view
                    // FIXME check IE compatibility
                    if(obj.offsetTop > result.scrollTop + result.clientHeight){
                        result.scrollTop += obj.clientHeight;
                    }else if(obj.offsetTop - result.clientHeight < result.scrollTop){ // this works but isn't quite right, fixes welcome
                        result.scrollTop -= obj.clientHeight;
                    }
                    // now recheck - if still not in view, the user used the mouse to scroll
                    if( (obj.offsetTop > result.scrollTop + result.clientHeight) ||
                        (obj.offsetTop < result.scrollTop) ){
                        obj.scrollIntoView();
                    }

                    selected = num;
                }
            };

            /**
             * deselect a result if any is selected
             */
            var deselect = function(){
                if(selected > -1){
                    var obj = getResult(selected);
                    if(obj){
                        obj.className = obj.className.replace(/ ?selected/,'');
                    }
                }
                selected = -1;
            };

            /**
             * Handle clicks in the result set an dispatch them to
             * resultClick()
             */
            var onResultClick = function(e){
                if(e.target.tagName != 'A') return;
                e.stopPropagation();
                e.preventDefault();
                resultClick(e.target);
                return false;
            };

            /**
             * Handles the "click" on a given result anchor
             */
            var resultClick = function(a){
                var id = a.title;
                if(id == '' || id.substr(id.length-1) == ':'){
                    entry.value = id;
                    autocomplete_exec();
                }else{
                    entry.value = id;
                    if(a.nextSibling && a.nextSibling.tagName == 'SPAN'){
                        insertLink(a.nextSibling.innerHTML);
                    }else{
                        insertLink('');
                    }
                }
            };

            /**
             * Insert the id currently in the entry box to the textarea,
             * replacing the current selection or at the curso postion.
             * When no selection is available the given title will be used
             * as link title instead
             */
            var insertLink = function(title){
                if(!entry.value) return;

                var sel = getSelection(textArea);
                if(sel.start == 0 && sel.end == 0) sel = selection;

                var stxt = sel.getText();

                // don't include trailing space in selection
                if(stxt.charAt(stxt.length - 1) == ' '){
                    sel.end--;
                    var stxt = sel.getText();
                }

                if(!stxt && !DOKU_UHC) stxt=title;

                // prepend colon inside namespaces for non namespace pages
                if(textArea.form['id'].value.indexOf(':') != -1 &&
                   entry.value.indexOf(':') == -1){
                    entry.value = ':'+entry.value;
                }

                var link = '[['+entry.value+'|';
                if(stxt) link += stxt;
                link += ']]';

                var so = entry.value.length+3;
                var eo = 2;

                pasteText(sel,link,{startofs: so, endofs: eo});
                hide();
                // reset the entry to the parent namespace and remove : at the beginning
                entry.value = entry.value.replace(/(^:)?[^:]*$/, '');
            };

            /**
             * Start the page/namespace lookup timer
             *
             * Calls autocomplete_exec when the timer runs out
             */
            var autocomplete = function(){
                if(timer !== null){
                    window.clearTimeout(timer);
                    timer = null;
                }

                timer = window.setTimeout(autocomplete_exec,350);
            };

            /**
             * Executes the AJAX call for the page/namespace lookup
             */
            var autocomplete_exec = function(){
                deselect();
                result.innerHTML = '<img src="'+DOKU_BASE+'lib/images/throbber.gif" alt="" width="16" height="16" />';

                // because we need to use POST, we
                // can not use the .load() function.
                $.post(
                    DOKU_BASE + 'lib/exe/ajax.php',
                    {
                        call: 'linkwiz',
                        q: entry.value
                    },
                    function (data) {
                        result.innerHTML = data;
                    },
                    'html'
                );
            };

            /**
             * Clears the result area
             */
            var clear = function(){
                result.innerHTML = 'Search for a matching page name above, or browse through the pages on the right';
                entry.value = '';
            };

            /**
             * Show the linkwizard
             */
            var show = function(){
                selection  = getSelection(textArea);
                wiz.style.marginLeft = '0px';
                wiz.style.marginTop = '0px';
                entry.focus();
                autocomplete();
            };

            /**
             * Hide the link wizard
             */
            var hide = function(){
                wiz.style.marginLeft = '-10000px';
                wiz.style.marginTop  = '-10000px';
                textArea.focus();
            };

            /**
             * Toggle the link wizard
             */
            var toggle = function(){
                if(wiz.style.marginLeft == '-10000px'){
                    show();
                }else{
                    hide();
                }
            };

            init($(editor)[0]);

            return this.each(function() {
                $(this).click(function (e) {
                    e.preventDefault();
                    toggle();
                });
            });
        }
    });
})(jQuery);