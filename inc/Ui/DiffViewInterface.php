<?php

namespace dokuwiki\Ui;

/**
 * DokuWiki DiffView Interface
 */
interface DiffViewInterface
{
    /**
     * Return id of page or media file
     * @return string
     */
    public function getId();

    /**
     * Set preference of the Ui\Diff Interface object
     *
     * @param string|array $prefs  a key name or key-value pair(s)
     * @param mixed $value         value used when the first args is string
     */
    public function setPreference($prefs = null, $value = null);

    /**
     * Set revision pair to be compared
     *
     * @param string|array $prefs  a key name or key-value pair(s)
     * @param mixed $value         value used when the first args is string
     */
    public function setRevisions(array $revs = []);

    /**
     * Display the differences in two revisions
     */
    public function show();
}
