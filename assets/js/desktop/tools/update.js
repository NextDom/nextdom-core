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

var tabsList = $('#accordionUpdate > .tab-pane > .row > div');
var updateInfoModal = $('#updateInfoModal');
var selectiveUpdateModal = $('#selectiveUpdateModal');
var updateCollapseButton = $('#updateCollapseButton');
var updateUncollapseButton = $('#updateUncollapseButton');
var updateLogView = $('#updateLog');

/**
 * Init content of all update tabs
 */
function initUpdateTabsContent() {
  // Get list of updates
  nextdom.update.get({
    error: function (error) {
      notify('Erreur', error.message, 'error');
    },
    success: function (updatesList) {
      tabsList.empty();
      for (var updateIndex in updatesList) {
        createUpdateBox(updatesList[updateIndex]);
      }
      tabsList.trigger('update');
    }
  });

  // Update last check badge
  nextdom.config.load({
    configuration: {'update::lastCheck': 0, 'update::lastDateCore': 0},
    error: function (error) {
      notify('Erreur', error.message, 'error');
    },
    success: function (data) {
      var lastUpdateBadge = $('#lastUpdateDate');
      lastUpdateBadge.value(data['update::lastCheck']);
      if (isset(data['update::lastDateCore'])) {
        $('#lastUpdateDate').attr('title', '{{Dernière mise à jour du core : }}' + data['update::lastDateCore']);
      }
    }
  });
}

/**
 * Create update box
 *
 * @param updateData HTML data of the update box
 */
function createUpdateBox(updateData) {
  var boxClass = 'box-success';
  var bgClass = 'bg-green';
  var boxUpdateClass = '';
  var updateIcon = '';
  var updateName = init(updateData.name);

  if (init(updateData.status) == '') {
    updateData.status = 'ok';
  } else if (updateData.status == 'update') {
    boxClass = 'box-warning';
    bgClass = 'bg-yellow';
  }

  boxUpdateClass = 'update-box';
  var htmlData = '<div class="objet col-lg-4 col-md-6 col-sm-6 col-xs-12">';
  htmlData += '<div class="' + boxUpdateClass + ' box ' + boxClass + '" data-id="' + init(updateData.id) + '" data-logicalId="' + init(updateData.logicalId) + '" data-type="' + init(updateData.type) + '">';
  htmlData += '<div class="box-header with-border accordion-toggle cursor" data-toggle="collapse" data-parent="#accordionUpdate" href="#update_' + init(updateData.id) + '">';
  if (init(updateData.type) == 'core') {
    updateIcon = '/public/img/NextDom/NextDom_Square_WhiteBlackBlue.png';
  } else {
    if (isset(updateData.plugin) && init(updateData.plugin.icon) != '') {
      updateIcon = init(updateData.plugin.icon);
    } else {
      updateIcon = '/public/img/NextDom_' + init(updateData.type).charAt(0).toUpperCase() + init(updateData.type).slice(1) + '_Gray.png';
    }
  }
  if (init(updateData.type) == 'widget') {
    updateName = updateName.split(".").pop();
  }
  htmlData += ' <h4 class="box-title" style="text-transform: capitalize;"><img class="box-header-icon spacing-right" src="' + updateIcon + '"/>' + updateName + '</h4>';
  htmlData += '<span data-toggle="tooltip" title="" class="updateAttr badge ' + bgClass + ' pull-right" data-original-title="" data-l1key="status" style="text-transform: uppercase;"></span>';
  htmlData += '</div>';
  if (init(updateData.type) == 'core') {
    htmlData += '<div id="update_' + init(updateData.id) + '" class="panel-collapse collapse in">';
  } else {
    htmlData += '<div id="update_' + init(updateData.id) + '" class="panel-collapse collapse">';
  }
  htmlData += '<div class="box-body" style="min-height: 268px;">';
  htmlData += '<span class="updateAttr" data-l1key="id" style="display:none;"></span><p><b>{{Source : }}</b><span class="updateAttr" data-l1key="source"></span></p>';
  htmlData += '<p><b>{{Type : }}</b><span class="updateAttr" data-l1key="type"></span></p>';
  htmlData += '<p><b>{{Branche : }}</b>';
  if (updateData.configuration && updateData.configuration.version) {
    htmlData += updateData.configuration.version;
  }
  htmlData += '</p>';
  if (updateData.type == 'widget') {
    htmlData += '<p><b>{{Id : }}</b>' + init(updateData.name);
    htmlData += '</p>';
  }
  htmlData += '<p><b>{{Version : }}</b>' + updateData.remoteVersion + '</p>';
  htmlData += '<input type="checkbox" class="updateAttr" data-l1key="configuration" data-l2key="doNotUpdate" id="doNotUpdate_' + init(updateData.id) + '">';
  htmlData += '<label for="doNotUpdate_' + init(updateData.id) + '" class="control-label label-check">{{Ne pas mettre à jour}}</label></br>';
  htmlData += '</div>';
  htmlData += '<div class="box-footer clearfix text-center">';

  if (updateData.type != 'core') {
    htmlData += '<a class="btn btn-danger btn-sm pull-right remove" ><i class="fas fa-trash"></i>{{Supprimer}}</a>';
    htmlData += '<a class="btn btn-action btn-sm update pull-right" title="{{Re-installer}}"><i class="fas fa-refresh"></i>{{Reinstaller}}</a> ';
  }
  if (updateData.status == 'update') {
    htmlData += '<a class="btn btn-warning btn-sm update pull-right" title="{{Mettre à jour}}"><i class="fas fa-refresh"></i>{{Mettre à jour}}</a> ';
  }
  if (isset(updateData.plugin) && isset(updateData.plugin.changelog) && updateData.plugin.changelog != '') {
    htmlData += '<a class="btn btn-default btn-sm pull-left cursor hidden-sm" target="_blank" href="' + updateData.plugin.changelog + '"><i class="fas fa-book"></i>{{Changelog}}</a>';
  } else {
    htmlData += '<a class="btn btn-default btn-sm pull-right" href="https://nextdom.github.io/core/fr_FR/changelog" target="_blank"><i class="fas fa-book"></i>{{Changelog}}</a>';
  }
  htmlData += '<a class="btn btn-info btn-sm pull-left checkUpdate" ><i class="fas fa-check"></i>{{Vérifier}}</a>';
  htmlData += '</div>';
  htmlData += '</div>';
  htmlData += '</div>';

  // Select target tab of update box
  var targetTabs = 'Other';
  switch (updateData.type) {
    case 'core':
      targetTabs = 'Core';
      break;
    case 'plugin':
      targetTabs = 'Plugin';
      break;
    case 'widget':
      targetTabs = 'Widget';
      break;
    case 'script':
      targetTabs = 'Script';
      break;
  }
  // Add update box in tab
  $('#list' + targetTabs).append(htmlData);
  $('#list' + targetTabs + ' .box:last').setValues(updateData, '.updateAttr');
}

/**
 * Save update changes
 */
function saveUpdateChanges() {
  nextdom.update.saves({
    updates: $('.tab-pane .box').getValues('.updateAttr'),
    error: function (error) {
      notify('Erreur', error.message, 'error');
    },
    success: function (data) {
      notify('Info', '{{Sauvegarde effectuée}}', 'success');
      initUpdateTabsContent();
    }
  });
}

/**
 * Check for update on single item
 *
 * @param updateId Id of the item to check
 */
function checkSingleUpdate(updateId) {
  $.hideAlert();
  nextdom.update.check({
    id: updateId,
    error: function (error) {
      notify('Erreur', error.message, 'error');
    },
    success: function () {
      initUpdateTabsContent();
    }
  });
}

/**
 * Remove item from NextDom
 *
 * @param updateId Id of the item to remove
 */
function removeUpdate(updateId) {
  bootbox.confirm('{{Êtes-vous sûr de vouloir supprimer cet objet ?}}', function (result) {
    if (result) {
      $.hideAlert();
      nextdom.update.remove({
        id: updateId,
        error: function (error) {
          notify('Erreur', error.message, 'error');
        },
        success: function () {
          initUpdateTabsContent();
        }
      });
    }
  });
}

/**
 * Start update install
 *
 * @param updateId Id of the item to install
 */
function launchUpdate(updateId) {
  bootbox.confirm('{{Etes vous sur de vouloir mettre à jour cet objet ?}}', function (result) {
    if (result) {
      $.hideAlert();
      updateInfoModal.dialog({title: '{{Avancement de la mise à jour}}'});
      updateInfoModal.dialog('open');
      nextdom.update.do({
        id: updateId,
        error: function (error) {
          notify('Erreur', error.message, 'error');
        },
        success: function () {
          showLogDialog();
        }
      });
    }
  });
}

/**
 * Get NextDom log and show it in the modal
 *
 * @param _autoUpdate
 * @param _log
 */
function getNextDomLog(_autoUpdate, _log) {
  $.ajax({
    type: 'POST',
    url: 'core/ajax/log.ajax.php',
    data: {
      action: 'get',
      log: _log,
    },
    dataType: 'json',
    global: false,
    error: function (request, status, error) {
      setTimeout(function () {
        getNextDomLog(_autoUpdate, _log)
      }, 1000);
    },
    success: function (data) {
      if (data.state != 'ok') {
        setTimeout(function () {
          getNextDomLog(_autoUpdate, _log)
        }, 1000);
        return;
      }
      var log = '';
      if ($.isArray(data.result)) {
        for (var i in data.result.reverse()) {
          log += data.result[i] + "\n";
          if (data.result[i].indexOf('[END ' + _log.toUpperCase() + ' SUCCESS]') != -1) {
            initUpdateTabsContent();
            notify('Info', '{{L\'opération est réussie. Merci de faire F5 pour avoir les dernières nouveautés}}', 'success');
            _autoUpdate = 0;
          }
          if (data.result[i].indexOf('[END ' + _log.toUpperCase() + ' ERROR]') != -1) {
            initUpdateTabsContent();
            notify('Erreur', '{{L\'opération a échoué}}', 'error');
            _autoUpdate = 0;
          }
        }
      }
      updateLogView.text(log);
      updateLogView.parent().scrollTop(updateLogView.parent().height() + 200000);
      if (init(_autoUpdate, 0) == 1) {
        setTimeout(function () {
          getNextDomLog(_autoUpdate, _log)
        }, 1000);
      } else {
        $('#bt_' + _log + 'NextDom .fa-refresh').hide();
        $('.bt_' + _log + 'NextDom .fa-refresh').hide();
      }
    }
  });
}

function showLogDialog() {
  updateInfoModal.dialog({title: '{{Avancement de la mise à jour}}'});
  updateInfoModal.dialog('open');
  getNextDomLog(1, 'update');
}

/**
 * Init all events
 */
function initEvents() {
  updateLogView.height($(window).height() - $('header').height() - $('footer').height() - 150);
  updateLogView.parent().height($(window).outerHeight() - $('header').outerHeight() - 160);
  $('#selectiveUpdateButton').off('click').on('click', function () {
    selectiveUpdateModal.modal('show');
  });
  $('.updateOption[data-l1key=force]').off('click').on('click', function () {
    if ($(this).value() == 1) {
      $('.updateOption[data-l1key="backup::before"]').value(0);
      $('.updateOption[data-l1key="backup::before"]').attr('disabled', 'disabled');

    } else {
      $('.updateOption[data-l1key="backup::before"]').attr('disabled', false);
    }
  });
  $('#startUpdateButton').off('click').on('click', function () {
    selectiveUpdateModal.modal('hide');
    updateInfoModal.dialog({title: '{{Avancement de la mise à jour}}'});
    updateInfoModal.dialog('open');
    var options = selectiveUpdateModal.getValues('.updateOption')[0];
    $.hideAlert();
    nextdom.update.doAll({
      options: options,
      error: function (error) {
        notify('Erreur', error.message, 'error');
      },
      success: function () {
        showLogDialog();
      }
    });
  });

  $('#logDialogButton').on('click', function () {
    showLogDialog();
  });

  $('#checkAllUpdatesButton').off('click').on('click', function () {
    $.hideAlert();
    nextdom.update.checkAll({
      error: function (error) {
        notify('Erreur', error.message, 'error');
      },
      success: function () {
        initUpdateTabsContent();
      }
    });
  });

  updateCollapseButton.on('click', function () {
    $('#accordionUpdate .panel-collapse').each(function () {
      if (!$(this).hasClass('in')) {
        $(this).css({'height': ''});
        $(this).addClass('in');
      }
    });
    updateCollapseButton.hide();
    updateUncollapseButton.show()
  });

  updateUncollapseButton.on('click', function () {
    $('#accordionUpdate .panel-collapse').each(function () {
      if ($(this).hasClass('in')) {
        $(this).removeClass('in');
      }
    });
    updateUncollapseButton.hide();
    updateCollapseButton.show()
  });

  // Init update button on update box
  tabsList.delegate('.update', 'click', function () {
    var updateId = $(this).closest('.box').attr('data-id');
    launchUpdate(updateId);
  });

  // Init remove button on update box
  tabsList.delegate('.remove', 'click', function () {
    var updateId = $(this).closest('.box').attr('data-id');
    removeUpdate(updateId);
  });

  // Init check update button on update box
  tabsList.delegate('.checkUpdate', 'click', function () {
    var updateId = $(this).closest('.box').attr('data-id');
    checkSingleUpdate(updateId);
  });

  $('#saveUpdateChanges').click(saveUpdateChanges);
}

/**
 * Init dialogs
 */
function initDialogs() {
  updateInfoModal.dialog({
    closeText: '',
    autoOpen: false,
    modal: true,
    width: ((jQuery(window).width() - 50) < 1500) ? (jQuery(window).width() - 50) : 1500,
    open: function () {
      $('body').css({overflow: 'hidden'});
    },
    beforeClose: function (event, ui) {
      $('body').css({overflow: 'inherit'});
    }
  });
}

initUpdateTabsContent();
initDialogs();
initEvents();
