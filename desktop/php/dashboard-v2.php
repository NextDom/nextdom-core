<?php
if (!isConnect()) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJs('SEL_OBJECT_ID', init('object_id'));
sendVarToJs('SEL_CATEGORY', init('category', 'all'));
sendVarToJs('SEL_TAG', init('tag', 'all'));
sendVarToJs('SEL_SUMMARY', init('summary'));
if (init('object_id') == '') {
    $object = jeeObject::byId($_SESSION['user']->getOptions('defaultDashboardObject'));
} else {
    $object = jeeObject::byId(init('object_id'));
}
if (!is_object($object)) {
    $object = jeeObject::rootObject();
}
if (!is_object($object)) {
    throw new Exception('{{Aucun objet racine trouvé. Pour en créer un, allez dans Outils -> Objets.<br/> Si vous ne savez pas quoi faire ou que c\'est la première fois que vous utilisez Jeedom, n\'hésitez pas à consulter cette <a href="https://jeedom.github.io/documentation/premiers-pas/fr_FR/index" target="_blank">page</a> et celle-là si vous avez un pack : <a href="https://jeedom.com/start" target="_blank">page</a>}}');
}
$child_object = jeeObject::buildTree($object);
sendVarToJs('rootObjectId', $object->getId());
?>


<i class='fa fa-picture-o cursor pull-left' id='bt_displayObject' data-display='<?php echo $_SESSION['user']->getOptions('displayObjetByDefault') ?>' title="{{Afficher/Masquer les objets}}"></i>
<i class='fa fa-sort-amount-desc pull-left cursor' id='bt_categorieHidden' title="{{Trier vos équipements}}"></i>
<i class='fa fa-cogs pull-right cursor' id='bt_displayScenario' data-display='<?php echo $_SESSION['user']->getOptions('displayScenarioByDefault') ?>' title="{{Afficher/Masquer les scénarios}}"></i>
<?php if (init('category', 'all') == 'all') {?>
    <i class="fas fa-pencil-alt pull-right cursor" id="bt_editDashboardWidgetOrder" data-mode="0" style="margin-right : 10px;"></i>
<?php }
?>
<div style="witdh:100%; display: none;" class="categorieHidden">
    <div style="witdh:45%; float:left;">
        <div class="demo">
            <select id="sel_eqLogicCategory">
                <?php
                if (init('category', 'all') == 'all') {
                    echo '<option value="all" selected> {{Toute}}</option>';
                } else {
                    echo '<option value="all"> {{Toute}}</option>';
                }
                foreach (jeedom::getConfiguration('eqLogic:category', true) as $key => $value) {
                    if (init('category', 'all') == $key) {
                        echo '<option value="' . $key . '" selected> {{' . $value['name'] . '}}</option>';
                    } else {
                        echo '<option value="' . $key . '"> {{' . $value['name'] . '}}</option>';
                    }
                }
                ?>
            </select>
        </div>
    </div>
    <div style="witdh:45%; float:left;">
        <div class="demo2">
            <select id="sel_eqLogicTags">
                <?php
                if (init('tag', 'all') == 'all') {
                    echo '<option value="all" selected> {{Tous}}</option>';
                } else {
                    echo '<option value="all"> {{Tous}}</option>';
                }
                $knowTags = eqLogic::getAllTags();
                foreach ($knowTags as $tag) {
                    if (init('tag', 'all') == $tag) {
                        echo '<option value="' . $tag . '" selected> ' . $tag . '</option>';
                    } else {
                        echo '<option value="' . $tag . '"> ' . $tag . '</option>';
                    }
                }
                ?>
            </select>
        </div>
    </div>
</div>
<?php include_file('desktop', 'dashboard', 'js');?>
<?php include_file('desktop', 'dashboard-v2', 'js'); ?>
<?php include_file('3rdparty', 'jquery.isotope/isotope.pkgd.min', 'js');?>
<?php include_file('desktop', 'dashboard', 'css');?>
<?php include_file('3rdparty', 'jquery.multi-column-select/multi-column-select', 'js');?>
<div class="row" >
    <?php
    if (init('object_id') != '') {
        echo '<div class="col-md-12">';
    } else {
        echo '<div class="col-md-' . $object->getDisplay('dashboard::size', 12) . '">';
    }

echo '<div class="panel" style="background-color:#ecf0f5;border-color:' . $object->getDisplay("tagColor") . ';" data-father_id="' . $object->getFather_id() .'">';
echo '<div class="panel-heading" style="background-color:' . $object->getDisplay("tagColor") . '; color:'.$object->getDisplay("tagTextColor").'">';
echo '<h3 class="panel-title">' . $object->getDisplay("icon" ).' '. $object->getName().'</h3>';
echo '</div>';
echo '<div id="config">';
echo '<div class="panel-body">';
echo '<div class="div_displayEquipement" id="div_ob' . $object->getId() . '" style="width: 100%;padding-top:3px;margin-bottom : 3px;">';
    echo '<script>getObjectHtml(' . $object->getId() . ')</script>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
echo '</div>';
    foreach ($child_object as $child) {
        if ($child->getConfiguration('hideOnDashboard', 0) == 1) {
            continue;
        }
      echo '<div class="panel div_object" style="background-color:#ecf0f5; border-color:' . $child->getDisplay("tagColor") . ';" data-father_id="' . $child->getFather_id() .'">';
echo '<div class="panel-heading" style="background-color:' . $child->getDisplay("tagColor") . ';color:'.$child->getDisplay("tagTextColor").'">';
echo '<h3 class="panel-title">' . $child->getDisplay("icon" ).' '. $child->getName().'</h3>';
echo '      <span class="pull-right clickable"><i class="glyphicon glyphicon-chevron-up"></i></span>';
echo '</div>';
echo '<div id="config">';
echo '<div class="panel-body">';
echo '<div class="div_displayEquipement" id="div_ob' . $child->getId() . '" style="width: 100%;padding-top:3px;margin-bottom : 3px;">';
        echo '<script>getObjectHtml(' . $child->getId() . ')</script>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    ?>
</div>
</div>
<?php
if ($_SESSION['user']->getOptions('displayScenarioByDefault') == 1) {
    echo '<div class="col-lg-2 col-md-2 col-sm-3" id="div_displayScenario">';
} else {
    echo '<div class="col-lg-2 col-md-2 col-sm-3" id="div_displayScenario" style="display:none;">';
}
?>
<legend><i class="fas fa-history"></i> {{Scénarios}}</legend>
<?php
foreach (scenario::all() as $scenario) {
    if ($scenario->getIsVisible() == 0) {
        continue;
    }
    echo $scenario->toHtml('dashboard');
}
?>

</div>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Create the tabs -->
    <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
        <li><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
        <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
    </ul>
    <!-- Tab panes -->
    <div class="tab-content">
        <!-- Home tab content -->
        <div class="tab-pane" id="control-sidebar-home-tab">
            <h3 class="control-sidebar-heading">Recent Activity</h3>
            <ul class="control-sidebar-menu">
                <li>
                    <a href="javascript:void(0)">
                        <i class="menu-icon fa fa-birthday-cake bg-red"></i>

                        <div class="menu-info">
                            <h4 class="control-sidebar-subheading">Langdon's Birthday</h4>

                            <p>Will be 23 on April 24th</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)">
                        <i class="menu-icon fa fa-user bg-yellow"></i>

                        <div class="menu-info">
                            <h4 class="control-sidebar-subheading">Frodo Updated His Profile</h4>

                            <p>New phone +1(800)555-1234</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)">
                        <i class="menu-icon fa fa-envelope-o bg-light-blue"></i>

                        <div class="menu-info">
                            <h4 class="control-sidebar-subheading">Nora Joined Mailing List</h4>

                            <p>nora@example.com</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)">
                        <i class="menu-icon fa fa-file-code-o bg-green"></i>

                        <div class="menu-info">
                            <h4 class="control-sidebar-subheading">Cron Job 254 Executed</h4>

                            <p>Execution time 5 seconds</p>
                        </div>
                    </a>
                </li>
            </ul>
            <!-- /.control-sidebar-menu -->

            <h3 class="control-sidebar-heading">Tasks Progress</h3>
            <ul class="control-sidebar-menu">
                <li>
                    <a href="javascript:void(0)">
                        <h4 class="control-sidebar-subheading">
                            Custom Template Design
                            <span class="label label-danger pull-right">70%</span>
                        </h4>

                        <div class="progress progress-xxs">
                            <div class="progress-bar progress-bar-danger" style="width: 70%"></div>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)">
                        <h4 class="control-sidebar-subheading">
                            Update Resume
                            <span class="label label-success pull-right">95%</span>
                        </h4>

                        <div class="progress progress-xxs">
                            <div class="progress-bar progress-bar-success" style="width: 95%"></div>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)">
                        <h4 class="control-sidebar-subheading">
                            Laravel Integration
                            <span class="label label-warning pull-right">50%</span>
                        </h4>

                        <div class="progress progress-xxs">
                            <div class="progress-bar progress-bar-warning" style="width: 50%"></div>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)">
                        <h4 class="control-sidebar-subheading">
                            Back End Framework
                            <span class="label label-primary pull-right">68%</span>
                        </h4>

                        <div class="progress progress-xxs">
                            <div class="progress-bar progress-bar-primary" style="width: 68%"></div>
                        </div>
                    </a>
                </li>
            </ul>
            <!-- /.control-sidebar-menu -->

        </div>
        <!-- /.tab-pane -->

        <!-- Settings tab content -->
        <div class="tab-pane" id="control-sidebar-settings-tab">
            <form method="post">
                <h3 class="control-sidebar-heading">General Settings</h3>

                <div class="form-group">
                    <label class="control-sidebar-subheading">
                        Report panel usage
                        <input type="checkbox" class="pull-right" checked>
                    </label>

                    <p>
                        Some information about this general settings option
                    </p>
                </div>
                <!-- /.form-group -->

                <div class="form-group">
                    <label class="control-sidebar-subheading">
                        Allow mail redirect
                        <input type="checkbox" class="pull-right" checked>
                    </label>

                    <p>
                        Other sets of options are available
                    </p>
                </div>
                <!-- /.form-group -->

                <div class="form-group">
                    <label class="control-sidebar-subheading">
                        Expose author name in posts
                        <input type="checkbox" class="pull-right" checked>
                    </label>

                    <p>
                        Allow the user to show his name in blog posts
                    </p>
                </div>
                <!-- /.form-group -->

                <h3 class="control-sidebar-heading">Chat Settings</h3>

                <div class="form-group">
                    <label class="control-sidebar-subheading">
                        Show me as online
                        <input type="checkbox" class="pull-right" checked>
                    </label>
                </div>
                <!-- /.form-group -->

                <div class="form-group">
                    <label class="control-sidebar-subheading">
                        Turn off notifications
                        <input type="checkbox" class="pull-right">
                    </label>
                </div>
                <!-- /.form-group -->

                <div class="form-group">
                    <label class="control-sidebar-subheading">
                        Delete chat history
                        <a href="javascript:void(0)" class="text-red pull-right"><i class="fa fa-trash-o"></i></a>
                    </label>
                </div>
                <!-- /.form-group -->
            </form>
        </div>
        <!-- /.tab-pane -->
    </div>
</aside>
<!-- /.control-sidebar -->
<!-- Add the sidebar's background. This div must be placed
immediately after the control sidebar -->
<div class="control-sidebar-bg"></div>

</div>
<!-- ./wrapper -->
</html>
<?php include_file('desktop', 'dashboard-v2', 'js'); ?>
