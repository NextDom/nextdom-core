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
<?php include_file('3rdparty', 'jquery.multi-column-select/multi-column-select', 'js');?>
<div class="row" >
    <?php
    if (init('object_id') != '') {
        echo '<div class="col-md-12">';
    } else {
        echo '<div class="col-md-' . $object->getDisplay('dashboard::size', 12) . '">';
    }

    echo '<div class="panel" style="background-color:'. $object->getDisplay("tagColor") .'20;border-color:' . $object->getDisplay("tagColor") . ';" data-father_id="' . $object->getFather_id() .'">';
    echo '<div class="panel-heading" style="box-shadow: 0 4px 20px 0 rgba(0,0,0,.14), 0 7px 10px -5px '. $object->getDisplay("tagColor") .'15;background-color:' . $object->getDisplay("tagColor") . '; color:'.$object->getDisplay("tagTextColor").'">';
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
        echo '<div class="panel div_object" style="background-color:'. $child->getDisplay("tagColor") .'15;border-color:' . $child->getDisplay("tagColor") . ';" data-father_id="' . $child->getFather_id() .'">';
        echo '<div class="panel-heading" style="box-shadow: 0 4px 20px 0 rgba(0,0,0,.14), 0 7px 10px -5px '. $child->getDisplay("tagColor") .'15;background-color:' . $child->getDisplay("tagColor") . ';color:'.$child->getDisplay("tagTextColor").'">';
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

<?php include_file('desktop', 'dashboard-v2', 'js'); ?>
</body>