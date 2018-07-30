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
use NextDom\Managers\JeeObjectManager;
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
<body class="hold-transition skin-blue sidebar-mini">
<header class="main-header">

  <!-- Logo -->
  <a href="<?php echo $homeLink; ?>" class="logo">
    <!-- mini logo for sidebar mini 50x50 pixels -->
    <span class="logo-mini"><b>Nx</b>D</span>
    <!-- logo for regular state and mobile devices -->
    <span class="logo-lg"><b>NextDom</b></span>
  </a>

  <!-- Header Navbar: style can be found in header.less -->
  <nav class="navbar navbar-static-top">
    <!-- Sidebar toggle button-->
    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
      <span class="sr-only">Toggle navigation</span>
    </a>
    <!-- Navbar Right Menu -->
    <div class="navbar-custom-menu">
      <ul class="nav navbar-nav">
                  <!-- Notifications: style can be found in dropdown.less -->
        <li class="dropdown notifications-menu">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <i class="fa fa-bell-o"></i>
            <span class="label label-warning"><?php echo $nbMessage; ?></span>
          </a>
          <ul class="dropdown-menu">
            <li class="header">You have <?php echo $nbMessage; ?>  notifications</li>

            </li>
            <li class="footer"><a href="index.php?v=d&p=update">View all</a></li>
          </ul>
        </li>
        <!-- Tasks: style can be found in dropdown.less -->
        <li class="dropdown tasks-menu">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <i class="fa fa-flag-o"></i>
            <span class="label label-danger"><?php echo $nbUpdate; ?></span>
          </a>
          <ul class="dropdown-menu">
            <li class="header">You have <?php echo $nbUpdate; ?> tasks</li>
            <li>
              <!-- inner menu: contains the actual data -->

                <!-- end task item -->

            </li>
            <li class="footer">
              <a href="index.php?v=d&p=update">View all tasks</a>
            </li>
          </ul>
        </li>

        <!-- Control Sidebar Toggle Button -->
        <li>
          <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
        </li>
      </ul>
    </div>

  </nav>
</header>
<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">

    <!-- search form -->
    <form action="#" method="get" class="sidebar-form">
      <div class="input-group">
        <input type="text" name="q" class="form-control" placeholder="Search...">
        <span class="input-group-btn">
              <button type="submit" name="search" id="search-btn" class="btn btn-flat">
                <i class="fa fa-search"></i>
              </button>
            </span>
      </div>
    </form>
    <!-- /.search form -->
    <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu" data-widget="tree">
      <li class="header">MENU PRINCIPAL</li>
      <li class="active treeview menu-open">
          <a href="#"><i class="fa fa-home"></i> <span>Acceuil</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
              <ul class="treeview-menu">
                  <li class="treeview">
                      <a href="#"><i class="fa fa-dashboard"></i> Dashboard <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
                      <ul class="treeview-menu">

                              <li class="treeview-menu">
                                  <?php
                                  foreach (JeeObjectManager::buildTree(null, false) as $objectLi) {
                                      echo '<li><a href="index.php?v=d&p=dashboard&object_id=' . $objectLi->getId() . '">' . $objectLi->getHumanName(true) . '</a></li>';
                                  }
                                  ?>
                              </li>

                      </ul>
                  </li>


                  <li class="treeview">
                      <a href="#"><i class="fa fa-picture-o"></i> Vue <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
                      <ul class="treeview-menu">

                              <li class="treeview-menu">
                                  <?php
                                  foreach (view::all() as $viewMenu) {
                                      echo '<li><a href="index.php?v=d&p=view&view_id=' . $viewMenu->getId() . '">' . trim($viewMenu->getDisplay('icon')) . ' ' . $viewMenu->getName() . '</a></li>';
                                  }
                                  ?>
                              </li>

                      </ul>
                  </li>


                  <li class="treeview">
                      <a href="#"><i class="fa fa-paint-brush"></i> Design <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>
                      <ul class="treeview-menu">

                              <li class="treeview-menu">
                 <?php
                                  foreach (planHeader::all() as $planMenu) {
                                      echo '<li><a href="index.php?v=d&p=plan&plan_id=' . $planMenu->getId() . '">' . trim($planMenu->getConfiguration('icon') . ' ' . $planMenu->getName()) . '</a></li>';
                                  }
                                  ?>
                              </li>

                      </ul>
                  </li>
              </ul>

       </li>
          <li class="treeview">
          <a href="#">
            <i class="fa fa-stethoscope"></i>
            <span>Analyse</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="index.php?v=d&p=history"><i class="fa fa-bar-chart-o"></i> {{Historique}}</a></li>
            <li><a href="index.php?v=d&p=report"><i class="fa fa-newspaper-o"></i> {{Rapport}}</a></li>
            <li><a href="#" id="bt_showEventInRealTime"><i class="fa fa-tachometer"></i> {{Temps réel}}</a></li>
            <li><a href="index.php?v=d&p=log"><i class="fa fa-file-o"></i> {{Logs}}</a></li>
            <li><a href="index.php?v=d&p=eqAnalyse"><i class="fa fa-battery-full"></i> {{Equipements}}</a></li>
            <li><a href="index.php?v=d&p=health"><i class="fa fa-medkit"></i> {{Santé}}</a></li>
          </ul>
        </li>
           <li class="treeview">
          <a href="#">
            <i class="fa fa-laptop"></i>
            <span>Outils</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="index.php?v=d&p=object"><i class="fa fa-picture-o"></i> {{Objets}}</a></li>
            <li><a href="index.php?v=d&p=interact"><i class="fa fa-comments-o"></i> {{Interactions}}</a></li>
            <li><a href="index.php?v=d&p=display"><i class="fa fa-th"></i> {{Résumé domotique}}</a></li>
            <li><a href="index.php?v=d&p=scenario"><i class="fa fa-cogs"></i> {{Scénarios}}</a></li>
          </ul>
        </li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-share"></i> <span>Plugins</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="index.php?v=d&p=plugin"><i class="fa fa-tags"></i> {{Gestion des plugins}}</a></li>
                <li class="treeview-menu">
                   <?php echo $pluginMenu; ?>
               </li>
        </li>


    </ul>
                     <li class="treeview">
          <a href="#">
            <i class="fa fa-laptop"></i>
            <span>{{Réglages}}</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="index.php?v=d&p=administration"><i class="fa fa-wrench"></i> {{Configuration}}</a></li>
            <li><a href="index.php?v=d&p=backup"><i class="fa fa-floppy-o"></i> {{Sauvegardes}}</a></li>
            <li><a href="index.php?v=d&p=migration"><i class="fa fa-upload"></i> {{Migration depuis jeedom}}</a></li>
            <li><a href="index.php?v=d&p=update"><i class="fa fa-refresh"></i> {{Centre de mise à jour}}</a></li>
            <li><a href="index.php?v=d&p=cron"><i class="fa fa-tasks"></i> {{Moteur de tâches}}</a></li>
            <li><a href="index.php?v=d&p=custom"><i class="fa fa-pencil-square-o"></i> {{Personnalisation avancée}}</a></li>
            <li><a href="index.php?v=d&p=user"><i class="fa fa-users"></i> {{Utilisateurs}}</a></li>
            <li><a href="index.php?v=d&p=profils"><i class="fa fa-briefcase"></i> {{Profil}}</a></li>
            <li><a href="index.php?v=m"><i class="fa fa-mobile"></i> {{Version mobile}}</a></li>
            <li><a href="index.php?v=d&p=log"><i class="fa fa-info-circle"></i> {{Version}}<?php echo nextdom::version(); ?></a></li>
            <li><a href="index.php?v=d&logout=1" class="noOnePageLoad"><i class="fa fa-circle-o"></i> {{Se déconnecter}}</a></li>
			<?php
                     if (nextdom::isCapable('sudo')) {
                       echo '<li class="divider"></li>';
                       echo '<li><a id="bt_rebootSystem" state="0"><i class="fa fa-repeat"></i> {{Redémarrer}}</a></li>';
                       echo '<li><a id="bt_haltSystem" state="0"><i class="fa fa-power-off"></i> {{Eteindre}}</a></li>';
                     }
			?>
          </ul>
        </li>
</ul>
  </section>
  <!-- /.sidebar -->
</aside>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
</body>
