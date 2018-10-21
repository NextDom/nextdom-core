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
setTimeout(function(){
    $('.listPlugin').packery();
    $('#listOther').packery();
},100);


$('#in_searchPlugin').off('keyup').keyup(function () {
    var search = $(this).value();
    if(search == ''){
        $('.box').show();
        $('#listPlugin').packery();
        $('#listOther').packery();
        return;
    }
    $('.box').hide();
    $('.box .box-title').each(function(){
        var text = $(this).text().toLowerCase();
        if(text.indexOf(search.toLowerCase()) >= 0){
            $(this)
            $(this).closest('.box').show();
        }
    });
    $('#listPlugin').packery();
    $('#listOther').packery();
});

printUpdate();

$("#md_specifyUpdate").dialog({
    closeText: '',
    autoOpen: false,
    modal: true,
    height: 600,
    width: 600,
    open: function () {
        $("body").css({overflow: 'hidden'});
    },
    beforeClose: function (event, ui) {
        $("body").css({overflow: 'inherit'});
    }
});

$("#md_updateInfo").dialog({
    closeText: '',
    autoOpen: false,
    modal: true,
    height: 600,
    width: 900,
    open: function () {
        $("body").css({overflow: 'hidden'});
    },
    beforeClose: function (event, ui) {
        $("body").css({overflow: 'inherit'});
    }
});

$('#bt_updateNextDom').off('click').on('click', function () {
    $('#md_specifyUpdate').dialog({title: "{{Options}}"});
    $("#md_specifyUpdate").dialog('open');
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
    $("#md_specifyUpdate").dialog('close');
    $('#md_updateInfo').dialog({title: "{{Avancement des mises a jour}}"});
    $("#md_updateInfo").dialog('open');
    var options = $('#md_specifyUpdate').getValues('.updateOption')[0];
    $.hideAlert();
    nextdom.update.doAll({
        options: options,
        error: function (error) {
            notify("Erreur", error.message, 'error');
        },
        success: function () {
            getNextDomLog(1, 'update');
        }
    });
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


$('#listPlugin,#listOther').delegate('.update', 'click', function () {
    var id = $(this).closest('.box').attr('data-id');
    bootbox.confirm('{{Etes vous sur de vouloir mettre à jour cet objet ?}}', function (result) {
        if (result) {
            $.hideAlert();
            nextdom.update.do({
                id: id,
                error: function (error) {
                    notify("Erreur", error.message, 'error');
                },
                success: function () {
                    getNextDomLog(1, 'update');
                }
            });
        }
    });
});

$('#listPlugin,#listOther').delegate('.remove', 'click', function () {
    var id = $(this).closest('.box').attr('data-id');
    bootbox.confirm('{{Etes vous sur de vouloir supprimer cet objet ?}}', function (result) {
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

$('#listPlugin,#listOther').delegate('.checkUpdate', 'click', function () {
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
            for (var i in data) {
                addUpdate(data[i]);
            }
            $('#listPlugin').trigger('update');
            $('#listOther').trigger('update');
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
    tr += '<div class="box-header">';
    tr += ' <h4 class="box-title"><img style="height:50px;padding-right:5px;" src="' + init(_update.icon) + '"/> <b>' + init(_update.name)+'</b> -  ';
    if(_update.configuration && _update.configuration.version){
        tr += _update.configuration.version ;
    }
    tr +='</h4>';
    tr += '<span data-toggle="tooltip" title="" class="updateAttr badge ' + bgClass +' pull-right" data-original-title="" data-l1key="status" style="text-transform: uppercase;"></span>';
    tr += '</div>';
    tr += '<div class="box-body">';
    tr += '<span class="updateAttr" data-l1key="id" style="display:none;"></span><b>{{Source : }}</b><span class="updateAttr" data-l1key="source"></span>';
    tr += '<p><b>{{Type : }}</b><span class="updateAttr" data-l1key="type"></span></p>';
    tr += '<p><i>{{Version : }}</i>'+_update.remoteVersion+'</p>';
    if (_update.type != 'core') {
        tr += '<input type="checkbox" class="updateAttr" data-l1key="configuration" data-l2key="doNotUpdate"><span style="font-size:1em;">{{Ne pas mettre à jour}}</span></br>';
    }
    tr += '</div>';
    tr += '<div class="box-footer clearfix text-center">';

    if (_update.type != 'core') {
        if (_update.status == 'update') {
            tr += '<a class="btn btn-warning btn-sm update" title="{{Mettre à jour}}"><i class="fas fa-refresh">&nbsp;&nbsp;</i>{{Mettre à jour}}</a> ';
        }else if (_update.type != 'core') {
            tr += '<a class="btn  btn-default btn-sm update" title="{{Re-installer}}"><i class="fas fa-refresh">&nbsp;&nbsp;</i>{{Reinstaller}}</a> ';
        }
    }
    if (_update.type != 'core') {
        if (isset(_update.plugin) && isset(_update.plugin.changelog) && _update.plugin.changelog != '') {
            tr += '<a class="btn btn-default btn-sm pull-left cursor" target="_blank" href="'+_update.plugin.changelog+'"><i class="fas fa-book">&nbsp;&nbsp;</i>{{Changelog}}</a>';
        }
    }else{
        tr += '<a class="btn btn-default btn-sm" href="https://nextdom.github.io/core/fr_FR/changelog" target="_blank"><i class="fas fa-book">&nbsp;&nbsp;</i>{{Changelog}}</a>';
    }
    tr += '<a class="btn btn-info btn-sm pull-left checkUpdate" ><i class="fas fa-check">&nbsp;&nbsp;</i>{{Vérifier}}</a>';
    if (_update.type != 'core') {
        tr += '<a class="btn btn-danger btn-sm pull-right remove" ><i class="far fa-trash-alt">&nbsp;&nbsp;</i>{{Supprimer}}</a>';
    }
    tr += '</div>';
    tr += '</div>';



    if(_update.type == 'core' || _update.type == 'plugin'){
        $('#listPlugin').append(tr);
        $('#listPlugin .box:last').setValues(_update, '.updateAttr');
    }else{
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
