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
    $( ".eqLogicSortable" ).sortable({
      connectWith: ".eqLogicSortable",
      stop: function (event, ui) {
        var eqLogics = [];
        var object = ui.item.closest('.object');
        order = 1;
        object.find('.eqLogic').each(function(){
            eqLogic = {};
            eqLogic.object_id = object.attr('data-id');
            eqLogic.id = $(this).attr('data-id');
            eqLogic.order = order;
            eqLogics.push(eqLogic);
            order++;
        });
        nextdom.eqLogic.setOrder({
            eqLogics: eqLogics,
            error: function (error) {
                notify('Erreur', error.message, 'error');
                $( ".eqLogicSortable" ).sortable( "cancel" );
            }
        });
      }
    }).disableSelection();

    $( ".cmdSortable" ).sortable({
      stop: function (event, ui) {
        var cmds = [];
        var eqLogic = ui.item.closest('.eqLogic');
        order = 1;
        eqLogic.find('.cmd').each(function(){
            cmd = {};
            cmd.id = $(this).attr('data-id');
            cmd.order = order;
            cmds.push(cmd);
            order++;
        });
        nextdom.cmd.setOrder({
            cmds: cmds,
            error: function (error) {
                notify('Erreur', error.message, 'error');
            }
        });
      }
    }).disableSelection();
}

/**
 * Init events on the profils page
 */
function initEvents() {
    // Panels collpase button
    $('#bt_displayCollapse').on('click',function(){
       $('#accordionDisplay .panel-collapse').each(function () {
          if (!$(this).hasClass("in")) {
              $(this).css({'height' : '' });
              $(this).addClass("in");
          }
       });
       $('#bt_displayCollapse').hide();
       $('#bt_displayUncollapse').show()
    });

    // Panels uncollapse button
    $('#bt_displayUncollapse').on('click',function(){
       $('#accordionDisplay .panel-collapse').each(function () {
          if ($(this).hasClass("in")) {
              $(this).removeClass("in");
          }
       });
       $('#bt_displayUncollapse').hide();
       $('#bt_displayCollapse').show()
    });

    // Equipement title double clic
    $( ".eqLogic" ).on('dblclick',function(e){
        if(e.target != this) return;
        $('#md_modal').dialog({title: "{{Configuration de l'équipement}}"});
        $('#md_modal').load('index.php?v=d&modal=eqLogic.configure&eqLogic_id=' + $(this).attr('data-id')).dialog('open');
    });

    // Equipement configure button
    $('.configureEqLogic').on('click',function(){
       $('#md_modal').dialog({title: "{{Configuration de l'équipement}}"});
       $('#md_modal').load('index.php?v=d&modal=eqLogic.configure&eqLogic_id=' + $(this).closest('.eqLogic').attr('data-id')).dialog('open');
    });

    // Object configure button
    $('.configureObject').on('click',function(){
       $('#md_modal').dialog({title: "{{Configuration de l'objet}}"});
       $('#md_modal').load('index.php?v=d&modal=object.configure&object_id=' + $(this).closest('.object').attr('data-id')).dialog('open');
    });

    // Object openning button
    $('.openObject').on('click',function(){
        loadPage($(this).attr('data-id'));
    });

    // Cmd double clic
    $( ".cmd" ).on('dblclick',function(){
       $('#md_modal').dialog({title: "{{Configuration de la commande}}"});
       $('#md_modal').load('index.php?v=d&modal=cmd.configure&cmd_id=' + $(this).attr('data-id')).dialog('open');
    });

    // cmd configure button
    $('.configureCmd').on('click',function(){
       $('#md_modal').dialog({title: "{{Configuration de la commande}}"});
       $('#md_modal').load('index.php?v=d&modal=cmd.configure&cmd_id=' + $(this).closest('.cmd').attr('data-id')).dialog('open');
    });

    // Cmd collapsing/uncollapsing arrow button
    $('.showCmd').on('click',function(){
        if($(this).hasClass('fa-chevron-right')){
            $(this).removeClass('fa-chevron-right').addClass('fa-chevron-down');
            $(this).closest('.eqLogic').find('.cmdSortable').show();
        }else if($(this).hasClass('fa-chevron-down')){
            $(this).removeClass('fa-chevron-down').addClass('fa-chevron-right');
            $(this).closest('.eqLogic').find('.cmdSortable').hide();
        }else{
            $(this).find('.summary-box-tools').each(function(){
                if ($(this).hasClass('fa-chevron-right')){
                    $(this).removeClass('fa-chevron-right').addClass('fa-chevron-down');
                    $(this).closest('.eqLogic').find('.cmdSortable').show();
                }else if($(this).hasClass('fa-chevron-down')){
                    $(this).removeClass('fa-chevron-down').addClass('fa-chevron-right');
                    $(this).closest('.eqLogic').find('.cmdSortable').hide();
                }
            });
        }
        $('.displayListContainer').packery();
    });

    // Equipement collapsing/uncollapsing arrow button
    $('.showEqLogic').on('click',function(){
      if($(this).hasClass('fa-chevron-right')){
          $(this).removeClass('fa-chevron-right').addClass('fa-chevron-down');
          $(this).closest('.object').find('.eqLogic').show();
      }else{
          $(this).removeClass('fa-chevron-down').addClass('fa-chevron-right');
          $(this).closest('.object').find('.eqLogic').hide();
      }
    });

    // Activ checkbox change
    $('#cb_actifDisplay').on('change',function(){
        if($(this).value() == 1){
            $('.eqLogic[data-enable=0]').show();
        }else{
            $('.eqLogic[data-enable=0]').hide();
        }
    });

    // Visible checkbox change
    $('#cb_visibleDisplay').on('change',function(){
        if($(this).value() == 1){
            $('.eqLogic[data-visible=0]').show();
        }else{
            $('.eqLogic[data-visible=0]').hide();
        }
    });

    // Equipement checkbox change
    $('.cb_selEqLogic').on('change',function(){
        var found = false;
        $('.cb_selEqLogic').each(function(){
            if($(this).value() == 1){
                found = true;
            }
        });
        if(found){
            $('#bt_removeEqlogic').show();
            $('.bt_setIsVisible').show();
            $('.bt_setIsEnable').show();
        }else{
            $('#bt_removeEqlogic').hide();
            $('.bt_setIsVisible').hide();
            $('.bt_setIsEnable').hide();
        }
        setHeaderPosition(false);
    });

    // Equipement remove button
    $('#bt_removeEqlogic').on('click',function(){
        bootbox.confirm('{{Etes-vous sûr de vouloir supprimer tous ces équipements ?}}', function (result) {
            if (result) {
                var eqLogics = [];
                $('.cb_selEqLogic').each(function(){
                    if($(this).value() == 1){
                        eqLogics.push($(this).closest('.eqLogic').attr('data-id'));
                    }
                });
                nextdom.eqLogic.removes({
                    eqLogics: eqLogics,
                    error: function (error) {
                        notify('Erreur', error.message, 'error');
                    },
                    success : function(){
                        loadPage('index.php?v=d&p=display');
                    }
                });
            }
        });
    });

    // Equipement visible button
    $('.bt_setIsVisible').on('click',function(){
        var eqLogics = [];
        $('.cb_selEqLogic').each(function(){
            if($(this).value() == 1){
                eqLogics.push($(this).closest('.eqLogic').attr('data-id'));
            }
        });
        nextdom.eqLogic.setIsVisibles({
            eqLogics: eqLogics,
            isVisible : $(this).attr('data-value'),
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success : function(){
             loadPage('index.php?v=d&p=display');
         }
       });
    });

    // Equipement enable button
    $('.bt_setIsEnable').on('click',function(){
        var eqLogics = [];
        $('.cb_selEqLogic').each(function(){
            if($(this).value() == 1){
                eqLogics.push($(this).closest('.eqLogic').attr('data-id'));
            }
        });
        nextdom.eqLogic.setIsEnables({
            eqLogics: eqLogics,
            isEnable : $(this).attr('data-value'),
            error: function (error) {
                notify('Erreur', error.message, 'error');
            },
            success : function(){
                loadPage('index.php?v=d&p=display');
            }
        });
    });

    // History deleting opening modal button
    $('#bt_removeHistory').on('click',function(){
        $('#md_modal').dialog({title: "{{Historique des suppressions}}"});
        $('#md_modal').load('index.php?v=d&modal=remove.history').dialog('open');
    });
}
