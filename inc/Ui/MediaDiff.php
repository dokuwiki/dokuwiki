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
    /* @var string */
    protected $id;

    /**
     * MediaDiff Ui constructor
     *
     * @param string $id  media id
     */
    public function __construct($id)
    {
        $this->id = $id;

        $this->preference['fromAjax'] = false; // see doluwiki\Ajax::callMediadiff()
        $this->preference['showIntro'] = false;
        $this->preference['difftype'] = null;  // both, opacity or portions. see lib/scripts/media.js
    }

    /**
     * Shows difference between two revisions of media
     */
    public function show()
    {
        $ns = getNS($this->id);
        $auth = auth_quickaclcheck("$ns:*");
        $this->media_diff($this->id, $ns, $auth, $this->preference['fromAjax']);
    }

    /**
     * Shows difference between two revisions of file
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     *
     * @param string $image  image id
     * @param string $ns
     * @param int $auth permission level
     * @param bool $fromajax
     * @return false|null|string
     */
    protected function media_diff($image, $ns, $auth, $fromajax = false)
    {
        global $conf;
        global $INPUT;

        if ($auth < AUTH_READ || !$image || !$conf['mediarevisions']) return '';

        $rev1 = $INPUT->int('rev');

        $rev2 = $INPUT->ref('rev2');
        if (is_array($rev2)) {
            $rev1 = (int) $rev2[0];
            $rev2 = (int) $rev2[1];

            if (!$rev1) {
                $rev1 = $rev2;
                unset($rev2);
            }
        } else {
            $rev2 = $INPUT->int('rev2');
        }

        if ($rev1 && !file_exists(mediaFN($image, $rev1))) $rev1 = false;
        if ($rev2 && !file_exists(mediaFN($image, $rev2))) $rev2 = false;

        if ($rev1 && $rev2) {  // two specific revisions wanted
            // make sure order is correct (older on the left)
            if ($rev1 < $rev2) {
                $l_rev = $rev1;
                $r_rev = $rev2;
            } else {
                $l_rev = $rev2;
                $r_rev = $rev1;
            }
        } elseif ($rev1) {     // single revision given, compare to current
            $r_rev = '';
            $l_rev = $rev1;
        } else {               // no revision was given, compare previous to current
            $r_rev = '';
            $medialog = new MediaChangeLog($image);
            $revs = $medialog->getRevisions(0, 1);
            if (file_exists(mediaFN($image, $revs[0]))) {
                $l_rev = $revs[0];
            } else {
                $l_rev = '';
            }
        }

        // prepare event data
        $data = array();
        $data[0] = $image;
        $data[1] = $l_rev;
        $data[2] = $r_rev;
        $data[3] = $ns;
        $data[4] = $auth;
        $data[5] = $fromajax;

        // trigger event
        return Event::createAndTrigger('MEDIA_DIFF', $data, [$this,'_media_file_diff'], true);
    }

    /**
     * Callback for media file diff
     *
     * @param array $data event data
     * @return false|null
     */
    public function _media_file_diff($data)
    {
        if (is_array($data) && count($data) === 6) {
            $this->media_file_diff($data[0], $data[1], $data[2], $data[3], $data[4], $data[5]);
        } else {
            return false;
        }
    }

    /**
     * Shows difference between two revisions of image
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     *
     * @param string $image
     * @param string|int $l_rev revision timestamp, or empty string
     * @param string|int $r_rev revision timestamp, or empty string
     * @param string $ns
     * @param int $auth permission level
     * @param bool $fromajax
     */
    protected function media_file_diff($image, $l_rev, $r_rev, $ns, $auth, $fromajax)
    {
        global $lang;
        global $INPUT;

        $l_meta = new \JpegMeta(mediaFN($image, $l_rev));
        $r_meta = new \JpegMeta(mediaFN($image, $r_rev));

        $is_img = preg_match('/\.(jpe?g|gif|png)$/', $image);
        if ($is_img) {
            $l_size = media_image_preview_size($image, $l_rev, $l_meta);
            $r_size = media_image_preview_size($image, $r_rev, $r_meta);
            $is_img = ($l_size && $r_size && ($l_size[0] >= 30 || $r_size[0] >= 30));

            $difftype = $INPUT->str('difftype');

            if (!$fromajax) {
                $form = new Form([
                    'id' => 'mediamanager__form_diffview',
                    'action' => media_managerURL([], '&'),
                    'method' => 'get',
                    'class' => 'diffView',
                ]);
                $form->addTagOpen('div')->addClass('no');
                $form->setHiddenField('sectok', null);
                $form->setHiddenField('mediado', 'diff');
                $form->setHiddenField('rev2[0]', $l_rev);
                $form->setHiddenField('rev2[1]', $r_rev);
                $form->addTagClose('div');
                echo $form->toHTML();

                echo NL.'<div id="mediamanager__diff" >'.NL;
            }

            if ($difftype == 'opacity' || $difftype == 'portions') {
                $this->media_image_diff($image, $l_rev, $r_rev, $l_size, $r_size, $difftype);
                if (!$fromajax) echo '</div>';
                return;
            }
        }

        $medialog = new MediaChangeLog($image);

        list($l_head, $r_head) = $this->diffHead($medialog, $l_rev, $r_rev);

        echo '<div class="table">';
        echo '<table>';
        echo '<tr>';
        echo '<th>'. $l_head .'</th>';
        echo '<th>'. $r_head .'</th>';
        echo '</tr>'.NL;

        echo '<tr class="image">';
        echo '<td>';
        media_preview($image, $auth, $l_rev, $l_meta);
        echo '</td>';

        echo '<td>';
        media_preview($image, $auth, $r_rev, $r_meta);
        echo '</td>';
        echo '</tr>'.NL;

        echo '<tr class="actions">';
        echo '<td>';
        media_preview_buttons($image, $auth, $l_rev);
        echo '</td>';

        echo '<td>';
        media_preview_buttons($image, $auth, $r_rev);
        echo '</td>';
        echo '</tr>'.NL;

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
            echo '<td>'.NL;

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
            echo '</dl>'.NL;

            echo '</td>';
        }
        echo '</tr>'.NL;

        echo '</table>'.NL;
        echo '</div>'.NL;

        if ($is_img && !$fromajax) echo '</div>';
    }

    /**
     * Prints two images side by side
     * and slider
     *
     * @author Kate Arzamastseva <pshns@ukr.net>
     *
     * @param string $image   image id
     * @param int    $l_rev   revision timestamp, or empty string
     * @param int    $r_rev   revision timestamp, or empty string
     * @param array  $l_size  array with width and height
     * @param array  $r_size  array with width and height
     * @param string $type
     */
    protected function media_image_diff($image, $l_rev, $r_rev, $l_size, $r_size, $type)
    {
        if ($l_size != $r_size) {
            if ($r_size[0] > $l_size[0]) {
                $l_size = $r_size;
            }
        }

        $l_more = array('rev' => $l_rev, 'h' => $l_size[1], 'w' => $l_size[0]);
        $r_more = array('rev' => $r_rev, 'h' => $l_size[1], 'w' => $l_size[0]);

        $l_src = ml($image, $l_more);
        $r_src = ml($image, $r_more);

        // slider
        echo '<div class="slider" style="max-width: '.($l_size[0]-20).'px;" ></div>'.NL;

        // two images in divs
        echo '<div class="imageDiff '.$type.'">'.NL;
        echo '<div class="image1" style="max-width: '.$l_size[0].'px;">';
        echo '<img src="'.$l_src.'" alt="" />';
        echo '</div>'.NL;
        echo '<div class="image2" style="max-width: '.$l_size[0].'px;">';
        echo '<img src="'.$r_src.'" alt="" />';
        echo '</div>'.NL;
        echo '</div>'.NL;
    }

}
