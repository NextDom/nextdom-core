/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* This file is part of NextDom.
*
* NextDom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* NextDom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with NextDom. If not, see <http://www.gnu.org/licenses/>.
*/

$('#mod_actionValue_sel').on('change', function () {
    var value = $(this).value();
    if (value == 'alert') {
        value = 'alert2';
    }
    $('.mod_actionValue_selDescription').hide();
    $('.mod_actionValue_selDescription.' + value).show();
});

function mod_insertAction() {
}

mod_insertAction.options = {};

mod_insertAction.setOptions = function (_options) {
    mod_insertAction.options = _options;
    if (init(_options.scenario, false) == false) {
        $('#mod_actionValue_sel .scenarioOnly').hide();
    } else {
        $('#mod_actionValue_sel .scenarioOnly').show();
    }
};

mod_insertAction.getValue = function () {
    return $('#mod_actionValue_sel').value();
};
