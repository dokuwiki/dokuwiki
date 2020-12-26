<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\MediaChangeLog;
use dokuwiki\Extension\Event;
use dokuwiki\Form\Form;

/**
 * DokuWiki MediaDiff Interface
 *
 * @package dokuwiki\Ui
 */
class MediaDiff extends Diff
{
    /**
     * MediaDiff Ui constructor
     *
     * @param string $id  media id
     */
    public function __construct($id)
    {
        $this->id = $id;

        // init preference
        $this->preference['fromAjax'] = false; // see doluwiki\Ajax::callMediadiff()
        $this->preference['showIntro'] = false;
        $this->preference['difftype'] = 'both';  // media diff view type: both, opacity or portions

        $this->setChangeLog();
    }

    /** @inheritdoc */
    protected function setChangeLog()
    {
        $this->changelog = new MediaChangeLog($this->id);
    }

    /** @inheritdoc */
    protected function preProcess()
    {
        parent::preProcess();
        if (!isset($this->old_rev, $this->new_rev)) {
            // no revision was given, compare previous to current
            $revs = $this->changelog->getRevisions(0, 1);
            $this->old_rev = file_exists(mediaFN($this->id, $revs[0])) ? $revs[0] : '';
            $this->new_rev = '';
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

       // determine left and right revision
        if (!isset($this->old_rev, $this->new_rev)) $this->preProcess();
        [$l_rev, $r_rev] = [$this->old_rev, $this->new_rev];

        // prepare event data
        // NOTE: MEDIA_DIFF event does not found in DokuWiki Event List?
        $data = array();
        $data[0] = $this->id;
        $data[1] = $l_rev;
        $data[2] = $r_rev;
        $data[3] = $ns;
        $data[4] = $auth; // permission level
        $data[5] = $this->preference['fromAjax'];

        // trigger event
        Event::createAndTrigger('MEDIA_DIFF', $data, null, false);

        if (is_array($data) && count($data) === 6) {
            $this->id = $data[0];
            $l_rev = $data[1];
            $r_rev = $data[2];
            $ns    = $data[3];
            $auth  = $data[4];
            $this->preference['fromAjax'] = $data[5];
        } else {
            return '';
        }

        $l_meta = new \JpegMeta(mediaFN($this->id, $l_rev));
        $r_meta = new \JpegMeta(mediaFN($this->id, $r_rev));

        $is_img = preg_match('/\.(jpe?g|gif|png)$/', $this->id);
        if ($is_img) {
            // get image width and height for the mediamanager preview panel
            $l_size = media_image_preview_size($this->id, $l_rev, $l_meta);
            $r_size = media_image_preview_size($this->id, $r_rev, $r_meta);
            // re-check image, ensure minimum image width for showImageDiff()
            $is_img = ($l_size && $r_size && ($l_size[0] >= 30 || $r_size[0] >= 30));
        }

        // determine requested diff view type
        if (!$is_img) {
            $this->preference['difftype'] = 'both';
        }

        // display intro
        if ($this->preference['showIntro']) echo p_locale_xhtml('diff');

        // print form to choose diff view type
        if ($is_img && !$this->preference['fromAjax']) {
            $this->showDiffViewSelector();
            echo '<div id="mediamanager__diff" >';
        }

        switch ($this->preference['difftype']) {
            case 'opacity':
            case 'portions':
                $this->showImageDiff($l_rev, $r_rev, $l_size, $r_size, $difftype);
                break;
            case 'both':
            default:
                $this->showFileDiff($l_rev, $r_rev, $l_meta, $r_meta, $auth);
                break;
        }

        if ($is_img && !$this->preference['fromAjax']) {
            echo '</div>';
        }
    }

    /**
     * Print form to choose diff view type
     * the dropdown is to be added through JavaScript, see lib/scripts/media.js
     */
    protected function showDiffViewSelector()
    {
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
        $form->setHiddenField('rev2[0]', $this->old_rev ?: 'current');
        $form->setHiddenField('rev2[1]', $this->new_rev ?: 'current');
        $form->addTagClose('div');
        echo $form->toHTML();

        echo '</div>'; // .diffoptions
    }

    /**
     * Prints two images side by side
     * and slider
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     *
     * @param string|int $l_rev revision timestamp, or empty string
     * @param string|int $r_rev revision timestamp, or empty string
     * @param array  $l_size  array with width and height
     * @param array  $r_size  array with width and height
     * @param string $type    diff view type: opacity or portions
     */
    protected function showImageDiff($l_rev, $r_rev, $l_size, $r_size, $type = null)
    {
        if (!isset($type)) {
            $type = $this->preference['difftype'];
        }

        // adjust image width, right side (newer) has priority
        if ($l_size != $r_size) {
            if ($r_size[0] > $l_size[0]) {
                $l_size = $r_size;
            }
        }

        $l_src = ml($this->id, ['rev' => $l_rev, 'h' => $l_size[1], 'w' => $l_size[0]]);
        $r_src = ml($this->id, ['rev' => $r_rev, 'h' => $l_size[1], 'w' => $l_size[0]]);

        // slider
        echo '<div class="slider" style="max-width: '.($l_size[0]-20).'px;" ></div>';

        // two images in divs
        echo '<div class="imageDiff '.$type.'">';
        echo '<div class="image1" style="max-width: '.$l_size[0].'px;">';
        echo '<img src="'.$l_src.'" alt="" />';
        echo '</div>';
        echo '<div class="image2" style="max-width: '.$l_size[0].'px;">';
        echo '<img src="'.$r_src.'" alt="" />';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Shows difference between two revisions of media file
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     *
     * @param string|int $l_rev revision timestamp, or empty string
     * @param string|int $r_rev revision timestamp, or empty string
     * @param JpegMeta $l_meta
     * @param JpegMeta $r_meta
     * @param int $auth permission level
     */
    protected function showFileDiff($l_rev, $r_rev, $l_meta, $r_meta, $auth)
    {
        list($l_head, $r_head) = $this->buildDiffHead($l_rev, $r_rev);

        echo '<div class="table">';
        echo '<table>';
        echo '<tr>';
        echo '<th>'. $l_head .'</th>';
        echo '<th>'. $r_head .'</th>';
        echo '</tr>';

        echo '<tr class="image">';
        echo '<td>';
        media_preview($this->id, $auth, $l_rev, $l_meta); // $auth not used in media_preview()?
        echo '</td>';

        echo '<td>';
        media_preview($this->id, $auth, $r_rev, $r_meta);
        echo '</td>';
        echo '</tr>';

        echo '<tr class="actions">';
        echo '<td>';
        media_preview_buttons($this->id, $auth, $l_rev); // $auth used in media_preview_buttons()
        echo '</td>';

        echo '<td>';
        media_preview_buttons($this->id, $auth, $r_rev);
        echo '</td>';
        echo '</tr>';

        $l_tags = media_file_tags($l_meta);
        $r_tags = media_file_tags($r_meta);
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

}
