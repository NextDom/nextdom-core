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

var chart;
var noChart = 1;
var colorChart = 0;
var lastId = null;

// Page init
loadInformations();
initEvents();
initHistoryTrigger();

/**
 * Load informations in all forms of the page
 */
function loadInformations() {
    // Height update
    $('#div_graph').css('height', $('#div_mainContainer').height()-325);
    // Remove graphs
    delete nextdom.history.chart['div_graph']
}

/**
 * Init events on the profils page
 */
function initEvents() {
    // Full screen button
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

    $('#bt_clearGraph').on('click',function(){
        nextdom.history.chart['div_graph'].chart.data.datasets.splice(0, nextdom.history.chart['div_graph'].chart.data.datasets.length);
        nextdom.history.chart['div_graph'].chart.update();
        $('.li_history').removeClass('active');
        $('.fa-circle-o').css("color", "");
    });

    // Add / remove graph from list
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

    // Delete history of cmd
    $(".li_history .remove").on('click', function () {
        var bt_remove = $(this);
        bootbox.prompt('{{Veuillez indiquer la date (Y-m-d H:m:s) avant laquelle il faut supprimer l\'historique de }} <span style="font-weight: bold ;">' + bt_remove.closest('.li_history').find('.history').text() + '</span> (laissez vide pour tout supprimer) ?', function (result) {
            if (result !== null) {
                emptyHistory(bt_remove.closest('.li_history').attr('data-cmd_id'),result);
            }
        });
    });

    // Panel collapsing / uncollasping
    $('.displayObject').on('click', function () {
        var list = $('.cmdList[data-object_id=' + $(this).attr('data-object_id') + ']');
        if (list.is(':visible')) {
            list.hide();
        } else {
            list.show();
        }
    });

    // Export history
    $(".li_history .export").on('click', function () {
        window.open('core/php/export.php?type=cmdHistory&id=' + $(this).closest('.li_history').attr('data-cmd_id'), "_blank", null);
    });

    // Configure history
    $('#bt_openCmdHistoryConfigure').on('click',function(){
        $('#md_modal').dialog({title: "{{Configuration de l'historique des commandes}}"});
        $("#md_modal").load('index.php?v=d&modal=cmd.configureHistory').dialog('open');
    });

    $('#bt_stackToggle').on('click',function(){
        var options = [];
        var ids= [];
        value = ($(this).value() == 1) ? true : false;
        options.stack = value;

        if (isset(nextdom.history.chart['div_graph']) && isset(nextdom.history.chart['div_graph'].chart)) {
            for (var i = 0; i < nextdom.history.chart['div_graph'].chart.data.datasets.length; i++){
                ids[i] = nextdom.history.chart['div_graph'].chart.data.datasets[i].id;
            }

        }
        nextdom.history.chart['div_graph'].chart.destroy();
        for (var i = 0; i < ids.length; i++){
            if (i == 0 ){
                options.newGraph = true;
                addChart(ids[i],1,options);
            }else{
                options.newGraph = false;
                addChart(ids[i],1,options);
            }


        }
    });

    $('#bt_resetZoom').on('click',function(){
        nextdom.history.chart['div_graph'].chart.resetZoom();
    });
}

/**
 * Clear an history
 *
 * @param _cmd_id Id of cmd to empty chart data
 * @param _date Date limit until now range to empty
 */
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

/**
 * Init history events on the profils page
 */
function initHistoryTrigger() {
    // Chart type change
    $('#sel_chartType').off('change').on('change', function () {
        nextdom.cmd.save({
            cmd: {id: lastId, display: {graphType: $(this).value()}},
            error: function (error) {
                notify("Erreur", error.message, 'error');
            },
            success: function () {
                if (nextdom.history.chart['div_graph'].chart) {
                    nextdom.history.chart['div_graph'].chart.destroy();
                }
                var options =[];
                options.type = document.getElementById('sel_chartType').value;
                options.newGraph = true;
                addChart(lastId,1,options);
            }
        });
    });

    $('#bt_areaToggle').on('click',function(){
        nextdom.cmd.save({
            cmd: {id: lastId, display: {graphArea: $(this).value()}},
            error: function (error) {
                notify("Erreur", error.message, 'error');
            },
            success: function () {


            }
        });
        value = ($(this).value() == 1) ? 1 : 0;

        for (var i = 0; i < nextdom.history.chart['div_graph'].chart.data.datasets.length; i++){
            nextdom.history.chart['div_graph'].chart.data.datasets[i].fill =  value ?  'start' : false;
        }
        nextdom.history.chart['div_graph'].chart.update();
    });

    $('#bt_smoothToggle').on('click',function(){
        nextdom.cmd.save({
            cmd: {id: lastId, display: {graphSmooth: $(this).value()}},
            error: function (error) {
                notify("Erreur", error.message, 'error');
            },
            success: function () {


            }
        });
        value = ($(this).value() == 1) ? 1 : 0;

        nextdom.history.chart['div_graph'].chart.options.elements.line.tension =  value ?  0.15 : 0.000001;

        nextdom.history.chart['div_graph'].chart.update();
    });

    $('#bt_steppedToggle').on('click',function(){
        nextdom.cmd.save({
            cmd: {id: lastId, display: {graphStep: $(this).value()}},
            error: function (error) {
                notify("Erreur", error.message, 'error');
            },
            success: function () {


            }
        });
        value = ($(this).value() == 1) ? 1 : 0;

        for (var i = 0; i < nextdom.history.chart['div_graph'].chart.data.datasets.length; i++){
            nextdom.history.chart['div_graph'].chart.data.datasets[i].steppedLine = value ?  true : false;
        }

        nextdom.history.chart['div_graph'].chart.update();
    });
}

/**
 * Add a chart
 *
 * @param _cmd_id Id of cmd to display chart
 * @param _action Action id : 0=remove
 * @param _options Draw options
 */
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
        stack: _options.stack ,
        success: function (data) {
            if(isset(data.cmd) && isset(data.cmd.display)){
                if (init(data.cmd.display.graphType) != '') {
                    $('#sel_chartType').off().value(init(data.cmd.display.graphType));
                }
                if (init(data.cmd.display.graphArea) != '') {
                    $('#bt_areaToggle').off().value(init(data.cmd.display.graphArea));
                }
                if (init(data.cmd.display.graphStep) != '') {
                    $('#bt_steppedToggle').off().value(init(data.cmd.display.graphStep));
                }
                if (init(data.cmd.display.graphSmooth) != '') {
                    $('#bt_smoothToggle').off().value(init(data.cmd.display.graphSmooth));
                }

                if (init(data.cmd.display.graphType) == "bar"){
                    nextdom.history.chart['div_graph'].chart.options.scales.xAxes.categorySpacing = 0
                    $('.chart-options').hide();
                }else{
                    $('.chart-options').show();
                }


            }
            initHistoryTrigger();
        }

    });
}