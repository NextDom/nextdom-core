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

function initEvents() {
    // save button
    $('#bt_saveSummaryScenario').off().on('click',function(){
        var scenarios = $('#table_scenarioSummary tbody .scenario').getValues('.scenarioAttr');
        nextdom.scenario.saveAll({
            scenarios : scenarios,
            error: function (error) {
                notify("{{ Scénario }}", error.message, 'error');
            },
            success : function(data){
                refreshScenarioSummary();
            }
        });
    });

    // Refresh button
    $('#bt_refreshSummaryScenario').off().on('click',function(){
        refreshScenarioSummary();
    });
}

function refreshScenarioSummary(){
    nextdom.scenario.all({
        nocache : true,
        error: function (error) {
            notify("{{ Scénario }}", error.message, 'error');
        },
        success : function(data){
            $('#table_scenarioSummary tbody').empty();
            var table = [];
            for(var i in data){
                var tr = '<tr class="scenario" data-id="' + init(data[i].id) + '">';
                tr += '<td>';
                tr += '<span class="scenarioAttr" data-l1key="id"></span>';
                tr += '</td>';
                tr += '<td>';
                tr += '<span class="scenarioAttr cursor bt_summaryGotoScenario" data-l1key="humanName"></span>';
                tr += '</td>';
                tr += '<td>';
                switch (data[i].state) {
                    case 'error' :
                    tr += '<span class="label label-warning label-sticker">{{ Erreur }}</span>';
                    break;
                    case 'on' :
                    tr += '<span class="label label-info label-sticker">{{ Actif }}</span>';
                    break;
                    case 'in progress' :
                    tr += '<span class="label label-success label-sticker">{{ En cours }}</span>';
                    break;
                    case 'stop' :
                    tr += '<span class="label label-danger label-sticker">{{ Arrêté }}</span>';
                    break;
                }
                tr += '</td>';
                tr += '<td>';
                tr += '<span class="scenarioAttr" data-l1key="lastLaunch"></span>';
                tr += '</td>';
                tr += '<td>';
                tr += '<input type="checkbox" class="scenarioAttr" data-label-text="{{ Actif }}" data-l1key="isActive">';
                tr += '</td>';
                tr += '<td>';
                tr += '<input type="checkbox" class="scenarioAttr" data-label-text="{{ Visible }}" data-l1key="isVisible">';
                tr += '</td>';
                tr += '<td>';
                tr += '<input type="checkbox" class="scenarioAttr" data-l1key="configuration" data-l2key="allowMultiInstance">';
                tr += '</td>';
                tr += '<td>';
                tr += '<input type="checkbox" class="scenarioAttr" data-l1key="configuration" data-l2key="syncmode">';
                tr += '</td>';
                tr += '<td style="width:120px">';
                tr += '<select class="scenarioAttr form-control" data-l1key="configuration" data-l2key="logmode">';
                tr += '<option value="default">{{ Défaut }}</option>';
                tr += '<option value="none">{{ Aucun }}</option>';
                tr += '<option value="realtime">{{ Temps réel }}</option>';
                tr += '</select>';
                tr += '</td>';
                tr += '<td>';
                tr += '<input type="checkbox" class="scenarioAttr" data-l1key="configuration" data-l2key="timeline::enable">';
                tr += '</td>';
                tr += '<td style="width:87px">';
                tr += '<a class="btn btn-default btn-sm pull-right bt_summaryViewLog" data-toggle="tooltip" title="" data-original-title="{{ Voir les logs }}"><i class="fas fa-file no-spacing"></i></a> ';
                if(data[i].state == 'in_progress'){
                    tr += '<a class="btn btn-danger btn-sm bt_summaryStopScenario" data-toggle="tooltip" title="" data-original-title="{{ Exécuter }}"><i class="fas fa-stop no-spacing"></i></a>';
                }else{
                    tr += '<a class="btn btn-success btn-sm pull-right bt_summaryLaunchScenario" data-toggle="tooltip" title="" data-original-title="{{ Exécuter }}"><i class="fas fa-play no-spacing"></i></a>';
                }
                tr += '</td>';
                tr += '</tr>';
                var result = $(tr);
                result.setValues(data[i], '.scenarioAttr');
                table.push(result);
            }
            $('#table_scenarioSummary tbody').append(table);
            $("#table_scenarioSummary").trigger("update");

            // Scenario log view button
            $('.bt_summaryViewLog').off().on('click',function(){
                var tr = $(this).closest('tr');
                loadModal('modal2', '{{ Log d\'exécution du scénario }}', 'scenario.log.execution&scenario_id=' + tr.attr('data-id'));
            });

            // Scenario stop button
            $('.bt_summaryStopScenario').off().on('click',function(){
                var tr = $(this).closest('tr');
                nextdom.scenario.changeState({
                    id: tr.attr('data-id'),
                    state: 'stop',
                    error: function (error) {
                        notify("{{ Scénario }}", error.message, 'error');
                    },
                    success:function(){
                        refreshScenarioSummary();
                    }
                });
            });

            // Scenario launch button
            $('.bt_summaryLaunchScenario').off().on('click',function(){
                var tr = $(this).closest('tr');
                nextdom.scenario.changeState({
                    id: tr.attr('data-id'),
                    state: 'start',
                    error: function (error) {
                        notify("{{ Scénario }}", error.message, 'error');
                    },
                    success:function(){
                        refreshScenarioSummary();
                    }
                });
            });

            // Scenario goto button
            $('.bt_summaryGotoScenario').off().on('click',function(){
                var tr = $(this).closest('tr');
                window.location.href = 'index.php?v=d&p=scenario&id='+tr.attr('data-id');
            });
        }
    });
}

initEvents();
initTableSorter();
refreshScenarioSummary();
