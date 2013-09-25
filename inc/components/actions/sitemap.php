<?php

/**
 * Handler for action sitemap
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Sitemap extends Doku_Action
{
    /**
     * The Doku_Action interface to specify the action name that this
     * handler can handle.
     * 
     * @return string the action name
     */
    public function action() { return "sitemap"; }

    /**
     * The Doku_Action interface to specify the required permissions
     * for action sitemap.
     * 
     * @return string the permission required
     */
    public function permission_required() {
        return AUTH_NONE;
    }

    /**
     * Doku_Action to handle the sitemap action
     * Was act_sitemap() by
     * @author Michael Hamann <michael@content-space.de>
     * 
     * @global array $conf
     */
    public function handle() {
        global $conf;

        if ($conf['sitemap'] < 1 || !is_numeric($conf['sitemap'])) {
            http_status(404);
            print "Sitemap generation is disabled.";
            exit;
        }

        $sitemap = Sitemapper::getFilePath();
        if (Sitemapper::sitemapIsCompressed()) {
            $mime = 'application/x-gzip';
        }else{
            $mime = 'application/xml; charset=utf-8';
        }

        // Check if sitemap file exists, otherwise create it
        if (!is_readable($sitemap)) {
            Sitemapper::generate();
        }

        if (is_readable($sitemap)) {
            // Send headers
            header('Content-Type: '.$mime);
            header('Content-Disposition: attachment; filename='.utf8_basename($sitemap));

            http_conditionalRequest(filemtime($sitemap));

            // Send file
            //use x-sendfile header to pass the delivery to compatible webservers
            if (http_sendfile($sitemap)) exit;

            readfile($sitemap);
            exit;
        }

        http_status(500);
        print "Could not read the sitemap file - bad permissions?";
        exit;
    }
}
