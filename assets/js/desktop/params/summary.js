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
*
* @Support <https://www.nextdom.org>
* @Email   <admin@nextdom.org>
* @Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
*/

// Page init
printObjectSummary();
initEvents();

/**
 * Init events on the profils page
 */
function initEvents() {
    // Param changed : page leaving lock by msgbox
    $('#summary').delegate('.objectSummaryAttr', 'change', function () {
        if (!lockModify) {
            modifyWithoutSave = true;
            $(".bt_cancelModifs").show();
        }
    });

    // Cancel modifications
    $('.bt_cancelModifs').on('click', function () {
        printObjectSummary();
    });

    // Save button
    $("#bt_savesummary").on('click', function (event) {
        saveObjectSummary();
    });

    // add button
    $('#bt_addObjectSummary').on('click', function () {
        addObjectSummary();
    });

    // Delete handlers on delete summary
    $('#summary').undelegate('.objectSummary .objectSummaryAction[data-l1key=chooseIcon]', 'click').delegate('.objectSummary .objectSummaryAction[data-l1key=chooseIcon]', 'click', function () {
        var objectSummary = $(this).closest('.objectSummary');
        chooseIcon(function (_icon) {
            objectSummary.find('.objectSummaryAttr[data-l1key=icon]').empty().append(_icon);
        });
        modifyWithoutSave = true;
        $(".bt_cancelModifs").show();
    });

    $('#summary').undelegate('.objectSummary .objectSummaryAction[data-l1key=remove]', 'click').delegate('.objectSummary .objectSummaryAction[data-l1key=remove]', 'click', function () {
        $(this).closest('.objectSummary').remove();
        modifyWithoutSave = true;
        $(".bt_cancelModifs").show();
    });

    $('#summary').undelegate('.objectSummary .objectSummaryAction[data-l1key=createVirtual]', 'click').delegate('.objectSummary .objectSummaryAction[data-l1key=createVirtual]', 'click', function () {
        var objectSummary = $(this).closest('.objectSummary');
        $.ajax({
            type: "POST",
            url: "src/ajax.php",
            data: {
                target: 'Object',
                action: "createSummaryVirtual",
                key: objectSummary.find('.objectSummaryAttr[data-l1key=key]').value()
            },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) {
                if (data.state != 'ok') {
                    notify('Erreur', data.result, 'error');
                    return;
                }
                notify('Info', '{{summary.virtual_cmd_succed}}', 'success');
            }
        });
    });

    // Init table of summary
    $("#table_objectSummary").sortable({axis: "y", cursor: "move", items: ".objectSummary", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
}

/**
 * Display all summary
 */
function printObjectSummary() {
    $.ajax({
        type: "POST",
        url: "src/ajax.php",
        data: {
            target: 'Config',
            action: "getKey",
            key: 'object:summary'
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                notify('Erreur', data.result, 'error');
                return;
            }
            $('#table_objectSummary tbody').empty();
            for (var i in data.result) {
              if(isset(data.result[i].key) && data.result[i].key == ''){
                continue;
              }
              if(!isset(data.result[i].name)){
                  continue;
              }
              if(!isset(data.result[i].key)){
                  data.result[i].key = i.toLowerCase().stripAccents().replace(/\_/g, '').replace(/\-/g, '').replace(/\&/g, '').replace(/\s/g, '');
              }
              addObjectSummary(data.result[i]);
            }
            modifyWithoutSave = false;
            $(".bt_cancelModifs").hide();
        }
    });
}

/**
 * Add a summary
 *
 * @param _summary Summary object, null to creat one
 */
function addObjectSummary(_summary) {
    var tr = '<tr class="objectSummary">';
    tr += '<td>';
    tr += '<input class="objectSummaryAttr form-control input-sm" data-l1key="key" />';
    tr += '</td>';
    tr += '<td>';
    tr += '<input class="objectSummaryAttr form-control input-sm" data-l1key="name" />';
    tr += '</td>';
    tr += '<td>';
    tr += '<select class="objectSummaryAttr form-control input-sm" data-l1key="calcul">';
    tr += '<option value="sum">{{summary.calcul.sum}}</option>';
    tr += '<option value="avg">{{summary.calcul.avg}}</option>';
    tr += '<option value="text">{{summary.calcul.text}}</option>';
    tr += '</select>';
    tr += '</td>';
    tr += '<td class="col-xs-1 input-group">';
    tr += '<a class="objectSummaryAction btn btn-action" data-l1key="chooseIcon"><i class="fas fa-plus"></i></a>';
    tr += '<span class="label label-icon objectSummaryAttr" data-l1key="icon"></span>';
    tr += '</td>';
    tr += '<td>';
    tr += '<input class="objectSummaryAttr form-control input-sm" data-l1key="unit" />';
    tr += '</td>';
    tr += '<td>';
    tr += '<select class="objectSummaryAttr form-control input-sm" data-l1key="count">';
    tr += '<option value="">{{Aucun}}</option>';
    tr += '<option value="binary">{{Binaire}}</option>';
    tr += '</select>';
    tr += '</td>';
    tr += '<td>';
    tr += '<input type="checkbox" class="objectSummaryAttr" data-l1key="allowDisplayZero"/>';
    tr += '</td>';
    tr += '<td>';
    if(isset(_summary) && isset(_summary.key) && _summary.key != ''){
        tr += '<a class="btn btn-success btn-sm objectSummaryAction" data-l1key="createVirtual"><i class="fas fa-puzzle-piece"></i>{{summary.create_virtual}}</a>';
    }
    tr += '</td>';
    tr += '<td>';
    tr += '<a class="objectSummaryAction btn btn-sm btn-danger" data-l1key="remove"><i class="fas fa-minus-circle no-spacing"></i></a>';
    tr += '</td>';
    tr += '</tr>';
    $('#table_objectSummary tbody').append(tr);
    if (isset(_summary)){
        $('#table_objectSummary tbody tr:last').setValues(_summary, '.objectSummaryAttr');
    }
    if(isset(_summary) && isset(_summary.key) && _summary.key != ''){
        $('#table_objectSummary tbody tr:last .objectSummaryAttr[data-l1key=key]').attr('disabled','disabled');
    }
    modifyWithoutSave = true;
    $(".bt_cancelModifs").show();
}

/**
 * Save all summary
 */
function saveObjectSummary() {
    summary = {};
    temp = $('#table_objectSummary tbody tr').getValues('.objectSummaryAttr');
    for(var i in temp){
        temp[i].key = temp[i].key.toLowerCase().stripAccents().replace(/\_/g, '').replace(/\-/g, '').replace(/\&/g, '').replace(/\s/g, '')
        if(temp[i].key == ''){
            temp[i].key = temp[i].name.toLowerCase().stripAccents().replace(/\_/g, '').replace(/\-/g, '').replace(/\&/g, '').replace(/\s/g, '')
        }
        summary[temp[i].key] = temp[i]
    }
    value = {'object:summary' : summary};
    $.ajax({
        type: "POST",
        url: "src/ajax.php",
        data: {
            target: 'Config',
            action: 'addKey',
            value: json_encode(value)
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                notify('Erreur', data.result, 'error');
                return;
            }
            printObjectSummary();
            modifyWithoutSave = false;
            $(".bt_cancelModifs").hide();
            notify('Info', '{{Sauvegarde réussie}}', 'success');
        }
    });
}
