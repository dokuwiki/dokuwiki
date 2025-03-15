<?php


class mediafn_test extends DokuWikiTest
{

    /**
     * Data provider for testMediaFN
     */
    public function mediaFNProvider()
    {
        return [
            // current
            ['wiki:dokuwiki-128.png', '', true, DOKU_TMP_DATA . 'media/wiki/dokuwiki-128.png'],
            ['wiki:dokuwiki-128.png', false, true, DOKU_TMP_DATA . 'media/wiki/dokuwiki-128.png'],
            ['wiki:dokuwiki-128.png', null, true, DOKU_TMP_DATA . 'media/wiki/dokuwiki-128.png'],

            // old
            ['wiki:dokuwiki-128.png', 1234567890, true, DOKU_TMP_DATA . 'media_attic/wiki/dokuwiki-128.1234567890.png'],

            // cleaning
            ['wiki:dokuwiki*oink.png', '', true, DOKU_TMP_DATA . 'media/wiki/dokuwiki_oink.png'],
            ['wiki:dokuwiki*oink.png', '', false, DOKU_TMP_DATA . 'media/wiki/dokuwiki%2Aoink.png'],
        ];
    }

    /**
     * @dataProvider mediaFNProvider
     */
    public function testMediaFN($id, $rev, $clean, $expected)
    {
        $result = mediaFN($id, $rev, $clean);
        $this->assertEquals($expected, $result);
    }

    public function testMediaFNCurrentRev()
    {
        $currentRev = filemtime(DOKU_TMP_DATA . 'media/wiki/dokuwiki-128.png');
        $result = mediaFN('wiki:dokuwiki-128.png', $currentRev);
        $this->assertEquals(DOKU_TMP_DATA . 'media/wiki/dokuwiki-128.png', $result);
    }

}

