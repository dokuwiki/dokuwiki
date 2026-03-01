qq.extend(qq.FileUploader.prototype, {
    _createUploadHandler: function(){
        var self = this,
            handlerClass;

        if(qq.UploadHandlerXhr.isSupported()){
            handlerClass = 'UploadHandlerXhr';
            //handlerClass = 'UploadHandlerForm';
        } else {
            handlerClass = 'UploadHandlerForm';
        }

        var handler = new qq[handlerClass]({
            debug: this._options.debug,
            action: this._options.action,
            maxConnections: this._options.maxConnections,
            onProgress: function(id, fileName, loaded, total){
                self._onProgress(id, fileName, loaded, total);
                self._options.onProgress(id, fileName, loaded, total);
            },
            onComplete: function(id, fileName, result){
                self._onComplete(id, fileName, result);
                self._options.onComplete(id, fileName, result);
            },
            onCancel: function(id, fileName){
                self._onCancel(id, fileName);
                self._options.onCancel(id, fileName);
            },
            onUpload: function(){
                self._onUpload();
            }
        });

        return handler;
    },

    _onUpload: function(){
        this._handler.uploadAll(this._options.params);
    },

    _uploadFile: function(fileContainer){
        var id = this._handler.add(fileContainer);
        var fileName = this._handler.getName(id);

        if (this._options.onSubmit(id, fileName) !== false){
            this._onSubmit(id, fileName);
        }
    },

    _addToList: function(id, fileName){
        var item = qq.toElement(this._options.fileTemplate);
        item.qqFileId = id;

        var fileElement = this._find(item, 'file');
        qq.setText(fileElement, fileName);
        this._find(item, 'size').style.display = 'none';

        // name suggestion (simplified cleanID)
        var nameElement = this._find(item, 'nameInput');
        fileName = fileName.toLowerCase();
        fileName = fileName.replace(/([ !"#$%&\'()+,\/;<=>?@[\]^`{|}~:]+)/g, '_');
        fileName = fileName.replace(/^_+/,'');
        nameElement.value = fileName;
        nameElement.id = 'mediamanager__upload_item'+id;

        this._listElement.appendChild(item);
    }

});

qq.FileUploaderExtended = function(o){
    // call parent constructor
    qq.FileUploaderBasic.apply(this, arguments);

    qq.extend(this._options, {
        element: null,
        // if set, will be used instead of qq-upload-list in template
        listElement: null,

        template: '<div class="qq-uploader">' +
            '<div class="qq-upload-drop-area"><span>' + LANG.media_drop + '</span></div>' +
            '<div class="qq-upload-button">' + LANG.media_select + '</div>' +
            '<ul class="qq-upload-list"></ul>' +
            '<div class="qq-action-container">' +
            '  <button class="qq-upload-action" type="submit" id="mediamanager__upload_button">' + LANG.media_upload_btn + '</button>' +
            '  <label class="qq-overwrite-check"><input type="checkbox" value="1" name="ow" class="dw__ow"> <span>' + LANG.media_overwrt + '</span></label>' +
            '</div>' +
            '</div>',

        // template for one item in file list
        fileTemplate: '<li>' +
              '<span class="qq-upload-file hidden"></span>' +
            '  <input class="qq-upload-name-input edit" type="text" value="" />' +
            '  <span class="qq-upload-spinner hidden"></span>' +
            '  <span class="qq-upload-size"></span>' +
            '  <a class="qq-upload-cancel" href="#">' + LANG.media_cancel + '</a>' +
            '  <span class="qq-upload-failed-text error">Failed</span>' +
            '</li>',

        classes: {
            // used to get elements from templates
            button: 'qq-upload-button',
            drop: 'qq-upload-drop-area',
            dropActive: 'qq-upload-drop-area-active',
            list: 'qq-upload-list',
            nameInput: 'qq-upload-name-input',
            overwriteInput: 'qq-overwrite-check',
            uploadButton: 'qq-upload-action',
            file: 'qq-upload-file',

            spinner: 'qq-upload-spinner',
            size: 'qq-upload-size',
            cancel: 'qq-upload-cancel',

            // added to list item when upload completes
            // used in css to hide progress spinner
            success: 'qq-upload-success',
            fail: 'qq-upload-fail',
            failedText: 'qq-upload-failed-text'
        }
    });

    qq.extend(this._options, o);

    this._element = this._options.element;
    this._element.innerHTML = this._options.template;
    this._listElement = this._options.listElement || this._find(this._element, 'list');

    this._classes = this._options.classes;

    this._button = this._createUploadButton(this._find(this._element, 'button'));

    this._bindCancelEvent();
    this._bindUploadEvent();
    this._setupDragDrop();
};

qq.extend(qq.FileUploaderExtended.prototype, qq.FileUploader.prototype);

qq.extend(qq.FileUploaderExtended.prototype, {
    _bindUploadEvent: function(){
        var self = this,
            list = this._listElement;

        qq.attach(document.getElementById('mediamanager__upload_button'), 'click', function(e){
            e = e || window.event;
            var target = e.target || e.srcElement;
            qq.preventDefault(e);
            self._handler._options.onUpload();

            jQuery(".qq-upload-name-input").each(function (i) {
                jQuery(this).prop('disabled', true);
            });
        });
    },

    _onComplete: function(id, fileName, result){
        this._filesInProgress--;

        // mark completed
        var item = this._getItemByFileId(id);
        qq.remove(this._find(item, 'cancel'));
        qq.remove(this._find(item, 'spinner'));

        var nameInput = this._find(item, 'nameInput');
        var fileElement = this._find(item, 'file');
        qq.setText(fileElement, nameInput.value);
        qq.removeClass(fileElement, 'hidden');
        qq.remove(nameInput);
        jQuery('.qq-upload-button, #mediamanager__upload_button').remove();
        jQuery('.dw__ow').parent().hide();
        jQuery('.qq-upload-drop-area').remove();

        if (result.success){
            qq.addClass(item, this._classes.success);
            $link = '<a href="' + result.link + '" id="h_:' + result.id + '" class="select">' + nameInput.value + '</a>';
            jQuery(fileElement).html($link);

        } else {
            qq.addClass(item, this._classes.fail);
            var fail = this._find(item, 'failedText');
            if (result.error) qq.setText(fail, result.error);
        }

        if (document.getElementById('media__content') && !document.getElementById('mediamanager__done_form')) {
            var action = document.location.href;
            var i = action.indexOf('?');
            if (i) action = action.substr(0, i);
            var button = '<form method="post" action="' + action + '" id="mediamanager__done_form"><div>';
            button += '<input type="hidden" value="' + result.ns + '" name="ns">';
            button += '<input type="hidden" value="1" name="recent">';
            button += '<button type="submit">' + LANG.media_done_btn + '</button></div></form>';
            jQuery('#mediamanager__uploader').append(button);
        }
    }

});

qq.extend(qq.UploadHandlerForm.prototype, {
    uploadAll: function(params){
        this._uploadAll(params);
    },

    getName: function(id){
        var file = this._inputs[id];
        var name = document.getElementById('mediamanager__upload_item'+id);
        if (name != null) {
            return name.value;
        } else {
            if (file != null) {
                // get input value and remove path to normalize
                return file.value.replace(/.*(\/|\\)/, "");
            } else {
                return null;
            }
        }
    },

    _uploadAll: function(params){
         jQuery(".qq-upload-spinner").each(function (i) {
            jQuery(this).removeClass('hidden');
        });
        for (key in this._inputs) {
            this.upload(key, params);
        }

    },

    _upload: function(id, params){
        var input = this._inputs[id];

        if (!input){
            throw new Error('file with passed id was not added, or already uploaded or cancelled');
        }

        var fileName = this.getName(id);

        var iframe = this._createIframe(id);
        var form = this._createForm(iframe, params);
        form.appendChild(input);

        var nameInput = qq.toElement('<input name="mediaid" value="' + fileName + '" type="text">');
        form.appendChild(nameInput);

        var checked = jQuery('.dw__ow').is(':checked');
        var owCheckbox = jQuery('.dw__ow').clone();
        owCheckbox.attr('checked', checked);
        jQuery(form).append(owCheckbox);

        var self = this;
        this._attachLoadEvent(iframe, function(){
            self.log('iframe loaded');

            var response = self._getIframeContentJSON(iframe);

            self._options.onComplete(id, fileName, response);
            self._dequeue(id);

            delete self._inputs[id];
            // timeout added to fix busy state in FF3.6
            setTimeout(function(){
                qq.remove(iframe);
            }, 1);
        });

        form.submit();
        qq.remove(form);

        return id;
    }
});

qq.extend(qq.UploadHandlerXhr.prototype, {
    uploadAll: function(params){
        this._uploadAll(params);
    },

    getName: function(id){
        var file = this._files[id];
        var name = document.getElementById('mediamanager__upload_item'+id);
        if (name != null) {
            return name.value;
        } else {
            if (file != null) {
                // fix missing name in Safari 4
                return file.fileName != null ? file.fileName : file.name;
            } else {
                return null;
            }
        }
    },

    getSize: function(id){
        var file = this._files[id];
        if (file == null) return null;
        return file.fileSize != null ? file.fileSize : file.size;
    },

    _upload: function(id, params){
        var file = this._files[id],
            name = this.getName(id),
            size = this.getSize(id);
        if (name == null || size == null) return;

        this._loaded[id] = 0;

        var xhr = this._xhrs[id] = new XMLHttpRequest();
        var self = this;

        xhr.upload.onprogress = function(e){
            if (e.lengthComputable){
                self._loaded[id] = e.loaded;
                self._options.onProgress(id, name, e.loaded, e.total);
            }
        };

        xhr.onreadystatechange = function(){
            if (xhr.readyState == 4){
                self._onComplete(id, xhr);
            }
        };

        // build query string
        params = params || {};
        params['qqfile'] = name;
        params['ow'] = jQuery('.dw__ow').is(':checked');
        var queryString = qq.obj2url(params, this._options.action);

        xhr.open("POST", queryString, true);
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.setRequestHeader("X-File-Name", encodeURIComponent(name));
        xhr.setRequestHeader("Content-Type", "application/octet-stream");
        xhr.send(file);
    },

    _uploadAll: function(params){
        jQuery(".qq-upload-spinner").each(function (i) {
            jQuery(this).removeClass('hidden');
        });
        for (key in this._files) {
            this.upload(key, params);
        }

    }
});
