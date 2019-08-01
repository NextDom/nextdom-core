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
editorDesktopJS = null;
editorDesktopCSS = null;

$('.colorpick').colorpicker();

nextdom.config.load({
    configuration: $('#custom').getValues('.configKey:not(.noSet)')[0],
    error: function (error) {
        notify("Erreur", error.message, 'error');
    },
    success: function (data) {
        $('#custom').setValues(data, '.configKey');
        $("#theme-"+$('.configKey[data-l1key="nextdom::theme"]').value()).attr("checked","checked");
        if ($('.configKey[data-l1key="nextdom::theme"]').value() == 'custom') {
            $('#theme4').show();
        }else{
            $('#theme4').hide();
        }
        modifyWithoutSave = false;
    }
});

var url = document.location.toString();
if (url.match('#')) {
    $('.nav-tabs a[href="#' + url.split('#')[1] + '"]').tab('show');
    if (url.split('#')[1] == "desktop") {
        $('.nav-tabs a[href="#advanced"]').tab('show');
    }
    if (url.split('#')[1] == "desktop" || url.split('#')[1] == "advanced") {
        printAdvancedDesktop();
    }
}

$('.nav-tabs a').on('shown.bs.tab', function (e) {
    window.location.hash = e.target.hash;
})

jwerty.key('ctrl+s/⌘+s', function (e) {
    e.preventDefault();
    $("#bt_savecustom").click();
});

$('a[data-toggle="tab"][href="#advanced"]').on('shown.bs.tab', function () {
    printAdvancedDesktop();
});

$('a[data-toggle="tab"][href="#desktop"]').on('shown.bs.tab', function (e) {
    printAdvancedDesktop();
});

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

function saveCustom() {
    if (editorDesktopJS !== null) {
        sendCustomData('js', editorDesktopJS.getValue());
    }
    if (editorDesktopCSS !== null) {
        sendCustomData('css', editorDesktopCSS.getValue());
    }
}

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

$(".colorpick input").change(function () {
    $('.configKey[data-l1key="nextdom::theme"]').value('custom');
});

$("#bt_savecustom").on('click', function (event) {
    $.hideAlert();
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

$("#waitSpinnerSelect").change(function () {
    document.getElementById("waitSpinner").innerHTML="<i class='fas fa-info'></i>";
    $("#waitSpinner i").removeClass('fa-info').addClass($("#waitSpinnerSelect").value());
});

$('.bt_resetColor').on('click', function () {
    nextdom.getConfiguration({
        key: $(this).attr('data-l1key'),
        default: 1,
        error: function (error) {
            notify("Core", error.message, 'error');
        },
        success: function (data) {
            $('.configKey[data-l1key="' + $(this).attr('data-l1key') + '"]').parent().colorpicker('setValue', data);
        }
    });
});

$("input[name=theme]").click(function () {
    var radio = $(this).val();
    $('.configKey[data-l1key="nextdom:theme"]').value(radio);
    changeThemeColors(radio,true);
});
