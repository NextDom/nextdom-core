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

$("#bt_saveupdate_admin").on('click', function (event) {
    $.hideAlert();
    var config = $('#update_admin').getValues('.configKey')[0];
    config.actionOnMessage = json_encode($('#div_actionOnMessage .actionOnMessage').getValues('.expressionAttr'));
    nextdom.config.save({
        configuration: config,
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            nextdom.config.load({
                configuration: $('#update_admin').getValues('.configKey:not(.noSet)')[0],
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    $('#update_admin').setValues(data, '.configKey');
                    modifyWithoutSave = false;
                    notify("Info", '{{Sauvegarde réussie}}', 'success');
                }
            });
        }
    });
});

nextdom.config.load({
    configuration: $('#update_admin').getValues('.configKey:not(.noSet)')[0],
    error: function (error) {
        notify("Erreur", error.message, 'error');
    },
    success: function (data) {
        $('#update_admin').setValues(data, '.configKey');

        modifyWithoutSave = false;
    }
});

$('#update_admin').delegate('.configKey', 'change', function () {
    modifyWithoutSave = true;
});

$('.testRepoConnection').on('click',function(){
    var repo = $(this).attr('data-repo');
    nextdom.config.save({
        configuration: $('#update_admin').getValues('.configKey')[0],
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            nextdom.config.load({
                configuration: $('#update_admin').getValues('.configKey:not(.noSet)')[0],
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    $('#update_admin').setValues(data, '.configKey');
                    modifyWithoutSave = false;
                    nextdom.repo.test({
                        repo: repo,
                        error: function (error) {
                            notify("Erreur", error.message, 'error');
                        },
                        success: function (data) {
                            notify("Info", '{{Test réussi}}', 'success');
                        }
                    });
                }
            });
        }
    });
});

$('#update_admin').delegate('.enableRepository', 'change', function () {
    if($(this).value() == 1){
        $('.repositoryConfiguration'+$(this).attr('data-repo')).show();
    }else{
        $('.repositoryConfiguration'+$(this).attr('data-repo')).hide();
    }
});
