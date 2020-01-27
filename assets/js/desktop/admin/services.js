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
        configuration: $('#services').getValues('.configKey:not(.noSet)')[0],
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function (data) {
            $('#services').setValues(data, '.configKey');
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
    $('#services').delegate('.configKey', 'change', function () {
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
    $("#bt_saveservices").on('click', function (event) {
        var config = $('#services').getValues('.configKey')[0];
        config.actionOnMessage = json_encode($('#div_actionOnMessage .actionOnMessage').getValues('.expressionAttr'));
        nextdom.config.save({
            configuration: config,
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function () {
                nextdom.config.load({
                    configuration: $('#services').getValues('.configKey:not(.noSet)')[0],
                    error: function (error) {
                        notify('Erreur', error.message, 'error');
                    },
                    success: function (data) {
                        $('#services').setValues(data, '.configKey');
                        modifyWithoutSave = false;
                        $(".bt_cancelModifs").hide();
                        notify('Info', '{{Sauvegarde réussie}}', 'success');
                    }
                });
            }
        });
    });

    // Repo test button
    $('.testRepoConnection').on('click',function(){
        var repo = $(this).attr('data-repo');
        nextdom.config.save({
            configuration: $('#services').getValues('.configKey')[0],
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function () {
                nextdom.config.load({
                    configuration: $('#services').getValues('.configKey:not(.noSet)')[0],
                    error: function (error) {
                        notify('Erreur', error.message, 'error');
                    },
                    success: function (data) {
                        $('#services').setValues(data, '.configKey');
                        modifyWithoutSave = false;
                        nextdom.repo.test({
                            repo: repo,
                            error: function (error) {
                                notify('Erreur', error.message, 'error');
                            },
                            success: function (data) {
                                notify('Info', '{{Test réussi}}', 'success');
                            }
                        });
                    }
                });
            }
        });
    });

    // Repo activation/desactivation
    $('#services').delegate('.enableRepository', 'change', function () {
        if($(this).value() == 1){
            $('.repositoryConfiguration'+$(this).attr('data-repo')).show();
        }else{
            $('.repositoryConfiguration'+$(this).attr('data-repo')).hide();
        }
    });
}
