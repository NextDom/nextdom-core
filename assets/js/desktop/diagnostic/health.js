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
$('.bt_configurationPlugin').on('click',function(){
  $('#md_modal').dialog({title: "{{Configuration du plugin}}"});
  $("#md_modal").load('index.php?v=d&p=plugin&ajax=1&id='+$(this).attr('data-pluginid')).dialog('open');
});

$('.bt_healthSpecific').on('click', function () {
  $('#md_modal').dialog({title: "{{Santé}} " + $(this).attr('data-pluginname')});
  $('#md_modal').load('index.php?v=d&plugin='+$(this).attr('data-pluginid')+'&modal=health').dialog('open');
});

$('#bt_benchmarkNextDom').on('click',function(){
  $('#md_modal').dialog({title: "{{NextDom benchmark}}"});
  $("#md_modal").load('index.php?v=d&modal=nextdom.benchmark').dialog('open');
});

$('#bt_healthCollapse').on('click',function(){
  $('#accordionHealth .panel-collapse').each(function () {
     if (!$(this).hasClass("in")) {
         $(this).css({'height' : '' });
         $(this).addClass("in");
     }
  });
  $('#bt_healthCollapse').hide();
  $('#bt_healthUncollapse').show()
});

$('#bt_healthUncollapse').on('click',function(){
  $('#accordionHealth .panel-collapse').each(function () {
     if ($(this).hasClass("in")) {
         $(this).removeClass("in");
     }
  });
  $('#bt_healthUncollapse').hide();
  $('#bt_healthCollapse').show()
});
