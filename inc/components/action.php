<?php

/**
 * Doku_Action class is the parent class of all actions. 
 * It has two interfaces: 
 *   - a static one that acts as action handler managers
 *   - an interface that specifies what actions should define
 * 
 * @author Junling Ma <junglingm@gmail.com> 
 */
abstract class Doku_Action extends Doku_Component
{
    // _actions maps action names to their handlers
    private static $_actions = array();

    /**
     * Sanitize the action command
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    private static function act_clean($act){
        // check if the action was given as array key
        if(is_array($act)){
            list($act) = array_keys($act);
        }

        //remove all bad chars
        $act = preg_replace('/[^1-9a-z_]+/','',strtolower($act));

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
    private static function redirect($id, $act){
        global $PRE;
        global $TEXT;

        $opts = array(
                'id'       => $id,
                'preact'   => $act
                );
        //get section name when coming from section edit
        if($PRE && preg_match('/^\s*==+([^=\n]+)/',$TEXT,$match)){
            $check = false; //Byref
            $opts['fragment'] = sectionID($match[0], $check);
        }

        $evt = new Doku_Event('ACTION_SHOW_REDIRECT',$opts);
        // broadcast ACTION_ACT_PREPROCESS
        if ($evt->advise_before()) {
            $go = wl($evt->data['id'],'',true);
            if(isset($opts['fragment'])) $go .= '#'.$opts['fragment'];
            //show it
            send_redirect($go);
        } // end of the default handler for ACTION_ACT_PREPROCESS
        $evt->advise_after();
        unset($evt);
    }

    /**
     * The Doku_Action public interface to perform an action
     * 
     * @global array $INFO
     * @global string $ID
     * @global string $ACT
     * @param type $action the action to perform
     * @return boolean whether the action was suscessful
     */
    public static function act($action) {
        global $INFO;
        // clean the action to make it sane
        $action = self::act_clean($action);
        $evt = new Doku_Event('ACTION_ACT_PREPROCESS',$action);
        // broadcast ACTION_ACT_PREPROCESS
        if ($evt->advise_before()) {
            // event ACTION_ACT_PREPROCESS default action
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
                self::redirect($ID, $new_action);
                return true;
            }
        }  // end event ACTION_ACT_PREPROCESS default action
        $evt->advise_after();
        unset($evt);

        // we need $INFO, $conf, $license in main.php!!!
        global $INFO;
        global $conf;
        global $license;
        global $ACT;
        $ACT = $action;

        //call template FIXME: all needed vars available?
        $headers[] = 'Content-Type: text/html; charset=utf-8';
        trigger_event('ACTION_HEADERS_SEND',$headers,'act_sendheaders');

        include(template('main.php'));

        return true;
    }

    /**
     * Doku_Action public interface to display the result of an action
     * 
     * @param type $action the action to display
     * @return boolean whether the results has been successfully displayed
     */
    public static function display_html($action) {
        ob_start();
        $evt = new Doku_Event('TPL_ACT_RENDER', $action);
        // broadcast TPL_ACT_RENDER
        if ($evt->advise_before()) {
            $action = $evt->data;
            // check if we can handle it
            if (!array_key_exists($action, self::$_actions)) {
                $evt = new Doku_Event('TPL_ACT_UNKNOWN', $action);
                if($evt->advise_before())
                    msg("Failed to handle command: ".hsc($action), -1);
                $evt->advise_after();
                unset($evt);
                return false;
            }
            $handler = self::$_actions[$action];
            return $handler->html();
        }  // end event TPL_ACT_RENDER default action
        $evt->advise_after();
        unset($evt);
        $html_output = ob_get_clean();
        trigger_event('TPL_CONTENT_DISPLAY', $html_output, 'ptln');
        return !empty($html_output);
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
            msg("action $action has conflict handers, previously registered handler was 
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

    /** html() should return the html for the actiontp be displayed.
     */
    public function html() { }

    /** Doku_Action() is the initializer, by default it registers 
     *  the action handler 
     */
    public function __construct() {
        self::register($this);
    }

    /**
     * A debug function
     */
    public static function print_actions() {
        echo "<H1>Action Handlers</H1>\n<ul>\n";
        foreach (self::$_actions as $action => $handler) {
            echo "<li>$action is handled by " . get_class($handler) . "</li>\n";
        }
        echo "</ul>\n";
    }
}

/**
 * Print the content
 *
 * This function is used for printing all the usual content
 * (defined by the global $ACT var) by calling the appropriate
 * outputfunction(s) from html.php
 *
 * Everything that doesn't use the main template file isn't
 * handled by this function. ACL stuff is not done here either.
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * trvised by Junling Ma <junlingm@gmail.com>
 * @triggers TPL_ACT_RENDER
 * @triggers TPL_CONTENT_DISPLAY
 * @param bool $prependTOC should the TOC be displayed here?
 * @return bool true if any output
 */
function tpl_content($prependTOC = true) {
    global $ACT;
    global $INFO;
    $INFO['prependTOC'] = $prependTOC;
    Doku_Action::display_html($ACT);
}
