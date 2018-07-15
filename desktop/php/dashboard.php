<?php

use NextDom\Helpers\Status;
use NextDom\Helpers\Utils;
use NextDom\Managers\ObjectManager;
use NextDom\Managers\ScenarioManager;

Status::initConnectState();
Status::isConnectedAdminOrFail();

if (Utils::init('object_id') == '') {
    $object = ObjectManager::byId($_SESSION['user']->getOptions('defaultDashboardObject'));
} else {
    $object = ObjectManager::byId(Utils::init('object_id'));
}
if (!is_object($object)) {
    $object = ObjectManager::rootObject();
}
if (!is_object($object)) {
    throw new \Exception('{{Aucun objet racine trouvé. Pour en créer un, allez dans Outils -> Objets.<br/> Si vous ne savez pas quoi faire ou que c\'est la première fois que vous utilisez NextDom, n\'hésitez pas à consulter cette <a href="https://nextdom.github.io/documentation/premiers-pas/fr_FR/index" target="_blank">page</a> et celle-là si vous avez un pack : <a href="https://nextdom.com/start" target="_blank">page</a>}}');
}
$childrenObjets = ObjectManager::buildTree($object);
$displayScenarioByDefault = false;
if ($_SESSION['user']->getOptions('displayScenarioByDefault') == 1) {
    $displayScenarioByDefault = true;
}
$displayObjetByDefault = false;
if ($_SESSION['user']->getOptions('displayObjetByDefault') == 1) {
    $displayObjetByDefault = true;
}
?>
<style>
    .scenario-widget {
        margin-top: 2px !important;
    }

    #div_displayObjectList.show-sidenav {
        display: block;
        left: 0;
    }

    #div_displayScenario.show-sidenav {
        display: block;
        right: 0;
    }

    #bt_editDashboardWidgetOrder {
        bottom: 5px;
        right: 5px;
        position: fixed;
    }

    #ul_object li.filter {
        margin-bottom: 5px;
    }

    #ul_object li.filter {
        width: 100%;
    }

    #scenario-title {
        display: inline;
        color: white;
        margin-left: 50px;
        font-size: 2rem;
    }

    #ul_object .li_object {
        padding: 2px 0px;
    }

    #config .div_displayEquipement {
        width: 100%;
        padding-top: 3px;
        margin-bottom: 3px;
    }

    #collapse_categorie a {
        margin-bottom: 5px;margin-right: 3px
    }
</style>
<div class="row row-overflow">
    <div id="div_displayObjectList" class="sidenav-overlay
    <?php
    if ($displayObjetByDefault) {
        echo ' show-sidenav';
    }
    ?>">
        <a class='btn-floating btn-lg' title="{{Afficher/Masquer les objets}}"><i id='bt_displayObject' data-status="close" class='fa fa-angle-double-right fa-2x icon_nextdom_blue'></i></a>
        <a class='btn-floating btn-lg' title="{{Afficher/Masquer les objets}}"><i id='bt_displayScenario' data-status="close" class='fa fa-angle-double-left fa-2x icon_nextdom_blue'></i></a>

        <?php if (Utils::init('category', 'all') == 'all') { ?>
            <i class="fa fa-pencil pull-right cursor" id="bt_editDashboardWidgetOrder" data-mode="0"></i>
        <?php }
        ?>

        <div class="bs-sidebar">
            <ul id="ul_object" class="nav nav-list bs-sidenav">
                <li class="filter">
                    <input class="filter form-control input-sm" placeholder="{{Rechercher}}"/>
                </li>
                <?php
                $allObject = ObjectManager::buildTree(null, true);
                foreach ($allObject as $childObject) {
                    $margin = 5 * $childObject->getConfiguration('parentNumber');
                    if ($childObject->getId() == $object->getId()) {
                        echo '<li class="cursor li_object active" ><a data-object_id="' . $childObject->getId() . '" data-href="index.php?v=d&p=dashboard&object_id=' . $childObject->getId() . '&category=' . Utils::init('category', 'all') . '"><span style="position:relative;left:' . $margin . 'px;font-size:0.85em;">' . $childObject->getHumanName(true, true) . '</span><span style="font-size : 0.65em;float:right;position:relative;top:7px;">' . $childObject->getHtmlSummary() . '</span></a></li>';
                    } else {
                        echo '<li class="cursor li_object" ><a data-object_id="' . $childObject->getId() . '" data-href="index.php?v=d&p=dashboard&object_id=' . $childObject->getId() . '&category=' . Utils::init('category', 'all') . '"><span style="position:relative;left:' . $margin . 'px;font-size:0.85em;">' . $childObject->getHumanName(true, true) . '</span><span style="font-size : 0.65em;float:right;position:relative;top:7px;">' . $childObject->getHtmlSummary() . '</span></a></li>';
                    }
                }
                ?>
            </ul>
        </div>
    </div>

    <div id="div_displayScenario" class="displayScenario-overlay<?php
    if ($displayScenarioByDefault) {
        echo ' show-sidenav';
    } ?>">
        <a id="scenario-title" class="text-center"><i class="fa fa-history"></i> {{Scénarios}}</a>
        <?php
        foreach (ScenarioManager::all() as $scenario) {
            if ($scenario->getIsVisible() != 0) {
                echo $scenario->toHtml('dashboard');
            }
        }
        ?>
    </div>

    <div id="div_displayObject" class="
<?php
    if ($displayScenarioByDefault) {
        if ($displayObjetByDefault) {
            echo 'col-lg-8 col-lg-offset-2 col-md-7-offset-2 col-sm-5-offset-2';
        } else {
            echo 'col-lg-10 col-md-9 col-sm-7';
        }
    } else {
        if ($displayObjetByDefault) {
            echo 'col-lg-10 col-lg-offset-2 col-md-9 col-sm-8';
        } else {
            echo 'col-lg-12 col-md-12 col-sm-12';
        }
    }
    ?>">
        <div class="fixed-action-btn">
        </div>

        <div id="collapse_categorie" class="collapse text-center">
            <?php
            if (Utils::init('category', 'all') == 'all') {
                echo '<a href="index.php?v=d&p=dashboard&object_id=' . Utils::init('object_id') . '&category=all&summary=' . Utils::init('summary') . '" class="btn btn-primary btn-sm categoryAction"><i class="fa fa-asterisk"></i> {{Tous}}</a>';
            } else {
                echo '<a href="index.php?v=d&p=dashboard&object_id=' . Utils::init('object_id') . '&category=all&summary=' . Utils::init('summary') . '" class="btn btn-default btn-sm categoryAction"><i class="fa fa-asterisk"></i> {{Tous}}</a>';
            }
            foreach (nextdom::getConfiguration('eqLogic:category', true) as $key => $value) {
                if (Utils::init('category', 'all') == $key) {
                    echo '<a href="index.php?v=d&p=dashboard&object_id=' . Utils::init('object_id') . '&category=' . $key . '&summary=' . Utils::init('summary') . '" class="btn btn-primary btn-sm categoryAction" data-l1key="' . $key . '"><i class="' . $value['icon'] . '"></i> {{' . $value['name'] . '}}</a>';
                } else {
                    echo '<a href="index.php?v=d&p=dashboard&object_id=' . Utils::init('object_id') . '&category=' . $key . '&summary=' . Utils::init('summary') . '" class="btn btn-default btn-sm categoryAction" data-l1key="' . $key . '"><i class="' . $value['icon'] . '"></i> {{' . $value['name'] . '}}</a>';
                }
            }
            ?>
        </div>
        <?php include_file('desktop', 'dashboard', 'js'); ?>
        <div class="row">
            <div class="
        <?php
            if (Utils::init('object_id') != '') {
                echo 'col-md-12';
            } else {
                echo 'col-md-' . $object->getDisplay('dashboard::size', 12);
            }
            ?>">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <a data-parent="" data-toggle="collapse" href="#config" style="text-decoration:none;"><?php echo $object->getDisplay('icon') . ' ' . $object->getName(); ?>
                        </h3>
                    </div>
                    <div id="config" class="accordion in">
                        <div class="panel-body">
                            <div class="div_displayEquipement" id="div_ob<?php echo $object->getId(); ?>">
                                <script>getObjectHtml(<?php echo $object->getId(); ?>)</script>
                            </div>
                        </div>
                    </div>
                </div>
                <?php

                foreach ($childrenObjets as $child) {
                    if ($child->getConfiguration('hideOnDashboard', 0) != 1) {
                        ?>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">
                                    <a data-parent="" data-toggle="collapse" href="#config_<?php echo $child->getId(); ?>" style="text-decoration:none;"><?php echo $child->getDisplay('icon') . ' ' . $child->getName(); ?>
                                </h3>
                            </div>
                            <div id="config_<?php echo $child->getId(); ?>" class="accordion in">
                                <div class="panel-body">
                                    <div class="div_displayEquipement" id="div_ob<?php echo $child->getId(); ?>" style="width: 100%;padding-top:3px;margin-bottom : 3px;">
                                        <script>getObjectHtml(<?php echo $child->getId(); ?>)</script>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>
