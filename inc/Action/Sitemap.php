<?php

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\FatalException;

/**
 * Class Sitemap
 *
 * Generate an XML sitemap for search engines. Do not confuse with Index
 *
 * @package dokuwiki\Action
 */
class Sitemap extends AbstractAction {

    /** @inheritdoc */
    public function minimumPermission() {
        return AUTH_NONE;
    }

    /**
     * Handle sitemap delivery
     *
     * @author Michael Hamann <michael@content-space.de>
     * @throws FatalException
     * @inheritdoc
     */
    public function preProcess() {
        global $conf;

        if($conf['sitemap'] < 1 || !is_numeric($conf['sitemap'])) {
            throw new FatalException(404, 'Sitemap generation is disabled');
        }

        $sitemap = \Sitemapper::getFilePath();
        if(\Sitemapper::sitemapIsCompressed()) {
            $mime = 'application/x-gzip';
        } else {
            $mime = 'application/xml; charset=utf-8';
        }

        // Check if sitemap file exists, otherwise create it
        if(!is_readable($sitemap)) {
            \Sitemapper::generate();
        }

        if(is_readable($sitemap)) {
            // Send headers
            header('Content-Type: ' . $mime);
            header('Content-Disposition: attachment; filename=' . utf8_basename($sitemap));

            http_conditionalRequest(filemtime($sitemap));

            // Send file
            //use x-sendfile header to pass the delivery to compatible webservers
            http_sendfile($sitemap);

            readfile($sitemap);
            exit;
        }

        throw new FatalException(500, 'Could not read the sitemap file - bad permissions?');
    }

}
