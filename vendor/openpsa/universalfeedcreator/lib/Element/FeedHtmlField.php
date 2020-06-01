<?php

/**
 * A FeedHtmlField describes and generates
 * a feed, item or image html field (probably a description). Output is
 * generated based on $truncSize, $syndicateHtml properties.
 *
 * @author  Pascal Van Hecke <feedcreator.class.php@vanhecke.info>
 * @version 1.6
 * @package de.bitfolge.feedcreator
 */
class FeedHtmlField
{
    /**
     * Mandatory attributes of a FeedHtmlField.
     */
    protected $rawFieldContent;

    /**
     * Optional attributes of a FeedHtmlField.
     */
    public $truncSize, $syndicateHtml;

    /**
     * Creates a new instance of FeedHtmlField.
     *
     * @param string $parFieldContent if given, sets the rawFieldContent property
     */
    public function __construct($parFieldContent)
    {
        if ($parFieldContent) {
            $this->rawFieldContent = $parFieldContent;
        }
    }

    /**
     * Creates the right output, depending on $truncSize, $syndicateHtml properties.
     *
     * @return string the formatted field
     */
    public function output()
    {
        // when field available and syndicated in html we assume
        // - valid html in $rawFieldContent and we enclose in CDATA tags
        // - no truncation (truncating risks producing invalid html)
        if (!$this->rawFieldContent) {
            $result = "";
        } elseif ($this->syndicateHtml) {
            $result = "<![CDATA[".$this->rawFieldContent."]]>";
        } else {
            if ($this->truncSize and is_int($this->truncSize)) {
                $result = FeedCreator::iTrunc(htmlspecialchars($this->rawFieldContent), $this->truncSize);
            } else {
                $result = htmlspecialchars($this->rawFieldContent);
            }
        }

        return $result;
    }
}
