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
editorDesktopJS = null;
editorDesktopCSS = null;
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
        if (url.split('#')[1] == "advanced") {
            printAdvancedDesktop();
        }
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

    // Advanced custom tab loading
    $('a[data-toggle="tab"][href="#advanced"]').on('shown.bs.tab', function () {
        printAdvancedDesktop();
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
                nextdom_waitSpinner = config['nextdom::waitSpinner'];
                nextdom.config.load({
                    configuration: $('#custom').getValues('.configKey:not(.noSet)')[0],
                    error: function (error) {
                        notify("Erreur", error.message, 'error');
                    },
                    success: function (data) {
                        $('#custom').setValues(data, '.configKey');
                        modifyWithoutSave = false;
                        // Relaod theme
                        updateTheme(function() {
                            notify("Info", '{{Sauvegarde réussie}}', 'success');
                            window.location.reload();
                        });
                    }
                });
            }
        });
        saveCustom();
    });

    // Custom spinner change
    $("#waitSpinnerSelect").change(function () {
        document.getElementById("waitSpinner").innerHTML="<i class='fas fa-info'></i>";
        $("#waitSpinner i").removeClass('fa-info').addClass($("#waitSpinnerSelect").value());
        modifyWithoutSave = true;
    });

    // Theme choice changed
    $("input[name=theme]").click(function () {
        changeThemeColors($(this).attr('data-l2key'),true);
    });
}

/**
 * Display the personnalisation
 */
function printAdvancedDesktop() {
    if (editorDesktopJS == null) {
        editorDesktopJS = CodeMirror.fromTextArea(document.getElementById("ta_jsDesktopContent"), {
            lineNumbers: true,
            mode: "text/javascript",
            matchBrackets: true,
            viewportMargin: Infinity
        });
    }
    if (editorDesktopCSS == null) {
        editorDesktopCSS = CodeMirror.fromTextArea(document.getElementById("ta_cssDesktopContent"), {
            lineNumbers: true,
            mode: "text/css",
            matchBrackets: true,
            viewportMargin: Infinity
        });
    }
}

/**
 * Save all custom personnalisation
 */
function saveCustom() {
    if (editorDesktopJS !== null) {
        sendCustomData('js', editorDesktopJS.getValue());
    }
    if (editorDesktopCSS !== null) {
        sendCustomData('css', editorDesktopCSS.getValue());
    }
}

/**
 * Save a cutom personnalisation
 *
 * @param type advanced custom type (css or js)
 * @param content custom code data
 */
function sendCustomData(type, content) {
    nextdom.config.save({
        configuration: $('#custom').getValues('.configKey')[0],
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            nextdom.saveCustom({
                type: type,
                content: content,
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                }
            });
        }
    });
}
