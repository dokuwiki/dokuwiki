<?php

/**
 * Display the default edit form
 *
 * Is the default action for HTML_EDIT_FORMSELECTION.
 */
function html_edit_form($param) {
    global $TEXT;

    if ($param['target'] !== 'section') {
        msg('No editor for edit target ' . hsc($param['target']) . ' found.', -1);
    }

    $attr = array('tabindex'=>'1');
    if (!$param['wr']) $attr['readonly'] = 'readonly';

    $param['form']->addElement(form_makeWikiText($TEXT, $attr));
}

/**
 * The root class for editing actions, including edit, preview, and revert
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
abstract class Doku_Action_Edit_Common extends Doku_Action
{
    /**
     * The Doku_Action interface to specify the permission required for 
     * editting.
     * 
     * @global type $INFO
     * @return string permission required
     */
    public function permission_required() {
        global $INFO;
        if ($INFO['exists']) return AUTH_EDIT;
        return AUTH_CREATE;
    }

    /**
     * Doku_Action interface for handling editting
     * 
     * @global type $ID
     * @global type $INFO
     * @global type $TEXT
     * @global type $RANGE
     * @global type $PRE
     * @global type $SUF
     * @global type $REV
     * @global type $SUM
     * @global type $lang
     * @global type $DATE
     */
    public function handle() {
        global $ID;
        global $INFO;

        global $TEXT;
        global $RANGE;
        global $PRE;
        global $SUF;
        global $REV;
        global $SUM;
        global $lang;
        global $DATE;

        if (!isset($TEXT)) {
            if ($INFO['exists']) {
                if ($RANGE) {
                    list($PRE,$TEXT,$SUF) = rawWikiSlices($RANGE,$ID,$REV);
                } else {
                    $TEXT = rawWiki($ID,$REV);
                }
            } else {
                $TEXT = pageTemplate($ID);
            }
        }

        //set summary default
        if(!$SUM){
            if($REV){
                $SUM = sprintf($lang['restored'], dformat($REV));
            }elseif(!$INFO['exists']){
                $SUM = $lang['created'];
            }
        }

        // Use the date of the newest revision, not of the revision we edit
        // This is used for conflict detection
        if(!$DATE) $DATE = @filemtime(wikiFN($ID));

        //check if locked by anyone - if not lock for my self
        //do not lock when the user can't edit anyway
        if ($INFO['writable']) {
            $lockedby = checklock($ID);
            if($lockedby) return 'locked';
            lock($ID);
        }
    }

    /**
     * Adds a checkbox for minor edits for logged in users
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    private function html_minoredit(){
        global $conf;
        global $lang;
        global $INPUT;
        // minor edits are for logged in users only
        if(!$conf['useacl'] || !$_SERVER['REMOTE_USER']){
            return false;
        }

        $p = array();
        $p['tabindex'] = 3;
        if($INPUT->bool('minor')) $p['checked']='checked';
        return form_makeCheckboxField('minor', '1', $lang['minoredit'], 'minoredit', 'nowrap', $p);
    }

    /**
     * The Doku_Action interface to return html pag for editing
     * @global array $INPUT
     * @global string $ID
     * @global string $REV
     * @global string $DATE
     * @global string $PRE
     * @global string $SUF
     * @global array $INFO
     * @global string $SUM
     * @global string $lang
     * @global array $conf
     * @global string $TEXT
     * @global string $RANGE
     * @global string $license
     */
    public function html() {
        global $INPUT;
        global $ID;
        global $REV;
        global $DATE;
        global $PRE;
        global $SUF;
        global $INFO;
        global $SUM;
        global $lang;
        global $conf;
        global $TEXT;
        global $RANGE;

        if ($INPUT->has('changecheck')) {
            $check = $INPUT->str('changecheck');
        } elseif(!$INFO['exists']){
            // $TEXT has been loaded from page template
            $check = md5('');
        } else {
            $check = md5($TEXT);
        }
        $mod = md5($TEXT) !== $check;

        $wr = $INFO['writable'] && !$INFO['locked'];
        $include = 'edit';
        if($wr){
            if ($REV) $include = 'editrev';
        }else{
            // check pseudo action 'source'
            if(!actionOK('source')){
                msg('Command disabled: source',-1);
                return;
            }
            $include = 'read';
        }

        global $license;

        $form = new Doku_Form(array('id' => 'dw__editform'));
        $form->addHidden('id', $ID);
        $form->addHidden('rev', $REV);
        $form->addHidden('date', $DATE);
        $form->addHidden('prefix', $PRE . '.');
        $form->addHidden('suffix', $SUF);
        $form->addHidden('changecheck', $check);

        $data = array('form' => $form,
                      'wr'   => $wr,
                      'media_manager' => true,
                      'target' => ($INPUT->has('target') && $wr) ? $INPUT->str('target') : 'section',
                      'intro_locale' => $include);

        if ($data['target'] !== 'section') {
            // Only emit event if page is writable, section edit data is valid and
            // edit target is not section.
            trigger_event('HTML_EDIT_FORMSELECTION', $data, 'html_edit_form', true);
        } else {
            html_edit_form($data);
        }
        if (isset($data['intro_locale'])) {
            echo p_locale_xhtml($data['intro_locale']);
        }

        $form->addHidden('target', $data['target']);
        $form->addElement(form_makeOpenTag('div', array('id'=>'wiki__editbar', 'class'=>'editBar')));
        $form->addElement(form_makeOpenTag('div', array('id'=>'size__ctl')));
        $form->addElement(form_makeCloseTag('div'));
        if ($wr) {
            $form->addElement(form_makeOpenTag('div', array('class'=>'editButtons')));
            $form->addElement(form_makeButton('submit', 'save', $lang['btn_save'], array('id'=>'edbtn__save', 'accesskey'=>'s', 'tabindex'=>'4')));
            $form->addElement(form_makeButton('submit', 'preview', $lang['btn_preview'], array('id'=>'edbtn__preview', 'accesskey'=>'p', 'tabindex'=>'5')));
            $form->addElement(form_makeButton('submit', 'draftdel', $lang['btn_cancel'], array('tabindex'=>'6')));
            $form->addElement(form_makeCloseTag('div'));
            $form->addElement(form_makeOpenTag('div', array('class'=>'summary')));
            $form->addElement(form_makeTextField('summary', $SUM, $lang['summary'], 'edit__summary', 'nowrap', array('size'=>'50', 'tabindex'=>'2')));
            $elem = $this->html_minoredit();
            if ($elem) $form->addElement($elem);
            $form->addElement(form_makeCloseTag('div'));
        }
        $form->addElement(form_makeCloseTag('div'));
        if($wr && $conf['license']){
            $form->addElement(form_makeOpenTag('div', array('class'=>'license')));
            $out  = $lang['licenseok'];
            $out .= ' <a href="'.$license[$conf['license']]['url'].'" rel="license" class="urlextern"';
            if($conf['target']['extern']) $out .= ' target="'.$conf['target']['extern'].'"';
            $out .= '>'.$license[$conf['license']]['name'].'</a>';
            $form->addElement($out);
            $form->addElement(form_makeCloseTag('div'));
        }

        if ($wr) {
            // sets changed to true when previewed
            echo '<script type="text/javascript">/*<![CDATA[*/'. NL;
            echo 'textChanged = ' . ($mod ? 'true' : 'false');
            echo '/*!]]>*/</script>' . NL;
        } ?>
        <div class="editBox" role="application">

        <div class="toolbar group">
            <div id="draft__status">
                <?php if(!empty($INFO['draft'])) echo $lang['draftdate'].' '.dformat();?>
            </div>
            <div id="tool__bar">
                <?php 
                    if ($wr && $data['media_manager']){
                ?>
                <a href="<?php echo DOKU_BASE?>lib/exe/mediamanager.php?ns=<?php echo $INFO['namespace']?>"
                target="_blank">
                    <?php echo $lang['mediaselect']; ?>
                </a>
                <?php
                    }
                ?>
            </div>        
        </div>
        <?php

        html_form('edit', $form);
        print '</div>'.NL;
    }
}
class Doku_Action_Edit extends Doku_Action_Edit_Common
{
    /**
     * The Doku_Action interface to specify the action that this handler
     * can handle.
     * 
     * @global type $INFO
     * @return string permission required
     */
    public function action() { return "edit"; }

    /**
     * The Doku_Action interface to specify the permission required for 
     * editting.
     * 
     * @global type $INFO
     * @return string permission required
     */
    public function permission_required() { return AUTH_READ; }
}

class Doku_Action_Preview extends Doku_Action_Edit_Common
{
    /**
     * The Doku_Action interface to specify the action that this handler
     * can handle.
     * 
     * @global type $INFO
     * @return string permission required
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
     * The Doku_Action interface for handling preview
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

class Doku_Action_Recover extends Doku_Action_Edit_Common
{
    /**
     * The Doku_Action interface to specify the action that this handler
     * can handle.
     * 
     * @global type $INFO
     * @return string permission required
     */
    public function action() { return "recover"; }
}
