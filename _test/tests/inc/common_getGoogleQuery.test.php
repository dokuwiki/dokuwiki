<?php

class common_getGoogleQuery_test extends DokuWikiTest {

    /**
     * https://github.com/splitbrain/dokuwiki/issues/2848
     */
    function test_google_form(){
        global $INPUT;
        $_SERVER['HTTP_REFERER'] = 'https://www.google.com/url?q=https://www.dokuwiki.org/&sa=D&ust=a&usg=b';
        $INPUT = new Input();
        $this->assertEquals('', getGoogleQuery());
    }

    function test_google_url(){
        global $INPUT;
        $_SERVER['HTTP_REFERER'] = 'https://www.google.com/url?sa=t&source=web&rct=j&url=https://www.dokuwiki.org/&ved=a';
        $INPUT = new Input();
        $this->assertEquals('', getGoogleQuery());
    }

    function test_uncommon_url(){
        global $INPUT;
        $_SERVER['HTTP_REFERER'] = 'http://search.example.com/search?q=DokuWiki';
        $INPUT = new Input();
        $this->assertEquals('', getGoogleQuery());
    }

    function test_old_google(){
        global $INPUT;
        $_SERVER['HTTP_REFERER'] = 'https://www.google.com/search?newwindow=1&q=what%27s+my+referer';
        $INPUT = new Input();
        $this->assertEquals(array('what', 's', 'my', 'referer'), getGoogleQuery());
    }

}

//Setup VIM: ex: et ts=4 :
