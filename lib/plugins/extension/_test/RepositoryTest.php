<?php

namespace dokuwiki\plugin\extension\test;

use dokuwiki\plugin\extension\Exception as ExtensionException;
use dokuwiki\plugin\extension\Repository;
use DokuWikiTest;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests for the repository caching of the extension plugin
 *
 * @group plugin_extension
 * @group plugins
 */
class RepositoryTest extends DokuWikiTest
{
    protected $pluginsEnabled = ['extension'];

    /** @inheritdoc */
    public function setUp(): void
    {
        parent::setUp();
        // the availability flag is cached in the file system and outlives a single test
        self::callInaccessibleMethod($this->getRepository(), 'revokeAccess', []);
    }

    /**
     * Get a repository whose API requests are stubbed out
     *
     * @return Repository|MockObject
     */
    protected function getRepository()
    {
        return $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['ping', 'fetchExtensions'])
            ->getMock();
    }

    /**
     * An extension known to not be in the repository must not be requested again
     */
    public function testKnownMissingExtensionIsNotFetchedAgain()
    {
        $repo = $this->getRepository();
        $repo->expects($this->never())->method('fetchExtensions');
        self::callInaccessibleMethod($repo, 'storeCache', ['nosuchextension', []]);

        $this->assertSame(['nosuchextension' => null], $repo->initExtensions(['nosuchextension']));
    }

    /**
     * An extension that has never been looked up must still be requested
     */
    public function testUnknownExtensionIsFetched()
    {
        $repo = $this->getRepository();
        $repo->expects($this->once())->method('fetchExtensions');

        $repo->initExtensions(['neverseenextension']);
    }

    /**
     * A reachable repository must not be pinged again in the next request
     */
    public function testAccessIsRememberedBetweenRequests()
    {
        $first = $this->getRepository();
        $first->expects($this->once())->method('ping')->willReturn('1');
        $this->assertTrue($first->checkAccess());

        $second = $this->getRepository();
        $second->expects($this->never())->method('ping');
        $this->assertTrue($second->checkAccess());
    }

    /**
     * A failing API request must make the next request ping again
     */
    public function testFailingRequestForgetsAccess()
    {
        $first = $this->getRepository();
        $first->method('ping')->willReturn('1');
        $first->checkAccess();
        self::callInaccessibleMethod($first, 'revokeAccess', []);

        $second = $this->getRepository();
        $second->expects($this->once())->method('ping')->willReturn('1');
        $this->assertTrue($second->checkAccess());
    }

    /**
     * An unreachable repository must not be remembered as reachable
     */
    public function testUnreachableRepositoryIsNotRemembered()
    {
        $failing = $this->getRepository();
        $failing->method('ping')->willReturn(false);
        try {
            $failing->checkAccess();
            $this->fail('checkAccess() should have thrown an exception');
        } catch (ExtensionException $e) {
            $this->assertNotEmpty($e->getMessage());
        }

        $second = $this->getRepository();
        $second->expects($this->once())->method('ping')->willReturn('1');
        $this->assertTrue($second->checkAccess());
    }
}
