/**
 * Simplyfies AJAX requests for types
 *
 * @param {string} column A configured column in the form schema.name
 * @param {function} fn Callback on success
 * @param {object} data Additional data to pass
 */
function struct_ajax(column, fn, data) {
    if (!data) data = {};

    data['call'] = 'plugin_struct';
    data['column'] = column;
    data['id'] = JSINFO.id;
    data['ns'] = JSINFO.namespace;

    jQuery.post(DOKU_BASE + 'lib/exe/ajax.php', data, fn, 'json')
        .fail(function (result) {
            if (result.responseJSON) {
                if (result.responseJSON.stacktrace) {
                    console.error(result.responseJSON.error + "\n" + result.responseJSON.stacktrace);
                }
                alert(result.responseJSON.error);
            } else {
                // some fatal error occured, get a text only version of the response
                alert(jQuery(result.responseText).text());
            }
        });
}

/**
 * @param {string} val
 * @return {Array}
 */
function split(val) {
    return val.split(/,\s*/);
}

/**
 * @param {string} term
 * @returns {string}
 */
function extractLast(term) {
    return split(term).pop();
}


/**
 * Replace numbered placeholders in a string with the given arguments
 *
 * Example formatString('{0} is dead, but {1} is alive! {0} {2}', 'ASP', 'ASP.NET');
 *
 * adapted from http://stackoverflow.com/a/4673436/3293343
 * @param format
 * @returns {*}
 */
function formatString(format) {
    var args = Array.prototype.slice.call(arguments, 1);
    return format.replace(/{(\d+)}/g, function (match, number) {
        return typeof args[number] != 'undefined'
            ? args[number]
            : match
            ;
    });
}

/**
 * Custom onSelect handler for struct img button
 */
window.insertStructMedia = function (edid, mediaid, opts, align) {
    jQuery('#' + edid).val(mediaid).change();
};
