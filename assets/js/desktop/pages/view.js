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

$('#div_pageContainer').on('click', '.bt_gotoViewZone', function () {
  var top = $('.div_displayViewContainer').scrollTop() + $('.div_viewZone[data-zone_id=' + $(this).attr('data-zone_id') + ']').offset().top - 60;
  $('.div_displayViewContainer').animate({scrollTop: top}, 500);
});

function fullScreen(_mode) {
  if (_mode) {
    $('header').hide();
    $('#div_mainContainer').css('margin-top', '-50px');
    $('#wrap').css('margin-bottom', '0px');
    $('.div_displayView').height($('html').height() - 5);
    $('.div_displayViewContainer').height($('html').height() - 5);
    $('.bt_hideFullScreen').hide();
  } else {
    $('header').show();
    $('#div_mainContainer').css('margin-top', '0px');
    $('#wrap').css('margin-bottom', '15px');
    $('.div_displayView').height($('body').height());
    $('.div_displayViewContainer').height($('body').height());
    $('.bt_hideFullScreen').show();
  }
}

if (view_id != '') {
  nextdom.view.toHtml({
    id: view_id,
    version: 'dview',
    useCache: true,
    error: function (error) {
      notify('Erreur', error.message, 'error');
    },
    success: function (html) {
      setTimeout(function () {
        if (isset(html.raw) && isset(html.raw.img) && html.raw.img != '') {
          $('.backgroundforNextDom').css('background-image', 'url("' + html.raw.img + '") !important');
        } else {
          $('.backgroundforNextDom').css('background-image', 'url("")');
        }
      }, 1);
      try {
        var summary = '';
        for (var i in html.raw.viewZone) {
          summary += '<li style="padding:0px 0px"><a style="padding:2px 20px" class="cursor bt_gotoViewZone" data-zone_id="' + html.raw.viewZone[i].id + '">' + html.raw.viewZone[i].name + '</a></li>';
        }
        $('#ul_viewSummary').empty().append(summary);
      } catch (err) {
        console.log(err);
      }

      try {
        $('.div_displayView:last').empty().html(html.html);
      } catch (err) {
        console.log(err);
      }
      setTimeout(function () {
        initReportMode();
        positionEqLogic();
        $('.eqLogicZone').disableSelection();
        $("input").click(function () {
          $(this).focus();
        });
        $("textarea").click(function () {
          $(this).focus();
        });
        $('.eqLogicZone').each(function () {
          var container = $(this).packery({
            gutter: 2
          });
          var itemElems = container.find('.eqLogic-widget');
          itemElems.draggable();
          container.packery('bindUIDraggableEvents', itemElems);
          container.packery('on', 'dragItemPositioned', function () {
            $('.div_displayEquipement').packery();
          });

          function orderItems() {
            var itemElems = container.packery('getItemElements');
            $(itemElems).each(function (i, itemElem) {
              $(itemElem).attr('data-order', i + 1);
            });
          }

          container.on('layoutComplete', orderItems);
          container.on('dragItemPositioned', orderItems);
        });

        $('.eqLogicZone .eqLogic-widget').draggable('disable');
        $('#bt_editViewWidgetOrder').off('click').on('click', function () {
          if ($(this).attr('data-mode') == 1) {
            $.hideAlert();
            $(this).attr('data-mode', 0);
            editWidgetMode(0);
            $(this).css('color', 'black');
          } else {
            notify('Core', "{{Vous êtes en mode édition vous pouvez déplacer les widgets, les redimensionner et changer l'ordre des commandes dans les widgets}}", "info");
            $(this).attr('data-mode', 1);
            editWidgetMode(1);
            $(this).css('color', 'rgb(46, 176, 75)');
          }
        });
        if (getUrlVars('fullscreen') == 1) {
          fullScreen(true);
        }
      }, 10);
    }
  });
}

$('#div_pageContainer').delegate('.cmd-widget.history', 'click', function () {
  $('#md_modal2').dialog({title: "Historique"});
  $("#md_modal2").load('index.php?v=d&modal=cmd.history&id=' + $(this).data('cmd_id')).dialog('open');
});

$('.bt_displayView').on('click', function () {
  if ($(this).attr('data-display') == 1) {
    $(this).closest('.row').find('.div_displayViewList').hide();
    $(this).closest('.row').find('.div_displayViewContainer').removeClass('col-lg-8 col-lg-10 col-lg-12 col-lg-8 col-lg-10 col-lg-12 col-md-8 col-md-10 col-md-12 col-sm-8 col-sm-10 col-sm-12').addClass('col-lg-12 col-md-12 col-sm-12');
    $('.eqLogicZone').each(function () {
      $(this).packery();
    });
    $(this).attr('data-display', 0);
  } else {
    $(this).closest('.row').find('.div_displayViewList').show();
    $(this).closest('.row').find('.div_displayViewContainer').removeClass('col-lg-8 col-lg-10 col-lg-12 col-lg-8 col-lg-10 col-lg-12 col-md-8 col-md-10 col-md-12 col-sm-8 col-sm-10 col-sm-12').addClass('col-lg-10 col-md-9 col-sm-8');
    $('.eqLogicZone').packery();
    $(this).attr('data-display', 1);
  }
});

function editWidgetMode(_mode, _save) {
  if (!isset(_mode)) {
    if ($('#bt_editViewWidgetOrder').attr('data-mode') != undefined && $('#bt_editViewWidgetOrder').attr('data-mode') == 1) {
      editWidgetMode(0, false);
      editWidgetMode(1, false);
    }
    return;
  }
  if (_mode == 0 || _mode == '0') {
    if (!isset(_save) || _save) {
      saveWidgetDisplay({view: 1});
    }
    if (document.querySelectorAll('.eqLogicZone .eqLogic-widget.ui-draggable').length > 0) {
      $('.eqLogicZone .eqLogic-widget').draggable('disable');
      $('.eqLogicZone .eqLogic-widget.allowResize').resizable('destroy');
    }
  } else {
    $('.eqLogicZone .eqLogic-widget').draggable('enable');

    $(".eqLogicZone .eqLogic-widget.allowResize").resizable({
      grid: [2, 2],
      resize: function (event, ui) {
        positionEqLogic(ui.element.attr('data-eqlogic_id'), false);
        ui.element.closest('.eqLogicZone').packery();
      },
      stop: function (event, ui) {
        positionEqLogic(ui.element.attr('data-eqlogic_id'), false);
        ui.element.closest('.eqLogicZone').packery();
      }
    });
  }
  editWidgetCmdMode(_mode);
}

// View page link event handler declaration
$('#bt_gotoView').on('click',function(){
  if('ontouchstart' in window || navigator.msMaxTouchPoints){
    return;
  }
  $('ul.dropdown-menu [data-toggle=dropdown]').parent().parent().parent().siblings().removeClass('open');
  loadPage('index.php?v=d&p=view');
});

