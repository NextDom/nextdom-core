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
editorMobileJS = null;
editorMobileCSS = null;

jwerty.key('ctrl+s/⌘+s', function (e) {
   e.preventDefault();
   $("#bt_savecustom").click();
});

printConvertColor();
$.showLoading();
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


$('a[data-toggle="tab"][href="#advanced"]').on('shown.bs.tab', function () {
   editorDesktopJS = CodeMirror.fromTextArea(document.getElementById("ta_jsDesktopContent"), {
       lineNumbers: true,
       mode: "text/javascript",
       matchBrackets: true,
       viewportMargin: Infinity
   });
   editorDesktopCSS = CodeMirror.fromTextArea(document.getElementById("ta_cssDesktopContent"), {
       lineNumbers: true,
       mode: "text/css",
       matchBrackets: true,
       viewportMargin: Infinity
   });
});



$('a[data-toggle="tab"][href="#mobile"]').on('shown.bs.tab', function (e) {
   if (editorMobileCSS == null) {
       editorMobileCSS = CodeMirror.fromTextArea(document.getElementById("ta_cssMobileContent"), {
           lineNumbers: true,
           mode: "text/css",
           matchBrackets: true,
           viewportMargin: Infinity
       });
   }
   if (editorMobileJS == null) {
       editorMobileJS = CodeMirror.fromTextArea(document.getElementById("ta_jsMobileContent"), {
           lineNumbers: true,
           mode: "text/javascript",
           matchBrackets: true,
           viewportMargin: Infinity
       });
   }
});

function saveCustom() {
    if (editorDesktopJS !== null) {
        sendCustomData('desktop', 'js', editorDesktopJS.getValue());
        sendCustomData('desktop', 'css', editorDesktopCSS.getValue());
        if (editorMobileCSS !== null) {
            sendCustomData('mobile', 'js', editorMobileJS.getValue());
            sendCustomData('mobile', 'css', editorMobileCSS.getValue());
        }
    }
}

function sendCustomData(version, type, content) {
   nextdom.config.save({
       configuration: $('#custom').getValues('.configKey')[0],
       error: function (error) {
           notify("Erreur", error.message, 'error');
       },
       success: function () {
         nextdom.saveCustom({
           version: version,
           type: type,
           content: content,
           error: function (error) {
               notify("Erreur", error.message, 'error');
           },
           success: function (data) {
//               notify("Info", 'Sauvegarde réussie', 'success');
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
            // Change config dynamically
            widget_size = config['widget::size'];
            widget_margin = config['widget::margin'];
            widget_padding = config['widget::padding'];
            widget_radius = config['widget::radius'];
            console.log(widget_size);
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


/********************Convertion************************/
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

/*CMD color*/

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
