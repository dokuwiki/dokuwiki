<?php

/**
 * Handler for action recent
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Recent extends Doku_Action
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "recent";
    }

    /**
     * Specifies the required permission level to display recent changes
     * 
     * @return string the permission
     */
    public function permission_required() {
        return AUTH_READ;
    }

    /**
     * handle the recent action
     * 
     * @global Input $INPUT
     */
    public function handle() {
        global $INPUT;
        $show_changes = $INPUT->str('show_changes');
        if (!empty($show_changes)) {
            set_doku_pref('show_changes', $show_changes);
        }
    }
}

/**
 * Renderer for action recent
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Renderer_Recent extends Doku_Action_Renderer
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "recent";
    }

    /**
     * display recent changes
     * Was html_recent() by
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     * @author Ben Coburn <btcoburn@silicodon.net>
     * @author Kate Arzamastseva <pshns@ukr.net>
     *
     * @global Input $INPUT
     * @global array $conf
     * @global array $lang
     * @global string $ID
     */
    public function xhtml() {
        global $INPUT;
        global $conf;
        global $lang;
        global $ID;

        $show_changes = $INPUT->str('show_changes');
        if (empty($show_changes)) {
            $show_changes = get_doku_pref('show_changes', $show_changes);
        }
        $first=$INPUT->extract('first')->int('first');
        /* we need to get one additionally log entry to be able to
         * decide if this is the last page or is there another one.
         * This is the cheapest solution to get this information.
         */
        $flags = 0;
        if ($show_changes == 'mediafiles' && $conf['mediarevisions']) {
            $flags = RECENTS_MEDIA_CHANGES;
        } elseif ($show_changes == 'pages') {
            $flags = 0;
        } elseif ($conf['mediarevisions']) {
            $show_changes = 'both';
            $flags = RECENTS_MEDIA_PAGES_MIXED;
        }

        $recents = getRecents($first,$conf['recent'] + 1,getNS($ID),$flags);
        if(count($recents) == 0 && $first != 0){
            $first=0;
            $recents = getRecents($first,$conf['recent'] + 1,getNS($ID),$flags);
        }
        $hasNext = false;
        if (count($recents)>$conf['recent']) {
            $hasNext = true;
            array_pop($recents); // remove extra log entry
        }

        print p_locale_xhtml('recent');

        if (getNS($ID) != '')
            print '<div class="level1"><p>' . sprintf($lang['recent_global'], getNS($ID), wl('', 'do=recent')) . '</p></div>';

        $form = new Doku_Form(array('id' => 'dw__recent', 'method' => 'GET', 'class' => 'changes'));
        $form->addHidden('sectok', null);
        $form->addHidden('do', 'recent');
        $form->addHidden('id', $ID);

        if ($conf['mediarevisions']) {
            $form->addElement('<div class="changeType">');
            $form->addElement(form_makeListboxField(
                        'show_changes',
                        array(
                            'pages'      => $lang['pages_changes'],
                            'mediafiles' => $lang['media_changes'],
                            'both'       => $lang['both_changes']),
                        $show_changes,
                        $lang['changes_type'],
                        '','',
                        array('class'=>'quickselect')));

            $form->addElement(form_makeButton('submit', 'recent', $lang['btn_apply']));
            $form->addElement('</div>');
        }

        $form->addElement(form_makeOpenTag('ul'));

        foreach($recents as $recent){
            $date = dformat($recent['date']);
            if ($recent['type']===DOKU_CHANGE_TYPE_MINOR_EDIT)
                $form->addElement(form_makeOpenTag('li', array('class' => 'minor')));
            else
                $form->addElement(form_makeOpenTag('li'));

            $form->addElement(form_makeOpenTag('div', array('class' => 'li')));

            if ($recent['media']) {
                $form->addElement(media_printicon($recent['id']));
            } else {
                $icon = DOKU_BASE.'lib/images/fileicons/file.png';
                $form->addElement('<img src="'.$icon.'" alt="'.$recent['id'].'" class="icon" />');
            }

            $form->addElement(form_makeOpenTag('span', array('class' => 'date')));
            $form->addElement($date);
            $form->addElement(form_makeCloseTag('span'));

            $diff = false;
            $href = '';

            if ($recent['media']) {
                $diff = (count(getRevisions($recent['id'], 0, 1, 8192, true)) && @file_exists(mediaFN($recent['id'])));
                if ($diff) {
                    $href = media_managerURL(array('tab_details' => 'history',
                        'mediado' => 'diff', 'image' => $recent['id'], 'ns' => getNS($recent['id'])), '&');
                }
            } else {
                $href = wl($recent['id'],"do=diff", false, '&');
            }

            if ($recent['media'] && !$diff) {
                $form->addElement('<img src="'.DOKU_BASE.'lib/images/blank.gif" width="15" height="11" alt="" />');
            } else {
                $form->addElement(form_makeOpenTag('a', array('class' => 'diff_link', 'href' => $href)));
                $form->addElement(form_makeTag('img', array(
                                'src'   => DOKU_BASE.'lib/images/diff.png',
                                'width' => 15,
                                'height'=> 11,
                                'title' => $lang['diff'],
                                'alt'   => $lang['diff']
                                )));
                $form->addElement(form_makeCloseTag('a'));
            }

            if ($recent['media']) {
                $href = media_managerURL(array('tab_details' => 'history',
                    'image' => $recent['id'], 'ns' => getNS($recent['id'])), '&');
            } else {
                $href = wl($recent['id'],"do=revisions",false,'&');
            }
            $form->addElement(form_makeOpenTag('a', array('class' => 'revisions_link', 'href' => $href)));
            $form->addElement(form_makeTag('img', array(
                            'src'   => DOKU_BASE.'lib/images/history.png',
                            'width' => 12,
                            'height'=> 14,
                            'title' => $lang['btn_revs'],
                            'alt'   => $lang['btn_revs']
                            )));
            $form->addElement(form_makeCloseTag('a'));

            if ($recent['media']) {
                $href = media_managerURL(array('tab_details' => 'view', 'image' => $recent['id'], 'ns' => getNS($recent['id'])), '&');
                $class = (file_exists(mediaFN($recent['id']))) ? 'wikilink1' : $class = 'wikilink2';
                $form->addElement(form_makeOpenTag('a', array('class' => $class, 'href' => $href)));
                $form->addElement($recent['id']);
                $form->addElement(form_makeCloseTag('a'));
            } else {
                $form->addElement(html_wikilink(':'.$recent['id'],useHeading('navigation')?null:$recent['id']));
            }
            $form->addElement(form_makeOpenTag('span', array('class' => 'sum')));
            $form->addElement(' – '.htmlspecialchars($recent['sum']));
            $form->addElement(form_makeCloseTag('span'));

            $form->addElement(form_makeOpenTag('span', array('class' => 'user')));
            if($recent['user']){
                $form->addElement('<bdi>'.editorinfo($recent['user']).'</bdi>');
                if(auth_ismanager()){
                    $form->addElement(' <bdo dir="ltr">('.$recent['ip'].')</bdo>');
                }
            }else{
                $form->addElement('<bdo dir="ltr">'.$recent['ip'].'</bdo>');
            }
            $form->addElement(form_makeCloseTag('span'));

            $form->addElement(form_makeCloseTag('div'));
            $form->addElement(form_makeCloseTag('li'));
        }
        $form->addElement(form_makeCloseTag('ul'));

        $form->addElement(form_makeOpenTag('div', array('class' => 'pagenav')));
        $last = $first + $conf['recent'];
        if ($first > 0) {
            $first -= $conf['recent'];
            if ($first < 0) $first = 0;
            $form->addElement(form_makeOpenTag('div', array('class' => 'pagenav-prev')));
            $form->addElement(form_makeTag('input', array(
                        'type'  => 'submit',
                        'name'  => 'first['.$first.']',
                        'value' => $lang['btn_newer'],
                        'accesskey' => 'n',
                        'title' => $lang['btn_newer'].' [N]',
                        'class' => 'button show'
                        )));
            $form->addElement(form_makeCloseTag('div'));
        }
        if ($hasNext) {
            $form->addElement(form_makeOpenTag('div', array('class' => 'pagenav-next')));
            $form->addElement(form_makeTag('input', array(
                            'type'  => 'submit',
                            'name'  => 'first['.$last.']',
                            'value' => $lang['btn_older'],
                            'accesskey' => 'p',
                            'title' => $lang['btn_older'].' [P]',
                            'class' => 'button show'
                            )));
            $form->addElement(form_makeCloseTag('div'));
        }
        $form->addElement(form_makeCloseTag('div'));
        html_form('recent', $form);
    }
}

