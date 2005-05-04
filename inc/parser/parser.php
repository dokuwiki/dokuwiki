<?php

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');

require_once DOKU_INC . 'inc/parser/lexer.php';
require_once DOKU_INC . 'inc/parser/handler.php';

//-------------------------------------------------------------------

/**
* Sets up the Lexer with modes and points it to the Handler
* For an intro to the Lexer see: wiki:parser
*/
class Doku_Parser {
    
    var $Handler;
    
    var $Lexer;
    
    var $modes = array();
    
    var $connected = FALSE;
    
    function addBaseMode(& $BaseMode) {
        $this->modes['base'] = & $BaseMode;
        if ( !$this->Lexer ) {
            $this->Lexer = & new Doku_Lexer($this->Handler,'base', TRUE);
        }
        $this->modes['base']->Lexer = & $this->Lexer;
    }
    
    /**
    * PHP preserves order of associative elements
    * Mode sequence is important
    */
    function addMode($name, & $Mode) {
        if ( !isset($this->modes['base']) ) {
            $this->addBaseMode(new Doku_Parser_Mode_Base());
        }
        $Mode->Lexer = & $this->Lexer;
        $this->modes[$name] = & $Mode;
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
        
        $this->connected = TRUE;
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
            return FALSE;
        }
    }
    
}

//-------------------------------------------------------------------
/**
 * This class and all the subclasses below are
 * used to reduce the effort required to register
 * modes with the Lexer. For performance these
 * could all be eliminated later perhaps, or
 * the Parser could be serialized to a file once
 * all modes are registered
 *
 * @author Harry Fuecks <hfuecks@gmail.com>
*/
class Doku_Parser_Mode {
    
    var $Lexer;
    
    var $allowedModes = array();
    
    // Called before any calls to connectTo
    function preConnect() {}
    
    function connectTo($mode) {}
    
    // Called after all calls to connectTo
    function postConnect() {}
    
    function accepts($mode) {
        return in_array($mode, $this->allowedModes );
    }
    
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_Base extends Doku_Parser_Mode {
    
    function Doku_Parser_Mode_Base() {
        
        $this->allowedModes = array_merge (
                Doku_Parser_BlockContainers(),
                Doku_Parser_BaseOnly(),
                Doku_Parser_Paragraphs(),
                Doku_Parser_Formatting(),
                Doku_Parser_Substition(),
                Doku_Parser_Protected(),
                Doku_Parser_Disabled()
            );
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_Footnote extends Doku_Parser_Mode {
    
    function Doku_Parser_Mode_Footnote() {
        
        $this->allowedModes = array_merge (
                Doku_Parser_BlockContainers(),
                Doku_Parser_Formatting(),
                Doku_Parser_Substition(),
                Doku_Parser_Protected(),
                Doku_Parser_Disabled()
            );
        
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

}

//-------------------------------------------------------------------
class Doku_Parser_Mode_Header extends Doku_Parser_Mode {
    
    function preConnect() {
        //we're not picky about the closing ones, two are enough
        
        // Header 1 is special case - match 6 or more
        $this->Lexer->addSpecialPattern(
                            '[ \t]*={6,}[^\n]+={2,}[ \t]*(?=\n)',
                            'base',
                            'header'
                        );
        
        // For the rest, match exactly
        for ( $i = 5; $i > 1; $i--) {
            $this->Lexer->addSpecialPattern(
                                '[ \t]*={'.$i.'}[^\n]+={2,}[ \t]*(?=\n)',
                                'base',
                                'header'
                            );
        }
    }
    
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_NoToc extends Doku_Parser_Mode {
    
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~NOTOC~~',$mode,'notoc');
    }
    
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_NoCache extends Doku_Parser_Mode {
            
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~NOCACHE~~',$mode,'nocache');
    }   
                
}               
 
//-------------------------------------------------------------------
class Doku_Parser_Mode_Linebreak extends Doku_Parser_Mode {
    
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\x5C{2}(?=\s)',$mode,'linebreak');
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_Eol extends Doku_Parser_Mode {
    
    function connectTo($mode) {
        $badModes = array('listblock','table');
        if ( in_array($mode, $badModes) ) {
            return;
        }
        $this->Lexer->addSpecialPattern('\n',$mode,'eol');
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_HR extends Doku_Parser_Mode {

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\n[ \t]*-{4,}[ \t]*(?=\n)',$mode,'hr');
    }
    
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_Formatting extends Doku_Parser_Mode {
    
    var $type;
    
    var $formatting = array (
        'strong' => array (
            'entry'=>'\*\*(?=.*\*\*)',
            'exit'=>'\*\*',
            ),
        
        'emphasis'=> array (
            'entry'=>'//(?=.*//)',
            'exit'=>'//',
            ),
        
        'underline'=> array (
            'entry'=>'__(?=.*__)',
            'exit'=>'__',
            ),
        
        'monospace'=> array (
            'entry'=>'\x27\x27(?=.*\x27\x27)',
            'exit'=>'\x27\x27',
            ),
        
        'subscript'=> array (
            'entry'=>'<sub>(?=.*\x3C/sub\x3E)',
            'exit'=>'</sub>',
            ),
        
        'superscript'=> array (
            'entry'=>'<sup>(?=.*\x3C/sup\x3E)',
            'exit'=>'</sup>',
            ),
        
        'deleted'=> array (
            'entry'=>'<del>(?=.*\x3C/del\x3E)',
            'exit'=>'</del>',
            ),
        );
    
    function Doku_Parser_Mode_Formatting($type) {
    
        if ( !array_key_exists($type, $this->formatting) ) {
            trigger_error('Invalid formatting type '.$type, E_USER_WARNING);
        }
        
        $this->type = $type;

        $this->allowedModes = array_merge (
                Doku_Parser_Formatting($type),
                Doku_Parser_Substition(),
                Doku_Parser_Disabled()
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
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_ListBlock extends Doku_Parser_Mode {

    function Doku_Parser_Mode_ListBlock() {
    
        $this->allowedModes = array_merge (
                Doku_Parser_Formatting(),
                Doku_Parser_Substition(),
                Doku_Parser_Disabled()
            );

        $this->allowedModes[] = 'footnote';
        $this->allowedModes[] = 'preformatted';
        $this->allowedModes[] = 'unformatted';
        $this->allowedModes[] = 'html';
        $this->allowedModes[] = 'php';
        $this->allowedModes[] = 'code';
        $this->allowedModes[] = 'file';
    }
    
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('\n {2,}[\-\*]',$mode,'listblock');
        $this->Lexer->addEntryPattern('\n\t{1,}[\-\*]',$mode,'listblock');
        
        $this->Lexer->addPattern('\n {2,}[\-\*]','listblock');
        $this->Lexer->addPattern('\n\t{1,}[\-\*]','listblock');
        
    }
    
    function postConnect() {
        $this->Lexer->addExitPattern('\n','listblock');
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_Table extends Doku_Parser_Mode {
    
    function Doku_Parser_Mode_Table() {
    
        $this->allowedModes = array_merge (
                Doku_Parser_Formatting(),
                Doku_Parser_Substition(),
                Doku_Parser_Disabled()
            );
        $this->allowedModes[] = 'footnote';
        $this->allowedModes[] = 'preformatted';
        $this->allowedModes[] = 'unformatted';
    }
    
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('\n\^',$mode,'table');
        $this->Lexer->addEntryPattern('\n\|',$mode,'table');
    }
    
    function postConnect() {
        $this->Lexer->addPattern('\n\^','table');
        $this->Lexer->addPattern('\n\|','table');
        $this->Lexer->addPattern(' {2,}','table');
        $this->Lexer->addPattern('\^','table');
        $this->Lexer->addPattern('\|','table');
        $this->Lexer->addExitPattern('\n','table');
    }
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_Unformatted extends Doku_Parser_Mode {
    
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('<nowiki>(?=.*\x3C/nowiki\x3E)',$mode,'unformatted');
        $this->Lexer->addEntryPattern('%%(?=.*%%)',$mode,'unformattedalt');
    }
    
    function postConnect() {
        $this->Lexer->addExitPattern('</nowiki>','unformatted');
        $this->Lexer->addExitPattern('%%','unformattedalt');
        $this->Lexer->mapHandler('unformattedalt','unformatted');
    }

}

//-------------------------------------------------------------------
class Doku_Parser_Mode_PHP extends Doku_Parser_Mode {
    
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('<php>(?=.*\x3C/php\x3E)',$mode,'php');
    }
    
    function postConnect() {
        $this->Lexer->addExitPattern('</php>','php');
    }

}

//-------------------------------------------------------------------
class Doku_Parser_Mode_HTML extends Doku_Parser_Mode {
    
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('<html>(?=.*\x3C/html\x3E)',$mode,'html');
    }
    
    function postConnect() {
        $this->Lexer->addExitPattern('</html>','html');
    }

}

//-------------------------------------------------------------------
class Doku_Parser_Mode_Preformatted extends Doku_Parser_Mode {
    
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
    
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_Code extends Doku_Parser_Mode {
    
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('<code(?=.*\x3C/code\x3E)',$mode,'code');
    }
    
    function postConnect() {
        $this->Lexer->addExitPattern('</code>','code');
    }
    
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_File extends Doku_Parser_Mode {
    
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('<file>(?=.*\x3C/file\x3E)',$mode,'file');
    }
    
    function postConnect() {
        $this->Lexer->addExitPattern('</file>','file');
    }
    
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_Quote extends Doku_Parser_Mode {
    
    function Doku_Parser_Mode_Quote() {
    
        $this->allowedModes = array_merge (
                Doku_Parser_Formatting(),
                Doku_Parser_Substition(),
                Doku_Parser_Disabled()
            );
            $this->allowedModes[] = 'footnote';
            $this->allowedModes[] = 'preformatted';
            $this->allowedModes[] = 'unformatted';
    }
    
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('\n>{1,}',$mode,'quote');
    }
    
    function postConnect() {
        $this->Lexer->addPattern('\n>{1,}','quote');
        $this->Lexer->addExitPattern('\n','quote');
    }
    
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_Acronym extends Doku_Parser_Mode {
    // A list
    var $acronyms = array();
    var $pattern = '';
    
    function Doku_Parser_Mode_Acronym($acronyms) {
        $this->acronyms = $acronyms;
    }
    
    function preConnect() {
        $sep = '';
        foreach ( $this->acronyms as $acronym ) {
            $this->pattern .= $sep.'(?<=\b)'.Doku_Lexer_Escape($acronym).'(?=\b)';
            $sep = '|';
        }
    }
    
    function connectTo($mode) {
        if ( strlen($this->pattern) > 0 ) {
            $this->Lexer->addSpecialPattern($this->pattern,$mode,'acronym');        
        }
    }
    
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_Smiley extends Doku_Parser_Mode {
    // A list
    var $smileys = array();
    var $pattern = '';
    
    function Doku_Parser_Mode_Smiley($smileys) {
        $this->smileys = $smileys;
    }
    
    function preConnect() {
        $sep = '';
        foreach ( $this->smileys as $smiley ) {
            $this->pattern .= $sep.Doku_Lexer_Escape($smiley);
            $sep = '|';
        }
    }
    
    function connectTo($mode) {
        if ( strlen($this->pattern) > 0 ) {
            $this->Lexer->addSpecialPattern($this->pattern,$mode,'smiley');        
        }
    }
    
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_Wordblock extends Doku_Parser_Mode {
    // A list
    var $badwords = array();
    var $pattern = '';
    
    function Doku_Parser_Mode_Wordblock($badwords) {
        $this->badwords = $badwords;
    }
    
    function preConnect() {
        
        if ( count($this->badwords) == 0 ) {
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
    
}

//-------------------------------------------------------------------
/**
* @TODO Quotes and 640x480 are not supported - just straight replacements here
*/
class Doku_Parser_Mode_Entity extends Doku_Parser_Mode {
    // A list
    var $entities = array();
    var $pattern = '';
    
    function Doku_Parser_Mode_Entity($entities) {
        $this->entities = $entities;
    }
    
    function preConnect() {
        $sep = '';
        foreach ( $this->entities as $entity ) {
            $this->pattern .= $sep.Doku_Lexer_Escape($entity);
            $sep = '|';
        }
    }
    
    function connectTo($mode) {
        if ( strlen($this->pattern) > 0 ) {
            $this->Lexer->addSpecialPattern($this->pattern,$mode,'entity');
        }
    }
    
}

//-------------------------------------------------------------------
// Implements the 640x480 replacement
class Doku_Parser_Mode_MultiplyEntity extends Doku_Parser_Mode {
    
    function connectTo($mode) {
    
        $this->Lexer->addSpecialPattern(
                    '(?<=\b)\d+[x|X]\d+(?=\b)',$mode,'multiplyentity'
                );

    }
    
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_Quotes extends Doku_Parser_Mode {
    
    function connectTo($mode) {
    
        $this->Lexer->addSpecialPattern(
                    '(?<=\s)\'(?=\S)',$mode,'singlequoteopening'
                );
        $this->Lexer->addSpecialPattern(
                    '(?<=^|\S)\'',$mode,'singlequoteclosing'
                );
        $this->Lexer->addSpecialPattern(
                    '(?<=^|\s)"(?=\S)',$mode,'doublequoteopening'
                );
        $this->Lexer->addSpecialPattern(
                    '(?<=\S)"',$mode,'doublequoteclosing'
                );

    }
    
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_CamelCaseLink extends Doku_Parser_Mode {
    
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern(
                '\b[A-Z]+[a-z]+[A-Z][A-Za-z]*\b',$mode,'camelcaselink'
            );
    }
    
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_InternalLink extends Doku_Parser_Mode {
    
    function connectTo($mode) {
        // Word boundaries?
        $this->Lexer->addSpecialPattern("\[\[[^\]]+?\]\]",$mode,'internallink');
    }
    
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_Media extends Doku_Parser_Mode {
    
    function connectTo($mode) {
        // Word boundaries?
        $this->Lexer->addSpecialPattern("\{\{[^\}]+\}\}",$mode,'media');
    }
    
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_RSS extends Doku_Parser_Mode {

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern("\{\{rss>[^\}]+\}\}",$mode,'rss');
    }

}

//-------------------------------------------------------------------
class Doku_Parser_Mode_ExternalLink extends Doku_Parser_Mode {    
    var $schemes = array('http','https','telnet','gopher','wais','ftp','ed2k','irc');
    var $patterns = array();
    
    function preConnect() {
        
        $ltrs = '\w';
        $gunk = '/\#~:.?+=&%@!\-';
        $punc = '.:?\-;,';
        $host = $ltrs.$punc;
        $any  = $ltrs.$gunk.$punc;
        
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
    
}

//-------------------------------------------------------------------
class Doku_Parser_Mode_FileLink extends Doku_Parser_Mode {
    
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
    

}

//-------------------------------------------------------------------
class Doku_Parser_Mode_WindowsShareLink extends Doku_Parser_Mode {
    
    var $pattern;
    
    function preConnect() {
        
        $ltrs = '\w';
        $gunk = '/\#~:.?+=&%@!\-';
        $punc = '.:?\-;,';
        $host = $ltrs.$punc;
        $any  = $ltrs.$gunk.$punc;
        
        $this->pattern = "[$gunk$punc\s]\\\\\\\\[$host]+?\\\\[$any]+?[$punc]*[^$any]";
    }
    
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern(
            $this->pattern,$mode,'windowssharelink');
    }
    

}

//-------------------------------------------------------------------
class Doku_Parser_Mode_EmailLink extends Doku_Parser_Mode {
    
    function connectTo($mode) {
    //<([\w0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)>
        $this->Lexer->addSpecialPattern("<[\w0-9\-_.]+?@[\w\-]+\.[\w\-\.]+\.*[\w]+>",$mode,'emaillink');
    }
    
}

//-------------------------------------------------------------------
// Help fns to keep mode lists - used to make it easier to populate
// the list of modes another mode accepts

// Can contain many other modes
// E.g. a footnote can containing formatting etc.
function Doku_Parser_BlockContainers() {
    $modes = array(
        'footnote', 'listblock', 'table','quote',
        // hr breaks the principle but HRs should not be used in tables / lists 
        // so put it here
        'hr', 
    );
    return $modes;
}

// Used to mark paragraph boundaries
function Doku_Parser_Paragraphs() {
    $modes = array(
        'eol'
    );
    return $modes;
}

// Can only be used by the base mode
function Doku_Parser_BaseOnly() {
    $modes = array(
        'header'
    );
    return $modes;
}

// "Styling" modes that format text.
function Doku_Parser_Formatting($remove = '') {
    $modes = array(
        'strong', 'emphasis', 'underline', 'monospace', 
        'subscript', 'superscript', 'deleted',
        );
    $key = array_search($remove, $modes);
    if ( is_int($key) ) {
        unset($modes[$key]);
    }
    
    return $modes;
}

// Modes where the token is simply replaced - contain no
// other modes
function Doku_Parser_Substition() {
    $modes = array(
        'acronym','smiley','wordblock','entity','camelcaselink',
        'internallink','media','externallink','linebreak','emaillink',
        'windowssharelink','filelink','notoc','nocache','multiplyentity',
        'quotes','rss',
    );
    return $modes;
}

// Modes which have a start and end token but inside which 
// no other modes should be applied
function Doku_Parser_Protected() {
    $modes = array(
        'preformatted','code','file',
        'php','html','quote',
    );
    return $modes;
}

// Disable wiki markup inside this mode
function Doku_Parser_Disabled() {
    $modes = array(
        'unformatted'
    );
    return $modes;
}


//Setup VIM: ex: et ts=4 enc=utf-8 :
