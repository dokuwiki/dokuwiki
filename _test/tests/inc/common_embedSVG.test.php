<?php

class common_embedSVG_test extends DokuWikiTest {

    /**
     * embed should succeed with a cleaned up result
     */
    function test_success() {
        $file = mediaFN('wiki:test.svg');
        $clean =
            '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" '.
            'width="64" height="64" viewBox="0 0 64 64"><path d="M64 20l-32-16-32 16 32 16 32-16zM32 '.
            '9.311l21.379 10.689-21.379 10.689-21.379-10.689 21.379-10.689zM57.59 28.795l6.41 3.205-32 16-32-16 '.
            '6.41-3.205 25.59 12.795zM57.59 40.795l6.41 3.205-32 16-32-16 6.41-3.205 25.59 12.795z" '.
            'fill="#000000"></path></svg>';

        ob_start();
        $this->assertTrue(embedSVG($file));
        $svg = ob_get_clean();
        $this->assertEquals($clean, $svg);
    }

    /**
     * embed should fail because of the file size limit
     */
    function test_fail() {
        $file = mediaFN('wiki:test.svg');
        ob_start();
        $this->assertFalse(embedSVG($file, 100));
        $svg = ob_get_clean();
        $this->assertEquals('', $svg);
    }

}
