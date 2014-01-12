<?php

include_once(dirname(__FILE__) . '/diff_lib.php');

/**
 * handler for action conflict
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Conflict extends Doku_Action
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "conflict";
    }

    /**
     * Specifies the required permission level to handle editing conflicts
     * 
     * @return string the permission
     */
    public function permission_required() {
        return AUTH_EDIT;
    }
}

/**
 * renderer for action conflict
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Renderer_Conflict extends Doku_Action_Renderer
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "conflict";
    }

    /**
     * Show warning on conflict detection
     * adapter from html_conflict() by 
     * @author Andreas Gohr <andi@splitbrain.org>
     * 
     * @global string $PRE
     * @global string $TEXT
     * @global string $SUF
     * @global string $SUM
     * @global string $ID
     * @global array $lang
     */
    public function xhtml() {
        global $PRE;
        global $TEXT;
        global $SUF;
        global $SUM;
        global $ID;
        global $lang;

        // show the conflict error
        $text = con($PRE, $TEXT, $SUF);
        print p_locale_xhtml('conflict');
        $form = new Doku_Form(array('id' => 'dw__editform'));
        $form->addHidden('id', $ID);
        $form->addHidden('wikitext', $text);
        $form->addHidden('summary', $summary);
        $form->addElement(form_makeButton('submit', 'save', $lang['btn_save'], array('accesskey'=>'s')));
        $form->addElement(form_makeButton('submit', 'cancel', $lang['btn_cancel']));
        html_form('conflict', $form);
        print '<br /><br /><br /><br />'.NL;

        // show diffs
        html_diff($text, false);
    }
}
