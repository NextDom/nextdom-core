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
      $('#div_alert').showAlert({message: error.message, level: 'danger'});
    },
    success: function (data) {
      data = data.reverse();
      var tr = '';
      for(var i in data){
        tr += '<tr>';
        tr += '<td>';
        tr += data[i].date;
        tr += '</td>';
        tr += '<td>';
        tr += data[i].type;
        tr += '</td>';
        tr += '<td>';
        tr += data[i].html;
        tr += '</td>';
        tr += '</tr>';
      }
      $('#table_timeline tbody').empty().append(tr).trigger('update');
    }
  });
}
