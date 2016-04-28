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
        'namespace' => '',
        'postfix' => '',
        'autocomplete' => array(
            'mininput' => 2,
            'maxresult' => 5,
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
        $link = cleanID($this->config['namespace'] .':'. $value . $this->config['postfix']);
        if(!$link) return true;

        $R->internallink(":$link");
        return true;
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
        $namespace = cleanID($this->config['namespace']);
        $postfix = $this->config['postfix'];
        if($namespace) $lookup .= ' @'.$namespace;

        $data = ft_pageLookup($lookup, true, useHeading('navigation'));
        if(!count($data)) return array();

        // this basically duplicates what we do in ajax_qsearch()
        $result = array();
        $counter = 0;
        foreach($data as $id => $title){
            if (useHeading('navigation')) {
                $name = $title;
            } else {
                $ns = getNS($id);
                if($ns){
                    $name = noNS($id).' ('.$ns.')';
                }else{
                    $name = $id;
                }
            }

            // check suffix and remove
            if($postfix) {
                if(substr($id, -1 * strlen($postfix)) == $postfix) {
                    $id = substr($id, 0, -1 * strlen($postfix));
                } else {
                    continue; // page does not end in postfix, don't suggest it
                }
            }

            // remove namespace again
            if($namespace) {
                if(substr($id, 0, strlen($namespace) + 1) == "$namespace:") {
                    $id = substr($id, strlen($namespace) + 1);
                } else {
                    continue; // this should usually not happen
                }
            }

            $result[] = array(
                'label' => $name,
                'value' => $id
            );

            $counter ++;
            if($counter > $max) break;
        }

        return $result;
    }

}
