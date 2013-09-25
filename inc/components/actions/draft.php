<?php

/**
 * Handler for the draft action
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Draft extends Doku_Action
{
    /**
     * The Doku_Action interface to specify the action name that this
     * handler can handle.
     * 
     * @return string the action name
     */
    public function action() { return "draft"; }

    /**
     * The Doku_Action interface to specify the required permissions
     * for draft action.
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_EDIT;
    }

    /**
     * The Doku_Action interface to handle action draft
     * 
     * @global array $INFO
     * @return string the action to take next
     */
    public function handle() {
        global $INFO;
        if (!file_exists($INFO['draft'])) return 'edit';
    }

    /**
     * The Doku_Action interface to return the html output of action draft
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
