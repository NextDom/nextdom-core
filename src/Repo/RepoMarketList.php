<?php

namespace NextDom\Repo;

use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\UpdateManager;

AuthentificationHelper::isConnectedAsAdminOrFail();

$type = Utils::init('type', null);
$categorie = Utils::init('categorie', null);
$name = Utils::init('name', null);
$author = Utils::init('author', null);
if ($name == 'false') {
    $name = null;
}
if ($author == null && $name === null && $categorie === null && Utils::init('certification', null) === null && Utils::init('cost', null) === null && $type == 'plugin') {
    $default = true;
    $markets = RepoMarket::byFilter(array(
        'status' => 'stable',
        'type' => 'plugin',
        'timeState' => 'popular',
    ));
    $markets2 = RepoMarket::byFilter(array(
        'status' => 'stable',
        'type' => 'plugin',
        'timeState' => 'newest',
    ));
    $markets = array_merge($markets, $markets2);
} else {
    $default = false;
    $markets = RepoMarket::byFilter(
        array(
            'status' => null,
            'type' => $type,
            'categorie' => $categorie,
            'name' => $name,
            'author' => $author,
            'cost' => Utils::init('cost', null),
            'timeState' => Utils::init('timeState'),
            'certification' => Utils::init('certification', null)
        )
    );
}
function buildUrl($_key, $_value)
{
    $url = 'index.php?v=d&modal=update.display&';
    foreach ($_GET as $key => $value) {
        if ($_key != $key) {
            $url .= $key . '=' . urlencode($value) . '&';
        }
    }
    if ($_key != '' && $_value != '') {
        $url .= $_key . '=' . urlencode($_value);
    }
    return $url;
}

function displayWidgetName($_name)
{
    $result = '';
    $name = explode('.', $_name);
    if (count($name) != 4) {
        return $name;
    }
    switch ($name[1]) {
        case 'info':
            $result .= '<i class="fa fa-eye fa-fw" title="{{Widget de type information}}"></i> ';
            break;
        case 'action':
            $result .= '<i class="fa fa-exclamation-circle fa-fw" title="{{Widget de type action}}"></i> ';
            break;
        default:
            $result .= $name[1];
            break;
    }
    switch ($name[2]) {
        case 'other':
            $result .= '<span class="label label-warning" style="text-shadow: none;">other</span> ';
            break;
        case 'color':
            $result .= '<span class="label label-success" style="text-shadow: none;">color</span> ';
            break;
        case 'slider':
            $result .= '<span class="label label-primary" style="text-shadow: none;">slider</span> ';
            break;
        case 'binary':
            $result .= '<span class="label label-info" style="text-shadow: none;">binary</span> ';
            break;
        case 'numeric':
            $result .= '<span class="label label-danger" style="text-shadow: none;">numeric</span> ';
            break;
        case 'string':
            $result .= '<span class="label label-default" style="text-shadow: none;">string</span> ';
            break;
        default:
            $result .= $name[2];
            break;
    }
    return $result .= $name[3];
}

function displayWidgetType($_name)
{
    $result = '';
    $name = explode('.', $_name);
    if (count($name) != 4) {
        return "";
    }
    switch ($name[1]) {
        case 'info':
            $result .= '<i class="fa fa-eye fa-fw" title="Widget de type information" style="position: absolute;top: 31px; left: 15px;"></i> ';
            break;
        case 'action':
            $result .= '<i class="fa fa-exclamation-circle fa-fw" title="Widget de type action" style="position: absolute;top: 31px; left: 15px;"></i> ';
            break;
        default:
            $result .= "";
            break;
    }
    return $result;
}

function displayWidgetSubtype($_name)
{
    $result = '';
    $name = explode('.', $_name);
    if (count($name) != 4) {
        return "";
    }
    switch ($name[2]) {
        case 'other':
            $result .= '<span class="label label-warning" style="text-shadow: none;position: absolute;top: 70px; left: -21px;transform: rotate(90deg);-webkit-transform: rotate(90deg);transform-origin: 38px 16px;-webkittransform-origin: 38px 16px;">other</span> ';
            break;
        case 'color':
            $result .= '<span class="label label-success" style="text-shadow: none;position: absolute;top: 70px; left: -21px;transform: rotate(90deg);-webkit-transform: rotate(90deg);transform-origin: 38px 16px;-webkittransform-origin: 38px 16px;">color</span> ';
            break;
        case 'slider':
            $result .= '<span class="label label-primary" style="text-shadow: none;position: absolute;top: 70px; left: -21px;transform: rotate(90deg);-webkit-transform: rotate(90deg);transform-origin: 38px 16px;-webkittransform-origin: 41px 16px;">slider</span> ';
            break;
        case 'binary':
            $result .= '<span class="label label-info" style="text-shadow: none;position: absolute;top: 70px; left: -21px;transform: rotate(90deg);-webkit-transform: rotate(90deg);transform-origin: 38px 16px;-webkittransform-origin: 44px 16px;">binary</span> ';
            break;
        case 'numeric':
            $result .= '<span class="label label-danger" style="text-shadow: none;position: absolute;top: 70px; left: -21px;transform: rotate(90deg);-webkit-transform: rotate(90deg);transform-origin: 38px 16px;-webkittransform-origin: 53px 16px;">numeric</span> ';
            break;
        case 'string':
            $result .= '<span class="label label-default" style="text-shadow: none;position: absolute;top: 70px; left: -21px;transform: rotate(90deg);-webkit-transform: rotate(90deg);transform-origin: 38px 16px;-webkittransform-origin: 41px 16px;">string</span> ';
            break;
        default:
            $result .= "";
            break;
    }
    return $result;
}
?>

<div class="action-bar">
    <form class="form-inline" role="form" onsubmit="return false;">
        <?php if (Utils::init('type', 'plugin') == 'plugin') { ?>
            <div class="form-group">
                <div class="btn-group">
                    <a class="btn btn-default bt_pluginFilter <?php echo (Utils::init('cost') == 'free') ? 'btn-primary' : '' ?>" data-href="<?php echo buildUrl('cost', 'free'); ?>">{{Gratuit}}</a>
                    <a class="btn btn-default bt_pluginFilter <?php echo (Utils::init('cost') == 'paying') ? 'btn-primary' : '' ?>" data-href="<?php echo buildUrl('cost', 'paying'); ?>">{{Payant}}</a>
                    <a class="btn btn-default bt_pluginFilter" data-href="<?php echo buildUrl('cost', ''); ?>"><i class="fa fa-times"></i></a>
                </div>
            </div>
        <?php }
        ?>
        <div class="form-group">
            <div class="btn-group">
                <a class="btn btn-default bt_pluginFilter <?php echo (Utils::init('certification') == 'Officiel') ? 'btn-primary' : '' ?>" data-href="<?php echo buildUrl('certification', 'Officiel'); ?>">{{Officiel}}</a>
                <a class="btn btn-default bt_pluginFilter <?php echo (Utils::init('certification') == 'Conseillé') ? 'btn-primary' : '' ?>" data-href="<?php echo buildUrl('certification', 'Conseillé'); ?>">{{Conseillé}}</a>
                <a class="btn btn-default bt_pluginFilter <?php echo (Utils::init('certification') == 'Premium') ? 'btn-primary' : '' ?>" data-href="<?php echo buildUrl('certification', 'Premium'); ?>">{{Premium}}</a>
                <a class="btn btn-default bt_pluginFilter <?php echo (Utils::init('certification') == 'Partenaire') ? 'btn-primary' : '' ?>" data-href="<?php echo buildUrl('certification', 'Partenaire'); ?>">{{Partenaire}}</a>
                <a class="btn btn-default bt_pluginFilter <?php echo (Utils::init('certification') == 'Legacy') ? 'btn-primary' : '' ?>" data-href="<?php echo buildUrl('certification', 'Legacy'); ?>">{{Legacy}}</a>
                <a class="btn btn-default bt_pluginFilter" data-href="<?php echo buildUrl('certification', ''); ?>"><i class="fa fa-times"></i></a>
            </div>
        </div>
        <div class="form-group">
            <div class="btn-group">
                <a class="btn btn-default bt_installFilter" data-state="-1">{{Installé}}</a>
                <a class="btn btn-default bt_installFilter" data-state="1">{{Non installé}}</a>
                <a class="btn btn-default bt_installFilter" data-state="0"><i class="fa fa-times"></i></a>
            </div>
        </div>
        <div class="form-group">
            <select class="form-control" id="sel_categorie" data-href='<?php echo buildUrl('categorie', ''); ?>'>
                <?php
                if (Utils::init('categorie') == '') {
                    echo '<option value="" selected>{{Top et nouveautés}}</option>';
                } else {
                    echo '<option value="">{{Top et nouveautés}}</option>';
                }
                if ($type !== null && $type != 'plugin') {
                    foreach (RepoMarket::distinctCategorie($type) as $id => $category) {
                        if (trim($category) != '' && is_numeric($id)) {
                            echo '<option value="' . $category . '"';
                            echo (Utils::init('categorie') == $category) ? 'selected >' : '>';
                            echo $category;
                            echo '</option>';
                        }
                    }
                } else {
                    global $NEXTDOM_INTERNAL_CONFIG;
                    foreach ($NEXTDOM_INTERNAL_CONFIG['plugin']['category'] as $key => $value) {
                        echo '<option value="' . $key . '"';
                        echo (Utils::init('categorie') == $key) ? 'selected >' : '>';
                        echo $value['name'];
                        echo '</option>';
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <input class="form-control" data-href='<?php echo buildUrl('name', ''); ?>' placeholder="Rechercher" id="in_search" value="<?php echo $name ?>"/>
            <a class="btn btn-success" id="bt_search" data-href='<?php echo buildUrl('name', ''); ?>'><i class="fa fa-search"></i></a>
        </div>
        <div class="form-group">
            <?php
            if (ConfigManager::byKey('market::username') != '') {
                echo '<span class="label label-info pull-right" style="font-size : 1em;">' . ConfigManager::byKey('market::username');
                try {
                    RepoMarket::test();
                    echo ' <i class="fa fa-check"></i>';
                } catch (\Exception $e) {
                    echo ' <i class="fa fa-times"></i>';
                }
                echo '</span>';
            }
            ?>
        </div>
    </form>
</div>
<?php

if ($name !== null && strpos($name, '$') !== false) {
    echo '<a class="btn btn-default" id="bt_returnMarketList" style="margin-top : 50px;" data-href=' . buildUrl('name', '') . '><i class="fa fa-arrow-circle-left"></i> {{Retour}}</a>';
}
?>


<div style="padding : 5px;">
    <?php
    $categorie = '';
    $first = true;
    $nCategory = 0;
    if ($default) {
        echo '<div class="pluginContainer">';
    }
    foreach ($markets as $market) {
        $update = UpdateManager::byLogicalId($market->getLogicalId());
        $category = $market->getCategorie();
        if ($category == '') {
            $category = '{{Aucune}}';
        }
        if ($categorie != $category) {
            $categorie = $category;
            if (!$default) {
                if (!$first) {
                    echo '</div>';

                    echo '</div>';

                }


                echo '<div class="box " data-category="' . $nCategory . '">';
                echo '<div class="box-title">';
                if (isset($NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$categorie])) {
                    echo '<h3 data-category="' . $nCategory . '"><i class="fa ' . $NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$categorie]['icon'] . '"></i> ' . ucfirst($NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$categorie]['name']) . '</h3>';
                } else {
                    echo '<h3 data-category="' . $nCategory . '">' . ucfirst($categorie) . '</h3>';
                }
                echo '</div>';
                echo '<div class="box-body pluginContainer">';

            }
            $first = false;
            $nCategory++;
        }

        $install = 'notInstall';
        if (!is_object($update)) {
            $install = 'install';
        }
        echo '<div class="market cursor ' . $install . '" data-market_id="' . $market->getId() . '" data-market_type="' . $market->getType() . '" style="background-color : #ffffff; height : 220px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >';
        if ($market->getType() != 'widget') {
            if ($market->getCertification() == 'Officiel') {
                echo '<div style="position : absolute; right : 0;top:0;width:58px;height:58px;"><img src="core/img/band_Officiel.png" /></div>';
            }
            if ($market->getCertification() == 'Conseillé') {
                echo '<div style="position : absolute; right : 0;top:0;width:58px;height:58px;"><img src="core/img/band_Conseille.png" /></div>';
            }
            if ($market->getCertification() == 'Legacy') {
                echo '<div style="position : absolute; right : 0;top:0;width:58px;height:58px;"><img src="core/img/band_Legacy.png" /></div>';
            }
            if ($market->getCertification() == 'Obsolète') {
                echo '<div style="position : absolute; right : 0;top:0;width:58px;height:58px;"><img src="core/img/band_Obsolete.png" /></div>';
            }
            if ($market->getCertification() == 'Premium') {
                echo '<div style="position : absolute; right : 0;top:0;width:58px;height:58px;"><img src="core/img/band_Premium.png" /></div>';
            }
            if ($market->getCertification() == 'Partenaire') {
                echo '<div style="position : absolute; right : 0;top:0;width:58px;height:58px;"><img src="core/img/band_Partenaire.png" /></div>';
            }
        }
        if ($market->getType() == 'widget') {
            if (strpos($market->getName(), 'mobile.') !== false) {
                echo '<i class="fa fa-mobile" style="position: absolute;top: 15px;left: 21px;" title="{{Widget pour la version mobile}}"></i>';
            } else {
                echo '<i class="fa fa-desktop" style="position: absolute;top: 15px;left: 17px;" title="{{Widget pour la version bureau}}"></i>';
            }
        }
        if (is_object($update)) {
            echo '<i class="fa fa-check" style="position : absolute; right : 5px;"></i>';
        }
        echo "<br/><center>";
        $default_image = 'core/img/no_image.gif';
        switch ($market->getType()) {
            case 'widget':
                $default_image = 'public/img/NextDom_NoPicture_Gray.png';
                break;
            case 'plugin':
                $default_image = 'public/img/NextDom_NoPicture_Gray.png';
                break;
            case 'script':
                $default_image = 'public/img/NextDom_NoPicture_Gray.png';
                break;
        }
        $urlPath = ConfigManager::byKey('market::address') . '/' . $market->getImg('icon');
        if ($market->getType() == 'widget') {
            echo '<img class="lazy" src="' . $default_image . '" data-original="' . $urlPath . '" height="105" width="95" style="margin-left: 20px;border: 1px solid #C5C5C5;border-radius:5px; padding: 3px" />';
        } else {
            echo '<img class="lazy" src="' . $default_image . '" data-original="' . $urlPath . '" height="105" width="95" />';
        }
        echo "</center>";
        echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;">' . $market->getName() . '</span>';
        echo '<span style="position : absolute;bottom : 25px;right : 12px;font-size : 0.7em;color:#999999;"><span style="font-size : 0.8em;">{{par}}</span> ' . $market->getAuthor() . '</span>';
        $note = $market->getRating();
        echo '<span style="position : absolute;bottom : 5px;left : 5px;font-size : 0.7em;">';
        for ($i = 1; $i < 6; $i++) {
            if ($i <= $note) {
                echo '<i class="fa fa-star"></i>';
            } else {
                echo '<i class="fa fa-star-o"></i>';
            }
        }
        echo '</span>';
        if ($market->getCertification() !== 'Premium') {
            if ($market->getCost() > 0) {
                echo '<span style="position : absolute;bottom : 5px;right : 12px;color:#97bd44;">';
                if ($market->getPurchase() == 1) {
                    echo ' <i class="fa fa-check-circle"></i>';
                } else if ($market->getCertification() == 'Premium') {
                    echo '';
                } else {
                    if ($market->getCost() != $market->getRealCost()) {
                        echo '<span style="text-decoration:line-through;">' . number_format($market->getRealCost(), 2) . ' €</span> ';
                    }
                    echo number_format($market->getCost(), 2) . ' €';
                }
                echo '</span>';
            } else {
                echo '<span style="position : absolute;bottom : 5px;right : 12px;color:#97bd44;">Gratuit</span>';
            }
        }

        echo '</div>';

    }

    if ($default) {
        echo '</div>';
    }

    ?>
</div>

<script>
    $(function () {
        $('.pluginContainer').packery();
        $("img.lazy").lazyload({
            event: "sporty"
        });
        $("img.lazy").trigger("sporty");
        initTableSorter();
        setTimeout(function () {
            $('#table_market tbody tr.install').hide();
        }, 500);
        $('.bt_pluginFilter').on('click', function () {
            $('#md_modal').load($(this).attr('data-href'));
        });
        $('#sel_categorie').on('change', function () {
            $('#md_modal').load($(this).attr('data-href') + '&categorie=' + encodeURI($(this).value()));
        });
        $('#bt_search').on('click', function () {
            $('#md_modal').load($(this).attr('data-href') + '&name=' + encodeURI($('#in_search').value()));
        });
        $('#in_search').keypress(function (e) {
            if (e.which == 13) {
                $('#md_modal').load($(this).attr('data-href') + '&name=' + encodeURI($('#in_search').value()));
            }
        });
        $('#bt_returnMarketList').on('click', function () {
            $('#md_modal').load($(this).attr('data-href'));
        });
        $('.marketMultiple').on('click', function () {
            $('#md_modal').load($(this).attr('data-href') + '&name=' + encodeURI('.' + $(this).attr('data-market_name')));
        });
        $('.bt_installFilter').on('click', function () {
            $('.bt_installFilter').removeClass('btn-primary');
            $('.pluginContainer').show();
            $('.market').show();
            if ($(this).attr('data-state') == 1) {
                $(this).addClass('btn-primary');
                $('.notInstall').hide();
            }
            if ($(this).attr('data-state') == -1) {
                $(this).addClass('btn-primary');
                $('.install').hide();
            }
            $('.pluginContainer').each(function () {
                var hasVisible = false;
                $(this).find('.market').each(function () {
                    if ($(this).is(':visible')) {
                        hasVisible = true;
                    }
                });
                if (hasVisible) {
                    $('legend[data-category=' + $(this).attr('data-category') + ']').show();
                    $(this).packery();
                } else {
                    $(this).hide();
                    $('legend[data-category=' + $(this).attr('data-category') + ']').hide();
                }
            });
        });
        $('.market').on('click', function () {
            $('#md_modal2').dialog({title: "{{Market Jeedom}}"});
            $('#md_modal2').load('index.php?v=d&modal=update.display&type=' + $(this).attr('data-market_type') + '&id=' + $(this).attr('data-market_id') + '&repo=market').dialog('open');
        });
    });
</script>
