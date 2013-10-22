<?php

define(DOKU_ACTIONS_FOLDER, 'commands');
define(DOKU_ACTIONS_ROOT, DOKU_INC . 'inc/' . DOKU_ACTIONS_FOLDER);

// include the old action handlers before we implement them all
include_once(DOKU_INC . 'inc/actions.php');

/**
 * These handlers are called right before an action is handled, so that
 * plugins have a change to change the data that is used by an action handler
 * 
 * A preprocessor that inherits from a parent preprocessor will replace the
 * parent.
 * 
 * Multiple preprocessers can be defined, the order that these processors
 * are called us unpredictable. So, to ensure that a preprocessor A should
 * be called before another one B, A should inherit from B, and then calls
 * B's process().
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
abstract class Doku_Action_Preprocessor {
    /**
     * Specifies the action name that this process responds to
     *
     * @return string the action name
     */
    abstract public function action();

    /**
     * process the global data that will be passed to the action handler
     */
    abstract public function process();
}

/**
 * These handlers are called after an action is handled, but before an action
 * is rendered, so that the data that an action renders
 * 
 * A postprocessor that inherits from a parent preprocessor will replace the
 * parent. So, to ensure that a postprocessor A should
 * be called before another one B, A should inherit from B, and then calls
 * B's process().
 * 
 * Multiple postprocessers can be defined, the order that these processors
 * are called us unpredictable. 
 *
 * @author Junling Ma <junlingm@gmail.com>
 */
abstract class Doku_Action_Postprocessor {
    /**
     * Specifies the action name that this process responds to
     *
     * @return string the action name
     */
    abstract public function action();

    /**
     * process the global data that has been handled by the action handler
     */
    abstract public function process();
}

/**
 * These renderers renders the output of an action.
 * If a renderer class is extended, then the subclass replaces the parent
 * as the renderer. Two subclasses of a the same parent renderer will cause a
 * conflict, and which renderer wins out is not unpredictable.
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
abstract class Doku_Action_Renderer {
    /**
     * Specifies the action name that this process responds to
     *
     * @return string the action name
     */
    abstract public function action();

    /**
     * renders the xhtml output of an action
     */
    abstract public function xhtml();
}

/**
 * Doku_Action class is the parent class of all actions. 
 * It has two interfaces: 
 *   - a static one that acts as action handler managers
 *     * act($action_name) to handle an action;
 *     * render($action_name) to render the output of an action.
 *   - an interface that specifies what each action should implement, namely
 *     * action() returning the action name;
 *     * permission_required() returning the permission level for the action;
 *     * handle() as the action handler;
 *
 * We require that actions are defined as subclasses of Doku_Action, and if
 * a class is extended, then the subclass replaces the parent as a handler.
 * Two subclasses of a the same parent handler will cause a conflict, and
 * which handler wins out is not unpredictable.
 * 
 * The action definitions are put in a file with the same name as the action
 * in the inc/commands folder, and a plugin's commands folder (to avoid
 * conflicts with the action (event_handler) plugins
 *
 * @author Junling Ma <junglingm@gmail.com> 
 */
abstract class Doku_Action
{
    // this array stores the subclasses of an action handler
    private static $_extensions = array();
    // this array maps action names to their preprocessors
    private static $_preprocessors = array();
    // this array maps action names to their postprocessors
    private static $_postprocessors = array();
    // this holds the renderer
    private static $_renderer = null;
    // this holds the handler
    private static $_handler = null;

    // this function loads the scripts with filename $action.php in
    // the folder specified by $dir and its subfolders
    private static function load_dir($dir, $action) {
        if (!is_dir($dir)) return;

        // read the entrys of $dir one by one
        $dh = dir($dir);
        $name = $action . '.php';
        $subdirs = array();
        while (false !== ($entry = $dh->read())) {
            // skip hidden files
            if ($entry[0] == '.') continue;
            $path = $dir . '/' . $entry;
            if (strtolower($entry) == $name)
                include_once($dir . '/' . $entry);
            else if (is_dir($path))
                array_push($subdirs, $path);
        }
        $dh->close();

        // load scripts in subdirs recursively
        foreach ($subdirs as $subdir) self::load_dir($subdir, $action);
    }

    // this function registers $class as an extension of its parent class.
    private static function register_extension($class) {
        $parent = get_parent_class($class);
        if ($parent) {
            if (!array_key_exists($parent, self::$_extensions))
                self::register_extension($parent);
            array_push(self::$_extensions[$parent], $class);
        }
        self::$_extensions[$class] = array();
    }

    // This is a utility function to check if a class is abstract
    // abstract component classes will not be initialized.
    private static function is_abstract_class($class) {
        $ref_class = new ReflectionClass($class);
        $abs = $ref_class->isAbstract();
        unset($ref_class);
        return $abs;
    }

    // create an object and check if it responds to the correct action
    private static function create($class, $action) {
        $handler = new $class;
        if ($handler->action() != $action) {
            unset($handler);
            return null;
        }
        return $handler;
    }
    /**
     * Loads the scripts that can handle a given action.
     * The scripts must by located in inc/commands and an pligin's commands
     * folders.
     *
     * @param string $action the action name to load its handlers
     */
    public static function load($action) {
        // take a snapshot of currently defined classes
        $old_classes = get_declared_classes();
        // load the dirs
        self::load_dir(DOKU_ACTIONS_ROOT, $action);

        // load all the plugins
        $plugins = plugin_list();
        foreach ($plugins as $plugin) {
            $path = DOKU_PLUGIN . '/' . plugin_directory($plugin) . '/' .
                DOKU_ACTIONS_FOLDER;
            if (is_dir($path)) $this->load_dir ($path, $action);
        }

        // get an array of newly defined classes from the includes
        $classes = get_declared_classes();
        $new_classes = array_diff($classes, $old_classes);
        // inspect each new class
        foreach ($new_classes as $class) {
            if (is_subclass_of($class, 'Doku_Action') ||
                is_subclass_of($class, 'Doku_Action_Preprocess') ||
                is_subclass_of($class, 'Doku_Action_Postprocess') ||
                is_subclass_of($class, 'Doku_Action_Renderer')) {
                // register the class as an extension of its parent
                self::register_extension($class);
            }
        }

        // clear the previously loaded handlers
        self::$_handler = null;
        self::$_renderer = null;
        self::$_preprocessors = array();
        self::$_postprocessors = array();

        // initialize all the leaf classes (that are not extended).
        // They are components that we should use.
        foreach(self::$_extensions as $component => $extensions)
            if (!$extensions && !self::is_abstract_class($component)) {
                if (is_subclass_of($component, 'Doku_Action_Preprocessor')) {
                    $handler = $this->create($component, $action);
                    if ($handler) array_push(self::$_preprocessors, $handler);
                }
                elseif (is_subclass_of($component,
                    'Doku_Action_Postprocessor')) {
                    $handler = $this->create($component, $action);
                    if ($handler) array_push(self::$_postprocessors, $handler);
                }
                elseif (is_subclass_of($component, 'Doku_Action_Renderer')) {
                    $renderer = self::create($component, $action);
                    if (!$renderer) continue;
                    if (self::$_renderer !== null) {
                        global $INFO;
                        $old_renderer = get_class(self::$_renderer);
                        if ($INFO['isadmin'])
                            msg("Action $action has conflicting renderers: 
                                $component has replaced $old_renderer");
                        unset(self::$_renderer);
                    }
                    self::$_renderer = $renderer;
                }
                elseif (is_subclass_of($component, 'Doku_Action')) {
                    $handler = self::create($component, $action);
                    if (!$handler) continue;
                    if (self::$_handler !== null) {
                        global $INFO;
                        $old_hanlder = get_class(self::$_handler);
                        if ($INFO['isadmin'])
                            msg("Action $action has conflicting handlers: 
                                $component has replaced $old_handler");
                    }
                    self::$_handler = $handler;
                }
            }
    }

    /**
     * Sanitize the action command
     * adapted from act_clean() by
     * @author Andreas Gohr <andi@splitbrain.org>
     * 
     * @global string $ACT
     * @param string $act the action name to clean
     * @return string the cleaned action name
     */
    private static function act_clean($act){
        global $ACT;
        // check if the action was given as array key
        if(is_array($act)){
            list($act) = array_keys($act);
        }

        //remove all bad chars
        $act = preg_replace('/[^1-9a-z_]+/','',strtolower($act));

        if($act == 'export_html') $act = 'export_xhtml';
        if($act == 'export_htmlbody') $act = 'export_xhtmlbody';

        if($act === '') $act = 'show';
        $ACT = $act;
        return $act;
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

        // broadcast ACTION_ACT_PREPROCESS
        $evt = new Doku_Event('ACTION_ACT_PREPROCESS',$action);
        if ($evt->advise_before()) {
            // event ACTION_ACT_PREPROCESS default action
            $action = $evt->data;

            // check if the action is disabled
            if (!actionOK($action)) {
                msg('action disabled: ' . htmlspecialchars($action), -1);
                return self::act("show");
            }
            // all export_* actions are lumped together
            if (substr($action, 0, 7) === "export_") $action = "export";

            self::load($action);
            foreach (self::$_preprocessors as $preprocessor)
                self::$_preprocessor->process();

            if (self::$_handler === null)
                msg("Old handler for $action", 0);

            global $ID;
            // check permission
            if (self::$_handler->permission_required() > $INFO['perm'])
                return self::act('denied');
            // try to unlock
            unlock($ID);
            // handle the action
            $new_action = self::$_handler->handle();

            // postprocess
            foreach (self::$_postprocessors as $preprocessor)
                self::$_postprocessor->process();

            // handle the next action
            if ($new_action !== null && $new_action !== $action)
                return self::act($new_action);
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
     * Doku_Action public interface to render the result of an action
     * 
     * @param type $action the action to display
     * @return boolean whether the results has been successfully displayed
     */
    public static function render($action) {
        ob_start();
        // broadcast TPL_ACT_RENDER
        $evt = new Doku_Event('TPL_ACT_RENDER', $action);
        if ($evt->advise_before()) {
            // the default rendering method
            $action = $evt->data;

            // check if we can handle it
            if (self::$_renderer === null) {
                tpl_content_core();
            } else {
                self::$_renderer->xhtml();
            }
        }  // end event TPL_ACT_RENDER default action
        $evt->advise_after();
        unset($evt);
        $html_output = ob_get_clean();
        trigger_event('TPL_CONTENT_DISPLAY', $html_output, 'ptln');
        return !empty($html_output);
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
    Doku_Action::render($ACT);
}
