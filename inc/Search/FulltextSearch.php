<?php

namespace dokuwiki\Search;

use dokuwiki\Extension\Event;
use dokuwiki\Search\Collection\CollectionSearch;
use dokuwiki\Search\Collection\PageFulltextCollection;
use dokuwiki\Search\Exception\SearchException;
use dokuwiki\Search\Query\QueryEvaluator;
use dokuwiki\Search\Query\QueryParser;
use dokuwiki\Utf8;

/**
 * DokuWiki Fulltext Search
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
class FulltextSearch
{
    /** @var int Maximum number of results to generate snippets for */
    protected int $maxSnippets = 15;

    /**
     * @return int
     */
    public function getMaxSnippets(): int
    {
        return $this->maxSnippets;
    }

    /**
     * @param int $maxSnippets
     */
    public function setMaxSnippets(int $maxSnippets): void
    {
        $this->maxSnippets = $maxSnippets;
    }

    /**
     * The fulltext search
     *
     * Returns a list of matching documents for the given query
     *
     * @triggers SEARCH_QUERY_FULLPAGE
     *
     * @param string     $query   the search query string
     * @param array      $highlight  will be filled with terms to highlight
     * @param string     $sort    sort mode: 'hits' (default) or 'mtime'
     * @param int|string $after   only show results with mtime after this date,
     *                            accepts timestamp or strtotime arguments
     * @param int|string $before  only show results with mtime before this date,
     *                            accepts timestamp or strtotime arguments
     *
     * @return array matching documents as pageid => score
     */
    public function pageSearch($query, &$highlight, $sort = null, $after = null, $before = null)
    {
        if ($sort === null) {
            $sort = 'hits';
        }
        $data = [
            'query' => $query,
            'sort' => $sort,
            'after' => $after,
            'before' => $before
        ];
        $data['highlight'] =& $highlight;
        $action = [$this, 'pageSearchCallBack'];
        return Event::createAndTrigger('SEARCH_QUERY_FULLPAGE', $data, $action);
    }

    /**
     * Returns a list of matching documents for the given query
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Kazutaka Miyasaka <kazmiya@gmail.com>
     *
     * @param array $data  event data
     * @return array       matching documents as pageid => score
     */
    public function pageSearchCallBack(&$data)
    {
        // parse the given query
        $q = (new QueryParser)->convert($data['query']);
        $data['highlight'] = $q['highlight'];

        if (empty($q['parsed_ary'])) return [];

        // look up all words via CollectionSearch
        $collection = new PageFulltextCollection();
        $search = new CollectionSearch($collection);
        foreach ($q['words'] as $word) {
            try {
                $search->addTerm($word);
            } catch (SearchException $e) {
                // term too short or invalid, skip
            }
        }
        $terms = $search->execute();

        // evaluate the query
        $evaluator = new QueryEvaluator($q['parsed_ary'], $terms);
        $docs = $evaluator->evaluate();

        if (empty($docs)) return [];

        // filter by visibility, acls, existence, and time range
        $docs = MetadataSearch::filterPages($docs, false, $data['after'], $data['before']);

        if ($data['sort'] === 'mtime') {
            uksort($docs, static function ($a, $b) {
                return filemtime(wikiFN($b)) - filemtime(wikiFN($a));
            });
        } else {
            arsort($docs);
        }

        return $docs;
    }

    /**
     * Creates a snippet extract
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @triggers FULLTEXT_SNIPPET_CREATE
     *
     * @param string $id page id
     * @param array $highlight
     * @return mixed
     */
    public function snippet($id, $highlight)
    {
        $text = rawWiki($id);
        $text = str_replace("\xC2\xAD",'',$text); // remove soft-hyphens
        $evdata = array(
            'id'        => $id,
            'text'      => &$text,
            'highlight' => &$highlight,
            'snippet'   => '',
        );

        $evt = new Event('FULLTEXT_SNIPPET_CREATE', $evdata);
        if ($evt->advise_before()) {
            $match = array();
            $snippets = array();
            $utf8_offset = $offset = $end = 0;
            $len = Utf8\PhpString::strlen($text);

            // build a regexp from the phrases to highlight
            $re1 = '(' .
                join(
                    '|',
                    array_map(
                        [$this, 'snippetRePreprocess'],
                        array_map(
                            'preg_quote_cb',
                            array_filter((array) $highlight)
                        )
                    )
                ) .
                ')';
            $re2 = "$re1.{0,75}(?!\\1)$re1";
            $re3 = "$re1.{0,45}(?!\\1)$re1.{0,45}(?!\\1)(?!\\2)$re1";

            for ($cnt=4; $cnt--;) {
                if (0) {
                } elseif (preg_match('/'.$re3.'/iu', $text, $match, PREG_OFFSET_CAPTURE, $offset)) {
                } elseif (preg_match('/'.$re2.'/iu', $text, $match, PREG_OFFSET_CAPTURE, $offset)) {
                } elseif (preg_match('/'.$re1.'/iu', $text, $match, PREG_OFFSET_CAPTURE, $offset)) {
                } else {
                    break;
                }

                list($str, $idx) = $match[0];

                // convert $idx (a byte offset) into a utf8 character offset
                $utf8_idx = Utf8\PhpString::strlen(substr($text, 0, $idx));
                $utf8_len = Utf8\PhpString::strlen($str);

                // establish context, 100 bytes surrounding the match string
                // first look to see if we can go 100 either side,
                // then drop to 50 adding any excess if the other side can't go to 50,
                $pre = min($utf8_idx - $utf8_offset, 100);
                $post = min($len - $utf8_idx - $utf8_len, 100);

                if ($pre > 50 && $post > 50) {
                    $pre = $post = 50;
                } elseif ($pre > 50) {
                    $pre = min($pre, 100 - $post);
                } elseif ($post > 50) {
                    $post = min($post, 100 - $pre);
                } elseif ($offset == 0) {
                    // both are less than 50, means the context is the whole string
                    // make it so and break out of this loop - there is no need for the
                    // complex snippet calculations
                    $snippets = array($text);
                    break;
                }

                // establish context start and end points, try to append to previous
                // context if possible
                $start = $utf8_idx - $pre;
                $append = ($start < $end) ? $end : false;  // still the end of the previous context snippet
                $end = $utf8_idx + $utf8_len + $post;      // now set it to the end of this context

                if ($append) {
                    $snippets[count($snippets)-1] .= Utf8\PhpString::substr($text, $append, $end-$append);
                } else {
                    $snippets[] = Utf8\PhpString::substr($text, $start, $end-$start);
                }

                // set $offset for next match attempt
                // continue matching after the current match
                // if the current match is not the longest possible match starting at the current offset
                // this prevents further matching of this snippet but for possible matches of length
                // smaller than match length + context (at least 50 characters) this match is part of the context
                $utf8_offset = $utf8_idx + $utf8_len;
                $offset = $idx + strlen(Utf8\PhpString::substr($text, $utf8_idx, $utf8_len));
                $offset = Utf8\Clean::correctIdx($text, $offset);
            }

            $m = "\1";
            $snippets = preg_replace('/'.$re1.'/iu', $m.'$1'.$m, $snippets);
            $snippet = preg_replace(
                '/' . $m . '([^' . $m . ']*?)' . $m . '/iu',
                '<strong class="search_hit">$1</strong>',
                hsc(join('... ', $snippets))
            );

            $evdata['snippet'] = $snippet;
        }
        $evt->advise_after();
        unset($evt);

        return $evdata['snippet'];
    }

    /**
     * Wraps a search term in regex boundary checks.
     *
     * @param string $term
     * @return string
     */
    public function snippetRePreprocess($term)
    {
        // do not process asian terms where word boundaries are not explicit
        if (Utf8\Asian::isAsianWords($term)) return $term;

        if (UTF8_PROPERTYSUPPORT) {
            // unicode word boundaries
            // see http://stackoverflow.com/a/2449017/172068
            $BL = '(?<!\pL)';
            $BR = '(?!\pL)';
        } else {
            // not as correct as above, but at least won't break
            $BL = '\b';
            $BR = '\b';
        }

        if (substr($term, 0, 2) == '\\*') {
            $term = substr($term, 2);
        } else {
            $term = $BL.$term;
        }

        if (substr($term, -2, 2) == '\\*') {
            $term = substr($term, 0, -2);
        } else {
            $term = $term.$BR;
        }

        if ($term == $BL || $term == $BR || $term == $BL.$BR) {
            $term = '';
        }
        return $term;
    }
}
