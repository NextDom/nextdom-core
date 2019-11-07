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

function initEvents() {
  // Input fields ENTER key press
  $('#in_testExpression').keypress(function(e) {
      if(e.which == 13) {
          $('#bt_executeExpressionOk').trigger('click');
      }
  });

  // Search info button
  $('#bt_searchInfoCmd').on('click', function() {
      var el = $(this);
      nextdom.cmd.getSelectModal({cmd: {type: 'info'}}, function(result) {
          $('#in_testExpression').atCaret('insert', result.human);
      });
  });

  // Search scenario button
  $('#bt_searchScenario').on('click', function() {
      var el = $(this);
      nextdom.scenario.getSelectModal({}, function(result) {
          $('#in_testExpression').atCaret('insert', result.human);
      });
  });

  // Search equipement button
  $('#bt_searchEqLogic').on('click', function() {
      var el = $(this);
      nextdom.eqLogic.getSelectModal({}, function(result) {
          $('#in_testExpression').atCaret('insert', result.human);
      });
  });

  // Execute test button
  $('#bt_executeExpressionOk').on('click',function(){
      if($('#in_testExpression').value() == ''){
          notify('{{ Expression }}', '{{ L\'expression de test ne peut être vide ! }}', 'error');
          return;
      }
      nextdom.scenario.testExpression({
          expression: $('#in_testExpression').value(),
          error: function (error) {
              notify("{{ Test Impossible }}", error.message, 'error');
          },
          success: function (data) {
              $('#div_expressionTestResult').empty();
              var html = '<div class="alert alert-info">{{ Evaluation de : }} <strong>'+data.evaluate+'</strong></div>';
              if(data.correct == 'nok'){
                  html += '<div class="alert alert-danger">{{ Attention : il doit y avoir un souci, car le résultat est le même que l\'expression }}</div>';
              }
              html += '<div class="alert alert-success">{{ Résultat : }} <strong>'+data.result+'</strong></div>';
              $('#div_expressionTestResult').append(html);
          }
      });
  });
}

initEvents();
