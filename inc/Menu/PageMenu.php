<?php

namespace easywiki\Menu;

/**
 * Class PageMenu
 *
 * Actions manipulating the current page. Shown as a floating menu in the easywiki template
 */
class PageMenu extends AbstractMenu
{
    protected $view = 'page';

    protected $types = ['Edit', 'Revert', 'Revisions', 'Backlink', 'Subscribe', 'Top'];
}
