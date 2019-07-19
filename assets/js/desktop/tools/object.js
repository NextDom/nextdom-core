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

$('#bt_graphObject').on('click', function () {
    $('#md_modal').dialog({title: "{{Graphique des liens}}"});
    $("#md_modal").load('index.php?v=d&modal=graph.link&filter_type=object&filter_id='+$('.objectAttr[data-l1key=id]').value()).dialog('open');
});

$('#bt_returnToThumbnailDisplay').on('click',function(){
    loadPage('index.php?v=d&p=object');
});

$(".bt_detailsObject").on('click', function (event) {
    $('#bt_returnToThumbnailDisplay').show();
    var object = $(this).closest(".objectDisplayCard");
    loadObjectConfiguration(object.attr("data-object_id"));
    $('.objectname_resume').empty().append(object.attr('data-object_icon')+'  '+object.attr('data-object_name'));
    if(document.location.toString().split('#')[1] == '' || document.location.toString().split('#')[1] == undefined){
        $('.nav-tabs a[href="#objecttab"]').click();
    }
    return false;
});

$('#bt_removeBackgroundImage').off('click').on('click', function () {
    nextdom.object.removeImage({
        view: $('.objectAttr[data-l1key=id]').value(),
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            notify("Info", '{{Image supprimée}}', 'success');
        },
    });
});

function loadObjectConfiguration(_id){
    try {
        $('#bt_uploadImage').fileupload('destroy');
        $('#bt_uploadImage').parent().html('<i class="fas fa-cloud-upload-alt"></i>{{Envoyer}}<input id="bt_uploadImage" type="file" name="file" style="display: inline-block;">');
    }catch(error) {
    }
    $('#bt_uploadImage').fileupload({
        replaceFileInput: false,
        url: 'core/ajax/object.ajax.php?action=uploadImage&id=' +_id,
        formData: {'nextdom_token': NEXTDOM_AJAX_TOKEN},
        dataType: 'json',
        done: function (e, data) {
            if (data.result.state != 'ok') {
                notify("Erreur", data.result.result, 'error');
                return;
            }
            notify("Info", '{{Image ajoutée}}', 'success');
        }
    });
    $(".objectDisplayCard").removeClass('active');
    $('.objectDisplayCard[data-object_id='+_id+']').addClass('active');
    $('#div_conf').show();
    $('#div_resumeObjectList').hide();
    $(this).addClass('active');
    nextdom.object.byId({
        id: _id,
        cache: false,
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            $('#objectId').value(_id);
            $('.objectAttr').value('');
            $('.objectAttr[data-l1key=father_id] option').show();
            $('#summarytab input[type=checkbox]').value(0);
            $('.object').setValues(data, '.objectAttr');
            if(data['display'] == ''){
                $('.objectAttr[data-l1key=display][data-l2key=tagColor]').value('#33B8CC');
                $('.objectAttr[data-l1key=display][data-l2key=tagTextColor]').value('#ffffff');
                $('.objectAttr[data-l1key=display][data-l2key="desktop::summaryTextColor"]').value('#ffffff');
                $('#colorpickTag').colorpicker('getValue', '#33B8CC');
                $('#colorpickTagText').colorpicker('getValue', '#ffffff');
                $('#colorpickSummaryText').colorpicker('getValue', '#ffffff');
            } else {
                $('#colorpickTag').colorpicker('setValue', data.display.tagColor);
                $('#colorpickTagText').colorpicker('setValue', data.display.tagTextColor);
                $('#colorpickSummaryText').colorpicker('setValue', data.display['desktop::summaryTextColor']);
            }
            $('.objectAttr[data-l1key=father_id] option[value=' + data.id + ']').hide();
            $('.div_summary').empty();
            $('.tabnumber').empty();
            if (isset(data.configuration) && isset(data.configuration.summary)) {
                for(var i in data.configuration.summary){
                    var el = $('.type'+i);
                    if(el != undefined){
                        for(var j in data.configuration.summary[i]){
                            addSummaryInfo(el,data.configuration.summary[i][j]);
                        }
                        if (data.configuration.summary[i].length != 0){
                            $('.summarytabnumber'+i).append('(' + data.configuration.summary[i].length + ')');
                        }
                    }

                }
            }
            var currentUrl = document.location.toString();
            // Mise à jour d'URL
            if (currentUrl.indexOf('id=') === -1) {
                var hashIndex = currentUrl.indexOf('#');
                var updatedUrl = '';
                if (hashIndex === -1) {
                    history.pushState({}, null, currentUrl + '&id=' + _id);
                }
                else {
                    updatedUrl = currentUrl.substr(0, hashIndex);
                    updatedUrl += '&id=' + scenarioId;
                    updatedUrl += currentUrl.substr(hashIndex);
                }
                history.pushState({}, null, updatedUrl);
            }
        }
    });
}

$("#bt_addObject,#bt_addObject2").on('click', function (event) {
    bootbox.prompt("Nom de l'objet ?", function (result) {
        if (result !== null) {
            nextdom.object.save({
                object: {name: result, isVisible: 1},
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data) {
                    modifyWithoutSave = false;
                    $('#bt_returnToThumbnailDisplay').hide();
                    loadObjectConfiguration(data.id);
                }
            });
        }
    });
});

jwerty.key('ctrl+s/⌘+s', function (e) {
    e.preventDefault();
    $("#bt_saveObject").click();
});

$('.objectAttr[data-l1key=display][data-l2key=icon]').on('dblclick',function(){
    $('.objectAttr[data-l1key=display][data-l2key=icon]').value('');
});

$("#bt_saveObject").on('click', function (event) {
    var object = $('.object').getValues('.objectAttr')[0];
    if (!isset(object.configuration)) {
        object.configuration = {};
    }
    if (!isset(object.configuration.summary)) {
        object.configuration.summary = {};
    }
    $('.object .div_summary').each(function () {
        var type = $(this).attr('data-type');
        object.configuration.summary[type] = [];
        summaries = {};
        $(this).find('.summary').each(function () {
            var summary = $(this).getValues('.summaryAttr')[0];
            object.configuration.summary[type].push(summary);
        });
    });
    nextdom.object.save({
        object: object,
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            modifyWithoutSave = false;
            notify("Info", '{{Sauvegarde effectuée avec succès}}', 'success');
        }
    });
    $('#bt_returnToThumbnailDisplay').show();
    return false;
});

$(".bt_removeObject").on('click', function (event) {
    $.hideAlert();
    var object = $(this);
    bootbox.confirm('{{Etes-vous sûr de vouloir supprimer l\'objet}} <span style="font-weight: bold ;">' + object.closest(".objectDisplayCard").attr("data-object_name") + '</span> ?', function (result) {
        if (result) {
            nextdom.object.remove({
                id: object.closest(".objectDisplayCard").attr("data-object_id"),
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function () {
                    modifyWithoutSave = false;
                    loadPage('index.php?v=d&p=object');
                    notify("Info", '{{Suppression effectuée avec succès}}', 'success');
                }
            });
        }
    });
    return false;
});


$('#bt_chooseIcon').on('click', function () {
    chooseIcon(function (_icon) {
        $('.objectAttr[data-l1key=display][data-l2key=icon]').empty().append(_icon);
    });
});

if (is_numeric(getUrlVars('id'))) {
    if ($('.objectDisplayCard[data-object_id=' + getUrlVars('id') + ']').length != 0) {
        $('.objectDisplayCard[data-object_id=' + getUrlVars('id') + ']').click();
    } else {
        $('.objectDisplayCard:first').click();
    }
}

$('#div_pageContainer').delegate('.objectAttr', 'change', function () {
    modifyWithoutSave = true;
});

$('.addSummary').on('click',function(){
    var type = $(this).attr('data-type');
    var el = $('.type'+type);
    addSummaryInfo(el);
});

$('#div_pageContainer').delegate(".listCmdInfo", 'click', function () {
    var el = $(this).closest('.summary').find('.summaryAttr[data-l1key=cmd]');
    nextdom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
        el.value(result.human);
    });
});

$('#div_pageContainer').delegate('.bt_removeSummary', 'click', function () {
    $(this).closest('.summary').remove();
});


function addSummaryInfo(_el, _summary) {
    if (!isset(_summary)) {
        _summary = {};
    }
    var div = '<div class="summary">';
    div += '<div class="form-group">';
    div += '  <label class="col-lg-2 col-md-2 col-sm-2 col-xs-6 control-label">{{Commande :}}</label>';
    div += '  <div class="col-lg-2 col-md-2 col-sm-2 col-xs-6">';
    div += '    <input type="checkbox" class="summaryAttr" data-l1key="enable" checked title="{{Activer}}" />';
    div += '    <label class="control-label label-check">{{Activer}}</label>';
    div += '  </div>';
    div += '  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 has-success">';
    div += '    <div class="input-group">';
    div += '      <span class="input-group-btn">';
    div += '        <a class="btn btn-danger bt_removeSummary btn-sm"><i class="fas fa-minus-circle"></i></a>';
    div += '      </span>';
    div += '      <input class="summaryAttr form-control input-sm" data-l1key="cmd" />';
    div += '      <span class="input-group-btn">';
    div += '        <a class="btn btn-sm listCmdInfo btn-default"><i class="fas fa-list-alt"></i></a>';
    div += '      </span>';
    div += '    </div>';
    div += '  </div>';
    div += '  <div class="col-lg-2 col-md-2 col-sm-2 col-xs-6">';
    div += '    <input type="checkbox" class="summaryAttr" data-l1key="invert" />';
    div += '    <label class="control-label label-check">{{Inverser}}</label>';
    div += '  </div>';
    div += '</div>';
    _el.find('.div_summary').append(div);
    _el.find('.summary:last').setValues(_summary, '.summaryAttr');
}

$('#bt_showObjectSummary').off('click').on('click', function () {
    $('#md_modal').dialog({title: "{{Résumé Objets}}"});
    $("#md_modal").load('index.php?v=d&modal=object.summary').dialog('open');
});

/**
 * Load object with the URL data
 */
function loadFromUrl() {
    var objectIdFromUrl = getUrlVars('id');
    if (is_numeric(objectIdFromUrl)) {
        if ($('.objectDisplayCard[data-object_id=' + objectIdFromUrl + ']').length !== 0) {
            var url = document.location.toString();
            loadObjectConfiguration(objectIdFromUrl);
        }
    }
}

loadFromUrl();
