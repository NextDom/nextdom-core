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

global $homeLink;

$pluginMenu = PrepareView::getPluginMenu();
$panelMenu = PrepareView::getPanelMenu();
?>
<header id="nextdom-header" class="navbar navbar-fixed-top navbar-default reportModeHidden">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="<?php echo $homeLink; ?>">
                <img src="<?php echo config::byKey('product_image') ?>" height="30"/>
            </a>
            <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">{{Toggle navigation}}</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <nav class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li class="dropdown cursor">
                    <a class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-home"></i> <span
                            class="hidden-xs hidden-sm hidden-md">{{Accueil}}</span> <b
                            class="caret"></b></a>
                    <ul class="dropdown-menu">

                        <li class="dropdown-submenu">
                            <a data-toggle="dropdown" id="bt_gotoDashboard" href="index.php?v=d&p=dashboard"><i
                                    class="fa fa-dashboard"></i> {{Dashboard}}</a>
                            <ul id="dashboard-submenu" class="dropdown-menu scrollable-menu" role="menu">
                                <?php
                                foreach (object::buildTree(null, false) as $object_li) {
                                    echo '<li><a href="index.php?v=d&p=dashboard&object_id=' . $object_li->getId() . '">' . $object_li->getHumanName(true) . '</a></li>';
                                }
                                ?>
                            </ul>
                        </li>
                        <li class="dropdown-submenu">
                            <a data-toggle="dropdown" id="bt_gotoView"><i class="fa fa-picture-o"></i>
                                {{Vue}}</a>
                            <ul class="dropdown-menu">
                                <?php
                                foreach (view::all() as $view_menu) {
                                    echo '<li><a href="index.php?v=d&p=view&view_id=' . $view_menu->getId() . '">' . trim($view_menu->getDisplay('icon')) . ' ' . $view_menu->getName() . '</a></li>';
                                }
                                ?>
                            </ul>
                        </li>
                        <li class="dropdown-submenu">
                            <a data-toggle="dropdown" id="bt_gotoPlan"><i class="fa fa-paint-brush"></i>
                                {{Design}}</a>
                            <ul class="dropdown-menu">
                                <?php
                                foreach (planHeader::all() as $plan_menu) {
                                    echo '<li><a href="index.php?v=d&p=plan&plan_id=' . $plan_menu->getId() . '">' . trim($plan_menu->getConfiguration('icon') . ' ' . $plan_menu->getName()) . '</a></li>';
                                }
                                ?>
                            </ul>
                        </li>
                        <?php
                        echo $panelMenu;
                        ?>
                    </ul>
                </li>
                <li class="dropdown cursor">
                    <a data-toggle="dropdown"><i class="fa fa-stethoscope"></i> <span
                            class="hidden-xs hidden-sm hidden-md">{{Analyse}}</span> <b
                            class="caret"></b></a>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="index.php?v=d&p=history"><i class="fa fa-bar-chart-o"></i>
                                {{Historique}}</a></li>
                        <?php
                        if (Status::isConnectAdmin()) {
                            ?>
                            <li><a href="index.php?v=d&p=report"><i class="fa fa-newspaper-o"></i>
                                    {{Rapport}}</a></li>
                            <?php
                        }
                        ?>
                        <li class="divider"></li>
                        <li><a href="#" id="bt_showEventInRealTime"><i class="fa fa-tachometer"></i> {{Temps
                                réel}}</a></li>
                        <?php
                        if (Status::isConnectAdmin()) {
                            ?>
                            <li><a href="index.php?v=d&p=log"><i class="fa fa-file-o"></i> {{Logs}}</a></li>
                            <li><a href="index.php?v=d&p=eqAnalyse"><i class="fa fa-battery-full"></i>
                                    {{Equipements}}</a></li>
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
                    <li class="dropdown cursor">
                        <a data-toggle="dropdown"><i class="fa fa-wrench"></i> <span
                                class="hidden-xs hidden-sm hidden-md">{{Outils}}</span> <b
                                class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="index.php?v=d&p=object"><i class="fa fa-picture-o"></i> {{Objets}}</a>
                            </li>
                            <li><a href="index.php?v=d&p=interact"><i class="fa fa-comments-o"></i>
                                    {{Interactions}}</a></li>
                            <li><a href="index.php?v=d&p=display"><i class="fa fa-th"></i> {{Résumé domotique}}</a>
                            </li>
                            <li><a href="index.php?v=d&p=scenario"><i class="fa fa-cogs"></i> {{Scénarios}}</a>
                            </li>
                        </ul>
                    </li>
                    <li class="dropdown cursor">
                        <a data-toggle="dropdown"><i class="fa fa-tasks"></i> <span
                                class="hidden-xs hidden-sm hidden-md">{{Plugins}}</span> <b
                                class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="index.php?v=d&p=plugin"><i class="fa fa-tags"></i> {{Gestion des
                                    plugins}}</a></li>
                            <li role="separator" class="divider"></li>
                            <?php
                            echo $pluginMenu;
                            ?>
                        </ul>
                    </li>
                    <?php
                }
                ?>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <a href="#" class="default-cursor">
                        <?php
                        echo object::getGlobalHtmlSummary();
                        ?>
                    </a>
                </li>
                <?php
                $nbMessage = message::nbMessage();
                $displayMessage = ($nbMessage > 0) ? '' : 'display : none;'; ?>
                <li>
                    <a href="#" id="bt_messageModal">
									<span class="badge" id="span_nbMessage" title="{{Nombre de messages}}"
                                          style="<?php echo $displayMessage; ?>">
                                        <?php echo $nbMessage; ?>
									</span>
                    </a>
                </li>
                <?php $nbUpdate = update::nbNeedUpdate();
                $displayUpdate = ($nbUpdate > 0) ? '' : 'display : none;'; ?>
                <li>
                    <a href="index.php?v=d&p=update">
                                <span class="badge" id="span_nbUpdate" title="{{Nombre de mises à jour}}"
                                      style="<?php echo $displayUpdate; ?>"><?php echo $nbUpdate; ?></span></a>
                </li>
                <?php if (Status::isConnectAdmin()) {
                    ?>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i
                                class="fa fa-cogs"></i><span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="index.php?v=d&p=administration" tabindex="0"><i
                                        class="fa fa-wrench"></i> {{Configuration}}</a></li>
                            <li><a href="index.php?v=d&p=backup"><i class="fa fa-floppy-o"></i> {{Sauvegardes}}</a></li>
                            <li><a href="index.php?v=d&p=migration"><i class="fa fa-floppy-o"></i> {{Migration}}</a></li>
                            <li><a href="index.php?v=d&p=update"><i class="fa fa-refresh"></i> {{Centre de mise
                                    à jour}}</a></li>
                            <li><a href="index.php?v=d&p=cron"><i class="fa fa-tasks"></i> {{Moteur de tâches}}</a>
                            </li>
                            <li><a href="index.php?v=d&p=custom"><i class="fa fa-pencil-square-o"></i>
                                    {{Personnalisation avancée}}</a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="index.php?v=d&p=user"><i class="fa fa-users"></i> {{Utilisateurs}}</a>
                            </li>
                        </ul>
                    </li>
                <?php }
                ?>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user"></i>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="index.php?v=d&p=profils"><i class="fa fa-briefcase"></i>
                                {{Profil}} <?php echo $_SESSION['user']->getLogin(); ?></a></li>
                        <li class="divider"></li>
                        <li><a href="index.php?v=m" class="noOnePageLoad"><i class="fa fa-mobile"></i> {{Version
                                mobile}}</a></li>
                        <li class="divider"></li>
                        <li><a href="#" id="bt_nextdomAbout"><i class="fa fa-info-circle"></i> {{Version}}
                                v<?php echo nextdom::version(); ?></a></li>
                        <?php if (nextdom::isCapable('sudo')) {
                            echo '<li class="divider"></li>';
                            echo '<li class="cursor"><a id="bt_rebootSystem" state="0"><i class="fa fa-repeat"></i> {{Redémarrer}}</a></li>';
                            echo '<li class="cursor"><a id="bt_haltSystem" state="0"><i class="fa fa-power-off"></i> {{Eteindre}}</a></li>';

                        }
                        ?>
                        <li class="divider"></li>
                        <li><a href="index.php?v=d&logout=1" class="noOnePageLoad"><i
                                    class="fa fa-sign-out"></i> {{Se déconnecter}}</a></li>
                    </ul>
                </li>
                <li>
                    <a id="bt_getHelpPage" class="cursor" data-plugin="<?php echo init('m'); ?>"
                       data-page="<?php echo init('p'); ?>" title="{{Aide sur la page en cours}}"><i
                            class="fa fa-question-circle"></i></a>
                </li>
                <?php if (Status::isConnectAdmin()) {
                    ?>
                    <li>
                        <?php if (isset($plugin) && is_object($plugin) && $plugin->getIssue() != '') {
                            ?>
                            <a target="_blank" href="<?php echo $plugin->getIssue() ?>"
                               title="{{Envoyer un rapport de bug}}">
                                <i class="fa fa-exclamation-circle"></i>
                            </a>
                        <?php } else { ?>
                            <a class="bt_reportBug cursor" title="{{Envoyer un rapport de bug}}">
                                <i class="fa fa-exclamation-circle"></i>
                            </a>
                        <?php } ?>
                    </li>
                <?php }
                ?>
                <li>
                    <a href="#" class="default-cursor">
                        <span id="horloge"><?php echo date('H:i:s'); ?></span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</header>
