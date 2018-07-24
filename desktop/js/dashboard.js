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



var category_dashabord = getUrlVars('category');

if(category_dashabord == false){

    category_dashabord = 'all';

}



var summary_dashabord = getUrlVars('summary');

if(summary_dashabord == false){

    summary_dashabord = '';

}



$(document).on('click', '.panel-heading span.clickable', function(e){

    var $this = $(this);

    if(!$this.hasClass('panel-collapsed')) {

        $this.parents('.panel').find('.panel-body').slideUp();

        $this.addClass('panel-collapsed');

        $this.find('i').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');

    } else {

        $this.parents('.panel').find('.panel-body').slideDown();

        $this.removeClass('panel-collapsed');

        $this.find('i').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');

    }

})



$('#div_pageContainer').on( 'click','.eqLogic-widget .history', function () {

    $('#md_modal2').dialog({title: "Historique"});

    $("#md_modal2").load('index.php?v=d&modal=cmd.history&id=' + $(this).data('cmd_id')).dialog('open');

});





$('#bt_displayScenario').click(function(){

    var msglist = document.getElementById('bt_displayScenario');

    if (msglist.getAttribute('data-status') == "close"){

        msglist.setAttribute('data-status', "open");

        $('#div_displayScenario').animate({right: "0px"});

        $('.div_displayEquipement').packery();

        msglist.setAttribute("class", "fa-flip-horizontal fa fa-angle-double-left fa-2x icon_nextdom_blue");

    } else {

        msglist.setAttribute('data-status', "close");

        $('#div_displayScenario').animate({right: "-250px"});

        $('.div_displayEquipement').packery();

        msglist.setAttribute("class", "fa fa-angle-double-left fa-2x icon_nextdom_blue");

    }

});





$('#bt_displayObject').click(function(){

    var object = document.getElementById('bt_displayObject');

    if (object.getAttribute('data-status') == "close"){

        object.setAttribute('data-status', "open");

        $('#div_displayObjectList').animate({left: "0px"});

        $('.div_displayEquipement').packery();

        object.setAttribute("class", "fa-flip-horizontal fa fa-angle-double-right fa-2x icon_nextdom_blue");

    } else {

        object.setAttribute('data-status', "close");

        $('#div_displayObjectList').animate({left: "-250px"});

        $('.div_displayEquipement').packery();

        object.setAttribute("class", "fa fa-angle-double-right fa-2x icon_nextdom_blue");

    }

});





function editWidgetMode(_mode,_save){

    if(!isset(_mode)){

        if($('#bt_editDashboardWidgetOrder').attr('data-mode') != undefined && $('#bt_editDashboardWidgetOrder').attr('data-mode') == 1){

            editWidgetMode(0,false);

            editWidgetMode(1,false);

        }

        return;

    }

    if(_mode == 0){

        if(!isset(_save) || _save){

            saveWidgetDisplay({dashboard : 1});

        }

        if( $('.div_displayEquipement .eqLogic-widget.ui-resizable').length > 0){

            $('.div_displayEquipement .eqLogic-widget.allowResize').resizable('destroy');

        }

        if( $('.div_displayEquipement .eqLogic-widget.ui-draggable').length > 0){

            $('.div_displayEquipement .eqLogic-widget').draggable('disable');

        }

    }else{

        $('.div_displayEquipement .eqLogic-widget').draggable('enable');



        $( ".div_displayEquipement .eqLogic-widget.allowResize").resizable({

            grid: [ 2, 2 ],

            resize: function( event, ui ) {

                var el = ui.element;

                el.closest('.div_displayEquipement').packery();

            },

            stop: function( event, ui ) {

                var el = ui.element;

                positionEqLogic(el.attr('data-eqlogic_id'));

                el.closest('.div_displayEquipement').packery();

            }

        });

    }

    editWidgetCmdMode(_mode);

}



function getObjectHtml(_object_id){

    nextdom.object.toHtml({

        id: _object_id,

        version: 'dashboard',

        category : category_dashabord,

        summary : summary_dashabord,

        error: function (error) {

            notify("Erreur", error.message, 'error');

        },

        success: function (html) {

            if($.trim(html) == ''){

                $('#div_ob'+_object_id).parent().remove();

                return;

            }

            try {

                $('#div_ob'+_object_id).empty().html(html).parent().show();

            }catch(err) {

                console.log(err);

            }

            setTimeout(function(){

                positionEqLogic();

                $('#div_ob'+_object_id+'.div_displayEquipement').disableSelection();

                $("input").click(function() { $(this).focus(); });

                $("textarea").click(function() { $(this).focus(); });

                $("select").click(function() { $(this).focus(); });

                $('#div_ob'+_object_id+'.div_displayEquipement').each(function(){

                    var container = $(this).packery({

                        itemSelector: ".eqLogic-widget",

                        gutter : 2

                    });

                    var itemElems =  container.find('.eqLogic-widget');

                    itemElems.draggable();

                    container.packery( 'bindUIDraggableEvents', itemElems );

                    container.packery( 'on', 'dragItemPositioned',function(){

                        $('.div_displayEquipement').packery();

                    });

                    function orderItems() {

                        var itemElems = container.packery('getItemElements');

                        $( itemElems ).each( function( i, itemElem ) {

                            $( itemElem ).attr('data-order', i + 1 );

                        });

                    }

                    container.on( 'layoutComplete', orderItems );

                    container.on( 'dragItemPositioned', orderItems );

                });

                $('#div_ob'+_object_id+'.div_displayEquipement .eqLogic-widget').draggable('disable');

            },10);

        }

    });

}





$('#bt_editDashboardWidgetOrder').on('click',function(){

    if($(this).attr('data-mode') == 1){

        $.hideAlert();

        $(this).attr('data-mode',0);

        editWidgetMode(0);

        $(this).css('color','black');

    }else{

        notify("Info", "{{Vous êtes en mode édition vous pouvez déplacer les widgets, les redimensionner et changer l'ordre des commandes dans les widgets. N'oubliez pas de quitter le mode édition pour sauvegarder}}", 'info');

        $(this).attr('data-mode',1);

        editWidgetMode(1);

        $(this).css('color','rgb(46, 176, 75)');

    }

});





$('.li_object').on('click',function(){

    var object_id = $(this).find('a').attr('data-object_id');

    if($('.div_object[data-object_id='+object_id+']').html() != undefined){

        $('.li_object').removeClass('active');

        $(this).addClass('active');

        var top = $('#div_displayObject').scrollTop()+ $('.div_object[data-object_id='+object_id+']').offset().top - 60;

        $('#div_displayObject').animate({ scrollTop: top}, 500);

    }else{

        loadPage($(this).find('a').attr('data-href'));

    }

});
