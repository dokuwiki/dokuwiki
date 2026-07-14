<?php

namespace dokuwiki\TreeBuilder\Node;

/**
 * A node representing a namespace startpage
 */
class WikiStartpage extends WikiNamespace
{
    protected string $originalNamespace;

    /**
     * Constructor
     *
     * @param string $id The pageID of the startpage
     * @param string|null $title The title as given in the link
     * @param string $originalNamespace The original namespace
     */
    public function __construct(string $id, ?string $title, string $originalNamespace)
    {
        $this->originalNamespace = $originalNamespace;
        parent::__construct($id, $title);
    }

    /**
     * This will return the namespace this startpage is for
     *
     * This might differ from the namespace of the pageID, because a startpage may be outside
     * the namespace.
     *
     * @inheritdoc
     */
    public function getNs(): string
    {
        return $this->originalNamespace;
    }
}
