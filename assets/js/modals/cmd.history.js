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

$(".in_datepicker").datepicker({dateFormat: "yy-mm-dd"});
$('#ui-datepicker-div').hide();

$('#div_historyChart').css('height', $('#div_historyChart').closest('.ui-dialog-content').height()-100);

delete nextdom.history.chart['div_historyChart'];

initHistoryTrigger();
addChart(historyId);

function addChart(_cmd_id) {
    $('#alertGraph').hide();
    if (isset(nextdom.history.chart['div_historyChart']) && isset(nextdom.history.chart['div_historyChart'].chart) && isset(nextdom.history.chart['div_historyChart'].chart.series)) {
        $(nextdom.history.chart['div_historyChart'].chart.series).each(function(i, serie){
            try {
                if(serie.options.id == _cmd_id){
                    serie.remove();
                }
            }catch(error) {
            }
        });
    }
    nextdom.cmd.save({
        cmd: {id: historyId},
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            nextdom.history.drawChart({
                cmd_id: _cmd_id,
                el: 'div_historyChart',
                dateRange: 'all',
                dateStart: $('#in_startDate').value(),
                dateEnd: $('#in_endDate').value(),
                height :$('#div_historyChart').height(),
                success: function (data) {
                    if (isset(data.cmd.display)) {
                        if (init(data.cmd.display.graphStep) != '') {
                            $('#cb_step').off().value(init(data.cmd.display.graphStep));
                        }
                        if (init(data.cmd.display.groupingType) != '') {
                            $('#sel_groupingType').off().value(init(data.cmd.display.groupingType));
                        }
                        if (init(data.cmd.display.graphType) != '') {
                            $('#sel_chartType').off().value(init(data.cmd.display.graphType));
                        }
                        if (init(data.cmd.display.graphDerive) != '') {
                            $('#cb_derive').off().value(init(data.cmd.display.graphDerive));
                        }
                    }
                    initHistoryTrigger();
                }
            });
        }
    });
}

function initHistoryTrigger() {
    $('#sel_chartType').on('change', function () {
        addChart(historyId);
        nextdom.cmd.save({
            cmd: {id: historyId, display: {graphType: $(this).value()}},
            error: function (error) {
                notify("Erreur", error.message, 'error');
            }
        });
    });

    $('#sel_groupingType').on('change', function () {
        addChart(historyId);
        nextdom.cmd.save({
            cmd: {id: historyId, display: {groupingType: $(this).value()}},
            error: function (error) {
                notify("Erreur", error.message, 'error');
            }
        });
    });

    $('#cb_derive').on('change', function () {
        addChart(historyId);
        nextdom.cmd.save({
            cmd: {id: historyId, display: {graphDerive: $(this).value()}},
            error: function (error) {
                notify("Erreur", error.message, 'error');
            }
        });
    });

    $('#cb_step').on('change', function () {
        addChart(historyId);
        nextdom.cmd.save({
            cmd: {id: historyId, display: {graphStep: $(this).value()}},
            error: function (error) {
                notify("Erreur", error.message, 'error');
            }
        });
    });
}

$('#bt_validChangeDate').on('click', function () {
    delete nextdom.history.chart['div_historyChart'];
    addChart(historyId);
    nextdom.cmd.save({
        cmd: {id: historyId},
        error: function (error) {
            notify("Erreur", error.message, 'error');
        }
    });
});
