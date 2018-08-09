<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
global $NEXTDOM_INTERNAL_CONFIG;
sendVarToJS('sel_plugin_id', init('id', '-1'));
$plugins_list = plugin::listPlugin(false, true);
?>
<section class="content">
<div class="row row-overflow">
    <div class="col-md-12 col-sm-12" id="div_resumePluginList" style="border-left: solid 1px #EEE; padding-left: 25px;">
        <div class="box">
            <div class="box-header">
                <legend><i class="fa fa-cog"></i> {{Gestion}}</legend>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
            </div>
            <div class="pluginListContainer box-body">
                <div class="iconlist text-center" id="bt_addPluginFromOtherSource">
                    <i class="fa fa-plus" style="font-size : 6em;color:#33B8CC;"></i>
                    <span style="font-size : 1.1em;position:relative;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#33B8CC"><center>{{Sources}}</center></span>
                </div>
                <?php
                foreach (update::listRepo() as $key => $value) {
                    if (!$value['enable']) {
                        continue;
                    }
                    if (!isset($value['scope']['hasStore']) || !$value['scope']['hasStore']) {
                        continue;
                    }
                    echo '<div class="iconlist displayStore text-center" data-repo="' . $key . '">
                <i class="fa fa-shopping-cart" style="font-size : 6em;color:#33B8CC;"></i>
                <span style="font-size : 1.1em;position:relative;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#33B8CC"><center>' . $value['name'] . '</center></span>
                </div>';
                }
                ?>
            </div>
        </div>
        <div class="box">
            <div class="box-header with-border">
                <legend><i class="fa fa-list-alt"></i> {{Mes plugins}}</legend>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
                <input class="form-control" placeholder="{{Rechercher}}" id="in_searchPlugin" /></div>

            <div class="pluginListContainer box-body">
                <?php
                foreach (plugin::listPlugin() as $plugin) {
                    $opacity = ($plugin->isActive()) ? '' : nextdom::getConfiguration('eqLogic:style:noactive');
                    echo '<div class="pluginDisplayCard iconlist text-center" data-pluginPath="' . $plugin->getFilepath() . '" data-plugin_id="' . $plugin->getId() . '" style="' . $opacity . '" >';
                    echo '<img class="img-responsive" style="width : 100px;" src="' . $plugin->getPathImgIcon() . '" />';
                    echo '<span style="display:none;" class="name">' . $plugin->getName() . '</span>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-9 col-sm-8" id="div_confPlugin" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
        <legend>
            <i class="fa fa-arrow-circle-left cursor" id="bt_returnToThumbnailDisplay"></i>
            <span id="span_plugin_name" ></span> (<span id="span_plugin_id"></span>) - <span id="span_plugin_install_version"></span>
            <span id="span_plugin_market" class="pull-right"></span>
            <span id="span_plugin_delete" class="pull-right"></span>
            <span id="span_plugin_doc" class="pull-right"></span>
        </legend>

        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="box">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-circle-o-notch"></i> {{Etat}}</h3></div>
                    <div class="box-body">
                        <div id="div_plugin_toggleState"></div>
                        <form class="form-horizontal">
                            <fieldset>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">{{Version}}</label>
                                    <div class="col-sm-4">
                                        <span id="span_plugin_install_date" style="position:relative;top:9px;"></span>
                                    </div>
                                    <label class="col-sm-2 control-label">{{Version NextDom}}</label>
                                    <div class="col-sm-4">
                                        <span id="span_plugin_require" style="position:relative;top:9px;"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">{{Auteur}}</label>
                                    <div class="col-sm-4">
                                        <span id="span_plugin_author" style="position:relative;top:9px;"></span>
                                    </div>
                                    <label class="col-sm-2 control-label">{{License}}</label>
                                    <div class="col-sm-4">
                                        <span id="span_plugin_license" style="position:relative;top:9px;"></span>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-12">
                <div class="box box-primary" id="div_configLog">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-file-o"></i> {{Log}}
                            <a class="btn btn-success btn-xs pull-right" id="bt_savePluginLogConfig"><i class="fa fa-check-circle icon-white "></i> {{Sauvegarder}}</a>
                        </h3></div>
                    <div class="box-body">
                        <form class="form-horizontal">
                            <fieldset>
                                <div id="div_plugin_log"></div>
                            </fieldset>
                        </form>
                        <div class="form-actions">

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="box">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-certificate"></i> {{Dépendances}}</h3></div>
                    <div class="box-body">
                        <div id="div_plugin_dependancy"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-12">
                <div class="box box-success">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-university"></i> {{Démon}}</h3></div>
                    <div class="box-body">
                        <div id="div_plugin_deamon"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-map"></i> {{Installation}}</h3></div>
            <div class="box-body">
                <span id="span_plugin_installation"></span>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-cogs"></i> {{Configuration}}
                    <a class="btn btn-success btn-xs pull-right" id="bt_savePluginConfig"><i class="fa fa-check-circle icon-white"></i> {{Sauvegarder}}</a>
                </h3></div>
            <div class="box-body">
                <div id="div_plugin_configuration"></div>

                <div class="form-actions">

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="box box-primary" id="div_functionalityPanel">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-picture-o"></i> {{Fonctionnalités}}
                            <a class="btn btn-success btn-xs pull-right" id="bt_savePluginFunctionalityConfig"><i class="fa fa-check-circle icon-white"></i> {{Sauvegarder}}</a>
                        </h3></div>
                    <div class="box-body">
                        <form class="form-horizontal">
                            <fieldset>
                                <div id="div_plugin_functionality"></div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-12">
                <div class="box box-primary" id="div_configPanel">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-picture-o"></i> {{Panel}}
                            <a class="btn btn-success btn-xs pull-right" id="bt_savePluginPanelConfig"><i class="fa fa-check-circle icon-white"></i> {{Sauvegarder}}</a>
                        </h3></div>
                    <div class="box-body">
                        <form class="form-horizontal">
                            <fieldset>
                                <div id="div_plugin_panel"></div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</section>
<?php include_file("desktop", "plugin", "js");?>
