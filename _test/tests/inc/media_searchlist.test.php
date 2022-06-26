<?php

class media_searchlist_test extends DokuWikiTest
{

    /**
     * @var string namespace used for testing
     */
    protected $upload_ns = 'media_searchlist_test';

    /**
     * Save the file
     *
     * @param string $name name of saving file
     * @param string $copy file used as a content of uploaded file
     */
    protected function save($name, $copy)
    {
        $media_id = $this->upload_ns . ':' . $name;
        media_save(array('name' => $copy), $media_id, true, AUTH_UPLOAD, 'copy');
    }

    /**
     * Called for each test
     *
     * @throws Exception
     */
    function setUp() : void
    {
        parent::setUp();

        //create some files to search
        $png = mediaFN('wiki:kind_zu_katze.png');
        $ogv = mediaFN('wiki:kind_zu_katze.ogv');
        $webm = mediaFN('wiki:kind_zu_katze.webm');

        $this->save('a.png', $png);
        $this->save('aa.png', $png);
        $this->save('ab.png', $png);

        $this->save('a.ogv', $ogv);
        $this->save('aa.ogv', $ogv);
        $this->save('ab.ogv', $ogv);

        $this->save('a:a.png', $png);
        $this->save('b:a.png', $png);

        $this->save('0.webm', $webm);

    }

    /**
     * Wrap around media_searchlist: return the result
     * Reset media_printfile static variables afterwards
     *
     * @param $query
     * @param $ns
     * @return string
     */
    protected function media_searchlist($query, $ns)
    {
        ob_start();
        media_searchlist($query, $ns);
        $out = ob_get_contents();
        ob_end_clean();
        return $out;
    }

    /**
     * @return array[]
     * @see testSearch
     */
    public function provideSearch()
    {
        return [
            ['a.png', ['a:a.png', 'b:a.png', 'a.png', 'aa.png']], // no globbing
            ['a*.png', ['a:a.png', 'b:a.png', 'a.png', 'aa.png', 'ab.png']], // globbing asterisk
            ['*.ogv', ['a.ogv', 'aa.ogv', 'ab.ogv']], // globbing find by ext
            ['a?.png', ['aa.png', 'ab.png']], // globbing question mark
            ['a?.*', ['aa.ogv', 'aa.png', 'ab.ogv', 'ab.png']], // globbing question mark and asterisk
            ['?.png', ['a:a.png', 'b:a.png', 'a.png']], // globbing question mark on the beginning
            ['??.png', ['aa.png', 'ab.png']], // globbing two question marks on the beginning
            ['??.*', ['aa.ogv', 'aa.png', 'ab.ogv', 'ab.png']], // globbing two letter file names
            ['0', ['0.webm']], // zero search
        ];
    }

    /**
     * @dataProvider provideSearch
     * @param string $query The query to use
     * @param string[] $expected The expected media IDs in the result HTML
     * @throws Exception
     */
    public function testSearch($query, $expected)
    {
        $result = $this->media_searchlist($query, $this->upload_ns);
        $pq = phpQuery::newDocument($result);

        $elements = $pq->find('a.mediafile');
        $actual = [];
        foreach ($elements as $element) {
            $actual[] = $element->textContent;
        }

        $this->assertEquals(count($expected), count($elements));
        $this->assertEquals($expected, $actual);
    }

}
