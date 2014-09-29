<?php
if(!defined('DOKU_INC')) die('meh.');
require_once DOKU_INC . 'inc/parser/lexer.php';
require_once DOKU_INC . 'inc/parser/handler.php';


/**
 * Define various types of modes used by the parser - they are used to
 * populate the list of modes another mode accepts
 */
global $PARSER_MODES;
$PARSER_MODES = array(
    // containers are complex modes that can contain many other modes
    // hr breaks the principle but they shouldn't be used in tables / lists
    // so they are put here
    'container'    => array('listblock','table','quote','hr'),

    // some mode are allowed inside the base mode only
    'baseonly'     => array('header'),

    // modes for styling text -- footnote behaves similar to styling
    'formatting'   => array('strong', 'emphasis', 'underline', 'monospace',
                            'subscript', 'superscript', 'deleted', 'footnote'),

    // modes where the token is simply replaced - they can not contain any
    // other modes
    'substition'   => array('acronym','smiley','wordblock','entity',
                            'camelcaselink', 'internallink','media',
                            'externallink','linebreak','emaillink',
                            'windowssharelink','filelink','notoc',
                            'nocache','multiplyentity','quotes','rss'),

    // modes which have a start and end token but inside which
    // no other modes should be applied
    'protected'    => array('preformatted','code','file','php','html','htmlblock','phpblock'),

    // inside this mode no wiki markup should be applied but lineendings
    // and whitespace isn't preserved
    'disabled'     => array('unformatted'),

    // used to mark paragraph boundaries
    'paragraphs'   => array('eol')
);

//-------------------------------------------------------------------

/**
 * Sets up the Lexer with modes and points it to the Handler
 * For an intro to the Lexer see: wiki:parser
 */
class Doku_Parser {

    var $Handler;

    /**
     * @var Doku_Lexer $Lexer
     */
    var $Lexer;

    var $modes = array();

    var $connected = false;

    function addBaseMode(& $BaseMode) {
        $this->modes['base'] =& $BaseMode;
        if ( !$this->Lexer ) {
            $this->Lexer = new Doku_Lexer($this->Handler,'base', true);
        }
        $this->modes['base']->Lexer =& $this->Lexer;
    }

    /**
     * PHP preserves order of associative elements
     * Mode sequence is important
     */
    function addMode($name, & $Mode) {
        if ( !isset($this->modes['base']) ) {
            $this->addBaseMode(new Doku_Parser_Mode_base());
        }
        $Mode->Lexer = & $this->Lexer;
        $this->modes[$name] =& $Mode;
    }

    function connectModes() {

        if ( $this->connected ) {
            return;
        }

        foreach ( array_keys($this->modes) as $mode ) {

            // Base isn't connected to anything
            if ( $mode == 'base' ) {
                continue;
            }
            $this->modes[$mode]->preConnect();

            foreach ( array_keys($this->modes) as $cm ) {

                if ( $this->modes[$cm]->accepts($mode) ) {
                    $this->modes[$mode]->connectTo($cm);
                }

            }

            $this->modes[$mode]->postConnect();
        }

        $this->connected = true;
    }

    function parse($doc) {
        if ( $this->Lexer ) {
            $this->connectModes();
            // Normalize CRs and pad doc
            $doc = "\n".str_replace("\r\n","\n",$doc)."\n";
            $this->Lexer->parse($doc);
            $this->Handler->_finalize();
            return $this->Handler->calls;
        } else {
            return false;
        }
    }

}

//-------------------------------------------------------------------

/**
 * Class Doku_Parser_Mode_Interface
 *
 * Defines a mode (syntax component) in the Parser
 */
interface Doku_Parser_Mode_Interface {
    /**
     * returns a number used to determine in which order modes are added
     */
    public function getSort();

    /**
     * Called before any calls to connectTo
     */
    function preConnect();

    /**
     * Connects the mode
     *
     * @param string $mode
     */
    function connectTo($mode);

    /**
     * Called after all calls to connectTo
     */
    function postConnect();

    /**
     * Check if given mode is accepted inside this mode
     *
     * @param string $mode
     * @return bool
     */
    function accepts($mode);
}

/**
 * This class and all the subclasses below are used to reduce the effort required to register
 * modes with the Lexer.
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
 */
class Doku_Parser_Mode implements Doku_Parser_Mode_Interface {
    /**
     * @var Doku_Lexer $Lexer
     */
    var $Lexer;
    var $allowedModes = array();

    function getSort() {
        trigger_error('getSort() not implemented in '.get_class($this), E_USER_WARNING);
    }

    function preConnect() {}
    function connectTo($mode) {}
    function postConnect() {}
    function accepts($mode) {
        return in_array($mode, (array) $this->allowedModes );
    }
}

/**
 * Basically the same as Doku_Parser_Mode but extends from DokuWiki_Plugin
 *
 * Adds additional functions to syntax plugins
 */
class Doku_Parser_Mode_Plugin extends DokuWiki_Plugin implements Doku_Parser_Mode_Interface {
    /**
     * @var Doku_Lexer $Lexer
     */
    var $Lexer;
    var $allowedModes = array();

    function getSort() {
        trigger_error('getSort() not implemented in '.get_class($this), E_USER_WARNING);
    }

    function preConnect() {}
    function connectTo($mode) {}
    function postConnect() {}
    function accepts($mode) {
        return in_array($mode, (array) $this->allowedModes );
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_base extends Doku_Parser_Mode {

    function Doku_Parser_Mode_base() {
        global $PARSER_MODES;

        $this->allowedModes = array_merge (
                $PARSER_MODES['container'],
                $PARSER_MODES['baseonly'],
                $PARSER_MODES['paragraphs'],
                $PARSER_MODES['formatting'],
                $PARSER_MODES['substition'],
                $PARSER_MODES['protected'],
                $PARSER_MODES['disabled']
            );
    }

    function getSort() {
        return 0;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_footnote extends Doku_Parser_Mode {

    function Doku_Parser_Mode_footnote() {
        global $PARSER_MODES;

        $this->allowedModes = array_merge (
                $PARSER_MODES['container'],
                $PARSER_MODES['formatting'],
                $PARSER_MODES['substition'],
                $PARSER_MODES['protected'],
                $PARSER_MODES['disabled']
            );

        unset($this->allowedModes[array_search('footnote', $this->allowedModes)]);
    }

    function connectTo($mode) {
        $this->Lexer->addEntryPattern(
            '\x28\x28(?=.*\x29\x29)',$mode,'footnote'
            );
    }

    function postConnect() {
        $this->Lexer->addExitPattern(
            '\x29\x29','footnote'
            );
    }

    function getSort() {
        return 150;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_header extends Doku_Parser_Mode {

    function connectTo($mode) {
        //we're not picky about the closing ones, two are enough
        $this->Lexer->addSpecialPattern(
                            '[ \t]*={2,}[^\n]+={2,}[ \t]*(?=\n)',
                            $mode,
                            'header'
                        );
    }

    function getSort() {
        return 50;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_notoc extends Doku_Parser_Mode {

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~NOTOC~~',$mode,'notoc');
    }

    function getSort() {
        return 30;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_nocache extends Doku_Parser_Mode {

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~NOCACHE~~',$mode,'nocache');
    }

    function getSort() {
        return 40;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_linebreak extends Doku_Parser_Mode {

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\x5C{2}(?:[ \t]|(?=\n))',$mode,'linebreak');
    }

    function getSort() {
        return 140;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_eol extends Doku_Parser_Mode {

    function connectTo($mode) {
        $badModes = array('listblock','table');
        if ( in_array($mode, $badModes) ) {
            return;
        }
        // see FS#1652, pattern extended to swallow preceding whitespace to avoid issues with lines that only contain whitespace
        $this->Lexer->addSpecialPattern('(?:^[ \t]*)?\n',$mode,'eol');
    }

    function getSort() {
        return 370;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_hr extends Doku_Parser_Mode {

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\n[ \t]*-{4,}[ \t]*(?=\n)',$mode,'hr');
    }

    function getSort() {
        return 160;
    }
}

//-------------------------------------------------------------------
/**
 * This class sets the markup for bold (=strong),
 * italic (=emphasis), underline etc.
 */
class Doku_Parser_Mode_formatting extends Doku_Parser_Mode {
    var $type;

    var $formatting = array (
        'strong' => array (
            'entry'=>'\*\*(?=.*\*\*)',
            'exit'=>'\*\*',
            'sort'=>70
            ),

        'emphasis'=> array (
            'entry'=>'//(?=[^\x00]*[^:])', //hack for bugs #384 #763 #1468
            'exit'=>'//',
            'sort'=>80
            ),

        'underline'=> array (
            'entry'=>'__(?=.*__)',
            'exit'=>'__',
            'sort'=>90
            ),

        'monospace'=> array (
            'entry'=>'\x27\x27(?=.*\x27\x27)',
            'exit'=>'\x27\x27',
            'sort'=>100
            ),

        'subscript'=> array (
            'entry'=>'<sub>(?=.*</sub>)',
            'exit'=>'</sub>',
            'sort'=>110
            ),

        'superscript'=> array (
            'entry'=>'<sup>(?=.*</sup>)',
            'exit'=>'</sup>',
            'sort'=>120
            ),

        'deleted'=> array (
            'entry'=>'<del>(?=.*</del>)',
            'exit'=>'</del>',
            'sort'=>130
            ),
        );

    function Doku_Parser_Mode_formatting($type) {
        global $PARSER_MODES;

        if ( !array_key_exists($type, $this->formatting) ) {
            trigger_error('Invalid formatting type '.$type, E_USER_WARNING);
        }

        $this->type = $type;

        // formatting may contain other formatting but not it self
        $modes = $PARSER_MODES['formatting'];
        $key = array_search($type, $modes);
        if ( is_int($key) ) {
            unset($modes[$key]);
        }

        $this->allowedModes = array_merge (
                $modes,
                $PARSER_MODES['substition'],
                $PARSER_MODES['disabled']
            );
    }

    function connectTo($mode) {

        // Can't nest formatting in itself
        if ( $mode == $this->type ) {
            return;
        }

        $this->Lexer->addEntryPattern(
                $this->formatting[$this->type]['entry'],
                $mode,
                $this->type
            );
    }

    function postConnect() {

        $this->Lexer->addExitPattern(
            $this->formatting[$this->type]['exit'],
            $this->type
            );

    }

    function getSort() {
        return $this->formatting[$this->type]['sort'];
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_listblock extends Doku_Parser_Mode {

    function Doku_Parser_Mode_listblock() {
        global $PARSER_MODES;

        $this->allowedModes = array_merge (
                $PARSER_MODES['formatting'],
                $PARSER_MODES['substition'],
                $PARSER_MODES['disabled'],
                $PARSER_MODES['protected'] #XXX new
            );

    //    $this->allowedModes[] = 'footnote';
    }

    function connectTo($mode) {
        $this->Lexer->addEntryPattern('[ \t]*\n {2,}[\-\*]',$mode,'listblock');
        $this->Lexer->addEntryPattern('[ \t]*\n\t{1,}[\-\*]',$mode,'listblock');

        $this->Lexer->addPattern('\n {2,}[\-\*]','listblock');
        $this->Lexer->addPattern('\n\t{1,}[\-\*]','listblock');

    }

    function postConnect() {
        $this->Lexer->addExitPattern('\n','listblock');
    }

    function getSort() {
        return 10;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_table extends Doku_Parser_Mode {

    function Doku_Parser_Mode_table() {
        global $PARSER_MODES;

        $this->allowedModes = array_merge (
                $PARSER_MODES['formatting'],
                $PARSER_MODES['substition'],
                $PARSER_MODES['disabled'],
                $PARSER_MODES['protected']
            );
    }

    function connectTo($mode) {
        $this->Lexer->addEntryPattern('[\t ]*\n\^',$mode,'table');
        $this->Lexer->addEntryPattern('[\t ]*\n\|',$mode,'table');
    }

    function postConnect() {
        $this->Lexer->addPattern('\n\^','table');
        $this->Lexer->addPattern('\n\|','table');
        $this->Lexer->addPattern('[\t ]*:::[\t ]*(?=[\|\^])','table');
        $this->Lexer->addPattern('[\t ]+','table');
        $this->Lexer->addPattern('\^','table');
        $this->Lexer->addPattern('\|','table');
        $this->Lexer->addExitPattern('\n','table');
    }

    function getSort() {
        return 60;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_unformatted extends Doku_Parser_Mode {

    function connectTo($mode) {
        $this->Lexer->addEntryPattern('<nowiki>(?=.*</nowiki>)',$mode,'unformatted');
        $this->Lexer->addEntryPattern('%%(?=.*%%)',$mode,'unformattedalt');
    }

    function postConnect() {
        $this->Lexer->addExitPattern('</nowiki>','unformatted');
        $this->Lexer->addExitPattern('%%','unformattedalt');
        $this->Lexer->mapHandler('unformattedalt','unformatted');
    }

    function getSort() {
        return 170;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_php extends Doku_Parser_Mode {

    function connectTo($mode) {
        $this->Lexer->addEntryPattern('<php>(?=.*</php>)',$mode,'php');
        $this->Lexer->addEntryPattern('<PHP>(?=.*</PHP>)',$mode,'phpblock');
    }

    function postConnect() {
        $this->Lexer->addExitPattern('</php>','php');
        $this->Lexer->addExitPattern('</PHP>','phpblock');
    }

    function getSort() {
        return 180;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_html extends Doku_Parser_Mode {

    function connectTo($mode) {
        $this->Lexer->addEntryPattern('<html>(?=.*</html>)',$mode,'html');
        $this->Lexer->addEntryPattern('<HTML>(?=.*</HTML>)',$mode,'htmlblock');
    }

    function postConnect() {
        $this->Lexer->addExitPattern('</html>','html');
        $this->Lexer->addExitPattern('</HTML>','htmlblock');
    }

    function getSort() {
        return 190;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_preformatted extends Doku_Parser_Mode {

    function connectTo($mode) {
        // Has hard coded awareness of lists...
        $this->Lexer->addEntryPattern('\n  (?![\*\-])',$mode,'preformatted');
        $this->Lexer->addEntryPattern('\n\t(?![\*\-])',$mode,'preformatted');

        // How to effect a sub pattern with the Lexer!
        $this->Lexer->addPattern('\n  ','preformatted');
        $this->Lexer->addPattern('\n\t','preformatted');

    }

    function postConnect() {
        $this->Lexer->addExitPattern('\n','preformatted');
    }

    function getSort() {
        return 20;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_code extends Doku_Parser_Mode {

    function connectTo($mode) {
        $this->Lexer->addEntryPattern('<code\b(?=.*</code>)',$mode,'code');
    }

    function postConnect() {
        $this->Lexer->addExitPattern('</code>','code');
    }

    function getSort() {
        return 200;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_file extends Doku_Parser_Mode {

    function connectTo($mode) {
        $this->Lexer->addEntryPattern('<file\b(?=.*</file>)',$mode,'file');
    }

    function postConnect() {
        $this->Lexer->addExitPattern('</file>','file');
    }

    function getSort() {
        return 210;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_quote extends Doku_Parser_Mode {

    function Doku_Parser_Mode_quote() {
        global $PARSER_MODES;

        $this->allowedModes = array_merge (
                $PARSER_MODES['formatting'],
                $PARSER_MODES['substition'],
                $PARSER_MODES['disabled'],
                $PARSER_MODES['protected'] #XXX new
            );
            #$this->allowedModes[] = 'footnote';
            #$this->allowedModes[] = 'preformatted';
            #$this->allowedModes[] = 'unformatted';
    }

    function connectTo($mode) {
        $this->Lexer->addEntryPattern('\n>{1,}',$mode,'quote');
    }

    function postConnect() {
        $this->Lexer->addPattern('\n>{1,}','quote');
        $this->Lexer->addExitPattern('\n','quote');
    }

    function getSort() {
        return 220;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_acronym extends Doku_Parser_Mode {
    // A list
    var $acronyms = array();
    var $pattern = '';

    function Doku_Parser_Mode_acronym($acronyms) {
        usort($acronyms,array($this,'_compare'));
        $this->acronyms = $acronyms;
    }

    function preConnect() {
        if(!count($this->acronyms)) return;

        $bound = '[\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f]';
        $acronyms = array_map('Doku_Lexer_Escape',$this->acronyms);
        $this->pattern = '(?<=^|'.$bound.')(?:'.join('|',$acronyms).')(?='.$bound.')';
    }

    function connectTo($mode) {
        if(!count($this->acronyms)) return;

        if ( strlen($this->pattern) > 0 ) {
            $this->Lexer->addSpecialPattern($this->pattern,$mode,'acronym');
        }
    }

    function getSort() {
        return 240;
    }

    /**
     * sort callback to order by string length descending
     */
    function _compare($a,$b) {
        $a_len = strlen($a);
        $b_len = strlen($b);
        if ($a_len > $b_len) {
            return -1;
        } else if ($a_len < $b_len) {
            return 1;
        }

        return 0;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_smiley extends Doku_Parser_Mode {
    // A list
    var $smileys = array();
    var $pattern = '';

    function Doku_Parser_Mode_smiley($smileys) {
        $this->smileys = $smileys;
    }

    function preConnect() {
        if(!count($this->smileys) || $this->pattern != '') return;

        $sep = '';
        foreach ( $this->smileys as $smiley ) {
            $this->pattern .= $sep.'(?<=\W|^)'.Doku_Lexer_Escape($smiley).'(?=\W|$)';
            $sep = '|';
        }
    }

    function connectTo($mode) {
        if(!count($this->smileys)) return;

        if ( strlen($this->pattern) > 0 ) {
            $this->Lexer->addSpecialPattern($this->pattern,$mode,'smiley');
        }
    }

    function getSort() {
        return 230;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_wordblock extends Doku_Parser_Mode {
    // A list
    var $badwords = array();
    var $pattern = '';

    function Doku_Parser_Mode_wordblock($badwords) {
        $this->badwords = $badwords;
    }

    function preConnect() {

        if ( count($this->badwords) == 0 || $this->pattern != '') {
            return;
        }

        $sep = '';
        foreach ( $this->badwords as $badword ) {
            $this->pattern .= $sep.'(?<=\b)(?i)'.Doku_Lexer_Escape($badword).'(?-i)(?=\b)';
            $sep = '|';
        }

    }

    function connectTo($mode) {
        if ( strlen($this->pattern) > 0 ) {
            $this->Lexer->addSpecialPattern($this->pattern,$mode,'wordblock');
        }
    }

    function getSort() {
        return 250;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_entity extends Doku_Parser_Mode {
    // A list
    var $entities = array();
    var $pattern = '';

    function Doku_Parser_Mode_entity($entities) {
        $this->entities = $entities;
    }

    function preConnect() {
        if(!count($this->entities) || $this->pattern != '') return;

        $sep = '';
        foreach ( $this->entities as $entity ) {
            $this->pattern .= $sep.Doku_Lexer_Escape($entity);
            $sep = '|';
        }
    }

    function connectTo($mode) {
        if(!count($this->entities)) return;

        if ( strlen($this->pattern) > 0 ) {
            $this->Lexer->addSpecialPattern($this->pattern,$mode,'entity');
        }
    }

    function getSort() {
        return 260;
    }
}

//-------------------------------------------------------------------
// Implements the 640x480 replacement
class Doku_Parser_Mode_multiplyentity extends Doku_Parser_Mode {

    function connectTo($mode) {

        $this->Lexer->addSpecialPattern(
                    '(?<=\b)(?:[1-9]|\d{2,})[xX]\d+(?=\b)',$mode,'multiplyentity'
                );

    }

    function getSort() {
        return 270;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_quotes extends Doku_Parser_Mode {

    function connectTo($mode) {
        global $conf;

        $ws   =  '\s/\#~:+=&%@\-\x28\x29\]\[{}><"\'';   // whitespace
        $punc =  ';,\.?!';

        if($conf['typography'] == 2){
            $this->Lexer->addSpecialPattern(
                        "(?<=^|[$ws])'(?=[^$ws$punc])",$mode,'singlequoteopening'
                    );
            $this->Lexer->addSpecialPattern(
                        "(?<=^|[^$ws]|[$punc])'(?=$|[$ws$punc])",$mode,'singlequoteclosing'
                    );
            $this->Lexer->addSpecialPattern(
                        "(?<=^|[^$ws$punc])'(?=$|[^$ws$punc])",$mode,'apostrophe'
                    );
        }

        $this->Lexer->addSpecialPattern(
                    "(?<=^|[$ws])\"(?=[^$ws$punc])",$mode,'doublequoteopening'
                );
        $this->Lexer->addSpecialPattern(
                    "\"",$mode,'doublequoteclosing'
                );

    }

    function getSort() {
        return 280;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_camelcaselink extends Doku_Parser_Mode {

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern(
                '\b[A-Z]+[a-z]+[A-Z][A-Za-z]*\b',$mode,'camelcaselink'
            );
    }

    function getSort() {
        return 290;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_internallink extends Doku_Parser_Mode {

    function connectTo($mode) {
        // Word boundaries?
        $this->Lexer->addSpecialPattern("\[\[(?:(?:[^[\]]*?\[.*?\])|.*?)\]\]",$mode,'internallink');
    }

    function getSort() {
        return 300;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_media extends Doku_Parser_Mode {

    function connectTo($mode) {
        // Word boundaries?
        $this->Lexer->addSpecialPattern("\{\{[^\}]+\}\}",$mode,'media');
    }

    function getSort() {
        return 320;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_rss extends Doku_Parser_Mode {

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern("\{\{rss>[^\}]+\}\}",$mode,'rss');
    }

    function getSort() {
        return 310;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_externallink extends Doku_Parser_Mode {
    var $schemes = array();
    var $patterns = array();

    function preConnect() {
        if(count($this->patterns)) return;

        $ltrs = '\w';
        $gunk = '/\#~:.?+=&%@!\-\[\]';
        $punc = '.:?\-;,';
        $host = $ltrs.$punc;
        $any  = $ltrs.$gunk.$punc;

        $this->schemes = getSchemes();
        foreach ( $this->schemes as $scheme ) {
            $this->patterns[] = '\b(?i)'.$scheme.'(?-i)://['.$any.']+?(?=['.$punc.']*[^'.$any.'])';
        }

        $this->patterns[] = '\b(?i)www?(?-i)\.['.$host.']+?\.['.$host.']+?['.$any.']+?(?=['.$punc.']*[^'.$any.'])';
        $this->patterns[] = '\b(?i)ftp?(?-i)\.['.$host.']+?\.['.$host.']+?['.$any.']+?(?=['.$punc.']*[^'.$any.'])';
    }

    function connectTo($mode) {

        foreach ( $this->patterns as $pattern ) {
            $this->Lexer->addSpecialPattern($pattern,$mode,'externallink');
        }
    }

    function getSort() {
        return 330;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_filelink extends Doku_Parser_Mode {

    var $pattern;

    function preConnect() {

        $ltrs = '\w';
        $gunk = '/\#~:.?+=&%@!\-';
        $punc = '.:?\-;,';
        $host = $ltrs.$punc;
        $any  = $ltrs.$gunk.$punc;

        $this->pattern = '\b(?i)file(?-i)://['.$any.']+?['.
            $punc.']*[^'.$any.']';
    }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern(
            $this->pattern,$mode,'filelink');
    }

    function getSort() {
        return 360;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_windowssharelink extends Doku_Parser_Mode {

    var $pattern;

    function preConnect() {
        $this->pattern = "\\\\\\\\\w+?(?:\\\\[\w-$]+)+";
    }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern(
            $this->pattern,$mode,'windowssharelink');
    }

    function getSort() {
        return 350;
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_emaillink extends Doku_Parser_Mode {

    function connectTo($mode) {
        // pattern below is defined in inc/mail.php
        $this->Lexer->addSpecialPattern('<'.PREG_PATTERN_VALID_EMAIL.'>',$mode,'emaillink');
    }

    function getSort() {
        return 340;
    }
}


//Setup VIM: ex: et ts=4 :
