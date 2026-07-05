<?php

namespace dokuwiki\test\Parsing\ParserMode;

use dokuwiki\Parsing\ModeRegistry;
use dokuwiki\Parsing\ParserMode\Eol;
use dokuwiki\Parsing\ParserMode\Table;

class AbstractModeTest extends \DokuWikiTest
{
    /** @var ModeRegistry */
    private $registry;

    function setUp(): void
    {
        parent::setUp();
        global $conf;
        $this->registry = new ModeRegistry($conf['syntax']);
    }

    /**
     * A mode that both declares accepted categories and assigns individual mode
     * names to $allowedModes directly (the sibling-component pattern) must keep
     * both after setModeRegistry(): the category-derived modes and its own direct
     * entries. Regression: setModeRegistry() used to replace the list with the
     * category-derived modes, dropping the direct entries.
     */
    function testSetModeRegistryMergesDirectlyAssignedModesWithCategories()
    {
        // Table declares FORMATTING/SUBSTITUTION/DISABLED/PROTECTED categories.
        $mode = new Table();
        self::setInaccessibleProperty($mode, 'allowedModes', ['plugin_foo_bar']);

        $mode->setModeRegistry($this->registry);

        // the directly-assigned sibling mode survives the merge
        $this->assertTrue($mode->accepts('plugin_foo_bar'));
        // the category-derived modes are still present
        $this->assertTrue($mode->accepts('strong'));
        $this->assertTrue($mode->accepts('unformatted'));
    }

    /**
     * With no categories declared, the directly-assigned $allowedModes are used
     * as-is (and deduplicated).
     */
    function testSetModeRegistryUsesDirectlyAssignedModesWhenNoCategories()
    {
        // Eol declares no categories.
        $mode = new Eol();
        self::setInaccessibleProperty($mode, 'allowedModes', ['plugin_foo_bar', 'plugin_foo_baz', 'plugin_foo_bar']);

        $mode->setModeRegistry($this->registry);

        $this->assertTrue($mode->accepts('plugin_foo_bar'));
        $this->assertTrue($mode->accepts('plugin_foo_baz'));
        $this->assertFalse($mode->accepts('strong'));
        $this->assertSame(
            ['plugin_foo_bar', 'plugin_foo_baz'],
            self::getInaccessibleProperty($mode, 'allowedModes')
        );
    }
}
