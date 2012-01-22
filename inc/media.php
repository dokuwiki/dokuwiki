<?php
/**
 * All output and handler function needed for the media management popup
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) die('meh.');
if(!defined('NL')) define('NL',"\n");

/**
 * Lists pages which currently use a media file selected for deletion
 *
 * References uses the same visual as search results and share
 * their CSS tags except pagenames won't be links.
 *
 * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
 */
function media_filesinuse($data,$id){
    global $lang;
    echo '<h1>'.$lang['reference'].' <code>'.hsc(noNS($id)).'</code></h1>';
    echo '<p>'.hsc($lang['ref_inuse']).'</p>';

    $hidden=0; //count of hits without read permission
    foreach($data as $row){
        if(auth_quickaclcheck($row) >= AUTH_READ && isVisiblePage($row)){
            echo '<div class="search_result">';
            echo '<span class="mediaref_ref">'.hsc($row).'</span>';
            echo '</div>';
        }else
            $hidden++;
    }
    if ($hidden){
        print '<div class="mediaref_hidden">'.$lang['ref_hidden'].'</div>';
    }
}

/**
 * Handles the saving of image meta data
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function media_metasave($id,$auth,$data){
    if($auth < AUTH_UPLOAD) return false;
    if(!checkSecurityToken()) return false;
    global $lang;
    global $conf;
    $src = mediaFN($id);

    $meta = new JpegMeta($src);
    $meta->_parseAll();

    foreach($data as $key => $val){
        $val=trim($val);
        if(empty($val)){
            $meta->deleteField($key);
        }else{
            $meta->setField($key,$val);
        }
    }

    $old = @filemtime($src);
    if(!@file_exists(mediaFN($id, $old)) && @file_exists($src)) {
        // add old revision to the attic
        media_saveOldRevision($id);
    }

    if($meta->save()){
        if($conf['fperm']) chmod($src, $conf['fperm']);

        $new = @filemtime($src);
        // add a log entry to the media changelog
        addMediaLogEntry($new, $id, DOKU_CHANGE_TYPE_EDIT, $lang['media_meta_edited']);

        msg($lang['metasaveok'],1);
        return $id;
    }else{
        msg($lang['metasaveerr'],-1);
        return false;
    }
}

/**
 * Display the form to edit image meta data
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function media_metaform($id,$auth){
    global $lang, $config_cascade;

    if($auth < AUTH_UPLOAD) {
        echo '<div class="nothing">'.$lang['media_perm_upload'].'</div>'.NL;
        return false;
    }

    // load the field descriptions
    static $fields = null;
    if(is_null($fields)){
        $config_files = getConfigFiles('mediameta');
        foreach ($config_files as $config_file) {
            if(@file_exists($config_file)) include($config_file);
        }
    }

    $src = mediaFN($id);

    // output
    $form = new Doku_Form(array('action' => media_managerURL(array('tab_details' => 'view'), '&'),
                                'class' => 'meta'));
    $form->addHidden('img', $id);
    $form->addHidden('mediado', 'save');
    foreach($fields as $key => $field){
        // get current value
        if (empty($field[0])) continue;
        $tags = array($field[0]);
        if(is_array($field[3])) $tags = array_merge($tags,$field[3]);
        $value = tpl_img_getTag($tags,'',$src);
        $value = cleanText($value);

        // prepare attributes
        $p = array();
        $p['class'] = 'edit';
        $p['id']    = 'meta__'.$key;
        $p['name']  = 'meta['.$field[0].']';
        $p_attrs    = array('class' => 'edit');

        $form->addElement('<div class="row">');
        if($field[2] == 'text'){
            $form->addElement(form_makeField('text', $p['name'], $value, ($lang[$field[1]]) ? $lang[$field[1]] : $field[1] . ':', $p['id'], $p['class'], $p_attrs));
        }else{
            $att = buildAttributes($p);
            $form->addElement('<label for="meta__'.$key.'">'.$lang[$field[1]].'</label>');
            $form->addElement("<textarea $att rows=\"6\" cols=\"50\">".formText($value).'</textarea>');
        }
        $form->addElement('</div>'.NL);
    }
    $form->addElement('<div class="buttons">');
    $form->addElement(form_makeButton('submit', '', $lang['btn_save'], array('accesskey' => 's', 'name' => 'mediado[save]')));
    $form->addElement('</div>'.NL);
    $form->printForm();
}

/**
 * Convenience function to check if a media file is still in use
 *
 * @author Michael Klier <chi@chimeric.de>
 */
function media_inuse($id) {
    global $conf;
    $mediareferences = array();
    if($conf['refcheck']){
        $mediareferences = ft_mediause($id,$conf['refshow']);
        if(!count($mediareferences)) {
            return false;
        } else {
            return $mediareferences;
        }
    } else {
        return false;
    }
}

define('DOKU_MEDIA_DELETED', 1);
define('DOKU_MEDIA_NOT_AUTH', 2);
define('DOKU_MEDIA_INUSE', 4);
define('DOKU_MEDIA_EMPTY_NS', 8);

/**
 * Handles media file deletions
 *
 * If configured, checks for media references before deletion
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @return int One of: 0,
                       DOKU_MEDIA_DELETED,
                       DOKU_MEDIA_DELETED | DOKU_MEDIA_EMPTY_NS,
                       DOKU_MEDIA_NOT_AUTH,
                       DOKU_MEDIA_INUSE
 */
function media_delete($id,$auth){
    global $lang;
    if($auth < AUTH_DELETE) return DOKU_MEDIA_NOT_AUTH;
    if(media_inuse($id)) return DOKU_MEDIA_INUSE;

    $file = mediaFN($id);

    // trigger an event - MEDIA_DELETE_FILE
    $data['id']   = $id;
    $data['name'] = basename($file);
    $data['path'] = $file;
    $data['size'] = (@file_exists($file)) ? filesize($file) : 0;

    $data['unl'] = false;
    $data['del'] = false;
    $evt = new Doku_Event('MEDIA_DELETE_FILE',$data);
    if ($evt->advise_before()) {
        $old = @filemtime($file);
        if(!@file_exists(mediaFN($id, $old)) && @file_exists($file)) {
            // add old revision to the attic
            media_saveOldRevision($id);
        }

        $data['unl'] = @unlink($file);
        if($data['unl']){
            addMediaLogEntry(time(), $id, DOKU_CHANGE_TYPE_DELETE, $lang['deleted']);
            $data['del'] = io_sweepNS($id,'mediadir');
        }
    }
    $evt->advise_after();
    unset($evt);

    if($data['unl'] && $data['del']){
        return DOKU_MEDIA_DELETED | DOKU_MEDIA_EMPTY_NS;
    }

    return $data['unl'] ? DOKU_MEDIA_DELETED : 0;
}

/**
 * Handle file uploads via XMLHttpRequest
 *
 * @return mixed false on error, id of the new file on success
 */
function media_upload_xhr($ns,$auth){
    if(!checkSecurityToken()) return false;

    $id = $_GET['qqfile'];
    list($ext,$mime,$dl) = mimetype($id);
    $input = fopen("php://input", "r");
    if (!($tmp = io_mktmpdir())) return false;
    $path = $tmp.'/'.md5($id);
    $target = fopen($path, "w");
    $realSize = stream_copy_to_stream($input, $target);
    fclose($target);
    fclose($input);
    if ($realSize != (int)$_SERVER["CONTENT_LENGTH"]){
        unlink($target);
        unlink($path);
        return false;
    }

    $res = media_save(
        array('name' => $path,
            'mime' => $mime,
            'ext'  => $ext),
        $ns.':'.$id,
        (($_REQUEST['ow'] == 'checked') ? true : false),
        $auth,
        'copy'
    );
    unlink($path);
    if ($tmp) dir_delete($tmp);
    if (is_array($res)) {
        msg($res[0], $res[1]);
        return false;
    }
    return $res;
}

/**
 * Handles media file uploads
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Michael Klier <chi@chimeric.de>
 * @return mixed false on error, id of the new file on success
 */
function media_upload($ns,$auth,$file=false){
    if(!checkSecurityToken()) return false;
    global $lang;

    // get file and id
    $id   = $_POST['mediaid'];
    if (!$file) $file = $_FILES['upload'];
    if(empty($id)) $id = $file['name'];

    // check for errors (messages are done in lib/exe/mediamanager.php)
    if($file['error']) return false;

    // check extensions
    list($fext,$fmime,$dl) = mimetype($file['name']);
    list($iext,$imime,$dl) = mimetype($id);
    if($fext && !$iext){
        // no extension specified in id - read original one
        $id   .= '.'.$fext;
        $imime = $fmime;
    }elseif($fext && $fext != $iext){
        // extension was changed, print warning
        msg(sprintf($lang['mediaextchange'],$fext,$iext));
    }

    $res = media_save(array('name' => $file['tmp_name'],
                            'mime' => $imime,
                            'ext'  => $iext), $ns.':'.$id,
                      $_REQUEST['ow'], $auth, 'move_uploaded_file');
    if (is_array($res)) {
        msg($res[0], $res[1]);
        return false;
    }
    return $res;
}

/**
 * This generates an action event and delegates to _media_upload_action().
 * Action plugins are allowed to pre/postprocess the uploaded file.
 * (The triggered event is preventable.)
 *
 * Event data:
 * $data[0]     fn_tmp: the temporary file name (read from $_FILES)
 * $data[1]     fn: the file name of the uploaded file
 * $data[2]     id: the future directory id of the uploaded file
 * $data[3]     imime: the mimetype of the uploaded file
 * $data[4]     overwrite: if an existing file is going to be overwritten
 *
 * @triggers MEDIA_UPLOAD_FINISH
 */
function media_save($file, $id, $ow, $auth, $move) {
    if($auth < AUTH_UPLOAD) {
        return array("You don't have permissions to upload files.", -1);
    }

    if (!isset($file['mime']) || !isset($file['ext'])) {
        list($ext, $mime) = mimetype($id);
        if (!isset($file['mime'])) {
            $file['mime'] = $mime;
        }
        if (!isset($file['ext'])) {
            $file['ext'] = $ext;
        }
    }

    global $lang, $conf;

    // get filename
    $id   = cleanID($id);
    $fn   = mediaFN($id);

    // get filetype regexp
    $types = array_keys(getMimeTypes());
    $types = array_map(create_function('$q','return preg_quote($q,"/");'),$types);
    $regex = join('|',$types);

    // because a temp file was created already
    if(!preg_match('/\.('.$regex.')$/i',$fn)) {
        return array($lang['uploadwrong'],-1);
    }

    //check for overwrite
    $overwrite = @file_exists($fn);
    $auth_ow = (($conf['mediarevisions']) ? AUTH_UPLOAD : AUTH_DELETE);
    if($overwrite && (!$ow || $auth < $auth_ow)) {
        return array($lang['uploadexist'], 0);
    }
    // check for valid content
    $ok = media_contentcheck($file['name'], $file['mime']);
    if($ok == -1){
        return array(sprintf($lang['uploadbadcontent'],'.' . $file['ext']),-1);
    }elseif($ok == -2){
        return array($lang['uploadspam'],-1);
    }elseif($ok == -3){
        return array($lang['uploadxss'],-1);
    }

    // prepare event data
    $data[0] = $file['name'];
    $data[1] = $fn;
    $data[2] = $id;
    $data[3] = $file['mime'];
    $data[4] = $overwrite;
    $data[5] = $move;

    // trigger event
    return trigger_event('MEDIA_UPLOAD_FINISH', $data, '_media_upload_action', true);
}

/**
 * Callback adapter for media_upload_finish()
 * @author Michael Klier <chi@chimeric.de>
 */
function _media_upload_action($data) {
    // fixme do further sanity tests of given data?
    if(is_array($data) && count($data)===6) {
        return media_upload_finish($data[0], $data[1], $data[2], $data[3], $data[4], $data[5]);
    } else {
        return false; //callback error
    }
}

/**
 * Saves an uploaded media file
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Michael Klier <chi@chimeric.de>
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function media_upload_finish($fn_tmp, $fn, $id, $imime, $overwrite, $move = 'move_uploaded_file') {
    global $conf;
    global $lang;
    global $REV;

    $old = @filemtime($fn);
    if(!@file_exists(mediaFN($id, $old)) && @file_exists($fn)) {
        // add old revision to the attic if missing
        media_saveOldRevision($id);
    }

    // prepare directory
    io_createNamespace($id, 'media');

    if($move($fn_tmp, $fn)) {
        @clearstatcache(true,$fn);
        $new = @filemtime($fn);
        // Set the correct permission here.
        // Always chmod media because they may be saved with different permissions than expected from the php umask.
        // (Should normally chmod to $conf['fperm'] only if $conf['fperm'] is set.)
        chmod($fn, $conf['fmode']);
        msg($lang['uploadsucc'],1);
        media_notify($id,$fn,$imime,$old);
        // add a log entry to the media changelog
        if ($REV){
            addMediaLogEntry($new, $id, DOKU_CHANGE_TYPE_REVERT, $lang['restored'], $REV);
        } elseif ($overwrite) {
            addMediaLogEntry($new, $id, DOKU_CHANGE_TYPE_EDIT);
        } else {
            addMediaLogEntry($new, $id, DOKU_CHANGE_TYPE_CREATE, $lang['created']);
        }
        return $id;
    }else{
        return array($lang['uploadfail'],-1);
    }
}

/**
 * Moves the current version of media file to the media_attic
 * directory
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 * @param string $id
 * @return int - revision date
 */
function media_saveOldRevision($id){
    global $conf, $lang;

    $oldf = mediaFN($id);
    if(!@file_exists($oldf)) return '';
    $date = filemtime($oldf);
    if (!$conf['mediarevisions']) return $date;

    if (!getRevisionInfo($id, $date, 8192, true)) {
        // there was an external edit,
        // there is no log entry for current version of file
        if (!@file_exists(mediaMetaFN($id,'.changes'))) {
            addMediaLogEntry($date, $id, DOKU_CHANGE_TYPE_CREATE, $lang['created']);
        } else {
            addMediaLogEntry($date, $id, DOKU_CHANGE_TYPE_EDIT);
        }
    }

    $newf = mediaFN($id,$date);
    io_makeFileDir($newf);
    if(copy($oldf, $newf)) {
        // Set the correct permission here.
        // Always chmod media because they may be saved with different permissions than expected from the php umask.
        // (Should normally chmod to $conf['fperm'] only if $conf['fperm'] is set.)
        chmod($newf, $conf['fmode']);
    }
    return $date;
}

/**
 * This function checks if the uploaded content is really what the
 * mimetype says it is. We also do spam checking for text types here.
 *
 * We need to do this stuff because we can not rely on the browser
 * to do this check correctly. Yes, IE is broken as usual.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @link   http://www.splitbrain.org/blog/2007-02/12-internet_explorer_facilitates_cross_site_scripting
 * @fixme  check all 26 magic IE filetypes here?
 */
function media_contentcheck($file,$mime){
    global $conf;
    if($conf['iexssprotect']){
        $fh = @fopen($file, 'rb');
        if($fh){
            $bytes = fread($fh, 256);
            fclose($fh);
            if(preg_match('/<(script|a|img|html|body|iframe)[\s>]/i',$bytes)){
                return -3;
            }
        }
    }
    if(substr($mime,0,6) == 'image/'){
        $info = @getimagesize($file);
        if($mime == 'image/gif' && $info[2] != 1){
            return -1;
        }elseif($mime == 'image/jpeg' && $info[2] != 2){
            return -1;
        }elseif($mime == 'image/png' && $info[2] != 3){
            return -1;
        }
        # fixme maybe check other images types as well
    }elseif(substr($mime,0,5) == 'text/'){
        global $TEXT;
        $TEXT = io_readFile($file);
        if(checkwordblock()){
            return -2;
        }
    }
    return 0;
}

/**
 * Send a notify mail on uploads
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function media_notify($id,$file,$mime,$old_rev=false){
    global $lang;
    global $conf;
    global $INFO;
    if(empty($conf['notify'])) return; //notify enabled?

    $ip = clientIP();

    $text = rawLocale('uploadmail');
    $text = str_replace('@DATE@',dformat(),$text);
    $text = str_replace('@BROWSER@',$_SERVER['HTTP_USER_AGENT'],$text);
    $text = str_replace('@IPADDRESS@',$ip,$text);
    $text = str_replace('@HOSTNAME@',gethostsbyaddrs($ip),$text);
    $text = str_replace('@DOKUWIKIURL@',DOKU_URL,$text);
    $text = str_replace('@USER@',$_SERVER['REMOTE_USER'],$text);
    $text = str_replace('@MIME@',$mime,$text);
    $text = str_replace('@MEDIA@',ml($id,'',true,'&',true),$text);
    $text = str_replace('@SIZE@',filesize_h(filesize($file)),$text);
    if ($old_rev && $conf['mediarevisions']) {
        $text = str_replace('@OLD@', ml($id, "rev=$old_rev", true, '&', true), $text);
    } else {
        $text = str_replace('@OLD@', '', $text);
    }

    if(empty($conf['mailprefix'])) {
        $subject = '['.$conf['title'].'] '.$lang['mail_upload'].' '.$id;
    } else {
        $subject = '['.$conf['mailprefix'].'] '.$lang['mail_upload'].' '.$id;
    }

    mail_send($conf['notify'],$subject,$text,$conf['mailfrom']);
}

/**
 * List all files in a given Media namespace
 */
function media_filelist($ns,$auth=null,$jump='',$fullscreenview=false,$sort=false){
    global $conf;
    global $lang;
    $ns = cleanID($ns);

    // check auth our self if not given (needed for ajax calls)
    if(is_null($auth)) $auth = auth_quickaclcheck("$ns:*");

    if (!$fullscreenview) echo '<h1 id="media__ns">:'.hsc($ns).'</h1>'.NL;

    if($auth < AUTH_READ){
        // FIXME: print permission warning here instead?
        echo '<div class="nothing">'.$lang['nothingfound'].'</div>'.NL;
    }else{
        if (!$fullscreenview) media_uploadform($ns, $auth);

        $dir = utf8_encodeFN(str_replace(':','/',$ns));
        $data = array();
        search($data,$conf['mediadir'],'search_media',
                array('showmsg'=>true,'depth'=>1),$dir,1,$sort);

        if(!count($data)){
            echo '<div class="nothing">'.$lang['nothingfound'].'</div>'.NL;
        }else {
            if ($fullscreenview) {
                echo '<ul class="' . _media_get_list_type() . '">';
            }
            foreach($data as $item){
                if (!$fullscreenview) {
                    media_printfile($item,$auth,$jump);
                } else {
                    media_printfile_thumbs($item,$auth,$jump);
                }
            }
            if ($fullscreenview) echo '</ul>'.NL;
        }
    }
    if (!$fullscreenview) media_searchform($ns);
}

/**
 * Prints tabs for files list actions
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 * @author Adrian Lang <mail@adrianlang.de>
 *
 * @param string $selected_tab - opened tab
 */

function media_tabs_files($selected_tab = ''){
    global $lang;
    $tabs = array();
    foreach(array('files'  => 'mediaselect',
                  'upload' => 'media_uploadtab',
                  'search' => 'media_searchtab') as $tab => $caption) {
        $tabs[$tab] = array('href'    => media_managerURL(array('tab_files' => $tab), '&'),
                            'caption' => $lang[$caption]);
    }

    html_tabs($tabs, $selected_tab);
}

/**
 * Prints tabs for files details actions
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 * @param string $selected_tab - opened tab
 */
function media_tabs_details($image, $selected_tab = ''){
    global $lang, $conf;

    $tabs = array();
    $tabs['view'] = array('href'    => media_managerURL(array('tab_details' => 'view'), '&'),
                          'caption' => $lang['media_viewtab']);

    list($ext, $mime) = mimetype($image);
    if ($mime == 'image/jpeg' && @file_exists(mediaFN($image))) {
        $tabs['edit'] = array('href'    => media_managerURL(array('tab_details' => 'edit'), '&'),
                              'caption' => $lang['media_edittab']);
    }
    if ($conf['mediarevisions']) {
        $tabs['history'] = array('href'    => media_managerURL(array('tab_details' => 'history'), '&'),
                                 'caption' => $lang['media_historytab']);
    }

    html_tabs($tabs, $selected_tab);
}

/**
 * Prints options for the tab that displays a list of all files
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function media_tab_files_options(){
    global $lang, $NS;
    $form = new Doku_Form(array('class' => 'options', 'method' => 'get',
                                'action' => wl($ID)));
    $media_manager_params = media_managerURL(array(), '', false, true);
    foreach($media_manager_params as $pKey => $pVal){
        $form->addHidden($pKey, $pVal);
    }
    $form->addHidden('sectok', null);
    if (isset($_REQUEST['q'])) {
        $form->addHidden('q', $_REQUEST['q']);
    }
    $form->addElement('<ul>'.NL);
    foreach(array('list' => array('listType', array('thumbs', 'rows')),
                  'sort' => array('sortBy', array('name', 'date')))
            as $group => $content) {
        $checked = "_media_get_${group}_type";
        $checked = $checked();

        $form->addElement('<li class="' . $content[0] . '">');
        foreach($content[1] as $option) {
            $attrs = array();
            if ($checked == $option) {
                $attrs['checked'] = 'checked';
            }
            $form->addElement(form_makeRadioField($group, $option,
                                       $lang['media_' . $group . '_' . $option],
                                                  $content[0] . '__' . $option,
                                                  $option, $attrs));
        }
        $form->addElement('</li>'.NL);
    }
    $form->addElement('<li>');
    $form->addElement(form_makeButton('submit', '', $lang['btn_apply']));
    $form->addElement('</li>'.NL);
    $form->addElement('</ul>'.NL);
    $form->printForm();
}

/**
 * Returns type of sorting for the list of files in media manager
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 * @return string - sort type
 */
function _media_get_sort_type() {
    return _media_get_display_param('sort', array('default' => 'name', 'date'));
}

function _media_get_list_type() {
    return _media_get_display_param('list', array('default' => 'thumbs', 'rows'));
}

function _media_get_display_param($param, $values) {
    if (isset($_REQUEST[$param]) && in_array($_REQUEST[$param], $values)) {
        // FIXME: Set cookie
        return $_REQUEST[$param];
    } else {
        $val = get_doku_pref($param, $values['default']);
        if (!in_array($val, $values)) {
            $val = $values['default'];
        }
        return $val;
    }
}

/**
 * Prints tab that displays a list of all files
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function media_tab_files($ns,$auth=null,$jump='') {
    global $lang;
    if(is_null($auth)) $auth = auth_quickaclcheck("$ns:*");

    if($auth < AUTH_READ){
        echo '<div class="nothing">'.$lang['media_perm_read'].'</div>'.NL;
    }else{
        media_filelist($ns,$auth,$jump,true,_media_get_sort_type());
    }
}

/**
 * Prints tab that displays uploading form
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function media_tab_upload($ns,$auth=null,$jump='') {
    global $lang;
    if(is_null($auth)) $auth = auth_quickaclcheck("$ns:*");

    echo '<div class="upload">'.NL;
    if ($auth >= AUTH_UPLOAD) {
        echo '<p>' . $lang['mediaupload'] . '</p>';
    }
    media_uploadform($ns, $auth, true);
    echo '</div>'.NL;
}

/**
 * Prints tab that displays search form
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function media_tab_search($ns,$auth=null) {
    global $lang;

    $do = $_REQUEST['mediado'];
    $query = $_REQUEST['q'];
    if (!$query) $query = '';
    echo '<div class="search">'.NL;

    media_searchform($ns, $query, true);
    if ($do == 'searchlist' || $query) {
        media_searchlist($query,$ns,$auth,true,_media_get_sort_type());
    }
    echo '</div>'.NL;
}

/**
 * Prints tab that displays mediafile details
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function media_tab_view($image, $ns, $auth=null, $rev=false) {
    global $lang, $conf;
    if(is_null($auth)) $auth = auth_quickaclcheck("$ns:*");

    if ($image && $auth >= AUTH_READ) {
        $meta = new JpegMeta(mediaFN($image, $rev));
        media_preview($image, $auth, $rev, $meta);
        media_preview_buttons($image, $auth, $rev);
        media_details($image, $auth, $rev, $meta);

    } else {
        echo '<div class="nothing">'.$lang['media_perm_read'].'</div>'.NL;
    }
}

/**
 * Prints tab that displays form for editing mediafile metadata
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function media_tab_edit($image, $ns, $auth=null) {
    global $lang;
    if(is_null($auth)) $auth = auth_quickaclcheck("$ns:*");

    if ($image) {
        list($ext, $mime) = mimetype($image);
        if ($mime == 'image/jpeg') media_metaform($image,$auth);
    }
}

/**
 * Prints tab that displays mediafile revisions
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function media_tab_history($image, $ns, $auth=null) {
    global $lang;
    if(is_null($auth)) $auth = auth_quickaclcheck("$ns:*");
    $do = $_REQUEST['mediado'];

    if ($auth >= AUTH_READ && $image) {
        if ($do == 'diff'){
            media_diff($image, $ns, $auth);
        } else {
            $first = isset($_REQUEST['first']) ? intval($_REQUEST['first']) : 0;
            html_revisions($first, $image);
        }
    } else {
        echo '<div class="nothing">'.$lang['media_perm_read'].'</div>'.NL;
    }
}

/**
 * Prints mediafile details
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function media_preview($image, $auth, $rev=false, $meta=false) {

    $size = media_image_preview_size($image, $rev, $meta);

    if ($size) {
        global $lang;
        echo '<div class="image">';

        $more = array();
        if ($rev) {
            $more['rev'] = $rev;
        } else {
            $t = @filemtime(mediaFN($image));
            $more['t'] = $t;
        }

        $more['w'] = $size[0];
        $more['h'] = $size[1];
        $src = ml($image, $more);

        echo '<a href="'.$src.'" target="_blank" title="'.$lang['mediaview'].'">';
        echo '<img src="'.$src.'" alt="" style="max-width: '.$size[0].'px;" />';
        echo '</a>';

        echo '</div>'.NL;
    }
}

/**
 * Prints mediafile action buttons
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function media_preview_buttons($image, $auth, $rev=false) {
    global $lang, $conf;

    echo '<ul class="actions">'.NL;

    if($auth >= AUTH_DELETE && !$rev && @file_exists(mediaFN($image))){

        // delete button
        $form = new Doku_Form(array('id' => 'mediamanager__btn_delete',
            'action'=>media_managerURL(array('delete' => $image), '&')));
        $form->addElement(form_makeButton('submit','',$lang['btn_delete']));
        echo '<li>';
        $form->printForm();
        echo '</li>'.NL;
    }

    $auth_ow = (($conf['mediarevisions']) ? AUTH_UPLOAD : AUTH_DELETE);
    if($auth >= $auth_ow && !$rev){

        // upload new version button
        $form = new Doku_Form(array('id' => 'mediamanager__btn_update',
            'action'=>media_managerURL(array('image' => $image, 'mediado' => 'update'), '&')));
        $form->addElement(form_makeButton('submit','',$lang['media_update']));
        echo '<li>';
        $form->printForm();
        echo '</li>'.NL;
    }

    if($auth >= AUTH_UPLOAD && $rev && $conf['mediarevisions'] && @file_exists(mediaFN($image, $rev))){

        // restore button
        $form = new Doku_Form(array('id' => 'mediamanager__btn_restore',
            'action'=>media_managerURL(array('image' => $image), '&')));
        $form->addHidden('mediado','restore');
        $form->addHidden('rev',$rev);
        $form->addElement(form_makeButton('submit','',$lang['media_restore']));
        echo '<li>';
        $form->printForm();
        echo '</li>'.NL;
    }

    echo '</ul>'.NL;
}

/**
 * Returns image width and height for mediamanager preview panel
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 * @param string $image
 * @param int $rev
 * @param JpegMeta $meta
 * @return array
 */
function media_image_preview_size($image, $rev, $meta, $size = 500) {
    if (!preg_match("/\.(jpe?g|gif|png)$/", $image) || !file_exists(mediaFN($image, $rev))) return false;

    $info = getimagesize(mediaFN($image, $rev));
    $w = (int) $info[0];
    $h = (int) $info[1];

    if($meta && ($w > $size || $h > $size)){
        $ratio = $meta->getResizeRatio($size, $size);
        $w = floor($w * $ratio);
        $h = floor($h * $ratio);
    }
    return array($w, $h);
}

/**
 * Returns the requested EXIF/IPTC tag from the image meta
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 * @param array $tags
 * @param JpegMeta $meta
 * @param string $alt
 * @return string
 */
function media_getTag($tags,$meta,$alt=''){
    if($meta === false) return $alt;
    $info = $meta->getField($tags);
    if($info == false) return $alt;
    return $info;
}

/**
 * Returns mediafile tags
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 * @param JpegMeta $meta
 * @return array
 */
function media_file_tags($meta) {
    global $config_cascade;

    // load the field descriptions
    static $fields = null;
    if(is_null($fields)){
        $config_files = getConfigFiles('mediameta');
        foreach ($config_files as $config_file) {
            if(@file_exists($config_file)) include($config_file);
        }
    }

    $tags = array();

    foreach($fields as $key => $tag){
        $t = array();
        if (!empty($tag[0])) $t = array($tag[0]);
        if(is_array($tag[3])) $t = array_merge($t,$tag[3]);
        $value = media_getTag($t, $meta);
        $tags[] = array('tag' => $tag, 'value' => $value);
    }

    return $tags;
}

/**
 * Prints mediafile tags
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function media_details($image, $auth, $rev=false, $meta=false) {
    global $lang;

    if (!$meta) $meta = new JpegMeta(mediaFN($image, $rev));
    $tags = media_file_tags($meta);

    echo '<dl>'.NL;
    foreach($tags as $tag){
        if ($tag['value']) {
            $value = cleanText($tag['value']);
            echo '<dt>'.$lang[$tag['tag'][1]].':</dt><dd>';
            if ($tag['tag'][2] == 'date') echo dformat($value);
            else echo hsc($value);
            echo '</dd>'.NL;
        }
    }
    echo '</dl>'.NL;
}

/**
 * Shows difference between two revisions of file
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function media_diff($image, $ns, $auth, $fromajax = false) {
    global $lang;
    global $conf;

    if ($auth < AUTH_READ || !$image || !$conf['mediarevisions']) return '';

    $rev1 = (int) $_REQUEST['rev'];

    if(is_array($_REQUEST['rev2'])){
        $rev1 = (int) $_REQUEST['rev2'][0];
        $rev2 = (int) $_REQUEST['rev2'][1];

        if(!$rev1){
            $rev1 = $rev2;
            unset($rev2);
        }
    }else{
        $rev2 = (int) $_REQUEST['rev2'];
    }

    if ($rev1 && !file_exists(mediaFN($image, $rev1))) $rev1 = false;
    if ($rev2 && !file_exists(mediaFN($image, $rev2))) $rev2 = false;

    if($rev1 && $rev2){            // two specific revisions wanted
        // make sure order is correct (older on the left)
        if($rev1 < $rev2){
            $l_rev = $rev1;
            $r_rev = $rev2;
        }else{
            $l_rev = $rev2;
            $r_rev = $rev1;
        }
    }elseif($rev1){                // single revision given, compare to current
        $r_rev = '';
        $l_rev = $rev1;
    }else{                        // no revision was given, compare previous to current
        $r_rev = '';
        $revs = getRevisions($image, 0, 1, 8192, true);
        if (file_exists(mediaFN($image, $revs[0]))) {
            $l_rev = $revs[0];
        } else {
            $l_rev = '';
        }
    }

    // prepare event data
    $data[0] = $image;
    $data[1] = $l_rev;
    $data[2] = $r_rev;
    $data[3] = $ns;
    $data[4] = $auth;
    $data[5] = $fromajax;

    // trigger event
    return trigger_event('MEDIA_DIFF', $data, '_media_file_diff', true);

}

function _media_file_diff($data) {
    if(is_array($data) && count($data)===6) {
        return media_file_diff($data[0], $data[1], $data[2], $data[3], $data[4], $data[5]);
    } else {
        return false;
    }
}

/**
 * Shows difference between two revisions of image
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function media_file_diff($image, $l_rev, $r_rev, $ns, $auth, $fromajax){
    global $lang, $config_cascade;

    $l_meta = new JpegMeta(mediaFN($image, $l_rev));
    $r_meta = new JpegMeta(mediaFN($image, $r_rev));

    $is_img = preg_match("/\.(jpe?g|gif|png)$/", $image);
    if ($is_img) {
        $l_size = media_image_preview_size($image, $l_rev, $l_meta);
        $r_size = media_image_preview_size($image, $r_rev, $r_meta);
        $is_img = ($l_size && $r_size && ($l_size[0] >= 30 || $r_size[0] >= 30));

        $difftype = $_REQUEST['difftype'];

        if (!$fromajax) {
            $form = new Doku_Form(array(
                'action' => media_managerURL(array(), '&'),
                'method' => 'get',
                'id' => 'mediamanager__form_diffview',
                'class' => 'diffView'
            ));
            $form->addHidden('sectok', null);
            $form->addElement('<input type="hidden" name="rev2[]" value="'.$l_rev.'" ></input>');
            $form->addElement('<input type="hidden" name="rev2[]" value="'.$r_rev.'" ></input>');
            $form->addHidden('mediado', 'diff');
            $form->printForm();

            echo NL.'<div id="mediamanager__diff" >'.NL;
        }

        if ($difftype == 'opacity' || $difftype == 'portions') {
            media_image_diff($image, $l_rev, $r_rev, $l_size, $r_size, $difftype);
            if (!$fromajax) echo '</div>';
            return '';
        }
    }

    list($l_head, $r_head) = html_diff_head($l_rev, $r_rev, $image, true);

    ?>
    <table>
      <tr>
        <th><?php echo $l_head; ?></th>
        <th><?php echo $r_head; ?></th>
      </tr>
    <?php

    echo '<tr class="image">';
    echo '<td>';
    media_preview($image, $auth, $l_rev, $l_meta);
    echo '</td>';

    echo '<td>';
    media_preview($image, $auth, $r_rev, $r_meta);
    echo '</td>';
    echo '</tr>'.NL;

    echo '<tr class="actions">';
    echo '<td>';
    media_preview_buttons($image, $auth, $l_rev);
    echo '</td>';

    echo '<td>';
    media_preview_buttons($image, $auth, $r_rev);
    echo '</td>';
    echo '</tr>'.NL;

    $l_tags = media_file_tags($l_meta);
    $r_tags = media_file_tags($r_meta);
    // FIXME r_tags-only stuff
    foreach ($l_tags as $key => $l_tag) {
        if ($l_tag['value'] != $r_tags[$key]['value']) {
            $r_tags[$key]['highlighted'] = true;
            $l_tags[$key]['highlighted'] = true;
        } else if (!$l_tag['value'] || !$r_tags[$key]['value']) {
            unset($r_tags[$key]);
            unset($l_tags[$key]);
        }
    }

    echo '<tr>';
    foreach(array($l_tags,$r_tags) as $tags){
        echo '<td>'.NL;

        echo '<dl class="img_tags">';
        foreach($tags as $tag){
            $value = cleanText($tag['value']);
            if (!$value) $value = '-';
            echo '<dt>'.$lang[$tag['tag'][1]].':</dt>';
            echo '<dd>';
            if ($tag['highlighted']) {
                echo '<strong>';
            }
            if ($tag['tag'][2] == 'date') echo dformat($value);
            else echo hsc($value);
            if ($tag['highlighted']) {
                echo '</strong>';
            }
            echo '</dd>';
        }
        echo '</dl>'.NL;

        echo '</td>';
    }
    echo '</tr>'.NL;

    echo '</table>'.NL;

    if ($is_img && !$fromajax) echo '</div>';
}

/**
 * Prints two images side by side
 * and slider
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 * @param string $image
 * @param int $l_rev
 * @param int $r_rev
 * @param array $l_size
 * @param array $r_size
 * @param string $type
 */
function media_image_diff($image, $l_rev, $r_rev, $l_size, $r_size, $type) {
    if ($l_size != $r_size) {
        if ($r_size[0] > $l_size[0]) {
            $l_size = $r_size;
        }
    }

    $l_more = array('rev' => $l_rev, 'h' => $l_size[1], 'w' => $l_size[0]);
    $r_more = array('rev' => $r_rev, 'h' => $l_size[1], 'w' => $l_size[0]);

    $l_src = ml($image, $l_more);
    $r_src = ml($image, $r_more);

    // slider
    echo '<div class="slider" style="max-width: '.($l_size[0]-20).'px;" ></div>'.NL;

    // two images in divs
    echo '<div class="imageDiff ' . $type . '">'.NL;
    echo '<div class="image1" style="max-width: '.$l_size[0].'px;">';
    echo '<img src="'.$l_src.'" alt="" />';
    echo '</div>'.NL;
    echo '<div class="image2" style="max-width: '.$l_size[0].'px;">';
    echo '<img src="'.$r_src.'" alt="" />';
    echo '</div>'.NL;
    echo '</div>'.NL;
}

/**
 * Restores an old revision of a media file
 *
 * @param string $image
 * @param int $rev
 * @param int $auth
 * @return string - file's id
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function media_restore($image, $rev, $auth){
    global $conf;
    if ($auth < AUTH_UPLOAD || !$conf['mediarevisions']) return false;
    $removed = (!file_exists(mediaFN($image)) && file_exists(mediaMetaFN($image, '.changes')));
    if (!$image || (!file_exists(mediaFN($image)) && !$removed)) return false;
    if (!$rev || !file_exists(mediaFN($image, $rev))) return false;
    list($iext,$imime,$dl) = mimetype($image);
    $res = media_upload_finish(mediaFN($image, $rev),
        mediaFN($image),
        $image,
        $imime,
        true,
        'copy');
    if (is_array($res)) {
        msg($res[0], $res[1]);
        return false;
    }
    return $res;
}

/**
 * List all files found by the search request
 *
 * @author Tobias Sarnowski <sarnowski@cosmocode.de>
 * @author Andreas Gohr <gohr@cosmocode.de>
 * @author Kate Arzamastseva <pshns@ukr.net>
 * @triggers MEDIA_SEARCH
 */
function media_searchlist($query,$ns,$auth=null,$fullscreen=false,$sort=''){
    global $conf;
    global $lang;

    $ns = cleanID($ns);

    if ($query) {
        $evdata = array(
                'ns'    => $ns,
                'data'  => array(),
                'query' => $query
                );
        $evt = new Doku_Event('MEDIA_SEARCH', $evdata);
        if ($evt->advise_before()) {
            $dir = utf8_encodeFN(str_replace(':','/',$evdata['ns']));
            $pattern = '/'.preg_quote($evdata['query'],'/').'/i';
            search($evdata['data'],
                    $conf['mediadir'],
                    'search_media',
                    array('showmsg'=>false,'pattern'=>$pattern),
                    $dir);
        }

        $data = array();
        foreach ($evdata['data'] as $k => $v) {
            $data[$k] = ($sort == 'date') ? $v['mtime'] : $v['id'];
        }
        array_multisort($data, SORT_DESC, SORT_NUMERIC, $evdata['data']);

        $evt->advise_after();
        unset($evt);
    }

    if (!$fullscreen) {
        echo '<h1 id="media__ns">'.sprintf($lang['searchmedia_in'],hsc($ns).':*').'</h1>'.NL;
        media_searchform($ns,$query);
    }

    if(!count($evdata['data'])){
        echo '<div class="nothing">'.$lang['nothingfound'].'</div>'.NL;
    }else {
        if ($fullscreen) {
            echo '<ul class="' . _media_get_list_type() . '">';
        }
        foreach($evdata['data'] as $item){
            if (!$fullscreen) media_printfile($item,$item['perm'],'',true);
            else media_printfile_thumbs($item,$item['perm'],false,true);
        }
        if ($fullscreen) echo '</ul>'.NL;
    }
}

/**
 * Formats and prints one file in the list
 */
function media_printfile($item,$auth,$jump,$display_namespace=false){
    global $lang;
    global $conf;

    // Prepare zebra coloring
    // I always wanted to use this variable name :-D
    static $twibble = 1;
    $twibble *= -1;
    $zebra = ($twibble == -1) ? 'odd' : 'even';

    // Automatically jump to recent action
    if($jump == $item['id']) {
        $jump = ' id="scroll__here" ';
    }else{
        $jump = '';
    }

    // Prepare fileicons
    list($ext,$mime,$dl) = mimetype($item['file'],false);
    $class = preg_replace('/[^_\-a-z0-9]+/i','_',$ext);
    $class = 'select mediafile mf_'.$class;

    // Prepare filename
    $file = utf8_decodeFN($item['file']);

    // Prepare info
    $info = '';
    if($item['isimg']){
        $info .= (int) $item['meta']->getField('File.Width');
        $info .= '&#215;';
        $info .= (int) $item['meta']->getField('File.Height');
        $info .= ' ';
    }
    $info .= '<i>'.dformat($item['mtime']).'</i>';
    $info .= ' ';
    $info .= filesize_h($item['size']);

    // output
    echo '<div class="'.$zebra.'"'.$jump.' title="'.hsc($item['id']).'">'.NL;
    if (!$display_namespace) {
        echo '<a name="h_:'.$item['id'].'" class="'.$class.'">'.hsc($file).'</a> ';
    } else {
        echo '<a name="h_:'.$item['id'].'" class="'.$class.'">'.hsc($item['id']).'</a><br/>';
    }
    echo '<span class="info">('.$info.')</span>'.NL;

    // view button
    $link = ml($item['id'],'',true);
    echo ' <a href="'.$link.'" target="_blank"><img src="'.DOKU_BASE.'lib/images/magnifier.png" '.
        'alt="'.$lang['mediaview'].'" title="'.$lang['mediaview'].'" class="btn" /></a>';

    // mediamanager button
    $link = wl('',array('do'=>'media','image'=>$item['id'],'ns'=>getNS($item['id'])));
    echo ' <a href="'.$link.'" target="_blank"><img src="'.DOKU_BASE.'lib/images/mediamanager.png" '.
        'alt="'.$lang['btn_media'].'" title="'.$lang['btn_media'].'" class="btn" /></a>';

    // delete button
    if($item['writable'] && $auth >= AUTH_DELETE){
        $link = DOKU_BASE.'lib/exe/mediamanager.php?delete='.rawurlencode($item['id']).
            '&amp;sectok='.getSecurityToken();
        echo ' <a href="'.$link.'" class="btn_media_delete" title="'.$item['id'].'">'.
            '<img src="'.DOKU_BASE.'lib/images/trash.png" alt="'.$lang['btn_delete'].'" '.
            'title="'.$lang['btn_delete'].'" class="btn" /></a>';
    }

    echo '<div class="example" id="ex_'.str_replace(':','_',$item['id']).'">';
    echo $lang['mediausage'].' <code>{{:'.$item['id'].'}}</code>';
    echo '</div>';
    if($item['isimg']) media_printimgdetail($item);
    echo '<div class="clearer"></div>'.NL;
    echo '</div>'.NL;
}

function media_printicon($filename){
    list($ext,$mime,$dl) = mimetype(mediaFN($filename),false);

    if (@file_exists(DOKU_INC.'lib/images/fileicons/'.$ext.'.png')) {
        $icon = DOKU_BASE.'lib/images/fileicons/'.$ext.'.png';
    } else {
        $icon = DOKU_BASE.'lib/images/fileicons/file.png';
    }

    return '<img src="'.$icon.'" alt="'.$filename.'" class="icon" />';

}

/**
 * Formats and prints one file in the list in the thumbnails view
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function media_printfile_thumbs($item,$auth,$jump=false,$display_namespace=false){
    global $lang;
    global $conf;

    // Prepare filename
    $file = utf8_decodeFN($item['file']);

    // output
    echo '<li><dl title="'.hsc($item['id']).'">'.NL;

        echo '<dt>';
    if($item['isimg']) {
        media_printimgdetail($item, true);

    } else {
        echo '<a name="d_:'.$item['id'].'" class="image" title="'.$item['id'].'" href="'.
            media_managerURL(array('image' => hsc($item['id']), 'ns' => getNS($item['id']),
            'tab_details' => 'view')).'">';
        echo media_printicon($item['id']);
        echo '</a>';
    }
    echo '</dt>'.NL;
    if (!$display_namespace) {
        $name = hsc($file);
    } else {
        $name = hsc($item['id']);
    }
    echo '<dd class="name"><a href="'.media_managerURL(array('image' => hsc($item['id']), 'ns' => getNS($item['id']),
        'tab_details' => 'view')).'" name="h_:'.$item['id'].'">'.$name.'</a></dd>'.NL;

    if($item['isimg']){
        $size = '';
        $size .= (int) $item['meta']->getField('File.Width');
        $size .= '&#215;';
        $size .= (int) $item['meta']->getField('File.Height');
        echo '<dd class="size">'.$size.'</dd>'.NL;
    } else {
        echo '<dd class="size">&nbsp;</dd>'.NL;
    }
    $date = dformat($item['mtime']);
    echo '<dd class="date">'.$date.'</dd>'.NL;
    $filesize = filesize_h($item['size']);
    echo '<dd class="filesize">'.$filesize.'</dd>'.NL;
    echo '</dl></li>'.NL;
}

/**
 * Prints a thumbnail and metainfos
 */
function media_printimgdetail($item, $fullscreen=false){
    // prepare thumbnail
    $size = $fullscreen ? 90 : 120;

    $w = (int) $item['meta']->getField('File.Width');
    $h = (int) $item['meta']->getField('File.Height');
    if($w>$size || $h>$size){
        if (!$fullscreen) {
            $ratio = $item['meta']->getResizeRatio($size);
        } else {
            $ratio = $item['meta']->getResizeRatio($size,$size);
        }
        $w = floor($w * $ratio);
        $h = floor($h * $ratio);
    }
    $src = ml($item['id'],array('w'=>$w,'h'=>$h,'t'=>$item['mtime']));
    $p = array();
    if (!$fullscreen) {
        // In fullscreen mediamanager view, image resizing is done via CSS.
        $p['width']  = $w;
        $p['height'] = $h;
    }
    $p['alt']    = $item['id'];
    $att = buildAttributes($p);

    // output
    if ($fullscreen) {
        echo '<a name="l_:'.$item['id'].'" class="image thumb" href="'.
            media_managerURL(array('image' => hsc($item['id']), 'ns' => getNS($item['id']), 'tab_details' => 'view')).'">';
        echo '<img src="'.$src.'" '.$att.' />';
        echo '</a>';
    }

    if ($fullscreen) return;

    echo '<div class="detail">';
    echo '<div class="thumb">';
    echo '<a name="d_:'.$item['id'].'" class="select">';
    echo '<img src="'.$src.'" '.$att.' />';
    echo '</a>';
    echo '</div>';

    // read EXIF/IPTC data
    $t = $item['meta']->getField(array('IPTC.Headline','xmp.dc:title'));
    $d = $item['meta']->getField(array('IPTC.Caption','EXIF.UserComment',
                'EXIF.TIFFImageDescription',
                'EXIF.TIFFUserComment'));
    if(utf8_strlen($d) > 250) $d = utf8_substr($d,0,250).'...';
    $k = $item['meta']->getField(array('IPTC.Keywords','IPTC.Category','xmp.dc:subject'));

    // print EXIF/IPTC data
    if($t || $d || $k ){
        echo '<p>';
        if($t) echo '<strong>'.htmlspecialchars($t).'</strong><br />';
        if($d) echo htmlspecialchars($d).'<br />';
        if($t) echo '<em>'.htmlspecialchars($k).'</em>';
        echo '</p>';
    }
    echo '</div>';
}

/**
 * Build link based on the current, adding/rewriting
 * parameters
 *
 * @author Kate Arzamastseva <pshns@ukr.net>
 * @param array $params
 * @param string $amp - separator
 * @return string - link
 */
function media_managerURL($params=false, $amp='&amp;', $abs=false, $params_array=false) {
    global $conf;
    global $ID;

    $gets = array('do' => 'media');
    $media_manager_params = array('tab_files', 'tab_details', 'image', 'ns', 'list', 'sort');
    foreach ($media_manager_params as $x) {
        if (isset($_REQUEST[$x])) $gets[$x] = $_REQUEST[$x];
    }

    if ($params) {
        $gets = $params + $gets;
    }
    unset($gets['id']);
    if (isset($gets['delete'])) {
        unset($gets['image']);
        unset($gets['tab_details']);
    }

    if ($params_array) return $gets;

    return wl($ID,$gets,$abs,$amp);
}

/**
 * Print the media upload form if permissions are correct
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function media_uploadform($ns, $auth, $fullscreen = false){
    global $lang, $conf;

    if($auth < AUTH_UPLOAD) {
        echo '<div class="nothing">'.$lang['media_perm_upload'].'</div>'.NL;
        return;
    }
    $auth_ow = (($conf['mediarevisions']) ? AUTH_UPLOAD : AUTH_DELETE);

    $update = false;
    $id = '';
    if ($auth >= $auth_ow && $fullscreen && $_REQUEST['mediado'] == 'update') {
        $update = true;
        $id = cleanID($_REQUEST['image']);
    }

    // The default HTML upload form
    $params = array('id'      => 'dw__upload',
                    'enctype' => 'multipart/form-data');
    if (!$fullscreen) {
        $params['action'] = DOKU_BASE.'lib/exe/mediamanager.php';
    } else {
        $params['action'] = media_managerURL(array('tab_files' => 'files',
            'tab_details' => 'view'), '&');
    }

    $form = new Doku_Form($params);
    if (!$fullscreen) echo '<div class="upload">' . $lang['mediaupload'] . '</div>';
    $form->addElement(formSecurityToken());
    $form->addHidden('ns', hsc($ns));
    $form->addElement(form_makeOpenTag('p'));
    $form->addElement(form_makeFileField('upload', $lang['txt_upload'].':', 'upload__file'));
    $form->addElement(form_makeCloseTag('p'));
    $form->addElement(form_makeOpenTag('p'));
    $form->addElement(form_makeTextField('mediaid', noNS($id), $lang['txt_filename'].':', 'upload__name'));
    $form->addElement(form_makeButton('submit', '', $lang['btn_upload']));
    $form->addElement(form_makeCloseTag('p'));

    if($auth >= $auth_ow){
        $form->addElement(form_makeOpenTag('p'));
        $attrs = array();
        if ($update) $attrs['checked'] = 'checked';
        $form->addElement(form_makeCheckboxField('ow', 1, $lang['txt_overwrt'], 'dw__ow', 'check', $attrs));
        $form->addElement(form_makeCloseTag('p'));
    }

    echo NL.'<div id="mediamanager__uploader">'.NL;
    html_form('upload', $form);
    echo '</div>'.NL;
}

/**
 * Print the search field form
 *
 * @author Tobias Sarnowski <sarnowski@cosmocode.de>
 * @author Kate Arzamastseva <pshns@ukr.net>
 */
function media_searchform($ns,$query='',$fullscreen=false){
    global $lang;

    // The default HTML search form
    $params = array('id' => 'dw__mediasearch');
    if (!$fullscreen) {
        $params['action'] = DOKU_BASE.'lib/exe/mediamanager.php';
    } else {
        $params['action'] = media_managerURL(array(), '&');
    }
    $form = new Doku_Form($params);
    $form->addHidden('ns', $ns);
    $form->addHidden($fullscreen ? 'mediado' : 'do', 'searchlist');

    if (!$fullscreen) $form->addElement('<div class="upload">' . $lang['mediasearch'] . '</div>'.NL);
    $form->addElement(form_makeOpenTag('p'));
    $form->addElement(form_makeTextField('q', $query,$lang['searchmedia'],'','',array('title'=>sprintf($lang['searchmedia_in'],hsc($ns).':*'))));
    $form->addElement(form_makeButton('submit', '', $lang['btn_search']));
    $form->addElement(form_makeCloseTag('p'));
    html_form('searchmedia', $form);
}

/**
 * Build a tree outline of available media namespaces
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function media_nstree($ns){
    global $conf;
    global $lang;

    // currently selected namespace
    $ns  = cleanID($ns);
    if(empty($ns)){
        global $ID;
        $ns = dirname(str_replace(':','/',$ID));
        if($ns == '.') $ns ='';
    }
    $ns  = utf8_encodeFN(str_replace(':','/',$ns));

    $data = array();
    search($data,$conf['mediadir'],'search_index',array('ns' => $ns, 'nofiles' => true));

    // wrap a list with the root level around the other namespaces
    array_unshift($data, array('level' => 0, 'id' => '', 'open' =>'true',
                               'label' => '['.$lang['mediaroot'].']'));

    echo html_buildlist($data,'idx','media_nstree_item','media_nstree_li');
}

/**
 * Userfunction for html_buildlist
 *
 * Prints a media namespace tree item
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function media_nstree_item($item){
    $pos   = strrpos($item['id'], ':');
    $label = substr($item['id'], $pos > 0 ? $pos + 1 : 0);
    if(!$item['label']) $item['label'] = $label;

    $ret  = '';
    if (!($_REQUEST['do'] == 'media'))
    $ret .= '<a href="'.DOKU_BASE.'lib/exe/mediamanager.php?ns='.idfilter($item['id']).'" class="idx_dir">';
    else $ret .= '<a href="'.media_managerURL(array('ns' => idfilter($item['id'], false), 'tab_files' => 'files'))
        .'" class="idx_dir">';
    $ret .= $item['label'];
    $ret .= '</a>';
    return $ret;
}

/**
 * Userfunction for html_buildlist
 *
 * Prints a media namespace tree item opener
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function media_nstree_li($item){
    $class='media level'.$item['level'];
    if($item['open']){
        $class .= ' open';
        $img   = DOKU_BASE.'lib/images/minus.gif';
        $alt   = '&minus;';
    }else{
        $class .= ' closed';
        $img   = DOKU_BASE.'lib/images/plus.gif';
        $alt   = '+';
    }
    // TODO: only deliver an image if it actually has a subtree...
    return '<li class="'.$class.'">'.
        '<img src="'.$img.'" alt="'.$alt.'" />';
}

/**
 * Resizes the given image to the given size
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function media_resize_image($file, $ext, $w, $h=0){
    global $conf;

    $info = @getimagesize($file); //get original size
    if($info == false) return $file; // that's no image - it's a spaceship!

    if(!$h) $h = round(($w * $info[1]) / $info[0]);

    // we wont scale up to infinity
    if($w > 2000 || $h > 2000) return $file;

    //cache
    $local = getCacheName($file,'.media.'.$w.'x'.$h.'.'.$ext);
    $mtime = @filemtime($local); // 0 if not exists

    if( $mtime > filemtime($file) ||
            media_resize_imageIM($ext,$file,$info[0],$info[1],$local,$w,$h) ||
            media_resize_imageGD($ext,$file,$info[0],$info[1],$local,$w,$h) ){
        if($conf['fperm']) chmod($local, $conf['fperm']);
        return $local;
    }
    //still here? resizing failed
    return $file;
}

/**
 * Crops the given image to the wanted ratio, then calls media_resize_image to scale it
 * to the wanted size
 *
 * Crops are centered horizontally but prefer the upper third of an vertical
 * image because most pics are more interesting in that area (rule of thirds)
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 */
function media_crop_image($file, $ext, $w, $h=0){
    global $conf;

    if(!$h) $h = $w;
    $info = @getimagesize($file); //get original size
    if($info == false) return $file; // that's no image - it's a spaceship!

    // calculate crop size
    $fr = $info[0]/$info[1];
    $tr = $w/$h;
    if($tr >= 1){
        if($tr > $fr){
            $cw = $info[0];
            $ch = (int) $info[0]/$tr;
        }else{
            $cw = (int) $info[1]*$tr;
            $ch = $info[1];
        }
    }else{
        if($tr < $fr){
            $cw = (int) $info[1]*$tr;
            $ch = $info[1];
        }else{
            $cw = $info[0];
            $ch = (int) $info[0]/$tr;
        }
    }
    // calculate crop offset
    $cx = (int) ($info[0]-$cw)/2;
    $cy = (int) ($info[1]-$ch)/3;

    //cache
    $local = getCacheName($file,'.media.'.$cw.'x'.$ch.'.crop.'.$ext);
    $mtime = @filemtime($local); // 0 if not exists

    if( $mtime > @filemtime($file) ||
            media_crop_imageIM($ext,$file,$info[0],$info[1],$local,$cw,$ch,$cx,$cy) ||
            media_resize_imageGD($ext,$file,$cw,$ch,$local,$cw,$ch,$cx,$cy) ){
        if($conf['fperm']) chmod($local, $conf['fperm']);
        return media_resize_image($local,$ext, $w, $h);
    }

    //still here? cropping failed
    return media_resize_image($file,$ext, $w, $h);
}

/**
 * Download a remote file and return local filename
 *
 * returns false if download fails. Uses cached file if available and
 * wanted
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @author  Pavel Vitis <Pavel.Vitis@seznam.cz>
 */
function media_get_from_URL($url,$ext,$cache){
    global $conf;

    // if no cache or fetchsize just redirect
    if ($cache==0)           return false;
    if (!$conf['fetchsize']) return false;

    $local = getCacheName(strtolower($url),".media.$ext");
    $mtime = @filemtime($local); // 0 if not exists

    //decide if download needed:
    if( ($mtime == 0) ||                           // cache does not exist
            ($cache != -1 && $mtime < time()-$cache)   // 'recache' and cache has expired
      ){
        if(media_image_download($url,$local)){
            return $local;
        }else{
            return false;
        }
    }

    //if cache exists use it else
    if($mtime) return $local;

    //else return false
    return false;
}

/**
 * Download image files
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function media_image_download($url,$file){
    global $conf;
    $http = new DokuHTTPClient();
    $http->max_bodysize = $conf['fetchsize'];
    $http->timeout = 25; //max. 25 sec
    $http->header_regexp = '!\r\nContent-Type: image/(jpe?g|gif|png)!i';

    $data = $http->get($url);
    if(!$data) return false;

    $fileexists = @file_exists($file);
    $fp = @fopen($file,"w");
    if(!$fp) return false;
    fwrite($fp,$data);
    fclose($fp);
    if(!$fileexists and $conf['fperm']) chmod($file, $conf['fperm']);

    // check if it is really an image
    $info = @getimagesize($file);
    if(!$info){
        @unlink($file);
        return false;
    }

    return true;
}

/**
 * resize images using external ImageMagick convert program
 *
 * @author Pavel Vitis <Pavel.Vitis@seznam.cz>
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function media_resize_imageIM($ext,$from,$from_w,$from_h,$to,$to_w,$to_h){
    global $conf;

    // check if convert is configured
    if(!$conf['im_convert']) return false;

    // prepare command
    $cmd  = $conf['im_convert'];
    $cmd .= ' -resize '.$to_w.'x'.$to_h.'!';
    if ($ext == 'jpg' || $ext == 'jpeg') {
        $cmd .= ' -quality '.$conf['jpg_quality'];
    }
    $cmd .= " $from $to";

    @exec($cmd,$out,$retval);
    if ($retval == 0) return true;
    return false;
}

/**
 * crop images using external ImageMagick convert program
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function media_crop_imageIM($ext,$from,$from_w,$from_h,$to,$to_w,$to_h,$ofs_x,$ofs_y){
    global $conf;

    // check if convert is configured
    if(!$conf['im_convert']) return false;

    // prepare command
    $cmd  = $conf['im_convert'];
    $cmd .= ' -crop '.$to_w.'x'.$to_h.'+'.$ofs_x.'+'.$ofs_y;
    if ($ext == 'jpg' || $ext == 'jpeg') {
        $cmd .= ' -quality '.$conf['jpg_quality'];
    }
    $cmd .= " $from $to";

    @exec($cmd,$out,$retval);
    if ($retval == 0) return true;
    return false;
}

/**
 * resize or crop images using PHP's libGD support
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Sebastian Wienecke <s_wienecke@web.de>
 */
function media_resize_imageGD($ext,$from,$from_w,$from_h,$to,$to_w,$to_h,$ofs_x=0,$ofs_y=0){
    global $conf;

    if($conf['gdlib'] < 1) return false; //no GDlib available or wanted

    // check available memory
    if(!is_mem_available(($from_w * $from_h * 4) + ($to_w * $to_h * 4))){
        return false;
    }

    // create an image of the given filetype
    if ($ext == 'jpg' || $ext == 'jpeg'){
        if(!function_exists("imagecreatefromjpeg")) return false;
        $image = @imagecreatefromjpeg($from);
    }elseif($ext == 'png') {
        if(!function_exists("imagecreatefrompng")) return false;
        $image = @imagecreatefrompng($from);

    }elseif($ext == 'gif') {
        if(!function_exists("imagecreatefromgif")) return false;
        $image = @imagecreatefromgif($from);
    }
    if(!$image) return false;

    if(($conf['gdlib']>1) && function_exists("imagecreatetruecolor") && $ext != 'gif'){
        $newimg = @imagecreatetruecolor ($to_w, $to_h);
    }
    if(!$newimg) $newimg = @imagecreate($to_w, $to_h);
    if(!$newimg){
        imagedestroy($image);
        return false;
    }

    //keep png alpha channel if possible
    if($ext == 'png' && $conf['gdlib']>1 && function_exists('imagesavealpha')){
        imagealphablending($newimg, false);
        imagesavealpha($newimg,true);
    }

    //keep gif transparent color if possible
    if($ext == 'gif' && function_exists('imagefill') && function_exists('imagecolorallocate')) {
        if(function_exists('imagecolorsforindex') && function_exists('imagecolortransparent')) {
            $transcolorindex = @imagecolortransparent($image);
            if($transcolorindex >= 0 ) { //transparent color exists
                $transcolor = @imagecolorsforindex($image, $transcolorindex);
                $transcolorindex = @imagecolorallocate($newimg, $transcolor['red'], $transcolor['green'], $transcolor['blue']);
                @imagefill($newimg, 0, 0, $transcolorindex);
                @imagecolortransparent($newimg, $transcolorindex);
            }else{ //filling with white
                $whitecolorindex = @imagecolorallocate($newimg, 255, 255, 255);
                @imagefill($newimg, 0, 0, $whitecolorindex);
            }
        }else{ //filling with white
            $whitecolorindex = @imagecolorallocate($newimg, 255, 255, 255);
            @imagefill($newimg, 0, 0, $whitecolorindex);
        }
    }

    //try resampling first
    if(function_exists("imagecopyresampled")){
        if(!@imagecopyresampled($newimg, $image, 0, 0, $ofs_x, $ofs_y, $to_w, $to_h, $from_w, $from_h)) {
            imagecopyresized($newimg, $image, 0, 0, $ofs_x, $ofs_y, $to_w, $to_h, $from_w, $from_h);
        }
    }else{
        imagecopyresized($newimg, $image, 0, 0, $ofs_x, $ofs_y, $to_w, $to_h, $from_w, $from_h);
    }

    $okay = false;
    if ($ext == 'jpg' || $ext == 'jpeg'){
        if(!function_exists('imagejpeg')){
            $okay = false;
        }else{
            $okay = imagejpeg($newimg, $to, $conf['jpg_quality']);
        }
    }elseif($ext == 'png') {
        if(!function_exists('imagepng')){
            $okay = false;
        }else{
            $okay =  imagepng($newimg, $to);
        }
    }elseif($ext == 'gif') {
        if(!function_exists('imagegif')){
            $okay = false;
        }else{
            $okay = imagegif($newimg, $to);
        }
    }

    // destroy GD image ressources
    if($image) imagedestroy($image);
    if($newimg) imagedestroy($newimg);

    return $okay;
}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
