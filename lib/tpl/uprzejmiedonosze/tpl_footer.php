<?php

/**
 * Template footer, included in the main and detail files
 */

// must be run from within DokuWiki
if (!defined('DOKU_INC')) die();
?>

<!-- ********** FOOTER ********** -->
<footer id="dokuwiki__footer"><div class="pad">
    <div class="grid3">
        <div>
            <div class="header">Wiki</div>
            <a href="?do=index">Indeks</a>
            <a href="?do=recent">Ostatnie zmiany</a>
            <?php
            try {
                $loginItem = new \dokuwiki\Menu\Item\Login();
                echo $loginItem->asHtmlLink(false, false);
            } catch (\RuntimeException $ignored) {
            }
            ?>
        </div>
        <div>
            <div class="header">Warto wspierać</div>
            <a href="/patronite.html">Patronite</a>
            <a rel="external" target="_blank" href="https://agendaparkingowa.pl/">
                Agenda Parkingowa
            </a>
            <a rel="external" target="_blank" href="https://www.change.org/Rowne-Prawa-Dla-Pieszych-i-Kierowcow">
                Podpisz wniosek do RPO
            </a>
            <a rel="external" target="_blank" href="https://www.facebook.com/groups/patologiaparkingowa/">
                Grupa wsparcia na FB
            </a>
            <a href="https://suppi.pl/uprzejmiedonosze"
                target="_blank"
                rel="external">Jednorazowa wpłata</a>
            <a href="/naklejki-robisz-to-zle.html">Kup naklejki</a>
        </div>
        <div>
            <div class="header">O projekcie</div>
            <a href="https://patronite.pl/uprzejmiedonosze/posts" target="_blank" rel="external">
                Comiesięczna aktualizacja
            </a>
            <a href="https://x.com/SzymonNieradka" target="_blank" rel="external">
                Codzienne aktualizacje
            </a>
            <a href="/changelog.html">Historia zmian</a>
            <a href="/projekt.html">Dla programistów</a>
            <a href="/regulamin.html">Regulamin</a>
            <a href="/polityka-prywatnosci.html">Polityka prywatności</a>
            <a href="/bezpieczenstwo.html">Bezpieczeństwo</a>
            <a href="/kontakt.html">Kontakt</a>
        </div>

    </div>

    <?php tpl_includeFile('footer.html'); ?>
</div></footer><!-- /footer -->
