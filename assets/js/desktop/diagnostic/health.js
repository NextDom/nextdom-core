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

// Page init
showSelectedTabFromUrl(document.location.toString());
initEvents();

/**
 * Init events on the profils page
 */
function initEvents() {
    // Plugin configuration button
    $('.bt_configurationPlugin').on('click', function() {
      $('#md_modal').dialog({title: "{{Configuration du plugin}}"});
      $("#md_modal").load('index.php?v=d&p=plugin&ajax=1&id='+$(this).attr('data-pluginid')).dialog('open');
    });

    // Specific health button
    $('.bt_healthSpecific').on('click', function () {
      $('#md_modal').dialog({title: "{{Santé}} " + $(this).attr('data-pluginname')});
      $('#md_modal').load('index.php?v=d&plugin='+$(this).attr('data-pluginid')+'&modal=health').dialog('open');
    });

    // Benchmark button
    $('#bt_benchmarkNextDom').on('click',function(){
      loadModal('modal', '{{NextDom benchmark}}', 'nextdom.benchmark');
    });

    // Plugin panel collapsing
    $('#bt_healthCollapse').on('click',function(){
      collapseHealth();
    });

    // Plugin panel uncollapsing
    $('#bt_healthUncollapse').on('click',function(){
      uncollapseHealth();
    });
}

/**
 * uncollapse Health panels
 */
function uncollapseHealth() {
  $('#accordionHealth .panel-collapse').each(function () {
     if ($(this).hasClass("in")) {
         $(this).removeClass("in");
     }
  });
  $('#bt_healthUncollapse').hide();
  $('#bt_healthCollapse').show()
}

/**
 * collapse Health panels
 */
function collapseHealth() {
  $('#accordionHealth .panel-collapse').each(function () {
     if (!$(this).hasClass("in")) {
         $(this).css({'height' : '' });
         $(this).addClass("in");
     }
  });
  $('#bt_healthCollapse').hide();
  $('#bt_healthUncollapse').show()
}

/**
 * Show the tab indicated in the url
 *
 * @param url Url to check
 */
function showSelectedTabFromUrl(url) {
    let tabCode = 'div_Health';
    if (url.match('#')) {
      tabCode = url.split('#')[1];
      $('.nav-tabs a[href="#' + url.split('#')[1] + '"]').tab('show');
    }
    $('.nav-tabs a').off('shown.bs.tab').on('shown.bs.tab', function (e) {
      window.location.hash = e.target.hash;
      showSelectedTabFromUrl(document.location.toString());
    });
    if (tabCode == 'div_Health') {
        $('#bt_healthCollapse').hide();
        $('#bt_healthUncollapse').hide()
    } else {
        uncollapseHealth();
    }
}
