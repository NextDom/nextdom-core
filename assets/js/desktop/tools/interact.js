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
    $("#div_action").sortable({axis: "y", cursor: "move", items: ".action", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
    loadFromUrl();
    setTimeout(function(){
        $('.interactListContainer').packery();
    },100);
}

/**
 * Init events on the profils page
 */
function initEvents() {
    // Param changed : page leaving lock by msgbox
    $('#div_conf').delegate('.interactAttr', 'change', function () {
        if (!lockModify) {
            modifyWithoutSave = true;
            $(".bt_cancelModifs").show();
        }
    });

    // Cancel modifications
    $('.bt_cancelModifs').on('click', function () {
        loadFromUrl();
    });

    // Interaction Sentence list display
    $('.displayInteracQuery').on('click', function () {
      loadModal('modal', '{{Liste des interactions}}', 'interact.query.display&interactDef_id=' + $('.interactAttr[data-l1key=id]').value());
    });

    // Intercation go back list button
    $('#bt_interactThumbnailDisplay').on('click', function () {
      loadPage('index.php?v=d&p=interact');
    });

    // Intercation category panel collasping
    $('#bt_interactCollapse').on('click',function(){
       $('#accordionInteract .panel-collapse').each(function () {
          if (!$(this).hasClass("in")) {
              $(this).css({'height' : '' });
              $(this).addClass("in");
          }
       });
       $('#bt_interactCollapse').hide();
       $('#bt_interactUncollapse').show()
    });

    // Intercation category panel uncollasping
    $('#bt_interactUncollapse').on('click',function(){
       $('#accordionInteract .panel-collapse').each(function () {
          if ($(this).hasClass("in")) {
              $(this).removeClass("in");
          }
       });
       $('#bt_interactUncollapse').hide();
       $('#bt_interactCollapse').show()
    });

    // Intercation display
    $('.interactDisplayCard').on('click',function(){
        displayInteract($(this).attr('data-interact_id'));
        if(document.location.toString().split('#')[1] == '' || document.location.toString().split('#')[1] == undefined){
            $('.nav-tabs a[href="#generaltab"]').click();
        }
    });

    // Packering intercation on panel size change
    $('.accordion-toggle').off('click').on('click', function () {
        setTimeout(function(){
            $('.interactListContainer').packery();
        },100);
    });

    // Intercation duplicate button
    $('#bt_duplicate').on('click', function () {
      bootbox.prompt("Nom ?", function (result) {
        if (result !== null) {
          var interact = $('.interact').getValues('.interactAttr')[0];
          interact.actions = {};
          interact.actions.cmd = $('#div_action .action').getValues('.expressionAttr');
          interact.name = result;
          interact.id = '';
          nextdom.interact.save({
            interact: interact,
            error: function (error) {
              notify('Erreur', error.message, 'error');
            },
            success: function (data) {
              modifyWithoutSave = false;
              $('#bt_interactThumbnailDisplay').hide();
              displayInteract(data.id);
            }
          });
        }
      });
    });

    // Interaction test button
    $('#bt_testInteract').on('click', function () {
      loadModal('modal', '{{Tester les interactions}}', 'interact.test');
    });

    // Interaction save button
    $("#bt_saveInteract").on('click', function () {
        var interact = $('.interact').getValues('.interactAttr')[0];
        interact.actions = {};
        interact.actions.cmd = $('#div_action .action').getValues('.expressionAttr');
        nextdom.interact.save({
            interact: interact,
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success: function (data) {
               $('.interactDisplayCard[data-interact_id=' + data.id + ']').click();
               modifyWithoutSave = false;
               $(".bt_cancelModifs").hide();
               notify('Info', '{{Sauvegarde réussie avec succès}}', 'success');
            }
        });
        $('#bt_interactThumbnailDisplay').show();
    });

    // Interaction regenerate button
    $("#bt_regenerateInteract").on('click', function () {
      bootbox.confirm('{{Etes-vous sûr de vouloir régénérer toutes les interactions (cela peut être très long) ?}}', function (result) {
        if (result) {
          nextdom.interact.regenerateInteract({
            interact: {query: result},
            error: function (error) {
              notify('Erreur', error.message, 'error');
            },
            success: function (data) {
             notify('Info', '{{Toutes les interactions ont été regénérées}}', 'success');
            }
          });
        }
      });
    });

    // Interaction add new button
    $("#bt_addInteract").on('click', function () {
        bootbox.prompt("{{Nom de votre interaction ?}}", function (result) {
            if (result !== null) {
                bootbox.prompt("{{Demande formulée ?}}", function (result2) {
                    if (result2 !== null) {
                        nextdom.interact.save({
                            interact: {query: result2,name: result},
                            error: function (error) {
                                notify('Erreur', error.message, 'error');
                                },
                            success: function (data) {
                                $('#bt_interactThumbnailDisplay').hide();
                                displayInteract(data.id);
                                }
                        });
                    }
                });
            }
        });
    });

    // Interaction delete button
    $("#bt_removeInteract").on('click', function () {
      bootbox.confirm('{{Etes-vous sûr de vouloir supprimer l\'interaction}} <span style="font-weight: bold ;">' + $('.interactDisplayCard.active .name').text() + '</span> ?', function (result) {
        if (result) {
          nextdom.interact.remove({
            id: $('.interactDisplayCard.active').attr('data-interact_id'),
            error: function (error) {
              notify('Erreur', error.message, 'error');
            },
            success: function () {
              modifyWithoutSave = false;
              loadPage('index.php?v=d&p=interact');
              notify('Info', '{{Suppression effectuée avec succès}}', 'success');
           }
         });
        }
      });
    });

    $('#div_pageContainer').delegate('.listEquipementInfoReply', 'click', function () {
      nextdom.cmd.getSelectModal({cmd : {type : 'info'}}, function (result) {
        $('.interactAttr[data-l1key=reply]').atCaret('insert',result.human);
      });
    });

    // Intercation action add button
    $('#bt_addAction').off('click').on('click',function(){
      addAction({}, 'action');
    });

    // Intercation action remove button
    $("body").undelegate('.bt_removeAction', 'click').delegate('.bt_removeAction', 'click', function () {
        var type = $(this).attr('data-type');
        $(this).closest('.' + type).remove();
    });

    // Intercation display action option on focusout
    $('#div_pageContainer').undelegate(".cmdAction.expressionAttr[data-l1key=cmd]", 'focusout').delegate('.cmdAction.expressionAttr[data-l1key=cmd]', 'focusout', function (event) {
        var type = $(this).attr('data-type')
        var expression = $(this).closest('.' + type).getValues('.expressionAttr');
        var el = $(this);
        nextdom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) {
            el.closest('.' + type).find('.actionOptions').html(html);
            initTextAreaAutosize();
        })
    });

    // Display cmd list for intercation action
    $("body").undelegate(".listCmd", 'click').delegate(".listCmd", 'click', function () {
        var type = $(this).attr('data-type');
        var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
        nextdom.cmd.getSelectModal({cmd:{type:'info'}}, function (result) {
            el.value(result.human);
            nextdom.cmd.displayActionOption(el.value(), '', function (html) {
                el.closest('.' + type).find('.actionOptions').html(html);
                initTextAreaAutosize();
            });
        });
    });

    // Display action list for intercation action
    $("body").undelegate(".listAction", 'click').delegate(".listAction", 'click', function () {
        var type = $(this).attr('data-type');
        var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
        nextdom.getSelectActionModal({}, function (result) {
            el.value(result.human);
            nextdom.cmd.displayActionOption(el.value(), '', function (html) {
                el.closest('.' + type).find('.actionOptions').html(html);
                initTextAreaAutosize();
            });
        });
    });

    // Display action list for intercation cmd
    $("body").undelegate(".listCmdAction", 'click').delegate(".listCmdAction", 'click', function () {
        var type = $(this).attr('data-type');
        var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
        nextdom.cmd.getSelectModal({cmd:{type:'action'}}, function (result) {
            el.value(result.human);
            nextdom.cmd.displayActionOption(el.value(), '', function (html) {
                el.closest('.' + type).find('.actionOptions').html(html);
                initTextAreaAutosize();
            });
        });
    });

    // Reset wait time before reply
    $('#bt_resetWaitTime').on('click', function () {
        $(this).siblings(".slider").value(0);
    });
}

/**
 * Display an interaction
 *
 * @param _id interaction id
 */
function displayInteract(_id){
    $('#div_conf').show();
    $('#interactThumbnailDisplay').hide();
    $('.interactDisplayCard').removeClass('active');
    $('.interactDisplayCard[data-interact_id='+_id+']').addClass('active');
    nextdom.interact.get({
        id: _id,
        success: function (data) {
            actionOptions = []
            $('#interactId').value(_id);
            $('#div_action').empty();
            $('.interactAttr').value('');
            $('.interact').setValues(data, '.interactAttr');
            $('.interactAttr[data-l1key=filtres][data-l2key=type]').value(1);
            $('.interactAttr[data-l1key=filtres][data-l2key=subtype]').value(1);
            $('.interactAttr[data-l1key=filtres][data-l2key=unite]').value(1);
            $('.interactAttr[data-l1key=filtres][data-l2key=object]').value(1);
            $('.interactAttr[data-l1key=filtres][data-l2key=plugin]').value(1);
            $('.interactAttr[data-l1key=filtres][data-l2key=category]').value(1);
            if(!isset(data.options) || !isset(data.options.waitBeforeReply)){
                $('.interactAttr[data-l1key=options][data-l2key=waitBeforeReply]').value(0);
            }
            if(isset(data.options) && isset(data.filtres.type) && $.isPlainObject(data.filtres.type)){
                for(var i in data.filtres.type){
                  $('.interactAttr[data-l1key=filtres][data-l2key=type][data-l3key='+i+']').value(data.filtres.type[i]);
                }
            }
            if(isset(data.filtres) && isset(data.filtres.subtype) && $.isPlainObject(data.filtres.subtype)){
                for(var i in data.filtres.subtype){
                    $('.interactAttr[data-l1key=filtres][data-l2key=subtype][data-l3key='+i+']').value(data.filtres.subtype[i]);
                }
            }
            if(isset(data.filtres) && isset(data.filtres.unite) && $.isPlainObject(data.filtres.unite)){
                for(var i in data.filtres.unite){
                    $('.interactAttr[data-l1key=filtres][data-l2key=unite][data-l3key="'+i+'"]').value(data.filtres.unite[i]);
                }
            }
            if(isset(data.filtres) && isset(data.filtres.object) && $.isPlainObject(data.filtres.object)){
                for(var i in data.filtres.object){
                    $('.interactAttr[data-l1key=filtres][data-l2key=object][data-l3key='+i+']').value(data.filtres.object[i]);
                }
            }
            if(isset(data.filtres) && isset(data.filtres.plugin) && $.isPlainObject(data.filtres.plugin)){
                for(var i in data.filtres.plugin){
                    $('.interactAttr[data-l1key=filtres][data-l2key=plugin][data-l3key='+i+']').value(data.filtres.plugin[i]);
                }
            }
            if(isset(data.filtres) && isset(data.filtres.category) && $.isPlainObject(data.filtres.category)){
                for(var i in data.filtres.category){
                    $('.interactAttr[data-l1key=filtres][data-l2key=category][data-l3key='+i+']').value(data.filtres.category[i]);
                }
            }
            if(isset(data.actions.cmd) && $.isArray(data.actions.cmd) && data.actions.cmd.length != null){
                for(var i in data.actions.cmd){
                    addAction(data.actions.cmd[i], 'action');
                }
            }
            initTextAreaAutosize();
            nextdom.cmd.displayActionsOption({
                params : actionOptions,
                async : false,
                error: function (error) {
                    notify('Erreur', error.message, 'error');
                },
                success : function(data){
                    for(var i in data){
                        if(data[i].html != ''){
                            $('#'+data[i].id).append(data[i].html.html);
                        }
                    }
                    initTextAreaAutosize();
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
                    modifyWithoutSave = false;
                    $(".bt_cancelModifs").hide();
                }
            });
        }
    });
}

/**
 * Add an interaction action
 *
 * @param _action action object
 * @param _type action type
 */
function addAction(_action, _type) {
    if (!isset(_action)) {
      _action = {};
    }
    if (!isset(_action.options)) {
      _action.options = {};
    }
    var div = '<div class="' + _type + '">';
    div += '<div class="form-group ">';
    div += '<div class="col-sm-5">';
    div += '<div class="input-group input-group-sm">';
    div += '<span class="input-group-btn">';
    div += '<a class="btn btn-danger btn-sm bt_removeAction" data-type="' + _type + '"><i class="fas fa-minus-circle"></i></a>';
    div += '</span>';
    div += '<input class="expressionAttr form-control cmdAction" data-l1key="cmd" data-type="' + _type + '" />';
    div += '<span class="input-group-btn">';
    div += '<a class="btn btn-default btn-sm listAction"" data-type="' + _type + '" title="{{Sélectionner un mot-clé}}"><i class="fas fa-tasks no-spacing"></i></a>';
    div += '<a class="btn btn-default btn-sm listCmdAction" data-type="' + _type + '"><i class="fas fa-list-alt"></i></a>';
    div += '</span>';
    div += '</div>';
    div += '</div>';
    var actionOption_id = uniqId();
    div += '<div class="col-sm-7 actionOptions" id="'+actionOption_id+'"></div>';
    $('#div_' + _type).append(div);
    $('#div_' + _type + ' .' + _type + ':last').setValues(_action, '.expressionAttr');
    actionOptions.push({
      expression : init(_action.cmd, ''),
      options : _action.options,
      id : actionOption_id
    });
}

/**
 * Load intercat with the URL data
 */
function loadFromUrl() {
    var interactIdFromUrl = getUrlVars('id');
    if (is_numeric(interactIdFromUrl)) {
        if ($('.interactDisplayCard[data-interact_id=' + interactIdFromUrl + ']').length !== 0) {
            var url = document.location.toString();
            displayInteract(interactIdFromUrl);
        }
    }
}
