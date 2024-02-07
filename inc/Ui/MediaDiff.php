<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\MediaChangeLog;
use dokuwiki\ChangeLog\RevisionInfo;
use dokuwiki\Form\Form;
use InvalidArgumentException;
use JpegMeta;

/**
 * DokuWiki MediaDiff Interface
 *
 * @package dokuwiki\Ui
 */
class MediaDiff extends Diff
{
    /* @var MediaChangeLog */
    protected $changelog;

    /* @var RevisionInfo older revision */
    protected $RevInfo1;
    /* @var RevisionInfo newer revision */
    protected $RevInfo2;

    /* @var bool */
    protected $is_img;

    /**
     * MediaDiff Ui constructor
     *
     * @param string $id media id
     */
    public function __construct($id)
    {
        if (!isset($id)) {
            throw new InvalidArgumentException('media id should not be empty!');
        }

        // init preference
        $this->preference['fromAjax'] = false;  // see dokuwiki\Ajax::callMediadiff()
        $this->preference['showIntro'] = false;
        $this->preference['difftype'] = 'both'; // diff view type: both, opacity or portions

        parent::__construct($id);
    }

    /** @inheritdoc */
    protected function setChangeLog()
    {
        $this->changelog = new MediaChangeLog($this->id);
    }

    /**
     * Handle requested revision(s) and diff view preferences
     *
     * @return void
     */
    protected function handle()
    {
        global $INPUT;

        // retrieve requested rev or rev2
        parent::handle();

        // requested diff view type
        if ($INPUT->has('difftype')) {
            $this->preference['difftype'] = $INPUT->str('difftype');
        }
    }

    /**
     * Prepare revision info of comparison pair
     */
    protected function preProcess()
    {
        $changelog =& $this->changelog;

        // create revision info object for older and newer sides
        // RevInfo1 : older, left side
        // RevInfo2 : newer, right side

        $changelogRev1 = $changelog->getRevisionInfo($this->rev1);
        $changelogRev2 = $changelog->getRevisionInfo($this->rev2);

        $this->RevInfo1 = new RevisionInfo($changelogRev1);
        $this->RevInfo2 = new RevisionInfo($changelogRev2);

        $this->is_img = preg_match('/\.(jpe?g|gif|png)$/', $this->id);

        foreach ([$this->RevInfo1, $this->RevInfo2] as $RevInfo) {
            $isCurrent = $changelog->isCurrentRevision($RevInfo->val('date'));
            $RevInfo->isCurrent($isCurrent);

            if ($this->is_img) {
                $rev = $isCurrent ? '' : $RevInfo->val('date');
                $meta = new JpegMeta(mediaFN($this->id, $rev));
                // get image width and height for the media manager preview panel
                $RevInfo->append([
                    'previewSize' => media_image_preview_size($this->id, $rev, $meta)
                ]);
            }
        }

        // re-check image, ensure minimum image width for showImageDiff()
        $this->is_img = ($this->is_img
            && ($this->RevInfo1->val('previewSize')[0] ?? 0) >= 30
            && ($this->RevInfo2->val('previewSize')[0] ?? 0) >= 30
        );
        // adjust requested diff view type
        if (!$this->is_img) {
            $this->preference['difftype'] = 'both';
        }
    }


    /**
     * Shows difference between two revisions of media
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     */
    public function show()
    {
        global $conf;

        $ns = getNS($this->id);
        $auth = auth_quickaclcheck("$ns:*");

        if ($auth < AUTH_READ || !$this->id || !$conf['mediarevisions']) return;

        // retrieve form parameters: rev, rev2, difftype
        $this->handle();
        // prepare revision info of comparison pair
        $this->preProcess();

        // display intro
        if ($this->preference['showIntro']) echo p_locale_xhtml('diff');

        // print form to choose diff view type
        if ($this->is_img && !$this->preference['fromAjax']) {
            $this->showDiffViewSelector();
            echo '<div id="mediamanager__diff" >';
        }

        switch ($this->preference['difftype']) {
            case 'opacity':
            case 'portions':
                $this->showImageDiff();
                break;
            case 'both':
            default:
                $this->showFileDiff();
                break;
        }

        if ($this->is_img && !$this->preference['fromAjax']) {
            echo '</div>';
        }
    }

    /**
     * Print form to choose diff view type
     * the dropdown is to be added through JavaScript, see lib/scripts/media.js
     */
    protected function showDiffViewSelector()
    {
        // use timestamp for current revision, date may be false when revisions < 2
        [$rev1, $rev2] = [(int)$this->RevInfo1->val('date'), (int)$this->RevInfo2->val('date')];

        echo '<div class="diffoptions group">';

        $form = new Form([
            'id' => 'mediamanager__form_diffview',
            'action' => media_managerURL([], '&'),
            'method' => 'get',
            'class' => 'diffView',
        ]);
        $form->addTagOpen('div')->addClass('no');
        $form->setHiddenField('sectok', null);
        $form->setHiddenField('mediado', 'diff');
        $form->setHiddenField('rev2[0]', $rev1);
        $form->setHiddenField('rev2[1]', $rev2);
        $form->addTagClose('div');
        echo $form->toHTML();

        echo '</div>'; // .diffoptions
    }

    /**
     * Prints two images side by side
     * and slider
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     */
    protected function showImageDiff()
    {
        $rev1 = $this->RevInfo1->isCurrent() ? '' : $this->RevInfo1->val('date');
        $rev2 = $this->RevInfo2->isCurrent() ? '' : $this->RevInfo2->val('date');

        // diff view type: opacity or portions
        $type = $this->preference['difftype'];

        // adjust image width, right side (newer) has priority
        $rev1Size = $this->RevInfo1->val('previewSize');
        $rev2Size = $this->RevInfo2->val('previewSize');
        if ($rev1Size != $rev2Size) {
            if ($rev2Size[0] > $rev1Size[0]) {
                $rev1Size = $rev2Size;
            }
        }

        $rev1Src = ml($this->id, ['rev' => $rev1, 'h' => $rev1Size[1], 'w' => $rev1Size[0]]);
        $rev2Src = ml($this->id, ['rev' => $rev2, 'h' => $rev1Size[1], 'w' => $rev1Size[0]]);

        // slider
        echo '<div class="slider" style="max-width: ' . ($rev1Size[0] - 20) . 'px;" ></div>';

        // two images in divs
        echo '<div class="imageDiff ' . $type . '">';
        echo '<div class="image1" style="max-width: ' . $rev1Size[0] . 'px;">';
        echo '<img src="' . $rev1Src . '" alt="" />';
        echo '</div>';
        echo '<div class="image2" style="max-width: ' . $rev1Size[0] . 'px;">';
        echo '<img src="' . $rev2Src . '" alt="" />';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Shows difference between two revisions of media file
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     */
    protected function showFileDiff()
    {
        global $lang;

        $ns = getNS($this->id);
        $auth = auth_quickaclcheck("$ns:*");

        $rev1 = $this->RevInfo1->isCurrent() ? '' : (int)$this->RevInfo1->val('date');
        $rev2 = $this->RevInfo2->isCurrent() ? '' : (int)$this->RevInfo2->val('date');

        // revision title
        $rev1Title = trim($this->RevInfo1->showRevisionTitle() . ' ' . $this->RevInfo1->showCurrentIndicator());
        $rev1Summary = ($this->RevInfo1->val('date'))
            ? $this->RevInfo1->showEditSummary() . ' ' . $this->RevInfo1->showEditor()
            : '';
        $rev2Title = trim($this->RevInfo2->showRevisionTitle() . ' ' . $this->RevInfo2->showCurrentIndicator());
        $rev2Summary = ($this->RevInfo2->val('date'))
            ? $this->RevInfo2->showEditSummary() . ' ' . $this->RevInfo2->showEditor()
            : '';

        $rev1Meta = new JpegMeta(mediaFN($this->id, $rev1));
        $rev2Meta = new JpegMeta(mediaFN($this->id, $rev2));

        // display diff view table
        echo '<div class="table">';
        echo '<table>';
        echo '<tr>';
        echo '<th>' . $rev1Title . ' ' . $rev1Summary . '</th>';
        echo '<th>' . $rev2Title . ' ' . $rev2Summary . '</th>';
        echo '</tr>';

        echo '<tr class="image">';
        echo '<td>';
        media_preview($this->id, $auth, $rev1, $rev1Meta); // $auth not used in media_preview()?
        echo '</td>';

        echo '<td>';
        media_preview($this->id, $auth, $rev2, $rev2Meta);
        echo '</td>';
        echo '</tr>';

        echo '<tr class="actions">';
        echo '<td>';
        media_preview_buttons($this->id, $auth, $rev1); // $auth used in media_preview_buttons()
        echo '</td>';

        echo '<td>';
        media_preview_buttons($this->id, $auth, $rev2);
        echo '</td>';
        echo '</tr>';

        $rev1Tags = media_file_tags($rev1Meta);
        $rev2Tags = media_file_tags($rev2Meta);
        // FIXME rev2Tags-only stuff ignored
        foreach ($rev1Tags as $key => $tag) {
            if ($tag['value'] != $rev2Tags[$key]['value']) {
                $rev2Tags[$key]['highlighted'] = true;
                $rev1Tags[$key]['highlighted'] = true;
            } elseif (!$tag['value'] || !$rev2Tags[$key]['value']) {
                unset($rev2Tags[$key]);
                unset($rev1Tags[$key]);
            }
        }

        echo '<tr>';
        foreach ([$rev1Tags, $rev2Tags] as $tags) {
            echo '<td>';

            echo '<dl class="img_tags">';
            foreach ($tags as $tag) {
                $value = cleanText($tag['value']);
                if (!$value) $value = '-';
                echo '<dt>' . $lang[$tag['tag'][1]] . '</dt>';
                echo '<dd>';
                if (!empty($tag['highlighted'])) echo '<strong>';
                if ($tag['tag'][2] == 'date') {
                    echo dformat($value);
                } else {
                    echo hsc($value);
                }
                if (!empty($tag['highlighted'])) echo '</strong>';
                echo '</dd>';
            }
            echo '</dl>';

            echo '</td>';
        }
        echo '</tr>';

        echo '</table>';
        echo '</div>';
    }
}
