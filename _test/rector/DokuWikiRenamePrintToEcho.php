<?php

namespace dokuwiki\test\rector;

use PhpParser\Node;
use PhpParser\Node\Expr\Print_;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Replace print calls with echo
 */
class DokuWikiRenamePrintToEcho extends AbstractRector
{

    /** @inheritdoc */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace print calls with echo', [
            new CodeSample(
                <<<'CODE_SAMPLE'
print 'Hello World';
CODE_SAMPLE,
                <<<'CODE_SAMPLE'
echo 'Hello World';
CODE_SAMPLE
            ),
        ]);
    }

    /** @inheritdoc */
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }

    /** @inheritdoc */
    public function refactor(Node $node)
    {
        if (!$node->expr instanceof Print_) {
            return null;
        }
        return new Echo_([$node->expr->expr], $node->getAttributes());
    }
}
