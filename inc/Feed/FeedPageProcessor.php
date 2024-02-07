<?php

namespace dokuwiki\Feed;

use Diff;
use dokuwiki\ChangeLog\PageChangeLog;
use TableDiffFormatter;
use UnifiedDiffFormatter;

/**
 * Accept more or less arbitrary data to represent a page and provide lazy loading accessors
 * to all the data we need for feed generation.
 */
class FeedPageProcessor extends FeedItemProcessor
{
    /** @var array[] metadata */
    protected $meta;

    // region data processors

    /** @inheritdoc */
    public function getURL($linkto)
    {
        switch ($linkto) {
            case 'page':
                $opt = ['rev' => $this->getRev()];
                break;
            case 'rev':
                $opt = ['rev' => $this->getRev(), 'do' => 'revisions'];
                break;
            case 'current':
                $opt = [];
                break;
            case 'diff':
            default:
                $opt = ['rev' => $this->getRev(), 'do' => 'diff'];
        }

        return wl($this->getId(), $opt, true, '&');
    }

    /** @inheritdoc */
    public function getBody($content)
    {
        global $lang;

        switch ($content) {
            case 'diff':
                $diff = $this->getDiff();
                // note: diff output must be escaped, UnifiedDiffFormatter provides plain text
                $udf = new UnifiedDiffFormatter();
                return "<pre>\n" . hsc($udf->format($diff)) . "\n</pre>";

            case 'htmldiff':
                $diff = $this->getDiff();
                // note: no need to escape diff output, TableDiffFormatter provides 'safe' html
                $tdf = new TableDiffFormatter();
                $content = '<table>';
                $content .= '<tr><th colspan="2" width="50%">' . dformat($this->getPrev()) . '</th>';
                $content .= '<th colspan="2" width="50%">' . $lang['current'] . '</th></tr>';
                $content .= $tdf->format($diff);
                $content .= '</table>';
                return $content;

            case 'html':
                if ($this->isExisting()) {
                    $html = p_wiki_xhtml($this->getId(), '', false);
                } else {
                    $html = p_wiki_xhtml($this->getId(), $this->getRev(), false);
                }
                return $this->cleanHTML($html);

            case 'abstract':
            default:
                return $this->getAbstract();
        }
    }

    /** @inheritdoc */
    public function getCategory()
    {
        $meta = $this->getMetaData();
        return (array)($meta['subject'] ?? (string)getNS($this->getId()));
    }

    // endregion

    // region data accessors

    /**
     * Get the page abstract
     *
     * @return string
     */
    public function getAbstract()
    {
        if (!isset($this->data['abstract'])) {
            $meta = $this->getMetaData();
            if (isset($meta['description']['abstract'])) {
                $this->data['abstract'] = (string)$meta['description']['abstract'];
            } else {
                $this->data['abstract'] = '';
            }
        }
        return $this->data['abstract'];
    }

    /** @inheritdoc */
    public function getRev()
    {
        $rev = parent::getRev();
        if ($rev) return $rev;

        if (page_exists($this->id)) {
            $this->data['rev'] = filemtime(wikiFN($this->id));
            $this->data['exists'] = true;
        } else {
            $this->loadRevisions();
        }
        return $this->data['rev'];
    }

    /**
     * Get the previous revision timestamp of this page
     *
     * @return int|null The previous revision or null if there is none
     */
    public function getPrev()
    {
        if ($this->data['prev'] ?? 0) return $this->data['prev'];
        $this->loadRevisions();
        return $this->data['prev'];
    }

    /**
     * Does this page exist?
     *
     * @return bool
     */
    public function isExisting()
    {
        if (!isset($this->data['exists'])) {
            $this->data['exists'] = page_exists($this->id);
        }
        return $this->data['exists'];
    }

    /**
     * Get the title of this page
     *
     * @return string
     */
    public function getTitle()
    {
        global $conf;
        if (!isset($this->data['title'])) {
            if ($conf['useheading']) {
                $this->data['title'] = p_get_first_heading($this->id);
            } else {
                $this->data['title'] = noNS($this->id);
            }
        }
        return $this->data['title'];
    }

    // endregion

    /**
     * Get the metadata of this page
     *
     * @return array[]
     */
    protected function getMetaData()
    {
        if (!isset($this->meta)) {
            $this->meta = (array)p_get_metadata($this->id);
        }
        return $this->meta;
    }

    /**
     * Load the current and previous revision from the changelog
     * @return void
     */
    protected function loadRevisions()
    {
        $changelog = new PageChangeLog($this->id);
        $revs = $changelog->getRevisions(0, 2); // FIXME check that this returns the current one correctly
        if (!isset($this->data['rev'])) {
            // prefer an already set date, only set if missing
            // it should usally not happen that neither is available
            $this->data['rev'] = $revs[0] ?? 0;
        }
        // a previous revision might not exist
        $this->data['prev'] = $revs[1] ?? null;
    }

    /**
     * Get a diff between this and the previous revision
     *
     * @return Diff
     */
    protected function getDiff()
    {
        $prev = $this->getPrev();

        if ($prev) {
            return new Diff(
                explode("\n", rawWiki($this->getId(), $prev)),
                explode("\n", rawWiki($this->getId(), ''))
            );
        }
        return new Diff([''], explode("\n", rawWiki($this->getId(), '')));
    }
}
