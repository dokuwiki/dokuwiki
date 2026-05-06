<?php

namespace dokuwiki\Search\Query;

use dokuwiki\Utf8\PhpString;
use dokuwiki\Extension\Event;
use dokuwiki\Search\Collection\Term;
use dokuwiki\Search\Indexer;
use dokuwiki\Utf8;

/**
 * Evaluates a parsed search query against word lookup results
 *
 * Uses typed stack entries (PageSet, NamespacePredicate, NegatedEntry)
 * to avoid materializing the full page index unless absolutely necessary
 * (standalone NOT or namespace-only queries).
 */
class QueryEvaluator
{
    /** @var string[] RPN token array from QueryParser */
    protected array $rpn;

    /** @var Term[] word => Term mapping from CollectionSearch */
    protected array $terms;

    /** @var PageSet|null lazy-loaded universe of all indexed pages */
    protected ?PageSet $allPages = null;

    /**
     * @param string[] $rpn RPN token array from QueryParser::convert()['parsed_ary']
     * @param Term[] $terms word => Term mapping from CollectionSearch::execute()
     */
    public function __construct(array $rpn, array $terms)
    {
        $this->rpn = $rpn;
        $this->terms = $terms;
    }

    /**
     * Evaluate the RPN query and return matching pages with scores
     *
     * The query is represented in Reverse Polish Notation (RPN), also known as postfix
     * notation. In RPN, operands come first and operators follow. For example, the infix
     * expression "A AND B" becomes "A B AND" in RPN. This eliminates the need for
     * parentheses — the expression "(A OR B) AND C" is simply "A B OR C AND".
     *
     * Evaluation uses a stack. Each token is processed left to right: operand tokens
     * (words, phrases, namespaces) push an entry onto the stack; operator tokens (AND,
     * OR, NOT) pop their operands from the stack, compute a result, and push it back.
     * After all tokens are processed, the single remaining stack entry is the final result.
     *
     * The stack entries are typed: word lookups produce PageSet entries (concrete page
     * results with scores), namespace tokens produce NamespacePredicate entries (a filter
     * to be applied later), and NOT wraps its operand in a NegatedEntry. Binary operators
     * inspect the types of their operands to choose the most efficient operation — for
     * example, AND with a NegatedEntry performs set subtraction rather than requiring
     * the full page universe.
     *
     * @return array<string, int> page ID => score
     */
    public function evaluate(): array
    {
        /** @var StackEntry[] $stack */
        $stack = [];

        foreach ($this->rpn as $token) {
            switch (substr($token, 0, 3)) {
                case 'W+:':
                case 'W-:':
                case 'W_:':
                    $word = substr($token, 3);
                    if (isset($this->terms[$word])) {
                        $stack[] = new PageSet($this->terms[$word]->getEntityFrequencies());
                    }
                    break;

                case 'P+:':
                case 'P-:':
                    $phrase = substr($token, 3);
                    // Phrases are always preceded by their component words AND'd together,
                    // so the top of stack contains pages matching all words in the phrase.
                    // We verify the actual phrase exists in those candidate pages.
                    $candidates = end($stack) ?: new PageSet();
                    $stack[] = $this->matchPhrase($phrase, $this->materialize($candidates));
                    break;

                case 'N+:':
                case 'N-:':
                    $ns = cleanID(substr($token, 3)) . ':';
                    $stack[] = new NamespacePredicate($ns);
                    break;

                case 'AND':
                    $right = array_pop($stack);
                    $left = array_pop($stack);
                    $stack[] = $this->opAnd($left, $right);
                    break;

                case 'OR':
                    $right = array_pop($stack);
                    $left = array_pop($stack);
                    $stack[] = $this->opOr($left, $right);
                    break;

                case 'NOT':
                    $operand = array_pop($stack);
                    $stack[] = new NegatedEntry($operand);
                    break;
            }
        }

        $result = array_pop($stack) ?? new PageSet();
        return $this->materialize($result)->getPages();
    }

    // region Operators

    /**
     * AND: combine two operands based on their types
     *
     * PageSet AND PageSet produces an intersection with summed scores. When one
     * operand is a NegatedEntry, the operation becomes set subtraction. When one
     * operand is a NamespacePredicate, the other is filtered by namespace prefix.
     *
     * @param StackEntry $left
     * @param StackEntry $right
     * @return StackEntry
     */
    protected function opAnd(StackEntry $left, StackEntry $right): StackEntry
    {
        // page set AND negated → subtract
        if ($left instanceof PageSet && $right instanceof NegatedEntry) {
            return $this->subtractNegated($left, $right);
        }
        if ($left instanceof NegatedEntry && $right instanceof PageSet) {
            return $this->subtractNegated($right, $left);
        }

        // page set AND namespace → filter by prefix
        if ($left instanceof PageSet && $right instanceof NamespacePredicate) {
            return $right->filter($left);
        }
        if ($left instanceof NamespacePredicate && $right instanceof PageSet) {
            return $left->filter($right);
        }

        // page set AND page set → intersect, sum scores
        if ($left instanceof PageSet && $right instanceof PageSet) {
            return $left->intersect($right);
        }

        // rare cases (negated AND negated, namespace AND namespace, etc.)
        return $this->materialize($left)->intersect($this->materialize($right));
    }

    /**
     * OR: unite two operands
     *
     * PageSet OR PageSet produces a union with summed scores. Other combinations
     * require materializing operands into concrete page sets first.
     *
     * @param StackEntry $left
     * @param StackEntry $right
     * @return StackEntry
     */
    protected function opOr(StackEntry $left, StackEntry $right): StackEntry
    {
        if ($left instanceof PageSet && $right instanceof PageSet) {
            return $left->unite($right);
        }

        return $this->materialize($left)->unite($this->materialize($right));
    }

    /**
     * Subtract a NegatedEntry from a PageSet
     *
     * The inner entry of the NegatedEntry determines the operation:
     * - NegatedEntry(PageSet): set subtraction
     * - NegatedEntry(NamespacePredicate): exclude pages matching the namespace
     *
     * @param PageSet $pages the positive page set
     * @param NegatedEntry $negated the negated operand
     * @return PageSet
     */
    protected function subtractNegated(PageSet $pages, NegatedEntry $negated): PageSet
    {
        $inner = $negated->getInner();

        if ($inner instanceof NamespacePredicate) {
            return $inner->exclude($pages);
        }

        return $pages->subtract($this->materialize($inner));
    }

    // endregion

    // region Phrase matching

    /**
     * Check which pages from the candidate set contain the given phrase
     *
     * Verifies phrase presence by reading each page's raw wiki text.
     * Plugins can override phrase matching via the FULLTEXT_PHRASE_MATCH event.
     * Pages that match retain their original scores from the candidate set.
     *
     * @param string $phrase the phrase to search for
     * @param PageSet $candidates pages to check (typically the AND'd word results)
     * @return PageSet pages where the phrase was found, with original scores preserved
     */
    protected function matchPhrase(string $phrase, PageSet $candidates): PageSet
    {
        $matched = [];
        foreach ($candidates->getPages() as $id => $score) {
            $evdata = [
                'id' => $id,
                'phrase' => $phrase,
                'text' => rawWiki($id),
            ];
            $event = new Event('FULLTEXT_PHRASE_MATCH', $evdata);
            if ($event->advise_before() && $event->result !== true) {
                $text = PhpString::strtolower($evdata['text']);
                if (str_contains($text, $phrase)) {
                    $event->result = true;
                }
            }
            $event->advise_after();
            if ($event->result === true) {
                $matched[$id] = $score;
            }
        }
        return new PageSet($matched);
    }

    // endregion

    // region Materialization

    /**
     * Convert any StackEntry into a concrete PageSet
     *
     * For PageSet entries, returns as-is. NamespacePredicate and NegatedEntry
     * cannot be resolved without knowing all existing pages — a namespace
     * predicate needs the full page list to find matching pages, and a negated
     * entry needs it to compute the complement. This is why materialization
     * triggers a lazy-load of the full page index from disk.
     *
     * This is only needed for standalone namespace or negative-only queries
     * (e.g., just "@wiki:" or just "-foo"). In combined queries, the typed
     * operators handle these entries without the universe.
     *
     * @param StackEntry $entry
     * @return PageSet
     */
    protected function materialize(StackEntry $entry): PageSet
    {
        if ($entry instanceof PageSet) {
            return $entry;
        }

        if ($entry instanceof NegatedEntry) {
            return $this->getAllPages()->subtract($this->materialize($entry->getInner()));
        }

        if ($entry instanceof NamespacePredicate) {
            return $entry->filter($this->getAllPages());
        }

        return new PageSet();
    }

    /**
     * Lazy-load the universe of all indexed pages
     *
     * @return PageSet all pages with score 0
     */
    protected function getAllPages(): PageSet
    {
        if (!$this->allPages instanceof PageSet) {
            $pages = (new Indexer())->getAllPages();
            $this->allPages = new PageSet(array_fill_keys($pages, 0));
        }
        return $this->allPages;
    }

    // endregion
}
