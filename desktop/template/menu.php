<?php

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
*/

use NextDom\Helpers\PrepareView;
use NextDom\Helpers\Status;
use NextDom\Managers\ObjectManager;
use NextDom\Managers\UpdateManager;

global $homeLink;

$pluginMenu = PrepareView::getPluginMenu();
$panelMenu = PrepareView::getPanelMenu();
$nbMessage = message::nbMessage();
$displayMessage = '';
if ($nbMessage == 0) {
    $displayMessage = 'display : none;';
}
$nbUpdate = UpdateManager::nbNeedUpdate();
$displayUpdate = '';
if ($nbUpdate == 0) {
    $displayUpdate = 'display : none;';
}

?>
<header id="nextdom-header" class="navbar navbar-fixed-top navbar-default reportModeHidden">
    <div class="container-fluid">
        <div class="navbar-header">
            <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">{{Toggle navigation}}</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <nav class="navbar-collapse collapse text-center">
            <div class="collapse navbar-collapse navbar-left col-lg-4 col-md-4">
                <ul class="nav navbar-nav">
                    <li class="dropdown">
                        <a data-toggle="dropdown" href="#"><i class="fa fa-home fa-3x"></i>
                            <span class="hidden-xs hidden-sm hidden-md">{{Accueil}}&nbsp;</span><span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="dropdown-submenu">
                                <a data-toggle="dropdown" id="bt_gotoDashboard" href="index.php?v=d&p=dashboard"><i class="fa fa-dashboard"></i> {{Dashboard}}</a>
                                <ul id="dashboard-submenu" class="dropdown-menu scrollable-menu" role="menu">
                                    <?php
                                    foreach (ObjectManager::buildTree(null, false) as $objectLi) {
                                        echo '<li><a href="index.php?v=d&p=dashboard&object_id=' . $objectLi->getId() . '">' . $objectLi->getHumanName(true) . '</a></li>';
                                    }
                                    ?>
                                </ul>
                            </li>
                            <li class="dropdown-submenu">
                                <a data-toggle="dropdown" id="bt_gotoView"><i class="fa fa-picture-o"></i> {{Vue}}</a>
                                <ul class="dropdown-menu">
                                    <?php
                                    foreach (view::all() as $viewMenu) {
                                        echo '<li><a href="index.php?v=d&p=view&view_id=' . $viewMenu->getId() . '">' . trim($viewMenu->getDisplay('icon')) . ' ' . $viewMenu->getName() . '</a></li>';
                                    }
                                    ?>
                                </ul>
                            </li>
                            <li class="dropdown-submenu">
                                <a data-toggle="dropdown" id="bt_gotoPlan"><i class="fa fa-paint-brush"></i> {{Design}}</a>
                                <ul class="dropdown-menu">
                                    <?php
                                    foreach (planHeader::all() as $planMenu) {
                                        echo '<li><a href="index.php?v=d&p=plan&plan_id=' . $planMenu->getId() . '">' . trim($planMenu->getConfiguration('icon') . ' ' . $planMenu->getName()) . '</a></li>';
                                    }
                                    ?>
                                </ul>
                            </li>
                            <?php
                            echo $panelMenu;
                            ?>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a data-toggle="dropdown" href="#"><i class="fa fa-stethoscope fa-3x"></i>
                            <span class="hidden-xs hidden-sm hidden-md">{{Analyse}}&nbsp;</span><span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="index.php?v=d&p=history"><i class="fa fa-bar-chart-o"></i> {{Historique}}</a></li>
                            <?php
                            if (Status::isConnectAdmin()) {
                                ?>
                                <li><a href="index.php?v=d&p=report"><i class="fa fa-newspaper-o"></i> {{Rapport}}</a></li>
                                <?php
                            }
                            ?>
                            <li class="divider"></li>
                            <li><a href="#" id="bt_showEventInRealTime"><i class="fa fa-tachometer"></i> {{Temps réel}}</a></li>
                            <?php
                            if (Status::isConnectAdmin()) {
                                ?>
                                <li><a href="index.php?v=d&p=log"><i class="fa fa-file-o"></i> {{Logs}}</a></li>
                                <li><a href="index.php?v=d&p=eqAnalyse"><i class="fa fa-battery-full"></i> {{Equipements}}</a></li>
                                <li class="divider"></li>
                                <li><a href="index.php?v=d&p=health"><i class="fa fa-medkit"></i> {{Santé}}</a></li>
                                <?php
                            }
                            ?>
                        </ul>
                    </li>
                    <?php
                    if (Status::isConnectAdmin()) {
                        ?>
                        <li class="dropdown">
                            <a data-toggle="dropdown" href="#"><i class="fa fa-wrench fa-3x"></i>
                                <span class="hidden-xs hidden-sm hidden-md">{{Outils}}&nbsp;</span><span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="index.php?v=d&p=object"><i class="fa fa-picture-o"></i> {{Objets}}</a></li>
                                <li><a href="index.php?v=d&p=interact"><i class="fa fa-comments-o"></i> {{Interactions}}</a></li>
                                <li><a href="index.php?v=d&p=display"><i class="fa fa-th"></i> {{Résumé domotique}}</a></li>
                                <li><a href="index.php?v=d&p=scenario"><i class="fa fa-cogs"></i> {{Scénarios}}</a></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a data-toggle="dropdown" href="#"><i class="fa fa-puzzle-piece fa-3x"></i>
                                <span class="hidden-xs hidden-sm hidden-md">{{Plugins}}&nbsp;</span><span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="index.php?v=d&p=plugin"><i class="fa fa-tags"></i> {{Gestion des plugins}}</a></li>
                                <li role="separator" class="divider"></li>
                                <?php echo $pluginMenu; ?>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a data-toggle="dropdown" href="#"><i class="fa fa-cogs fa-3x"></i>
                                <span class="hidden-xs hidden-sm hidden-md">{{Réglages}}&nbsp;</span><span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="index.php?v=d&p=administration" tabindex="0"><i class="fa fa-wrench"></i> {{Configuration}}</a></li>
                                <li><a href="index.php?v=d&p=backup"><i class="fa fa-floppy-o"></i> {{Sauvegardes}}</a></li>
                                <li><a href="index.php?v=d&p=migration"><i class="fa fa-floppy-o"></i> {{Migration depuis jeedom}}</a></li>
                                <li><a href="index.php?v=d&p=update"><i class="fa fa-refresh"></i> {{Centre de mise à jour}}</a></li>
                                <li><a href="index.php?v=d&p=cron"><i class="fa fa-tasks"></i> {{Moteur de tâches}}</a></li>
                                <li><a href="index.php?v=d&p=custom"><i class="fa fa-pencil-square-o"></i> {{Personnalisation avancée}}</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="index.php?v=d&p=user"><i class="fa fa-users"></i> {{Utilisateurs}}</a></li>
                                <li><a href="index.php?v=d&p=profils"><i class="fa fa-briefcase"></i> {{Profil}} <?php echo $_SESSION['user']->getLogin(); ?></a></li>
                                <li><a href="index.php?v=m" class="noOnePageLoad"><i class="fa fa-mobile"></i> {{Version mobile}}</a></li>
                                <li><a href="#" id="bt_nextdomAbout"><i class="fa fa-info-circle"></i> {{Version}}<?php echo nextdom::version(); ?></a></li>
                                <li><a href="index.php?v=d&logout=1" class="noOnePageLoad"><i class="fa fa-sign-out"></i> {{Se déconnecter}}</a></li>
                                <li><a id="bt_getHelpPage" class="cursor" data-plugin="<?php echo init('m'); ?>" data-page="<?php echo init('p'); ?>" title="{{Aide sur la page en cours}}"><i class="fa fa-question-circle"></i> {{Aide}}</a></li>
                                <li>
                                    <?php if (isset($plugin) && is_object($plugin) && $plugin->getIssue() != '') {
                                        ?>
                                        <a target="_blank" href="<?php echo $plugin->getIssue() ?>" title="{{Envoyer un rapport de bug}}"><i class="fa fa-exclamation-circle"></i> {{Rapport de bug}}</a>
                                    <?php } else { ?>
                                        <a class="bt_reportBug cursor" title="{{Envoyer un rapport de bug}}"><i class="fa fa-exclamation-circle"></i> {{Rapport de bug}}</a>
                                    <?php } ?>
                                </li>
                                <?php
                                if (nextdom::isCapable('sudo')) {
                                    echo '<li class="divider"></li>';
                                    echo '<li class="cursor"><a id="bt_rebootSystem" state="0"><i class="fa fa-repeat"></i> {{Redémarrer}}</a></li>';
                                    echo '<li class="cursor"><a id="bt_haltSystem" state="0"><i class="fa fa-power-off"></i> {{Eteindre}}</a></li>';
                                }
                                ?>
                            </ul>
                        </li>
                        <?php
                    }
                    ?>
                </ul>

            </div>
            <div class="collapse navbar-collapse col-lg-4 col-md-4 col-lg-offset-2 col-md-offset-2 text-center">
                <ul class="nav navbar-nav">
                    <a class="navbar-brand" href="<?php echo $homeLink; ?>">
                        <img src="<?php echo config::byKey('product_image') ?>" /></a><br/>
                    <i class="fa fa-bars burger" data-toggle="collapse" data-target="#collapse_categorie"></i>
                </ul>
            </div>
            <div class="collapse navbar-collapse navbar-right col-lg-4 col-md-4">
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <a href="#" class="default-cursor">
                            <span id="horloge"><?php setlocale(LC_TIME, 'fra_fra');
                                echo strftime('%A %d %B %Y, %H:%M:%S'); ?></span>
                        </a>

                        <a href="#" class="default-cursor">
                            <?php echo ObjectManager::getGlobalHtmlSummary(); ?>
                        </a>

                    </li>
                    <li>
                        <a href="#" id="bt_messageModal"><span class="badge" id="span_nbMessage" title="{{Nombre de messages}}" style="<?php echo $displayMessage; ?>"><?php echo $nbMessage; ?></span></a>
                        <a href="index.php?v=d&p=update"><span class="badge" id="span_nbUpdate" title="{{Nombre de mises à jour}}" style="<?php echo $displayUpdate; ?>"><?php echo $nbUpdate; ?></span></a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</header>
