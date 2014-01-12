<?php

/**
 * Handler for action search search
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Search extends Doku_Action
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() { return "search"; }

    /**
     * Specifies the required permission to search
     * Was AUTH_NONE, but shouldn't one need read permission at all 
     * to search, so that there would be no information leak?
     * 
     * @return string the permission
     */
    public function permission_required() {
        return AUTH_READ;
    }

    /**
     * handle the search action
     * 
     * @return string the action to do next
     */
    public function handle() {
        global $QUERY;
        $s = cleanID($QUERY);
        if (empty($s)) return "show";
    }
}

/**
 * Renderer for action search search
 * 
 * @author Junling Ma <junlingm@gmail.com>
 */
class Doku_Action_Renderer_Search extends Doku_Action_Renderer
{
    /**
     * Specifies the action name
     * 
     * @return string the action name
     */
    public function action() { return "search"; }

    /**
     * Display search results.
     * was html_search() by Andreas Gohr <andi@splitbrain.org>
     *
     * @global array $QUERY
     * @global array $lang
     */
    function xhtml(){
        global $QUERY;
        global $lang;

        $intro = p_locale_xhtml('searchpage');
        // allow use of placeholder in search intro
        $intro = str_replace(
                    array('@QUERY@','@SEARCH@'),
                    array(hsc(rawurlencode($QUERY)),hsc($QUERY)),
                    $intro);
        echo $intro;
        flush();

        //show progressbar
        print '<div id="dw__loading">'.NL;
        print '<script type="text/javascript">/*<![CDATA[*/'.NL;
        print 'showLoadBar();'.NL;
        print '/*!]]>*/</script>'.NL;
        print '</div>'.NL;
        flush();

        //do quick pagesearch
        $data = ft_pageLookup($QUERY,true,useHeading('navigation'));
        if(count($data)){
            print '<div class="search_quickresult">';
            print '<h3>'.$lang['quickhits'].':</h3>';
            print '<ul class="search_quickhits">';
            foreach($data as $id => $title){
                print '<li> ';
                if (useHeading('navigation')) {
                    $name = $title;
                }else{
                    $ns = getNS($id);
                    if($ns){
                        $name = shorten(noNS($id), ' ('.$ns.')',30);
                    }else{
                        $name = $id;
                    }
                }
                print html_wikilink(':'.$id,$name);
                print '</li> ';
            }
            print '</ul> ';
            //clear float (see http://www.complexspiral.com/publications/containing-floats/)
            print '<div class="clearer"></div>';
            print '</div>';
        }
        flush();

        //do fulltext search
        $data = ft_pageSearch($QUERY,$regex);
        if(count($data)){
            print '<dl class="search_results">';
            $num = 1;
            foreach($data as $id => $cnt){
                print '<dt>';
                print html_wikilink(':'.$id,useHeading('navigation')?null:$id,$regex);
                if($cnt !== 0){
                    print ': '.$cnt.' '.$lang['hits'].'';
                }
                print '</dt>';
                if($cnt !== 0){
                    if($num < FT_SNIPPET_NUMBER){ // create snippets for the first number of matches only
                        print '<dd>'.ft_snippet($id,$regex).'</dd>';
                    }
                    $num++;
                }
                flush();
            }
            print '</dl>';
        }else{
            print '<div class="nothing">'.$lang['nothingfound'].'</div>';
        }

        //hide progressbar
        print '<script type="text/javascript">/*<![CDATA[*/'.NL;
        print 'hideLoadBar("dw__loading");'.NL;
        print '/*!]]>*/</script>'.NL;
        flush();
    }
}
