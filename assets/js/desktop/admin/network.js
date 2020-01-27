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
        configuration: $('#network').getValues('.configKey:not(.noSet)')[0],
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function (data) {
            $('#network').setValues(data, '.configKey');
            $('.configKey[data-l1key="market::allowDNS"]').trigger('change');
            $('.configKey[data-l1key="ldap:enable"]').trigger('change');
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
    $('#network').delegate('.configKey', 'change', function () {
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
    $("#bt_savenetwork").on('click', function (event) {
        nextdom.config.save({
            configuration: $('#network').getValues('.configKey')[0],
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function () {
                nextdom.config.load({
                    configuration: $('#network').getValues('.configKey')[0],
                    plugin: 'core',
                    error: function (error) {
                        notify('Erreur', error.message, 'error');
                    },
                    success: function (data) {
                        $('#network').setValues(data, '.configKey');
                        modifyWithoutSave = false;
                        notify('Info', '{{Sauvegarde réussie}}', 'success');
                    }
                });
            }
        });
    });

    // BDNS restart button
    $('#bt_restartDns').on('click', function () {
        jeedom.config.save({
            configuration: $('#network').getValues('.configKey')[0],
            error: function (error) {
                notify('Core',error.message,"error");
                },
            success: function () {
                jeedom.network.restartDns({
                    error: function (error) {
                        notify('Core',error.message,"error");
                        },
                    success: function (data) {
                        modifyWithoutSave = false;
                        $(".bt_cancelModifs").hide();
                        location.reload();
                    }
                });
            }
        });
    });

    // DNS halt button
    $('#bt_haltDns').on('click', function () {
        jeedom.config.save({
            configuration: $('#network').getValues('.configKey')[0],
            error: function (error) {
                notify('Core',error.message,"error");
                },
            success: function () {
                jeedom.network.stopDns({
                    error: function (error) {
                        notify('Core',error.message,"error");
                        },
                    success: function (data) {
                        modifyWithoutSave = false;
                        $(".bt_cancelModifs").hide();
                        location.reload();
                    }
                });
            }
        });
    });

    // DNS changes
    $('#network').delegate('.configKey[data-l1key="market::allowDNS"],.configKey[data-l1key="network::disableMangement"]', 'change', function () {
        setTimeout(function() {
            if ($('.configKey[data-l1key="market::allowDNS"]').value() == 1 && $('.configKey[data-l1key="network::disableMangement"]').value() == 0) {
                $('.configKey[data-l1key=externalProtocol]').attr('disabled', true);
                $('.configKey[data-l1key=externalAddr]').attr('disabled', true);
                $('.configKey[data-l1key=externalPort]').attr('disabled', true);
                $('.configKey[data-l1key=externalAddr]').value('');
                $('.configKey[data-l1key=externalPort]').value('');
            } else {
                $('.configKey[data-l1key=externalProtocol]').attr('disabled', false);
                $('.configKey[data-l1key=externalAddr]').attr('disabled', false);
                $('.configKey[data-l1key=externalPort]').attr('disabled', false);
            }
        }, 100);
    });
}
