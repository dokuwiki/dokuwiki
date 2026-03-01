<?php

class sectionid_test extends Dokuwikitest
{
    /**
     * DataProvider
     *
     * @return Generator|array
     * @see testSectionidsAreUnique
     */
    public function provideTestData(){
        // Each test case represents a sequence of sections title
        return [
            [['A', 'A', 'A1']],
            [['A', 'A1', 'A']]
        ];
    }

    /**
     * @dataProvider provideTestData
     * @param array $titles
     */
    function testSectionidsAreUnique($titles)
    {
        $check = array();
        $alreadyGeneratedIds = array();
        foreach($titles as $title){
            $newId = sectionID($title, $check);
            $this->assertNotContains($newId, $alreadyGeneratedIds, "id $newId has been generated twice. The 2nd time it was for the title $title");
            $alreadyGeneratedIds []= $newId;
        }
    }

    /**
     * The convention in the code is to pass $check=false when we're not interested in having
     * unique sectionID. This test ensures that this type of call is correctly handled
     */
    function testSectionIDCanBeCalledWithNonArrayCheck(){
        $check = false;
        $this->assertEquals("abc", sectionID("abc", $check), "Passing \$check=false shouldn't lead to an error");
        $this->assertEquals("abc", sectionID("abc", $check), "Passing \$check=false shouldn't try to deduplicate id");
    }
}
