<?php
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');

require_once DOKU_INC . 'inc/parser/renderer.php';

class Doku_Renderer_SpamCheck extends Doku_Renderer {
    
    // This should be populated by the code executing the instructions
    var $currentCall;
    
    // An array of instructions that contain spam
    var $spamFound = array();
    
    // pcre pattern for finding spam
    var $spamPattern = '#^$#';
    
    function internallink($link, $title = NULL) {
        $this->__checkTitle($title);
    }
    
    function externallink($link, $title = NULL) {
        $this->__checkLinkForSpam($link);
        $this->__checkTitle($title);
    }
    
    function interwikilink($link, $title = NULL) {
        $this->__checkTitle($title);
    }
    
    function filelink($link, $title = NULL) {
        $this->__checkLinkForSpam($link);
        $this->__checkTitle($title);
    }
    
    function windowssharelink($link, $title = NULL) {
        $this->__checkLinkForSpam($link);
        $this->__checkTitle($title);
    }
    
    function email($address, $title = NULL) {
        $this->__checkLinkForSpam($address);
        $this->__checkTitle($title);
    }
    
    function internalmedialink ($src) {
        $this->__checkLinkForSpam($src);
    }

    function externalmedialink($src) {
        $this->__checkLinkForSpam($src);
    }

    function __checkTitle($title) {
        if ( is_array($title) && isset($title['src'])) {
            $this->__checkLinkForSpam($title['src']);
        }
    }
    
    // Pattern matching happens here
    /**
    * @TODO What about links like www.google.com - no http://
    */
    function __checkLinkForSpam($link) {
        if( preg_match($this->spamPattern,$link) ) {
            $spam = $this->currentCall;
            $spam[3] = $link;
            $this->spamFound[] = $spam;
        }
    }
}


//Setup VIM: ex: et ts=2 enc=utf-8 :
