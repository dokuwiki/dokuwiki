<?php
/**
 * Tests the pid functions of the indexer.
 *
 * @author Michael Hamann <michael@content-space.de>
 */
class indexer_pid_test extends EasyWikiTest {
    function test_pid() {
        $indexer = idx_get_indexer();
        $syntaxPID = $indexer->getPID('wiki:syntax');
        $this->assertEquals('wiki:syntax', $indexer->getPageFromPID($syntaxPID), 'getPageFromPID(getPID(\'wiki:syntax\')) != \'wiki:syntax\'');
        $easywikiPID = $indexer->getPID('wiki:easywiki');
        $this->assertEquals('wiki:syntax', $indexer->getPageFromPID($syntaxPID), 'getPageFromPID(getPID(\'wiki:syntax\')) != \'wiki:syntax\' after getting the PID for wiki:easywiki');
        $this->assertEquals($syntaxPID, $indexer->getPID('wiki:syntax'), 'getPID(\'wiki:syntax\') didn\'t returned different PIDs when called twice');
        $this->assertNotEquals($syntaxPID, $easywikiPID, 'Same PID returned for different pages');
        $this->assertTrue(is_numeric($syntaxPID) && is_numeric($easywikiPID), 'PIDs are not numeric');
    }
}
