<?php
namespace dokuwiki\plugin\struct\types;

/**
 * Class Page
 *
 * Represents a single page in the wiki. Will be linked in output.
 *
 * @package dokuwiki\plugin\struct\types
 */
class Page extends AbstractMultiBaseType {

    protected $config = array(
        'autocomplete' => array(
            'mininput' => 2,
            'maxresult' => 5,
            'namespace' => '',
            'postfix' => '',
        ),
    );

    /**
     * Output the stored data
     *
     * @param string|int $value the value stored in the database
     * @param \Doku_Renderer $R the renderer currently used to render the data
     * @param string $mode The mode the output is rendered in (eg. XHTML)
     * @return bool true if $mode could be satisfied
     */
    public function renderValue($value, \Doku_Renderer $R, $mode) {
        if(!$value) return true;

        $R->internallink(":$value");
        return true;
    }

    /**
     * Cleans the link
     *
     * @param string $value
     * @return string
     */
    public function validate($value) {
        return cleanID($value);
    }

    /**
     * Autocompletion support for pages
     *
     * @return array
     */
    public function handleAjax() {
        global $INPUT;

        // check minimum length
        $lookup = trim($INPUT->str('search'));
        if(utf8_strlen($lookup) < $this->config['autocomplete']['mininput']) return array();

        // results wanted?
        $max = $this->config['autocomplete']['maxresult'];
        if($max <= 0) return array();

        // lookup with namespace and postfix applied
        $namespace = $this->config['autocomplete']['namespace'];
        if($namespace) {
            // namespace may be relative, resolve in current context
            $namespace .= ':foo'; // resolve expects pageID
            resolve_pageid($INPUT->str('ns'), $namespace, $exists);
            $namespace = getNS($namespace);
        }
        $postfix = $this->config['postfix'];
        if($namespace) $lookup .= ' @' . $namespace;

        $data = ft_pageLookup($lookup, true, useHeading('navigation'));
        if(!count($data)) return array();

        // this basically duplicates what we do in ajax_qsearch()
        $result = array();
        $counter = 0;
        foreach($data as $id => $title) {
            if(useHeading('navigation')) {
                $name = $title;
            } else {
                $ns = getNS($id);
                if($ns) {
                    $name = noNS($id) . ' (' . $ns . ')';
                } else {
                    $name = $id;
                }
            }

            // check suffix
            if($postfix && !substr($id, -1 * strlen($postfix)) == $postfix) {
                continue; // page does not end in postfix, don't suggest it
            }

            $result[] = array(
                'label' => $name,
                'value' => $id
            );

            $counter++;
            if($counter > $max) break;
        }

        return $result;
    }
}
