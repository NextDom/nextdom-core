<?php

/* -------------------------------------------------------------------- */
/* Copyright (C) 2018 - 2019 - NextDom - www.nextdom.org                */
/* This file is part of nextdom.                                        */
/*                                                                      */
/* nextdom is free software: you can redistribute it and/or modify      */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation, either version 3 of the License, or    */
/* (at your option) any later version.                                  */
/*                                                                      */
/* nextdom is distributed in the hope that it will be useful,           */
/* but WITHOUT ANY WARRANTY; without even the implied warranty of       */
/* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        */
/* GNU General Public License for more details.                         */
/*                                                                      */
/* You should have received a copy of the GNU General Public License    */
/* along with nextdom.  If not, see <http://www.gnu.org/licenses/>.     */
/* -------------------------------------------------------------------- */

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

$plugin = plugin::byId('plugin4tests');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());

include_file('desktop', 'plugin4tests', 'css', 'plugin4tests');
?>
    <!-- Sidebar navigator -->
    <div class="row row-overflow">
        <div class="col-lg-2 col-md-3 col-sm-4">
            <div class="bs-sidebar">
                <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                    <a class="btn btn-default eqLogicAction" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter un équipement}}</a>
                    <li class="filter"><input class="filter form-control input-sm" placeholder="{{Rechercher}}"/></li>
                    <?php
                    /** @var eqLogic $eqLogic */
                    foreach ($eqLogics as $eqLogic) {
                        $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
                        echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"  style="' . $opacity . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
        <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay">
            <!-- Actions list -->
            <legend><i class="fa fa-cog"></i> {{Gestion}}</legend>
            <div class="eqLogicThumbnailContainer">
                <div id="add-eqlogic-btn" class="cursor eqLogicAction" data-action="add">
                    <i class="fa fa-plus-circle"></i>
                    <div>{{Ajouter}}</div>
                </div>
                <div id="config-btn" class="cursor eqLogicAction" data-action="gotoPluginConf">
                    <i class="fa fa-wrench"></i>
                    <div>{{Configuration}}</div>
                </div>
            </div>
            <!-- EqLogics list -->
            <legend><i class="fa fa-table"></i> {{Mes équipements}}</legend>
            <div class="eqLogicThumbnailContainer">
                <?php
                foreach ($eqLogics as $eqLogic) {
                    $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
                    echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="' . $opacity . '" >';
                    if ($eqLogic->getConfiguration('icon') !== '') {
                        echo $eqLogic->getConfiguration('icon');
                    } else {
                        echo '<i class="icon fa fa-times"></i>';
                    }
                    echo '<div>' . $eqLogic->getHumanName(true, true) . '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
        <!-- EqLogic edit -->
        <div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="display: none">
            <a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
            <a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
            <a class="btn btn-default eqLogicAction pull-right" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a>
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation"><a class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
                <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
                <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
            </ul>
            <!-- EqLogic form -->
            <div class="tab-content eqLogic-form">
                <div role="tabpanel" class="tab-pane active" id="eqlogictab">
                    <br/>
                    <div class="row">
                        <div class="col-sm-7">
                            <form class="form-horizontal">
                                <fieldset>
                                    <div class="form-group">
                                        <label class="col-lg-3 col-md-3 col-sm-4 control-label">{{Nom}}</label>
                                        <div class="col-lg-9 col-md-9 col-sm-8 col-xs-12">
                                            <input type="text" class="eqLogicAttr form-control" data-l1key="id"/>
                                            <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom}}"/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 col-md-3 col-sm-4 control-label"></label>
                                        <div class="col-lg-9 col-md-9 col-sm-8 col-xs-12">
                                            <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked="checked"/>{{Activer}}</label>
                                            <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked="checked"/>{{Visible}}</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 col-md-3 col-sm-4 control-label" for="sel_object">{{Objet parent}}</label>
                                        <div class="col-lg-9 col-md-9 col-sm-8 col-xs-12">
                                            <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                                                <option value="">{{Aucun}}</option>
                                                <?php
                                                foreach (object::all() as $object) {
                                                    echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 col-md-3 col-sm-4 col-xs-12 control-label">{{Icône}}</label>
                                        <div class="col-lg-8 col-md-8 col-sm-7 col-xs-12">
                                            <div class="input-group">
                                                <a class="btn btn-action" id="choose-icon"><span class="eqLogicAttr configuration" data-l1key="configuration" data-l2key="icon"></span> <span>{{Choisir}}</span></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 col-md-3 col-sm-4 control-label" for="configuration-ip">{{Adresse IP}}</label>
                                        <div class="col-lg-9 col-md-9 col-sm-8 col-xs-12">
                                            <input type="text" id="configuration-ip" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="ip"/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 col-md-3 col-sm-4 control-label" for="configuration-port">{{Port}}</label>
                                        <div class="col-lg-9 col-md-9 col-sm-8 col-xs-12">
                                            <input type="text" id="configuration-port" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="port"/>
                                        </div>
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Command form -->
                <div role="tabpanel" class="tab-pane" id="commandtab">
                    <a class="btn btn-success cmdAction pull-right"><i class="fa fa-plus-circle"></i> {{Ajouter une commande}}</a><br/><br/>
                    <table id="table_cmd" class="table table-bordered table-condensed">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{Nom}}</th>
                            <th>{{Afficher}}</th>
                            <th>{{Actions}}</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php
include_file('desktop', 'plugin4tests', 'js', 'plugin4tests');
include_file('core', 'plugin.template', 'js');