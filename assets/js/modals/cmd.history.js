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

$('#divHightcharts').css('height', $('#divHightcharts').closest('.ui-dialog-content').height()-100);

delete nextdom.history.chart['divHightcharts'];

initHistoryTrigger();
var options = [];
options.newGraph = true;
addChart(historyId, 1, options);

function addChart(_cmd_id, _action, _options) {
    if (_action == 0) {
        if (isset(nextdom.history.chart['divHightcharts']) && isset(nextdom.history.chart['divHightcharts'].chart)) {
            for (var i = 0; i < nextdom.history.chart['divHightcharts'].chart.data.datasets.length; i++){
                try {
                    if(nextdom.history.chart['divHightcharts'].chart.data.datasets[i].id == _cmd_id){
                        nextdom.history.chart['divHightcharts'].chart.data.datasets.splice(i, 1);
                        nextdom.history.chart['divHightcharts'].chart.update();
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
        el: 'divHightcharts',
        dateRange : 'all',
        dateStart : $('#in_startDate').value(),
        dateEnd :  $('#in_endDate').value(),
        height : $('#divHightcharts').height(),
        type: _options.type ,
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



            }
            initHistoryTrigger();
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
                if (nextdom.history.chart['divHightcharts'].chart) {
                    nextdom.history.chart['divHightcharts'].chart.destroy();
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

        for (var i = 0; i < nextdom.history.chart['divHightcharts'].chart.data.datasets.length; i++){
            nextdom.history.chart['divHightcharts'].chart.data.datasets[i].fill =  value ?  'start' : false;
        }
        nextdom.history.chart['divHightcharts'].chart.update();
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

        nextdom.history.chart['divHightcharts'].chart.options.elements.line.tension =  value ?  0.15 : 0.000001;

        nextdom.history.chart['divHightcharts'].chart.update();
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

        for (var i = 0; i < nextdom.history.chart['divHightcharts'].chart.data.datasets.length; i++){
            nextdom.history.chart['divHightcharts'].chart.data.datasets[i].steppedLine = value ?  true : false;
        }

        nextdom.history.chart['divHightcharts'].chart.update();
    });

}