<?php

namespace dokuwiki\plugin\extension\test;

use dokuwiki\plugin\extension\Extension;
use dokuwiki\plugin\extension\Notice;
use DokuWikiTest;

/**
 * Tests for the notice generation of the extension plugin
 *
 * @group plugin_extension
 * @group plugins
 */
class NoticeTest extends DokuWikiTest
{
    protected $pluginsEnabled = ['extension'];

    /**
     * A malformed conflict id in the repository metadata must not crash the notice generation
     *
     * Extension authors may enter free form text like "sprintdoc template" in the conflicts
     * field. Such values are not valid extension ids and must be skipped silently.
     */
    public function testMalformedConflictIsIgnored()
    {
        $extension = Extension::createFromRemoteData([
            'plugin' => 'plugin1',
            'conflicts' => ['sprintdoc template'],
        ]);

        $notices = Notice::list($extension);

        $this->assertIsArray($notices);
        $this->assertEmpty($notices[Notice::WARNING]);
    }

    /**
     * A malformed dependency id in the repository metadata must not crash the notice generation
     */
    public function testMalformedDependencyIsIgnored()
    {
        // the extension plugin itself is installed, so the dependency check is not skipped
        $extension = Extension::createFromRemoteData([
            'plugin' => 'extension',
            'depends' => ['not:a:valid:id'],
        ]);

        $notices = Notice::list($extension);

        $this->assertIsArray($notices);
        $this->assertEmpty($notices[Notice::ERROR]);
    }
}
