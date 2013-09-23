<?php

/**
 * Sanitize the action command
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function act_clean($act){
    // check if the action was given as array key
    if(is_array($act)){
        list($act) = array_keys($act);
    }

    //remove all bad chars
    $act = strtolower($act);
    $act = preg_replace('/[^1-9a-z_]+/','',$act);

    if($act == 'export_html') $act = 'export_xhtml';
    if($act == 'export_htmlbody') $act = 'export_xhtmlbody';

    if($act === '') $act = 'show';
    return $act;
}

/**
 * Do a redirect after receiving post data
 *
 * Tries to add the section id as hash mark after section editing
 */
function act_redirect($id,$preact){
    global $PRE;
    global $TEXT;

    $opts = array(
            'id'       => $id,
            'preact'   => $preact
            );
    //get section name when coming from section edit
    if($PRE && preg_match('/^\s*==+([^=\n]+)/',$TEXT,$match)){
        $check = false; //Byref
        $opts['fragment'] = sectionID($match[0], $check);
    }

    trigger_event('ACTION_SHOW_REDIRECT',$opts,'act_redirect_execute');
}

/**
 * Execute the redirect
 *
 * @param array $opts id and fragment for the redirect
 */
function act_redirect_execute($opts){
    $go = wl($opts['id'],'',true);
    if(isset($opts['fragment'])) $go .= '#'.$opts['fragment'];
    //show it
    send_redirect($go);
}

abstract class Doku_Action extends Doku_Component
{
    private static $_actions = array();

    public static function act($action) {
        global $INFO;
        // clean the action to make it sane
        $action = act_clean($action);
        $evt = new Doku_Event('ACTION_ACT_PREPROCESS',$action);
        if ($evt->advise_before()) {
            $action = $evt->data;
            // check if the action is disabled
            if (!actionOK($action)) {
                msg('action disabled: ' . htmlspecialchars($action), -1);
                return self::act("show");
            }
            if (substr($action, 0, 7) === "export_") $action = "export";
            // check if we can handle it
            if (!array_key_exists($action, self::$_actions)) {
                msg('Unknown command: ' . htmlspecialchars($action), -1);
                return self::act("show");
            }
            $handler = self::$_actions[$action];
            // check permission
            if ($handler->permission_required() > $INFO['perm'])
                return self::act('denied');
            //try to unlock
            global $ID;
            unlock($ID);
            // perform the action
            $new_action = $handler->handle();
            if ($new_action !== null && $new_action !== $action) {
                act_redirect($ID, $new_action);
                return true;
            }
        }  // end event ACTION_ACT_PREPROCESS default action
        $evt->advise_after();
        unset($evt);

        global $conf;
        global $license;
        global $ACT;
        $ACT = $action;

        //call template FIXME: all needed vars available?
        $headers[] = 'Content-Type: text/html; charset=utf-8';
        trigger_event('ACTION_HEADERS_SEND',$headers,'act_sendheaders');

        include(template('main.php'));
        // output for the actions is now handled in inc/templates.php
        // in function tpl_content()

        return true;
    }

    // register($handler) is a private method that registers an handler to
    // handle the action given by $handler->action()
    // the parameter $handler must be an object of a subclass of Doku_Action
    private static function register($handler) {
        if (!is_subclass_of($handler, "Doku_action", false)) return;
        $action = $handler->action();
        if (array_key_exists($action, self::$_actions)) {
            $old_handler_class = get_class(self::$_action[$action]);
            $handler_class = get_class($handler);
            msg("action $c has conflict handers, previously registered handler was 
                $old_handler_class, now is handled by $handler_class", -1);
        }
        self::$_actions[$action] = $handler;
    }

    /** action() should return the name of the action that this handler
     *  can handle, e.g., 'edit', 'show', etc.
     */
    abstract public function action();

    /** permission_required() should return the permission level that
     *  this action needs, e.g., 'AUTH_NONE', 'AUTH_READ', etc.
     */
    abstract public function permission_required();

    /** handle() method perform the action, 
     *  and return a command to be passed to
     *  the main template to display the result.
     *  If there should be no change in action name, 
     *  the return value can be omitted.
     */
    public function handle() { }

    /** Doku_Action() is the initializer, by default it registers 
     *  the action handler 
     */
    public function __construct() {
        self::register($this);
    }

}
