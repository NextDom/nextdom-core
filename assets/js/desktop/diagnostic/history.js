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

$('#bt_fullscreen').on('click',function(){
    var ele = document.getElementById('div_History');
    if (ele.requestFullscreen) {
        ele.requestFullscreen();
    } else if (ele.webkitRequestFullscreen) {
        ele.webkitRequestFullscreen();
    } else if (ele.mozRequestFullScreen) {
        ele.mozRequestFullScreen();
    } else if (ele.msRequestFullscreen) {
        ele.msRequestFullscreen();
    } else {
        console.log('Fullscreen API is not supported.');
    }
});

var noChart = 1;
var colorChart = 0;
var lastId = null;

$('#div_graph').css('height', $('#div_mainContainer').height()-325);

delete nextdom.history.chart['div_graph']

initHistoryTrigger();

$('#bt_findCmdCalculHistory').on('click',function(){
    nextdom.cmd.getSelectModal({cmd: {type: 'info',subType : 'numeric',isHistorized : 1}}, function(result) {
        $('#in_calculHistory').atCaret('insert', result.human);
    });
});

$('#bt_displayCalculHistory').on('click',function(){
    addChart($('#in_calculHistory').value(), 1)
});

$('#bt_configureCalculHistory').on('click',function(){
    $('#md_modal').dialog({title: "{{Configuration des formules de calcul}}"});
    $("#md_modal").load('index.php?v=d&modal=history.calcul').dialog('open');
});

$('#bt_clearGraph').on('click',function(){
    nextdom.history.chart['div_graph'].chart.data.datasets.splice(0, nextdom.history.chart['div_graph'].chart.data.datasets.length);
    nextdom.history.chart['div_graph'].chart.update();
    $('.li_history').removeClass('active');
    $('.fa-circle-o').css("color", "");
});

$(".in_datepicker").datepicker({dateFormat: "yy-mm-dd"});

$(".li_history .history").on('click', function (event) {
    var options = [];
    options.newGraph = true;
    if ($(this).closest('.li_history').hasClass('active')) {
        $(this).closest('.li_history').removeClass('active');
        $(this).children().css("color", "");

        addChart($(this).closest('.li_history').attr('data-cmd_id'), 0);
    } else {
        $(this).closest('.li_history').addClass('active');
        addChart($(this).closest('.li_history').attr('data-cmd_id'), 1, options.newGraph);
    }
    return false;
});

$("body").delegate("ul div input.filter", 'keyup', function () {
    if ($(this).value() == '') {
        $('.cmdList').hide();
    } else {
        $('.cmdList').show();
    }
});

$(".li_history .remove").on('click', function () {
    var bt_remove = $(this);
    $.hideAlert();
    bootbox.prompt('{{Veuillez indiquer la date (Y-m-d H:m:s) avant laquelle il faut supprimer l\'historique de }} <span style="font-weight: bold ;">' + bt_remove.closest('.li_history').find('.history').text() + '</span> (laissez vide pour tout supprimer) ?', function (result) {
        if (result !== null) {
            emptyHistory(bt_remove.closest('.li_history').attr('data-cmd_id'),result);
        }
    });
});

$('.displayObject').on('click', function () {
    var list = $('.cmdList[data-object_id=' + $(this).attr('data-object_id') + ']');
    if (list.is(':visible')) {
        list.hide();
    } else {
        list.show();
    }
});

$('#bt_openCmdHistoryConfigure').on('click',function(){
    $('#md_modal').dialog({title: "{{Configuration de l'historique des commandes}}"});
    $("#md_modal").load('index.php?v=d&modal=cmd.configureHistory').dialog('open');
});

function emptyHistory(_cmd_id,_date) {
    $.ajax({
        type: "POST",
        url: "core/ajax/cmd.ajax.php",
        data: {
            action: "emptyHistory",
            id: _cmd_id,
            date: _date
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                notify("Erreur", data.result, 'error');
                return;
            }
            notify("Info", '{{Historique supprimé avec succès}}', 'success');
            li = $('li[data-cmd_id=' + _cmd_id + ']');
            if (li.hasClass('active')) {
                li.find('.history').click();
            }
        }
    });
}

function initHistoryTrigger() {
    $('#sel_chartType').off('change').on('change', function () {
        nextdom.cmd.save({
            cmd: {id: lastId, display: {graphType: $(this).value()}},
            error: function (error) {
                notify("Erreur", error.message, 'error');
            },
            success: function () {
                $('.li_history[data-cmd_id=' + lastId + '] .history').click();
            }
        });
        $('.li_history[data-cmd_id=' + lastId + ']').removeClass('active');

        // Remove the old chart and all its event handles
        if (nextdom.history.chart['div_graph'].chart) {
            nextdom.history.chart['div_graph'].chart.destroy();
        }

        // Chart.js modifies the object you pass in. Pass a copy of the object so we can use the original object later
        var options =[];
        options.type = document.getElementById('sel_chartType').value;
        options.newGraph = true;
        if (options.type == "bar"){
            nextdom.history.chart['div_graph'].chart.options.scales.xAxes.categorySpacing = 0
        }
        addChart(lastId,1,options);
    });


}

$('#bt_validChangeDate').on('click',function(){
    var options = [];
    var ids= [];
    options.newGraph = false;

    if (isset(nextdom.history.chart['div_graph']) && isset(nextdom.history.chart['div_graph'].chart)) {
        for (var i = 0; i < nextdom.history.chart['div_graph'].chart.data.datasets.length; i++){
            ids[i] = nextdom.history.chart['div_graph'].chart.data.datasets[i].id;
            nextdom.history.chart['div_graph'].chart.data.datasets.splice(i, 1);
        }
        console.log(ids);
        for (var i = 0; i < ids.length; i++){
            addChart(ids[i],1,false);


        }
    }
});

function addChart(_cmd_id, _action, _options) {
    if (_action == 0) {
        if (isset(nextdom.history.chart['div_graph']) && isset(nextdom.history.chart['div_graph'].chart)) {
            for (var i = 0; i < nextdom.history.chart['div_graph'].chart.data.datasets.length; i++){
                try {
                    if(nextdom.history.chart['div_graph'].chart.data.datasets[i].id == _cmd_id){
                        nextdom.history.chart['div_graph'].chart.data.datasets.splice(i, 1);
                        nextdom.history.chart['div_graph'].chart.update();
                    }
                }catch(error) {
                }
            }
        }
        return;
    }
    lastId = _cmd_id;
    nextdom.history.drawChart({
        newGraph: _options.newGraph,
        cmd_id: _cmd_id,
        el: 'div_graph',
        dateRange : 'all',
        dateStart : $('#in_startDate').value(),
        dateEnd :  $('#in_endDate').value(),
        height : $('#div_graph').height(),
        type: _options.type ,
        success: function (data) {
            if(isset(data.cmd) && isset(data.cmd.display)){
                if (init(data.cmd.display.graphStep) != '') {
                    $('#cb_step').off().value(init(data.cmd.display.graphStep));
                }
                if (init(data.cmd.display.groupingType) != '') {
                    $('#sel_groupingType').off().value(init(data.cmd.display.groupingType));
                }

            }
            initHistoryTrigger();
        }
    });
}

$('#bt_smoothToggle').on('click',function(){
    var value = this.classList.toggle('btn-on');
    nextdom.history.chart['div_graph'].chart.options.elements.line.tension = value ?  0.15 : 0.000001;
    nextdom.history.chart['div_graph'].chart.update();
});

$('#bt_steppedToggle').on('click',function(){
    var value = this.classList.toggle('btn-on');

    for (var i = 0; i < nextdom.history.chart['div_graph'].chart.data.datasets.length; i++){
        nextdom.history.chart['div_graph'].chart.data.datasets[i].steppedLine = value ?  'before' : false;
    }
    nextdom.history.chart['div_graph'].chart.update();
});

$('#bt_areaToggle').on('click',function(){
    var value = this.classList.toggle('btn-on');

    for (var i = 0; i < nextdom.history.chart['div_graph'].chart.data.datasets.length; i++){
        nextdom.history.chart['div_graph'].chart.data.datasets[i].fill = value ?  'start' : false;
    }
    nextdom.history.chart['div_graph'].chart.update();
});

$('#bt_resetZoom').on('click',function(){
    nextdom.history.chart['div_graph'].chart.resetZoom();
});