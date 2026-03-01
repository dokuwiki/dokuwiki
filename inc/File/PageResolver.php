<?php

namespace dokuwiki\File;

/**
 * Creates an absolute page ID from a relative one
 */
class PageResolver extends Resolver
{
    /**
     * Resolves a given ID to be absolute
     *
     * This handles all kinds of relative shortcuts, startpages and autoplurals
     * @inheritDoc
     */
    public function resolveId($id, $rev = '', $isDateAt = false)
    {
        global $conf;
        $id = (string) $id;

        // pages may have a hash attached, we separate it on resolving
        if (strpos($id, '#') !== false) {
            [$id, $hash] = sexplode('#', $id, 2);
            $hash = cleanID($hash);
        } else {
            $hash = '';
        }

        if ($id !== '') {
            $id = parent::resolveId($id, $rev, $isDateAt);
            $id = $this->resolveStartPage($id, $rev, $isDateAt);
            if ($conf['autoplural']) {
                $id = $this->resolveAutoPlural($id, $rev, $isDateAt);
            }
        } else {
            $id = $this->contextID;
        }

        $id = cleanID($id); // FIXME always? or support parameter
        // readd hash if any
        if ($hash !== '') $id .= "#$hash";
        return $id;
    }

    /**
     * IDs ending in :
     *
     * @param string $id
     * @param string|int|false $rev
     * @param bool $isDateAt
     * @return string
     */
    protected function resolveStartPage($id, $rev, $isDateAt)
    {
        global $conf;

        if ($id === '' || $id[-1] !== ':') return $id;

        if (page_exists($id . $conf['start'], $rev, true, $isDateAt)) {
            // start page inside namespace
            return $id . $conf['start'];
        } elseif (page_exists($id . noNS(cleanID($id)), $rev, true, $isDateAt)) {
            // page named like the NS inside the NS
            return $id . noNS(cleanID($id));
        } elseif (page_exists(substr($id, 0, -1), $rev, true, $isDateAt)) {
            // page named like the NS outside the NS
            return substr($id, 0, -1);
        }

        // fall back to default start page
        return $id . $conf['start'];
    }

    /**
     * Try alternative plural/singular form
     *
     * @param string $id
     * @param int $rev
     * @param bool $isDateAt
     * @return string
     */
    protected function resolveAutoPlural($id, $rev, $isDateAt)
    {
        if (page_exists($id, $rev, $isDateAt)) return $id;

        if ($id[-1] === 's') {
            $try = substr($id, 0, -1);
        } else {
            $try = $id . 's';
        }

        if (page_exists($try, $rev, true, $isDateAt)) {
            return $try;
        }
        return $id;
    }
}
