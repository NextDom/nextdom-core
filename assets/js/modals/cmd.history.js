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

// Page init
loadInformations();
initEvents();

/**
 * Load informations in all forms of the page
 */
function loadInformations() {
  // Set charts size
  $('#div_historyChart').css('height', $('#div_historyChart').closest('.ui-dialog-content').height()-$('#div_historyChart').closest('.ui-dialog-content').find('.content-header').height() - 15);
  // Add the cmd chart
  addChart(historyId);
  // Init datepicker
  $(".in_datepicker").datepicker();
  setTimeout(function () {
    $("#ui-datepicker-div").hide();
  }, 200);
}

/**
 * Init events on the profils page
 */
function initEvents() {
  // Chart type change
  $('#sel_chartType').on('change', function () {
      addChart(historyId);
      nextdom.cmd.save({
          cmd: {id: historyId, display: {graphType: $(this).value()}},
          error: function (error) {
              notify("Erreur", error.message, 'error');
          }
      });
  });

  // Chart group change
  $('#sel_groupingType').on('change', function () {
      addChart(historyId);
      nextdom.cmd.save({
          cmd: {id: historyId, display: {groupingType: $(this).value()}},
          error: function (error) {
              notify("Erreur", error.message, 'error');
          }
      });
  });

  // Chart derive option change
  $('#cb_derive').on('change', function () {
      addChart(historyId);
      nextdom.cmd.save({
          cmd: {id: historyId, display: {graphDerive: $(this).value()}},
          error: function (error) {
              notify("Erreur", error.message, 'error');
          }
      });
  });

  // Chart step option change
  $('#cb_step').on('change', function () {
      addChart(historyId);
      nextdom.cmd.save({
          cmd: {id: historyId, display: {graphStep: $(this).value()}},
          error: function (error) {
              notify("Erreur", error.message, 'error');
          }
      });
  });

  // Date change
  $('#bt_validChangeDate').on('click', function () {
      addChart(historyId);
      nextdom.cmd.save({
          cmd: {id: historyId},
          error: function (error) {
              notify("Erreur", error.message, 'error');
          }
      });
  });
}

/**
 * Add a charts
 */
function addChart(_cmd_id) {
    // Hide alert
    $('#alertGraph').hide();
    // Delete old chart
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
    delete nextdom.history.chart['div_historyChart'];
    // Drawing
    nextdom.cmd.save({
        cmd: {id: historyId},
        error: function (error) {
            notify('Erreur', error.message, 'error');
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
                    initEvents();
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
                notify('Erreur', error.message, 'error');
            }
        });
    });

    $('#sel_groupingType').on('change', function () {
        addChart(historyId);
        nextdom.cmd.save({
            cmd: {id: historyId, display: {groupingType: $(this).value()}},
            error: function (error) {
                notify('Erreur', error.message, 'error');
            }
        });
    });

    $('#cb_derive').on('change', function () {
        addChart(historyId);
        nextdom.cmd.save({
            cmd: {id: historyId, display: {graphDerive: $(this).value()}},
            error: function (error) {
                notify('Erreur', error.message, 'error');
            }
        });
    });

    $('#cb_step').on('change', function () {
        addChart(historyId);
        nextdom.cmd.save({
            cmd: {id: historyId, display: {graphStep: $(this).value()}},
            error: function (error) {
                notify('Erreur', error.message, 'error');
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
            notify('Erreur', error.message, 'error');
        }
    });
});
