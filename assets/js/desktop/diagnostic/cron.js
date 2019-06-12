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

printCron();
printListener();

$("#bt_refreshCron").on('click', function () {
    printCron();
    printListener();
});

$("#bt_addCron").on('click', function () {
    $('#table_cron tbody').append(addCron({}));
});

jwerty.key('ctrl+s/⌘+s', function (e) {
    e.preventDefault();
    $("#bt_save").click();
});

$("#bt_save").on('click', function () {
    nextdom.cron.save({
        crons: $('#table_cron tbody tr').getValues('.cronAttr'),
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: printCron
    });
});

$("#bt_changeCronState").on('click', function () {
    var el = $(this);
    nextdom.config.save({
        configuration: {enableCron: el.attr('data-state')},
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            if (el.attr('data-state') == 1) {
                el.removeClass('btn-success').addClass('btn-danger').attr('data-state', 0);
                el.empty().html('<i class="fas fa-times"></i> {{Désactiver le système cron}}');
            } else {
                el.removeClass('btn-danger').addClass('btn-success').attr('data-state', 1);
                el.empty().html('<i class="fas fa-check"></i> {{Activer le système cron}}</a>');
            }
        }
    });
});

$("#table_cron").delegate(".remove", 'click', function () {
    $(this).closest('tr').remove();
});

$("#table_cron").delegate(".stop", 'click', function () {
    nextdom.cron.setState({
        state: 'stop',
        id: $(this).closest('tr').attr('id'),
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: printCron
    });
});

$("#table_cron").delegate(".start", 'click', function () {
    nextdom.cron.setState({
        state: 'start',
        id: $(this).closest('tr').attr('id'),
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: printCron
    });
});

$("#table_cron").delegate(".display", 'click', function () {
    $('#md_modal').dialog({title: "{{Détails du cron}}"});
    $("#md_modal").load('index.php?v=d&modal=object.display&class=cron&id='+$(this).closest('tr').attr('id')).dialog('open');
});

$("#table_listener").delegate(".display", 'click', function () {
    $('#md_modal').dialog({title: "{{Détails du listener}}"});
    $("#md_modal").load('index.php?v=d&modal=object.display&class=listener&id='+$(this).closest('tr').attr('id')).dialog('open');
});

$('#table_cron').delegate('.cronAttr[data-l1key=deamon]', 'change', function () {
    if ($(this).value() == 1) {
        $(this).closest('tr').find('.cronAttr[data-l1key=deamonSleepTime]').show();
    } else {
        $(this).closest('tr').find('.cronAttr[data-l1key=deamonSleepTime]').hide();
    }
});

$('#div_pageContainer').delegate('.cronAttr', 'change', function () {
    modifyWithoutSave = true;
});

function printCron() {
    showLoadingCustom();
    nextdom.cron.all({
        success: function (data) {
            showLoadingCustom();
            $('#table_cron tbody').empty();
            var tr = [];
            for (var i in data) {
                tr.push(addCron(data[i]));
            }
            $('#table_cron tbody').append(tr);
            modifyWithoutSave = false;
            hideLoadingCustom();
        }
    });
}

function addCron(_cron) {
    $.hideAlert();
    var disabled ='';
    if(init(_cron.deamon) == 1){
        disabled ='disabled';
    }
    var tr = '<tr id="' + init(_cron.id) + '">';
    tr += '<td class="option"><span class="cronAttr" data-l1key="id"></span></td>';
    tr += '<td>';
    if(init(_cron.id) != ''){
        tr += '<a class="btn btn-default btn-sm display"><i class="fas fa-file no-spacing"></i></a> ';
    }
    if(init(_cron.deamon) == 0){
        if (init(_cron.state) == 'run') {
            tr += ' <a class="btn btn-danger btn-sm stop"><i class="fas fa-stop no-spacing"></i></a>';
        }
        if (init(_cron.state) != '' && init(_cron.state) != 'starting' && init(_cron.state) != 'run' && init(_cron.state) != 'stoping') {
            tr += ' <a class="btn btn-success btn-sm start"><i class="fas fa-play no-spacing"></i></a>';
        }
    }
    tr += '</td>';
    tr += '<td class="enable"><center>';
    tr += '<input type="checkbox"class="cronAttr" data-l1key="enable" checked '+disabled+'/>';
    tr += '</center></td>';
    tr += '<td>';
    tr += init(_cron.pid);
    tr += '</td>';
    tr += '<td class="deamons">';
    tr += '<input type="checkbox" class="cronAttr" data-l1key="deamon" '+disabled+' /></span> ';
    tr += '<input class="cronAttr form-control input-sm" data-l1key="deamonSleepTime" style="width : 50px; display : inline-block;" />';
    tr += '</td>';
    tr += '<td class="once">';
    if(init(_cron.deamon) == 0){
        tr += '<input type="checkbox" class="cronAttr" data-l1key="once" /></span> ';
    }
    tr += '</td>';
    tr += '<td class="class"><input class="form-control cronAttr input-sm" data-l1key="class" '+disabled+' /></td>';
    tr += '<td class="function"><input class="form-control cronAttr input-sm" data-l1key="function" '+disabled+' /></td>';
    tr += '<td class="schedule"><input class="cronAttr form-control input-sm" data-l1key="schedule" '+disabled+' /></td>';
    tr += '<td class="function">';
    if(init(_cron.deamon) == 0){
        tr += '<input class="form-control cronAttr input-sm" data-l1key="timeout" />';
    }
    tr += '</td>';
    tr += '<td class="lastRun">';
    tr += init(_cron.lastRun);
    tr += '</td>';
    tr += '<td class="runtime">';
    tr += init(_cron.runtime,'0')+'s';
    tr += '</td>';
    tr += '<td class="state">';
    var label = 'label label-info';
    if (init(_cron.state) == 'run') {
        label = 'label label-success';
    }
    if (init(_cron.state) == 'stop') {
        label = 'label label-danger';
    }
    if (init(_cron.state) == 'starting') {
        label = 'label label-warning';
    }
    if (init(_cron.state) == 'stoping') {
        label = 'label label-warning';
    }
    tr += '<span class="' + label + ' label-sticker">' + init(_cron.state) + '</span>';
    tr += '</td>';
    tr += '<td class="action">';
    tr += '<a class="btn btn-danger btn-sm"><i class="fas fa-minus-circle remove cursor no-spacing"></i></a>';
    tr += '</td>';
    tr += '</tr>';
    var result = $(tr);
    result.setValues(_cron, '.cronAttr');
    return result;
}


function printListener() {
    showLoadingCustom();
    nextdom.listener.all({
        success: function (data) {
            showLoadingCustom();
            $('#table_listener tbody').empty();
            var tr = [];
            for (var i in data) {
                tr.push(addListener(data[i]));
            }
            $('#table_listener tbody').append(tr);
            modifyWithoutSave = false;
            hideLoadingCustom();
        }
    });
}


function addListener(_listener) {
    $.hideAlert();
    var disabled ='';
    var tr = '<tr id="' + init(_listener.id) + '">';
    tr += '<td class="option"><span class="listenerAttr" data-l1key="id"></span></td>';
    tr += '<td>';
    if(init(_listener.id) != ''){
        tr += '<a class="btn btn-default btn-sm display"><i class="fas fa-file no-spacing"></i></a> ';
    }
    tr += '</td>';
    tr += '<td><textarea class="form-control listenerAttr input-sm" data-l1key="event_str" disabled ></textarea></td>';
    tr += '<td><input class="form-control listenerAttr input-sm" data-l1key="class" disabled /></td>';
    tr += '<td><input class="form-control listenerAttr input-sm" data-l1key="function" disabled /></td>';
    tr += '</tr>';
    var result = $(tr);
    result.setValues(_listener, '.listenerAttr');
    return result;
}
