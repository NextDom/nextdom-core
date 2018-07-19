<?php

// TODO: bt_showScenarioSummary et le suivant en classe alors que tous les autres ont des identifiants

use NextDom\Helpers\Status;
use NextDom\Managers\ScenarioManager;
use NextDom\Managers\ObjectManager;

/**
 * Show HTML button for scenario
 *
 * @param $scenario
 */
function showScenarioIcon($scenario)
{
    $opacity = false;
    if (!$scenario->getIsActive()) {
        $opacity = nextdom::getConfiguration('eqLogic:style:noactive');
    }
    ?>
    <div class="scenarioDisplayCard iconlist" data-scenario_id="<?php echo $scenario->getId(); ?>"
        <?php if ($opacity) echo ' style="<?php echo $opacity; ?>"'; ?>>
        <img src="core/img/scenario.png"/>
        <span class="name"><?php echo $scenario->getHumanName(true, true, true, true); ?></span>
    </div>
    <?php
}

Status::initConnectState();
Status::isConnectedAdminOrFail();

$scenarios = array();
$totalScenario = 0;
//TODO : -1 ????
$scenarios[-1] = ScenarioManager::all(null);
$noGroupScenarioCount = count($scenarios[-1]);
$totalScenario += $noGroupScenarioCount;
$scenarioListGroup = ScenarioManager::listGroup();
if (is_array($scenarioListGroup)) {
    foreach ($scenarioListGroup as $scenarioGroup) {
        $scenarios[$scenarioGroup['group']] = ScenarioManager::all($scenarioGroup['group']);
        $totalScenario .= count($scenarios[$scenarioGroup['group']]);
    }
}
?>
<style>
    .expressions .sortable-placeholder {
        background-color: #33B8CC;
    }

    #bt_displayScenarioList {
        position: fixed;
        height: 100%;
        width: 15px;
        top: 50px;
        left: 0px;
        z-index: 998;
        background-color: #f6f6f6;
    }

    #bt_displayScenarioList i {
        color: #b6b6b6;
    }

    #div_listScenario {
        z-index: 999;
    }

    #bt_addScenario {
        width: 100%;
        margin-top: 5px;
        margin-bottom: 5px;
    }

    #scenarioCommands {
        border-left: solid 1px #EEE;
        padding-left: 25px;
    }

    #scenarioCommands .iconlist i {
        display: block;
        font-size: 6em;
    }

    #scenarioCommands .iconlist span,
    .scenarioDisplayCard > span {
        font-size: 1.1em;
        position: relative;
        top: 15px;
        word-break: break-all;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    #bt_addScenario2 i,
    #bt_addScenario2 span {
        color: #33B8CC
    }

    #bt_changeAllScenarioState2[data-state="1"] i,
    #bt_changeAllScenarioState2[data-state="1"] span {
        color: #5cb85c;
    }

    #bt_changeAllScenarioState2[data-state="0"] i,
    #bt_changeAllScenarioState2[data-state="0"] span {
        color: #d9534f;
    }

    #bt_displayScenarioVariable2 i,
    #bt_displayScenarioVariable2 span,
    .bt_showScenarioSummary i,
    .bt_showScenarioSummary span,
    .bt_showExpressionTest i,
    .bt_showExpressionTest span {
        color: #337ab7;
    }

    #no-scenario-msg {
        width: 100%;
        text-align: center;
        margin-top: 4em;
    }

    #no-scenario-msg span {
        color: #767676;
        font-size: 1.2em;
        font-weight: bold;
    }

    .scenarioDisplayCard {
        text-align: center;
    }

    .scenarioDisplayCard img {
        height: 90px;
        width: 85px;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }

    #div_scenarioElement {
        margin-top: 2em;
        padding-bottom: 70px;
    }

    .tab-content {
        height: calc(100% - 50px);
        overflow: auto;
        overflow-x: hidden;
    }

    #span_ongoing {
        font-size: 1em;
        position: relative;
        top: 5px;
    }

    #in_searchScenario {
        margin-bottom: 4px;
    }

    .panel-title a {
        text-decoration: none;
    }
</style>
<div class="div_smallSideBar" id="bt_displayScenarioList"><i class="fa fa-arrow-circle-o-right"></i></div>

<div class="row row-overflow">
    <div class="col-xs-2" id="div_listScenario">
        <div class="bs-sidebar nav nav-list bs-sidenav">
            <a class="btn btn-default" id="bt_addScenario"><i class="fa fa-plus-circle cursor"></i> {{Nouveau scénario}}</a>
            <input id='in_treeSearch' class='form-control' placeholder="{{Rechercher}}"/>
            <div id="div_tree">
                <ul id="ul_scenario">
                    <?php if ($noGroupScenarioCount > 0) { ?>
                    <li data-jstree='{"opened":true}'>
                        <a>Aucun - <?php echo $noGroupScenarioCount; ?> scénario(s)</a>
                        <ul>
                            <?php foreach ($scenarios[-1] as $scenario) { ?>
                                <li data-jstree='{"opened":true,"icon":"<?php echo $scenario->getIcon(true); ?>"}'>
                                    <a class="li_scenario" id="scenario<?php echo $scenario->getId(); ?>" data-scenario_id="<?php echo $scenario->getId(); ?>"><?php echo $scenario->getHumanName(false, true); ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                        <?php
                        }
                        foreach ($scenarioListGroup

                        as $scenarioGroup) {
                        if ($scenarioGroup['group'] != '') { ?>
                    <li data-jstree='{"opened":true}'>
                        <a><?php echo $scenarioGroup['group']; ?> - <?php echo count($scenarios[$scenarioGroup['group']]); ?> scénario(s)</a>
                        <ul>
                            <?php foreach ($scenarios[$scenarioGroup['group']] as $scenario) { ?>
                                <li data-jstree='{"opened":true,"icon":"<?php echo $scenario->getIcon(true); ?>"}'>
                                    <a class="li_scenario" id="scenario<?php echo $scenario->getId(); ?>" data-scenario_id="<?php echo $scenario->getId(); ?>"><?php echo $scenario->getHumanName(false, true); ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php
                }
                }
                ?>
                </ul>
            </div>
        </div>
    </div>
    <div id="scenarioThumbnailDisplay" class="col-xs-10">
        <div id="scenarioCommands" class="scenarioListContainer">
            <legend><i class="fa fa-cog"></i> {{Gestion}}</legend>
            <div class="iconlist text-center" id="bt_addScenario2">
                <i class="fa fa-plus-circle"></i>
                <span>{{Ajouter}}</span>
            </div>
            <?php if (config::byKey('enableScenario') == 0) { ?>
                <div class="iconlist text-center" id="bt_changeAllScenarioState2" data-state="1">
                    <i class="fa fa-check"></i>
                    <span>{{Activer scénarios}}</span>
                </div>
            <?php } else { ?>
                <div class="iconlist text-center" id="bt_changeAllScenarioState2" data-state="0">
                    <i class="fa fa-times"></i>
                    <span>{{Désactiver scénarios}}</span>
                </div>
            <?php }
            ?>
            <div class="iconlist text-center" id="bt_displayScenarioVariable2">
                <i class="fa fa-eye"></i>
                <span>{{Voir variables}}</span>
            </div>

            <div class="iconlist bt_showScenarioSummary text-center">
                <i class="fa fa-list"></i>
                <span>{{Vue d'ensemble}}</span>
            </div>

            <div class="iconlist bt_showExpressionTest text-center">
                <i class="fa fa-check"></i>
                <span>{{Testeur d'expression}}</span>
            </div>
        </div>

        <legend><i class="icon nextdom-clap_cinema"></i> {{Mes scénarios}}</legend>
        <?php
        if ($totalScenario == 0) { ?>
            <div id="no-scenario-msg"><span>{{Vous n'avez encore aucun scénario. Cliquez sur ajouter pour commencer}}</span></div>
            <?php
        } else {
        ?>
        <input class="form-control" placeholder="{{Rechercher}}" id="in_searchScenario"/>
        <div class="panel-group" id="accordionScenar">
            <?php
            if (count($scenarios[-1]) > 0) {
            ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="" href="#config_none">Aucun - <?php echo count($scenarios[-1]); ?> scénario(s)</a>
                    </h3>
                </div>
                <div id="config_none" class="panel-collapse collapse text-center">
                    <div class="panel-body">
                        <div class="scenarioListContainer">
                            <?php
                            foreach ($scenarios[-1] as $scenario) {
                                showScenarioIcon($scenario);
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }
    $i = 0;
    foreach ($scenarioListGroup as $scenarioGroup) {
        if ($scenarioGroup['group'] != '') {
            ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <a class="accordion-toggle" data-toggle="collapse" data-parent="" href="#config_<?php echo $i; ?>"><?php echo $scenarioGroup['group']; ?> - <?php echo count($scenarios[$scenarioGroup['group']]); ?> scénario(s)</a>
                    </h3>
                </div>
                <div id="config_<?php echo $i; ?>" class="panel-collapse collapse">
                    <div class="panel-body">
                        <div class="scenarioListContainer">
                            <?php
                            foreach ($scenarios[$scenarioGroup['group']] as $scenario) {
                                showScenarioIcon($scenario);
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            ++$i;
        }
    } ?>
    </div>
    <?php
    }
    ?>
</div>

<div id="div_editScenario" class="col-xs-10" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
    <a class="btn btn-default btn-sm pull-right" id="bt_graphScenario"><i class="fa fa-object-group"></i> {{Liens}}</a>
    <a class="btn btn-default btn-sm pull-right" id="bt_copyScenario"><i class="fa fa-copy"></i> {{Dupliquer}}</a>
    <a class="btn btn-default btn-sm pull-right" id="bt_logScenario"><i class="fa fa-file-text-o"></i> {{Log}}</a>
    <a class="btn btn-default btn-sm pull-right" id="bt_exportScenario"><i class="fa fa fa-share"></i> {{Exporter}}</a>
    <a class="btn btn-danger btn-sm pull-right" id="bt_stopScenario"><i class="fa fa-stop"></i> {{Arrêter}}</a>
    <a class="btn btn-default btn-sm pull-right" id="bt_templateScenario"><i class="fa fa-cubes"></i> {{Template}}</a>
    <a class="btn btn-success btn-sm pull-right" id="bt_saveScenario2"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
    <a class="btn btn-danger btn-sm pull-right" id="bt_delScenario2"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
    <a class="btn btn-warning btn-sm pull-right" id="bt_testScenario2" title='{{Veuillez sauvegarder avant de tester. Ceci peut ne pas aboutir.}}'><i class="fa fa-gamepad"></i> {{Exécuter}}</a>
    <a class="btn btn-primary btn-sm pull-right bt_showExpressionTest"><i class="fa fa-check"></i> {{Expression}}</a>
    <a class="btn btn-primary btn-sm pull-right" id="bt_displayScenarioVariable"><i class="fa fa-eye"></i> {{Variables}}</a>
    <span id="span_ongoing" class="label pull-right"></span>

    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation"><a class="cursor" aria-controls="home" role="tab" id="bt_scenarioThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
        <li role="presentation" class="active"><a href="#generaltab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Général}} (ID : <span class="scenarioAttr" data-l1key="id"></span>)</a></li>
        <li role="presentation"><a id="bt_scenarioTab" href="#scenariotab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-filter"></i> {{Scénario}}</a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="generaltab">
            <br/>
            <div class="row">
                <div class="col-sm-6">
                    <form class="form-horizontal">
                        <fieldset>
                            <div class="form-group">
                                <label class="col-xs-5 control-label">{{Nom du scénario}}</label>
                                <div class="col-xs-6">
                                    <input class="form-control scenarioAttr" data-l1key="name" type="text" placeholder="{{Nom du scénario}}"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-xs-5 control-label">{{Nom à afficher}}</label>
                                <div class="col-xs-6">
                                    <input class="form-control scenarioAttr" title="{{Ne rien mettre pour laisser le nom par défaut}}" data-l1key="display" data-l2key="name" type="text" placeholder="{{Nom à afficher}}"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-xs-5 control-label">{{Groupe}}</label>
                                <div class="col-xs-6">
                                    <input class="form-control scenarioAttr" data-l1key="group" type="text" placeholder="{{Groupe du scénario}}"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-xs-5 control-label"></label>
                                <label>
                                    {{Actif}} <input type="checkbox" class="scenarioAttr" data-l1key="isActive">
                                </label>
                                <label>
                                    {{Visible}} <input type="checkbox" class="scenarioAttr" data-l1key="isVisible">
                                </label>
                            </div>
                            <div class="form-group">
                                <label class="col-xs-5 control-label">{{Objet parent}}</label>
                                <div class="col-xs-6">
                                    <select class="scenarioAttr form-control" data-l1key="object_id">
                                        <option value="">{{Aucun}}</option>
                                        <?php
                                        foreach (ObjectManager::all() as $object) {
                                            echo '<option value="<?php echo $object->getId(); ?>"><?php echo $object->getName(); ?></option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-xs-5 control-label">{{Timeout en secondes (0 = illimité)}}</label>
                                <div class="col-xs-6">
                                    <input class="form-control scenarioAttr" data-l1key="timeout">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-xs-5 control-label">{{Multi lancement}}</label>
                                <div class="col-xs-1">
                                    <input type="checkbox" class="scenarioAttr" data-l1key="configuration" data-l2key="allowMultiInstance" title="{{Le scénario pourra tourner plusieurs fois en même temps}}">
                                </div>
                                <label class="col-xs-4 control-label">{{Mode synchrone}}</label>
                                <div class="col-xs-1">
                                    <input type="checkbox" class="scenarioAttr" data-l1key="configuration" data-l2key="syncmode" title="{{Le scénario est en mode synchrone. Attention, cela peut rendre le système instable}}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-xs-5 control-label">{{Log}}</label>
                                <div class="col-xs-6">
                                    <select class="scenarioAttr form-control" data-l1key="configuration" data-l2key="logmode">
                                        <option value="default">{{Défaut}}</option>
                                        <option value="none">{{Aucun}}</option>
                                        <option value="realtime">{{Temps réel}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-xs-5 control-label">{{Suivre dans la timeline}}</label>
                                <div class="col-xs-1">
                                    <input type="checkbox" class="scenarioAttr" data-l1key="configuration" data-l2key="timeline::enable" title="{{Les exécutions du scénario pourront être vues dans la timeline.}}">
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
                <div class="col-sm-6">
                    <form class="form-horizontal">
                        <div class="form-group">
                            <div class="col-md-12">
                                <textarea class="form-control scenarioAttr ta_autosize" data-l1key="description" placeholder="Description"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 col-xs-6 control-label">{{Mode du scénario}}</label>
                            <div class="col-sm-9 col-xs-6">
                                <div class="input-group">
                                    <select class="form-control scenarioAttr input-sm" data-l1key="mode">
                                        <option value="provoke">{{Provoqué}}</option>
                                        <option value="schedule">{{Programmé}}</option>
                                        <option value="all">{{Les deux}}</option>
                                    </select>
                                    <span class="input-group-btn">
            <a class="btn btn-default btn-sm" id="bt_addTrigger"><i class="fa fa-plus-square"></i> {{Déclencheur}}</a>
            <a class="btn btn-default btn-sm" id="bt_addSchedule"><i class="fa fa-plus-square"></i> {{Programmation}}</a>
          </span>
                                </div>
                            </div>
                        </div>
                        <div class="scheduleDisplay" style="display: none;">
                            <div class="form-group">
                                <label class="col-xs-3 control-label">{{Précédent}}</label>
                                <div class="col-xs-3"><span class="scenarioAttr label label-primary" data-l1key="forecast" data-l2key="prevDate" data-l3key="date"></span></div>
                                <label class="col-xs-3 control-label">{{Prochain}}</label>
                                <div class="col-xs-3"><span class="scenarioAttr label label-success" data-l1key="forecast" data-l2key="nextDate" data-l3key="date"></span></div>
                            </div>
                            <div class="scheduleMode"></div>
                        </div>
                        <div class="provokeMode provokeDisplay" style="display: none;">

                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="scenariotab">
            <div id="div_scenarioElement" class="element"></div>
        </div>
    </div>
</div>


<div class="modal fade" id="md_copyScenario">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button class="close" data-dismiss="modal">×</button>
                <h3>{{Dupliquer le scénario}}</h3>
            </div>
            <div class="modal-body">
                <div style="display: none;" id="div_copyScenarioAlert"></div>
                <center>
                    <input class="form-control" type="text" id="in_copyScenarioName" size="16" placeholder="{{Nom du scénario}}"/><br/><br/>
                </center>
            </div>
            <div class="modal-footer">
                <a class="btn btn-danger" data-dismiss="modal"><i class="fa fa-minus-circle"></i> {{Annuler}}</a>
                <a class="btn btn-success" id="bt_copyScenarioSave"><i class="fa fa-check-circle"></i> {{Enregistrer}}</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="md_addElement">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button class="close" data-dismiss="modal">×</button>
                <h3>{{Ajouter élément}}</h3>
            </div>
            <div class="modal-body">
                <center>
                    <select id="in_addElementType" class="form-control">
                        <option value="if">{{Si/Alors/Sinon}}</option>
                        <option value="action">{{Action}}</option>
                        <option value="for">{{Boucle}}</option>
                        <option value="in">{{Dans}}</option>
                        <option value="at">{{A}}</option>
                        <option value="code">{{Code}}</option>
                        <option value="comment">{{Commentaire}}</option>
                    </select>
                </center>
                <br/>
                <div class="alert alert-info addElementTypeDescription if">
                    Permet de faire des conditions dans votre scénario. Par exemple : Si mon détecteur d’ouverture de porte se déclenche Alors allumer la lumière.
                </div>

                <div class="alert alert-info addElementTypeDescription action" style="display:none;">
                    Permet de lancer une action, sur un de vos modules, scénarios ou autre. Par exemple : Passer votre sirène sur ON.
                </div>

                <div class="alert alert-info addElementTypeDescription for" style="display:none;">
                    Une boucle permet de réaliser une action de façon répétée un certain nombre de fois. Par exemple : Permet de répéter une action de 1 à X, c’est-à-dire X fois.
                </div>

                <div class="alert alert-info addElementTypeDescription in" style="display:none;">
                    Permet de faire une action dans X min. Par exemple : Dans 5 min, éteindre la lumière.
                </div>

                <div class="alert alert-info addElementTypeDescription at" style="display:none;">
                    A un temps précis, cet élément permet de lancer une action. Par exemple : A 9h30, ouvrir les volets.
                </div>

                <div class="alert alert-info addElementTypeDescription code" style="display:none;">
                    Cet élément permet de rajouter dans votre scénario de la programmation à l’aide d’un code, PHP/Shell, etc.
                </div>

                <div class="alert alert-info addElementTypeDescription comment" style="display:none;">
                    Permet de commenter votre scénario.
                </div>

            </div>
            <div class="modal-footer">
                <a class="btn btn-danger" data-dismiss="modal"><i class="fa fa-minus-circle"></i> {{Annuler}}</a>
                <a class="btn btn-success" id="bt_addElementSave"><i class="fa fa-check-circle"></i> {{Enregistrer}}</a>
            </div>
        </div>
    </div>
</div>

<?php
include_file('desktop', 'scenario', 'js');
include_file('3rdparty', 'jquery.sew/jquery.caretposition', 'js');
include_file('3rdparty', 'jquery.sew/jquery.sew.min', 'js');
?>
