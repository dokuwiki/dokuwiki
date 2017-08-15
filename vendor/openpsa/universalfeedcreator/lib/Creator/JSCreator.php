<?php

/**
 * JSCreator is a class that writes a js file to a specific
 * location, overriding the createFeed method of the parent HTMLCreator.
 *
 * @author  Pascal Van Hecke
 * @package de.bitfolge.feedcreator
 */
class JSCreator extends HTMLCreator
{
    protected $contentType = "text/javascript";

    /**
     * writes the javascript
     *
     * @inheritdoc
     */
    public function createFeed()
    {
        $feed = parent::createFeed();
        $feedArray = explode("\n", $feed);

        $jsFeed = "";
        foreach ($feedArray as $value) {
            $jsFeed .= "document.write('".trim(addslashes($value))."');\n";
        }

        return $jsFeed;
    }

    /**
     * Overrides parent to produce .js extensions
     *
     * @return string the feed cache filename
     * @since  1.4
     * @access private
     */
    protected function _generateFilename()
    {
        $fileInfo = pathinfo($_SERVER["PHP_SELF"]);

        return substr($fileInfo["basename"], 0, -(strlen($fileInfo["extension"]) + 1)).".js";
    }
}
