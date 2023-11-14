<?php

namespace dokuwiki\test\rector;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Replace ptln() calls with echo
 */
class DokuWikiPtlnRector extends AbstractRector
{

    /** @inheritdoc */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace ptln() calls with echo', [
            new CodeSample(
                <<<'CODE_SAMPLE'
ptln('Hello World', 7);
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
        if (!$node->expr instanceof FuncCall) {
            return null;
        }

        if (!$this->nodeNameResolver->isName($node->expr, 'ptln')) {
            return null;
        }

        return new Echo_([
            $node->expr->args[0]->value
        ]);
    }
}
