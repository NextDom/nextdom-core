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

function mod_insertCmd() {
}

mod_insertCmd.options = {};
mod_insertCmd.options.cmd = {};
mod_insertCmd.options.eqLogic = {};
mod_insertCmd.options.object = {};


$("#table_mod_insertCmdValue_valueEqLogicToMessage").delegate("td.mod_insertCmdValue_object select", 'change', function () {
    mod_insertCmd.changeObjectCmd($('#table_mod_insertCmdValue_valueEqLogicToMessage td.mod_insertCmdValue_object select'), mod_insertCmd.options);
});

mod_insertCmd.setOptions = function (_options) {
    mod_insertCmd.options = _options;
    if (!isset(mod_insertCmd.options.cmd)) {
        mod_insertCmd.options.cmd = {};
    }
    if (!isset(mod_insertCmd.options.eqLogic)) {
        mod_insertCmd.options.eqLogic = {};
    }
    if (!isset(mod_insertCmd.options.object)) {
        mod_insertCmd.options.object = {};
    }
    if (isset(mod_insertCmd.options.object.id)) {
        $('#table_mod_insertCmdValue_valueEqLogicToMessage td.mod_insertCmdValue_object select').value(mod_insertCmd.options.object.id);
    }
    mod_insertCmd.changeObjectCmd($('#table_mod_insertCmdValue_valueEqLogicToMessage td.mod_insertCmdValue_object select'), mod_insertCmd.options);
};

mod_insertCmd.getValue = function () {
    var object_name = $('#table_mod_insertCmdValue_valueEqLogicToMessage tbody tr:first .mod_insertCmdValue_object select option:selected').html();
    var equipement_name = $('#table_mod_insertCmdValue_valueEqLogicToMessage tbody tr:first .mod_insertCmdValue_eqLogic select option:selected').html();
    var cmd_name = $('#table_mod_insertCmdValue_valueEqLogicToMessage tbody tr:first .mod_insertCmdValue_cmd select option:selected').html();
    if (cmd_name == undefined) {
        return '';
    }
    return '#[' + object_name + '][' + equipement_name + '][' + cmd_name + ']#';
};

mod_insertCmd.getCmdId = function () {
    return $('#table_mod_insertCmdValue_valueEqLogicToMessage tbody tr:first .mod_insertCmdValue_cmd select').value();
};

mod_insertCmd.getType = function () {
    return $('#table_mod_insertCmdValue_valueEqLogicToMessage tbody tr:first .mod_insertCmdValue_cmd select option:selected').attr('data-type');
};

mod_insertCmd.getSubType = function () {
    return $('#table_mod_insertCmdValue_valueEqLogicToMessage tbody tr:first .mod_insertCmdValue_cmd select option:selected').attr('data-subType');
};

mod_insertCmd.changeObjectCmd = function (_select) {
    nextdom.object.getEqLogic({
        id: _select.value(),
        orderByName: true,
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function (eqLogics) {
            _select.closest('tr').find('.mod_insertCmdValue_eqLogic').empty();
            var selectEqLogic = '<select class="form-control">';
            for (var i in eqLogics) {
                if (init(mod_insertCmd.options.eqLogic.eqType_name, 'all') == 'all' || eqLogics[i].eqType_name == mod_insertCmd.options.eqLogic.eqType_name) {
                    selectEqLogic += '<option value="' + eqLogics[i].id + '">' + eqLogics[i].name + '</option>';
                }
            }
            selectEqLogic += '</select>';
            _select.closest('tr').find('.mod_insertCmdValue_eqLogic').append(selectEqLogic);
            _select.closest('tr').find('.mod_insertCmdValue_eqLogic select').change(function () {
                mod_insertCmd.changeEqLogic($(this), mod_insertCmd.options);
            });
            if (isset(mod_insertCmd.options.object.id)) {
                _select.closest('tr').find('.mod_insertCmdValue_eqLogic select').value(mod_insertCmd.options.eqLogic.id);
            }
            mod_insertCmd.changeEqLogic(_select.closest('tr').find('.mod_insertCmdValue_eqLogic select'), mod_insertCmd.options);
        }
    });

};

mod_insertCmd.changeEqLogic = function (_select) {
    nextdom.eqLogic.builSelectCmd({
        id: _select.value(),
        filter: mod_insertCmd.options.cmd,
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function (html) {
            _select.closest('tr').find('.mod_insertCmdValue_cmd').empty();
            var selectCmd = '<select class="form-control">';
            selectCmd += html;
            selectCmd += '</select>';
            _select.closest('tr').find('.mod_insertCmdValue_cmd').append(selectCmd);
        }
    });
};

mod_insertCmd.changeObjectCmd($('#table_mod_insertCmdValue_valueEqLogicToMessage td.mod_insertCmdValue_object select'), mod_insertCmd.options);
