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

displayTimeline();

$('#bt_tabTimeline').on('click',function(){
    $('#div_visualization').empty();
    displayTimeline();
});

$('#bt_configureTimelineCommand').on('click',function(){
    $('#md_modal').dialog({title: "{{Configuration de l'historique des commandes}}"});
    $("#md_modal").load('index.php?v=d&modal=cmd.configureHistory').dialog('open');
});

$('#bt_configureTimelineScenario').on('click',function(){
    $('#md_modal').dialog({title: "{{Résumé scénario}}"});
    $("#md_modal").load('index.php?v=d&modal=scenario.summary').dialog('open');
});

$('#div_visualization').on('click','.bt_scenarioLog',function(){
    $('#md_modal').dialog({title: "{{Log d'exécution du scénario}}"});
    $("#md_modal").load('index.php?v=d&modal=scenario.log.execution&scenario_id=' + $(this).closest('.scenario').attr('data-id')).dialog('open');
});

$('#div_visualization').on('click','.bt_gotoScenario',function(){
    loadPage('index.php?v=d&p=scenario&id='+ $(this).closest('.scenario').attr('data-id'));
});

$('#div_visualization').on('click','.bt_configureCmd',function(){
    $('#md_modal').dialog({title: "{{Configuration de la commande}}"});
    $('#md_modal').load('index.php?v=d&modal=cmd.configure&cmd_id=' + $(this).closest('.cmd').attr('data-id')).dialog('open');
});

$('#bt_refreshTimeline').on('click',function(){
    displayTimeline();
});

timeline = null;

function displayTimeline(){
    jeedom.getTimelineEvents({
        error: function (error) {
            notify("Core",error.message,"error");
        },
        success: function (data) {
            data = data.reverse();
            var tr = '';
            for(var i in data){
                if (i > 0) {
                    if (moment(data[i].date).format('DD/MM/YYYY') != moment(data[i-1].date).format('DD/MM/YYYY')) {
                        tr += '<li class="time-label">';
                        tr += '<span class="bg-green">';
                        tr += moment(data[i].date).format('DD/MM/YYYY');
                        tr += '</span>';
                        tr += ' </li>';
                    }
                } else {
                    tr += '<li class="time-label">';
                    tr += '<span class="bg-green">';
                    tr += moment(data[i].date).format('DD/MM/YYYY');
                    tr += '</span>';
                    tr += ' </li>';
                }
                tr += '<li>';
                if (data[i].group == "info") {
                    tr += '<i class="fa fa-info bg-blue" data-toggle="tooltip" title="" data-original-title="commande type info"></i>';
                }else{
                    tr += '<i class="fa fa-rocket bg-red" data-toggle="tooltip" title="" data-original-title="commande type action"></i>';

                }
                tr +=data[i].html;
                tr += ' </li>';
            }
            console.log(data[i]);
            $('#data').append(tr).trigger('update');
        }
    });
}