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

$("#sel_typesTimeline").change(function(){
    displayTimeline();
});

$("#sel_objectsTimeline").change(function(){
    displayTimeline();
});

$("#sel_categoryTimeline").change(function(){
    displayTimeline();
});

$("#sel_pluginsTimeline").change(function(){
    displayTimeline();
});

$('.bt_timelineZoom').on('click',function(){
    zoom = $(this).attr('data-zoom');
    var end = new Date();
    var start = new Date();
    if(zoom == 'all'){
        timeline.fit();
        return;
    }else if (zoom == 'y'){
        start.setFullYear(end.getFullYear() - 1);
        end.setTime(start.getTime() + 390 * 24 *3600 *1000);
    }else if (zoom == 'm'){
        if(end.getMonth() == 1){
           start.setFullYear(end.getFullYear() - 1);
           start.setMonth(12);
           end.setTime(start.getTime() + 35 * 24 *3600 *1000);
       }else{
           start.setMonth(end.getMonth() - 1);
           end.setTime(start.getTime() + 33 * 24 *3600 *1000);
       }
   }else if (zoom == 'w'){
    start.setTime(end.getTime() - 7 * 24 *3600 * 1000);
    end.setTime(start.getTime() + 7.5 * 24 *3600 *1000);
}else if (zoom == 'd'){
 start.setTime(end.getTime() - 1 * 24 *3600 * 1000);
 end.setTime(start.getTime() + 1.1 * 24 *3600 *1000);
}else if (zoom == 'h'){
 start.setTime(end.getTime() -  3600 * 1000);
 end.setTime(start.getTime() + 3700 *1000);
}
timeline.setWindow(start,end);
});

timeline = null;

function displayTimeline(){
    var typefilter = $("#sel_typesTimeline").value();
    var pluginfilter = $("#sel_pluginsTimeline").value();
    var categoryfilter = $("#sel_categoryTimeline").value();
    var objectfilter = $("#sel_objectsTimeline").value();
    var end = new Date();
    var start = new Date();
    start.setTime(end.getTime() -  3600 * 1000);
    end.setTime(start.getTime() + 3700 *1000);
    nextdom.getTimelineEvents({
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            if(timeline != null){
                windowTimeline = timeline.getWindow()
                end=windowTimeline.end
                start = windowTimeline.start
                timeline.destroy()
            }
            data_item = [];
            id = 0;
            for(var i in data){
                if (typefilter != 'all' && data[i].type != typefilter) {
                 continue;
             }
             if (pluginfilter != 'all' && data[i].plugins != pluginfilter && typefilter != 'scenario') {
                 continue;
             }
             if (objectfilter != 'all' && data[i].object != objectfilter) {
                 continue;
             }
             if (categoryfilter != 'all' && typefilter != 'scenario'){
                 var hascat =0;
                 for (var category in data[i].category){
                  if (category == categoryfilter && data[i].category[category] == 1) {
                   hascat += 1;
               }
           }
           if (hascat==0){
              continue;
          }
      }
      item = {id : id,start : data[i].date,content : data[i].html,group : data[i].group,title:data[i].date};
      id++;
      data_item.push(item);
  }
  var items = new vis.DataSet(data_item);
  var options = {
    groupOrder:'content',
    verticalScroll: true,
    zoomKey: 'ctrlKey',
    orientation : 'top',
    maxHeight: $('body').height() - $('header').height() - 75
};
timeline = new vis.Timeline(document.getElementById('div_visualization'),items,options);
timeline.setWindow(start,end);
}
});
}
