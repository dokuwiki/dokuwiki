/**
 * Flash Multi Upload
 *
 * Based on a example from Alastair Dawson
 *
 * @link http://blog.vixiom.com/2006/09/08/multiple-file-upload-with-flash-and-ruby-on-rails/
 * @author Alastair Dawson
 * @author Andreas Gohr <andi@splitbrain.org>
 * @license MIT <http://www.opensource.org/licenses/mit-license.php>
 */

// delegate
import mx.utils.Delegate;
// ui components
import mx.controls.DataGrid;
import mx.controls.gridclasses.DataGridColumn
import mx.controls.Button;
import mx.controls.TextInput;
import mx.controls.CheckBox;
import mx.controls.Label;
// file reference
import flash.net.FileReferenceList;
import flash.net.FileReference;

class MultipleUpload {

    private var fileRef:FileReferenceList;
    private var fileRefListener:Object;
    private var list:Array;
    private var dp:Array;

    private var files_dg:DataGrid;
    private var browse_btn:Button;
    private var upload_btn:Button;
    private var ns_input:TextInput;
    private var ns_label:Label;
    private var overwrite_cb:CheckBox;

    private var url:String;
    private var upurl:String;
    private var current:Number;
    private var done:Number;
    private var lasterror:String;

    /**
     * Constructor.
     *
     * Initializes the needed objects and stage objects
     */
    public function MultipleUpload(fdg:DataGrid, bb:Button, ub:Button, nsi:TextInput, nsl:Label, ob:CheckBox) {
        // references for objects on the stage
        files_dg = fdg;
        browse_btn = bb;
        upload_btn = ub;
        ns_input = nsi;
        ns_label = nsl;
        overwrite_cb = ob;

        // file list references & listener
        fileRef = new FileReferenceList();
        fileRefListener = new Object();
        fileRef.addListener(fileRefListener);

        // setup
        iniUI();
        inifileRefListener();
    }

    /**
     * Initializes the User Interface
     *
     * Uses flashvars to access possibly localized names
     */
    private function iniUI() {
        // register button handlers
        browse_btn.onRelease = Delegate.create(this, this.browse);
        upload_btn.onRelease = Delegate.create(this, this.upload);

        // columns for dataGrid
        var col:DataGridColumn;
        col = new DataGridColumn('name');
        col.headerText = ( _root.L_gridname ? _root.L_gridname : 'Filename' );
        col.sortable = false;
        files_dg.addColumn(col);
        col = new DataGridColumn('size');
        col.headerText = ( _root.L_gridsize ? _root.L_gridsize : 'Size' );
        col.sortable = false;
        files_dg.addColumn(col);
        col = new DataGridColumn('status');
        col.headerText = ( _root.L_gridstat ? _root.L_gridstat : 'Status' );
        col.sortable = false;
        files_dg.addColumn(col);

        // label translations
        if(_root.L_overwrite) overwrite_cb.label = _root.L_overwrite;
        if(_root.L_browse)    browse_btn.label   = _root.L_browse;
        if(_root.L_upload)    upload_btn.label   = _root.L_upload;
        if(_root.L_namespace) ns_label.text     = _root.L_namespace;

        // prefill input field
        if(_root.O_ns) ns_input.text = _root.O_ns;

        // disable buttons
        upload_btn.enabled = false;
        if(!_root.O_overwrite) overwrite_cb.visible = false;

        // initalize the data provider list
        dp   = new Array();
        list = new Array();
        files_dg.spaceColumnsEqually();
    }

    /**
     * Open files selection dialog
     *
     * Adds the allowed file types
     */
    private function browse() {
        if(_root.O_extensions){
            var exts:Array = _root.O_extensions.split('|');
            var filter:Object = new Object();
            filter.description = (_root.L_filetypes ? _root.L_filetypes : 'Allowed filetypes');
            filter.extension   = '';
            for(var i:Number = 0; i<exts.length; i++){
                filter.extension += '*.'+exts[i]+';';
            }
            filter.extension = filter.extension.substr(0,filter.extension.length-1);
            var apply:Array = new Array();
            apply.push(filter);
            fileRef.browse(apply);
        }else{
            fileRef.browse();
        }
    }

    /**
     * Initiates the upload process
     */
    private function upload() {
        // prepare backend URL
        this.url  = _root.O_backend; // from flashvars
        this.url += '&ns='+escape(ns_input.text);

        // prepare upload url
        this.upurl = this.url;
        this.upurl += '&sectok='+escape(_root.O_sectok);
        this.upurl += '&authtok='+escape(_root.O_authtok);
        if(overwrite_cb.selected) this.upurl += '&ow=1';

        // disable buttons
        upload_btn.enabled = false;
        browse_btn.enabled = false;
        ns_input.enabled = false;
        overwrite_cb.enabled = false;

        // init states
        this.current = -1;
        this.done = 0;
        this.lasterror = '';

        // start process detached
        _global.setTimeout(this,'uploadNext',100);
        nextFrame();
    }

    /**
     * Uploads the next file in the list
     */
    private function uploadNext(){
        this.current++;
        if(this.current >= this.list.length){
            return this.uploadDone();
        }

        var file = this.list[this.current];

        if(_root.O_maxsize && (file.size > _root.O_maxsize)){
            this.lasterror = (_root.L_toobig ? _root.L_toobig : 'too big');
            _global.setTimeout(this,'uploadNext',100);
            nextFrame();
        }else{
            file.addListener(fileRefListener);
            file.upload(upurl);
            // continues in the handlers
        }
    }

    /**
     * Redirect to the namespace and set a success/error message
     *
     * Called when all files in the list where processed
     */
    private function uploadDone(){
        var info = (_root.L_info ? _root.L_info : 'files uploaded');
        if(this.done == this.list.length){
            this.url += '&msg1='+escape(this.done+'/'+this.list.length+' '+info);
        }else{
            var lasterr = (_root.L_lasterr ? _root.L_lasterr : 'Last error:');
            this.url += '&err='+escape(this.done+'/'+this.list.length+' '+info+' '+lasterr+' '+this.lasterror);
        }

        // when done redirect
        getURL(this.url,'_self');
    }

    /**
     * Set the status of a given file in the data grid
     */
    private function setStatus(file,msg){
        for(var i:Number = 0; i < list.length; i++) {
            if (list[i].name == file.name) {
                files_dg.editField(i, 'status', msg);
                nextFrame();
                return;
            }
        }
    }

    /**
     * Initialize the file reference listener
     */
    private function inifileRefListener() {
        fileRefListener.onSelect        = Delegate.create(this, this.onSelect);
        fileRefListener.onCancel        = Delegate.create(this, this.onCancel);
        fileRefListener.onOpen          = Delegate.create(this, this.onOpen);
        fileRefListener.onProgress      = Delegate.create(this, this.onProgress);
        fileRefListener.onComplete      = Delegate.create(this, this.onComplete);
        fileRefListener.onHTTPError     = Delegate.create(this, this.onHTTPError);
        fileRefListener.onIOError       = Delegate.create(this, this.onIOError);
        fileRefListener.onSecurityError = Delegate.create(this, this.onSecurityError);
    }

    /**
     * Handle file selection
     *
     * Files are added as in a list of references and beautified into the data grid dataprovider array
     *
     * Multiple browses will add to the list
     */
    private function onSelect(fileRefList:FileReferenceList) {
        var sel = fileRefList.fileList;
        for(var i:Number = 0; i < sel.length; i++) {
            // check size
            var stat:String;
            if(_root.O_maxsize && sel[i].size > _root.O_maxsize){
                stat = (_root.L_toobig ? _root.L_toobig : 'too big');
            }else{
                stat = (_root.L_ready ? _root.L_ready : 'ready for upload');
            }
            // add to grid
            dp.push({name:sel[i].name, size:Math.round(sel[i].size / 1000) + " kb", status:stat});
            // add to reference list
            list.push(sel[i]);
        }
        // update dataGrid
        files_dg.dataProvider = dp;
        files_dg.spaceColumnsEqually();

        if(list.length > 0) upload_btn.enabled = true;
    }

    /**
     * Does nothing
     */
    private function onCancel() {
    }

    /**
     * Does nothing
     */
    private function onOpen(file:FileReference) {
    }

    /**
     * Set the upload progress
     */
    private function onProgress(file:FileReference, bytesLoaded:Number, bytesTotal:Number) {
        var percentDone = Math.round((bytesLoaded / bytesTotal) * 100);
        var msg:String = 'uploading @PCT@%';
        if(_root.L_progress) msg = _root.L_progress;
        msg = msg.split('@PCT@').join(percentDone);
        this.setStatus(file,msg);
    }

    /**
     * Handle upload completion
     */
    private function onComplete(file:FileReference) {
        this.setStatus(file,(_root.L_done ? _root.L_done : 'complete'));
        this.done++;
        uploadNext();
    }

    /**
     * Handle upload errors
     */
    private function onHTTPError(file:FileReference, httpError:Number) {
        var error;
        if(httpError == 400){
            error = (_root.L_fail ? _root.L_fail : 'failed');
        }else if(httpError == 401){
            error = (_root.L_authfail ? _root.L_authfail : 'auth failed');
        }else{
            error = "HTTP Error " + httpError
        }
        this.setStatus(file,error);
        this.lasterror = error;
        uploadNext();
    }

    /**
     * Handle IO errors
     */
    private function onIOError(file:FileReference) {
        this.setStatus(file,"IO Error");
        this.lasterror = "IO Error";
        uploadNext();
    }

    /**
     * Handle Security errors
     */
    private function onSecurityError(file:FileReference, errorString:String) {
        this.setStatus(file,"SecurityError: " + errorString);
        this.lasterror = "SecurityError: " + errorString;
        uploadNext();
    }


}
