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
        qq.setText(fileElement, this._formatFileName(fileName));
        this._find(item, 'size').style.display = 'none';

        var nameElement = this._find(item, 'nameInput');
        nameElement.value = this._formatFileName(fileName);
        nameElement.id = id;

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
                '<div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>' +
                '<div class="qq-upload-button">Upload a file</div>' +
                '<ul class="qq-upload-list"></ul>' +
                '<input class="button" type="submit" value="Upload" id="mediamanager__upload_button">' +
             '</div>',

        // template for one item in file list
        fileTemplate: '<li>' +
                '<span class="qq-upload-file"></span>' +
                '<label><span>Upload as (optional):</span><input class="qq-upload-name-input" type="text"></label>' +
                '<span class="qq-upload-spinner-hidden"></span>' +
                '<span class="qq-upload-size"></span>' +
                '<a class="qq-upload-cancel" href="#">Cancel</a>' +
                '<span class="qq-upload-failed-text">Failed</span>' +
            '</li>',

        classes: {
            // used to get elements from templates
            button: 'qq-upload-button',
            drop: 'qq-upload-drop-area',
            dropActive: 'qq-upload-drop-area-active',
            list: 'qq-upload-list',
            nameInput: 'qq-upload-name-input',
            file: 'qq-upload-file',

            spinner: 'qq-upload-spinner',
            size: 'qq-upload-size',
            cancel: 'qq-upload-cancel',

            // added to list item when upload completes
            // used in css to hide progress spinner
            success: 'qq-upload-success',
            fail: 'qq-upload-fail'
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

        });
    }

});

qq.extend(qq.UploadHandlerForm.prototype, {
    uploadAll: function(params){
        this._uploadAll(params);
    },

    _uploadAll: function(params){
        for (key in this._inputs) {
            this.upload(key, params);
        }

    }
});

qq.extend(qq.UploadHandlerXhr.prototype, {
    uploadAll: function(params){
        this._uploadAll(params);
    },

    getName: function(id){
        var file = this._files[id];
        var name = document.getElementById(id);
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
        var queryString = qq.obj2url(params, this._options.action);

        xhr.open("POST", queryString, true);
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.setRequestHeader("X-File-Name", encodeURIComponent(name));
        xhr.setRequestHeader("Content-Type", "application/octet-stream");
        xhr.send(file);
    },

    _uploadAll: function(params){
        jQuery(".qq-upload-spinner-hidden").each(function (i) {
            jQuery(this).addClass('qq-upload-spinner');
        });
        for (key in this._files) {
            this.upload(key, params);
        }

    }
});
