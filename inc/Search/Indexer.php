<?php

namespace dokuwiki\Search;

use dokuwiki\Utf8\Asian;
use dokuwiki\Utf8\Clean;
use dokuwiki\Utf8\PhpString;
use dokuwiki\Extension\Event;

/**
 * Class that encapsulates operations on the indexer database.
 *
 * @author Tom N Harris <tnharris@whoopdedo.org>
 */
class Indexer
{
    /**
     * @var array $pidCache Cache for getPID()
     */
    protected $pidCache = [];

    /**
     * Adds the contents of a page to the fulltext index
     *
     * The added text replaces previous words for the same page.
     * An empty value erases the page.
     *
     * @param string    $page   a page name
     * @param string    $text   the body of the page
     * @return string|boolean  the function completed successfully
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public function addPageWords($page, $text)
    {
        if (!$this->lock())
            return "locked";

        // load known documents
        $pid = $this->getPIDNoLock($page);
        if ($pid === false) {
            $this->unlock();
            return false;
        }

        $pagewords = [];
        // get word usage in page
        $words = $this->getPageWords($text);
        if ($words === false) {
            $this->unlock();
            return false;
        }

        if (!empty($words)) {
            foreach (array_keys($words) as $wlen) {
                $index = $this->getIndex('i', $wlen);
                foreach ($words[$wlen] as $wid => $freq) {
                    $idx = ($wid < count($index)) ? $index[$wid] : '';
                    $index[$wid] = $this->updateTuple($idx, $pid, $freq);
                    $pagewords[] = "$wlen*$wid";
                }
                if (!$this->saveIndex('i', $wlen, $index)) {
                    $this->unlock();
                    return false;
                }
            }
        }

        // Remove obsolete index entries
        $pageword_idx = $this->getIndexKey('pageword', '', $pid);
        if ($pageword_idx !== '') {
            $oldwords = explode(':', $pageword_idx);
            $delwords = array_diff($oldwords, $pagewords);
            $upwords = [];
            foreach ($delwords as $word) {
                if ($word != '') {
                    [$wlen, $wid] = explode('*', $word);
                    $wid = (int)$wid;
                    $upwords[$wlen][] = $wid;
                }
            }
            foreach ($upwords as $wlen => $widx) {
                $index = $this->getIndex('i', $wlen);
                foreach ($widx as $wid) {
                    $index[$wid] = $this->updateTuple($index[$wid], $pid, 0);
                }
                $this->saveIndex('i', $wlen, $index);
            }
        }
        // Save the reverse index
        $pageword_idx = implode(':', $pagewords);
        if (!$this->saveIndexKey('pageword', '', $pid, $pageword_idx)) {
            $this->unlock();
            return false;
        }

        $this->unlock();
        return true;
    }

    /**
     * Split the words in a page and add them to the index.
     *
     * @param string    $text   content of the page
     * @return array            list of word IDs and number of times used
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Christopher Smith <chris@jalakai.co.uk>
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    protected function getPageWords($text)
    {

        $tokens = $this->tokenizer($text);
        $tokens = array_count_values($tokens);  // count the frequency of each token

        $words = [];
        foreach ($tokens as $w => $c) {
            $l = wordlen($w);
            if (isset($words[$l])) {
                $words[$l][$w] = $c + ($words[$l][$w] ?? 0);
            } else {
                $words[$l] = [$w => $c];
            }
        }

        // arrive here with $words = array(wordlen => array(word => frequency))
        $index = [];   //resulting index
        foreach (array_keys($words) as $wlen) {
            $word_idx = $this->getIndex('w', $wlen);
            $word_idx_modified = false;
            foreach ($words[$wlen] as $word => $freq) {
                $word = (string)$word;
                $wid = array_search($word, $word_idx, true);
                if ($wid === false) {
                    $wid = count($word_idx);
                    $word_idx[] = $word;
                    $word_idx_modified = true;
                }
                if (!isset($index[$wlen]))
                    $index[$wlen] = [];
                $index[$wlen][$wid] = $freq;
            }
            // save back the word index
            if ($word_idx_modified && !$this->saveIndex('w', $wlen, $word_idx))
                return false;
        }

        return $index;
    }

    /**
     * Add/update keys to/of the metadata index.
     *
     * Adding new keys does not remove other keys for the page.
     * An empty value will erase the key.
     * The $key parameter can be an array to add multiple keys. $value will
     * not be used if $key is an array.
     *
     * @param string    $page   a page name
     * @param mixed     $key    a key string or array of key=>value pairs
     * @param mixed     $value  the value or list of values
     * @return boolean|string     the function completed successfully
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     * @author Michael Hamann <michael@content-space.de>
     */
    public function addMetaKeys($page, $key, $value = null)
    {
        if (!is_array($key)) {
            $key = [$key => $value];
        } elseif (!is_null($value)) {
            // $key is array, but $value is not null
            trigger_error("array passed to addMetaKeys but value is not null", E_USER_WARNING);
        }

        if (!$this->lock())
            return "locked";

        // load known documents
        $pid = $this->getPIDNoLock($page);
        if ($pid === false) {
            $this->unlock();
            return false;
        }

        // Special handling for titles so the index file is simpler
        if (isset($key['title'])) {
            $value = $key['title'];
            if (is_array($value)) {
                $value = $value[0];
            }
            $this->saveIndexKey('title', '', $pid, $value);
            unset($key['title']);
        }

        foreach ($key as $name => $values) {
            $metaname = idx_cleanName($name);
            $this->addIndexKey('metadata', '', $metaname);
            $metaidx = $this->getIndex($metaname . '_i', '');
            $metawords = $this->getIndex($metaname . '_w', '');
            $addwords = false;

            if (!is_array($values)) $values = [$values];

            $val_idx = $this->getIndexKey($metaname . '_p', '', $pid);
            if ($val_idx !== '') {
                $val_idx = explode(':', $val_idx);
                // -1 means remove, 0 keep, 1 add
                $val_idx = array_combine($val_idx, array_fill(0, count($val_idx), -1));
            } else {
                $val_idx = [];
            }

            foreach ($values as $val) {
                $val = (string)$val;
                if ($val !== "") {
                    $id = array_search($val, $metawords, true);
                    if ($id === false) {
                        // didn't find $val, so we'll add it to the end of metawords and create a placeholder in metaidx
                        $id = count($metawords);
                        $metawords[$id] = $val;
                        $metaidx[$id] = '';
                        $addwords = true;
                    }
                    // test if value is already in the index
                    if (isset($val_idx[$id]) && $val_idx[$id] <= 0) {
                        $val_idx[$id] = 0;
                    } else { // else add it
                        $val_idx[$id] = 1;
                    }
                }
            }

            if ($addwords) {
                $this->saveIndex($metaname . '_w', '', $metawords);
            }
            $vals_changed = false;
            foreach ($val_idx as $id => $action) {
                if ($action == -1) {
                    $metaidx[$id] = $this->updateTuple($metaidx[$id], $pid, 0);
                    $vals_changed = true;
                    unset($val_idx[$id]);
                } elseif ($action == 1) {
                    $metaidx[$id] = $this->updateTuple($metaidx[$id], $pid, 1);
                    $vals_changed = true;
                }
            }

            if ($vals_changed) {
                $this->saveIndex($metaname . '_i', '', $metaidx);
                $val_idx = implode(':', array_keys($val_idx));
                $this->saveIndexKey($metaname . '_p', '', $pid, $val_idx);
            }

            unset($metaidx);
            unset($metawords);
        }

        $this->unlock();
        return true;
    }

    /**
     * Rename a page in the search index without changing the indexed content. This function doesn't check if the
     * old or new name exists in the filesystem. It returns an error if the old page isn't in the page list of the
     * indexer and it deletes all previously indexed content of the new page.
     *
     * @param string $oldpage The old page name
     * @param string $newpage The new page name
     * @return string|bool If the page was successfully renamed, can be a message in the case of an error
     */
    public function renamePage($oldpage, $newpage)
    {
        if (!$this->lock()) return 'locked';

        $pages = $this->getPages();

        $id = array_search($oldpage, $pages, true);
        if ($id === false) {
            $this->unlock();
            return 'page is not in index';
        }

        $new_id = array_search($newpage, $pages, true);
        if ($new_id !== false) {
            // make sure the page is not in the index anymore
            if (!$this->deletePageNoLock($newpage)) {
                return false;
            }

            $pages[$new_id] = 'deleted:' . time() . random_int(0, 9999);
        }

        $pages[$id] = $newpage;

        // update index
        if (!$this->saveIndex('page', '', $pages)) {
            $this->unlock();
            return false;
        }

        // reset the pid cache
        $this->pidCache = [];

        $this->unlock();
        return true;
    }

    /**
     * Renames a meta value in the index. This doesn't change the meta value in the pages, it assumes that all pages
     * will be updated.
     *
     * @param string $key       The metadata key of which a value shall be changed
     * @param string $oldvalue  The old value that shall be renamed
     * @param string $newvalue  The new value to which the old value shall be renamed, if exists values will be merged
     * @return bool|string      If renaming the value has been successful, false or error message on error.
     */
    public function renameMetaValue($key, $oldvalue, $newvalue)
    {
        if (!$this->lock()) return 'locked';

        // change the relation references index
        $metavalues = $this->getIndex($key, '_w');
        $oldid = array_search($oldvalue, $metavalues, true);
        if ($oldid !== false) {
            $newid = array_search($newvalue, $metavalues, true);
            if ($newid !== false) {
                // free memory
                unset($metavalues);

                // okay, now we have two entries for the same value. we need to merge them.
                $indexline = $this->getIndexKey($key . '_i', '', $oldid);
                if ($indexline != '') {
                    $newindexline = $this->getIndexKey($key . '_i', '', $newid);
                    $pagekeys     = $this->getIndex($key . '_p', '');
                    $parts = explode(':', $indexline);
                    foreach ($parts as $part) {
                        [$id, $count] = explode('*', $part);
                        $newindexline =  $this->updateTuple($newindexline, $id, $count);

                        $keyline = explode(':', $pagekeys[$id]);
                        // remove old meta value
                        $keyline = array_diff($keyline, [$oldid]);
                        // add new meta value when not already present
                        if (!in_array($newid, $keyline)) {
                            $keyline[] = $newid;
                        }
                        $pagekeys[$id] = implode(':', $keyline);
                    }
                    $this->saveIndex($key . '_p', '', $pagekeys);
                    unset($pagekeys);
                    $this->saveIndexKey($key . '_i', '', $oldid, '');
                    $this->saveIndexKey($key . '_i', '', $newid, $newindexline);
                }
            } else {
                $metavalues[$oldid] = $newvalue;
                if (!$this->saveIndex($key . '_w', '', $metavalues)) {
                    $this->unlock();
                    return false;
                }
            }
        }

        $this->unlock();
        return true;
    }

    /**
     * Remove a page from the index
     *
     * Erases entries in all known indexes.
     *
     * @param string    $page   a page name
     * @return string|boolean  the function completed successfully
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    public function deletePage($page)
    {
        if (!$this->lock())
            return "locked";

        $result = $this->deletePageNoLock($page);

        $this->unlock();

        return $result;
    }

    /**
     * Remove a page from the index without locking the index, only use this function if the index is already locked
     *
     * Erases entries in all known indexes.
     *
     * @param string    $page   a page name
     * @return boolean          the function completed successfully
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    protected function deletePageNoLock($page)
    {
        // load known documents
        $pid = $this->getPIDNoLock($page);
        if ($pid === false) {
            return false;
        }

        // Remove obsolete index entries
        $pageword_idx = $this->getIndexKey('pageword', '', $pid);
        if ($pageword_idx !== '') {
            $delwords = explode(':', $pageword_idx);
            $upwords = [];
            foreach ($delwords as $word) {
                if ($word != '') {
                    [$wlen, $wid] = explode('*', $word);
                    $wid = (int)$wid;
                    $upwords[$wlen][] = $wid;
                }
            }
            foreach ($upwords as $wlen => $widx) {
                $index = $this->getIndex('i', $wlen);
                foreach ($widx as $wid) {
                    $index[$wid] = $this->updateTuple($index[$wid], $pid, 0);
                }
                $this->saveIndex('i', $wlen, $index);
            }
        }
        // Save the reverse index
        if (!$this->saveIndexKey('pageword', '', $pid, "")) {
            return false;
        }

        $this->saveIndexKey('title', '', $pid, "");
        $keyidx = $this->getIndex('metadata', '');
        foreach ($keyidx as $metaname) {
            $val_idx = explode(':', $this->getIndexKey($metaname . '_p', '', $pid));
            $meta_idx = $this->getIndex($metaname . '_i', '');
            foreach ($val_idx as $id) {
                if ($id === '') continue;
                $meta_idx[$id] = $this->updateTuple($meta_idx[$id], $pid, 0);
            }
            $this->saveIndex($metaname . '_i', '', $meta_idx);
            $this->saveIndexKey($metaname . '_p', '', $pid, '');
        }

        return true;
    }

    /**
     * Clear the whole index
     *
     * @return bool If the index has been cleared successfully
     */
    public function clear()
    {
        global $conf;

        if (!$this->lock()) return false;

        @unlink($conf['indexdir'] . '/page.idx');
        @unlink($conf['indexdir'] . '/title.idx');
        @unlink($conf['indexdir'] . '/pageword.idx');
        @unlink($conf['indexdir'] . '/metadata.idx');
        $dir = @opendir($conf['indexdir']);
        if ($dir !== false) {
            while (($f = readdir($dir)) !== false) {
                if (
                    str_ends_with($f, '.idx') &&
                    (str_starts_with($f, 'i') ||
                     str_starts_with($f, 'w') ||
                     str_ends_with($f, '_w.idx') ||
                     str_ends_with($f, '_i.idx') ||
                     str_ends_with($f, '_p.idx'))
                )
                    @unlink($conf['indexdir'] . "/$f");
            }
        }
        @unlink($conf['indexdir'] . '/lengths.idx');

        // clear the pid cache
        $this->pidCache = [];

        $this->unlock();
        return true;
    }

    /**
     * Split the text into words for fulltext search
     *
     * TODO: does this also need &$stopwords ?
     *
     * @triggers INDEXER_TEXT_PREPARE
     * This event allows plugins to modify the text before it gets tokenized.
     * Plugins intercepting this event should also intercept INDEX_VERSION_GET
     *
     * @param string    $text   plain text
     * @param boolean   $wc     are wildcards allowed?
     * @return array            list of words in the text
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public function tokenizer($text, $wc = false)
    {
        $wc = ($wc) ? '' : '\*';
        $stopwords =& idx_get_stopwords();

        // prepare the text to be tokenized
        $evt = new Event('INDEXER_TEXT_PREPARE', $text);
        if ($evt->advise_before(true)) {
            if (preg_match('/[^0-9A-Za-z ]/u', $text)) {
                $text = Asian::separateAsianWords($text);
            }
        }
        $evt->advise_after();
        unset($evt);

        $text = strtr(
            $text,
            ["\r" => ' ', "\n" => ' ', "\t" => ' ', "\xC2\xAD" => '']
        );
        if (preg_match('/[^0-9A-Za-z ]/u', $text))
            $text = Clean::stripspecials($text, ' ', '\._\-:' . $wc);

        $wordlist = explode(' ', $text);
        foreach ($wordlist as $i => $word) {
            $wordlist[$i] = (preg_match('/[^0-9A-Za-z]/u', $word)) ?
                PhpString::strtolower($word) : strtolower($word);
        }

        foreach ($wordlist as $i => $word) {
            if (
                (!is_numeric($word) && strlen($word) < IDX_MINWORDLENGTH)
                || in_array($word, $stopwords, true)
            )
                unset($wordlist[$i]);
        }
        return array_values($wordlist);
    }

    /**
     * Get the numeric PID of a page
     *
     * @param string $page The page to get the PID for
     * @return bool|int The page id on success, false on error
     */
    public function getPID($page)
    {
        // return PID without locking when it is in the cache
        if (isset($this->pidCache[$page])) return $this->pidCache[$page];

        if (!$this->lock())
            return false;

        // load known documents
        $pid = $this->getPIDNoLock($page);
        if ($pid === false) {
            $this->unlock();
            return false;
        }

        $this->unlock();
        return $pid;
    }

    /**
     * Get the numeric PID of a page without locking the index.
     * Only use this function when the index is already locked.
     *
     * @param string $page The page to get the PID for
     * @return bool|int The page id on success, false on error
     */
    protected function getPIDNoLock($page)
    {
        // avoid expensive addIndexKey operation for the most recently requested pages by using a cache
        if (isset($this->pidCache[$page])) return $this->pidCache[$page];
        $pid = $this->addIndexKey('page', '', $page);
        // limit cache to 10 entries by discarding the oldest element as in DokuWiki usually only the most recently
        // added item will be requested again
        if (count($this->pidCache) > 10) array_shift($this->pidCache);
        $this->pidCache[$page] = $pid;
        return $pid;
    }

    /**
     * Get the page id of a numeric PID
     *
     * @param int $pid The PID to get the page id for
     * @return string The page id
     */
    public function getPageFromPID($pid)
    {
        return $this->getIndexKey('page', '', $pid);
    }

    /**
     * Find pages in the fulltext index containing the words,
     *
     * The search words must be pre-tokenized, meaning only letters and
     * numbers with an optional wildcard
     *
     * The returned array will have the original tokens as key. The values
     * in the returned list is an array with the page names as keys and the
     * number of times that token appears on the page as value.
     *
     * @param array  $tokens list of words to search for
     * @return array         list of page names with usage counts
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public function lookup(&$tokens)
    {
        $result = [];
        $wids = $this->getIndexWords($tokens, $result);
        if (empty($wids)) return [];
        // load known words and documents
        $page_idx = $this->getIndex('page', '');
        $docs = [];
        foreach (array_keys($wids) as $wlen) {
            $wids[$wlen] = array_unique($wids[$wlen]);
            $index = $this->getIndex('i', $wlen);
            foreach ($wids[$wlen] as $ixid) {
                if ($ixid < count($index))
                    $docs["$wlen*$ixid"] = $this->parseTuples($page_idx, $index[$ixid]);
            }
        }
        // merge found pages into final result array
        $final = [];
        foreach ($result as $word => $res) {
            $final[$word] = [];
            foreach ($res as $wid) {
                // handle the case when ($ixid < count($index)) has been false
                // and thus $docs[$wid] hasn't been set.
                if (!isset($docs[$wid])) continue;
                $hits = &$docs[$wid];
                foreach ($hits as $hitkey => $hitcnt) {
                    // make sure the document still exists
                    if (!page_exists($hitkey, '', false)) continue;
                    if (!isset($final[$word][$hitkey]))
                        $final[$word][$hitkey] = $hitcnt;
                    else $final[$word][$hitkey] += $hitcnt;
                }
            }
        }
        return $final;
    }

    /**
     * Find pages containing a metadata key.
     *
     * The metadata values are compared as case-sensitive strings. Pass a
     * callback function that returns true or false to use a different
     * comparison function. The function will be called with the $value being
     * searched for as the first argument, and the word in the index as the
     * second argument. The function preg_match can be used directly if the
     * values are regexes.
     *
     * @param string    $key    name of the metadata key to look for
     * @param string    $value  search term to look for, must be a string or array of strings
     * @param callback  $func   comparison function
     * @return array            lists with page names, keys are query values if $value is array
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     * @author Michael Hamann <michael@content-space.de>
     */
    public function lookupKey($key, &$value, $func = null)
    {
        if (!is_array($value))
            $value_array = [$value];
        else $value_array =& $value;

        // the matching ids for the provided value(s)
        $value_ids = [];

        $metaname = idx_cleanName($key);

        // get all words in order to search the matching ids
        if ($key == 'title') {
            $words = $this->getIndex('title', '');
        } else {
            $words = $this->getIndex($metaname . '_w', '');
        }

        if (!is_null($func)) {
            foreach ($value_array as $val) {
                foreach ($words as $i => $word) {
                    if (call_user_func_array($func, [$val, $word]))
                        $value_ids[$i][] = $val;
                }
            }
        } else {
            foreach ($value_array as $val) {
                $xval = $val;
                $caret = '^';
                $dollar = '$';
                // check for wildcards
                if (str_starts_with($xval, '*')) {
                    $xval = substr($xval, 1);
                    $caret = '';
                }
                if (str_ends_with($xval, '*')) {
                    $xval = substr($xval, 0, -1);
                    $dollar = '';
                }
                if (!$caret || !$dollar) {
                    $re = $caret . preg_quote($xval, '/') . $dollar;
                    foreach (array_keys(preg_grep('/' . $re . '/', $words)) as $i)
                        $value_ids[$i][] = $val;
                } elseif (($i = array_search($val, $words, true)) !== false) {
                    $value_ids[$i][] = $val;
                }
            }
        }

        unset($words); // free the used memory

        // initialize the result so it won't be null
        $result = [];
        foreach ($value_array as $val) {
            $result[$val] = [];
        }

        $page_idx = $this->getIndex('page', '');

        // Special handling for titles
        if ($key == 'title') {
            foreach ($value_ids as $pid => $val_list) {
                $page = $page_idx[$pid];
                foreach ($val_list as $val) {
                    $result[$val][] = $page;
                }
            }
        } else {
            // load all lines and pages so the used lines can be taken and matched with the pages
            $lines = $this->getIndex($metaname . '_i', '');

            foreach ($value_ids as $value_id => $val_list) {
                // parse the tuples of the form page_id*1:page2_id*1 and so on, return value
                // is an array with page_id => 1, page2_id => 1 etc. so take the keys only
                $pages = array_keys($this->parseTuples($page_idx, $lines[$value_id]));
                foreach ($val_list as $val) {
                    $result[$val] = [...$result[$val], ...$pages];
                }
            }
        }
        if (!is_array($value)) $result = $result[$value];
        return $result;
    }

    /**
     * Find the index ID of each search term.
     *
     * The query terms should only contain valid characters, with a '*' at
     * either the beginning or end of the word (or both).
     * The $result parameter can be used to merge the index locations with
     * the appropriate query term.
     *
     * @param array  $words  The query terms.
     * @param array  $result Set to word => array("length*id" ...)
     * @return array         Set to length => array(id ...)
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    protected function getIndexWords(&$words, &$result)
    {
        $tokens = [];
        $tokenlength = [];
        $tokenwild = [];
        foreach ($words as $word) {
            $result[$word] = [];
            $caret = '^';
            $dollar = '$';
            $xword = $word;
            $wlen = wordlen($word);

            // check for wildcards
            if (str_starts_with($xword, '*')) {
                $xword = substr($xword, 1);
                $caret = '';
                --$wlen;
            }
            if (str_ends_with($xword, '*')) {
                $xword = substr($xword, 0, -1);
                $dollar = '';
                --$wlen;
            }
            if ($wlen < IDX_MINWORDLENGTH && $caret && $dollar && !is_numeric($xword))
                continue;
            if (!isset($tokens[$xword]))
                $tokenlength[$wlen][] = $xword;
            if (!$caret || !$dollar) {
                $re = $caret . preg_quote($xword, '/') . $dollar;
                $tokens[$xword][] = [$word, '/' . $re . '/'];
                if (!isset($tokenwild[$xword]))
                    $tokenwild[$xword] = $wlen;
            } else {
                $tokens[$xword][] = [$word, null];
            }
        }
        asort($tokenwild);
        // $tokens = array( base word => array( [ query term , regexp ] ... ) ... )
        // $tokenlength = array( base word length => base word ... )
        // $tokenwild = array( base word => base word length ... )
        $length_filter = $tokenwild === [] ? $tokenlength : min(array_keys($tokenlength));
        $indexes_known = $this->indexLengths($length_filter);
        if ($tokenwild !== []) sort($indexes_known);
        // get word IDs
        $wids = [];
        foreach ($indexes_known as $ixlen) {
            $word_idx = $this->getIndex('w', $ixlen);
            // handle exact search
            if (isset($tokenlength[$ixlen])) {
                foreach ($tokenlength[$ixlen] as $xword) {
                    $wid = array_search($xword, $word_idx, true);
                    if ($wid !== false) {
                        $wids[$ixlen][] = $wid;
                        foreach ($tokens[$xword] as $w)
                            $result[$w[0]][] = "$ixlen*$wid";
                    }
                }
            }
            // handle wildcard search
            foreach ($tokenwild as $xword => $wlen) {
                if ($wlen >= $ixlen) break;
                foreach ($tokens[$xword] as $w) {
                    if (is_null($w[1])) continue;
                    foreach (array_keys(preg_grep($w[1], $word_idx)) as $wid) {
                        $wids[$ixlen][] = $wid;
                        $result[$w[0]][] = "$ixlen*$wid";
                    }
                }
            }
        }
        return $wids;
    }

    /**
     * Return a list of all pages
     * Warning: pages may not exist!
     *
     * @param string    $key    list only pages containing the metadata key (optional)
     * @return array            list of page names
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    public function getPages($key = null)
    {
        $page_idx = $this->getIndex('page', '');
        if (is_null($key)) return $page_idx;

        $metaname = idx_cleanName($key);

        // Special handling for titles
        if ($key == 'title') {
            $title_idx = $this->getIndex('title', '');
            array_splice($page_idx, count($title_idx));
            foreach ($title_idx as $i => $title)
                if ($title === "") unset($page_idx[$i]);
            return array_values($page_idx);
        }

        $pages = [];
        $lines = $this->getIndex($metaname . '_i', '');
        foreach ($lines as $line) {
            $pages = array_merge($pages, $this->parseTuples($page_idx, $line));
        }
        return array_keys($pages);
    }

    /**
     * Return a list of words sorted by number of times used
     *
     * @param int       $min    bottom frequency threshold
     * @param int       $max    upper frequency limit. No limit if $max<$min
     * @param int       $minlen minimum length of words to count
     * @param string    $key    metadata key to list. Uses the fulltext index if not given
     * @return array            list of words as the keys and frequency as values
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    public function histogram($min = 1, $max = 0, $minlen = 3, $key = null)
    {
        if ($min < 1)
            $min = 1;
        if ($max < $min)
            $max = 0;

        $result = [];

        if ($key == 'title') {
            $index = $this->getIndex('title', '');
            $index = array_count_values($index);
            foreach ($index as $val => $cnt) {
                if ($cnt >= $min && (!$max || $cnt <= $max) && strlen($val) >= $minlen)
                    $result[$val] = $cnt;
            }
        } elseif (!is_null($key)) {
            $metaname = idx_cleanName($key);
            $index = $this->getIndex($metaname . '_i', '');
            $val_idx = [];
            foreach ($index as $wid => $line) {
                $freq = $this->countTuples($line);
                if ($freq >= $min && (!$max || $freq <= $max))
                    $val_idx[$wid] = $freq;
            }
            if (!empty($val_idx)) {
                $words = $this->getIndex($metaname . '_w', '');
                foreach ($val_idx as $wid => $freq) {
                    if (strlen($words[$wid]) >= $minlen)
                        $result[$words[$wid]] = $freq;
                }
            }
        } else {
            $lengths = idx_listIndexLengths();
            foreach ($lengths as $length) {
                if ($length < $minlen) continue;
                $index = $this->getIndex('i', $length);
                $words = null;
                foreach ($index as $wid => $line) {
                    $freq = $this->countTuples($line);
                    if ($freq >= $min && (!$max || $freq <= $max)) {
                        if ($words === null)
                            $words = $this->getIndex('w', $length);
                        $result[$words[$wid]] = $freq;
                    }
                }
            }
        }

        arsort($result);
        return $result;
    }

    /**
     * Lock the indexer.
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     *
     * @return bool|string
     */
    protected function lock()
    {
        global $conf;
        $status = true;
        $run = 0;
        $lock = $conf['lockdir'] . '/_indexer.lock';
        while (!@mkdir($lock)) {
            usleep(50);
            if (is_dir($lock) && time() - @filemtime($lock) > 60 * 5) {
                // looks like a stale lock - remove it
                if (!@rmdir($lock)) {
                    $status = "removing the stale lock failed";
                    return false;
                } else {
                    $status = "stale lock removed";
                }
            } elseif ($run++ == 1000) {
                // we waited 5 seconds for that lock
                return false;
            }
        }
        if ($conf['dperm']) {
            chmod($lock, $conf['dperm']);
        }
        return $status;
    }

    /**
     * Release the indexer lock.
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     *
     * @return bool
     */
    protected function unlock()
    {
        global $conf;
        @rmdir($conf['lockdir'] . '/_indexer.lock');
        return true;
    }

    /**
     * Retrieve the entire index.
     *
     * The $suffix argument is for an index that is split into
     * multiple parts. Different index files should use different
     * base names.
     *
     * @param string    $idx    name of the index
     * @param string    $suffix subpart identifier
     * @return array            list of lines without CR or LF
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    protected function getIndex($idx, $suffix)
    {
        global $conf;
        $fn = $conf['indexdir'] . '/' . $idx . $suffix . '.idx';
        if (!file_exists($fn)) return [];
        return file($fn, FILE_IGNORE_NEW_LINES);
    }

    /**
     * Replace the contents of the index with an array.
     *
     * @param string    $idx    name of the index
     * @param string    $suffix subpart identifier
     * @param array     $lines  list of lines without LF
     * @return bool             If saving succeeded
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    protected function saveIndex($idx, $suffix, &$lines)
    {
        global $conf;
        $fn = $conf['indexdir'] . '/' . $idx . $suffix;
        $fh = @fopen($fn . '.tmp', 'w');
        if (!$fh) return false;
        fwrite($fh, implode("\n", $lines));
        if (!empty($lines))
            fwrite($fh, "\n");
        fclose($fh);
        if ($conf['fperm'])
            chmod($fn . '.tmp', $conf['fperm']);
        io_rename($fn . '.tmp', $fn . '.idx');
        return true;
    }

    /**
     * Retrieve a line from the index.
     *
     * @param string    $idx    name of the index
     * @param string    $suffix subpart identifier
     * @param int       $id     the line number
     * @return string           a line with trailing whitespace removed
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    protected function getIndexKey($idx, $suffix, $id)
    {
        global $conf;
        $fn = $conf['indexdir'] . '/' . $idx . $suffix . '.idx';
        if (!file_exists($fn)) return '';
        $fh = @fopen($fn, 'r');
        if (!$fh) return '';
        $ln = -1;
        while (($line = fgets($fh)) !== false) {
            if (++$ln == $id) break;
        }
        fclose($fh);
        return rtrim((string)$line);
    }

    /**
     * Write a line into the index.
     *
     * @param string    $idx    name of the index
     * @param string    $suffix subpart identifier
     * @param int       $id     the line number
     * @param string    $line   line to write
     * @return bool             If saving succeeded
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    protected function saveIndexKey($idx, $suffix, $id, $line)
    {
        global $conf;
        if (!str_ends_with($line, "\n"))
            $line .= "\n";
        $fn = $conf['indexdir'] . '/' . $idx . $suffix;
        $fh = @fopen($fn . '.tmp', 'w');
        if (!$fh) return false;
        $ih = @fopen($fn . '.idx', 'r');
        if ($ih) {
            $ln = -1;
            while (($curline = fgets($ih)) !== false) {
                fwrite($fh, (++$ln == $id) ? $line : $curline);
            }
            if ($id > $ln) {
                while ($id > ++$ln)
                    fwrite($fh, "\n");
                fwrite($fh, $line);
            }
            fclose($ih);
        } else {
            $ln = -1;
            while ($id > ++$ln)
                fwrite($fh, "\n");
            fwrite($fh, $line);
        }
        fclose($fh);
        if ($conf['fperm'])
            chmod($fn . '.tmp', $conf['fperm']);
        io_rename($fn . '.tmp', $fn . '.idx');
        return true;
    }

    /**
     * Retrieve or insert a value in the index.
     *
     * @param string    $idx    name of the index
     * @param string    $suffix subpart identifier
     * @param string    $value  line to find in the index
     * @return int|bool          line number of the value in the index or false if writing the index failed
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    protected function addIndexKey($idx, $suffix, $value)
    {
        $index = $this->getIndex($idx, $suffix);
        $id = array_search($value, $index, true);
        if ($id === false) {
            $id = count($index);
            $index[$id] = $value;
            if (!$this->saveIndex($idx, $suffix, $index)) {
                trigger_error("Failed to write $idx index", E_USER_ERROR);
                return false;
            }
        }
        return $id;
    }

    /**
     * Get the list of lengths indexed in the wiki.
     *
     * Read the index directory or a cache file and returns
     * a sorted array of lengths of the words used in the wiki.
     *
     * @author YoBoY <yoboy.leguesh@gmail.com>
     *
     * @return array
     */
    protected function listIndexLengths()
    {
        return idx_listIndexLengths();
    }

    /**
     * Get the word lengths that have been indexed.
     *
     * Reads the index directory and returns an array of lengths
     * that there are indices for.
     *
     * @author YoBoY <yoboy.leguesh@gmail.com>
     *
     * @param array|int $filter
     * @return array
     */
    protected function indexLengths($filter)
    {
        global $conf;
        $idx = [];
        if (is_array($filter)) {
            // testing if index files exist only
            $path = $conf['indexdir'] . "/i";
            foreach (array_keys($filter) as $key) {
                if (file_exists($path . $key . '.idx'))
                    $idx[] = $key;
            }
        } else {
            $lengths = idx_listIndexLengths();
            foreach ($lengths as $length) {
                // keep all the values equal or superior
                if ((int)$length >= (int)$filter)
                    $idx[] = $length;
            }
        }
        return $idx;
    }

    /**
     * Insert or replace a tuple in a line.
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     *
     * @param string $line
     * @param string|int $id
     * @param int    $count
     * @return string
     */
    protected function updateTuple($line, $id, $count)
    {
        if ($line != '') {
            $line = preg_replace('/(^|:)' . preg_quote($id, '/') . '\*\d*/', '', $line);
        }
        $line = trim($line, ':');
        if ($count) {
            if ($line) {
                return "$id*$count:" . $line;
            } else {
                return "$id*$count";
            }
        }
        return $line;
    }

    /**
     * Split a line into an array of tuples.
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param array $keys
     * @param string $line
     * @return array
     */
    protected function parseTuples(&$keys, $line)
    {
        $result = [];
        if ($line == '') return $result;
        $parts = explode(':', $line);
        foreach ($parts as $tuple) {
            if ($tuple === '') continue;
            [$key, $cnt] = explode('*', $tuple);
            if (!$cnt) continue;
            if (isset($keys[$key])) {
                $key = $keys[$key];
                if ($key === false || is_null($key)) continue;
            }
            $result[$key] = $cnt;
        }
        return $result;
    }

    /**
     * Sum the counts in a list of tuples.
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     *
     * @param string $line
     * @return int
     */
    protected function countTuples($line)
    {
        $freq = 0;
        $parts = explode(':', $line);
        foreach ($parts as $tuple) {
            if ($tuple === '') continue;
            [/* pid */, $cnt] = explode('*', $tuple);
            $freq += (int)$cnt;
        }
        return $freq;
    }
}
