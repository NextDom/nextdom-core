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


// variable
var widget_parameters_opt = {
    'desktop_width': {
        'type': 'text',
        'name': '{{Largeur desktop}} <sub>px</sub>'
    },
    'mobile_width': {
        'type': 'text',
        'name': '{{Largeur mobile}} <sub>px</sub>'
    },
    'time_widget': {
        'type': 'checkbox',
        'name': '{{Time widget}}'
    }
};
// Page init
loadInformations();
initEvents();
/**
 * Load informations in all forms of the page
 */
function loadInformations() {
    $('#div_usedBy').off('click', '.cmdAdvanceConfigure').on('click', '.cmdAdvanceConfigure', function () {
        $('#md_modal').dialog({title: "{{Configuration de la commande}}"});
        $('#md_modal').load('index.php?v=d&modal=cmd.configure&cmd_id=' + $(this).attr('data-cmd_id')).dialog('open');
    });
    $(".widgetDisplayCard").off('click').on('click', function (event) {
        if (event.ctrlKey) {
            var url = '/index.php?v=d&p=widget&id=' + $(this).attr('data-widget_id');
            window.open(url).focus();
        } else {
            displayWidget($(this).attr('data-widget_id'));
        }
    });
    $('.widgetDisplayCard').off('mouseup').on('mouseup', function (event) {
        if (event.which === 2) {
            event.preventDefault();
            var id = $(this).attr('data-widget_id');
            $('.widgetDisplayCard[data-widget_id="' + id + '"]').trigger(jQuery.Event('click', {ctrlKey: true}));
        }
    });
    if (is_numeric(getUrlVars('id'))) {
        if ($('.widgetDisplayCard[data-widget_id=' + getUrlVars('id') + ']').length !== 0) {
            $('.widgetDisplayCard[data-widget_id=' + getUrlVars('id') + ']').click();
        } else {
            $('.widgetDisplayCard').first().click();
        }
    }
}

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

if (is_numeric(getUrlVars('id'))) {
    if ($('.widgetDisplayCard[data-widget_id=' + getUrlVars('id') + ']').length !== 0) {
        $('.widgetDisplayCard[data-widget_id=' + getUrlVars('id') + ']').click();
    } else {
        $('.widgetDisplayCard').first().click();
    }
}

function cleanDiv() {
    $('#div_programation').empty();
    $('#div_display_config').empty();
    $('#div_used_by').empty();
}

/**
 * Init events on the profils page
 */
function initEvents() {
// Param changed : page leaving lock by msgbox
    $('#div_conf').delegate('.widgetAttr', 'change', function () {
        if (!lockModify) {
            modifyWithoutSave = true;
            $(".bt_cancelModifs").show();
        }
    });
    //change select box : selectWidgetSubType
    $('.widgetAttr[data-l1key=type]').off('change').on('change', function () {
        cleanDiv();
        $('.selectWidgetSubType').hide().removeClass('widgetAttr');
        $('.selectWidgetSubType[data-type=' + $(this).value() + ']').show().addClass('widgetAttr').change();
    });
    //change select box : selectWidgetTemplate
    $('.selectWidgetSubType').off('change').on('change', function () {
        cleanDiv();
        $('.selectWidgetTemplate').hide().removeClass('widgetAttr');
        $('.selectWidgetTemplate[data-type=' + $('.widgetAttr[data-l1key=type]').value() + '][data-subtype=' + $(this).value() + ']').show().addClass('widgetAttr').change();
    });
    // Cancel modifications
    $('.bt_cancelModifs').on('click', function () {
        loadFromUrl();
    });
    // widget go back list button
    $('#bt_widgetThumbnailDisplay').on('click', function () {
        loadPage('index.php?v=d&p=widget');
    });
    // widget display
    $('.widgetDisplayCard').on('click', function () {
        displayWidget($(this).attr('data-widget_id'));
        if (document.location.toString().split('#')[1] === '' || document.location.toString().split('#')[1] === undefined) {
            $('.nav-tabs a[href="#generaltab"]').click();
        }
    });
    // Packering widget on panel size change
    $('.accordion-toggle').off('click').on('click', function () {
        setTimeout(function () {
            $('.widgetListContainer').packery();
        }, 100);
    });
    // widget duplicate button
    $('#bt_duplicate').on('click', function () {
        bootbox.prompt("Nom ?", function (result) {
            if (result !== null) {
                var widget = $('.widget').getValues('.widgetAttr')[0];
                widget.test = $('#div_programation .test').getValues('.testAttr');
                widget.test = $('#div_programation .test').getValues('.testAttr');
                widget.name = result;
                widget.id = '';
                nextdom.widget.save({
                    widget: widget,
                    error: function (error) {
                        notify("Erreur", error.message, 'error');
                    },
                    success: function (data) {
                        modifyWithoutSave = false;
                        displayWidget(data.id);
                    }
                });
            }
        });
    });

    // Widget save button
    $("#bt_saveWidget").on('click', function () {
        var widget = $('.widget').getValues('.widgetAttr')[0];
        widget.test = $('#div_programation .test').getValues('.testAttr');
        nextdom.widget.save({
            widget: widget,
            error: function (error) {
                notify("Erreur", error.message, 'error');
            },
            success: function (data) {
                $('.widgetDisplayCard[data-widget_id=' + data.id + ']').click();
                modifyWithoutSave = false;
                $(".bt_cancelModifs").hide();
                notify("Info", '{{Sauvegarde réussie avec succès}}', 'success');
            }
        });
        $('#bt_widgetThumbnailDisplay').show();
    }
    );

    // Widget add new button
    $("#bt_addWidget").on('click', function () {
        bootbox.prompt("{{Nom de votre widget ?}}", function (result) {
            if (result !== null) {
                nextdom.widget.save({
                    widget: {name: result},
                    error: function (error) {
                        notify("Erreur", error.message, 'error');
                    },
                    success: function (data) {
                        $('#bt_widgetThumbnailDisplay').hide();
                        displayWidget(data.id);
                    }
                });
            }
        });
    });
// Widget delete button
    $("#bt_removeWidget").on('click', function () {
        bootbox.confirm('{{Etes-vous sûr de vouloir supprimer le widget}} <span style="font-weight: bold ;">' + $('.widgetDisplayCard.active .name').text() + '</span> ?', function (result) {
            if (result) {
                nextdom.widget.remove({
                    id: $('.widgetDisplayCard.active').attr('data-widget_id'),
                    error: function (error) {
                        notify("Erreur", error.message, 'error');
                    },
                    success: function () {
                        modifyWithoutSave = false;
                        loadPage('index.php?v=d&p=widget');
                        notify("Info", '{{Suppression effectuée avec succès}}', 'success');
                    }
                });
            }
        });
    });

// Icon delete on double click
    $('.widgetAttr[data-l1key=display][data-l2key=icon]').on('dblclick', function () {
        $('.widgetAttr[data-l1key=display][data-l2key=icon]').value('');
    });
// Icon choose button
    $('#bt_chooseIcon').on('click', function () {
        var icon = false;
        var color = false;
        if ( $('div[data-l2key="icon"] > i').length ) {
            color = '';
            class_icon = $('div[data-l2key="icon"] > i').attr('class');
            class_icon = class_icon.replace(' ', '.').split(' ');
            icon = '.'+class_icon[1];
            if(class_icon[2]){
                color = class_icon[2];
            }
        }
        chooseIcon(function (_icon) {
            $('.widgetAttr[data-l1key=display][data-l2key=icon]').empty().append(_icon);
        },{icon:icon,color:color});

    });

// Element dans div_programation
    $('#bt_programation_add_test').off('click').on('click', function () {
        addTest({});
    });
    $('#div_programation').off('click', '.bt_removeTest').on('click', '.bt_removeTest', function () {
        $(this).closest('.test').remove();
    });
    $('#div_programation').off('click', '.chooseIcon').on('click', '.chooseIcon', function () {
        var bt = $(this);
        chooseIcon(function (_icon) {
            bt.closest('.input-group').find('.testAttr').value(_icon);
        }, {img: true});
    });
    $("#bt_import").change(function (event) {
        $('#div_alert').hide();
        var uploadedFile = event.target.files[0];
        if (uploadedFile.type !== "application/json") {
            $('#div_alert').showAlert({message: "{{L'import de widgets se fait au format json à partir de widgets précedemment exporté.}}", level: 'danger'});
            return false;
        }
        if (uploadedFile) {
            var readFile = new FileReader();
            readFile.readAsText(uploadedFile);
            readFile.onload = function (e) {
                objectData = JSON.parse(e.target.result);
                if (!isset(objectData.jeedomCoreVersion)) {
                    $('#div_alert').showAlert({message: "{{Fichier json non compatible.}}", level: 'danger'});
                    return false;
                }
                objectData.id = $('.widgetAttr[data-l1key=id]').value();
                objectData.name = $('.widgetAttr[data-l1key=name]').value();
                if (isset(objectData.test)) {
                    for (var i in objectData.test) {
                        addTest(objectData.test[i]);
                    }
                }
                loadConfig('cmd.' + objectData.type + '.' + objectData.subtype + '.' + objectData.template, objectData);
            };
        } else {
            $('#div_alert').showAlert({message: "{{Problème lors de la lecture du fichier.}}", level: 'danger'});
            return false;
        }
    });

    $("#bt_import_main").change(function (event) {
        $('#div_alert').hide();
        var uploadedFile = event.target.files[0];
        if (uploadedFile.type !== "application/json") {
            $('#div_alert').showAlert({message: "{{L'import de widgets se fait au format json à partir de widgets précedemment exporté.}}", level: 'danger'});
            return false;
        }

        if (uploadedFile) {
            bootbox.prompt("Nom du widget ?", function (result) {
                if (result !== null) {
                    nextdom.widget.save({
                        widget: {name: result},
                        error: function (error) {
                            $('#div_alert').showAlert({message: error.message, level: 'danger'});
                        },
                        success: function (data) {
                            var readFile = new FileReader();
                            readFile.readAsText(uploadedFile);
                            readFile.onload = function (e) {
                                objectData = JSON.parse(e.target.result);
                                if (!isset(objectData.jeedomCoreVersion)) {
                                    $('#div_alert').showAlert({message: "{{Fichier json non compatible.}}", level: 'danger'});
                                    return false;
                                }
                                objectData.id = data.id;
                                objectData.name = data.name;
                                if (isset(objectData.test)) {
                                    for (var i in objectData.test) {
                                        addTest(objectData.test[i]);
                                    }
                                }
                                nextdom.widget.save({
                                    widget: objectData,
                                    error: function (error) {
                                        $('#div_alert').showAlert({message: error.message, level: 'danger'});
                                    },
                                    success: function (data) {
                                        loadPage('index.php?v=d&p=widget&id=' + objectData.id + '&saveSuccessFull=1');
                                    }
                                });
                            };
                        }
                    });
                }
            });
        } else {
            $('#div_alert').showAlert({message: "{{Problème lors de la lecture du fichier.}}", level: 'danger'});
            return false;
        }
    });
    $("#bt_export").on('click', function (event) {
        var widget = $('.widget').getValues('.widgetAttr')[0];
        widget.test = $('#div_programation .test').getValues('.testAttr');
        widget.id = "";
        nextdom.version({success: function (version) {
                widget.jeedomCoreVersion = version.jeedom;
                //widget.nextdomCoreVersion = version.nextdom;
                downloadObjectAsJson(widget, widget.name);
            }
        });
    });
    $('#bt_replacement').off('click').on('click', function () {
        $('#md_modal').dialog({title: "{{Remplacement de widget}}"}).load('index.php?v=d&modal=widget.replace').dialog('open');
        $('#md_modal').dialog("option", "width", 800).dialog("option", "height", 500);
        $("#md_modal").dialog({
            position: {
                my: "center center",
                at: "center center",
                of: window
            }
        });
    });
}
/**
 * Display an widget
 *
 * @param _id widget id
 */
function displayWidget(_id) {
    $('#div_conf').show();
    $('#widgetThumbnailDisplay').hide();
    $('.widgetDisplayCard').removeClass('active');
    $('.widgetDisplayCard[data-widget_id=' + _id + ']').addClass('active');
    nextdom.widget.get({
        id: _id,
        error: function (error) {
            $('#div_alert').showAlert({message: error.message, level: 'danger'});
        },
        success: function (data) {
            cleanDiv();
            $('#widgetId').value(_id);
            $('.selectWidgetTemplate').off('change')
            $('.widgetAttr').value('');
            $('.widget').setValues(data, '.widgetAttr');
            $('.widgetAttr[data-l1key=type]').value('info');
            $('.widgetAttr[data-l1key=subtype]').value($('.widgetAttr[data-l1key=subtype]').find('option:first').attr('value'));
            $('.widget').setValues(data, '.widgetAttr');
            if (isset(data.test)) {
                for (var i in data.test) {
                    addTest(data.test[i]);
                }
            }
            var usedBy = '';
            var usedByNb = 0;
            if (isset(data.usedByList)) {
                for (var i in data.usedByList) {
                    usedByNb += 1;
                    usedBy += '<span class="label label-info cursor cmdAdvanceConfigure" data-cmd_id="' + i + '">' + data.usedByList[i] + '</span> ';
                }
            } else {
                usedBy += 'Pas de commande pour ce widget';
            }
            $('#div_used_by').value(usedBy);
            $('#usedByListNb').value(usedByNb);
            var template = 'cmd.';
            if (data.type && data.type !== null) {
                template += data.type + '.';
            } else {
                template += 'action.';
            }
            if (data.subtype && data.subtype !== null) {
                template += data.subtype + '.';
            } else {
                template += 'other.';
            }
            if (data.template && data.template !== null) {
                template += data.template;
            } else {
                template += 'tmplicon';
            }
            loadConfig(template, data);
            //addOrUpdateUrl('id', data.id);
            modifyWithoutSave = false;
            nextdom.widget.getPreview({
                id: data.id,
                cache: false,
                error: function (error) {
                    $('#div_display_preview').empty().html('<div class="alert alert-warning">Aucune commande affectée au widget, prévisualisation impossible</div>');
                },
                success: function (data) {
                    $('#div_display_preview').empty().html(data.html);
                    $('#div_display_preview .eqLogic-widget').css('position', 'relative');
                }
            });
        }
    });
}

/**
 * Load widget with the URL data
 */
function loadFromUrl() {
    var widgetIdFromUrl = getUrlVars('id');
    if (is_numeric(widgetIdFromUrl)) {
        if ($('.widgetDisplayCard[data-widget_id=' + widgetIdFromUrl + ']').length !== 0) {
            var url = document.location.toString();
            displayWidget(widgetIdFromUrl);
        }
    }
}

function addTest(_test) {
    if (!isset(_test)) {
        _trigger = {};
    }
    var div = '<div class="test">';
    div += '<div class="form-group">';
    div += '<div class="mix-group">';
    div += '<label class="control-label">{{Test}}</label>';
    div += '<div class="col-sm-3">';
    div += '<div class="input-group">';
    div += '<span class="input-group-btn">';
    div += '<a class="btn btn-sm bt_removeTest roundedLeft"><i class="fas fa-minus-circle"></i></a>';
    div += '</span>';
    div += '<input type="text" class="testAttr form-control roundedRight" data-l1key="operation" placeholder="Test, utiliser #value# pour la valeur"/>';
    div += '</div>';
    div += '</div>';
    div += '<div class="col-sm-3">';
    div += '<div class="input-group">';
    div += '<input type="text" class="testAttr form-control roundedLeft" data-l1key="state_light" placeholder="Résultat si test ok (light)"/>';
    div += '<span class="input-group-btn">';
    div += '<a class="btn btn-sm chooseIcon roundedRight"><i class="fas fa-flag"></i></a>';
    div += '</span>';
    div += '</div>';
    div += '</div>';
    div += '<div class="col-sm-3">';
    div += '<div class="input-group">';
    div += '<input type="text" class="testAttr form-control roundedLeft" data-l1key="state_dark" placeholder="Résultat si test ok (dark)"/>';
    div += '<span class="input-group-btn">';
    div += '<a class="btn btn-sm chooseIcon roundedRight"><i class="fas fa-flag"></i></a>';
    div += '</span>';
    div += '</div>';
    div += '</div>';
    div += '</div>';
    div += '</div>';
    div += '</div>';
    $('#div_programation').append(div);
    $('#div_programation').find('.test').last().setValues(_test, '.testAttr');
}

function getThemeImg(_light, _dark) {
    if (_light !== '' && _dark === '') {
        return _light;
    }
    if (_light === '' && _dark !== '') {
        return _dark;
    }
    if ($('body')[0].hasAttribute('data-theme')) {
        if ($('body').attr('data-theme').endsWith('Light'))
            return _light;
    }
    return _dark;
}

function loadConfig(_template, _data) {
    $('.selectWidgetTemplate').off('change');
    nextdom.widget.loadConfig({
        template: _template,
        error: function (error) {
            $('#div_alert').showAlert({message: error.message, level: 'danger'});
        },
        success: function (data) {
            $('#div_display_config').empty();
            if (typeof data.replace !== 'undefined' && data.replace.length > 0) {
                $('.display_config').show();
                var replace = '';
                for (var i in data.replace) {
                    replace += '<div class="form-group col-sm-12 col-xs-12 col-padding">';
                    if (widget_parameters_opt[data.replace[i]]) {
                        replace += '<label for="' + data.replace[i] + '" class="control-label">' + widget_parameters_opt[data.replace[i]].name + '</label>';
                    } else {
                        replace += '<label for="' + data.replace[i] + '" class="control-label">' + capitalizeFirstLetter(data.replace[i].replace("icon_", "").replace("img_", "").replace("_", " ")) + '</label>';
                    }
                    replace += '<div class="mix-group">';
                    if (data.replace[i].indexOf('icon_') !== -1 || data.replace[i].indexOf('img_') !== -1) {
                        replace += '<a class="btn btn-action chooseIcon"><i class="fas fa-flag"></i><span>{{ Choisir }}</span></a>';
                        replace += '<div class="label label-icon widgetAttr" data-l1key="replace" data-l2key="#_' + data.replace[i] + '_#"></div>';
                    }
                    if (widget_parameters_opt[data.replace[i]]) {
                        replace += '<input id="' + data.replace[i] + '" type="' + widget_parameters_opt[data.replace[i]].type + '" class="form-control widgetAttr" data-l1key="replace" data-l2key="#_' + data.replace[i] + '_#"/>';
                    } else {
                        replace += '<input id="' + data.replace[i] + '" type="text" class="form-control widgetAttr" data-l1key="replace" data-l2key="#_' + data.replace[i] + '_#"/>';
                    }
                    replace += '</div>';
                    replace += '</div>';
                }
                $('#div_display_config').append(replace);
            } else {
                $('.display_config').hide();
            }
            if (typeof _data !== 'undefined') {
                $('.widget').setValues({replace: _data.replace}, '.widgetAttr');
            }
            if (data.test) {
                $('.programation').show();
            } else {
                $('.programation').hide();
            }
            $('.selectWidgetTemplate').on('change', function () {
                if ($(this).value() === '' || !$(this).hasClass('widgetAttr')) {
                    return;
                }
                loadConfig('cmd.' + $('.widgetAttr[data-l1key=type]').value() + '.' + $('.widgetAttr[data-l1key=subtype]').value() + '.' + $(this).value());
            });
            modifyWithoutSave = false;
        }
    });
}

function downloadObjectAsJson(exportObj, exportName) {
    var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(exportObj));
    var downloadAnchorNode = document.createElement('a');
    downloadAnchorNode.setAttribute("href", dataStr);
    downloadAnchorNode.setAttribute("target", "_blank");
    downloadAnchorNode.setAttribute("download", exportName + ".json");
    document.body.appendChild(downloadAnchorNode); // required for firefox
    downloadAnchorNode.click();
    downloadAnchorNode.remove();
}