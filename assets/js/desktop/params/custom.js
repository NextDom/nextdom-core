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
loadInformations();
initEvents();

/**
 * Show the tab indicated in the url
 *
 * @param url Url to check
 */
function showSelectedTabFromUrl(url) {
    if (url.match('#')) {
        $('.nav-tabs a[href="#' + url.split('#')[1] + '"]').tab('show');
    }
    $('.nav-tabs a').on('shown.bs.tab', function (e) {
        window.location.hash = e.target.hash;
    });
}

/**
 * Load informations in all forms of the page
 */
function loadInformations() {
    nextdom.config.load({
        configuration: $('#custom').getValues('.configKey:not(.noSet)')[0],
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            $('#custom').setValues(data, '.configKey');
            modifyWithoutSave = false;
        }
    });
}

/**
 * Init events on the page
 */
function initEvents() {
    // Show confirm modal on non saved changes
    $('#custom').delegate('.configKey:not(.noSet)', 'change', function () {
        modifyWithoutSave = true;
    });

    // Theme config changing
    $("#themeBase").on('change', function (event) {
        $('.configKey[data-l1key="nextdom::user-theme"]').value($("#themeBase").value() + "-" + $("#themeIdentity").value());
        $('#customPreview').contents().find("head").append($("<link href='/public/css/themes/" + $('.configKey[data-l1key="nextdom::user-theme"]').value() + ".css' rel='stylesheet'>"));
    });
    $("#themeIdentity").on('change', function (event) {
        $('.configKey[data-l1key="nextdom::user-theme"]').value($("#themeBase").value() + "-" + $("#themeIdentity").value());
        $('#customPreview').contents().find("head").append($("<link href='/public/css/themes/" + $('.configKey[data-l1key="nextdom::user-theme"]').value() + ".css' rel='stylesheet'>"));
    });

    // Save customs
    $("#bt_savecustom").on('click', function (event) {
        var config = $('#custom').getValues('.configKey')[0];
        nextdom.config.save({
            configuration: config,
            error: function (error) {
                notify("Erreur", error.message, 'error');
            },
            success: function () {
                // Change config dynamically
                widget_size = config['widget::size'];
                widget_margin = config['widget::margin'];
                widget_padding = config['widget::padding'];
                widget_radius = config['widget::radius'];
                nextdom.config.load({
                    configuration: $('#custom').getValues('.configKey:not(.noSet)')[0],
                    error: function (error) {
                        notify("Erreur", error.message, 'error');
                    },
                    success: function (data) {
                        $('#custom').setValues(data, '.configKey');
                        modifyWithoutSave = false;
                        notify("Info", '{{Sauvegarde réussie}}', 'success');
                        window.location.reload();
                    }
                });
            }
        });
        saveCustom();
    });
}
