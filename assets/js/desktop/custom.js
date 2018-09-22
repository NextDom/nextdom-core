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

editorDesktopJS = null;
editorDesktopCSS = null;
editorMobileJS = null;
editorMobileCSS = null;

jwerty.key('ctrl+s', function (e) {
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


setTimeout(function () {
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
}, 1);

$('#div_pageContainer').delegate('.configKey', 'change', function () {
   modifyWithoutSave = true;
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

$('.savecustom').on('click', function () {
   var version = $(this).attr('data-version');
   var type = $(this).attr('data-type');
   var content = '';
   var editor = null;
   if (version == 'desktop' && type == 'js') {
       editor = editorDesktopJS;
   }
   if (version == 'desktop' && type == 'css') {
       editor = editorDesktopCSS;
   }
   if (version == 'mobile' && type == 'js') {
       editor = editorMobileJS;
   }
   if (version == 'mobile' && type == 'css') {
       editor = editorMobileCSS;
   }
   if (editor != null) {
       content = editor.getValue();
   }

   nextdom.config.save({
       configuration: $('#custom').getValues('.configKey')[0],
       error: function (error) {
           notify("Erreur", error.message, 'error');
       },
       success: function () {
         nextdom.saveCustum({
           version: version,
           type: type,
           content: content,
           error: function (error) {
               notify("Erreur", error.message, 'error');
           },
           success: function (data) {
               notify("Info", 'Sauvegarde réussie', 'success');
           }
       });
     }
 });
});

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
   modifyWithoutSave = true;
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

$('.colorpick').colorpicker();