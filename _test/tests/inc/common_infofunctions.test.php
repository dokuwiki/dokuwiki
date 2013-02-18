<?php

class common_infofunctions_test extends DokuWikiTest {
 
    function setup(){
        parent::setup();

        global $USERINFO; 
        $USERINFO = array(
           'pass' => '179ad45c6ce2cb97cf1029e212046e81',
           'name' => 'Arthur Dent',
           'mail' => 'arthur@example.com',
           'grps' => array ('admin','user'),
        );
        $_SERVER['REMOTE_USER'] = 'testuser';
        $_SERVER['REMOTE_ADDR'] = '1.2.3.4';
    }
    
    function _get_info() {
        global $USERINFO;
        $info = array (
          'isadmin' => true,
          'ismanager' => true,
          'userinfo' => $USERINFO,
          'perm' => 255,
          'namespace' => false,
          'ismobile' => false,
          'client' => 'testuser',
        );
      
        return $info;
    }

    /**
     * Its important to have the correct set of keys.
     * Other functions provide the values
     */
    function test_basicinfo(){
        // test with REMOTE_USER set and the user an admin user
        $info = $this->_get_info();
        $this->assertEquals(basicinfo($ID,true),$info);
        
        // with $httpclient parameter set to false 
        unset($info['ismobile']);
        $this->assertEquals(basicinfo($ID,false),$info);
        
        // with anonymous user
        unset($_SERVER['REMOTE_USER']);
        global $USERINFO; $USERINFO = array();

        $info = array(
          'isadmin' => false,
          'ismanager' => false,
          'perm' => 8,
          'namespace' => false,
          'ismobile' => false,
          'client' => '1.2.3.4',
        );
        $this->assertEquals(basicinfo($ID,true),$info);
    }
    
    /**
     * We're interested in the extra keys required for $INFO when its a page request
     * and that $REV, $RANGE globals are set/cleared correctly
     */
    function test_pageinfo(){
        global $ID,$conf;
        $ID = 'wiki:start';
        
        $info = $this->_get_info();
        $info['id'] = 'wiki:start';
        $info['namespace'] = 'wiki';
        $info['rev'] = null;
        $info['subscribed'] = false;
        $info['locked'] = false;
        $info['filepath'] = $conf['datadir'].'/wiki/start.txt';
        $info['exists'] = false;
        $info['writable'] = true;
        $info['editable'] = true;
        $info['lastmod'] = false;
        $info['meta'] = array();
        $info['ip'] = null;
        $info['user'] = null;
        $info['sum'] = null;
        $info['editor'] = null;

        // basic test, no revision
        $this->assertEquals(pageinfo(),$info);
        
        // TODO: test with revision = current page
        
        // TODO: test with true revision
        
        // TODO: test with revision & range
    }
    
    /**
     * We're interested in the extra keys for $INFO when its a media request
     */
    function test_mediainfo(){
        global $NS, $IMG;
        $NS = '';
        $IMG = 'testimage.png';
         
        $info = $this->_get_info();
        $info['image'] = 'testimage.png';
        
        $this->assertEquals(mediainfo(),$info);
    }
}

//Setup VIM: ex: et ts=4 :
