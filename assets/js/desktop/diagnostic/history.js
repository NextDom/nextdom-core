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
  while(nextdom.history.chart['div_graph'].chart.series.length > 0){
      nextdom.history.chart['div_graph'].chart.series[0].remove(true);
  }
  delete nextdom.history.chart['div_graph'];
  $(this).closest('.li_history').removeClass('active');
});

$(".in_datepicker").datepicker({dateFormat: "yy-mm-dd"});

$(".li_history .history").on('click', function (event) {
  $.hideAlert();
  if ($(this).closest('.li_history').hasClass('active')) {
      $(this).closest('.li_history').removeClass('active');
      addChart($(this).closest('.li_history').attr('data-cmd_id'), 0);
  } else {
      $(this).closest('.li_history').addClass('active');
      addChart($(this).closest('.li_history').attr('data-cmd_id'), 1);
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

$(".li_history .export").on('click', function () {
  window.open('core/php/export.php?type=cmdHistory&id=' + $(this).closest('.li_history').attr('data-cmd_id'), "_blank", null);
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
        if(lastId == null){
            return;
        }
        if(lastId.indexOf('#') != -1){
            addChart(lastId,0);
            addChart(lastId,1);
            return;
        }
        $('.li_history[data-cmd_id=' + lastId + ']').removeClass('active');
        addChart(lastId,0);
        nextdom.cmd.save({
            cmd: {id: lastId, display: {graphType: $(this).value()}},
            error: function (error) {
                notify("Erreur", error.message, 'error');
            },
            success: function () {
                $('.li_history[data-cmd_id=' + lastId + '] .history').click();
            }
        });
    });
    $('#sel_groupingType').off('change').on('change', function () {
        if(lastId == null){
            return;
        }
        if(lastId.indexOf('#') != -1){
            addChart(lastId,0);
            addChart(lastId,1);
            return;
        }
        $('.li_history[data-cmd_id=' + lastId + ']').removeClass('active');
        addChart(lastId,0);
        nextdom.cmd.save({
            cmd: {id: lastId, display: {groupingType: $(this).value()}},
            error: function (error) {
                notify("Erreur", error.message, 'error');
            },
            success: function () {
                $('.li_history[data-cmd_id=' + lastId + '] .history').click();
            }
        });
    });
    $('#cb_derive').off('change').on('change', function () {
        if(lastId == null){
            return;
        }
        if(lastId.indexOf('#') != -1){
            addChart(lastId,0);
            addChart(lastId,1);
            return;
        }
        $('.li_history[data-cmd_id=' + lastId + ']').removeClass('active');
        addChart(lastId,0);
        nextdom.cmd.save({
            cmd: {id: lastId, display: {graphDerive: $(this).value()}},
            error: function (error) {
                notify("Erreur", error.message, 'error');
            },
            success: function () {
                $('.li_history[data-cmd_id=' + lastId + '] .history').click();
            }
        });
    });
    $('#cb_step').off('change').on('change', function () {
        if(lastId == null){
            return;
        }
        if(lastId.indexOf('#') != -1){
            addChart(lastId,0);
            addChart(lastId,1);
            return;
        }
        $('.li_history[data-cmd_id=' + lastId + ']').removeClass('active');
        addChart(lastId,0);
        nextdom.cmd.save({
            cmd: {id: lastId, display: {graphStep: $(this).value()}},
            error: function (error) {
                notify("Erreur", error.message, 'error');
            },
            success: function () {
                $('.li_history[data-cmd_id=' + lastId + '] .history').click();
            }
        });
    });
}

$('#bt_validChangeDate').on('click',function(){
    if (isset(nextdom.history.chart['div_graph']) && isset(nextdom.history.chart['div_graph'].chart) && isset(nextdom.history.chart['div_graph'].chart.series)) {
        $(nextdom.history.chart['div_graph'].chart.series).each(function(i, serie){
            if(!isNaN(serie.options.id)){
                var cmd_id = serie.options.id;
                addChart(cmd_id, 0);
                addChart(cmd_id, 1);
            }
        });
    }
});

function addChart(_cmd_id, _action,_options) {
  if (_action == 0) {
      if (isset(nextdom.history.chart['div_graph']) && isset(nextdom.history.chart['div_graph'].chart) && isset(nextdom.history.chart['div_graph'].chart.series)) {
        $(nextdom.history.chart['div_graph'].chart.series).each(function(i, serie){
           try {
              if(serie.options.id == _cmd_id){
                  serie.remove();
              }
          }catch(error) {
          }
        });
      }
      return;
  }
  lastId = _cmd_id;
  nextdom.history.drawChart({
      cmd_id: _cmd_id,
      el: 'div_graph',
      dateRange : 'all',
      dateStart : $('#in_startDate').value(),
      dateEnd :  $('#in_endDate').value(),
      height : $('#div_graph').height(),
      option : _options,
      success: function (data) {
          if(isset(data.cmd) && isset(data.cmd.display)){
              if (init(data.cmd.display.graphStep) != '') {
                  $('#cb_step').off().value(init(data.cmd.display.graphStep));
              }
              if (init(data.cmd.display.graphType) != '') {
                  $('#sel_chartType').off().value(init(data.cmd.display.graphType));
              }
              if (init(data.cmd.display.groupingType) != '') {
                  $('#sel_groupingType').off().value(init(data.cmd.display.groupingType));
              }
              if (init(data.cmd.display.graphDerive) != '') {
                  $('#cb_derive').off().value(init(data.cmd.display.graphDerive));
              }
          }
          initHistoryTrigger();
      }
  });
}
