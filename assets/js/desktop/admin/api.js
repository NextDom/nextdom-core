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
loadInformations();
initEvents();

/**
 * Load informations in all forms of the page
 */
function loadInformations() {
    nextdom.config.load({
        configuration: $('#API').getValues('.configKey:not(.noSet)')[0],
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function (data) {
            $('#API').setValues(data, '.configKey');
            modifyWithoutSave = false;
            $(".bt_cancelModifs").hide();
        }
    });
}

/**
 * Init events on the profils page
 */
function initEvents() {
    // Param changed : page leaving lock by msgbox
    $('#API').delegate('.configKey', 'change', function () {
        if (!lockModify) {
            modifyWithoutSave = true;
            $(".bt_cancelModifs").show();
        }
    });

    // Cancel modifications
    $('.bt_cancelModifs').on('click', function () {
        loadInformations();
    });

    // Save button
    $("#bt_saveapi").on('click', function (event) {
        nextdom.config.save({
            configuration: $('#API').getValues('.configKey')[0],
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function () {
                nextdom.config.load({
                    configuration: $('#API').getValues('.configKey')[0],
                    plugin: 'core',
                    error: function (error) {
                        notify('Erreur', error.message, 'error');
                    },
                    success: function (data) {
                        $('#API').setValues(data, '.configKey');
                        modifyWithoutSave = false;
                        notify('Info', '{{Sauvegarde réussie}}', 'success');
                    }
                });
            }
        });
    });

    // Regenerate key button
    $(".bt_regenerate_api").on('click', function (event) {
        var el = $(this);
        bootbox.confirm('{{Etes-vous sûr de vouloir réinitialiser la clé API de }}'+el.attr('data-plugin')+' ?', function (result) {
            if (result) {
               $.ajax({
                  type: "POST",
                  url: "src/ajax.php",
                  data: {
                      target: 'Config',
                      action: "genApiKey",
                      plugin:el.attr('data-plugin'),
                  },
                  dataType: 'json',
                  error: function (request, status, error) {
                      handleAjaxError(request, status, error);
                  },
                  success: function (data) {
                      if (data.state != 'ok') {
                          notify('Erreur', data.result, 'error');
                          return;
                      }
                      el.closest('.mix-group').find('.span_apikey').value(data.result);
                  }
              });
           }
       });
    });

    // Regenerate key button
    $(".bt_copy_api").on('click', function (event) {
        var apiKeyField = $(this).closest('.mix-group').find('.span_apikey');
        if (document.selection) {
           var selectedRange = document.body.createTextRange();
           selectedRange.moveToElementText(apiKeyField[0]);
           selectedRange.select();
        }
        else if (window.getSelection) {
           var selectedRange = document.createRange();
           selectedRange.selectNodeContents(apiKeyField[0]);
           window.getSelection().removeAllRanges();
           window.getSelection().addRange(selectedRange);
        }
        document.execCommand('copy');
        notify('Info', '{{Clé copiée !}}', 'success');
    });
}
