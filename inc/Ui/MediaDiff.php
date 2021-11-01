<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\MediaChangeLog;
use dokuwiki\Ui\MediaRevisions;
use dokuwiki\Extension\Event;
use dokuwiki\Form\Form;
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

    /* @var array */
    protected $oldRevInfo;
    protected $newRevInfo;
 
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
            throw new \InvalidArgumentException('media id should not be empty!');
        }

        // init preference
        $this->preference['fromAjax'] = false;  // see doluwiki\Ajax::callMediadiff()
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

        // revision info of older file (left side)
        $this->oldRevInfo = $changelog->getRevisionInfo($this->oldRev);
        // revision info of newer file (right side)
        $this->newRevInfo = $changelog->getRevisionInfo($this->newRev);

        $this->is_img = preg_match('/\.(jpe?g|gif|png)$/', $this->id);

        foreach ([&$this->oldRevInfo, &$this->newRevInfo] as &$revInfo) {
            // use timestamp and '' properly as $rev for the current file
            $isCurrent = $changelog->isCurrentRevision($revInfo['date']);
            $revInfo += [
                'current' => $isCurrent,
                'rev'     => $isCurrent ? '' : $revInfo['date'],
            ];

            // headline in the Diff view navigation
            $revInfo['navTitle'] = $this->revisionTitle($revInfo);

            if ($this->is_img) {
                $rev = $revInfo['rev'];
                $meta = new JpegMeta(mediaFN($this->id, $rev));
                // get image width and height for the mediamanager preview panel
                $revInfo['previewSize'] = media_image_preview_size($this->id, $rev, $meta);
            }
        }
        unset($revInfo);

        // re-check image, ensure minimum image width for showImageDiff()
        $this->is_img = ($this->is_img
            && ($this->oldRevInfo['previewSize'][0] ?? 0) >= 30
            && ($this->newRevInfo['previewSize'][0] ?? 0) >= 30
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

        if ($auth < AUTH_READ || !$this->id || !$conf['mediarevisions']) return '';

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
        // use timestamp for current revision
        [$oldRev, $newRev] = [(int)$this->oldRevInfo['date'], (int)$this->newRevInfo['date']];

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
        $form->setHiddenField('rev2[0]', $oldRev);
        $form->setHiddenField('rev2[1]', $newRev);
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
        // diff view type: opacity or portions
        $type = $this->preference['difftype']; 

        // use '' for current revision
        [$oldRev, $newRev] = [$this->oldRevInfo['rev'], $this->newRevInfo['rev']];

        // adjust image width, right side (newer) has priority
        $oldRevSize = $this->oldRevInfo['previewSize'];
        $newRevSize = $this->newRevInfo['previewSize'];
        if ($oldRevSize != $newRevSize) {
            if ($newRevSize[0] > $oldRevSize[0]) {
                $oldRevSize = $newRevSize;
            }
        }

        $oldRevSrc = ml($this->id, ['rev' => $oldRev, 'h' => $oldRevSize[1], 'w' => $oldRevSize[0]]);
        $newRevSrc = ml($this->id, ['rev' => $newRev, 'h' => $oldRevSize[1], 'w' => $oldRevSize[0]]);

        // slider
        echo '<div class="slider" style="max-width: '.($oldRevSize[0]-20).'px;" ></div>';

        // two images in divs
        echo '<div class="imageDiff '.$type.'">';
        echo '<div class="image1" style="max-width: '.$oldRevSize[0].'px;">';
        echo '<img src="'.$oldRevSrc.'" alt="" />';
        echo '</div>';
        echo '<div class="image2" style="max-width: '.$oldRevSize[0].'px;">';
        echo '<img src="'.$newRevSrc.'" alt="" />';
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
        $changelog =& $this->changelog;

        $ns = getNS($this->id);
        $auth = auth_quickaclcheck("$ns:*");

        // use '' for current revision
        [$oldRev, $newRev] = [$this->oldRevInfo['rev'], $this->newRevInfo['rev']];

        $oldRevMeta = new JpegMeta(mediaFN($this->id, $oldRev));
        $newRevMeta = new JpegMeta(mediaFN($this->id, $newRev));

        // display diff view table
        echo '<div class="table">';
        echo '<table>';
        echo '<tr>';
        echo '<th>'. $this->oldRevInfo['navTitle'] .'</th>';
        echo '<th>'. $this->newRevInfo['navTitle'] .'</th>';
        echo '</tr>';

        echo '<tr class="image">';
        echo '<td>';
        media_preview($this->id, $auth, $oldRev, $oldRevMeta); // $auth not used in media_preview()?
        echo '</td>';

        echo '<td>';
        media_preview($this->id, $auth, $newRev, $newRevMeta);
        echo '</td>';
        echo '</tr>';

        echo '<tr class="actions">';
        echo '<td>';
        media_preview_buttons($this->id, $auth, $oldRev); // $auth used in media_preview_buttons()
        echo '</td>';

        echo '<td>';
        media_preview_buttons($this->id, $auth, $newRev);
        echo '</td>';
        echo '</tr>';

        $l_tags = media_file_tags($oldRevMeta);
        $r_tags = media_file_tags($newRevMeta);
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
        foreach (array($l_tags, $r_tags) as $tags) {
            echo '<td>';

            echo '<dl class="img_tags">';
            foreach ($tags as $tag) {
                $value = cleanText($tag['value']);
                if (!$value) $value = '-';
                echo '<dt>'.$lang[$tag['tag'][1]].'</dt>';
                echo '<dd>';
                if ($tag['highlighted']) echo '<strong>';
                if ($tag['tag'][2] == 'date') {
                    echo dformat($value);
                } else {
                    echo hsc($value);
                }
                if ($tag['highlighted']) echo '</strong>';
                echo '</dd>';
            }
            echo '</dl>';

            echo '</td>';
        }
        echo '</tr>';

        echo '</table>';
        echo '</div>';
    }

    /**
     * Revision Title for MediaDiff table headline
     *
     * @param array $info  Revision info structure of a media file
     * @return string
     */
    protected function revisionTitle(array $info)
    {
        global $lang, $INFO;

        if (isset($info['date'])) {
            $rev = $info['date'];
            $title = '<bdi><a class="wikilink1" href="'.ml($this->id, ['rev' => $rev]).'">'
                   . dformat($rev).'</a></bdi>';
        } else {
            $rev = false;
            $title = '&mdash;';
        }
        if (isset($info['current']) || ($rev && $rev == $INFO['currentrev'])) {
            $title .= '&nbsp;('.$lang['current'].')';
        }

        // append separator
        $title .= ($this->preference['difftype'] === 'inline') ? ' ' : '<br />';

        // supplement
        if (isset($info['date'])) {
            $objRevInfo = (new MediaRevisions($this->id))->getObjRevInfo($info);
            $title .= $objRevInfo->editSummary().' '.$objRevInfo->editor();
        }
        return $title;
    }

}
