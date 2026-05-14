/*--------------------------------------------------------|
 | dTree 2.05 | www.destroydrop.com/javascript/tree/      |
 |--------------------------------------------------------|
 | Copyright (c) 2002-2003 Geir Landro                    |
 |                                                        |
 | This script can be used freely as long as all          |
 | copyright messages are intact.                         |
 |                                                        |
 | Updated: 17.04.2003                                    |
 |--------------------------------------------------------|
 | Modified for Dokuwiki by                               |
 | Samuele Tognini <samuele@samuele.netsons.org>          |
 | under GPL 2 license                                    |
 | (http://www.gnu.org/licenses/gpl.html)                 |
 | Updated: 29.08.2009                                    |
 |--------------------------------------------------------|
 | Modified for Dokuwiki by                               |
 | Rene Hadler <rene.hadler@iteas.at>                     |
 | under GPL 2 license                                    |
 | (http://www.gnu.org/licenses/gpl.html)                 |
 | Updated: 07.08.2012                                    |
 |--------------------------------------------------------|
 | jQuery update - 27 02 2012                             |
 | Gerrit Uitslag <klapinklapin@gmail.com                 |
 |--------------------------------------------------------|
 | indexmenu  | https://www.dokuwiki.org/plugin:indexmenu |
 |-------------------------------------------------------*/
/* global DOKU_COOKIE_PARAM */
/* global DOKU_BASE */

/*
 * ids used in the dTree:
 *  - div#cdtree_<id indexmenu>  div top level
 *      - div#dtree_<id indexmenu> div contains all nodes
 *          - div#toc_<id indexmenu> ??
 *          - div.dtreeNode
 *              - img#i<id indexmenu><nodenr?>   icon
 *              - a#s<id indexmenu><nodenr?>     url to page/namespace with title
 *              - div#t<id indexmenu><nodenr?>   button for opening ToC, included if hovered
 *          - div.d<id indexmenu><nodenr?>
 *  repeats:    - div.dtreeNode (with img#i, a#s and div#t)
 *  repeats:    - div.d<id indexmenu><nodenr?>
 *      - z<id indexmenu>  scroll rightward arrows
 *      - left_<id indexmenu> scroll leftward arrows
 *
 * at the end of body:
 *  - picker_<id indexmenu> popup with ToC
 *  - r<id indexmenu>  rightmouse button menu
 */

/**
 * dTreeNode object
 *
 * @param {string}        dokuid page id of node
 * @param {number}        id     node id
 * @param {number}        pid    parent node id
 * @param {string}        name   Page Title
 * @param {number|string} hns    page id of headpage of namespace
 * @param {number}        isdir  is directory?
 * @param {number}        ajax   load subnodes by ajax
 * @constructor
 */
function dTreeNode(dokuid, id, pid, name, hns, isdir, ajax) {
    /** @type {string} */
    this.dokuid = dokuid; // page id of node
    /** @type {number} */
    this.id = id;         // id number of node
    /** @type {number} */
    this.pid = pid;       // id number of parent node
    /** @type {string} */
    this.name = name;     // ns/page title
    /** @type {number|string} */
    this.hns = hns;       // headpage of namespace or zero
    /** @type {boolean} */
    this.isdir = Boolean(isdir); // is directory
    /** @type {boolean} */
    this.ajax = Boolean(ajax);   // load its nodes by ajax
    /** @type {boolean} */
    this._io = false;     // is node open
    /** @type {boolean} */
    this._is = false;     // is selected
    /** @type {boolean} */
    this._ls = false;     // is last sibling
    /** @type {boolean} */
    this._hc = Boolean(ajax); // has children
    /** @type {number} */
    this._ai = 0;         // id number of first child....
    /** @type {dTreeNode} */
    this._p = undefined;  // parent dTreeNode
    /** @type {number} */
    this._lvl = 0;        // level
    /** @type {boolean} */
    this._ok = false;     // all children are loaded
    /** @type {boolean} */
    this._cp = false;     // current page
    /** @type {string} */
    this.icon = '';       // icon of closed node
    /** @type {string} */
    this.iconOpen = '';   // icon of opened node
}

/**
 * Tree object
 *
 * @param {string} treeName id of the indexmenu, has form 'indexmenu_<identifier>'
 * @param {string} theme   name of theme dir
 * @constructor
 */
function dTree(treeName, theme) {
    let imgExt = IndexmenuUtils.determineExtension(theme);
    this.config = {
        urlbase: DOKU_BASE + 'doku.php?id=',           // base of dokuwiki (set in page)
        plugbase: DOKU_BASE + 'lib/plugins/indexmenu', // base of plugin folder
        useCookies: true,                              // use cookies (set in page) e.g. disabled for context option
        scroll: true,                                  // enable scrolling of tree in too small columns (set in page)
        toc: true,                                     // enable ToC popups in tree (set in page)
        maxjs: 1,                                      // number set by maxjs option (set in page)
        jsajax: '',                                    //  &max=#&sort=(t|d)&msort=(indexmenu_n|<metakey>)&rsort=1&nsort=1&hsort=1&nopg=1&skipns=+=/.../&skipfile=+=/.../(set in page)
        sepchar: ':',                                  // value ':', ';' or '/'  (set in page)
        theme: theme                                   // dir name of theme folder
    };
    let imagePath = this.config.plugbase + '/images/' + theme + '/';
    this.icon = {
        root: imagePath + 'base.' + imgExt,
        folder: imagePath + 'folder.' + imgExt,
        folderH: imagePath + 'folderh.' + imgExt,
        folderOpen: imagePath + 'folderopen.' + imgExt,
        folderHOpen: imagePath + 'folderhopen.' + imgExt,
        node: imagePath + 'page.' + imgExt,
        empty: imagePath + 'empty.' + imgExt,
        line: imagePath + 'line.' + imgExt,
        join: imagePath + 'join.' + imgExt,
        joinBottom: imagePath + 'joinbottom.' + imgExt,
        plus: imagePath + 'plus.' + imgExt,
        plusBottom: imagePath + 'plusbottom.' + imgExt,
        minus: imagePath + 'minus.' + imgExt,
        minusBottom: imagePath + 'minusbottom.' + imgExt,
        nlPlus: imagePath + 'nolines_plus.' + imgExt,
        nlMinus: imagePath + 'nolines_minus.' + imgExt
    };
    /** @type {string} */
    this.treeName = treeName; // (unique) name of this indexmenu
    /** @type {dTreeNode[]} */
    this.aNodes = [];   // array of nodes
    /** @type {number[]} */
    this.aIndent = [];  // array stores the indents of the tree (contains values 0 or 1)
    /** @type {dTreeNode} */
    this.root = new dTreeNode(false, -1);
    /** @type {number} */
    this.selectedNode = undefined;      // node id
    /** @type {boolean} */
    this.selectedFound = false;    // set to true when found
    /** @type {boolean} */
    this.completed = false;        // succesfull written js tree to the page
    /** @type {number} */
    this.scrllTmr = 0;             // store timer id for horizontal scrolling the page
    /** @type {string} */
    this.pageid = JSINFO.id || ''; // current page

    this.fajax = false;            // if retrieve next level of opened nodes
}
/**
 * CSS classes:
 *
 * a.nodeFdUrl	Namespace with url link (headpage)
 * a.node 	    Namespace without url link
 * a.nodeUrl	Page
 * a.nodeSel 	Last visited page
 * a.navSel 	Current page
 */


/**
 * Adds a new node to the node array
 *
 * @param {string}        dokuid page id of node
 * @param {number}        id     node id
 * @param {number}        pid    parent node id
 * @param {string}        name   Page Title
 * @param {number|string} hns    page id of headpage of namespace
 * @param {number}        isdir  is directory?
 * @param {number}        ajax   load subnodes by ajax
 */
dTree.prototype.add = function (dokuid, id, pid, name, hns, isdir, ajax) {
    this.aNodes[this.aNodes.length] = new dTreeNode(dokuid, id, pid, name, hns, isdir, ajax);
};

/**
 * Open all nodes, if no node status was stored in cookie
 */
dTree.prototype.openAll = function () {
    if (!this.getCookie('co' + this.treeName)) {
        this.oAll(true);
    }
};

/**
 * Outputs the tree to the page. Called by document.write after adding the nodes to the tree.
 *
 * @returns {string} html of whole tree
 */
dTree.prototype.toString = function () {
    let str = '';
    this.pageid = this.pageid.replace(/:/g,this.config.sepchar);
    if (this.config.scroll) {
        str += '<div id="cdtree_' + this.treeName + '" class="dtree" style="position:relative;overflow:hidden;width:100%;">';
    }
    str += '<div id="dtree_' + this.treeName + '" class="dtree ' + this.config.theme + '" style="overflow:';
    if (this.config.scroll) {
        str += 'visible;position:relative;width:100%"';
    } else {
        str += 'hidden;"';
    }
    str += '>';
	if (jQuery('#dtree_' + this.treeName)[0]) {
        str += '<div class="error">Indexmenu id conflict</div>';
    }
    if (this.config.toc) {
        str += '<div id="t' + this.treeName + '" class="indexmenu_tocbullet ' + this.config.theme + '" style="display:none;" title="Table of contents"></div>';
        str += '<div id="toc_' + this.treeName + '" style="display:none;"></div>';
    }
    if (this.config.useCookies) {
        this.selectedNode = this.getSelected();
    }
    str += this.addNode(this.root) + '</div>';
    if (this.config.scroll) {
        str += '<div id="z' + this.treeName + '" class="indexmenu_rarrow"></div>';
        str += '<div id="left_' + this.treeName + '" class="indexmenu_larrow" style="display:none;" title="Click to scroll back" onmousedown="' + this.treeName + '.scroll(\'r\',1)" onmouseup="' + this.treeName + '.stopscroll()"></div>';
        str += '</div>';
    }
    this.completed = true;
    //hide the fallback nojs indexmenu
    jQuery('#nojs_' + this.treeName).css("display", "none"); //using  .hide(); let's  crash opera
    return str;
};

/**
 * Creates the tree structure
 *
 * @param {dTreeNode} pNode
 * @returns {string} html of node (inclusive children)
 */
dTree.prototype.addNode = function (pNode) {
    let str = '', cn, n = pNode._ai, l = pNode._lvl + 1;
    for (n; n < this.aNodes.length; n++) {
        if (this.aNodes[n].pid === pNode.id) {
            cn = this.aNodes[n];
            cn._p = pNode;
            cn._ai = n;
            cn._lvl = l;
            this.setCS(cn);
            if (cn._hc && !cn._io && this.config.useCookies) {
                cn._io = this.isOpen(cn.id);
            }
            if (this.pageid === (!cn.hns && cn.dokuid || cn.hns)) {
                cn._cp = true;
            } else if (cn.id === this.selectedNode && !this.selectedFound) {
                cn._is = true;
                this.selectedNode = n;
                this.selectedFound = true;
            }
            if (!cn._hc && cn.isdir && !cn.ajax && !cn.hns) {
                if (cn._ls) {
                    str += this.noderr(cn, n);
                }
            } else {
                str += this.node(cn, n);
            }
            if (cn._ls) {
                break;
            }
        }
    }
    return str;
};

/**
 * Create empty node
 *
 * @param {dTreeNode} node
 * @param {int} nodeId
 * @returns {string} html of empty node
 */
dTree.prototype.noderr = function (node, nodeId) {
    let str = '<div class="dTreeNode">' + this.indent(node, nodeId);
    str += '<div class="emptynode" title="Empty"></div></div>';
    return str;
};

/**
 * Creates the node icon, url and text
 *
 * @param {dTreeNode} node
 * @param {int} nodeId
 * @returns {string} html of node (inclusive children)
 */
dTree.prototype.node = function (node, nodeId) {
    let h = 1, jsfnc, str;
    jsfnc = 'onmouseover="' + this.treeName + '.show_feat(\'' + nodeId + '\');" onmousedown="return IndexmenuContextmenu.checkcontextm(\'' + nodeId + '\',' + this.treeName + ',event);" oncontextmenu="return IndexmenuContextmenu.stopevt(event)"';
    if (node._lvl > this.config.maxjs) {
        h = 0;
    } else {
        node._ok = true;
    }
    str = '<div class="dTreeNode">' + this.indent(node, nodeId);
    node.icon = (this.root.id === node.pid) ? this.icon.root : ((node.hns) ? this.icon.folderH : ((node._hc) ? this.icon.folder : this.icon.node));
    node.iconOpen = (node._hc) ? ((node.hns) ? this.icon.folderHOpen : this.icon.folderOpen) : this.icon.node;
    if (this.root.id === node.pid) {
        node.icon = this.icon.root;
        node.iconOpen = this.icon.root;
    }
    str += '<img id="i' + this.treeName + nodeId + '" src="' + ((node._io) ? node.iconOpen : node.icon) + '" alt="" />';
    if (!node._hc || node.hns) {
        str += '<a id="s' + this.treeName + nodeId + '" class="' + ((node._cp) ? 'navSel' : ((node._is) ? 'nodeSel' : (node._hc) ? 'nodeFdUrl' : 'nodeUrl'));
        str += '" href="' + this.config.urlbase;
        (node.hns) ? str += node.hns : str += node.dokuid;
        str += '"' + ' title="' + node.name + '"' + jsfnc;
        str += ' onclick="javascript: ' + this.treeName + '.s(' + nodeId + ');"';
        str += ' data-wiki-id="' + node.dokuid + '"';
        str += '>' + node.name + '</a>';
    }
    else if (node.pid !== this.root.id) {
        str += '<a id="s' + this.treeName + nodeId + '" href="javascript: ' + this.treeName + '.o(' + nodeId + '); " data-wiki-id="' + node.dokuid + '" class="node"' + jsfnc + '>' + node.name + '</a>';
    } else {
        str += node.name;
    }
    str += '</div>';
    if (node._hc) {
        str += '<div id="d' + this.treeName + nodeId + '" class="clip" style="display:' + ((this.root.id === node.pid || node._io) ? 'block' : 'none') + ';">';
        if (h) {
            str += this.addNode(node);
        }
        str += '</div>';
    }
    this.aIndent.pop();
    return str;
};

/**
 * Adds the empty and line icons which indent the node
 *
 * @param {dTreeNode} node
 * @param {int} nodeId
 * @returns {string} html of indent icons
 */
dTree.prototype.indent = function (node, nodeId) {
    let n, str = '';
    if (this.root.id !== node.pid) {
        for (n = 0; n < this.aIndent.length; n++) {
            str += '<img src="' + ( (this.aIndent[n] === 1) ? this.icon.line : this.icon.empty ) + '" alt="" />';
        }
        if (node._ls) {
            this.aIndent.push(0);
        } else {
            this.aIndent.push(1);
        }
        if (node._hc) {
            str += '<a href="javascript: ' + this.treeName + '.o(' + nodeId + ');">' +
                   '<img id="j' + this.treeName + nodeId + '" src="' +
                   ( (node._io) ? ((node._ls) ? this.icon.minusBottom : this.icon.minus) : ((node._ls) ? this.icon.plusBottom : this.icon.plus ) ) +
                   '" alt="" /></a>';
        } else {
            str += '<img src="' + ((node._ls) ? this.icon.joinBottom : this.icon.join) + '" alt="" />';
        }
    }
    return str;
};

/**
 * Checks if a node has any children and if it is the last sibling
 *
 * @param {dTreeNode} node
 */
dTree.prototype.setCS = function (node) {
    let lastId, n;
    for (n = 0; n < this.aNodes.length; n++) {
        if (this.aNodes[n].pid === node.id) {
            node._hc = true;
        }
        if (this.aNodes[n].pid === node.pid) {
            lastId = this.aNodes[n].id;
        }
    }
    if (lastId === node.id) {
        node._ls = true;
    }
};

/**
 * Returns the selected node as stored in cookie
 *
 * @returns {int} node id
 */
dTree.prototype.getSelected = function () {
    let sn = this.getCookie('cs' + this.treeName);
    return (sn) ? parseInt(sn, 10) : null;
};

/**
 * Highlights the selected node
 *
 * @param {int} id node id
 */
dTree.prototype.s = function (id) {
    let eOld, eNew, cn = this.aNodes[id];
    if (this.selectedNode !== id) {
        eNew = jQuery("#s" + this.treeName + id)[0];
        if (!eNew) {
            return;
        }
        if (this.selectedNode || this.selectedNode === 0) {
            eOld = jQuery("#s" + this.treeName + this.selectedNode)[0];
            eOld.className = "node";
        }
        eNew.className = "nodeSel";
        this.selectedNode = id;
        if (this.config.useCookies) {
            this.setCookie('cs' + this.treeName, cn.id);
        }
    }
};

/**
 * Toggle Open or close
 *
 * @param {int} id node id
 */
dTree.prototype.o = function (id) {
    let cn = this.aNodes[id];
    this.nodeStatus(!cn._io, id, cn._ls);
    cn._io = !cn._io;
    if (this.config.useCookies) {
        this.updateCookie();
    }
    // scroll
    this.divdisplay('z', false);
    this.resizescroll("block");
};

/**
 * Open or close all nodes
 *
 * @param {boolean} status if true open
 */
dTree.prototype.oAll = function (status) {
    for (let n = 0; n < this.aNodes.length; n++) {
        if (this.aNodes[n]._hc && this.aNodes[n].pid !== this.root.id) {
            this.nodeStatus(status, n, this.aNodes[n]._ls);
            this.aNodes[n]._io = status;
        }
    }
    if (this.config.useCookies) {
        this.updateCookie();
    }
};

/**
 * Opens the tree to a specific node
 *
 * @param {number} nId node id
 * @param {boolean} bSelect
 * @param {boolean} bFirst
 */
dTree.prototype.openTo = function (nId, bSelect, bFirst) {
    let n, cn;
    if (!bFirst) {
        for (n = 0; n < this.aNodes.length; n++) {
            if (this.aNodes[n].id === nId) {
                nId = n;
                break;
            }
        }
    }
    this.fill(this.aNodes[nId].pid);
    cn = this.aNodes[nId];
    if (cn.pid === this.root.id || !cn._p) {
        return;
    }
    cn._io = 1;
    if (this.completed && cn._hc) {
        this.nodeStatus(true, cn._ai, cn._ls);
    }
    if (cn._is) {
        (this.completed) ? this.s(cn._ai) : this._sn = cn._ai;
    }
    this.openTo(cn._p._ai, false, true);
};

/**
 * Open the given nodes, if no node status is already stored
 *
 * @param {Array|string} nodes array of nodes to open or empty string to open all nodes
 */
dTree.prototype.getOpenTo = function (nodes) {
    if (nodes === '') {
        this.openAll();
    } else if (!this.config.useCookies || !this.getCookie('co' + this.treeName)) {
        for (let n = 0; n < nodes.length; n++) {
            this.openTo(nodes[n], false, true);
        }
    }
};

/**
 * Change the status of a node(open or closed)
 *
 * @param {boolean} status true if open
 * @param {int}     id     node id
 * @param {boolean} bottom true if bottom node
 */
dTree.prototype.nodeStatus = function (status, id, bottom) {
    if (status && !this.fill(id)) {
        return;
    }
    let eJoin, eIcon;
	eJoin = jQuery('#j' + this.treeName + id)[0];
	eIcon = jQuery('#i' + this.treeName + id)[0];
    eIcon.src = (status) ? this.aNodes[id].iconOpen : this.aNodes[id].icon;
    eJoin.src = ((status) ? ((bottom) ? this.icon.minusBottom : this.icon.minus) : ((bottom) ? this.icon.plusBottom : this.icon.plus));
    jQuery('#d' + this.treeName + id)[0].style.display = (status) ? 'block' : 'none';
};

/**
 * [Cookie] Clears a cookie
 */
dTree.prototype.clearCookie = function () {
    let now, yday;
    now = new Date();
    yday = new Date(now.getTime() - 1000 * 60 * 60 * 24);
    this.setCookie('co' + this.treeName, 'cookieValue', yday);
    this.setCookie('cs' + this.treeName, 'cookieValue', yday);
};

/**
 * [Cookie] Sets value in a cookie
 *
 * @param {string}  cookieName
 * @param {string}  cookieValue
 * @param {boolean|Date} expires
 */
dTree.prototype.setCookie = function (cookieName, cookieValue, expires = false) {
    document.cookie =
        encodeURIComponent(cookieName) + '=' + encodeURIComponent(cookieValue) +
            (expires ? '; expires=' + expires.toUTCString() : '') +
            '; path=' + DOKU_COOKIE_PARAM.path +
            '; secure=' + DOKU_COOKIE_PARAM.secure;
};

/**
 * [Cookie] Gets a value from a cookie
 *
 * @param cookieName
 * @returns {string}
 */
dTree.prototype.getCookie = function (cookieName) {
    let cookieValue = '', pN, posValue, endPos;
    pN = document.cookie.indexOf(encodeURIComponent(cookieName) + '=');
    if (pN !== -1) {
        posValue = pN + (encodeURIComponent(cookieName) + '=').length;
        endPos = document.cookie.indexOf(';', posValue);
        if (endPos !== -1) {
            cookieValue = decodeURIComponent(document.cookie.substring(posValue, endPos));
        }
        else {
            cookieValue = decodeURIComponent(document.cookie.substring(posValue));
        }
    }
    return (cookieValue);
};

/**
 * [Cookie] Stores ids of open nodes as a string in cookie
 */
dTree.prototype.updateCookie = function () {
    let str = '', n;
    for (n = 0; n < this.aNodes.length; n++) {
        if (this.aNodes[n]._io && this.aNodes[n].pid !== this.root.id) {
            if (str) {
                str += '.';
            }
            str += this.aNodes[n].id;
        }
    }
    this.setCookie('co' + this.treeName, str);
};

/**
 * [Cookie] Checks if a node id is in the cookie
 *
 * @param {int} id node id
 * @return {Boolean} if open true
 */
dTree.prototype.isOpen = function (id) {
    let n, aOpen = this.getCookie('co' + this.treeName).split('.');
    for (n = 0; n < aOpen.length; n++) {
        if (parseInt(aOpen[n],10) === id) {
            return true;
        }
    }
    return false;
};

/**
 * Open the node of the current namespace
 *
 * @param {int} max
 */
dTree.prototype.openCurNS = function (max) {
    let r, cn, match, t, i, n, cnsa, cna;
    let cns = this.pageid;
    r = new RegExp("\\b" + this.config.sepchar + "\\b", "g");
    match = cns.match(r) || -1;
    if (max > 0 && match.length >= max) {
        t = cns.split(this.config.sepchar);
        n = (this.aNodes[0].dokuid === '') ? 0 : this.aNodes[0].dokuid.split(this.config.sepchar).length;
        t.splice(max + n, t.length);
        cnsa = t.join(this.config.sepchar);
    }
    for (i = 0; i < this.aNodes.length; i++) {
        cn = this.aNodes[i];
        if (cns === cn.dokuid || cns === cn.hns) {
            this.openTo(cn.id, false, true);
            this.fajax = false;
            if (cn.pid >= 0) {
				jQuery(this.scroll("l", 4, cn.pid, 1));
            }
            break;
        }
        if (cnsa === cn.dokuid || cnsa === cn.hns) {
            cna = cn;
            this.fajax = true;
        }
    }
    if (cna) {
        this.openTo(cna.id, false, true);
    }
};

/**
 * Load children when not available
 *
 * @param {int} id node id
 * @returns {boolean}
 */
dTree.prototype.fill = function (id) {
    if (id === -1 || this.aNodes[id]._ok) {
        return true;
    }
    let n = id, $eLoad, a, rd, ln, eDiv;
    if (this.aNodes[n].ajax) {
        //temporary load indicator
        $eLoad = jQuery('#l' + this.treeName);
        if (!$eLoad.length) {
            $eLoad = IndexmenuUtils.createPicker('l' + this.treeName, 'picker');
        }
        jQuery('#s' + this.treeName + n).parent().append($eLoad);
        $eLoad
            .html('Loading ...')
            .css({width: 'auto'})
            .show();

        //retrieves children
        this.getAjax(n);
        return true;
    }
    rd = [];
    while (!this.aNodes[n]._ok) {
        rd[rd.length] = n;
        n = this.aNodes[n].pid;
    }
    for (ln = rd.length - 1; ln >= 0; ln--) {
        id = rd[ln];
        a = this.aNodes[id];
		eDiv = jQuery('#d' + this.treeName + id)[0];
        if (!eDiv) {
            return false;
        }
        this.aIndent = [];
        n = a;
        while (n.pid >= 0) {
            if (n._ls) {
                this.aIndent.unshift(0);
            } else {
                this.aIndent.unshift(1);
            }
            n = n._p;
        }
        eDiv.innerHTML = this.addNode(a);
        a._ok = true;
    }
    return true;
};

/**
 * Open the nodes stored in cookie
 */
dTree.prototype.openCookies = function () {
    let n, cn, aOpen = this.getCookie('co' + this.treeName).split('.');
    for (n = 0; n < aOpen.length; n++) {
        if (aOpen[n] === "") {
            break;
        }
        cn = this.aNodes[aOpen[n]];
        if (!cn._ok) {
            this.nodeStatus(true, aOpen[n], cn._ls);
            cn._io = true;
        }
    }
};

/**
 * Scrolls the index
 *
 * @param {string} where to move to
 * @param {int}    s     start
 * @param {int}    n     parent node id
 * @param {int}    i
 */
dTree.prototype.scroll = function (where, s, n, i) {
    if (!this.config.scroll) {
        return false;
    }
    let w, dtree, dtreel, nodeId;
    dtree = jQuery('#dtree_' + this.treeName)[0];
    dtreel = parseInt(dtree.offsetLeft);
    if (where === "r") {
        jQuery('#left_' + this.treeName)[0].style.border = "thin inset";
        this.scrollRight(dtreel, s);
    } else {
        nodeId = jQuery('#s' + this.treeName + n)[0];
        if (nodeId == null) {
            return false;
        }
        w = parseInt(dtree.parentNode.offsetWidth - nodeId.offsetWidth - nodeId.offsetLeft);
        if (this.config.toc) {
            w = w - 11;
        }
        if (dtreel <= w) {
            return;
        }
        this.resizescroll("none");
        this.stopscroll();
        this.scrollLeft(dtreel, s, w - 3, i);
    }
};

/**
 * Scroll index to the left
 *
 * @param {int} lft current position
 * @param {int} s start
 * @param {int} w width
 * @param {int} i
 */
dTree.prototype.scrollLeft = function (lft, s, w, i) {
    if (lft < w - i - 10) {
        this.divdisplay('z', false);
        this.scrllTmr = 0;
        return;
    }
    var self = this;
    jQuery('#dtree_' + self.treeName)[0].style.left = lft + "px";
    this.scrllTmr = setTimeout(function () {
        self.scrollLeft(lft - s, s + i, w, i);
    }, 20);
};

/**
 * Scroll Index back to the right
 *
 * @param {int} lft current position
 * @param {int} s   start
 */
dTree.prototype.scrollRight = function (lft, s) {
    if (lft >= s) {
        this.divdisplay('left_', false);
        this.stopscroll();
        return;
    }
    var self = this;
    jQuery('#dtree_' + self.treeName)[0].style.left = lft + "px";
    if (lft > -15) {
        s = 1;
    }
    this.scrllTmr = setTimeout(function () {
        self.scrollRight(lft + s, s + 1);
    }, 20);
};

/**
 * Stop scroll movement
 */
dTree.prototype.stopscroll = function () {
    jQuery('#left_' + this.treeName)[0].style.border = "none";
    clearTimeout(this.scrllTmr);
    this.scrllTmr = 0;
};

/**
 * Show features and add event handlers for ToC and scroll
 *
 * @param {int} n node id
 */
dTree.prototype.show_feat = function (n) {
	var w, div, id, dtree, dtreel, self, node = jQuery('#s' + this.treeName + n)[0];
    self = this;
    if (this.config.toc && node.className !== "node") {
		div = jQuery('#t' + this.treeName)[0];
        id = (this.aNodes[n].hns) ? this.aNodes[n].hns : this.aNodes[n].dokuid;
        div.onmousedown = function () {
            IndexmenuContextmenu.createTocMenu('call=indexmenu&req=toc&id=' + decodeURIComponent(id), 'picker_' + self.treeName, 't' + self.treeName);
        };
        node.parentNode.appendChild(div);
        if (div.style.display === "none") {
            div.style.display = "inline";
        }
    }
    if (this.config.scroll) {
		div = jQuery('#z' + this.treeName)[0];
        div.onmouseover = function () {
            div.style.border = "none";
            self.scroll("l", 1, n, 0);
        };
        div.onmousedown = function () {
            div.style.border = "thin inset";
            self.scroll("l", 4, n, 1);
        };
        div.onmouseout = function () {
            div.style.border = "none";
            self.stopscroll();
        };
        div.onmouseup = div.onmouseover;
		dtree = jQuery('#dtree_' + this.treeName)[0];
        dtreel = parseInt(dtree.offsetLeft);
        w = parseInt(dtree.parentNode.offsetWidth - node.offsetWidth - node.offsetLeft + 1);
        if (dtreel > w) {
            div.style.display = "none";
            div.style.top = node.offsetTop + "px";
            div.style.left = parseInt(node.offsetLeft + node.offsetWidth + w - 12) + "px";
            div.style.display = "block";
        }
    }
};

/**
 * Show and resize the scroll-back button relatively to size of tree
 *
 * @param {string} status 'block' or 'none'
 */
dTree.prototype.resizescroll = function (status) {
	let dtree, w, h, left = jQuery('#left_' + this.treeName)[0];
    if (!left) {
        return;
    }
    if (left.style.display === status) {
        dtree = jQuery('#dtree_' + this.treeName)[0];
        w = Math.trunc(dtree.offsetHeight / 3);
        h = Math.trunc(w / 50) * 50;
        if (h < 50) {
            h = 50;
        }
        left.style.height = h + "px";
        left.style.top = w + "px";
        if (status === "none") {
            left.style.display = "block";
        }
    }
};

/**
 * Toggle Open or close
 *
 * @param {int} n node id
 */
dTree.prototype.getAjax = function (n) {
    var node, selft = this;
    let req, curns;
    node = selft.aNodes[n];

    req = 'req=index&idx=' + node.dokuid + decodeURIComponent(this.config.jsajax);

    curns = this.pageid.substring(0, this.pageid.lastIndexOf(this.config.sepchar));
    if (this.fajax) {
        req += '&nss=' + curns + '&max=1';
    }

    var onCompletion = function (data) {
        var i, ajxnodes, ajxnode, plus;
        plus = selft.aNodes.length - 1;
        eval(data);
        if (!ajxnodes instanceof Array || ajxnodes.length < 1) {
            ajxnodes = [
                ['', 1, 0, '', 0, 1, 0]
            ];
        }
        node.ajax = false;
        for (i = 0; i < ajxnodes.length; i++) {
            ajxnode = ajxnodes[i];
            ajxnode[2] = (ajxnode[2] == 0) ? node.id : ajxnode[2] + plus;
            ajxnode[1] += plus;
            selft.add(ajxnode[0], ajxnode[1], ajxnode[2], ajxnode[3], ajxnode[4], ajxnode[5], ajxnode[6]);
        }
        if (selft.fajax) {
            selft.fajax = false;
            selft.openCurNS(0);
        } else {
            selft.openTo(node.id, false, true);
        }
        jQuery('#l' + selft.treeName).hide();
    };

    jQuery.post(
        DOKU_BASE + 'lib/exe/ajax.php',
        'call=indexmenu&'+req,
        onCompletion,
        'html'
    );
};

/**
 * Load custom css for theme
 */
dTree.prototype.loadCss = function () {
    let oLink = document.createElement("link");
    oLink.href = this.config.plugbase + '/images/' + this.config.theme + '/style.css';
    oLink.rel = "stylesheet";
    oLink.type = "text/css";
    document.getElementsByTagName('head')[0].appendChild(oLink);
};

/**
 * Show the contextmenu
 *
 * @param {int}   n node id
 * @param {Event} e event
 * @returns {boolean}
 */
dTree.prototype.contextmenu = function (n, e) {
    let type, node, cdtree, $rmenu;
    cdtree = jQuery("#cdtree_" + this.treeName)[0];
	$rmenu = jQuery('#r' + this.treeName)[0];
    if (!$rmenu) {
        return true;
    }
    IndexmenuContextmenu.mouseposition($rmenu, e);
    let cmenu = window.indexmenu_contextmenu;
    node = this.aNodes[n];
    $rmenu.innerHTML = '<div class="indexmenu_rmenuhead" title="' + node.name + '">' + node.name + "</div>";
    $rmenu.appendChild(document.createElement('ul'));
    type = (node.isdir || node._hc) ? 'ns' : 'pg';
    IndexmenuContextmenu.arrconcat(cmenu['all'][type], this, n);
    if (node.hns) {
        IndexmenuContextmenu.arrconcat(cmenu[type], this, n);
        type = 'pg';
        IndexmenuContextmenu.arrconcat(cmenu['all'][type], this, n);
    }
    IndexmenuContextmenu.arrconcat(cmenu[type], this, n);
    $rmenu.style.display = 'inline';
    return false;
};

/**
 * Show/hide object with given id of current indexmenu
 *
 * @param {string}  objName name of object, which is combined with the unique id of the indexmenu
 * @param {boolean} visible true: visible, false: hide.
 */
dTree.prototype.divdisplay = function (objName, visible) {
	let o = jQuery('#' + objName + this.treeName)[0];
    if (!o) {
        return;
    }
    (visible) ? o.style.display = 'inline' : o.style.display = 'none';
};

/**
 * Initialise the dTree index
 *
 * @param {int}    hasstyle  has an additional css style sheet
 * @param {int}    nocookies use no cookies
 * @param {string} opennodes string of initial opened nodes
 * @param {int}    nav       is navbar option set
 * @param {int}    max       max level of available nodes (deeper levels are included with js)
 * @param {int}    nomenu    show no menu
 */
dTree.prototype.init = function (hasstyle, nocookies, opennodes, nav, max, nomenu) {
    if (hasstyle) {
        this.loadCss();
    }
    if (!nocookies) {
        this.openCookies();
    }
    //open given nodes
    if (opennodes) {
        this.getOpenTo(opennodes.split(" "));
    }
    if (nav) {
        this.openCurNS(max);
    }
    //create contextmenu
    if (!nomenu) {
        var self = this;
        IndexmenuUtils.createPicker('r' + this.treeName, 'indexmenu_rmenu ' + this.config.theme);
        jQuery('#r' + this.treeName)[0].oncontextmenu = IndexmenuContextmenu.stopevt;
		jQuery(document).on("click",function() {
            self.divdisplay('r', false);
        });
    }
};
