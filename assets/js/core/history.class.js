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

nextdom.history.drawChart = function (_params) {
  if ($.type(_params.dateRange) == 'object') {
    _params.dateRange = json_encode(_params.dateRange);
  }
  _params.option = init(_params.option, {derive: ''});
  $.ajax({
    type: "POST",
    url: "core/ajax/cmd.ajax.php",
    data: {
      action: "getHistory",
      id: _params.cmd_id,
      dateRange: _params.dateRange || '',
      dateStart: _params.dateStart || '',
      dateEnd: _params.dateEnd || '',
      derive: _params.option.derive || '',
      allowZero: init(_params.option.allowZero, 0)
    },
    dataType: 'json',
    global: _params.global || true,
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        notify("Erreur", data.result, 'error');
        return;
      }
      if (data.result.data.length < 1) {
        if (_params.option.displayAlert == false) {
          return;
        }
        if (!_params.noError) {
          var message = '{{Il n\'existe encore aucun historique pour cette commande :}} ' + data.result.history_name;
          if (init(data.result.dateStart) != '') {
            message += (init(data.result.dateEnd) != '') ? ' {{du}} ' + data.result.dateStart + ' {{au}} ' + data.result.dateEnd : ' {{à partir de}} ' + data.result.dateStart;
          } else {
            message += (init(data.result.dateEnd) != '') ? ' {{jusqu\'au}} ' + data.result.dateEnd : '';
          }
          notify("Erreur", message, 'error');
        }
        return;
      }
      if (isset(nextdom.history.chart[_params.el]) && isset(nextdom.history.chart[_params.el].cmd[_params.cmd_id])) {
        nextdom.history.chart[_params.el].cmd[_params.cmd_id] = null;
      }

      _params.graphStep = (_params.option.graphStep == 1) ? true : false;
      _params.graphArea = (_params.option.graphArea == 1) ? 'start' : false;
      _params.graphSmooth = (_params.option.graphSmooth == 1) ? 0.15 : 0.000001;


      if (isset(data.result.cmd)) {

        if (init(_params.option.graphType) == '') {
          if (isset(data.result.cmd.display)) {
            if (data.result.cmd.display.graphType == 'line' || data.result.cmd.display.graphType == 'bar' || data.result.cmd.display.graphType == 'bubble') {
              _params.option.graphType = data.result.cmd.display.graphType;
            } else {
              _params.option.graphType = 'line';
            }
          }
        }

        if (init(_params.stack) == '') {

          _params.stack = false;

        } else {
          _params.stack = _params.stack;

        }

        if (init(_params.option.graphArea) == '') {
          if (isset(data.result.cmd.display)) {

            if (data.result.cmd.display.graphArea == 1) {
              _params.graphArea = 'start';

            } else {
              _params.graphArea = false;

            }
          }
        }

        if (init(_params.option.graphSmooth) == '') {
          if (isset(data.result.cmd.display)) {
            if (data.result.cmd.display.graphSmooth == 1) {
              _params.graphSmooth = 0.15;
            } else {
              _params.graphSmooth = 0.000001;
            }
          }
        }
        if (init(_params.option.graphStep) == '') {
          if (isset(data.result.cmd.display)) {
            if (data.result.cmd.display.graphStep == 1) {
              _params.graphStep = true;
            } else {
              _params.graphStep = false;
            }
          }
        }

      }


      if (data.result.cmd.display.graphType == 'bubble') {
        _params.pointRadius = '4';
      } else {
        _params.pointRadius = '0';
      }

      function getRandomColor() {
        var letters = '0123456789ABCDEF';
        var color = '#';
        for (var i = 0; i < 6; i++) {
          color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
      }

      _params.option.graphColor = (isset(nextdom.history.chart[_params.el])) ? init(_params.option.graphColor, getRandomColor()) : init(_params.option.graphColor, getRandomColor());

      var labels = [];
      var values = '[';
      for (var i in data.result.data) {
        values = values + '{"x": ' + data.result.data[i][0] + ', "y": ' + data.result.data[i][1] + "}";
        if (i != data.result.data.length - 1) {
          values = values + ",";
        }
      }
      values = values + ']';

      var config = {
        type: _params.option.graphType,
        data: {
          datasets: [{
            data: JSON.parse(values),
            id: _params.cmd_id,
            label: data.result.history_name,
            borderColor: _params.option.graphColor,
            backgroundColor: _params.option.graphColor + 33,
            borderWidth: 1.3,
            fill: _params.graphArea,
            steppedLine: _params.graphStep
          }]
        },

        options: {
          legend: {
            display: false
          },
          elements: {
            point: {
              radius: _params.pointRadius,
            },
            line: {
              tension: _params.graphSmooth
            }
          },
          responsive: true,
          title: {
            display: false,
            text: 'Chart.js Line Chart'
          },
          tooltips: {
            mode: 'index',
            intersect: false,
          },
          hover: {
            mode: 'nearest',
            intersect: true
          },
          pan: {
            enabled: true,
            mode: "xy",
            speed: 10,
            threshold: 10
          },
          zoom: {
            enabled: true,
            drag: false,
            mode: "xy",
            limits: {
              max: 10,
              min: 0.5
            }
          },
          scales: {
            xAxes: [{
              stacked: _params.stack,
              type: 'time',
              display: true,
              scaleLabel: {
                display: false,
                labelString: 'Date'
              },
              time: {
                parser: 'DD/MM/YYYY HH:mm',
                // round: 'day'
                tooltipFormat: 'll HH:mm',
              },
              scaleLabel: {
                display: false,
                labelString: 'Date'
              }
            }],
            yAxes: [{
              stacked: _params.stack,
              display: true,
              scaleLabel: {
                display: false,
                labelString: 'Value'
              }
            }],

          }
        }
      };


      if (!isset(nextdom.history.chart[_params.el]) || (isset(_params.newGraph) && _params.newGraph == true)) {
        nextdom.history.chart[_params.el] = {};
        nextdom.history.chart[_params.el].cmd = new Array();
        nextdom.history.chart[_params.el].color = 0;
        nextdom.history.chart[_params.el].nbTimeline = 1;
        console.log(document.getElementById(_params.el));
        var ctx = document.getElementById(_params.el).getContext('2d');
        nextdom.history.chart[_params.el].chart = new Chart(ctx, config);

      } else {
        nextdom.history.chart[_params.el].chart.data.datasets.push({
          label: data.result.history_name,
          borderColor: _params.option.graphColor,
          backgroundColor: _params.option.graphColor + 33,
          borderWidth: 1.3,
          pointRadius: 0,
          fill: _params.graphArea,
          steppedLine: _params.graphStep,
          id: _params.cmd_id,
          data: JSON.parse(values),
        });

        nextdom.history.chart[_params.el].chart.update();

      }

      $("[data-cmd_id=" + _params.cmd_id + "]").children().children().closest('.fa-circle-o').css("cssText", "color:" + _params.option.graphColor + " !important;");
      nextdom.history.chart[_params.el].cmd[_params.cmd_id] = {option: _params.option, dateRange: _params.dateRange};

      if (typeof (init(_params.success)) == 'function') {
        _params.success(data.result);
      }
    }
  });
};