/**
 * Copy to a JavaScript console on your DokuWiki instance and execute
 * Runs JSLint on all our JavaScript files with our settings
 */

(function () {
var globals = ['jQuery', 'SIG', 'NS', 'JSINFO', 'LANG', 'DOKU_BASE',
               'DOKU_UHC' // FIXME: Should be moved to JSINFO
    ], files = {
        'scripts/behaviour.js': null,
        //"scripts/compatibility.js": null,
        "scripts/cookie.js": null,
        //"scripts/delay.js": null,
        //"scripts/drag.js": null,
        "scripts/edit.js": null,
        "scripts/editor.js": null,
        "scripts/helpers.js": null,
        "scripts/hotkeys.js": null,
        "scripts/index.js": null,
        "scripts/linkwiz.js": null,
        "scripts/locktimer.js": null,
        "scripts/media.js": null,
        "scripts/page.js": null,
        "scripts/qsearch.js": null,
        "scripts/script.js": null,
        "scripts/textselection.js": null,
        "scripts/toolbar.js": null,
        "scripts/tree.js": null //,
        //"scripts/tw-sack.js": null
    }, overwrites = {
        "scripts/script.js": {evil: true},
        "scripts/media.js": {devel: true, windows: true},
        "scripts/locktimer.js": {devel: true},
        "scripts/behaviour.js": {devel: true},
        "scripts/helpers.js": {windows: true}
    };

jQuery.ajax({
    dataType: 'script',
    type: "GET",
//  url: 'http://jshint.com/jshint.js'
    url: 'https://raw.github.com/douglascrockford/JSLint/master/jslint.js',
    success: function () {
        for (var file in files) {
            jQuery.ajax({
                cache: false,
                async: false,
                type: "GET",
                url: DOKU_BASE + 'lib/' + file,
                dataType: 'text',
                success: function (res) {
                    files[file] = res;
                    var data = lint(files[file]);
                    jQuery.merge(globals, data.globals);
            }});
        }

        for (var file in files) {
            if (!files[file]) {
                continue;
            }
            // FIXME more fine-grained write access
            var data = lint('/*global ' + globals.join(':true, ') +
                            ':true*/\n' + files[file], overwrites[file]);
            console.log(file);
            jQuery.each(data.errors || [], function (_, val) {
                if (val === null) {
                    return;
                }
                console.error(val.reason + ' (Line ' + (val.line - 1) +
                              ', character ' + val.character + '):\n' +
                              val.evidence);
            });
        };
    }
});

function lint(txt, overwrite) {
    JSLINT(txt, jQuery.extend({
        // These settings are necessary
        browser: true,

        // Things we probably should learn someday
        sloppy: true, white: true, eqeq: true, nomen: true,
        plusplus: true, regexp: true
    }, overwrite));
    return JSLINT.data();
}
})();
