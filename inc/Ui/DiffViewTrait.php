<?php

namespace dokuwiki\Ui;

/**
 * DokuWiki DiffViewTrait
 *
 * @package dokuwiki\Ui
 */
trait DiffViewTrait
{
    /* @var string */
    protected $id;

    /* @var array */
    protected $preference = [];

    /* @var int[] timestamps of older [0] and newer [1] revisions */
    protected $revisions = [];

    /* @var ChangeLog */
    protected $changelog;

    // must have access to changelog
    abstract protected function setChangeLog();

    /**
     * Return id of page or media file
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set preference of the DiffViewInterface object
     *
     * @param string|array $prefs  a key name or key-value pair(s)
     * @param mixed $value         value used when the first args is string
     * @return void
     */
    public function setPreference($prefs = null, $value = null)
    {
        if (is_string($prefs) && isset($value)) {
            $this->preference[$prefs] = $value;
        } elseif (is_array($prefs)) {
            foreach ($prefs as $name => $value) {
                $this->preference[$name] = $value;
            }
        }
    }

    /**
     * Set revision pair to be compared
     *
     * @param int[] $revs timestamps of older [0] and newer [1] revisions
     * @return void
     */
    public function setRevisions(array $revs = [])
    {
        $changelog =& $this->changelog;

        if (count($revs) > 1) {
            // url parameter rev2, &do=diff&rev2[0]=#&rev2[1]=#
            $revs[1] = $changelog->traceExternalRevision($revs[1]);
        } elseif (!empty($revs[0])) {
            // compare given revision with current
            // url parameter rev, &do=diff&rev=#
            $revs[1] = $changelog->currentRevision();
            if ($revs[0] > $revs[1]) $revs = [];
        }

        if (empty($revs)) {
            // no revision was given, compare previous with current revision
            $rev2 = $changelog->currentRevision();
            if ($rev2 > $changelog->lastRevision()) {
                $rev1 = $changelog->lastRevision();
            } else {
                $revs = $changelog->getRevisions(0, 1);
                $rev1 = count($revs) ? $revs[0] : false;
            }
            $revs = [$rev1, $rev2];
        }
        $this->revisions = $revs;
    }
}
