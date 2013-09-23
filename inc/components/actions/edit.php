<?php

/**
 * Saves a draft on preview
 *
 * @todo this currently duplicates code from ajax.php :-/
 */
function act_draftsave($act){
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
    return $act;
}

/**
 * Handle 'edit', 'preview', 'recover'
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function act_edit($act){
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

    return $act;
}

function act_edit_perm() 
{
	global $INFO;
	if ($INFO['exists']) return AUTH_EDIT;
	return AUTH_CREATE;
}

class Doku_Action_Edit extends Doku_Action
{
	public function action() { return "edit"; }

	// auth_edit will check again, and if without AUTH_EDIT previlidge, 
	// will do a source show.
	public function permission_required() { return AUTH_READ; }
	
	public function handle() { return act_edit($this->action()); }
}

class Doku_Action_Preview extends Doku_Action
{
	public function action() { return "preview"; }

	public function permission_required() { return act_edit_perm(); }
	
	public function handle() { 
		act_draftsave($this->action());
		return act_edit($this->action()); 
	}
}

class Doku_Action_Recover extends Doku_Action
{
	public function action() { return "recover"; }

	public function permission_required() { return act_edit_perm(); }
	
	public function handle() { return act_edit($this->action()); }
}
