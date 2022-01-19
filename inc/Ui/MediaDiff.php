<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\MediaChangeLog;
use dokuwiki\ChangeLog\RevisionInfo;
use dokuwiki\Form\Form;
use InvalidArgumentException;
use JpegMeta;

/**
 * DokuWiki MediaDiff
 *
 * compare two revisions of the media file
 *
 * @package dokuwiki\Ui
 */
class MediaDiff extends Diff
{
    /* @var MediaChangeLog */
    protected $changelog;

    /* @var RevisionInfo older revision */
    protected $Revision1;
    /* @var RevisionInfo newer revision */
    protected $Revision2;

    /* @var bool */
    protected $is_img;

    /**
     * MediaDiff Ui constructor
     *
     * @param string $id  media id
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

        // requested rev or rev2
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
        list($rev1, $rev2) = $this->revisions;

        // create revision info object for older and newer sides
        // Revision1 : older, left side
        // Revision2 : newer, right side
        $this->Revision1 = new RevisionInfo($changelog->getRevisionInfo($rev1));
        $this->Revision2 = new RevisionInfo($changelog->getRevisionInfo($rev2));

        $this->is_img = preg_match('/\.(jpe?g|gif|png)$/', $this->id);

        foreach ([$this->Revision1, $this->Revision2] as $Revision) {
            $isCurrent = $changelog->isCurrentRevision((int)$Revision->val('date'));
            $Revision->isCurrent($isCurrent);

            if ($this->is_img) {
                $rev = $isCurrent ? '' : $Revision->val('date');
                $meta = new JpegMeta(mediaFN($this->id, $rev));
                // get image width and height for the mediamanager preview panel
                $Revision->append([
                    'previewSize' => media_image_preview_size($this->id, $rev, $meta)
                ]);
            }
        }

        // re-check image, ensure minimum image width for showImageDiff()
        $this->is_img = ($this->is_img
            && ($this->Revision1->val('previewSize')[0] ?? 0) >= 30
            && ($this->Revision2->val('previewSize')[0] ?? 0) >= 30
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
        // revision information object
        [$Revision1, $Revision2] = [$this->Revision1, $this->Revision2];

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
        $form->setHiddenField('rev2[0]', (int)$Revision1->val('date'));
        $form->setHiddenField('rev2[1]', (int)$Revision2->val('date'));
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
        // revision information object
        [$Revision1, $Revision2] = [$this->Revision1, $this->Revision2];

        $rev1 = $Revision1->isCurrent() ? '' : $Revision1->val('date');
        $rev2 = $Revision2->isCurrent() ? '' : $Revision2->val('date');

        // diff view type: opacity or portions
        $type = $this->preference['difftype'];

        // adjust image width, right side (newer) has priority
        $rev1Size = $Revision1->val('previewSize');
        $rev2Size = $Revision2->val('previewSize');
        if ($rev1Size != $rev2Size) {
            if ($rev2Size[0] > $rev1Size[0]) {
                $rev1Size = $rev2Size;
            }
        }

        $rev1Src = ml($this->id, ['rev' => $rev1, 'h' => $rev1Size[1], 'w' => $rev1Size[0]]);
        $rev2Src = ml($this->id, ['rev' => $rev2, 'h' => $rev1Size[1], 'w' => $rev1Size[0]]);

        // slider
        echo '<div class="slider" style="max-width: '.($rev1Size[0]-20).'px;" ></div>';

        // two images in divs
        echo '<div class="imageDiff '.$type.'">';
        echo '<div class="image1" style="max-width: '.$rev1Size[0].'px;">';
        echo '<img src="'.$rev1Src.'" alt="" />';
        echo '</div>';
        echo '<div class="image2" style="max-width: '.$rev1Size[0].'px;">';
        echo '<img src="'.$rev2Src.'" alt="" />';
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

        // revision information object
        [$Revision1, $Revision2] = [$this->Revision1, $this->Revision2];

        $rev1 = $Revision1->isCurrent() ? '' : (int)$Revision1->val('date');
        $rev2 = $Revision2->isCurrent() ? '' : (int)$Revision2->val('date');

        // revision title
        $rev1Title = trim($Revision1->showRevisionTitle() .' '. $Revision1->showCurrentIndicator());
        $rev1Supple = ($Revision1->val('date'))
            ? $Revision1->showEditSummary() .' '. $Revision1->showEditor()
            : '';
        $rev2Title = trim($Revision2->showRevisionTitle() .' '. $Revision2->showCurrentIndicator());
        $rev2Supple = ($Revision2->val('date'))
            ? $Revision2->showEditSummary() .' '. $Revision2->showEditor()
            : '';

        $rev1Meta = new JpegMeta(mediaFN($this->id, $rev1));
        $rev2Meta = new JpegMeta(mediaFN($this->id, $rev2));

        // display diff view table
        echo '<div class="table">';
        echo '<table>';
        echo '<tr>';
        echo '<th>'. $rev1Title .' '. $rev1Supple .'</th>';
        echo '<th>'. $rev2Title .' '. $rev2Supple .'</th>';
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

        $l_tags = media_file_tags($rev1Meta);
        $r_tags = media_file_tags($rev2Meta);
        // FIXME r_tags-only stuff
        foreach ($l_tags as $key => $l_tag) {
            if ($l_tag['value'] != $r_tags[$key]['value']) {
                $r_tags[$key]['highlighted'] = true;
                $l_tags[$key]['highlighted'] = true;
            } elseif (!$l_tag['value'] || !$r_tags[$key]['value']) {
                unset($r_tags[$key]);
                unset($l_tags[$key]);
            }
        }

        echo '<tr>';
        foreach ([$l_tags, $r_tags] as $tags) {
            echo '<td>';

            echo '<dl class="img_tags">';
            foreach ($tags as $tag) {
                $value = cleanText($tag['value']);
                if (!$value) $value = '-';
                echo '<dt>'.$lang[$tag['tag'][1]].'</dt>';
                echo '<dd>';
                if (isset($tag['highlighted']) && $tag['highlighted']) echo '<strong>';
                if ($tag['tag'][2] == 'date') {
                    echo dformat($value);
                } else {
                    echo hsc($value);
                }
                if (isset($tag['highlighted']) && $tag['highlighted']) echo '</strong>';
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
