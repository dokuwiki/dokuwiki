<?php

namespace dokuwiki\test\Search\Query;

use dokuwiki\Search\Collection\Term;
use dokuwiki\Search\Query\QueryEvaluator;

/**
 * Tests for the QueryEvaluator class
 *
 * These tests verify RPN evaluation with typed stack entries (page sets,
 * namespace predicates, negated wrappers) independent of actual index data.
 */
class QueryEvaluatorTest extends \DokuWikiTest
{
    /**
     * Create a Term with pre-resolved entity frequencies
     *
     * @param string $word the word this term represents
     * @param array $frequencies [pageName => frequency]
     * @return Term
     */
    protected function makeTerm(string $word, array $frequencies): Term
    {
        $term = new Term($word);
        // Use addEntityFrequency with numeric IDs, then resolve with a map
        $map = [];
        $id = 0;
        foreach ($frequencies as $page => $freq) {
            $term->addEntityFrequency($id, $freq);
            $map[$id] = $page;
            $id++;
        }
        $term->resolveEntities($map);
        return $term;
    }

    // region Basic word lookups

    public function testSingleWord()
    {
        $terms = [
            'dokuwiki' => $this->makeTerm('dokuwiki', ['page1' => 3, 'page2' => 1]),
        ];
        $rpn = ['W+:dokuwiki'];

        $evaluator = new QueryEvaluator($rpn, $terms);
        $result = $evaluator->evaluate();

        $this->assertEquals(['page1' => 3, 'page2' => 1], $result);
    }

    public function testUnknownWord()
    {
        $terms = [];
        $rpn = ['W+:nonexistent'];

        $evaluator = new QueryEvaluator($rpn, $terms);
        $result = $evaluator->evaluate();

        $this->assertEquals([], $result);
    }

    // endregion

    // region AND operation

    public function testAndTwoWords()
    {
        $terms = [
            'foo' => $this->makeTerm('foo', ['page1' => 2, 'page2' => 3, 'page3' => 1]),
            'bar' => $this->makeTerm('bar', ['page1' => 1, 'page3' => 4]),
        ];
        // foo AND bar → pages in both, scores summed
        $rpn = ['W+:foo', 'W+:bar', 'AND'];

        $evaluator = new QueryEvaluator($rpn, $terms);
        $result = $evaluator->evaluate();

        $this->assertEquals(['page1' => 3, 'page3' => 5], $result);
    }

    // endregion

    // region OR operation

    public function testOrTwoWords()
    {
        $terms = [
            'foo' => $this->makeTerm('foo', ['page1' => 2, 'page2' => 3]),
            'bar' => $this->makeTerm('bar', ['page1' => 1, 'page3' => 4]),
        ];
        // foo OR bar → union, scores summed where overlapping
        $rpn = ['W+:foo', 'W+:bar', 'OR'];

        $evaluator = new QueryEvaluator($rpn, $terms);
        $result = $evaluator->evaluate();

        $this->assertEquals(['page1' => 3, 'page2' => 3, 'page3' => 4], $result);
    }

    // endregion

    // region NOT with AND (subtraction)

    public function testNotWithAnd()
    {
        // "foo -bar" → foo AND NOT bar → foo minus bar
        $terms = [
            'foo' => $this->makeTerm('foo', ['page1' => 2, 'page2' => 3, 'page3' => 1]),
            'bar' => $this->makeTerm('bar', ['page2' => 1]),
        ];
        // RPN: foo bar NOT AND
        $rpn = ['W+:foo', 'W-:bar', 'NOT', 'AND'];

        $evaluator = new QueryEvaluator($rpn, $terms);
        $result = $evaluator->evaluate();

        $this->assertEquals(['page1' => 2, 'page3' => 1], $result);
    }

    public function testNegatedGroupWithAnd()
    {
        // "baz -(foo OR bar)" → baz AND NOT(foo OR bar) → baz minus (foo ∪ bar)
        $terms = [
            'foo' => $this->makeTerm('foo', ['page1' => 1, 'page2' => 2]),
            'bar' => $this->makeTerm('bar', ['page2' => 1, 'page3' => 3]),
            'baz' => $this->makeTerm('baz', ['page1' => 5, 'page2' => 4, 'page3' => 2, 'page4' => 1]),
        ];
        // RPN: foo bar OR NOT baz AND
        $rpn = ['W+:foo', 'W+:bar', 'OR', 'NOT', 'W+:baz', 'AND'];

        $evaluator = new QueryEvaluator($rpn, $terms);
        $result = $evaluator->evaluate();

        // page1, page2, page3 are in (foo ∪ bar), so only page4 remains
        $this->assertEquals(['page4' => 1], $result);
    }

    // endregion

    // region Namespace filtering

    public function testNamespaceInclude()
    {
        // "foo @wiki:" → foo AND namespace wiki:
        $terms = [
            'foo' => $this->makeTerm('foo', ['wiki:page1' => 2, 'other:page2' => 3, 'wiki:sub:page3' => 1]),
        ];
        // RPN: foo N+:wiki AND
        $rpn = ['W+:foo', 'N+:wiki', 'AND'];

        $evaluator = new QueryEvaluator($rpn, $terms);
        $result = $evaluator->evaluate();

        $this->assertEquals(['wiki:page1' => 2, 'wiki:sub:page3' => 1], $result);
    }

    public function testNamespaceExclude()
    {
        // "foo ^wiki:" → foo AND NOT namespace wiki:
        $terms = [
            'foo' => $this->makeTerm('foo', ['wiki:page1' => 2, 'other:page2' => 3, 'wiki:sub:page3' => 1]),
        ];
        // RPN: foo N+:wiki NOT AND
        $rpn = ['W+:foo', 'N+:wiki', 'NOT', 'AND'];

        $evaluator = new QueryEvaluator($rpn, $terms);
        $result = $evaluator->evaluate();

        $this->assertEquals(['other:page2' => 3], $result);
    }

    // endregion

    // region Combined queries

    public function testOrThenNot()
    {
        // "(foo OR bar) -baz" → (foo OR bar) AND NOT baz
        $terms = [
            'foo' => $this->makeTerm('foo', ['page1' => 2, 'page2' => 1]),
            'bar' => $this->makeTerm('bar', ['page2' => 3, 'page3' => 4]),
            'baz' => $this->makeTerm('baz', ['page2' => 1]),
        ];
        // RPN: foo bar OR baz NOT AND
        $rpn = ['W+:foo', 'W+:bar', 'OR', 'W-:baz', 'NOT', 'AND'];

        $evaluator = new QueryEvaluator($rpn, $terms);
        $result = $evaluator->evaluate();

        $this->assertEquals(['page1' => 2, 'page3' => 4], $result);
    }

    public function testWordWithNamespaceAndNot()
    {
        // "foo -bar @wiki:" → foo AND NOT bar AND @wiki:
        $terms = [
            'foo' => $this->makeTerm('foo', [
                'wiki:a' => 5, 'wiki:b' => 3, 'other:c' => 2, 'wiki:d' => 1,
            ]),
            'bar' => $this->makeTerm('bar', ['wiki:b' => 1]),
        ];
        // RPN: foo bar NOT AND N+:wiki AND
        $rpn = ['W+:foo', 'W-:bar', 'NOT', 'AND', 'N+:wiki', 'AND'];

        $evaluator = new QueryEvaluator($rpn, $terms);
        $result = $evaluator->evaluate();

        // foo minus bar = wiki:a, other:c, wiki:d
        // filtered to wiki: = wiki:a, wiki:d
        $this->assertEquals(['wiki:a' => 5, 'wiki:d' => 1], $result);
    }

    public function testNamespaceDoesNotMatchPartialPrefix()
    {
        // @foo should not match pages in foobar: namespace
        $terms = [
            'test' => $this->makeTerm('test', [
                'foo:page1' => 1,
                'foobar:page2' => 2,
                'foo:sub:page3' => 3,
            ]),
        ];
        // RPN: test N+:foo AND
        $rpn = ['W+:test', 'N+:foo', 'AND'];

        $evaluator = new QueryEvaluator($rpn, $terms);
        $result = $evaluator->evaluate();

        // foobar:page2 must NOT match — only foo: prefix pages
        $this->assertEquals(['foo:page1' => 1, 'foo:sub:page3' => 3], $result);
    }

    // endregion

    // region Empty result cases

    public function testAndNoOverlap()
    {
        $terms = [
            'foo' => $this->makeTerm('foo', ['page1' => 1]),
            'bar' => $this->makeTerm('bar', ['page2' => 1]),
        ];
        $rpn = ['W+:foo', 'W+:bar', 'AND'];

        $evaluator = new QueryEvaluator($rpn, $terms);
        $result = $evaluator->evaluate();

        $this->assertEquals([], $result);
    }

    public function testNotRemovesAll()
    {
        $terms = [
            'foo' => $this->makeTerm('foo', ['page1' => 1]),
            'bar' => $this->makeTerm('bar', ['page1' => 2]),
        ];
        // foo -bar where bar covers all foo pages
        $rpn = ['W+:foo', 'W-:bar', 'NOT', 'AND'];

        $evaluator = new QueryEvaluator($rpn, $terms);
        $result = $evaluator->evaluate();

        $this->assertEquals([], $result);
    }

    public function testEmptyRpn()
    {
        $evaluator = new QueryEvaluator([], []);
        $result = $evaluator->evaluate();

        $this->assertEquals([], $result);
    }

    // endregion
}
