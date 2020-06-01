<?php

class toolbar_test extends DokuWikiTest {

    function test_encode_toolbar_signature() {
        global $conf, $INFO, $INPUT;

        $conf['signature'] = '" --- \\\\n //[[@MAIL@|@NAME@]] (@USER@) @DATE@//"';
        $_SERVER['REMOTE_USER'] = 'john';
        $INFO['userinfo']['name'] = '/*!]]>*/</script><script>alert("\123\")</script>';
        $INFO['userinfo']['mail'] = 'example@example.org';

        $date = str_replace('/', '\/', dformat());

        $expected = '"\" --- \\\n \/\/[[example@example.org|\/*!]]>*\/<\/script><script>'.
                    'alert(\"\\\\123\\\\\\")<\/script>]] (john) '.$date.'\/\/\""';

        $this->assertEquals($expected, toolbar_signature());
    }
}
