<?php

include_once(DOKU_INC . "/inc/components/action.php");

/**
 * Handler for action index
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Index extends Doku_Action
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() {
        return "index";
    }

    /**
     * Specifies the required permissions for indexing.
     * The original permission was AUTH_NONE, but shouldn't we
     * require whoever can read for indexing?
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_READ;
    }

    /**
     * Display the page index
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
