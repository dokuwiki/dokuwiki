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

    if($meta->save()){
        if($conf['fperm']) chmod($src, $conf['fperm']);
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
 */
function media_metaform($id,$auth){
    if($auth < AUTH_UPLOAD) return false;
    global $lang, $config_cascade;

    // load the field descriptions
    static $fields = null;
    if(is_null($fields)){

        foreach (array('default','local') as $config_group) {
            if (empty($config_cascade['mediameta'][$config_group])) continue;
            foreach ($config_cascade['mediameta'][$config_group] as $config_file) {
                if(@file_exists($config_file)){
                    include($config_file);
                }
            }
        }
    }

    $src = mediaFN($id);

    // output
    echo '<h1>'.hsc(noNS($id)).'</h1>'.NL;
    echo '<form action="'.DOKU_BASE.'lib/exe/mediamanager.php" accept-charset="utf-8" method="post" class="meta">'.NL;
    formSecurityToken();
    foreach($fields as $key => $field){
        // get current value
        $tags = array($field[0]);
        if(is_array($field[3])) $tags = array_merge($tags,$field[3]);
        $value = tpl_img_getTag($tags,'',$src);
        $value = cleanText($value);

        // prepare attributes
        $p = array();
        $p['class'] = 'edit';
        $p['id']    = 'meta__'.$key;
        $p['name']  = 'meta['.$field[0].']';

        // put label
        echo '<div class="metafield">';
        echo '<label for="meta__'.$key.'">';
        echo ($lang[$field[1]]) ? $lang[$field[1]] : $field[1];
        echo ':</label>';

        // put input field
        if($field[2] == 'text'){
            $p['value'] = $value;
            $p['type']  = 'text';
            $att = buildAttributes($p);
            echo "<input $att/>".NL;
        }else{
            $att = buildAttributes($p);
            echo "<textarea $att rows=\"6\" cols=\"50\">".formText($value).'</textarea>'.NL;
        }
        echo '</div>'.NL;
    }
    echo '<div class="buttons">'.NL;
    echo '<input type="hidden" name="img" value="'.hsc($id).'" />'.NL;
    echo '<input name="do[save]" type="submit" value="'.$lang['btn_save'].
        '" title="'.$lang['btn_save'].' [S]" accesskey="s" class="button" />'.NL;
    echo '<input name="do[cancel]" type="submit" value="'.$lang['btn_cancel'].
        '" title="'.$lang['btn_cancel'].' [C]" accesskey="c" class="button" />'.NL;
    echo '</div>'.NL;
    echo '</form>'.NL;
}

/**
 * Conveinience function to check if a media file is still in use
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

/**
 * Handles media file deletions
 *
 * If configured, checks for media references before deletion
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @return mixed false on error, true on delete or array with refs
 */
function media_delete($id,$auth){
    if($auth < AUTH_DELETE) return false;
    if(!checkSecurityToken()) return false;
    global $conf;
    global $lang;

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
        $data['unl'] = @unlink($file);
        if($data['unl']){
            addMediaLogEntry(time(), $id, DOKU_CHANGE_TYPE_DELETE);
            $data['del'] = io_sweepNS($id,'mediadir');
        }
    }
    $evt->advise_after();
    unset($evt);

    if($data['unl'] && $data['del']){
        // current namespace was removed. redirecting to root ns passing msg along
        send_redirect(DOKU_URL.'lib/exe/mediamanager.php?msg1='.
                rawurlencode(sprintf(noNS($id),$lang['deletesucc'])));
    }

    return $data['unl'];
}

/**
 * Handles media file uploads
 *
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
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Michael Klier <chi@chimeric.de>
 * @return mixed false on error, id of the new file on success
 */
function media_upload($ns,$auth){
    if($auth < AUTH_UPLOAD) return false;
    if(!checkSecurityToken()) return false;
    global $lang;
    global $conf;

    // get file and id
    $id   = $_POST['id'];
    $file = $_FILES['upload'];
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

    // get filename
    $id   = cleanID($ns.':'.$id,false,true);
    $fn   = mediaFN($id);

    // get filetype regexp
    $types = array_keys(getMimeTypes());
    $types = array_map(create_function('$q','return preg_quote($q,"/");'),$types);
    $regex = join('|',$types);

    // because a temp file was created already
    if(preg_match('/\.('.$regex.')$/i',$fn)){
        //check for overwrite
        $overwrite = @file_exists($fn);
        if($overwrite && (!$_REQUEST['ow'] || $auth < AUTH_DELETE)){
            msg($lang['uploadexist'],0);
            return false;
        }
        // check for valid content
        $ok = media_contentcheck($file['tmp_name'],$imime);
        if($ok == -1){
            msg(sprintf($lang['uploadbadcontent'],".$iext"),-1);
            return false;
        }elseif($ok == -2){
            msg($lang['uploadspam'],-1);
            return false;
        }elseif($ok == -3){
            msg($lang['uploadxss'],-1);
            return false;
        }

        // prepare event data
        $data[0] = $file['tmp_name'];
        $data[1] = $fn;
        $data[2] = $id;
        $data[3] = $imime;
        $data[4] = $overwrite;

        // trigger event
        return trigger_event('MEDIA_UPLOAD_FINISH', $data, '_media_upload_action', true);

    }else{
        msg($lang['uploadwrong'],-1);
    }
    return false;
}

/**
 * Callback adapter for media_upload_finish()
 * @author Michael Klier <chi@chimeric.de>
 */
function _media_upload_action($data) {
    // fixme do further sanity tests of given data?
    if(is_array($data) && count($data)===5) {
        return media_upload_finish($data[0], $data[1], $data[2], $data[3], $data[4]);
    } else {
        return false; //callback error
    }
}

/**
 * Saves an uploaded media file
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Michael Klier <chi@chimeric.de>
 */
function media_upload_finish($fn_tmp, $fn, $id, $imime, $overwrite) {
    global $conf;
    global $lang;

    // prepare directory
    io_createNamespace($id, 'media');

    if(move_uploaded_file($fn_tmp, $fn)) {
        // Set the correct permission here.
        // Always chmod media because they may be saved with different permissions than expected from the php umask.
        // (Should normally chmod to $conf['fperm'] only if $conf['fperm'] is set.)
        chmod($fn, $conf['fmode']);
        msg($lang['uploadsucc'],1);
        media_notify($id,$fn,$imime);
        // add a log entry to the media changelog
        if ($overwrite) {
            addMediaLogEntry(time(), $id, DOKU_CHANGE_TYPE_EDIT);
        } else {
            addMediaLogEntry(time(), $id, DOKU_CHANGE_TYPE_CREATE);
        }
        return $id;
    }else{
        msg($lang['uploadfail'],-1);
    }
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
function media_notify($id,$file,$mime){
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

    $subject = '['.$conf['title'].'] '.$lang['mail_upload'].' '.$id;

    mail_send($conf['notify'],$subject,$text,$conf['mailfrom']);
}

/**
 * List all files in a given Media namespace
 */
function media_filelist($ns,$auth=null,$jump=''){
    global $conf;
    global $lang;
    $ns = cleanID($ns);

    // check auth our self if not given (needed for ajax calls)
    if(is_null($auth)) $auth = auth_quickaclcheck("$ns:*");

    echo '<h1 id="media__ns">:'.hsc($ns).'</h1>'.NL;

    if($auth < AUTH_READ){
        // FIXME: print permission warning here instead?
        echo '<div class="nothing">'.$lang['nothingfound'].'</div>'.NL;
    }else{
        media_uploadform($ns, $auth);

        $dir = utf8_encodeFN(str_replace(':','/',$ns));
        $data = array();
        search($data,$conf['mediadir'],'search_media',
                array('showmsg'=>true,'depth'=>1),$dir);

        if(!count($data)){
            echo '<div class="nothing">'.$lang['nothingfound'].'</div>'.NL;
        }else foreach($data as $item){
            media_printfile($item,$auth,$jump);
        }
    }
    media_searchform($ns);
}

/**
 * List all files found by the search request
 *
 * @author Tobias Sarnowski <sarnowski@cosmocode.de>
 * @author Andreas Gohr <gohr@cosmocode.de>
 * @triggers MEDIA_SEARCH
 */
function media_searchlist($query,$ns,$auth=null){
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
        $evt->advise_after();
        unset($evt);
    }

    echo '<h1 id="media__ns">'.sprintf($lang['searchmedia_in'],hsc($ns).':*').'</h1>'.NL;
    media_searchform($ns,$query);

    if(!count($evdata['data'])){
        echo '<div class="nothing">'.$lang['nothingfound'].'</div>'.NL;
    }else foreach($evdata['data'] as $item){
        media_printfile($item,$item['perm'],'',true);
    }
}

/**
 * Print action links for a file depending on filetype
 * and available permissions
 */
function media_fileactions($item,$auth){
    global $lang;

    // view button
    $link = ml($item['id'],'',true);
    echo ' <a href="'.$link.'" target="_blank"><img src="'.DOKU_BASE.'lib/images/magnifier.png" '.
        'alt="'.$lang['mediaview'].'" title="'.$lang['mediaview'].'" class="btn" /></a>';

    // no further actions if not writable
    if(!$item['writable']) return;

    // delete button
    if($auth >= AUTH_DELETE){
        echo ' <a href="'.DOKU_BASE.'lib/exe/mediamanager.php?delete='.rawurlencode($item['id']).
            '&amp;sectok='.getSecurityToken().'" class="btn_media_delete" title="'.$item['id'].'">'.
            '<img src="'.DOKU_BASE.'lib/images/trash.png" alt="'.$lang['btn_delete'].'" '.
            'title="'.$lang['btn_delete'].'" class="btn" /></a>';
    }

    // edit button
    if($auth >= AUTH_UPLOAD && $item['isimg'] && $item['meta']->getField('File.Mime') == 'image/jpeg'){
        echo ' <a href="'.DOKU_BASE.'lib/exe/mediamanager.php?edit='.rawurlencode($item['id']).'">'.
            '<img src="'.DOKU_BASE.'lib/images/pencil.png" alt="'.$lang['metaedit'].'" '.
            'title="'.$lang['metaedit'].'" class="btn" /></a>';
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
    echo '<div class="'.$zebra.'"'.$jump.'>'.NL;
    if (!$display_namespace) {
        echo '<a name="h_:'.$item['id'].'" class="'.$class.'">'.hsc($file).'</a> ';
    } else {
        echo '<a name="h_:'.$item['id'].'" class="'.$class.'">'.hsc($item['id']).'</a><br/>';
    }
    echo '<span class="info">('.$info.')</span>'.NL;
    media_fileactions($item,$auth);
    echo '<div class="example" id="ex_'.str_replace(':','_',$item['id']).'">';
    echo $lang['mediausage'].' <code>{{:'.$item['id'].'}}</code>';
    echo '</div>';
    if($item['isimg']) media_printimgdetail($item);
    echo '<div class="clearer"></div>'.NL;
    echo '</div>'.NL;
}

/**
 * Prints a thumbnail and metainfos
 */
function media_printimgdetail($item){
    // prepare thumbnail
    $w = (int) $item['meta']->getField('File.Width');
    $h = (int) $item['meta']->getField('File.Height');
    if($w>120 || $h>120){
        $ratio = $item['meta']->getResizeRatio(120);
        $w = floor($w * $ratio);
        $h = floor($h * $ratio);
    }
    $src = ml($item['id'],array('w'=>$w,'h'=>$h));
    $p = array();
    $p['width']  = $w;
    $p['height'] = $h;
    $p['alt']    = $item['id'];
    $p['class']  = 'thumb';
    $att = buildAttributes($p);

    // output
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
 * Print the media upload form if permissions are correct
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function media_uploadform($ns, $auth){
    global $lang;

    if($auth < AUTH_UPLOAD) return; //fixme print info on missing permissions?

    // The default HTML upload form
    $form = new Doku_Form(array('id'      => 'dw__upload',
                                'action'  => DOKU_BASE.'lib/exe/mediamanager.php',
                                'enctype' => 'multipart/form-data'));
    $form->addElement('<div class="upload">' . $lang['mediaupload'] . '</div>');
    $form->addElement(formSecurityToken());
    $form->addHidden('ns', hsc($ns));
    $form->addElement(form_makeOpenTag('p'));
    $form->addElement(form_makeFileField('upload', $lang['txt_upload'].':', 'upload__file'));
    $form->addElement(form_makeCloseTag('p'));
    $form->addElement(form_makeOpenTag('p'));
    $form->addElement(form_makeTextField('id', '', $lang['txt_filename'].':', 'upload__name'));
    $form->addElement(form_makeButton('submit', '', $lang['btn_upload']));
    $form->addElement(form_makeCloseTag('p'));

    if($auth >= AUTH_DELETE){
        $form->addElement(form_makeOpenTag('p'));
        $form->addElement(form_makeCheckboxField('ow', 1, $lang['txt_overwrt'], 'dw__ow', 'check'));
        $form->addElement(form_makeCloseTag('p'));
    }
    html_form('upload', $form);

    // prepare flashvars for multiupload
    $opt = array(
            'L_gridname'  => $lang['mu_gridname'] ,
            'L_gridsize'  => $lang['mu_gridsize'] ,
            'L_gridstat'  => $lang['mu_gridstat'] ,
            'L_namespace' => $lang['mu_namespace'] ,
            'L_overwrite' => $lang['txt_overwrt'],
            'L_browse'    => $lang['mu_browse'],
            'L_upload'    => $lang['btn_upload'],
            'L_toobig'    => $lang['mu_toobig'],
            'L_ready'     => $lang['mu_ready'],
            'L_done'      => $lang['mu_done'],
            'L_fail'      => $lang['mu_fail'],
            'L_authfail'  => $lang['mu_authfail'],
            'L_progress'  => $lang['mu_progress'],
            'L_filetypes' => $lang['mu_filetypes'],
            'L_info'      => $lang['mu_info'],
            'L_lasterr'   => $lang['mu_lasterr'],

            'O_ns'        => ":$ns",
            'O_backend'   => 'mediamanager.php?'.session_name().'='.session_id(),
            'O_maxsize'   => php_to_byte(ini_get('upload_max_filesize')),
            'O_extensions'=> join('|',array_keys(getMimeTypes())),
            'O_overwrite' => ($auth >= AUTH_DELETE),
            'O_sectok'    => getSecurityToken(),
            'O_authtok'   => auth_createToken(),
            );
    $var = buildURLparams($opt);
    // output the flash uploader
    ?>
        <div id="dw__flashupload" style="display:none">
        <div class="upload"><?php echo $lang['mu_intro']?></div>
        <?php echo html_flashobject('multipleUpload.swf','500','190',null,$opt); ?>
        </div>
        <?php
}

/**
 * Print the search field form
 *
 * @author Tobias Sarnowski <sarnowski@cosmocode.de>
 */
function media_searchform($ns,$query=''){
    global $lang;

    // The default HTML search form
    $form = new Doku_Form(array('id' => 'dw__mediasearch', 'action' => DOKU_BASE.'lib/exe/mediamanager.php'));
    $form->addElement('<div class="upload">' . $lang['mediasearch'] . '</div>');
    $form->addElement(formSecurityToken());
    $form->addHidden('ns', $ns);
    $form->addHidden('do', 'searchlist');
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
    $item = array( 'level' => 0, 'id' => '',
            'open' =>'true', 'label' => '['.$lang['mediaroot'].']');

    echo '<ul class="idx">';
    echo media_nstree_li($item);
    echo media_nstree_item($item);
    echo html_buildlist($data,'idx','media_nstree_item','media_nstree_li');
    echo '</li>';
    echo '</ul>';
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
    $ret .= '<a href="'.DOKU_BASE.'lib/exe/mediamanager.php?ns='.idfilter($item['id']).'" class="idx_dir">';
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

    if( $mtime > filemtime($file) ||
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
