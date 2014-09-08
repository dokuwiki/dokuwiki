<?php

class common_stripsourcemaps_test extends DokuWikiTest {

    function test_all() {

        $text = <<<EOL
//@ sourceMappingURL=/foo/bar/xxx.map
//# sourceMappingURL=/foo/bar/xxx.map
/*@ sourceMappingURL=/foo/bar/xxx.map */
/*# sourceMappingURL=/foo/bar/xxx.map */
bang
EOL;

        $expect = <<<EOL
//
//
/**/
/**/
bang
EOL;

        stripsourcemaps($text);


        $this->assertEquals($expect, $text);
    }

}