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

initTableSorter();
$("#table_cmdConfigureHistory").tablesorter({headers: {0: {sorter: 'checkbox'}}});
table_history = [];
for (var i in cmds_history_configure) {
    table_history.push(addCommandHistory(cmds_history_configure[i]));
}
$('#table_cmdConfigureHistory tbody').empty().append(table_history);
$('#table_cmdConfigureHistory tbody tr').attr('data-change', '0');
$("#table_cmdConfigureHistory").trigger("update");
$("#table_cmdConfigureHistory").width('100%');

function addCommandHistory(_cmd) {
    var tr = '<tr data-cmd_id="' + _cmd.id + '">';
    tr += '<td>';
    if (_cmd.type == 'info') {
        tr += '<input type="checkbox" class="cmdAttr" data-l1key="isHistorized" />';
    }
    tr += '</td>';
    tr += '<td>';
    tr += '<input type="checkbox" class="cmdAttr" data-l1key="configuration" data-l2key="timeline::enable" />';
    tr += '</td>';
    tr += '<td>';
    tr += '<span class="cmdAttr">' + _cmd.type + ' / ' + _cmd.subType + '</span>';
    tr += '</td>';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
    tr += '<span class="cmdAttr" data-l1key="humanName"></span>';
    tr += '</td>';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="plugins"></span>';
    tr += '</td>';
    tr += '<td>';
    if (_cmd.type == 'info' && _cmd.subType == 'numeric') {
        tr += '<select class="form-control cmdAttr input-sm" data-l1key="configuration" data-l2key="historizeMode">';
        tr += '<option value="avg">{{ Moyenne }}</option>';
        tr += '<option value="min">{{ Minimum }}</option>';
        tr += '<option value="max">{{ Maximum }}</option>';
        tr += '<option value="none">{{ Aucun }}</option>';
        tr += '</select>';
    }
    tr += '</td>';
    tr += '<td>';
    if (_cmd.type == 'info') {
        tr += '<select class="form-control cmdAttr input-sm" data-l1key="configuration" data-l2key="historyPurge">';
        tr += '<option value="">{{ Jamais }}</option>';
        tr += '<option value="-1 day">{{ 1 jour }}</option>';
        tr += '<option value="-7 days">{{ 7 jours }}</option>';
        tr += '<option value="-1 month">{{ 1 mois }}</option>';
        tr += '<option value="-3 month">{{ 3 mois }}</option>';
        tr += '<option value="-6 month">{{ 6 mois }}</option>';
        tr += '<option value="-1 year">{{ 1 an }}</option>';
        tr += '</select>';
    }
    tr += '</td>';
    tr += '<td>';
    if (_cmd.type == 'info') {
        tr += '<a class="btn btn-default btn-sm pull-right cursor bt_configureHistoryExportData" data-id="' + _cmd.id + '"><i class="fas fa-share export no-spacing"></i></a>';
    }
    tr += '<a class="btn btn-default btn-sm pull-right cursor bt_configureHistoryAdvanceCmdConfiguration" data-id="' + _cmd.id + '"><i class="fas fa-cogs"></i></a>';
    tr += '</td>';
    tr += '</tr>';
    var result = $(tr);
    result.setValues(_cmd, '.cmdAttr');
    return result;
}


$('.bt_configureHistoryAdvanceCmdConfiguration').off('click').on('click', function () {
    $('#md_modal2').dialog({title: "{{ Configuration de la commande }}"});
    $('#md_modal2').load('index.php?v=d&modal=cmd.configure&cmd_id=' + $(this).attr('data-id')).dialog('open');
});

$(".bt_configureHistoryExportData").on('click', function () {
    window.open('core/php/export.php?type=cmdHistory&id=' + $(this).attr('data-id'), "_blank", null);
});

$('.cmdAttr').on('change click', function () {
    $(this).closest('tr').attr('data-change', '1');
});

$('#bt_cmdConfigureCmdHistoryApply').on('click', function () {
    var cmds = [];
    $('#table_cmdConfigureHistory tbody tr').each(function () {
        if ($(this).attr('data-change') == '1') {
            cmds.push($(this).getValues('.cmdAttr')[0]);
        }
    });
    nextdom.cmd.multiSave({
        cmds: cmds,
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            $("#table_cmdConfigureHistory").trigger("update");
            notify("Info", "{{ Opération effectuée }}", 'success');
        }
    });
});

$('#bt_canceltimeline').on('click', function () {
    $('.cmdAttr[data-l1key=configuration][data-l2key="timeline::enable"]:visible').each(function () {
        $(this).prop('checked', false);
        $(this).closest('tr').attr('data-change', '1');
    });
});

$('#bt_applytimeline').on('click', function () {
    $('.cmdAttr[data-l1key=configuration][data-l2key="timeline::enable"]:visible').each(function () {
        $(this).prop('checked', true);
        $(this).closest('tr').attr('data-change', '1');
    });
});
