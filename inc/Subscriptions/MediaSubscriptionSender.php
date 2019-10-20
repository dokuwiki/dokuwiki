<?php


namespace dokuwiki\Subscriptions;


class MediaSubscriptionSender extends SubscriptionSender
{

    /**
     * Send the diff for some media change
     *
     * @fixme this should embed thumbnails of images in HTML version
     *
     * @param string   $subscriber_mail The target mail address
     * @param string   $template        Mail template ('uploadmail', ...)
     * @param string   $id              Media file for which the notification is
     * @param int|bool $rev             Old revision if any
     * @param int|bool $current_rev     New revision if any
     */
    public function sendMediaDiff($subscriber_mail, $template, $id, $rev = false, $current_rev = false)
    {
        global $conf;

        $file = mediaFN($id);
        list($mime, /* $ext */) = mimetype($id);

        $trep = [
            'MIME' => $mime,
            'MEDIA' => ml($id, $current_rev?('rev='.$current_rev):'', true, '&', true),
            'SIZE' => filesize_h(filesize($file)),
        ];

        if ($rev && $conf['mediarevisions']) {
            $trep['OLD'] = ml($id, "rev=$rev", true, '&', true);
        } else {
            $trep['OLD'] = '---';
        }

        $headers = ['Message-Id' => $this->getMessageID($id, @filemtime($file))];
        if ($rev) {
            $headers['In-Reply-To'] = $this->getMessageID($id, $rev);
        }

        $this->send($subscriber_mail, 'upload', $id, $template, $trep, null, $headers);
    }
}
