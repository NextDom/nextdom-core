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

$("#div_action").sortable({axis: "y", cursor: "move", items: ".action", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

$('.displayInteracQuery').on('click', function () {
  $('#md_modal').dialog({title: "{{Liste des intéractions}}"});
  $('#md_modal').load('index.php?v=d&modal=interact.query.display&interactDef_id=' + $('.interactAttr[data-l1key=id]').value()).dialog('open');
});

setTimeout(function(){
  $('.interactListContainer').packery();
},100);


$("#div_listInteract").trigger('resize');

$('.interactListContainer').packery();

$('#bt_interactThumbnailDisplay').on('click', function () {
  loadPage('index.php?v=d&p=interact');
});

$('.interactDisplayCard').on('click', function () {
  $('#div_tree').jstree('deselect_all');
  $('#div_tree').jstree('select_node', 'interact' + $(this).attr('data-interact_id'));
});

$("#div_tree").jstree({
  "plugins": ["search"]
});

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

$('#bt_interactUncollapse').on('click',function(){
   $('#accordionInteract .panel-collapse').each(function () {
      if ($(this).hasClass("in")) {
          $(this).removeClass("in");
      }
   });
   $('#bt_interactUncollapse').hide();
   $('#bt_interactCollapse').show()
});

$('.interactDisplayCard').on('click',function(){
  displayInteract($(this).attr('data-interact_id'));
  if(document.location.toString().split('#')[1] == '' || document.location.toString().split('#')[1] == undefined){
    $('.nav-tabs a[href="#generaltab"]').click();
  }
});

$('.accordion-toggle').off('click').on('click', function () {
  setTimeout(function(){
    $('.interactListContainer').packery();
  },100);
});

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
          notify("Erreur", error.message, 'error');
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

if (is_numeric(getUrlVars('id'))) {
  if ($('.interactDisplayCard[data-interact_id=' + getUrlVars('id') + ']').length != 0) {
    $('.interactDisplayCard[data-interact_id=' + getUrlVars('id') + ']').click();
  }
}

$('#bt_testInteract,#bt_testInteract2').on('click', function () {
  $('#md_modal').dialog({title: "{{Tester les intéractions}}"});
  $('#md_modal').load('index.php?v=d&modal=interact.test').dialog('open');
});

$('#div_pageContainer').delegate('.listEquipementInfoReply', 'click', function () {
  nextdom.cmd.getSelectModal({cmd : {type : 'info'}}, function (result) {
    $('.interactAttr[data-l1key=reply]').atCaret('insert',result.human);
  });
});

jwerty.key('ctrl+s/⌘+s', function (e) {
  e.preventDefault();
  $("#bt_saveInteract").click();
});

$("#bt_saveInteract").on('click', function () {
  var interact = $('.interact').getValues('.interactAttr')[0];
  interact.actions = {};
  interact.actions.cmd = $('#div_action .action').getValues('.expressionAttr');
  nextdom.interact.save({
    interact: interact,
    error: function (error) {
      notify("Erreur", error.message, 'error');
    },
    success: function (data) {
     $('.interactDisplayCard[data-interact_id=' + data.id + ']').click();
     notify("Info", '{{Sauvegarde réussie avec succès}}', 'success');
   }
 });
 $('#bt_interactThumbnailDisplay').show();
});

$("#bt_regenerateInteract,#bt_regenerateInteract2").on('click', function () {
  bootbox.confirm('{{Etes-vous sûr de vouloir régénérer toutes les intérations (cela peut être très long) ?}}', function (result) {
    if (result) {
      nextdom.interact.regenerateInteract({
        interact: {query: result},
        error: function (error) {
          notify("Erreur", error.message, 'error');
        },
        success: function (data) {
         notify("Info", '{{Toutes les interations ont été regénérées}}', 'success');
        }
      });
    }
  });
});

$("#bt_addInteract,#bt_addInteract2").on('click', function () {
    bootbox.prompt("{{Nom de votre intéraction ?}}", function (result) {
        if (result !== null) {
            bootbox.prompt("{{Demande formulée ?}}", function (result2) {
                if (result2 !== null) {
                    nextdom.interact.save({
                        interact: {query: result2,name: result},
                        error: function (error) {
                            notify("Erreur", error.message, 'error');
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

$("#bt_removeInteract").on('click', function () {
  $.hideAlert();
  bootbox.confirm('{{Etes-vous sûr de vouloir supprimer l\'intéraction}} <span style="font-weight: bold ;">' + $('.interactDisplayCard.active .name').text() + '</span> ?', function (result) {
    if (result) {
      nextdom.interact.remove({
        id: $('.interactDisplayCard.active').attr('data-interact_id'),
        error: function (error) {
          notify("Erreur", error.message, 'error');
        },
        success: function () {
          modifyWithoutSave = false;
          loadPage('index.php?v=d&p=interact');
          notify("Info", '{{Suppression effectuée avec succès}}', 'success');
       }
     });
    }
  });
});

$('#bt_addAction').off('click').on('click',function(){
  addAction({}, 'action','{{Action}}');
});

$('#div_pageContainer').undelegate(".cmdAction.expressionAttr[data-l1key=cmd]", 'focusout').delegate('.cmdAction.expressionAttr[data-l1key=cmd]', 'focusout', function (event) {
  var type = $(this).attr('data-type')
  var expression = $(this).closest('.' + type).getValues('.expressionAttr');
  var el = $(this);
  nextdom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) {
    el.closest('.' + type).find('.actionOptions').html(html);
    taAutosize();
  })
});

$("body").undelegate(".listCmd", 'click').delegate(".listCmd", 'click', function () {
  var type = $(this).attr('data-type');
  var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
  nextdom.cmd.getSelectModal({cmd:{type:'info'}}, function (result) {
    el.value(result.human);
    nextdom.cmd.displayActionOption(el.value(), '', function (html) {
      el.closest('.' + type).find('.actionOptions').html(html);
      taAutosize();
    });
  });
});

$("body").undelegate(".listAction", 'click').delegate(".listAction", 'click', function () {
  var type = $(this).attr('data-type');
  var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
  nextdom.getSelectActionModal({}, function (result) {
    el.value(result.human);
    nextdom.cmd.displayActionOption(el.value(), '', function (html) {
      el.closest('.' + type).find('.actionOptions').html(html);
      taAutosize();
    });
  });
});

$("body").undelegate(".listCmdAction", 'click').delegate(".listCmdAction", 'click', function () {
 var type = $(this).attr('data-type');
 var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
 nextdom.cmd.getSelectModal({cmd:{type:'action'}}, function (result) {
  el.value(result.human);
  nextdom.cmd.displayActionOption(el.value(), '', function (html) {
    el.closest('.' + type).find('.actionOptions').html(html);
    taAutosize();
  });
});
});

$("body").undelegate('.bt_removeAction', 'click').delegate('.bt_removeAction', 'click', function () {
  var type = $(this).attr('data-type');
  $(this).closest('.' + type).remove();
});

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
            if(isset(data.filtres) && isset(data.filtres.type) && $.isPlainObject(data.filtres.type)){
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
                    addAction(data.actions.cmd[i], 'action','{{Action}}');
                }
            }
            taAutosize();
            nextdom.cmd.displayActionsOption({
                params : actionOptions,
                async : false,
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success : function(data){
                    for(var i in data){
                        if(data[i].html != ''){
                            $('#'+data[i].id).append(data[i].html.html);
                        }
                    }
                    taAutosize();
                }
            });
        }
    });
}

function addAction(_action, _type, _name) {
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
