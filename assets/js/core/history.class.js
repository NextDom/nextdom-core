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


nextdom.history = function () {
};

nextdom.history.chart = [];

nextdom.history.get = function (queryParams) {
  var paramsRequired = ['cmd_id', 'dateStart', 'dateEnd'];
  var paramsSpecifics = {
    pre_success: function (data) {
      if (isset(nextdom.cmd.cache.byId[data.result.id])) {
        delete nextdom.cmd.cache.byId[data.result.id];
      }
      return data;
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Cmd', 'getHistory');
    ajaxParams.data['id'] = queryParams.cmd_id;
    ajaxParams.data['dateStart'] = queryParams.dateStart || '';
    ajaxParams.data['dateEnd'] = queryParams.dateEnd || '';
    $.ajax(ajaxParams);
  }
};

nextdom.history.copyHistoryToCmd = function (queryParams) {
  var paramsRequired = ['source_id', 'target_id'];
  var paramsSpecifics = {};
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Cmd', 'copyHistoryToCmd');
    ajaxParams.data['source_id'] = queryParams.source_id;
    ajaxParams.data['target_id'] = queryParams.target_id;
    $.ajax(ajaxParams);
  }
};

nextdom.history.drawChart = function (queryParams) {
  if ($.type(queryParams.dateRange) == 'object') {
    queryParams.dateRange = json_encode(queryParams.dateRange);
  }
  queryParams.option = init(queryParams.option, {derive: ''});
  $.ajax({
    type: 'POST',
    url: 'src/ajax.php',
    data: {
      action: 'getHistory',
      target: 'Cmd',
      id: queryParams.cmd_id,
      dateRange: queryParams.dateRange || '',
      dateStart: queryParams.dateStart || '',
      dateEnd: queryParams.dateEnd || '',
      derive: queryParams.option.derive || '',
      allowZero: init(queryParams.option.allowZero, 0)
    },
    dataType: 'json',
    global: queryParams.global || true,
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        notify('Erreur', data.result, 'error');
        return;
      }
      if (data.result.data.length < 1) {
        if (queryParams.option.displayAlert == false) {
          return;
        }
        if (!queryParams.noError) {
          var message = '{{Il n\'existe encore aucun historique pour cette commande :}} ' + data.result.history_name;
          if (init(data.result.dateStart) != '') {
            message += (init(data.result.dateEnd) != '') ? ' {{du}} ' + data.result.dateStart + ' {{au}} ' + data.result.dateEnd : ' {{à partir de}} ' + data.result.dateStart;
          } else {
            message += (init(data.result.dateEnd) != '') ? ' {{jusqu\'au}} ' + data.result.dateEnd : '';
          }
          notify('Erreur', message, 'error');
        }
        return;
      }
      if (isset(nextdom.history.chart[queryParams.el]) && isset(nextdom.history.chart[queryParams.el].cmd[queryParams.cmd_id])) {
        nextdom.history.chart[queryParams.el].cmd[queryParams.cmd_id] = null;
      }
      queryParams.option.graphColor = (isset(nextdom.history.chart[queryParams.el])) ? init(queryParams.option.graphColor, Highcharts.getOptions().colors[init(nextdom.history.chart[queryParams.el].color, 0)]) : init(queryParams.option.graphColor, Highcharts.getOptions().colors[0]);
      queryParams.option.graphStep = (queryParams.option.graphStep == '1');
      if (isset(data.result.cmd)) {
        if (init(queryParams.option.graphStep) == '') {
          queryParams.option.graphStep = (data.result.cmd.subType == 'binary');
          if (isset(data.result.cmd.display) && init(data.result.cmd.display.graphStep) != '') {
            queryParams.option.graphStep = !(data.result.cmd.display.graphStep == '0');
          }
        }
        if (init(queryParams.option.graphType) == '') {
          queryParams.option.graphType = (isset(data.result.cmd.display) && init(data.result.cmd.display.graphType) != '') ? data.result.cmd.display.graphType : 'line';
        }
        if (init(queryParams.option.groupingType) == '' && isset(data.result.cmd.display) && init(data.result.cmd.display.groupingType) != '') {
          var split = data.result.cmd.display.groupingType.split('::');
          queryParams.option.groupingType = {function: split[0], time: split[1]};
        }
      }
      var stacking = (queryParams.option.graphStack === undefined || queryParams.option.graphStack == null || queryParams.option.graphStack == 0) ? null : 'value';
      queryParams.option.graphStack = (queryParams.option.graphStack === undefined || queryParams.option.graphStack == null || queryParams.option.graphStack == 0) ? Math.floor(Math.random() * 10000 + 2) : 1;
      queryParams.option.graphScale = (queryParams.option.graphScale === undefined) ? 0 : parseInt(queryParams.option.graphScale);
      queryParams.showLegend = (init(queryParams.showLegend, true) && init(queryParams.showLegend, true) != '0') ? true : false;
      queryParams.showTimeSelector = (init(queryParams.showTimeSelector, true) && init(queryParams.showTimeSelector, true) != '0') ? true : false;
      queryParams.showScrollbar = (init(queryParams.showScrollbar, true) && init(queryParams.showScrollbar, true) != '0') ? true : false;
      queryParams.showNavigator = (init(queryParams.showNavigator, true) && init(queryParams.showNavigator, true) != '0') ? true : false;

      var legend = {borderColor: 'black', borderWidth: 2, shadow: true};
      legend.enabled = init(queryParams.showLegend, true);
      if (isset(queryParams.newGraph) && queryParams.newGraph == true) {
        delete nextdom.history.chart[queryParams.el];
      }
      var charts = {
        zoomType: 'x',
        renderTo: queryParams.el,
        alignTicks: false,
        spacingBottom: 5,
        spacingTop: 5,
        spacingRight: 5,
        spacingLeft: 5,
        height: queryParams.height || null
      };
      if (charts.height < 10) {
        charts.height = null;
      }

      if (isset(queryParams.transparentBackground) && queryParams.transparentBackground == '1') {
        charts.backgroundColor = 'rgba(255, 255, 255, 0)';
      }

      if (isset(nextdom.history.chart[queryParams.el]) && nextdom.history.chart[queryParams.el].type == 'pie') {
        queryParams.option.graphType = 'pie';
      }

      if (queryParams.option.graphType == 'pie') {
        var series = {
          type: queryParams.option.graphType,
          id: queryParams.cmd_id,
          cursor: 'pointer',
          data: [{
            y: data.result.data[data.result.data.length - 1][1],
            name: (isset(queryParams.option.name)) ? queryParams.option.name + ' ' + data.result.unite : data.result.history_name + ' ' + data.result.unite
          }],
          color: queryParams.option.graphColor,
        };
        if (!isset(nextdom.history.chart[queryParams.el]) || (isset(queryParams.newGraph) && queryParams.newGraph == true)) {
          nextdom.history.chart[queryParams.el] = {};
          nextdom.history.chart[queryParams.el].cmd = new Array();
          nextdom.history.chart[queryParams.el].color = 0;
          nextdom.history.chart[queryParams.el].type = queryParams.option.graphType;
          nextdom.history.chart[queryParams.el].chart = new Highcharts.Chart({
            chart: charts,
            title: {
              text: ''
            },
            credits: {
              text: '',
              href: '',
            },
            exporting: {
              enabled: queryParams.enableExport || ($.mobile) ? false : true
            },
            tooltip: {
              pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>',
              valueDecimals: 2,
            },
            plotOptions: {
              pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                  enabled: true,
                  format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                  style: {
                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                  }
                },
                showInLegend: true
              }
            },
            series: [series]
          });
        } else {
          nextdom.history.chart[queryParams.el].chart.series[0].addPoint({
            y: data.result.data[data.result.data.length - 1][1],
            name: (isset(queryParams.option.name)) ? queryParams.option.name + ' ' + data.result.unite : data.result.history_name + ' ' + data.result.unite,
            color: queryParams.option.graphColor
          });
        }
      } else {
        var dataGrouping = {
          enabled: false
        };
        if (isset(queryParams.option.groupingType) && jQuery.type(queryParams.option.groupingType) == 'string' && queryParams.option.groupingType != '') {
          var split = queryParams.option.groupingType.split('::');
          queryParams.option.groupingType = {function: split[0], time: split[1]};
        }
        if (isset(queryParams.option.groupingType) && isset(queryParams.option.groupingType.function) && isset(queryParams.option.groupingType.time)) {
          dataGrouping = {
            approximation: queryParams.option.groupingType.function,
            enabled: true,
            forced: true,
            units: [[queryParams.option.groupingType.time, [1]]]
          };
        }
        if (data.result.timelineOnly) {
          if (!isset(nextdom.history.chart[queryParams.el]) || !isset(nextdom.history.chart[queryParams.el].nbTimeline)) {
            nbTimeline = 1;
          } else {
            nextdom.history.chart[queryParams.el].nbTimeline++;
            nbTimeline = nextdom.history.chart[queryParams.el].nbTimeline;
          }
          var series = {
            type: 'flags',
            name: (isset(queryParams.option.name)) ? queryParams.option.name + ' ' + data.result.unite : data.result.history_name + ' ' + data.result.unite,
            data: [],
            id: queryParams.cmd_id,
            color: queryParams.option.graphColor,
            shape: 'squarepin',
            cursor: 'pointer',
            y: -30 - 25 * (nbTimeline - 1),
            point: {
              events: {
                click: function (event) {
                  if ($('#md_modal2').is(':visible')) {
                    return;
                  }
                  if ($('#md_modal1').is(':visible')) {
                    return;
                  }
                  var id = this.series.userOptions.id;
                  var datetime = Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', this.x);
                  var value = this.y;
                  bootbox.prompt("{{Edition de la série :}} <b>" + this.series.name + "</b> {{et du point de}} <b>" + datetime + "</b> ({{valeur :}} <b>" + value + "</b>) ? {{Ne rien mettre pour supprimer la valeur}}", function (result) {
                    if (result !== null) {
                      nextdom.history.changePoint({cmd_id: id, datetime: datetime, oldValue: value, value: result});
                    }
                  });
                }
              }
            }
          };
          for (var i in data.result.data) {
            series.data.push({
              x: data.result.data[i][0],
              title: data.result.data[i][1]
            });
          }
        } else {
          var series = {
            dataGrouping: dataGrouping,
            type: queryParams.option.graphType,
            id: queryParams.cmd_id,
            cursor: 'pointer',
            name: (isset(queryParams.option.name)) ? queryParams.option.name + ' ' + data.result.unite : data.result.history_name + ' ' + data.result.unite,
            data: data.result.data,
            color: queryParams.option.graphColor,
            stack: queryParams.option.graphStack,
            step: queryParams.option.graphStep,
            yAxis: queryParams.option.graphScale,
            stacking: stacking,
            tooltip: {
              valueDecimals: 2
            },
            point: {
              events: {
                click: function () {
                  if ($('#md_modal2').is(':visible')) {
                    return;
                  }
                  if ($('#md_modal1').is(':visible')) {
                    return;
                  }
                  var id = this.series.userOptions.id;
                  var datetime = Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', this.x);
                  var value = this.y;
                  bootbox.prompt("{{Edition de la série :}} <b>" + this.series.name + "</b> {{et du point de}} <b>" + datetime + "</b> ({{valeur :}} <b>" + value + "</b>) ? {{Ne rien mettre pour supprimer la valeur}}", function (result) {
                    if (result !== null) {
                      nextdom.history.changePoint({cmd_id: id, datetime: datetime, oldValue: value, value: result});
                    }
                  });
                }
              }
            }
          };
        }
        if (isset(queryParams.option.graphZindex)) {
          series.zIndex = queryParams.option.graphZindex;
        }

        if (!isset(nextdom.history.chart[queryParams.el]) || (isset(queryParams.newGraph) && queryParams.newGraph == true)) {
          nextdom.history.chart[queryParams.el] = {};
          nextdom.history.chart[queryParams.el].cmd = new Array();
          nextdom.history.chart[queryParams.el].color = 0;
          nextdom.history.chart[queryParams.el].nbTimeline = 1;

          if (queryParams.dateRange == '30 min') {
            var dateRange = 0
          } else if (queryParams.dateRange == '1 hour') {
            var dateRange = 1
          } else if (queryParams.dateRange == '1 day') {
            var dateRange = 2
          } else if (queryParams.dateRange == '7 days') {
            var dateRange = 3
          } else if (queryParams.dateRange == '1 month') {
            var dateRange = 4
          } else if (queryParams.dateRange == '1 year') {
            var dateRange = 5
          } else if (queryParams.dateRange == 'all') {
            var dateRange = 6
          } else {
            var dateRange = 3;
          }

          nextdom.history.chart[queryParams.el].type = queryParams.option.graphType;
          nextdom.history.chart[queryParams.el].chart = new Highcharts.StockChart({
            chart: charts,
            credits: {
              text: '',
              href: '',
            },
            navigator: {
              enabled: queryParams.showNavigator,
              series: {
                includeInCSVExport: false
              }
            },
            exporting: {
              enabled: queryParams.enableExport || ($.mobile) ? false : true
            },
            rangeSelector: {
              buttons: [{
                type: 'minute',
                count: 30,
                text: '30m'
              }, {
                type: 'hour',
                count: 1,
                text: 'H'
              }, {
                type: 'day',
                count: 1,
                text: 'J'
              }, {
                type: 'week',
                count: 1,
                text: 'S'
              }, {
                type: 'month',
                count: 1,
                text: 'M'
              }, {
                type: 'year',
                count: 1,
                text: 'A'
              }, {
                type: 'all',
                count: 1,
                text: 'Tous'
              }],
              selected: dateRange,
              inputEnabled: false,
              enabled: queryParams.showTimeSelector
            },
            legend: legend,
            tooltip: {
              xDateFormat: '%Y-%m-%d %H:%M:%S',
              pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>',
              valueDecimals: 2,
            },
            yAxis: [{
              format: '{value}',
              showEmpty: false,
              minPadding: 0.001,
              maxPadding: 0.001,
              showLastLabel: true,
            }, {
              opposite: false,
              format: '{value}',
              showEmpty: false,
              gridLineWidth: 0,
              minPadding: 0.001,
              maxPadding: 0.001,
              labels: {
                align: 'left',
                x: 2
              }
            }],
            xAxis: {
              type: 'datetime',
              ordinal: false,
              maxPadding: 0.02,
              minPadding: 0.02
            },
            scrollbar: {
              barBackgroundColor: 'gray',
              barBorderRadius: 7,
              barBorderWidth: 0,
              buttonBackgroundColor: 'gray',
              buttonBorderWidth: 0,
              buttonBorderRadius: 7,
              trackBackgroundColor: 'none', trackBorderWidth: 1,
              trackBorderRadius: 8,
              trackBorderColor: '#CCC',
              enabled: queryParams.showScrollbar
            },
            series: [series]
          });
        } else {
          nextdom.history.chart[queryParams.el].chart.addSeries(series);
        }
        nextdom.history.chart[queryParams.el].cmd[queryParams.cmd_id] = {
          option: queryParams.option,
          dateRange: queryParams.dateRange
        };
      }

      nextdom.history.chart[queryParams.el].color++;
      if (nextdom.history.chart[queryParams.el].color > 9) {
        nextdom.history.chart[queryParams.el].color = 0;
      }

      var extremes = nextdom.history.chart[queryParams.el].chart.xAxis[0].getExtremes();
      var plotband = nextdom.history.generatePlotBand(extremes.min, extremes.max);
      for (var i in plotband) {
        nextdom.history.chart[queryParams.el].chart.xAxis[0].addPlotBand(plotband[i]);
      }
      if (typeof (init(queryParams.success)) == 'function') {
        queryParams.success(data.result);
      }
    }
  });
};;

nextdom.history.generatePlotBand = function (_startTime, _endTime) {
  var plotBands = [];
  if ((_endTime - _startTime) > (7 * 86400000)) {
    return plotBands;
  }
  var pas = 86400000;
  var offset = 0;
  _startTime = (Math.floor(_startTime / 86400000) * 86400000) - offset;
  while (_startTime < _endTime) {
    var plotBand = {};
    plotBand.color = '#F8F8F8';
    plotBand.from = _startTime;
    plotBand.to = _startTime + pas;
    if (plotBand.to > _endTime) {
      plotBand.to = _endTime;
    }
    plotBands.push(plotBand);
    _startTime += 2 * pas;
  }
  return plotBands;
};

nextdom.history.changePoint = function (queryParams) {
  var paramsRequired = ['cmd_id', 'datetime', 'value', 'oldValue'];
  var paramsSpecifics = {
    error: function (error) {
      notify('Core', error.message, "error");
    },
    success: function (result) {
      notify('Core', '{{La valeur a été éditée avec succès}}', "success");
      for (var i in nextdom.history.chart) {
        var serie = nextdom.history.chart[i].chart.get(queryParams.cmd_id);
        if (serie !== null) {
          serie.remove();
          serie = null;
          nextdom.history.drawChart({
            cmd_id: queryParams.cmd_id,
            el: i,
            dateRange: nextdom.history.chart[i].cmd[queryParams.cmd_id].dateRange,
            option: nextdom.history.chart[i].cmd[queryParams.cmd_id].option
          });
        }
      }
    }
  };
  if (nextdom.private.isValidQuery(queryParams, paramsRequired, paramsSpecifics)) {
    var params = $.extend({}, nextdom.private.defaultqueryParams, paramsSpecifics, queryParams || {});
    var ajaxParams = nextdom.private.getAjaxParams(params, 'Cmd', 'changeHistoryPoint');
    ajaxParams.data['cmd_id'] = queryParams.cmd_id;
    ajaxParams.data['datetime'] = queryParams.datetime;
    ajaxParams.data['value'] = queryParams.value;
    ajaxParams.data['oldValue'] = queryParams.oldValue;
    $.ajax(ajaxParams);
  }
};