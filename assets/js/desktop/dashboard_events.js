$(function(){
    setTimeout(function(){
        if(typeof rootObjectId != 'undefined'){
            jeedom.object.getImgPath({
                id : rootObjectId,
                success : function(_path){
                    $('.backgroundforJeedom').css('background-image','url("'+_path+'")');
                }
            });
        }

    },1);
});

$('#bt_categorieHidden').on( 'click',function () {
    if($('.categorieHidden').css('display') == 'none'){
        $('.categorieHidden').show();
    }else{
        $('.categorieHidden').hide();
    }
});

$(document).ready(function(){
    $('.demo').MultiColumnSelect({
        multiple: false,
        useOptionText: true,
        hideselect: true,
        openmenuClass: 'mcs-open',
        openmenuText: 'Catégories',
        openclass: 'open',
        containerClass: 'mcs-container',
        itemClass: 'mcs-item',
        idprefix: 'categorie-',
        duration: 200,
        onOpen: null,
        onClose: null,
        onItemSelect: function(){
            SEL_CATEGORY = $('#sel_eqLogicCategory').value();
            SEL_TAG = $('#sel_eqLogicTags').value();
            gotoFilterDashboardPage();
        }
    });
    $('.demo2').MultiColumnSelect({
        multiple: false,
        useOptionText: true,
        hideselect: true,
        openmenuClass: 'mcs-open',
        openmenuText: 'Tags',
        openclass: 'open',
        containerClass: 'mcs-container',
        itemClass: 'mcs-item',
        idprefix: 'tags-',
        duration: 200,
        onOpen: null,
        onClose: null,
        onItemSelect: function(){
            SEL_CATEGORY = $('#sel_eqLogicCategory').value();
            SEL_TAG = $('#sel_eqLogicTags').value();
            gotoFilterDashboardPage();
        }
    });
});

$('#div_pageContainer').on( 'click','.eqLogic-widget .history', function () {
    $('#md_modal2').dialog({title: "Historique"});
    $("#md_modal2").load('index.php?v=d&modal=cmd.history&id=' + $(this).data('cmd_id')).dialog('open');
});

$('#bt_displayScenario').on('click', function () {
    if ($(this).attr('data-display') == 1) {
        $('#div_displayScenario').hide();
        if ($('#bt_displayObject').attr('data-display') == 1) {
            $('#div_displayObject').removeClass('col-lg-8 col-lg-10 col-lg-12 col-md-8 col-md-10 col-md-12 col-sm-8 col-sm-10 col-sm-12').addClass('col-lg-10 col-md-9 col-sm-8');
        } else {
            $('#div_displayObject').removeClass('col-lg-8 col-lg-10 col-lg-12 col-lg-8 col-lg-10 col-lg-12 col-md-8 col-md-10 col-md-12 col-sm-8 col-sm-10 col-sm-12').addClass('col-lg-12 col-md-12 col-sm-12');
        }
        $('.div_displayEquipement').each(function () {
            $(this).packery();
        });
        $(this).attr('data-display', 0);
    } else {
        $('#div_displayScenario').show();
        if ($('#bt_displayObject').attr('data-display') == 1) {
            $('#div_displayObject').removeClass('col-lg-8 col-lg-10 col-lg-12 col-lg-8 col-lg-10 col-lg-12 col-md-8 col-md-10 col-md-12 col-sm-8 col-sm-10 col-sm-12').addClass('col-lg-8 col-md-7 col-sm-5');
        } else {
            $('#div_displayObject').removeClass('col-lg-8 col-lg-10 col-lg-12 col-lg-8 col-lg-10 col-lg-12 col-md-8 col-md-10 col-md-12 col-sm-8 col-sm-10 col-sm-12').addClass('col-lg-10 col-md-9 col-sm-7');
        }
        $('.div_displayEquipement').packery();
        $(this).attr('data-display', 1);
    }
});

$('#bt_displayObject').on('click', function () {
    if ($(this).attr('data-display') == 1) {
        $('#div_displayObjectList').hide();
        if ($('#bt_displayScenario').attr('data-display') == 1) {
            $('#div_displayObject').removeClass('col-lg-8 col-lg-10 col-lg-12 col-lg-8 col-lg-10 col-lg-12 col-md-8 col-md-10 col-md-12 col-sm-8 col-sm-10 col-sm-12').addClass('col-lg-10 col-md-9 col-sm-7');
        } else {
            $('#div_displayObject').removeClass('col-lg-8 col-lg-10 col-lg-12 col-lg-8 col-lg-10 col-lg-12 col-md-8 col-md-10 col-md-12 col-sm-8 col-sm-10 col-sm-12').addClass('col-lg-12 col-md-12 col-sm-12');
        }
        $('.div_displayEquipement').each(function () {
            $(this).packery();
        });
        $(this).attr('data-display', 0);
    } else {
        $('#div_displayObjectList').show();
        if ($('#bt_displayScenario').attr('data-display') == 1) {
            $('#div_displayObject').removeClass('col-lg-8 col-lg-10 col-lg-12 col-lg-8 col-lg-10 col-lg-12 col-md-8 col-md-10 col-md-12 col-sm-8 col-sm-10 col-sm-12').addClass('col-lg-8 col-md-7 col-sm-5');
        } else {
            $('#div_displayObject').removeClass('col-lg-8 col-lg-10 col-lg-12 col-lg-8 col-lg-10 col-lg-12 col-md-8 col-md-10 col-md-12 col-sm-8 col-sm-10 col-sm-12').addClass('col-lg-10 col-md-9 col-sm-8');
        }
        $('.div_displayEquipement').packery();
        $(this).attr('data-display', 1);
    }
});

$('#bt_editDashboardWidgetOrder').on('click',function(){
    if($(this).attr('data-mode') == 1){
        $.hideAlert();
        $(this).attr('data-mode',0);
        editWidgetMode(0);
        $(this).css('color','black');
        $('.bt_editDashboardWidgetAutoResize').hide();
        $('.counterReorderJeedom').remove();
        $('.div_displayEquipement').packery();
    }else{
        $('#div_alert').showAlert({message: "{{Vous êtes en mode édition vous pouvez déplacer les widgets, les redimensionner et changer l'ordre des commandes dans les widgets. N'oubliez pas de quitter le mode édition pour sauvegarder}}", level: 'info'});
        $(this).attr('data-mode',1);
        $('.bt_editDashboardWidgetAutoResize').show();
        $('.bt_editDashboardWidgetAutoResize').off('click').on('click', function(){
            var id_object = $(this).attr('id');
            id_object = id_object.replace('edit_object_','');
            var heightObjectex = 0;
            $('#div_ob'+id_object+'.div_displayEquipement .eqLogic-widget').each(function(index, element){
                var heightObject = this.style.height;
                heightObject = eval(heightObject.replace('px',''));
                var valueAdd = eval(heightObject * 0.20);
                var valueRemove = eval(heightObject * 0.05);
                var heightObjectadd = eval(heightObject + valueAdd);
                var heightObjectremove = eval(heightObject - valueRemove);
                if(heightObjectadd >= heightObjectex && (heightObjectex > heightObject || heightObjectremove < heightObjectex)){
                    if($(element).hasClass('allowResize')){
                        $( element ).height(heightObjectex);
                        heightObject = heightObjectex;
                    }
                }
                heightObjectex = heightObject;
            });
        });
        editWidgetMode(1);
        $(this).css('color','rgb(46, 176, 75)');
    }
});


$('.li_object').on('click',function(){
    var object_id = $(this).find('a').attr('data-object_id');
    if($('.div_object[data-object_id='+object_id+']').html() != undefined){
        jeedom.object.getImgPath({
            id : object_id,
            success : function(_path){
                $('.backgroundforJeedom').css('background-image','url("'+_path+'")');
            }
        });
        $('.li_object').removeClass('active');
        $(this).addClass('active');
        displayChildObject(object_id,false);
    }else{
        loadPage($(this).find('a').attr('data-href'));
    }
});

