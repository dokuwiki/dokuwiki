<?php

// This file contains the common functions to display diffs between revisions
// It is used by the diff and conflict actions

/**
 * Get header of diff HTML
 * @param string $l_rev   Left revisions
 * @param string $r_rev   Right revision
 * @param string $id      Page id, if null $ID is used
 * @param bool   $media   If it is for media files
 * @param bool   $inline  Return the header on a single line
 * @return array HTML snippets for diff header
 */
function html_diff_head($l_rev, $r_rev, $id = null, $media = false, $inline = false) {
    global $lang;
    if ($id === null) {
        global $ID;
        $id = $ID;
    }
    $head_separator = $inline ? ' ' : '<br />';
    $media_or_wikiFN = $media ? 'mediaFN' : 'wikiFN';
    $ml_or_wl = $media ? 'ml' : 'wl';
    $l_minor = $r_minor = '';

    if(!$l_rev){
        $l_head = '&mdash;';
    }else{
        $l_info   = getRevisionInfo($id,$l_rev,true, $media);
        if($l_info['user']){
            $l_user = '<bdi>'.editorinfo($l_info['user']).'</bdi>';
            if(auth_ismanager()) $l_user .= ' <bdo dir="ltr">('.$l_info['ip'].')</bdo>';
        } else {
            $l_user = '<bdo dir="ltr">'.$l_info['ip'].'</bdo>';
        }
        $l_user  = '<span class="user">'.$l_user.'</span>';
        $l_sum   = ($l_info['sum']) ? '<span class="sum"><bdi>'.hsc($l_info['sum']).'</bdi></span>' : '';
        if ($l_info['type']===DOKU_CHANGE_TYPE_MINOR_EDIT) $l_minor = 'class="minor"';

        $l_head_title = ($media) ? dformat($l_rev) : $id.' ['.dformat($l_rev).']';
        $l_head = '<bdi><a class="wikilink1" href="'.$ml_or_wl($id,"rev=$l_rev").'">'.
        $l_head_title.'</a></bdi>'.
        $head_separator.$l_user.' '.$l_sum;
    }

    if($r_rev){
        $r_info   = getRevisionInfo($id,$r_rev,true, $media);
        if($r_info['user']){
            $r_user = '<bdi>'.editorinfo($r_info['user']).'</bdi>';
            if(auth_ismanager()) $r_user .= ' <bdo dir="ltr">('.$r_info['ip'].')</bdo>';
        } else {
            $r_user = '<bdo dir="ltr">'.$r_info['ip'].'</bdo>';
        }
        $r_user = '<span class="user">'.$r_user.'</span>';
        $r_sum  = ($r_info['sum']) ? '<span class="sum"><bdi>'.hsc($r_info['sum']).'</bdi></span>' : '';
        if ($r_info['type']===DOKU_CHANGE_TYPE_MINOR_EDIT) $r_minor = 'class="minor"';

        $r_head_title = ($media) ? dformat($r_rev) : $id.' ['.dformat($r_rev).']';
        $r_head = '<bdi><a class="wikilink1" href="'.$ml_or_wl($id,"rev=$r_rev").'">'.
        $r_head_title.'</a></bdi>'.
        $head_separator.$r_user.' '.$r_sum;
    }elseif($_rev = @filemtime($media_or_wikiFN($id))){
        $_info   = getRevisionInfo($id,$_rev,true, $media);
        if($_info['user']){
            $_user = '<bdi>'.editorinfo($_info['user']).'</bdi>';
            if(auth_ismanager()) $_user .= ' <bdo dir="ltr">('.$_info['ip'].')</bdo>';
        } else {
            $_user = '<bdo dir="ltr">'.$_info['ip'].'</bdo>';
        }
        $_user = '<span class="user">'.$_user.'</span>';
        $_sum  = ($_info['sum']) ? '<span class="sum"><bdi>'.hsc($_info['sum']).'</span></bdi>' : '';
        if ($_info['type']===DOKU_CHANGE_TYPE_MINOR_EDIT) $r_minor = 'class="minor"';

        $r_head_title = ($media) ? dformat($_rev) : $id.' ['.dformat($_rev).']';
        $r_head  = '<bdi><a class="wikilink1" href="'.$ml_or_wl($id).'">'.
        $r_head_title.'</a></bdi> '.
        '('.$lang['current'].')'.
        $head_separator.$_user.' '.$_sum;
    }else{
        $r_head = '&mdash; ('.$lang['current'].')';
    }

    return array($l_head, $r_head, $l_minor, $r_minor);
}

/**
 * show diff
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @param  string $text - compare with this text with most current version
 * @param  bool   $intro - display the intro text
 * @param  string $type type of the diff (inline or sidebyside)
 */
function html_diff($text='',$intro=true,$type=null){
    global $ID;
    global $REV;
    global $lang;
    global $INPUT;
    global $INFO;

    if(!$type) {
        $type = $INPUT->str('difftype');
        if (empty($type)) {
            $type = get_doku_pref('difftype', $type);
            if (empty($type) && $INFO['ismobile']) {
                $type = 'inline';
            }
        }
    }
    if($type != 'inline') $type = 'sidebyside';

    // we're trying to be clever here, revisions to compare can be either
    // given as rev and rev2 parameters, with rev2 being optional. Or in an
    // array in rev2.
    $rev1 = $REV;

    $rev2 = $INPUT->ref('rev2');
    if(is_array($rev2)){
        $rev1 = (int) $rev2[0];
        $rev2 = (int) $rev2[1];

        if(!$rev1){
            $rev1 = $rev2;
            unset($rev2);
        }
    }else{
        $rev2 = $INPUT->int('rev2');
    }

    $r_minor = '';
    $l_minor = '';

    if($text){                      // compare text to the most current revision
        $l_rev   = '';
        $l_text  = rawWiki($ID,'');
        $l_head  = '<a class="wikilink1" href="'.wl($ID).'">'.
            $ID.' '.dformat((int) @filemtime(wikiFN($ID))).'</a> '.
            $lang['current'];

        $r_rev   = '';
        $r_text  = cleanText($text);
        $r_head  = $lang['yours'];
    }else{
        if($rev1 && isset($rev2) && $rev2){            // two specific revisions wanted
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
            $revs = getRevisions($ID, 0, 1);
            $l_rev = $revs[0];
            $REV = $l_rev; // store revision back in $REV
        }

        // when both revisions are empty then the page was created just now
        if(!$l_rev && !$r_rev){
            $l_text = '';
        }else{
            $l_text = rawWiki($ID,$l_rev);
        }
        $r_text = rawWiki($ID,$r_rev);

        list($l_head, $r_head, $l_minor, $r_minor) = html_diff_head($l_rev, $r_rev, null, false, $type == 'inline');
    }

    $df = new Diff(explode("\n",$l_text),explode("\n",$r_text));

    if($type == 'inline'){
        $tdf = new InlineDiffFormatter();
    } else {
        $tdf = new TableDiffFormatter();
    }

    if($intro) print p_locale_xhtml('diff');

    if (!$text) {
        ptln('<div class="diffoptions">');

        $form = new Doku_Form(array('action'=>wl()));
        $form->addHidden('id',$ID);
        $form->addHidden('rev2[0]',$l_rev);
        $form->addHidden('rev2[1]',$r_rev);
        $form->addHidden('do','diff');
        $form->addElement(form_makeListboxField(
                            'difftype',
                            array(
                                'sidebyside' => $lang['diff_side'],
                                'inline'     => $lang['diff_inline']),
                            $type,
                            $lang['diff_type'],
                            '','',
                            array('class'=>'quickselect')));
        $form->addElement(form_makeButton('submit', 'diff','Go'));
        $form->printForm();

        $diffurl = wl($ID, array(
                        'do'       => 'diff',
                        'rev2[0]'  => $l_rev,
                        'rev2[1]'  => $r_rev,
                        'difftype' => $type,
                      ));
        ptln('<p><a class="wikilink1" href="'.$diffurl.'">'.$lang['difflink'].'</a></p>');
        ptln('</div>');
    }
    ?>
    <div class="table">
    <table class="diff diff_<?php echo $type?>">
    <?php if ($type == 'inline') { ?>
    <tr>
    <th class="diff-lineheader">-</th><th <?php echo $l_minor?>>
    <?php echo $l_head?>
    </th>
    </tr>
    <tr>
    <th class="diff-lineheader">+</th><th <?php echo $r_minor?>>
    <?php echo $r_head?>
    </th>
    </tr>
    <?php } else { ?>
    <tr>
    <th colspan="2" <?php echo $l_minor?>>
    <?php echo $l_head?>
    </th>
    <th colspan="2" <?php echo $r_minor?>>
    <?php echo $r_head?>
    </th>
    </tr>
    <?php }
    echo html_insert_softbreaks($tdf->format($df)); ?>
    </table>
    </div>
    <?php
}
