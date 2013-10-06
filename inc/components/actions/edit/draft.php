<?php

include_once(DOKU_INC . "/inc/components/action.php");

/**
 * Handler for the draft action
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Draft extends Doku_Action
{
    /**
     * Specify the action name.
     * 
     * @return string the action name
     */
    public function action() { return "draft"; }

    /**
     * Specify the required permissions for handling drafts.
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_EDIT;
    }

    /**
     * handle the draft action
     * 
     * @global array $INFO
     * @return string the action to take next
     */
    public function handle() {
        global $INFO;
        if (!file_exists($INFO['draft'])) return 'edit';
    }

    /**
     * ask the user about how to handle an exisiting draft
     * was html_draft() by Andreas Gohr <andi@splitbrain.org>
     *
     * @global type $INFO
     * @global type $ID
     * @global type $lang
     */
    public function html() {
        global $INFO;
        global $ID;
        global $lang;
        $draft = unserialize(io_readFile($INFO['draft'],false));
        $text  = cleanText(con($draft['prefix'],$draft['text'],$draft['suffix'],true));

        print p_locale_xhtml('draft');
        $form = new Doku_Form(array('id' => 'dw__editform'));
        $form->addHidden('id', $ID);
        $form->addHidden('date', $draft['date']);
        $form->addElement(form_makeWikiText($text, array('readonly'=>'readonly')));
        $form->addElement(form_makeOpenTag('div', array('id'=>'draft__status')));
        $form->addElement($lang['draftdate'].' '. dformat(filemtime($INFO['draft'])));
        $form->addElement(form_makeCloseTag('div'));
        $form->addElement(form_makeButton('submit', 'recover', $lang['btn_recover'], array('tabindex'=>'1')));
        $form->addElement(form_makeButton('submit', 'draftdel', $lang['btn_draftdel'], array('tabindex'=>'2')));
        $form->addElement(form_makeButton('submit', 'show', $lang['btn_cancel'], array('tabindex'=>'3')));
        html_form('draft', $form);
    }
}
