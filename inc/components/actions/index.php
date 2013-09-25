<?php

/**
 * Handler for action index
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Index extends Doku_Action
{
    /**
     * The Doku_Action interface to specify the action name that this
     * handler can handle.
     * 
     * @return string the action name
     */
    public function action() {
        return "index";
    }

    /**
     * The Doku_Action interface to specify the required permissions
     * for action index.
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_NONE;
    }

    /**
     * Doku_Action interface to display page index
     * was html_index() by
     * @author Andreas Gohr <andi@splitbrain.org>
     * 
     * @global array $conf
     * @global string $ID
     * @global string $IDX
     */
    public function html(){
        global $conf;
        global $ID;
        global $IDX;

        $ns  = cleanID($IDX);
        #fixme use appropriate function
        if(empty($ns)){
            $ns = dirname(str_replace(':','/',$ID));
            if($ns == '.') $ns ='';
        }
        $ns  = utf8_encodeFN(str_replace(':','/',$ns));

        echo p_locale_xhtml('index');
        echo '<div id="index__tree">';

        $data = array();
        search($data,$conf['datadir'],'search_index',array('ns' => $ns));
        echo html_buildlist($data,'idx','html_list_index','html_li_index');

        echo '</div>';
    }
}
