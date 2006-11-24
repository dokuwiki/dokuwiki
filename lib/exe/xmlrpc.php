<?php
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');

// fix when '<?xml' isn't on the very first line
if(isset($HTTP_RAW_POST_DATA)) $HTTP_RAW_POST_DATA = trim($HTTP_RAW_POST_DATA);


//EXPERIMENTAL CODE
die('remove me to get it work');

require_once(DOKU_INC.'inc/init.php');
require_once(DOKU_INC.'inc/common.php');
require_once(DOKU_INC.'inc/auth.php');
session_write_close();  //close session
require_once(DOKU_INC.'inc/IXR_Library.php');



/**
 * Contains needed wrapper functions and registers all available
 * XMLRPC functions.
 */
class dokuwiki_xmlrpc_server extends IXR_IntrospectionServer {
    var $methods = array();

    /**
     * Constructor. Register methods and run Server
     */
    function dokuwiki_xmlrpc_server(){
        $this->IXR_IntrospectionServer();

        /* DokuWiki's own methods */
        $this->addCallback(
            'dokuwiki.getVersion',
            'getVersion',
            array('string'),
            'Returns the running DokuWiki version'
        );

        /* Wiki API v2 http://www.jspwiki.org/wiki/WikiRPCInterface2 */
        $this->addCallback(
            'wiki.getRPCVersionSupported',
            'this:wiki_RPCVersion',
            array('int'),
            'Returns 2 with the supported RPC API version'
        );
        $this->addCallback(
            'wiki.getPage',
            'this:rawPage',
            array('string','string'),
            'Get the raw Wiki text of page, latest version.'
        );
        $this->addCallback(
            'wiki.getPageVersion',
            'this:rawPage',
            array('string','string','int'),
            'Get the raw Wiki text of page.'
        );
        $this->addCallback(
            'wiki.getPageHTML',
            'this:htmlPage',
            array('string','string'),
            'Return page in rendered HTML, latest version.'
        );
        $this->addCallback(
            'wiki.getPageHTMLVersion',
            'this:htmlPage',
            array('string','string','int'),
            'Return page in rendered HTML.'
        );
        $this->addCallback(
            'wiki.getAllPages',
            'this:listPages',
            array('struct'),
            'Returns a list of all pages. The result is an array of utf8 pagenames.'
        );
        $this->addCallback(
            'wiki.getBackLinks',
            'this:listBackLinks',
            array('struct','string'),
            'Returns the pages that link to this page.'
        );
        $this->addCallback(
            'wiki.getPageInfo',
            'this:pageInfo',
            array('struct','string'),
            'Returns a struct with infos about the page.'
        );
        $this->addCallback(
            'wiki.getPageInfoVersion',
            'this:pageInfo',
            array('struct','string','int'),
            'Returns a struct with infos about the page.'
        );
/*
  FIXME: missing, yet
            'wiki.getRecentChanges'
            'wiki.listLinks'
            'wiki.putPage'
*/

        $this->serve();
    }

    /**
     * Return a raw wiki page
     */
    function rawPage($id,$rev=''){
        if(auth_quickaclcheck($id) < AUTH_READ){
            return new IXR_Error(1, 'You are not allowed to read this page');
        }
        return rawWiki($id,$rev);
    }

    /**
     * Return a wiki page rendered to html
     */
    function htmlPage($id,$rev=''){
        if(auth_quickaclcheck($id) < AUTH_READ){
            return new IXR_Error(1, 'You are not allowed to read this page');
        }
        return p_wiki_xhtml($id,$rev,false);
    }

    /**
     * List all pages - we use the indexer list here
     */
    function listPages(){
        require_once(DOKU_INC.'inc/fulltext.php');
        return ft_pageLookup('');
    }


    /**
     * Return a list of backlinks
     */
    function listBacklinks($id){
        require_once(DOKU_INC.'inc/fulltext.php');
        return ft_backlinks($id);
    }

    /**
     * return some basic data about a page
     */
    function pageInfo($id,$rev=''){
        if(auth_quickaclcheck($id) < AUTH_READ){
            return new IXR_Error(1, 'You are not allowed to read this page');
        }
        $file = wikiFN($id,$rev);
        $time = @filemtime($file);
        if(!$time){
            return new IXR_Error(10, 'The requested page does not exist');
        }

        $info = getRevisionInfo($id, $time, 1024);

        $data = array(
            'name'         => $id,
            'lastModified' => new IXR_Date($time),
            'author'       => (($info['user']) ? $info['user'] : $info['ip']),
            'version'      => $time
        );
        return $data;
    }

    /**
     * The version of Wiki RPC API supported
     */
    function wiki_RPCVersion(){
        return 2;
    }

}

$server = new dokuwiki_xmlrpc_server();

