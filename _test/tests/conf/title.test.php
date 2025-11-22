<?php

class conf_title_test extends EasyWikiTest {

    function testTitle() {
        global $conf;

        $request = new TestRequest();
        $response = $request->get();
        $content = $response->queryHTML('title');
        $this->assertTrue(strpos($content,$conf['title']) > 0);

        $conf['title'] = 'Foo';
        $request = new TestRequest();
        $response = $request->get();
        $content = $response->queryHTML('title');
        $this->assertTrue(strpos($content,'Foo') > 0);
    }
}
