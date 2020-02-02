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
    if (sel_plugin_id != -1) {
        showPlugin(sel_plugin_id);
    }
}

/**
 * Init events on the profils page
 */
function initEvents() {
    // Panel collapsing
    $('#bt_pluginCollapse').on('click',function(){
       $('#accordionPlugin .panel-collapse').each(function () {
          if (!$(this).hasClass("in")) {
              $(this).css({'height' : '' });
              $(this).addClass("in");
          }
       });
       $('#bt_pluginCollapse').hide();
       $('#bt_pluginUncollapse').show()
    });

    // Plugins panels uncollapsing
    $('#bt_pluginUncollapse').on('click',function(){
       $('#accordionPlugin .panel-collapse').each(function () {
          if ($(this).hasClass("in")) {
              $(this).removeClass("in");
          }
       });
       $('#bt_pluginUncollapse').hide();
       $('#bt_pluginCollapse').show()
    });

    // Markets open buttons
    $('.displayStore').on('click', function () {
        var repo = $(this).attr('data-repo');
        if (repo.indexOf('nextdom') === -1) {
            loadPage('index.php?v=d&p=marketJee&type=plugin');
        }
        else {
            loadPage('index.php?v=d&p=market&type=core');
        }
    });

    // Add plugin by other source button
    $('#bt_addPluginFromOtherSource').on('click',function(){
        $('#md_modal').dialog({title: "{{Ajouter un plugin}}"});
        $('#md_modal').load('index.php?v=d&modal=update.add').dialog('open');
    });

    // Plugin configure button
    $(".plugin_configure").on('click', function () {
        var id = $(this).closest('.pluginDisplayCard').attr('data-plugin_id');
        showPlugin(id);
        return false;
    });

    // Plugin delete button
    $('.plugin_delete').on('click',function(){
        var id = $(this).closest('.pluginDisplayCard').attr('data-plugin_id');
        bootbox.confirm('{{Êtes-vous sûr de vouloir supprimer ce plugin ?}}', function (result) {
            if (result) {
                $.hideAlert();
                nextdom.update.remove({
                    id: id,
                    error: function (error) {
                        notify('Core',error.message,'error');
                    },
                    success: function () {
                        loadPage('index.php?v=d&p=plugin');
                    }
                });
            }
        });
    });

    // Back plugin list button
    $('#bt_returnToThumbnailDisplay').on('click',function(){
        $('#div_resumePluginList').show();
        $('#div_confPlugin').hide();
    });

    // Plugin state toggle button
    $("#div_plugin_toggleState").delegate(".togglePlugin", 'click', function () {
        var _el = $(this);
        nextdom.plugin.toggle({
            id: _el.attr('data-plugin_id'),
            state: _el.attr('data-state'),
            error: function (error) {
                notify('Core',error.message,'error');
            },
            success: function () {
                if($('#md_modal').is(':visible')){
                    $("#md_modal").load('index.php?v=d&p=plugin&ajax=1&id=' + _el.attr('data-plugin_id')).dialog('open');
                }else{
                    window.location.href = 'index.php?v=d&p=plugin&id=' + _el.attr('data-plugin_id');
                }
            }
        });
    });

    // Plugin config save button
    $("#bt_savePluginConfig").on('click', function (event) {
        savePluginConfig();
        return false;
    });

    // Panel plugin config save button
    $('#bt_savePluginPanelConfig').off('click').on('click',function(){
        nextdom.config.save({
            configuration: $('#div_plugin_panel').getValues('.configKey')[0],
            plugin: sel_plugin_id,
            error: function (error) {
                notify('Core',error.message,'error');
            },
            success: function () {
                notify("Core",'{{Sauvegarde de la configuration des panneaux effectuée}}',"success");
                modifyWithoutSave = false;
            }
        });
    })

    // Functionnality plugin config save button
    $('#bt_savePluginFunctionalityConfig').off('click').on('click',function(){
        nextdom.config.save({
            configuration: $('#div_plugin_functionality').getValues('.configKey')[0],
            plugin: sel_plugin_id,
            error: function (error) {
                notify('Core',error.message,'error');
            },
            success: function () {
                notify("Core",'{{Sauvegarde des fonctionalités effectuée}}',"success");
                modifyWithoutSave = false;
            }
        });
    })

    // Log plugin config save button
    $('#bt_savePluginLogConfig').off('click').on('click',function(){
        nextdom.config.save({
            configuration: $('#div_plugin_log').getValues('.configKey')[0],
            error: function (error) {
                notify('Core',error.message,'error');
            },
            success: function () {
                notify("Core",'{{Sauvegarde de la configuration des logs effectuée}}',"success");
                modifyWithoutSave = false;
            }
        });
    })

    // Plugin log display button
    $('#div_plugin_log').on('click','.bt_plugin_conf_view_log',function(){
        if($('#md_modal').is(':visible')){
            $('#md_modal2').dialog({title: "{{Log du plugin}}"});
            $("#md_modal2").load('index.php?v=d&modal=log.display&log='+$(this).attr('data-log')).dialog('open');
        }else{
            $('#md_modal').dialog({title: "{{Log du plugin}}"});
            $("#md_modal").load('index.php?v=d&modal=log.display&log='+$(this).attr('data-log')).dialog('open');
        }
    });

    // Param changed : page leaving lock by msgbox
    $('#div_pageContainer').delegate('.configKey', 'change', function () {
        modifyWithoutSave = true;
    });
}

/**
 * Show plugin configuration panel
 *
 * @param pluginId string Plugin Id
 */
function showPlugin(pluginId) {
    $('#div_resumePluginList').hide();
    sel_plugin_id = pluginId;
    nextdom.plugin.get({
        id: pluginId,
        error: function (error) {
            notify('Core',error.message,'error');
        },
        success: function (data) {
            $('#span_plugin_id').html(data.id);
            $('#span_plugin_name').html(data.name);
            $('#span_plugin_author').html(data.author);
            $('#span_plugin_description').html(data.description);
            $('#span_plugin_icon').attr("src",data.icon);
            if(isset(data.update) && isset(data.update.localVersion)){
                $('#span_plugin_install_date').html(data.update.localVersion);
            }else{
                $('#span_plugin_install_date').html('');
            }
            $('#span_plugin_license').html(data.license);
            if($.trim(data.installation) == '' || $.trim(data.installation) == 'Aucune'){
                $('#span_plugin_installation').closest('.box').hide();
            }else{
                $('#span_plugin_installation').closest('.box').show();
                $('#span_plugin_installation').html(data.installation);
            }

            if(isset(data.update) && isset(data.update.configuration) && isset(data.update.configuration.version)){
                $('#span_plugin_install_version').html(data.update.configuration.version);
            }else{
                $('#span_plugin_install_version').html('');
            }

            if(data.hasDependency == 0 || data.activate != 1){
                $('#div_plugin_dependancy').closest('.box').hide();
            }else{
                $('#div_plugin_dependancy').closest('.box').show();
                $('#div_plugin_dependancy').closest('.panel')
                $("#div_plugin_dependancy").load('index.php?v=d&modal=plugin.dependancy&plugin_id='+data.id);
            }

            if(data.hasOwnDeamon == 0 || data.activate != 1){
                $('#div_plugin_deamon').closest('.box').hide();
            }else{
                $('#div_plugin_deamon').closest('.box').show();
                $("#div_plugin_deamon").load('index.php?v=d&modal=plugin.deamon&plugin_id='+data.id);
            }

            $('#span_plugin_doc').empty();
            if(isset(data.documentation) && data.documentation != ''){
                $('#span_plugin_doc').append('<a class="btn btn-success" target="_blank" href="'+data.documentation+'"><i class="fas fa-book spacing-right"></i>{{Documentation}}</a> ');
            }
            if(isset(data.changelog) && data.changelog != ''){
                $('#span_plugin_doc').append('<a class="btn btn-primary" target="_blank" href="'+data.changelog+'"><i class="fas fa-list-ul spacing-right"></i>{{Changelog}}</a> ');
            }
            if(isset(data.info.display) && data.info.display != ''){
                $('#span_plugin_doc').append('<a class="btn btn-default" target="_blank" href="'+data.info.display+'"><i class="fas fa-info-circle spacing-right"></i>{{Détails}}</a> ');
            }

            if (data.checkVersion != -1) {
                $('#span_plugin_require').html('<span>' + data.require + '</span>');
            } else {
                $('#span_plugin_require').html('<span class="label label-danger">' + data.require + '</span>');
            }

            $('#div_configPanel').hide();
            $('#div_plugin_panel').empty();
            if(isset(data.display) && data.display != ''){
                $('#div_configPanel').show();
                var config_panel_html = '<div class="form-group">';
                config_panel_html += '<label class="col-lg-4 col-md-4 col-sm-4 col-xs-6 control-label">{{Afficher le panneau desktop}}</label>';
                config_panel_html += '<div class="col-lg-2 col-md-3 col-sm-4 col-xs-6">';
                config_panel_html += '<input type="checkbox" class="configKey tooltips" data-l1key="displayDesktopPanel" />';
                config_panel_html += '</div>';
                config_panel_html += '</div>';
                $('#div_plugin_panel').append(config_panel_html);
            }

            if(isset(data.mobile) && data.mobile != ''){
                $('#div_configPanel').show();
                var config_panel_html = '<div class="form-group">';
                config_panel_html += '<label class="col-lg-4 col-md-4 col-sm-4 col-xs-6 control-label">{{Afficher le panneau mobile}}</label>';
                config_panel_html += '<div class="col-lg-2 col-md-3 col-sm-4 col-xs-6">';
                config_panel_html += '<input type="checkbox" class="configKey tooltips" data-l1key="displayMobilePanel" />';
                config_panel_html += '</div>';
                config_panel_html += '</div>';
                $('#div_plugin_panel').append(config_panel_html);
            }

            $('#div_plugin_functionality').empty();
            count = 0;
            var config_panel_html = '<div class="row">';
            config_panel_html += '<div class="col-xs-6">';
            for(var i in data.functionality){
                config_panel_html += '<div class="form-group">';
                config_panel_html += '<label class="col-xs-4 control-label">'+i+'</label>';
                if(data.functionality[i].exists){
                    config_panel_html += '<span class="label label-success label-sticker col-xs-2">{{Oui}}</span>';
                    if(data.functionality[i].controlable){
                        config_panel_html += '<div class="col-xs-6">';
                        config_panel_html += '<input type="checkbox" class="configKey tooltips" data-l1key="functionality::'+i+'::enable" checked/>';
                        config_panel_html += '<label class="control-label label-check">{{Activer}}</label>';
                        config_panel_html += '</div>';
                    }
                }else{
                    config_panel_html += '<span class="label label-danger label-sticker col-xs-2">{{Non}}</span>';
                }
                config_panel_html += '</div>';
                count++;
                if(count == 5){
                    config_panel_html += '</div>';
                    config_panel_html += '<div class="col-xs-6">';
                }
            }
            config_panel_html += '</div>';
            config_panel_html += '</div>';
            $('#div_plugin_functionality').append(config_panel_html);

            $('#div_plugin_toggleState').empty();
            if (data.checkVersion != -1) {
                var html = '<form class="form-horizontal">';
                if (data.activate == 1) {
                    $('#div_plugin_toggleState').closest('.box').removeClass('box-default box-danger').addClass('box-success');
                    html += '<span class="label label-success label-sticker-big pull-left">{{Actif}}</span>';
                }else{
                    $('#div_plugin_toggleState').closest('.box').removeClass('box-default box-success').addClass('box-danger');
                    html += '<span class="label label-danger label-sticker-big pull-left">{{Inactif}}</span>';
                }
                if (data.activate == 1) {
                    html += '<a class="btn btn-danger togglePlugin pull-right" data-state="0" data-plugin_id="' + data.id + '"><i class="fas fa-times spacing-right"></i>{{Désactiver}}</a>';
                }else{
                    html += '<a class="btn btn-success togglePlugin pull-right" data-state="1" data-plugin_id="' + data.id + '"><i class="fas fa-check spacing-right"></i>{{Activer}}</a>';
                }
                html += '</form>';
                $('#div_plugin_toggleState').html(html);
            }else{
                $('#div_plugin_toggleState').closest('.panel').removeClass('panel-default panel-success').addClass('panel-danger');
                $('#div_plugin_toggleState').html('{{Votre version de NextDom ne permet pas d\'activer ce plugin}}');
            }
            var log_conf = '';
            for(var i in  data.logs){
                log_conf = '<form class="form-horizontal">';
                log_conf += '<legend>{{Niveau de log local}}</legend>';
                log_conf += '<div class="form-group" style="min-height:40px;text-align:center;">';
                log_conf += '<label class="radio-inline"><input type="radio" name="rd_logupdate' + data.id + '" class="configKey" data-l1key="log::level::' + data.id + '" data-l2key="1000" /> {{Aucun}}</label>';
                log_conf += '<label class="radio-inline"><input type="radio" name="rd_logupdate' + data.id + '" class="configKey" data-l1key="log::level::' + data.id + '" data-l2key="default" /> {{Defaut}}</label>';
                log_conf += '<label class="radio-inline"><input type="radio" name="rd_logupdate' + data.id + '" class="configKey" data-l1key="log::level::' + data.id + '" data-l2key="100" /> {{Debug}}</label>';
                log_conf += '<label class="radio-inline"><input type="radio" name="rd_logupdate' + data.id + '" class="configKey" data-l1key="log::level::' + data.id + '" data-l2key="200" /> {{Info}}</label>';
                log_conf += '<label class="radio-inline"><input type="radio" name="rd_logupdate' + data.id + '" class="configKey" data-l1key="log::level::' + data.id + '" data-l2key="300" /> {{Warning}}</label>';
                log_conf += '<label class="radio-inline"><input type="radio" name="rd_logupdate' + data.id + '" class="configKey" data-l1key="log::level::' + data.id + '" data-l2key="400" /> {{Error}}</label>';
                log_conf += '</div>';
                log_conf += '<legend>{{Logs}}</legend>';
                for(j in data.logs[i].log){
                    log_conf += '<a class="btn btn-info bt_plugin_conf_view_log btn-log" data-slaveId="'+data.logs[i].id+'" data-log="'+data.logs[i].log[j]+'"><i class="fas fa-paperclip spacing-right"></i>'+data.logs[i].log[j].charAt(0).toUpperCase() + data.logs[i].log[j].slice(1)+'</a> ';
                }
            }

            log_conf += '<legend>{{Surveillance}}</legend>';
            log_conf += '<div class="form-group" style="min-height:40px;text-align:center;">';
            log_conf += '<label class="col-sm-3 control-label">{{Heartbeat (min)}}</label>';
            log_conf += '<div class="col-sm-2">';
            log_conf += '<input class="configKey form-control" data-l1key="heartbeat::delay::' + data.id + '" />';
            log_conf += '</div>';
            if(data.hasOwnDeamon){
                log_conf += '<label class="col-sm-3 control-label">{{Redémarrer démon}}</label>';
                log_conf += '<div class="col-sm-2">';
                log_conf += '<input type="checkbox" class="configKey" data-l1key="heartbeat::restartDeamon::' + data.id + '" />';
                log_conf += '</div>';
            }
            log_conf += '</div>';
            log_conf += '</form>';

            $('#div_plugin_log').empty().append(log_conf);
            $('#div_plugin_configuration').empty();
            if (data.checkVersion != -1) {
                if (data.configurationPath != '' && data.activate == 1) {
                    $('#div_plugin_configuration').load('index.php?v=d&plugin='+data.id+'&configure=1', function () {
                        if($.trim($('#div_plugin_configuration').html()) == ''){
                            $('#div_plugin_configuration').closest('.box').hide();
                            return;
                        }else{
                            $('#div_plugin_configuration').closest('.box').show();
                        }
                        nextdom.config.load({
                            configuration: $('#div_plugin_configuration').getValues('.configKey')[0],
                            plugin: sel_plugin_id,
                            error: function (error) {
                                notify('Core',error.message,'error');
                            },
                            success: function (data) {
                                $('#div_plugin_configuration').setValues(data, '.configKey');
                                $('#div_plugin_configuration').parent().show();
                                modifyWithoutSave = false;
                            }
                        });
                    });
                } else {
                    $('#div_plugin_configuration').closest('.box').hide();
                }
                nextdom.config.load({
                    configuration: $('#div_plugin_panel').getValues('.configKey')[0],
                    plugin: sel_plugin_id,
                    error: function (error) {
                        notify('Core',error.message,'error');
                    },
                    success: function (data) {
                        $('#div_plugin_panel').setValues(data, '.configKey');
                        modifyWithoutSave = false;
                    }
                });
                nextdom.config.load({
                    configuration: $('#div_plugin_functionality').getValues('.configKey')[0],
                    plugin: sel_plugin_id,
                    error: function (error) {
                        notify('Core',error.message,'error');
                    },
                    success: function (data) {
                        $('#div_plugin_functionality').setValues(data, '.configKey');
                        modifyWithoutSave = false;
                    }
                });
                nextdom.config.load({
                    configuration: $('#div_plugin_log').getValues('.configKey')[0],
                    error: function (error) {
                        notify('Core',error.message,'error');
                    },
                    success: function (data) {
                        $('#div_plugin_log').setValues(data, '.configKey');
                        modifyWithoutSave = false;
                    }
                });
            } else {
                $('#div_plugin_configuration').closest('.alert').hide();
            }
            $('#div_confPlugin').show();
            modifyWithoutSave = false;
        }
    });
}

/**
 * Show plugin configuration panel
 *
 * @param _param demon relaunch option
 */
function savePluginConfig(_param) {
    nextdom.config.save({
        configuration: $('#div_plugin_configuration').getValues('.configKey')[0],
        plugin: sel_plugin_id,
        error: function (error) {
            notify('Core',error.message,'error');
        },
        success: function () {
            notify("Core",'{{Sauvegarde effectuée}}',"success");
            modifyWithoutSave = false;
            var postSave = sel_plugin_id+'_postSaveConfiguration';
            if (typeof window[postSave] == 'function'){
                window[postSave]();
            }
            if (isset(_param) && typeof _param.success == 'function'){
                _param.success(0);
            }
            if(!isset(_param) || !isset(_param.relaunchDeamon) || _param.relaunchDeamon){
                nextdom.plugin.deamonStart({
                    id : sel_plugin_id,
                    slave_id: 0,
                    forceRestart: 1,
                    error: function (error) {
                        notify('Core',error.message,'error');
                    },
                    success: function (data) {
                        $("#div_plugin_deamon").load('index.php?v=d&modal=plugin.deamon&plugin_id='+sel_plugin_id);
                    }
                });
            }
        }
    });
}
