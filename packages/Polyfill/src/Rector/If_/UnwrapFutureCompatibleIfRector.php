<?php

declare(strict_types=1);

namespace Rector\Polyfill\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use Rector\PhpParser\Node\Manipulator\IfManipulator;
use Rector\Polyfill\FeatureSupport\FunctionSupportResolver;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Polyfill\Tests\Rector\If_\UnwrapFutureCompatibleIfRector\UnwrapFutureCompatibleIfRectorTest
 */
final class UnwrapFutureCompatibleIfRector extends AbstractRector
{
    /**
     * @var IfManipulator
     */
    private $ifManipulator;

    /**
     * @var FunctionSupportResolver
     */
    private $functionSupportResolver;

    public function __construct(IfManipulator $ifManipulator, FunctionSupportResolver $functionSupportResolver)
    {
        $this->ifManipulator = $ifManipulator;
        $this->functionSupportResolver = $functionSupportResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove functions exists if with else for always existing', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        // session locking trough other addons
        if (function_exists('session_abort')) {
            session_abort();
        } else {
            session_write_close();
        }
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        // session locking trough other addons
        session_abort();
    }
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $match = $this->ifManipulator->isIfElseWithFunctionCondition($node, 'function_exists');
        if ($match === false) {
            return null;
        }

        /** @var Node\Expr\FuncCall $funcCall */
        $funcCall = $node->cond;

        $functionToExistName = $this->getValue($funcCall->args[0]->value);
        if (! is_string($functionToExistName)) {
            return null;
        }

        if (! $this->functionSupportResolver->isFunctionSupported($functionToExistName)) {
            return null;
        }

        foreach ($node->stmts as $key => $ifStmt) {
            if ($key === 0) {
                // move comment from if to first element to keep it
                $ifStmt->setAttribute('comments', $node->getComments());
            }

            $this->addNodeAfterNode($ifStmt, $node);
        }

        $this->removeNode($node);

        return null;
    }
}
