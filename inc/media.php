<?php
/**
 * All output and handler function needed for the media management popup
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',fullpath(dirname(__FILE__).'/../').'/');
if(!defined('NL')) define('NL',"\n");

require_once(DOKU_INC.'inc/html.php');
require_once(DOKU_INC.'inc/search.php');
require_once(DOKU_INC.'inc/JpegMeta.php');

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
    global $lang;

    // load the field descriptions
    static $fields = null;
    if(is_null($fields)){
        include(DOKU_CONF.'mediameta.php');
        if(@file_exists(DOKU_CONF.'mediameta.local.php')){
            include(DOKU_CONF.'mediameta.local.php');
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
         '" title="ALT+S" accesskey="s" class="button" />'.NL;
    echo '<input name="do[cancel]" type="submit" value="'.$lang['btn_cancel'].
         '" title="ALT+C" accesskey="c" class="button" />'.NL;
    echo '</div>'.NL;
    echo '</form>'.NL;
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

    // check for references if needed
    $mediareferences = array();
    if($conf['refcheck']){
        require_once(DOKU_INC.'inc/fulltext.php');
        $mediareferences = ft_mediause($id,$conf['refshow']);
    }

    if(!count($mediareferences)){
        $file = mediaFN($id);
        if(@unlink($file)){
            msg(str_replace('%s',noNS($id),$lang['deletesucc']),1);
            $del = io_sweepNS($id,'mediadir');
            if($del){
                // current namespace was removed. redirecting to root ns passing msg along
                header('Location: '.DOKU_URL.'lib/exe/mediamanager.php?msg1='.
                        rawurlencode(str_replace('%s',noNS($id),$lang['deletesucc'])));
                exit;
            }
            return true;
        }
        //something went wrong
        msg(str_replace('%s',$file,$lang['deletefail']),-1);
        return false;
    }elseif(!$conf['refshow']){
        msg(str_replace('%s',noNS($id),$lang['mediainuse']),0);
        return false;
    }

    return $mediareferences;
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
 *
 * @triggers MEDIA_UPLOAD_FINISH
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Michael Klier <chi@chimeric.de>
 * @return mixed false on error, id of the new file on success
 */
function media_upload($ns,$auth){
    if($auth < AUTH_UPLOAD) return false;
    if(!checkSecurityToken()) return false;
    require_once(DOKU_INC.'inc/confutils.php');
    global $lang;
    global $conf;

    // get file and id
    $id   = $_POST['id'];
    $file = $_FILES['upload'];
    if(empty($id)) $id = $file['name'];

    // check extensions
    list($fext,$fmime) = mimetype($file['name']);
    list($iext,$imime) = mimetype($id);
    if($fext && !$iext){
        // no extension specified in id - read original one
        $id   .= '.'.$fext;
        $imime = $fmime;
    }elseif($fext && $fext != $iext){
        // extension was changed, print warning
        msg(sprintf($lang['mediaextchange'],$fext,$iext));
    }

    // get filename
    $id   = cleanID($ns.':'.$id);
    $fn   = mediaFN($id);

    // get filetype regexp
    $types = array_keys(getMimeTypes());
    $types = array_map(create_function('$q','return preg_quote($q,"/");'),$types);
    $regex = join('|',$types);

    // because a temp file was created already
    if(preg_match('/\.('.$regex.')$/i',$fn)){
        //check for overwrite
        if(@file_exists($fn) && (!$_POST['ow'] || $auth < AUTH_DELETE)){
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
    if(is_array($data) && count($data)===4) {
        return media_upload_finish($data[0], $data[1], $data[2], $data[3]);
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
function media_upload_finish($fn_tmp, $fn, $id, $imime) {
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
    if(empty($conf['notify'])) return; //notify enabled?

    $text = rawLocale('uploadmail');
    $text = str_replace('@DATE@',strftime($conf['dformat']),$text);
    $text = str_replace('@BROWSER@',$_SERVER['HTTP_USER_AGENT'],$text);
    $text = str_replace('@IPADDRESS@',$_SERVER['REMOTE_ADDR'],$text);
    $text = str_replace('@HOSTNAME@',gethostbyaddr($_SERVER['REMOTE_ADDR']),$text);
    $text = str_replace('@DOKUWIKIURL@',DOKU_URL,$text);
    $text = str_replace('@USER@',$_SERVER['REMOTE_USER'],$text);
    $text = str_replace('@MIME@',$mime,$text);
    $text = str_replace('@MEDIA@',ml($id,'',true,'&',true),$text);
    $text = str_replace('@SIZE@',filesize_h(filesize($file)),$text);

    $from = $conf['mailfrom'];
    $from = str_replace('@USER@',$_SERVER['REMOTE_USER'],$from);
    $from = str_replace('@NAME@',$INFO['userinfo']['name'],$from);
    $from = str_replace('@MAIL@',$INFO['userinfo']['mail'],$from);

    $subject = '['.$conf['title'].'] '.$lang['mail_upload'].' '.$id;

    mail_send($conf['notify'],$subject,$text,$from);
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
        return;
    }

    media_uploadform($ns, $auth);

    $dir = utf8_encodeFN(str_replace(':','/',$ns));
    $data = array();
    search($data,$conf['mediadir'],'search_media',array('showmsg'=>true),$dir);

    if(!count($data)){
        echo '<div class="nothing">'.$lang['nothingfound'].'</div>'.NL;
        return;
    }

    foreach($data as $item){
        media_printfile($item,$auth,$jump);
    }
}

/**
 * Print action links for a file depending on filetype
 * and available permissions
 *
 * @todo contains inline javascript
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
        $ask  = addslashes($lang['del_confirm']).'\\n';
        $ask .= addslashes($item['id']);

        echo ' <a href="'.DOKU_BASE.'lib/exe/mediamanager.php?delete='.rawurlencode($item['id']).
             '&amp;sectok='.getSecurityToken().'" '.
             'onclick="return confirm(\''.$ask.'\')" onkeypress="return confirm(\''.$ask.'\')">'.
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
function media_printfile($item,$auth,$jump){
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
    list($ext,$mime) = mimetype($item['file']);
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
    $info .= '<i>'.strftime($conf['dformat'],$item['mtime']).'</i>';
    $info .= ' ';
    $info .= filesize_h($item['size']);

    // ouput
    echo '<div class="'.$zebra.'"'.$jump.'>'.NL;
    echo '<a name="h_'.$item['id'].'" class="'.$class.'">'.$file.'</a> ';
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
    echo '<a name="d_'.$item['id'].'" class="select">';
    echo '<img src="'.$src.'" '.$att.' />';
    echo '</a>';
    echo '</div>';

    // read EXIF/IPTC data
    $t = $item['meta']->getField('IPTC.Headline');
    $d = $item['meta']->getField(array('IPTC.Caption','EXIF.UserComment',
                                       'EXIF.TIFFImageDescription',
                                       'EXIF.TIFFUserComment'));
    if(utf8_strlen($d) > 250) $d = utf8_substr($d,0,250).'...';
    $k = $item['meta']->getField(array('IPTC.Keywords','IPTC.Category'));

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

    print '<div class="upload">' . $lang['mediaupload'] . '</div>';
    $form = new Doku_Form('dw__upload', DOKU_BASE.'lib/exe/mediamanager.php', false, 'multipart/form-data');
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
