<?php

/**
 * Test namespace includes
 *
 * @group plugin_indexmenu
 * @group plugins
 */
class ActionTest extends DokuWikiTest
{
    /**
     * Setup - enable and load the include plugin and create the test pages
     */
    public function setUp(): void
    {
        $this->pluginsEnabled[] = 'indexmenu';
        parent::setUp(); // this enables the include plugin
//        $this->helper = plugin_load('helper', 'include');

//        global $conf;
//        $conf['hidepages'] = 'inclhidden:hidden';

        // for testing hidden pages
        saveWikiText('ns2:bpage', "======H1======\nText", 'Sort different naturally/title/page');
        saveWikiText('ns2:apage', "======H3======\nText", 'Sort different naturally/title/page');
        saveWikiText('ns2:cpage', "======H2======\nText", 'Sort different naturally/title/page');

        // pages on different levels
        saveWikiText('ns1:ns1:apage', 'Page on level 1', 'Created page on level 1');
        saveWikiText('ns1:lvl2:lvl3:lvl4:apage', 'Page on level 4', 'Created page on level 4');
        saveWikiText('ns1:ns2:apage', 'Page on level 2', 'Created page on level 2');
        saveWikiText('ns1:ns0:bpage', 'Page on level 2', 'Created page on level 2');
    }

}
