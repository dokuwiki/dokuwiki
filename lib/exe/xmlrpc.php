<?php
if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../');

// fix when '< ?xml' isn't on the very first line
if(isset($HTTP_RAW_POST_DATA)) $HTTP_RAW_POST_DATA = trim($HTTP_RAW_POST_DATA);

/**
 * Increased whenever the API is changed
 */
define('DOKU_XMLRPC_API_VERSION', 6);

require_once(DOKU_INC.'inc/init.php');
session_write_close();  //close session

if(!$conf['xmlrpc']) die('XML-RPC server not enabled.');

/**
 * Contains needed wrapper functions and registers all available
 * XMLRPC functions.
 */
class dokuwiki_xmlrpc_server extends IXR_IntrospectionServer {
    var $methods       = array();
    var $public_methods = array();

    /**
     * Checks if the current user is allowed to execute non anonymous methods
     */
    function checkAuth(){
        global $conf;
        global $USERINFO;

        if(!$conf['useacl']) return true; //no ACL - then no checks
        if(trim($conf['xmlrpcuser']) == '') return true; //no restrictions

        return auth_isMember($conf['xmlrpcuser'],$_SERVER['REMOTE_USER'],(array) $USERINFO['grps']);
    }

    /**
     * Adds a callback, extends parent method
     *
     * add another parameter to define if anonymous access to
     * this method should be granted.
     */
    function addCallback($method, $callback, $args, $help, $public=false){
        if($public) $this->public_methods[] = $method;
        return parent::addCallback($method, $callback, $args, $help);
    }

    /**
     * Execute a call, extends parent method
     *
     * Checks for authentication first
     */
    function call($methodname, $args){
        if(!in_array($methodname,$this->public_methods) && !$this->checkAuth()){
            if (!isset($_SERVER['REMOTE_USER'])) {
                header('HTTP/1.1 401 Unauthorized');
            } else {
                header('HTTP/1.1 403 Forbidden');
            }
            return new IXR_Error(-32603, 'server error. not authorized to call method "'.$methodname.'".');
        }
        return parent::call($methodname, $args);
    }

    /**
     * Constructor. Register methods and run Server
     */
    function dokuwiki_xmlrpc_server(){
        $this->IXR_IntrospectionServer();

        /* DokuWiki's own methods */
        $this->addCallback(
            'dokuwiki.getXMLRPCAPIVersion',
            'this:getAPIVersion',
            array('integer'),
            'Returns the XMLRPC API version.',
            true
        );

        $this->addCallback(
            'dokuwiki.getVersion',
            'getVersion',
            array('string'),
            'Returns the running DokuWiki version.',
            true
        );

        $this->addCallback(
            'dokuwiki.login',
            'this:login',
            array('integer','string','string'),
            'Tries to login with the given credentials and sets auth cookies.',
            true
        );

        $this->addCallback(
            'dokuwiki.getPagelist',
            'this:readNamespace',
            array('struct','string','struct'),
            'List all pages within the given namespace.'
        );

        $this->addCallback(
            'dokuwiki.search',
            'this:search',
            array('struct','string'),
            'Perform a fulltext search and return a list of matching pages'
        );

        $this->addCallback(
            'dokuwiki.getTime',
            'time',
            array('int'),
            'Return the current time at the wiki server.'
        );

        $this->addCallback(
            'dokuwiki.setLocks',
            'this:setLocks',
            array('struct','struct'),
            'Lock or unlock pages.'
        );


        $this->addCallback(
            'dokuwiki.getTitle',
            'this:getTitle',
            array('string'),
            'Returns the wiki title.',
            true
        );

        $this->addCallback(
            'dokuwiki.appendPage',
            'this:appendPage',
            array('int', 'string', 'string', 'struct'),
            'Append text to a wiki page.'
        );

        /* Wiki API v2 http://www.jspwiki.org/wiki/WikiRPCInterface2 */
        $this->addCallback(
            'wiki.getRPCVersionSupported',
            'this:wiki_RPCVersion',
            array('int'),
            'Returns 2 with the supported RPC API version.',
            true
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
            'wiki.getAttachments',
            'this:listAttachments',
            array('struct', 'string', 'struct'),
            'Returns a list of all media files.'
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
        $this->addCallback(
            'wiki.getPageVersions',
            'this:pageVersions',
            array('struct','string','int'),
            'Returns the available revisions of the page.'
        );
        $this->addCallback(
            'wiki.putPage',
            'this:putPage',
            array('int', 'string', 'string', 'struct'),
            'Saves a wiki page.'
        );
        $this->addCallback(
            'wiki.listLinks',
            'this:listLinks',
            array('struct','string'),
            'Lists all links contained in a wiki page.'
        );
        $this->addCallback(
            'wiki.getRecentChanges',
            'this:getRecentChanges',
            array('struct','int'),
            'Returns a struct about all recent changes since given timestamp.'
        );
        $this->addCallback(
            'wiki.getRecentMediaChanges',
            'this:getRecentMediaChanges',
            array('struct','int'),
            'Returns a struct about all recent media changes since given timestamp.'
        );
        $this->addCallback(
            'wiki.aclCheck',
            'this:aclCheck',
            array('int', 'string'),
            'Returns the permissions of a given wiki page.'
        );
        $this->addCallback(
            'wiki.putAttachment',
            'this:putAttachment',
            array('struct', 'string', 'base64', 'struct'),
            'Upload a file to the wiki.'
        );
        $this->addCallback(
            'wiki.deleteAttachment',
            'this:deleteAttachment',
            array('int', 'string'),
            'Delete a file from the wiki.'
        );
        $this->addCallback(
            'wiki.getAttachment',
            'this:getAttachment',
            array('base64', 'string'),
            'Download a file from the wiki.'
        );
        $this->addCallback(
            'wiki.getAttachmentInfo',
            'this:getAttachmentInfo',
            array('struct', 'string'),
            'Returns a struct with infos about the attachment.'
        );

        /**
         * Trigger XMLRPC_CALLBACK_REGISTER, action plugins can use this event
         * to extend the XMLRPC interface and register their own callbacks.
         *
         * Event data:
         *  The XMLRPC server object:
         *
         *  $event->data->addCallback() - register a callback, the second
         *  paramter has to be of the form "plugin:<pluginname>:<plugin
         *  method>"
         *
         *  $event->data->callbacks - an array which holds all awaylable
         *  callbacks
         */
        trigger_event('XMLRPC_CALLBACK_REGISTER', $this);

        $this->serve();
    }

    /**
     * Return a raw wiki page
     */
    function rawPage($id,$rev=''){
        $id = cleanID($id);
        if(auth_quickaclcheck($id) < AUTH_READ){
            return new IXR_Error(1, 'You are not allowed to read this page');
        }
        $text = rawWiki($id,$rev);
        if(!$text) {
            return pageTemplate($id);
        } else {
            return $text;
        }
    }

    /**
     * Return a media file encoded in base64
     *
     * @author Gina Haeussge <osd@foosel.net>
     */
    function getAttachment($id){
        $id = cleanID($id);
        if (auth_quickaclcheck(getNS($id).':*') < AUTH_READ)
            return new IXR_Error(1, 'You are not allowed to read this file');

        $file = mediaFN($id);
        if (!@ file_exists($file))
            return new IXR_Error(1, 'The requested file does not exist');

        $data = io_readFile($file, false);
        $base64 = base64_encode($data);
        return $base64;
    }

    /**
     * Return info about a media file
     *
     * @author Gina Haeussge <osd@foosel.net>
     */
    function getAttachmentInfo($id){
        $id = cleanID($id);
        $info = array(
            'lastModified' => 0,
            'size' => 0,
        );

        $file = mediaFN($id);
        if ((auth_quickaclcheck(getNS($id).':*') >= AUTH_READ) && file_exists($file)){
            $info['lastModified'] = new IXR_Date(filemtime($file));
            $info['size'] = filesize($file);
        }

        return $info;
    }

    /**
     * Return a wiki page rendered to html
     */
    function htmlPage($id,$rev=''){
        $id = cleanID($id);
        if(auth_quickaclcheck($id) < AUTH_READ){
            return new IXR_Error(1, 'You are not allowed to read this page');
        }
        return p_wiki_xhtml($id,$rev,false);
    }

    /**
     * List all pages - we use the indexer list here
     */
    function listPages(){
        $list  = array();
        $pages = idx_get_indexer()->getPages();
        $pages = array_filter(array_filter($pages,'isVisiblePage'),'page_exists');

        foreach(array_keys($pages) as $idx) {
            $perm = auth_quickaclcheck($pages[$idx]);
            if($perm < AUTH_READ) {
                continue;
            }
            $page = array();
            $page['id'] = trim($pages[$idx]);
            $page['perms'] = $perm;
            $page['size'] = @filesize(wikiFN($pages[$idx]));
            $page['lastModified'] = new IXR_Date(@filemtime(wikiFN($pages[$idx])));
            $list[] = $page;
        }

        return $list;
    }

    /**
     * List all pages in the given namespace (and below)
     */
    function readNamespace($ns,$opts){
        global $conf;

        if(!is_array($opts)) $opts=array();

        $ns = cleanID($ns);
        $dir = utf8_encodeFN(str_replace(':', '/', $ns));
        $data = array();
        $opts['skipacl'] = 0; // no ACL skipping for XMLRPC
        search($data, $conf['datadir'], 'search_allpages', $opts, $dir);
        return $data;
    }

    /**
     * List all pages in the given namespace (and below)
     */
    function search($query){
        require_once(DOKU_INC.'inc/fulltext.php');

        $regex = '';
        $data  = ft_pageSearch($query,$regex);
        $pages = array();

        // prepare additional data
        $idx = 0;
        foreach($data as $id => $score){
            $file = wikiFN($id);

            if($idx < FT_SNIPPET_NUMBER){
                $snippet = ft_snippet($id,$regex);
                $idx++;
            }else{
                $snippet = '';
            }

            $pages[] = array(
                'id'      => $id,
                'score'   => $score,
                'rev'     => filemtime($file),
                'mtime'   => filemtime($file),
                'size'    => filesize($file),
                'snippet' => $snippet,
            );
        }
        return $pages;
    }

    /**
     * Returns the wiki title.
     */
    function getTitle(){
        global $conf;
        return $conf['title'];
    }

    /**
     * List all media files.
     *
     * Available options are 'recursive' for also including the subnamespaces
     * in the listing, and 'pattern' for filtering the returned files against
     * a regular expression matching their name.
     *
     * @author Gina Haeussge <osd@foosel.net>
     */
    function listAttachments($ns, $options = array()) {
        global $conf;
        global $lang;

        $ns = cleanID($ns);

        if (!is_array($options)) $options = array();
        $options['skipacl'] = 0; // no ACL skipping for XMLRPC


        if(auth_quickaclcheck($ns.':*') >= AUTH_READ) {
            $dir = utf8_encodeFN(str_replace(':', '/', $ns));

            $data = array();
            search($data, $conf['mediadir'], 'search_media', $options, $dir);
            $len = count($data);
            if(!$len) return array();

            for($i=0; $i<$len; $i++) {
                unset($data[$i]['meta']);
                $data[$i]['lastModified'] = new IXR_Date($data[$i]['mtime']);
            }
            return $data;
        } else {
            return new IXR_Error(1, 'You are not allowed to list media files.');
        }
    }

    /**
     * Return a list of backlinks
     */
    function listBackLinks($id){
        return ft_backlinks(cleanID($id));
    }

    /**
     * Return some basic data about a page
     */
    function pageInfo($id,$rev=''){
        $id = cleanID($id);
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

        return ($data);
    }

    /**
     * Save a wiki page
     *
     * @author Michael Klier <chi@chimeric.de>
     */
    function putPage($id, $text, $params) {
        global $TEXT;
        global $lang;
        global $conf;

        $id    = cleanID($id);
        $TEXT  = cleanText($text);
        $sum   = $params['sum'];
        $minor = $params['minor'];

        if(empty($id))
            return new IXR_Error(1, 'Empty page ID');

        if(!page_exists($id) && trim($TEXT) == '' ) {
            return new IXR_ERROR(1, 'Refusing to write an empty new wiki page');
        }

        if(auth_quickaclcheck($id) < AUTH_EDIT)
            return new IXR_Error(1, 'You are not allowed to edit this page');

        // Check, if page is locked
        if(checklock($id))
            return new IXR_Error(1, 'The page is currently locked');

        // SPAM check
        if(checkwordblock())
            return new IXR_Error(1, 'Positive wordblock check');

        // autoset summary on new pages
        if(!page_exists($id) && empty($sum)) {
            $sum = $lang['created'];
        }

        // autoset summary on deleted pages
        if(page_exists($id) && empty($TEXT) && empty($sum)) {
            $sum = $lang['deleted'];
        }

        lock($id);

        saveWikiText($id,$TEXT,$sum,$minor);

        unlock($id);

        // run the indexer if page wasn't indexed yet
        idx_addPage($id);

        return 0;
    }

    /**
     * Appends text to a wiki page.
     */
    function appendPage($id, $text, $params) {
        $currentpage = $this->rawPage($id);
        if (!is_string($currentpage)) {
            return $currentpage;
        }
        return $this->putPage($id, $currentpage.$text, $params);
    }

    /**
     * Uploads a file to the wiki.
     *
     * Michael Klier <chi@chimeric.de>
     */
    function putAttachment($id, $file, $params) {
        $id = cleanID($id);
        $auth = auth_quickaclcheck(getNS($id).':*');

        if(!isset($id)) {
            return new IXR_ERROR(1, 'Filename not given.');
        }

        global $conf;

        $ftmp = $conf['tmpdir'] . '/' . md5($id.clientIP());

        // save temporary file
        @unlink($ftmp);
        if (preg_match('/^[A-Za-z0-9\+\/]*={0,2}$/', $file) === 1) {
            // DEPRECATED: Double-decode file if it still looks like base64
            // after first decoding (which is done by the library)
            $file = base64_decode($file);
        }
        io_saveFile($ftmp, $file);

        $res = media_save(array('name' => $ftmp), $id, $params['ow'], $auth, 'rename');
        if (is_array($res)) {
            return new IXR_ERROR(-$res[1], $res[0]);
        } else {
            return $res;
        }
    }

    /**
     * Deletes a file from the wiki.
     *
     * @author Gina Haeussge <osd@foosel.net>
     */
    function deleteAttachment($id){
        $id = cleanID($id);
        $auth = auth_quickaclcheck(getNS($id).':*');
        $res = media_delete($id, $auth);
        if ($res & DOKU_MEDIA_DELETED) {
            return 0;
        } elseif ($res & DOKU_MEDIA_NOT_AUTH) {
            return new IXR_ERROR(1, "You don't have permissions to delete files.");
        } elseif ($res & DOKU_MEDIA_INUSE) {
            return new IXR_ERROR(1, 'File is still referenced');
        } else {
            return new IXR_ERROR(1, 'Could not delete file');
        }
    }

    /**
    * Returns the permissions of a given wiki page
    */
    function aclCheck($id) {
        $id = cleanID($id);
        return auth_quickaclcheck($id);
    }

    /**
     * Lists all links contained in a wiki page
     *
     * @author Michael Klier <chi@chimeric.de>
     */
    function listLinks($id) {
        $id = cleanID($id);
        if(auth_quickaclcheck($id) < AUTH_READ){
            return new IXR_Error(1, 'You are not allowed to read this page');
        }
        $links = array();

        // resolve page instructions
        $ins   = p_cached_instructions(wikiFN($id));

        // instantiate new Renderer - needed for interwiki links
        include(DOKU_INC.'inc/parser/xhtml.php');
        $Renderer = new Doku_Renderer_xhtml();
        $Renderer->interwiki = getInterwiki();

        // parse parse instructions
        foreach($ins as $in) {
            $link = array();
            switch($in[0]) {
                case 'internallink':
                    $link['type'] = 'local';
                    $link['page'] = $in[1][0];
                    $link['href'] = wl($in[1][0]);
                    array_push($links,$link);
                    break;
                case 'externallink':
                    $link['type'] = 'extern';
                    $link['page'] = $in[1][0];
                    $link['href'] = $in[1][0];
                    array_push($links,$link);
                    break;
                case 'interwikilink':
                    $url = $Renderer->_resolveInterWiki($in[1][2],$in[1][3]);
                    $link['type'] = 'extern';
                    $link['page'] = $url;
                    $link['href'] = $url;
                    array_push($links,$link);
                    break;
            }
        }

        return ($links);
    }

    /**
     * Returns a list of recent changes since give timestamp
     *
     * @author Michael Hamann <michael@content-space.de>
     * @author Michael Klier <chi@chimeric.de>
     */
    function getRecentChanges($timestamp) {
        if(strlen($timestamp) != 10)
            return new IXR_Error(20, 'The provided value is not a valid timestamp');

        $recents = getRecentsSince($timestamp);

        $changes = array();

        foreach ($recents as $recent) {
            $change = array();
            $change['name']         = $recent['id'];
            $change['lastModified'] = new IXR_Date($recent['date']);
            $change['author']       = $recent['user'];
            $change['version']      = $recent['date'];
            $change['perms']        = $recent['perms'];
            $change['size']         = @filesize(wikiFN($recent['id']));
            array_push($changes, $change);
        }

        if (!empty($changes)) {
            return $changes;
        } else {
            // in case we still have nothing at this point
            return new IXR_Error(30, 'There are no changes in the specified timeframe');
        }
    }

    /**
     * Returns a list of recent media changes since give timestamp
     *
     * @author Michael Hamann <michael@content-space.de>
     * @author Michael Klier <chi@chimeric.de>
     */
    function getRecentMediaChanges($timestamp) {
        if(strlen($timestamp) != 10)
            return new IXR_Error(20, 'The provided value is not a valid timestamp');

        $recents = getRecentsSince($timestamp, null, '', RECENTS_MEDIA_CHANGES);

        $changes = array();

        foreach ($recents as $recent) {
            $change = array();
            $change['name']         = $recent['id'];
            $change['lastModified'] = new IXR_Date($recent['date']);
            $change['author']       = $recent['user'];
            $change['version']      = $recent['date'];
            $change['perms']        = $recent['perms'];
            $change['size']         = @filesize(mediaFN($recent['id']));
            array_push($changes, $change);
        }

        if (!empty($changes)) {
            return $changes;
        } else {
            // in case we still have nothing at this point
            return new IXR_Error(30, 'There are no changes in the specified timeframe');
        }
    }

    /**
     * Returns a list of available revisions of a given wiki page
     *
     * @author Michael Klier <chi@chimeric.de>
     */
    function pageVersions($id, $first) {
        $id = cleanID($id);
        if(auth_quickaclcheck($id) < AUTH_READ){
            return new IXR_Error(1, 'You are not allowed to read this page');
        }
        global $conf;

        $versions = array();

        if(empty($id))
            return new IXR_Error(1, 'Empty page ID');

        $revisions = getRevisions($id, $first, $conf['recent']+1);

        if(count($revisions)==0 && $first!=0) {
            $first=0;
            $revisions = getRevisions($id, $first, $conf['recent']+1);
        }

        if(count($revisions)>0 && $first==0) {
            array_unshift($revisions, '');  // include current revision
            array_pop($revisions);          // remove extra log entry
        }

        $hasNext = false;
        if(count($revisions)>$conf['recent']) {
            $hasNext = true;
            array_pop($revisions); // remove extra log entry
        }

        if(!empty($revisions)) {
            foreach($revisions as $rev) {
                $file = wikiFN($id,$rev);
                $time = @filemtime($file);
                // we check if the page actually exists, if this is not the
                // case this can lead to less pages being returned than
                // specified via $conf['recent']
                if($time){
                    $info = getRevisionInfo($id, $time, 1024);
                    if(!empty($info)) {
                        $data['user'] = $info['user'];
                        $data['ip']   = $info['ip'];
                        $data['type'] = $info['type'];
                        $data['sum']  = $info['sum'];
                        $data['modified'] = new IXR_Date($info['date']);
                        $data['version'] = $info['date'];
                        array_push($versions, $data);
                    }
                }
            }
            return $versions;
        } else {
            return array();
        }
    }

    /**
     * The version of Wiki RPC API supported
     */
    function wiki_RPCVersion(){
        return 2;
    }


    /**
     * Locks or unlocks a given batch of pages
     *
     * Give an associative array with two keys: lock and unlock. Both should contain a
     * list of pages to lock or unlock
     *
     * Returns an associative array with the keys locked, lockfail, unlocked and
     * unlockfail, each containing lists of pages.
     */
    function setLocks($set){
        $locked     = array();
        $lockfail   = array();
        $unlocked   = array();
        $unlockfail = array();

        foreach((array) $set['lock'] as $id){
            $id = cleanID($id);
            if(auth_quickaclcheck($id) < AUTH_EDIT || checklock($id)){
                $lockfail[] = $id;
            }else{
                lock($id);
                $locked[] = $id;
            }
        }

        foreach((array) $set['unlock'] as $id){
            $id = cleanID($id);
            if(auth_quickaclcheck($id) < AUTH_EDIT || !unlock($id)){
                $unlockfail[] = $id;
            }else{
                $unlocked[] = $id;
            }
        }

        return array(
            'locked'     => $locked,
            'lockfail'   => $lockfail,
            'unlocked'   => $unlocked,
            'unlockfail' => $unlockfail,
        );
    }

    function getAPIVersion(){
        return DOKU_XMLRPC_API_VERSION;
    }

    function login($user,$pass){
        global $conf;
        global $auth;
        if(!$conf['useacl']) return 0;
        if(!$auth) return 0;

        @session_start(); // reopen session for login
        if($auth->canDo('external')){
            $ok = $auth->trustExternal($user,$pass,false);
        }else{
            $evdata = array(
                'user'     => $user,
                'password' => $pass,
                'sticky'   => false,
                'silent'   => true,
            );
            $ok = trigger_event('AUTH_LOGIN_CHECK', $evdata, 'auth_login_wrapper');
        }
        session_write_close(); // we're done with the session

        return $ok;
    }


}

$server = new dokuwiki_xmlrpc_server();

// vim:ts=4:sw=4:et:
