<?php

/**
 * An HtmlDescribable is an item within a feed that can have a description that may
 * include HTML markup.
 *
 * @package de.bitfolge.feedcreator
 */
class HtmlDescribable
{
    /**
     * Indicates whether the description field should be rendered in HTML.
     */
    public $descriptionHtmlSyndicated;

    /**
     * Indicates whether and to how many characters a description should be truncated.
     */
    public $descriptionTruncSize;

    /** @var string the Description */
    public $description;

    /**
     * Returns a formatted description field, depending on descriptionHtmlSyndicated and
     * $descriptionTruncSize properties
     *
     * @param bool $overrideSyndicateHtml
     * @return string the formatted description
     */
    public function getDescription($overrideSyndicateHtml = false)
    {
        $descriptionField = new FeedHtmlField($this->description);
        $descriptionField->syndicateHtml = $overrideSyndicateHtml || $this->descriptionHtmlSyndicated;
        $descriptionField->truncSize = $this->descriptionTruncSize;

        return $descriptionField->output();
    }
}
