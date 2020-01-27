/**
 * Create or Destroy the right context menu
 *
 * @param _mode 0=destroy, 1=initialize
 */
function editWidgetCmdMode(_mode) {
  if (!isset(_mode)) {
    if ($('#bt_editDashboardWidgetOrder').attr('data-mode') != undefined && $('#bt_editDashboardWidgetOrder').attr('data-mode') == 1) {
      editWidgetMode(0);
      editWidgetMode(1);
    }
    return;
  }
  if (_mode == 0) {
    $(".eqLogic-widget.eqLogic_layout_table table.tableCmd").removeClass('table-bordered');
    $.contextMenu('destroy');
    if ($('.eqLogic-widget.allowReorderCmd.eqLogic_layout_table table.tableCmd.ui-sortable').length > 0) {
      try {
        $('.eqLogic-widget.allowReorderCmd.eqLogic_layout_table table.tableCmd').sortable('destroy');
      } catch (e) {

      }
    }
    if ($('.eqLogic-widget.allowReorderCmd.eqLogic_layout_default.ui-sortable').length > 0) {
      try {
        $('.eqLogic-widget.allowReorderCmd.eqLogic_layout_default').sortable('destroy');
      } catch (e) {

      }
    }
    if ($('.eqLogic-widget.ui-draggable').length > 0) {
      $('.eqLogic-widget.allowReorderCmd').off('mouseover', '.cmd');
      $('.eqLogic-widget.allowReorderCmd').off('mouseleave', '.cmd');
    }
  } else {
    $(".eqLogic-widget.allowReorderCmd.eqLogic_layout_default").sortable({items: ".cmd"});
    $(".eqLogic-widget.eqLogic_layout_table table.tableCmd").addClass('table-bordered');
    $('.eqLogic-widget.eqLogic_layout_table table.tableCmd td').sortable({
      connectWith: '.eqLogic-widget.eqLogic_layout_table table.tableCmd td', items: ".cmd"
    });
    $('.eqLogic-widget.allowReorderCmd').on('mouseover', '.cmd', function () {
      $('.eqLogic-widget').draggable('disable');
    });
    $('.eqLogic-widget.allowReorderCmd').on('mouseleave', '.cmd', function () {
      $('.eqLogic-widget').draggable('enable');
    });
    $.contextMenu({
      selector: '.eqLogic-widget',
      zIndex: 9999,
      events: {
        show: function (opt) {
          $.contextMenu.setInputValues(opt, this.data());
        },
        hide: function (opt) {
          $.contextMenu.getInputValues(opt, this.data());
        }
      },
      items: {
        configuration: {
          name: "{{Configuration avancée}}",
          icon: 'fa-cog',
          callback: function (key, opt) {
            saveWidgetDisplay()
            $('#md_modal').dialog({title: "{{Configuration du widget}}"});
            $('#md_modal').load('index.php?v=d&modal=eqLogic.configure&eqLogic_id=' + $(this).attr('data-eqLogic_id')).dialog('open');
          }
        },
        sep1: "---------",
        layoutDefaut: {
          name: "{{Defaut}}",
          icon: 'fa-square-o',
          disabled: function (key, opt) {
            return !$(this).hasClass('allowLayout') || !$(this).hasClass('eqLogic_layout_table');
          },
          callback: function (key, opt) {
            saveWidgetDisplay();
            nextdom.eqLogic.simpleSave({
              eqLogic: {
                id: $(this).attr('data-eqLogic_id'),
                display: {'layout::dashboard': 'default'},
              },
              error: function (error) {
                notify('Erreur', error.message, 'error');
              }
            });
          }
        },
        layoutTable: {
          name: "{{Table}}",
          icon: 'fa-table',
          disabled: function (key, opt) {
            return !$(this).hasClass('allowLayout') || $(this).hasClass('eqLogic_layout_table');
          },
          callback: function (key, opt) {
            saveWidgetDisplay();
            nextdom.eqLogic.simpleSave({
              eqLogic: {
                id: $(this).attr('data-eqLogic_id'),
                display: {'layout::dashboard': 'table'},
              },
              error: function (error) {
                notify('Erreur', error.message, 'error');
              }
            });
          }
        },
        sep2: "---------",
        addTableColumn: {
          name: "{{Ajouter colonne}}",
          icon: 'fa-plus',
          disabled: function (key, opt) {
            return !$(this).hasClass('eqLogic_layout_table');
          },
          callback: function (key, opt) {
            saveWidgetDisplay();
            var column = 0;
            if ($(this).find('table.tableCmd').attr('data-column') !== undefined) {
              column = parseInt($(this).find('table.tableCmd').attr('data-column'));
            }
            nextdom.eqLogic.simpleSave({
              eqLogic: {
                id: $(this).attr('data-eqLogic_id'),
                display: {'layout::dashboard::table::nbColumn': column + 1},
              },
              error: function (error) {
                notify('Erreur', error.message, 'error');
              }
            });
          }
        },
        addTableLine: {
          name: "{{Ajouter ligne}}",
          icon: 'fa-plus',
          disabled: function (key, opt) {
            return !$(this).hasClass('eqLogic_layout_table');
          },
          callback: function (key, opt) {
            saveWidgetDisplay();
            var line = 0;
            if ($(this).find('table.tableCmd').attr('data-line') !== undefined) {
              line = parseInt($(this).find('table.tableCmd').attr('data-line'));
            }
            nextdom.eqLogic.simpleSave({
              eqLogic: {
                id: $(this).attr('data-eqLogic_id'),
                display: {'layout::dashboard::table::nbLine': line + 1},
              },
              error: function (error) {
                notify('Erreur', error.message, 'error');
              }
            });
          }
        },
        removeTableColumn: {
          name: "{{Supprimer colonne}}",
          icon: 'fa-minus',
          disabled: function (key, opt) {
            return !$(this).hasClass('eqLogic_layout_table');
          },
          callback: function (key, opt) {
            saveWidgetDisplay();
            var column = 0;
            if ($(this).find('table.tableCmd').attr('data-column') !== undefined) {
              column = parseInt($(this).find('table.tableCmd').attr('data-column')) - 1;
              column = (column < 0) ? 0 : column;
            }
            nextdom.eqLogic.simpleSave({
              eqLogic: {
                id: $(this).attr('data-eqLogic_id'),
                display: {'layout::dashboard::table::nbColumn': column},
              },
              error: function (error) {
                notify('Erreur', error.message, 'error');
              }
            });
          }
        },
        removeTableLine: {
          name: "{{Supprimer ligne}}",
          icon: 'fa-minus',
          disabled: function (key, opt) {
            return !$(this).hasClass('eqLogic_layout_table');
          },
          callback: function (key, opt) {
            saveWidgetDisplay();
            var line = 0;
            if ($(this).find('table.tableCmd').attr('data-line') !== undefined) {
              line = parseInt($(this).find('table.tableCmd').attr('data-line')) - 1;
              line = (line < 0) ? 0 : line;
            }
            nextdom.eqLogic.simpleSave({
              eqLogic: {
                id: $(this).attr('data-eqLogic_id'),
                display: {'layout::dashboard::table::nbLine': line},
              },
              error: function (error) {
                notify('Erreur', error.message, 'error');
              }
            });
          }
        },
      }
    });
  }
}

/**
 * Save position and sizes of widgets
 *
 * @param _params array of param dedicated for know page in edition
 */
function saveWidgetDisplay(_params) {
  if (init(_params) == '') {
    _params = {};
  }
  var cmds = [];
  var eqLogics = [];
  var scenarios = [];
  $('.eqLogic-widget:not(.eqLogic_layout_table)').each(function () {
    var eqLogic = $(this);
    order = 1;
    eqLogic.find('.cmd').each(function () {
      cmd = {};
      cmd.id = $(this).attr('data-cmd_id');
      cmd.order = order;
      cmds.push(cmd);
      order++;
    });
  });
  $('.eqLogic-widget.eqLogic_layout_table').each(function () {
    var eqLogic = $(this);
    order = 1;
    eqLogic.find('.cmd').each(function () {
      cmd = {};
      cmd.id = $(this).attr('data-cmd_id');
      cmd.line = $(this).closest('td').attr('data-line');
      cmd.column = $(this).closest('td').attr('data-column');
      cmd.order = order;
      cmds.push(cmd);
      order++;
    });
  });
  if (init(_params['dashboard']) == 1) {
    $('.div_displayEquipement').each(function () {
      order = 1;
      $(this).find('.eqLogic-widget').each(function () {
        var eqLogic = {id: $(this).attr('data-eqlogic_id')}
        eqLogic.display = {};
        eqLogic.display.width = Math.floor($(this).width() / 2) * 2 + 'px';
        eqLogic.display.height = Math.floor($(this).height() / 2) * 2 + 'px';
        if ($(this).attr('data-order') != undefined) {
          eqLogic.order = $(this).attr('data-order');
        } else {
          eqLogic.order = order;
        }
        eqLogics.push(eqLogic);
        order++;
      });
    });
    nextdom.eqLogic.setOrder({
      eqLogics: eqLogics,
      error: function (error) {
        notify('Erreur', error.message, 'error');
      },
      success: function (data) {
        nextdom.cmd.setOrder({
          cmds: cmds,
          error: function (error) {
            notify('Erreur', error.message, 'error');
          }
        });
      }
    });
  }
  if (init(_params['view']) == 1) {
    $('.eqLogicZone').each(function () {
      order = 1;
      $(this).find('.eqLogic-widget').each(function () {
        var eqLogic = {id: $(this).attr('data-eqlogic_id')}
        eqLogic.display = {};
        eqLogic.viewZone_id = $(this).closest('.eqLogicZone').attr('data-viewZone-id');
        eqLogic.order = order;
        eqLogics.push(eqLogic);
        order++;
      });
    });
    nextdom.view.setEqLogicOrder({
      eqLogics: eqLogics,
      error: function (error) {
        notify('Erreur', error.message, 'error');
      },
      success: function (data) {
        nextdom.cmd.setOrder({
          cmds: cmds,
          error: function (error) {
            notify('Erreur', error.message, 'error');
          }
        });
      }
    });
  }
}
