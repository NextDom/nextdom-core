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

showLoadingCustom();
printConvertColor();
$('.colorpick').colorpicker();

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

$("#bt_savecustom").on('click', function (event) {
    $.hideAlert();
    saveConvertColor();
    var config = $('#custom').getValues('.configKey')[0];
    nextdom.config.save({
        configuration: config,
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            updateTheme(null);
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
                    notify("Info", '{{Sauvegarde réussie}}', 'success');
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

function printConvertColor() {
    $.ajax({
        type: "POST",
        url: "core/ajax/config.ajax.php",
        data: {
            action: "getKey",
            key: 'convertColor'
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                notify("Erreur", data.result, 'error');
                return;
            }

            $('#table_convertColor tbody').empty();
            for (var color in data.result) {
                addConvertColor(color, data.result[color]);
            }
            modifyWithoutSave = false;
        }
    });
}

function addConvertColor(_color, _html) {
    var tr = '<tr>';
    tr += '<td>';
    tr += '<input class="color form-control input-sm" value="' + init(_color) + '"/>';
    tr += '</td>';
    tr += '<td>';
    tr += '<input type="color" class="html form-control input-sm" value="' + init(_html) + '" />';
    tr += '</td>';
    tr += '</tr>';
    $('#table_convertColor tbody').append(tr);
    modifyWithoutSave = false;
}

function saveConvertColor() {
    var value = {};
    var colors = {};
    $('#table_convertColor tbody tr').each(function () {
        colors[$(this).find('.color').value()] = $(this).find('.html').value();
    });
    value.convertColor = colors;
    $.ajax({
        type: "POST",
        url: "core/ajax/config.ajax.php",
        data: {
            action: 'addKey',
            value: json_encode(value)
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                notify("Core", data.result, 'error');
                return;
            }
            modifyWithoutSave = false;
        }
    });
}

$('.bt_resetColor').on('click', function () {
    var el = $(this);
    nextdom.getConfiguration({
        key: $(this).attr('data-l1key'),
        default: 1,
        error: function (error) {
            notify("Core", error.message, 'error');
        },
        success: function (data) {
            $('.configKey[data-l1key="' + el.attr('data-l1key') + '"]').value(data);
        }
    });
});

$("input[name=theme]").click(function () {
    var radio = $(this).val();
    var config ="";
    if (radio == "dark"){
        config = {
            'theme:color1' : '#33b8cc',
            'theme:color2' : '#ffffff',
            'theme:color3' : '#ffffff',
            'theme:color4' : '#33b8cc',
            'theme:color5' : '#ffffff',
            'theme:color6' : '#222d32',
            'theme:color7' : '#1e282c',
            'theme:color8' : '#2c3b41',
            'theme:color9' : '#2c3b41',
            'theme:color10' : '#222d32',
            'theme:color11' : '#2c3b41',
            'theme:color12' : '#e6e7e8',
            'theme:color13' : '#484c52',
            'theme:color14' : '#484c52',
            'theme:color15' : '#222d32',
            'theme:color16' : '#666666',
            'theme:color17' : '#2c3b41',
            'theme:color18' : '#e6e7e8',
            'theme:color19' : '#8aa4af',
            'theme:color20' : '#222d32',
            'theme:color21' : '50',
            'theme:color22' : '#263238',
        }
    }
    if (radio == "white"){
        config = {
            'theme:color1' : '#33b8cc',
            'theme:color2' : '#ffffff',
            'theme:color3' : '#f4f4f5',
            'theme:color4' : '#33B8CC',
            'theme:color5' : '#ffffff',
            'theme:color6' : '#f9fafc',
            'theme:color7' : '#dbdbdb',
            'theme:color8' : '#f4f4f5',
            'theme:color9' : '#ecf0f5',
            'theme:color10' : '#ffffff',
            'theme:color11' : '#f5f5f5',
            'theme:color12' : '#555555',
            'theme:color13' : '#f5f5f5',
            'theme:color14' : '#dddddd',
            'theme:color15' : '#ffffff',
            'theme:color16' : '#dddddd',
            'theme:color17' : '#f4f4f4',
            'theme:color18' : '#555555',
            'theme:color19' : '#555555',
            'theme:color20' : '#dddddd',
            'theme:color21' : '100',
            'theme:color22' : '#fafafa',
        }
    }
    if (radio == "mix"){
        config = {
            'theme:color1' : '#33b8cc',
            'theme:color2' : '#ffffff',
            'theme:color3' : '#ffffff',
            'theme:color4' : '#33B8CC',
            'theme:color5' : '#ffffff',
            'theme:color6' : '#222d32',
            'theme:color7' : '#1e282c',
            'theme:color8' : '#2c3b41',
            'theme:color9' : '#ecf0f5',
            'theme:color10' : '#ffffff',
            'theme:color11' : '#f5f5f5',
            'theme:color12' : '#555555',
            'theme:color13' : '#ffffff',
            'theme:color14' : '#dddddd',
            'theme:color15' : '#fafafa',
            'theme:color16' : '#666666',
            'theme:color17' : '#f4f4f4',
            'theme:color18' : '#e6e7e8',
            'theme:color19' : '#8aa4af',
            'theme:color20' : '#dddddd',
            'theme:color21' : '100',
            'theme:color22' : '#fafafa',
        }
    }
    nextdom.config.save({
        configuration: config,
        success: function () {
            updateTheme(function() {
                notify("Info", '{{Thème parametré !}}', 'success');
                window.location.reload();
            });
        }
    });
});

function updateTheme(successFunc) {
    $.ajax({
        url: 'core/ajax/config.ajax.php',
        type: 'GET',
        data: {'action': 'updateTheme', 'nextdom_token': NEXTDOM_AJAX_TOKEN},
        success: successFunc
    });
}
