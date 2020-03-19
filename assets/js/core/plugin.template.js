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
var changeLeftMenuObjectOrEqLogicName = false;

if ((!isset(userProfils.doNotAutoHideMenu) || userProfils.doNotAutoHideMenu != 1) && !jQuery.support.touch && $('.eqLogicThumbnailDisplay').html() !== undefined && $('#ul_eqLogic').html() !== undefined) {
  $('#div_mainContainer').append('<div style="position : fixed;height:100%;width:15px;top:50px;right:0px;z-index:998;background-color:#f6f6f6;" class="bt_pluginTemplateShowSidebar div_smallSideBar"><i class="fa fa-arrow-circle-o-right" style="color : #b6b6b6;"></i><div>');
  $('#ul_eqLogic').closest('.bs-sidebar').parent().hide();
  $('#ul_eqLogic').closest('.bs-sidebar').parent().css('z-index', '999');
  $('#ul_eqLogic').closest('.bs-sidebar').parent().removeClass().addClass('col-xs-2');
  $('.eqLogicThumbnailDisplay').removeClass().addClass('eqLogicThumbnailDisplay col-xs-12');
  $('.eqLogic').removeClass('col-xs-10 col-lg-10 col-md-9 col-sm-8 col-lg-9 col-md-8 col-sm-7').addClass('eqLogic col-xs-12');

  $('#ul_eqLogic').closest('.bs-sidebar').parent().on('mouseleave', function () {
    var timer = setTimeout(function () {
      $('#ul_eqLogic').closest('.bs-sidebar').parent().hide();
      $('.bt_pluginTemplateShowSidebar').find('i').show();
      $('.eqLogicThumbnailDisplay').removeClass().addClass('eqLogicThumbnailDisplay col-xs-12');
      $('.eqLogic').removeClass('col-xs-10 col-lg-10 col-md-9 col-sm-8 col-lg-9 col-md-8 col-sm-7').addClass('col-xs-12');
      $('.eqLogicThumbnailContainer').packery();
    }, 300);
    $(this).data('timerMouseleave', timer)
  }).on("mouseenter", function () {
    clearTimeout($(this).data('timerMouseleave'));
  });

  $('.bt_pluginTemplateShowSidebar').on('mouseenter', function () {
    var timer = setTimeout(function () {
      $('.eqLogicThumbnailDisplay').removeClass().addClass('eqLogicThumbnailDisplay col-xs-10');
      $('.bt_pluginTemplateShowSidebar').find('i').hide();
      $('.eqLogic').removeClass('col-xs-12').addClass('eqLogic col-xs-10');
      var sidebar = $('#ul_eqLogic').closest('.bs-sidebar').parent();
      sidebar[0].style.cssFloat = 'right';
      sidebar.show();
      $('.eqLogicThumbnailContainer').packery();
    }, 100);
    $(this).data('timerMouseleave', timer)
  }).off('mouseleave').on("mouseleave", function () {
    clearTimeout($(this).data('timerMouseleave'));
  });
}

var url = document.location.toString();
if (url.match('#')) {
  $('.nav-tabs a[href="#' + url.split('#')[1] + '"]').tab('show');
}
$('.nav-tabs a').on('shown.bs.tab', function (e) {
  window.location.hash = e.target.hash;
});

$('.eqLogicAction[data-action=gotoPluginConf]').on('click', function () {
  $('#md_modal').dialog({title: "{{Configuration du plugin}}"});
  $("#md_modal").load('index.php?v=d&p=plugin&ajax=1&id=' + eqType).dialog('open');
});

$('.eqLogicAction[data-action=returnToThumbnailDisplay]').on('click', function () {
  $('.eqLogic').hide();
  $('.eqLogicThumbnailDisplay').show();
  $('.li_eqLogic').removeClass('active');
  $('.eqLogicThumbnailContainer').packery();
});

$(".li_eqLogic,.eqLogicDisplayCard").on('click', function () {
  nextdom.eqLogic.cache.getCmd = Array();
  if ($('.eqLogicThumbnailDisplay').html() !== undefined) {
    $('.eqLogicThumbnailDisplay').hide();
  }
  $('.eqLogic').hide();
  if ('function' == typeof (prePrintEqLogic)) {
    prePrintEqLogic($(this).attr('data-eqLogic_id'));
  }
  if (isset($(this).attr('data-eqLogic_type')) && isset($('.' + $(this).attr('data-eqLogic_type')))) {
    $('.' + $(this).attr('data-eqLogic_type')).show();
  } else {
    $('.eqLogic').show();
  }
  if ($('.li_eqLogic').length !== 0) {
    $('.li_eqLogic').removeClass('active');
  }
  $(this).addClass('active');
  if ($('.li_eqLogic[data-eqLogic_id=' + $(this).attr('data-eqLogic_id') + ']').html() !== undefined) {
    $('.li_eqLogic[data-eqLogic_id=' + $(this).attr('data-eqLogic_id') + ']').addClass('active');
  }
  if (!url.match('#')) {
    $('.nav-tabs a[href="#eqlogictab"]').click();
  }
  nextdom.eqLogic.print({
    type: isset($(this).attr('data-eqLogic_type')) ? $(this).attr('data-eqLogic_type') : eqType,
    id: $(this).attr('data-eqLogic_id'),
    status: 1,
    error: function (error) {
      notify('Core', error.message, "error");
    },
    success: function (data) {
      $('body .eqLogicAttr').value('');
      if (isset(data) && isset(data.timeout) && data.timeout == 0) {
        data.timeout = '';
      }
      $('body').setValues(data, '.eqLogicAttr');
      if ('function' == typeof (printEqLogic)) {
        printEqLogic(data);
      }
      if ('function' == typeof (addCmdToTable)) {
        $('.cmd').remove();
        for (var i in data.cmd) {
          addCmdToTable(data.cmd[i]);
        }
      }
      $('body').delegate('.cmd .cmdAttr[data-l1key=type]', 'change', function () {
        nextdom.cmd.changeType($(this).closest('.cmd'));
      });

      $('body').delegate('.cmd .cmdAttr[data-l1key=subType]', 'change', function () {
        nextdom.cmd.changeSubType($(this).closest('.cmd'));
      });
      changeLeftMenuObjectOrEqLogicName = false;
      modifyWithoutSave = false;
    }
  });
  return false;
});

if (getUrlVars('saveSuccessFull') == 1) {
  notify('Info', '{{Sauvegarde effectuée avec succès}}', 'success');
}

if (getUrlVars('removeSuccessFull') == 1) {
  notify('Info', '{{Suppression effectuée avec succès}}', 'success');
}

/**************************EqLogic*********************************************/
$('.eqLogicAction[data-action=copy]').on('click', function () {
  if ($('.eqLogicAttr[data-l1key=id]').value() !== undefined && $('.eqLogicAttr[data-l1key=id]').value() != '') {
    bootbox.prompt("{{Nom de la copie de l'équipement ?}}", function (result) {
      if (result !== null) {
        nextdom.eqLogic.copy({
          id: $('.eqLogicAttr[data-l1key=id]').value(),
          name: result,
          error: function (error) {
            notify('Erreur', error.message, 'error');
          },
          success: function (data) {
            modifyWithoutSave = false;
            var vars = getUrlVars();
            var url = 'index.php?';
            for (var i in vars) {
              if (i != 'id' && i != 'saveSuccessFull' && i != 'removeSuccessFull') {
                url += i + '=' + vars[i].replace('#', '') + '&';
              }
            }
            url += 'id=' + data.id + '&saveSuccessFull=1';
            loadPage(url);
            bootbox.hideAll();
          }
        });
        return false;
      }
    });
  }
});

$('.eqLogicAction[data-action=export]').on('click', function () {
  window.open('src/Api/export.php?type=eqLogic&id=' + $('.eqLogicAttr[data-l1key=id]').value(), "_blank", null);
});

$('.eqLogicAction[data-action=save]').on('click', function () {
  var eqLogics = [];
  $('.eqLogic').each(function () {
    if ($(this).is(':visible')) {
      var eqLogic = $(this).getValues('.eqLogicAttr');
      eqLogic = eqLogic[0];
      eqLogic.cmd = $(this).find('.cmd').getValues('.cmdAttr');
      if ('function' == typeof (saveEqLogic)) {
        eqLogic = saveEqLogic(eqLogic);
      }
      eqLogics.push(eqLogic);
    }
  });
  nextdom.eqLogic.save({
    type: isset($(this).attr('data-eqLogic_type')) ? $(this).attr('data-eqLogic_type') : eqType,
    id: $(this).attr('data-eqLogic_id'),
    eqLogics: eqLogics,
    error: function (error) {
      notify('Erreur', error.message, 'error');
    },
    success: function (data) {
      modifyWithoutSave = false;
      var vars = getUrlVars();
      var url = 'index.php?';
      for (var i in vars) {
        if (i != 'id' && i != 'saveSuccessFull' && i != 'removeSuccessFull') {
          url += i + '=' + vars[i].replace('#', '') + '&';
        }
      }
      url += 'id=' + data.id + '&saveSuccessFull=1';
      if (document.location.toString().match('#')) {
        url += '#' + document.location.toString().split('#')[1];
      }
      loadPage(url);
      modifyWithoutSave = false;
    }
  });
  return false;
});

$('.eqLogicAttr[data-l1key=name]').on('change', function () {
  changeLeftMenuObjectOrEqLogicName = true;
});

$('.eqLogicAttr[data-l1key=object_id]').on('change', function () {
  changeLeftMenuObjectOrEqLogicName = true;
});

$('.eqLogicAction[data-action=remove]').on('click', function () {
  if ($('.eqLogicAttr[data-l1key=id]').value() !== undefined) {
    bootbox.confirm('{{Etes-vous sûr de vouloir supprimer l\'équipement}} ' + eqType + ' <b>' + $('.eqLogicAttr[data-l1key=name]').value() + '</b> ?', function (result) {
      if (result) {
        nextdom.eqLogic.remove({
          type: isset($(this).attr('data-eqLogic_type')) ? $(this).attr('data-eqLogic_type') : eqType,
          id: $('.eqLogicAttr[data-l1key=id]').value(),
          error: function (error) {
            notify('Erreur', error.message, 'error');
          },
          success: function () {
            var vars = getUrlVars();
            var url = 'index.php?';
            for (var i in vars) {
              if (i != 'id' && i != 'removeSuccessFull' && i != 'saveSuccessFull') {
                url += i + '=' + vars[i].replace('#', '') + '&';
              }
            }
            modifyWithoutSave = false;
            url += 'removeSuccessFull=1';
            loadPage(url);
          }
        });
      }
    });
  } else {
    notify('Erreur', '{{Veuillez d\'abord sélectionner un}} ' + eqType, 'error');
  }
});

$('.eqLogicAction[data-action=add]').on('click', function () {
  bootbox.prompt("{{Nom de l'équipement ?}}", function (result) {
    if (result !== null) {
      nextdom.eqLogic.save({
        type: eqType,
        eqLogics: [{name: result}],
        error: function (error) {
          notify('Erreur', error.message, 'error');
        },
        success: function (_data) {
          var vars = getUrlVars();
          var url = 'index.php?';
          for (var i in vars) {
            if (i != 'id' && i != 'saveSuccessFull' && i != 'removeSuccessFull') {
              url += i + '=' + vars[i].replace('#', '') + '&';
            }
          }
          modifyWithoutSave = false;
          url += 'id=' + _data.id + '&saveSuccessFull=1';
          loadPage(url);
        }
      });
    }
  });
});

$('.eqLogic .eqLogicAction[data-action=configure]').on('click', function () {
  loadModal('modal', '{{Configuration de l\'équipement}}', 'eqLogic.configure&eqLogic_id=' + $('.eqLogicAttr[data-l1key=id]').value());
});

$('#in_searchEqlogic').off('keyup').keyup(function () {
  var search = $(this).value();
  if (search == '') {
    $('.eqLogicDisplayCard').show();
    $('.eqLogicThumbnailContainer').packery();
    return;
  }
  $('.eqLogicDisplayCard').hide();
  $('.eqLogicDisplayCard .name').each(function () {
    var text = $(this).text().toLowerCase();
    if (text.indexOf(search.toLowerCase()) >= 0) {
      $(this).closest('.eqLogicDisplayCard').show();
    }
  });
  $('.eqLogicThumbnailContainer').packery();
});

/**************************CMD*********************************************/
$('.cmdAction[data-action=add]').on('click', function () {
  modifyWithoutSave = true;
  addCmdToTable();
  $('.cmd:last .cmdAttr[data-l1key=type]').trigger('change');
});

$('#div_pageContainer').on('click', '.cmd .cmdAction[data-l1key=chooseIcon]', function () {
  modifyWithoutSave = true;
  var cmd = $(this).closest('.cmd');
  chooseIcon(function (_icon) {
    cmd.find('.cmdAttr[data-l1key=display][data-l2key=icon]').empty().append(_icon);
  });
});

$('#div_pageContainer').on('click', '.cmd .cmdAttr[data-l1key=display][data-l2key=icon]', function () {
  modifyWithoutSave = true;
  $(this).empty();
});

$('#div_pageContainer').on('click', '.cmd .cmdAction[data-action=remove]', function () {
  modifyWithoutSave = true;
  $(this).closest('tr').remove();
});

$('#div_pageContainer').on('click', '.cmd .cmdAction[data-action=copy]', function () {
  modifyWithoutSave = true;
  var cmd = $(this).closest('.cmd').getValues('.cmdAttr')[0];
  cmd.id = '';
  addCmdToTable(cmd);
});

$('#div_pageContainer').on('click', '.cmd .cmdAction[data-action=test]', function (event) {
  $.hideAlert();
  if ($('.eqLogicAttr[data-l1key=isEnable]').is(':checked')) {
    var id = $(this).closest('.cmd').attr('data-cmd_id');
    nextdom.cmd.test({id: id});
  } else {
    notify('Info', '{{Veuillez activer l\'équipement avant de tester une de ses commandes}}', 'warning');
  }

});

$('#div_pageContainer').on('dblclick', '.cmd input,select,span,a', function (event) {
  event.stopPropagation();
});

$('#div_pageContainer').on('dblclick', '.cmd', function () {
  loadModal('modal', '{{Configuration commande}}', 'cmd.configure&cmd_id=' + $(this).closest('.cmd').attr('data-cmd_id'));
});

$('#div_pageContainer').on('click', '.cmd .cmdAction[data-action=configure]', function () {
  loadModal('modal', '{{Configuration commande}}', 'cmd.configure&cmd_id=' + $(this).closest('.cmd').attr('data-cmd_id'));
});

$('.eqLogicThumbnailContainer').packery();

if (is_numeric(getUrlVars('id'))) {
  if ($('#ul_eqLogic .li_eqLogic[data-eqLogic_id=' + getUrlVars('id') + ']').length !== 0) {
    $('#ul_eqLogic .li_eqLogic[data-eqLogic_id=' + getUrlVars('id') + ']').click();
  } else if ($('.eqLogicThumbnailContainer .eqLogicDisplayCard[data-eqLogic_id=' + getUrlVars('id') + ']').length !== 0) {
    $('.eqLogicThumbnailContainer .eqLogicDisplayCard[data-eqLogic_id=' + getUrlVars('id') + ']').click();
  } else if ($('.eqLogicThumbnailDisplay').html() === undefined) {
    $('#ul_eqLogic .li_eqLogic:first').click();
  }
} else {
  if ($('.eqLogicThumbnailDisplay').html() === undefined) {
    $('#ul_eqLogic .li_eqLogic:first').click();
  }
}

$("img.lazy").lazyload({
  event: "sporty"
});

$("img.lazy").each(function () {
  var el = $(this);
  if (el.attr('data-original2') !== undefined) {
    $("<img>", {
      src: el.attr('data-original'),
      error: function () {
        $("<img>", {
          src: el.attr('data-original2'),
          error: function () {
            if (el.attr('data-original3') !== undefined) {
              $("<img>", {
                src: el.attr('data-original3'),
                error: function () {
                  el.lazyload({
                    event: "sporty"
                  });
                  el.trigger("sporty");
                },
                load: function () {
                  el.attr("data-original", el.attr('data-original3'));
                  el.lazyload({
                    event: "sporty"
                  });
                  el.trigger("sporty");
                }
              });
            } else {
              el.lazyload({
                event: "sporty"
              });
              el.trigger("sporty");
            }
          },
          load: function () {
            el.attr("data-original", el.attr('data-original2'));
            el.lazyload({
              event: "sporty"
            });
            el.trigger("sporty");
          }
        });
      },
      load: function () {
        el.lazyload({
          event: "sporty"
        });
        el.trigger("sporty");
      }
    });
  } else {
    el.lazyload({
      event: "sporty"
    });
    el.trigger("sporty");
  }
});

$('body').delegate('.cmdAttr', 'change', function () {
  modifyWithoutSave = true;
});

$('body').delegate('.eqLogicAttr', 'change', function () {
  modifyWithoutSave = true;
});
