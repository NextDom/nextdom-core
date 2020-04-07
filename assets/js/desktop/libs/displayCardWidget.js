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

class DisplayCardWidget extends DisplayCardPageA {
    widget_parameters_opt = {
        desktop_width: {
            type: 'text',
            name: '{{Largeur desktop}} <sub>px</sub>'
        },
        mobile_width: {
            type: 'text',
            name: '{{Largeur mobile}} <sub>px</sub>'
        },
        time_widget: {
            type: 'checkbox',
            name: '{{Time widget}}'
        }
    };

    constructor() {
        super('widget');
    }

    save(widget) {
        var $this = this;
        widget.test = $('#div_programmation .test').getValues('.testAttr');
        nextdom.widget.save({
            widget: widget,
            error: function (error) {
                notify("Erreur", error.message, 'error');
            },
            success: function (data) {
                $this.displayA(data.id);
                notify("Info", '{{Sauvegarde réussie avec succès}}', 'success');
            }
        });
    }

    remove(id) {
        nextdom.widget.remove({
            id: id,
            error: function (error) {
                notify("Erreur", error.message, 'error');
            },
            success: function () {
                loadPage('index.php?v=d&p=widget');
                notify("Info", '{{Suppression effectuée avec succès}}', 'success');
            }
        });
    }

    loadInformations() {
    }

    clean() {
        $('#div_programmation').empty();
        $('#div_display_config').empty();
        $('#div_used_by').empty();
    }

    initEvents() {
        var $this = this;
        $('#div_used_by').off('click', '.cmdAdvanceConfigure').on('click', '.cmdAdvanceConfigure', function () {
            $('#md_modal').dialog({title: "{{Configuration de la commande}}"});
            $('#md_modal').load('index.php?v=d&modal=cmd.configure&cmd_id=' + $(this).attr('data-cmd_id')).dialog('open');
        });
        $('.attr[data-l1key=type]').off('change').on('change', function () {
            $this.clean();
            $('.selectSubType').hide().removeClass('attr');
            $('.selectSubType[data-type=' + $(this).value() + ']').show().addClass('attr').change();
        });

        $('.selectSubType').off('change').on('change', function () {
            $this.clean();
            $('.selectTemplate').hide().removeClass('attr');
            $('.selectTemplate[data-type=' + $('.attr[data-l1key=type]').value() + '][data-subtype=' + $(this).value() + ']').show().addClass('attr').change();
        });
        $('#bt_programmation_add_test').off('click').on('click', function () {
            addTest({});
        });
        $('#div_programmation').off('click', '.bt_removeTest').on('click', '.bt_removeTest', function () {
            $(this).closest('.test').remove();
        });
        $('#div_programmation').off('click', '.chooseIcon').on('click', '.chooseIcon', function () {
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
                    objectData.id = $('.attr[data-l1key=id]').value();
                    objectData.name = $('.attr[data-l1key=name]').value();
                    if (isset(objectData.test)) {
                        for (var i in objectData.test) {
                            addTest(objectData.test[i]);
                        }
                    }
                    $this.loadConfig('cmd.' + objectData.type + '.' + objectData.subtype + '.' + objectData.template, objectData);
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
            var widget = $('.widget').getValues('.attr')[0];
            widget.test = $('#div_programmation .test').getValues('.testAttr');
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
        $('.bt_applyToCmd').off('click').on('click', function () {
            //store usedBy:
            var checkedId = [];
            $('#div_usedBy .cmdAdvanceConfigure').each(function () {
                checkedId.push($(this).data('cmd_id'))
            });

            $('#md_modal').dialog({title: "{{Appliquer sur}}"}).load('index.php?v=d&modal=cmd.selectMultiple&type=' + $('.attr[data-l1key="type"]').value() + '&subtype=' + $('.attr[data-l1key="subtype"]').value() + '&name=' + $('.attr[data-l1key="name"]').value(), function () {
                initTableSorter();

                $('#table_cmdConfigureSelectMultiple tbody tr').each(function (index) {
                    if (checkedId.includes($(this).data('cmd_id'))) {
                        $(this).find('.selectMultipleApplyCmd').prop('checked', true)
                    }
                });

                $('#bt_cmdConfigureSelectMultipleAlertToogle').off('click').on('click', function () {
                    var state = false;
                    if ($(this).attr('data-state') == 0) {
                        state = true;
                        $(this).attr('data-state', 1)
                                .find('i').removeClass('fa-check-circle-o').addClass('fa-circle-o');
                        $('#table_cmdConfigureSelectMultiple tbody tr .selectMultipleApplyCmd:visible').value(1);
                    } else {
                        state = false;
                        $(this).attr('data-state', 0)
                                .find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o');
                        $('#table_cmdConfigureSelectMultiple tbody tr .selectMultipleApplyCmd:visible').value(0);
                    }
                });

                $('#bt_cmdConfigureSelectMultipleAlertApply').off().on('click', function () {
                    var widget = $('.widget').getValues('.attr')[0];
                    widget.test = $('#div_programmation .test').getValues('.testAttr');
                    nextdom.widget.save({
                        widget: widget,
                        error: function (error) {
                            $('#div_alert').showAlert({message: error.message, level: 'danger'});
                        },
                        success: function (data) {
                            modifyWithoutSave = false;
                            var cmd = {template: {dashboard: 'custom::' + $('.attr[data-l1key="name"]').value(), mobile: 'custom::' + $('.attr[data-l1key="name"]').value()}};
                            var cmdDefault = {template: {dashboard: 'default', mobile: 'default'}};
                            $('#table_cmdConfigureSelectMultiple tbody tr').each(function () {
                                var thisId = $(this).data('cmd_id');
                                if ($(this).find('.selectMultipleApplyCmd').prop('checked')) {
                                    if (!checkedId.includes(thisId)) {
                                        //show in usedBy
                                        var thisObject = $(this).find('td').eq(1).html();
                                        var thisEq = $(this).find('td').eq(2).html();
                                        var thisName = $(this).find('td').eq(3).html();
                                        var cmdHumanName = '[' + thisObject + '][' + thisEq + '][' + thisName + ']';
                                        var newSpan = '<span class="label label-info cursor cmdAdvanceConfigure" data-cmd_id="' + thisId + '">' + cmdHumanName + '</span>';
                                        $('#div_usedBy').append(newSpan);
                                    }
                                    cmd.id = thisId;
                                    nextdom.cmd.save({
                                        cmd: cmd,
                                        error: function (error) {
                                            $('#md_cmdConfigureSelectMultipleAlert').showAlert({message: error.message, level: 'danger'});
                                        },
                                        success: function () {}
                                    });

                                } else {
                                    if (checkedId.includes(thisId)) {
                                        cmdDefault.id = thisId;
                                        nextdom.cmd.save({
                                            cmd: cmdDefault,
                                            error: function (error) {
                                                $('#md_cmdConfigureSelectMultipleAlert').showAlert({message: error.message, level: 'danger'});
                                            },
                                            success: function (data) {
                                                $('#div_usedBy .cmdAdvanceConfigure[data-cmd_id="' + data.id + '"]').remove();
                                            }
                                        });
                                    }
                                }
                            });
                            $('#md_cmdConfigureSelectMultipleAlert').showAlert({message: "{{Modification(s) appliquée(s) avec succès}}", level: 'success'});
                        }
                    });
                });
            }).dialog('open');
        });
    }
    /**
     * Display an widget
     * @param id widget id
     */
    display(id) {
        var $this = this;
        nextdom.widget.get({
            id: id,
            error: function (error) {
                $('#div_alert').showAlert({message: error.message, level: 'danger'});
            },
            success: function (data) {
                $this.clean();
                $('id').value(id);
                $('.selectTemplate').off('change');
                $('.attr').value('');
                $('.attr[data-l1key=type]').value('info')
                $('.attr[data-l1key=subtype]').value($('.attr[data-l1key=subtype]').find('option:first').attr('value'));
                $('#div_conf').setValues(data, '.attr');
                if (isset(data.test)) {
                    for (var i in data.test) {
                        $this.addTest(data.test[i]);
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
                $this.loadConfig(template, data);
                //addOrUpdateUrl('id', data.id);
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

    addTest(_test) {
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
        $('#div_programmation').append(div);
        $('#div_programmation').find('.test').last().setValues(_test, '.testAttr');
    }

    getThemeImg(_light, _dark) {
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

    loadConfig(_template, _data) {
        var $this = this;
        $('.selectTemplate').off('change');
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
                        if ($this.widget_parameters_opt[data.replace[i]]) {
                            replace += '<label for="' + data.replace[i] + '" class="control-label">' + $this.widget_parameters_opt[data.replace[i]].name + '</label>';
                        } else {
                            replace += '<label for="' + data.replace[i] + '" class="control-label">' + capitalizeFirstLetter(data.replace[i].replace("icon_", "").replace("img_", "").replace("_", " ")) + '</label>';
                        }
                        replace += '<div class="mix-group">';
                        if (data.replace[i].indexOf('icon_') !== -1 || data.replace[i].indexOf('img_') !== -1) {
                            replace += '<a class="btn btn-action chooseIcon"><i class="fas fa-flag"></i><span>{{ Choisir }}</span></a>';
                            replace += '<div class="label label-icon attr" data-l1key="replace" data-l2key="#_' + data.replace[i] + '_#"></div>';
                        }
                        if ($this.widget_parameters_opt[data.replace[i]]) {
                            replace += '<input id="' + data.replace[i] + '" type="' + $this.widget_parameters_opt[data.replace[i]].type + '" class="form-control attr" data-l1key="replace" data-l2key="#_' + data.replace[i] + '_#"/>';
                        } else {
                            replace += '<input id="' + data.replace[i] + '" type="text" class="form-control attr" data-l1key="replace" data-l2key="#_' + data.replace[i] + '_#"/>';
                        }
                        replace += '</div>';
                        replace += '</div>';
                    }
                    $('#div_display_config').append(replace);
                } else {
                    $('.display_config').hide();
                }
                if (typeof _data !== 'undefined') {
                    $('.widget').setValues({replace: _data.replace}, '.attr');
                }
                if (data.test) {
                    $('.programmation').show();
                } else {
                    $('.programmation').hide();
                }
                $('.selectTemplate').on('change', function () {
                    if ($(this).value() === '' || !$(this).hasClass('attr')) {
                        return;
                    }
                    $this.loadConfig('cmd.' + $('.attr[data-l1key=type]').value() + '.' + $('.attr[data-l1key=subtype]').value() + '.' + $(this).value());
                });
                modifyWithoutSave = false;
            }
        });
    }

    downloadObjectAsJson(exportObj, exportName) {
        var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(exportObj));
        var downloadAnchorNode = document.createElement('a');
        downloadAnchorNode.setAttribute("href", dataStr);
        downloadAnchorNode.setAttribute("target", "_blank");
        downloadAnchorNode.setAttribute("download", exportName + ".json");
        document.body.appendChild(downloadAnchorNode); // required for firefox
        downloadAnchorNode.click();
        downloadAnchorNode.remove();
    }
}