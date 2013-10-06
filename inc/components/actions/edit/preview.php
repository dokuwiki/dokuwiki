<?php

include_once(DOKU_INC . "/inc/components/action.php");
include_once(DOKU_INC . "/inc/components/actions/edit_common.php");

/**
 * Handler for the preview action
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Preview extends Doku_Action_Edit_Common
{
    /**
     * Specify the action name
     * 
     * @return string the action name
     */
    public function action() { return "preview"; }

    /**
     * Saves a draft on preview. was originally act_draftsave()
     *
     * @todo this currently duplicates code from ajax.php :-/
     */
    private function draft_save() {
        global $INFO;
        global $ID;
        global $INPUT;
        global $conf;
        if($conf['usedraft'] && $INPUT->post->has('wikitext')) {
            $draft = array('id'     => $ID,
                    'prefix' => substr($INPUT->post->str('prefix'), 0, -1),
                    'text'   => $INPUT->post->str('wikitext'),
                    'suffix' => $INPUT->post->str('suffix'),
                    'date'   => $INPUT->post->int('date'),
                    'client' => $INFO['client'],
                    );
            $cname = getCacheName($draft['client'].$ID,'.draft');
            if(io_saveFile($cname,serialize($draft))){
                $INFO['draft'] = $cname;
            }
        }
    }

    /**
     * handle preview
     * 
     * @return string the next action
     */
    public function handle() {
        $this->draft_save();
        return parent::handle();
    }

    /**
     * The Doku_Action interface to display the preview
     * 
     * @global string $ID
     * @global string $REV
     * @global array $INFO
     * @global string $TEXT
     */
    public function html() {
        global $ID;
        global $REV;
        global $INFO;
        global $TEXT;

        // show the editing form
        parent::html();

        // show the preview
        $secedit = false; // do not show secedit buttons
        //PreviewHeader
        echo '<br id="scroll__here" />';
        echo p_locale_xhtml('preview');
        echo '<div class="preview"><div class="pad">';
        $html = html_secedit(
                    p_render('xhtml',p_get_instructions($TEXT),$info),
                    $secedit);
        if($INFO['prependTOC']) $html = tpl_toc(true).$html;
        echo $html;
        echo '<div class="clearer"></div>';
        echo '</div></div>';
    }
}

