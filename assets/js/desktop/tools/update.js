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

setTimeout(function(){
    $('#listPlugin').packery();
    $('#listOther').packery();
    $('#listCore').packery();
    $('#listWidget').packery();
    $('#listScript').packery();
},100);


$('#in_searchPlugin').off('keyup').keyup(function () {
    var search = $(this).value();
    if(search == ''){
        $('.box-warning').show();
        $('.box-success').show();
        $('.box-danger').show();
        $('#listPlugin').packery();
        $('#listOther').packery();
        $('#listCore').packery();
        $('#listWidget').packery();
        $('#listScript').packery();
        return;
    }
    $('.box-warning').hide();
    $('.box-success').hide();
    $('.box-danger').hide();
    $('.box .box-title').each(function(){
        var text = $(this).text().toLowerCase();
        if(text.indexOf(search.toLowerCase()) >= 0){
            $(this)
            $(this).closest('.box').show();
        }
    });
    $('#listPlugin').packery();
    $('#listOther').packery();
    $('#listCore').packery();
    $('#listWidget').packery();
    $('#listScript').packery();
});

printUpdate();

$('#pre_updateInfo').height($(window).height() - $('header').height() - $('footer').height() - 150);

$('#bt_updateNextDom').off('click').on('click', function () {
    $('#md_specifyUpdate').modal('show');
});


$('.updateOption[data-l1key=force]').off('click').on('click',function(){
    if($(this).value() == 1){
        $('.updateOption[data-l1key="backup::before"]').value(0);
        $('.updateOption[data-l1key="backup::before"]').attr('disabled','disabled');

    }else{
        $('.updateOption[data-l1key="backup::before"]').attr('disabled',false);
    }
});


$('#bt_doUpdate').off('click').on('click', function () {
    $("#md_specifyUpdate").modal('hide');
    $('#md_updateInfo').dialog({title: "{{Avancement de la mise à jour}}"});
    $("#md_updateInfo").dialog('open');
    var options = $('#md_specifyUpdate').getValues('.updateOption')[0];
    $.hideAlert();
    nextdom.update.doAll({
        options: options,
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            $('a[data-toggle=tab][href="#log"]').click();
            getNextDomLog(1, 'update');
        }
    });
});

$("#bt_updateOpenLog").on('click', function (event) {
  $('#md_updateInfo').dialog({title: "{{Avancement de la mise à jour}}"});
  $("#md_updateInfo").dialog('open');
});

$('#bt_checkAllUpdate').off('click').on('click', function () {
    $.hideAlert();
    nextdom.update.checkAll({
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            printUpdate();
        }
    });
});


$('#listPlugin,#listOther,#listCore,#listWidget,#listScript').delegate('.update', 'click', function () {
    var id = $(this).closest('.box').attr('data-id');
    bootbox.confirm('{{Etes vous sur de vouloir mettre à jour cet objet ?}}', function (result) {
        if (result) {
            $.hideAlert();
            $('#md_updateInfo').dialog({title: "{{Avancement de la mise à jour}}"});
            $("#md_updateInfo").dialog('open');
            nextdom.update.do({
                id: id,
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function () {
                    $('a[data-toggle=tab][href="#log"]').click();
                    getNextDomLog(1, 'update');
                }
            });
        }
    });
});

$('#listPlugin,#listOther,#listCore,#listWidget,#listScript').delegate('.remove', 'click', function () {
    var id = $(this).closest('.box').attr('data-id');
    bootbox.confirm('{{Etês-vous sûr de vouloir supprimer cet objet ?}}', function (result) {
        if (result) {
            $.hideAlert();
            nextdom.update.remove({
                id: id,
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function () {
                    printUpdate();
                }
            });
        }
    });
});

$('#listPlugin,#listOther,#listCore,#listWidget,#listScript').delegate('.checkUpdate', 'click', function () {
    var id = $(this).closest('.box').attr('data-id');
    $.hideAlert();
    nextdom.update.check({
        id: id,
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            printUpdate();
        }
    });

});

$("#md_updateInfo").dialog({
    closeText: '',
    autoOpen: false,
    modal: true,
    width: ((jQuery(window).width() - 50) < 1500) ? (jQuery(window).width() - 50) : 1500,
    open: function () {
        $("body").css({overflow: 'hidden'});
    },
    beforeClose: function (event, ui) {
        $("body").css({overflow: 'inherit'});
    }
});

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
            if($.isArray(data.result)){
                for (var i in data.result.reverse()) {
                    log += data.result[i]+"\n";
                    if(data.result[i].indexOf('[END ' + _log.toUpperCase() + ' SUCCESS]') != -1){
                        printUpdate();
                        notify("Info", '{{L\'opération est réussie. Merci de faire F5 pour avoir les dernières nouveautés}}', 'success');
                        _autoUpdate = 0;
                    }
                    if(data.result[i].indexOf('[END ' + _log.toUpperCase() + ' ERROR]') != -1){
                        printUpdate();
                        notify("Erreur", '{{L\'opération a échoué}}', 'error');
                        _autoUpdate = 0;
                    }
                }
            }
            $('#pre_' + _log + 'Info').text(log);
            $('#pre_updateInfo').parent().scrollTop($('#pre_updateInfo').parent().height() + 200000);
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

function printUpdate() {

    nextdom.update.get({
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            $('#listPlugin').empty();
            $('#listOther').empty();
            $('#listCore').empty();
            $('#listWidget').empty();
            $('#listScript').empty();
            for (var i in data) {
                addUpdate(data[i]);
            }
            $('#listPlugin').trigger('update');
            $('#listOther').trigger('update');
            $('#listCore').trigger('update');
            $('#listWidget').trigger('update');
            $('#listScript').trigger('update');
        }
    });

    nextdom.config.load({
        configuration: {"update::lastCheck":0,"update::lastDateCore": 0},
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            $('#span_lastUpdateCheck').value(data['update::lastCheck']);
            $('#span_lastUpdateCheck').attr('title','{{Dernière mise à jour du core : }}'+data['update::lastDateCore']);
        }
    });
}

function addUpdate(_update) {
    var boxClass = 'box-success';
    var bgClass = 'bg-green';

    if(init(_update.status) == ''){
        _update.status = 'ok';
    }
    if (_update.status == 'update'){
        boxClass = 'box-warning';
        bgClass = 'bg-yellow';
    }

    var tr = '<div class="objet col-lg-4 col-md-6 col-sm-6 col-xs-12">';
    tr += '<div class="box ' + boxClass +'" data-id="' + init(_update.id) + '" data-logicalId="' + init(_update.logicalId) + ' col-lg-4 col-md-6 col-sm-6 col-xs-12" data-type="' + init(_update.type) + '">';
    tr += '<div class="box-header with-border">';
    if (_update.type == 'core') {
        tr += ' <h4 class="box-title" style="text-transform: capitalize;"><img style="height:50px;padding-right:5px;" src="/public/img/NextDom/NextDom_Square_AlphaBlackBlue.png"/>' + init(_update.name)+'</h4>';
    }else{
        tr += ' <h4 class="box-title" style="text-transform: capitalize;"><img style="height:50px;padding-right:5px;" src="' + init(_update.icon) + '"/>' + init(_update.name)+'</h4>';
    }
    tr += '<span data-toggle="tooltip" title="" class="updateAttr badge ' + bgClass +' pull-right" data-original-title="" data-l1key="status" style="text-transform: uppercase;"></span>';
    tr += '</div>';
    tr += '<div class="box-body">';
    tr += '<span class="updateAttr" data-l1key="id" style="display:none;"></span><p><b>{{Source : }}</b><span class="updateAttr" data-l1key="source"></span></p>';
    tr += '<p><b>{{Type : }}</b><span class="updateAttr" data-l1key="type"></span></p>';
    tr += '<p><b>{{Branche : }}</b>';
    if(_update.configuration && _update.configuration.version){
        tr += _update.configuration.version ;
    }
    tr += '</p>';
    tr += '<p><b>{{Version : }}</b>'+_update.remoteVersion+'</p>';
    if (_update.type != 'core') {
        tr += '<input type="checkbox" class="updateAttr" data-l1key="configuration" data-l2key="doNotUpdate"><span style="font-size:1em;">{{Ne pas mettre à jour}}</span></br>';
    }
    tr += '</div>';
    tr += '<div class="box-footer clearfix text-center">';

    if (_update.type != 'core') {
        tr += '<a class="btn btn-danger btn-sm pull-right remove" ><i class="far fa-trash-alt spacing-right"></i>{{Supprimer}}</a>';
        if (_update.status == 'update') {
            tr += '<a class="btn btn-warning btn-sm update pull-right" title="{{Mettre à jour}}"><i class="fas fa-refresh spacing-right"></i>{{Mettre à jour}}</a> ';
        }else if (_update.type != 'core') {
            tr += '<a class="btn  btn-default btn-sm update pull-right" title="{{Re-installer}}"><i class="fas fa-refresh spacing-right"></i>{{Reinstaller}}</a> ';
        }
        if (isset(_update.plugin) && isset(_update.plugin.changelog) && _update.plugin.changelog != '') {
            tr += '<a class="btn btn-default btn-sm pull-left cursor hidden-sm" target="_blank" href="'+_update.plugin.changelog+'"><i class="fas fa-book spacing-right"></i>{{Changelog}}</a>';
        }
    }else{
        tr += '<a class="btn btn-default btn-sm pull-right" href="https://nextdom.github.io/core/fr_FR/changelog" target="_blank"><i class="fas fa-book spacing-right"></i>{{Changelog}}</a>';
    }
    tr += '<a class="btn btn-info btn-sm pull-left checkUpdate" ><i class="fas fa-check spacing-right"></i>{{Vérifier}}</a>';
    tr += '</div>';
    tr += '</div>';

    switch(_update.type) {
        case 'core':
            $('#listCore').append(tr);
            $('#listCore .box:last').setValues(_update, '.updateAttr');
            break;
        case 'plugin':
            $('#listPlugin').append(tr);
            $('#listPlugin .box:last').setValues(_update, '.updateAttr');
            break;
        case 'widget':
            $('#listWidget').append(tr);
            $('#listWidget .box:last').setValues(_update, '.updateAttr');
            break;
        case 'script':
            $('#listScript').append(tr);
            $('#listScript .box:last').setValues(_update, '.updateAttr');
            break;
        default:
            $('#listOther').append(tr);
            $('#listOther .box:last').setValues(_update, '.updateAttr');
    }
}

$('#bt_saveUpdate').on('click',function(){
    nextdom.update.saves({
        updates : $('#table_update tbody tr').getValues('.updateAttr'),
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function (data) {
            notify("Info", '{{Sauvegarde effectuée}}', 'success');
            printUpdate();
        }
    });
});
