<?php

namespace dokuwiki\Feed;

use dokuwiki\ChangeLog\MediaChangeLog;
use dokuwiki\File\MediaFile;
use dokuwiki\Ui\Media\Display;

class FeedMediaProcessor extends FeedItemProcessor
{
    /** @inheritdoc */
    public function getURL($linkto)
    {
        switch ($linkto) {
            case 'page':
                $opt = [
                    'image' => $this->getId(),
                    'ns' => getNS($this->getId()),
                    'rev' => $this->getRev()
                ];
                break;
            case 'rev':
                $opt = [
                    'image' => $this->getId(),
                    'ns' => getNS($this->getId()),
                    'rev' => $this->getRev(),
                    'tab_details' => 'history'
                ];
                break;
            case 'current':
                $opt = [
                    'image' => $this->getId(),
                    'ns' => getNS($this->getId())
                ];
                break;
            case 'diff':
            default:
                $opt = [
                    'image' => $this->getId(),
                    'ns' => getNS($this->getId()),
                    'rev' => $this->getRev(),
                    'tab_details' => 'history',
                    'media_do' => 'diff'
                ];
        }

        return media_managerURL($opt, '&', true);
    }

    public function getBody($content)
    {
        switch ($content) {
            case 'diff':
            case 'htmldiff':
                $prev = $this->getPrev();

                if ($prev) {
                    if ($this->isExisting()) {
                        $src1 = new MediaFile($this->getId(), $prev);
                        $src2 = new MediaFile($this->getId());
                    } else {
                        $src1 = new MediaFile($this->getId(), $prev);
                        $src2 = null;
                    }
                } else {
                    $src1 = null;
                    $src2 = new MediaFile($this->getId());
                }
                return $this->createDiffTable($src1, $src2);

            case 'abstract':
            case 'html':
            default:
                $src = new Display(new MediaFile($this->getId()));
                return $this->cleanHTML($src->getPreviewHtml(500, 500));
        }
    }

    /**
     * @inheritdoc
     * @todo read exif keywords
     */
    public function getCategory()
    {
        return (array)getNS($this->getId());
    }

    /**
     * Get the revision timestamp of this page
     *
     * Note: we only handle most current revisions in feeds, so the revision is usually just the
     * lastmodifed timestamp of the page file. However, if the page does not exist, we need to
     * determine the revision from the changelog.
     * @return int
     */
    public function getRev()
    {
        $rev = parent::getRev();
        if ($rev) return $rev;

        if (media_exists($this->id)) {
            $this->data['rev'] = filemtime(mediaFN($this->id));
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
            $this->data['exists'] = media_exists($this->id);
        }
        return $this->data['exists'];
    }

    /**
     * Load the current and previous revision from the changelog
     * @return void
     */
    protected function loadRevisions()
    {
        $changelog = new MediaChangeLog($this->id);
        $revs = $changelog->getRevisions(-1, 2);
        if (!isset($this->data['rev'])) {
            // prefer an already set date, only set if missing
            // it should usally not happen that neither is available
            $this->data['rev'] = $revs[0] ?? 0;
        }
        // a previous revision might not exist
        $this->data['prev'] = $revs[1] ?? null;
    }

    /**
     * Create a table showing the two media files
     *
     * @param MediaFile|null $src1
     * @param MediaFile|null $src2
     * @return string
     */
    protected function createDiffTable($src1, $src2)
    {
        global $lang;

        $content = '<table>';
        $content .= '<tr>';
        $content .= '<th width="50%">' . ($src1 ? $src1->getRev() : '') . '</th>';
        $content .= '<th width="50%">' . $lang['current'] . '</th>';
        $content .= '</tr>';
        $content .= '<tr>';

        $content .= '<td align="center">';
        if ($src1) {
            $display = new Display($src1);
            $display->getPreviewHtml(300, 300);
        }
        $content .= '</td>';

        $content .= '<td align="center">';
        if ($src2) {
            $display = new Display($src2);
            $display->getPreviewHtml(300, 300);
        }
        $content .= '</td>';

        $content .= '</tr>';
        $content .= '</table>';

        return $this->cleanHTML($content);
    }
}
