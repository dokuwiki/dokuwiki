/**
 * Hide list subscription style if target is a page
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */

addInitEvent(function () {
    var form = $('subscribe__form');
    if (!form) {
        return;
    }

    var styleradios = {};

    function update_state() {
        if (!this.checked) {
            return;
        }
        if (this.value.match(/:$/)) {
            styleradios.list.parentNode.style.display = '';
        } else {
            styleradios.list.parentNode.style.display = 'none';
            if (styleradios.list.checked) {
                styleradios.digest.checked = 'checked';
            }
        }
    }

    var cur_sel = null;

    var inputs = form.getElementsByTagName('input');
    for (var i = 0; i < inputs.length ; ++i) {
        switch (inputs[i].name) {
        case 'sub_target':
            addEvent(inputs[i], 'click', update_state);
            if (inputs[i].checked) {
                cur_sel = inputs[i];
            }
            break;
        case 'sub_style':
            styleradios[inputs[i].value] = inputs[i];
            break;
        }
    }
    update_state.call(cur_sel);
});
