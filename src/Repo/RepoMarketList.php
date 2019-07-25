<?php

namespace NextDom\Repo;

use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\UpdateManager;

AuthentificationHelper::isConnectedAsAdminOrFail();

$searchLimit = Utils::init('limit', 100);
if ($searchLimit == 0) {
  $searchLimit = '';
}
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
        'timeState' => 'popular'
    ));
    $markets2 = RepoMarket::byFilter(array(
        'status' => 'stable',
        'type' => 'plugin',
        'timeState' => 'newest'
    ));
    $markets = array_merge($markets, $markets2);
} else {
    $default = false;
    $markets = RepoMarket::byFilter(array(
        'status' => null,
        'type' => $type,
        'categorie' => $categorie,
        'name' => $name,
        'author' => $author,
        'cost' => Utils::init('cost', null),
        'timeState' => Utils::init('timeState'),
        'certification' => Utils::init('certification', null),
        'limit' => $searchLimit
    ));
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

<link rel="stylesheet" href="/public/css/pages/markets.css">

<section class="content-header">
    <div class="action-bar">
        <?php
        if ($name !== null && strpos($name, '$') !== false) {
            echo '<a class="btn btn-danger btn-action-bar" id="bt_returnMarketList" style="margin-top : 50px;" data-href=' . buildUrl('name', '') . '><i class="fas fa-chevron-left"></i>{{Retour}}</a>';
        }
        ?>
        <?php if (Utils::init('type', 'plugin') == 'plugin') { ?>
            <div class="action-group">
                <div class="btn-group">
                    <a class="btn bt_pluginFilter <?php echo (Utils::init('cost') == 'free') ? 'btn-primary' : 'btn-default' ?>" data-href="<?php echo buildUrl('cost', 'free'); ?>"><i class="fas fa-gift"></i><span>{{Gratuit}}</span></a>
                    <a class="btn bt_pluginFilter <?php echo (Utils::init('cost') == 'paying') ? 'btn-primary' : 'btn-default' ?>" data-href="<?php echo buildUrl('cost', 'paying'); ?>"><i class="fas fa-euro-sign"></i><span>{{Payant}}</span></a>
                    <a class="btn bt_pluginFilter <?php echo (Utils::init('cost') == '') ? 'btn-primary' : 'btn-default' ?>" data-href="<?php echo buildUrl('cost', ''); ?>"><i class="fas fa-times"></i></a>
                </div>
            </div>
        <?php } ?>
        <div class="action-group">
            <div class="btn-group">
                <a class="btn btn-default bt_installFilter" data-state="-1"><i class="fas fa-thumbs-up"></i><span>{{Installé}}</span></a>
                <a class="btn btn-default bt_installFilter" data-state="1"><i class="fas fa-thumbs-down"></i><span>{{Non installé}}</span></a>
                <a class="btn btn-primary bt_installFilter" data-state="0"><i class="fas fa-times"></i></a>
            </div>
        </div>
        <div class="action-group">
            <div class="btn-group">
                <select class="form-control" id="sel_certif">
                    <option value="" selected>{{Tous}}</option>
                    <option value="Officiel">{{Officiel}}</option>
                    <option value="Conseillé">{{Conseillé}}</option>
                    <option value="Premium">{{Premium}}</option>
                    <option value="Partenaire">{{Partenaire}}</option>
                    <option value="Legacy">{{Legacy}}</option>
                </select>
            </div>
        </div>
        <div class="action-group">
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
        <div class="action-group">
            <div class="input-group">
                <a class="input-group-addon cursor" id="bt_resetSearch" data-href='<?php echo buildUrl('name', ''); ?>'><i class="fas fa-times"></i></a>
                <input class="form-control" data-href='<?php echo buildUrl('name', ''); ?>' placeholder="{{Rechercher...}}" id="in_search" value="<?php echo $name ?>"/>
                <a class="input-group-addon cursor" id="bt_search" data-href='<?php echo buildUrl('name', ''); ?>'><i class="fas fa-search"></i></a>
            </div>
        </div>
        <div class="action-group">
            <a class="btn btn-action btn-action-bar pull-right" style="display:none;" id="bt_marketCollapse"><i class="fas fa-plus-square"></i>{{Déplier}}</a>
            <a class="btn btn-action btn-action-bar pull-right" id="bt_marketUncollapse"><i class="fas fa-minus-square"></i>{{Replier}}</a>
        </div>
    </div>
</section>

<section class="content">
    <div class="box">
        <div class="box-header with-border">
            <?php
            echo '<h3 class="box-title"><i class="fas fa-shopping-cart"></i>';
            if ($type == "") {
                echo '{{Market Jeedom}}</h3>';
            } else {
                echo ucfirst($type) .'{{ Jeedom}}</h3>';
            }
            if (ConfigManager::byKey('market::username') != '') {
                echo '<span class="label label-info badge pull-right">' . ConfigManager::byKey('market::username');
                try {
                    RepoMarket::test();
                    echo '<i class="fas fa-check spacing-left"></i>';
                } catch (\Exception $e) {
                    echo '<i class="fas fa-times spacing-left"></i>';
                }
                echo '</span>';
            }
            ?>
        </div>
        <div class="box-body">
            <?php
            if (count($markets) >= $searchLimit) {
                echo '<div>';
                echo '<span class="alert alert-warning market-limited">{{Attention l\'affichage est limité à }}' . $searchLimit . ' {{résultats, utilisez les filtres ou la recherche si ce que vous cherchez n\'apparaît pas...}}</span>';
                echo '<a class="btn btn-action pull-right" id="bt_resetSearchLimit" data-href="' . buildUrl('limit', '0') .'"><i class="fas fa-times"></i>{{Sans limites}}</a>';
                echo '</div>';
            } else {
                echo '<div><span class="market-unlimited">' . count($markets);
                if ($type == "") {
                    echo '{{ objets}}';
                } else {
                    echo ' ' . $type . '{{s}}';
                }
                echo ' {{disponibles dans cette catégorie...}}</span>';
                if (count($markets) >= 100) {
                    echo '<a class="btn btn-action pull-right" id="bt_SearchLimit" data-href="' . buildUrl('limit', '100') .'"><i class="fas fa-filter"></i>{{Limiter à 100}}</a>';
                }
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <div>
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
                        echo '</div></div></div></div></div>';
                    }
                    echo '<div class="box-group" id="accordionPlugin' . $nCategory . '">';
                    echo '<div class="panel box" data-category="' . $nCategory . '">';
                    echo '<a class="box-header with-border accordion-toggle" data-toggle="collapse" data-parent="" href="#config_' . $nCategory . '">';
                    echo '<h3 class="box-title">';
                    echo '<span class="accordion-toggle" data-toggle="collapse" data-parent="#accordionPlugin' . $nCategory . '" href="#config_none" style="text-decoration:none;" data-category="' . $nCategory . '">';
                    if (isset($NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$categorie])) {
                        echo '<i class="fas ' . $NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$categorie]['icon'] . ' spacing-right"></i>' . ucfirst($NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$categorie]['name']) . '</span>';
                    } else {
                        echo ucfirst($categorie) . '</span>';
                    }
                    echo '</h3>';
                    echo '</a>';
                    echo '<div id="config_' . $nCategory . '" class="panel-collapse collapse in">';
                    echo '<div class="box-body">';
                    echo '<div class="pluginContainer DisplayCard text-center" data-category="' . $nCategory . '">';
                }
                $first = false;
                $nCategory++;
            }
            $explodedName = explode('.', $market->getName());
            if (count($explodedName) > 1) {
                $shortName = $explodedName[count($explodedName) - 1];
            } else {
                $shortName = $market->getName();
            }
            if (strpos($market->getName(), 'mobile.') === false) {
                $install = 'notInstall';
                if (!is_object($update)) {
                    $install = 'install';
                }
                $note = $market->getRating();
                $cost = $market->getCost();
                $realCost = $market->getRealCost();
                echo '<div class="marketOverload cursor ' . $install . '" data-market_id="' . $market->getId() . '" data-market_type="' . $market->getType() . '" data-name="' . $shortName . '">';
                switch ($market->getCertification()) {
                    case 'Officiel':
                        $certificationClass = 'official';
                        break;
                    case 'Conseillé':
                        $certificationClass = 'advised';
                        break;
                    case 'Legacy':
                        $certificationClass = 'legacy';
                        break;
                    case 'Obsolète':
                        $certificationClass = 'obsolete';
                        break;
                    case 'Premium':
                        $certificationClass = 'premium';
                        $cost = -1;
                        break;
                    case 'Partenaire':
                        $certificationClass = 'partner';
                        break;
                    default:
                        $certificationClass = '';
                }
                if ($market->getType() != 'widget') {
                    echo '<div class="market-certification market-' . $certificationClass .'">' . strtoupper($market->getCertification()) . '</div>';
                }
                if ($install == 'notInstall') {
                    echo '<i class="fas fa-check market-install"></i>';
                }
                switch ($market->getType()) {
                    case 'widget':
                        $default_image = 'public/img/NextDom_Widget_Gray.png';
                        break;
                    case 'plugin':
                        $default_image = 'public/img/NextDom_Plugin_Gray.png';
                        break;
                    case 'script':
                        $default_image = 'public/img/NextDom_Script_Gray.png';
                        break;
                    default:
                        $default_image = 'public/img/NextDom_NoPicture_Gray.gif';
                }
                $urlPath = ConfigManager::byKey('market::address') . '/' . $market->getImg('icon');
                echo '<div><img class="lazy lazyload market-icon" src="' . $default_image . '" data-original="'. $urlPath . '"/></div>';
                echo '<span class="market-name">' . $shortName . '</span>';
                echo '<span class="market-author"><i>{{par}}</i> ' . $market->getAuthor() . '</span>';
                echo '<span class="market-rating">';
                for ($i = 1; $i < 6; $i++) {
                    if ($i <= $note) {
                        echo '<i class="fas fa-star"></i>';
                    } else {
                        echo '<i class="far fa-star"></i>';
                    }
                }
                echo '</span>';
                if ($cost > 0) {
                    echo '<span class="market-cost">';
                        if ($market->getPurchase() == 1) {
                            echo '<i class="fas fa-check-circle"></i>';
                        } else {
                            if ($cost != $realCost) {
                                echo '<span style="text-decoration:line-through;">' . $realCost . ' {{€}}</span>';
                            }
                            echo $cost . ' {{€}}';
                        }
                    echo '</span>';
                } else {
                    if ($cost < 0) {
                        echo '<span class="market-cost">{{Nous contacter}}</span>';
                    } else {
                        echo '<span class="market-cost">{{Gratuit}}</span>';
                    }
                }
                echo '</div>';
            }
        }
        if ($default) {
            echo '</div>';
        }
        ?>
    </div>
</section>

<script>
    $(function () {
        $("img.lazy").lazyload();
        initTableSorter();
        setTimeout(function(){
            $('.pluginContainer').packery();
        },200);
        setTimeout(function () {
            $('#table_market tbody tr.install').hide();
        }, 500);
        $('.bt_pluginFilter').on('click', function () {
            $('#md_modal').load($(this).attr('data-href'));
        });
        $('#sel_certif').on('change', function () {
            $('#md_modal').load($(this).attr('data-href') + '&certification=' + encodeURI($(this).value()));
        });
        $('#sel_categorie').on('change', function () {
            $('#md_modal').load($(this).attr('data-href') + '&categorie=' + encodeURI($(this).value()));
        });
        $('#bt_search').on('click', function () {
            $('#md_modal').load($(this).attr('data-href') + '&categorie=' + '&name=' + encodeURI($('#in_search').value()));
        });
        $('#bt_resetSearch').on('click', function () {
            $('#md_modal').load($(this).attr('data-href'));
        });
        $('#in_search').keyup(function (e) {
            marketFilterRepo();
        });
        $('#bt_returnMarketList').on('click', function () {
            $('#md_modal').load($(this).attr('data-href'));
        });
        $('.marketMultiple').on('click', function () {
            $('#md_modal').load($(this).attr('data-href') + '&name=' + encodeURI('.' + $(this).attr('data-market_name')));
        });
        $('.bt_installFilter').on('click', function () {
            $('.bt_installFilter').removeClass('btn-primary').removeClass('btn-default');
            $('.pluginContainer').show();
            $('.marketOverload').show();
            if ($(this).attr('data-state') == 1) {
                $('.notInstall').hide();
            }
            if ($(this).attr('data-state') == -1) {
                $('.install').hide();
            }
            $(this).addClass('btn-primary');
            $('.bt_installFilter').each(function () {
                if (!$(this).hasClass("btn-primary")) {
                    $(this).addClass('btn-default');
                }
            });
            $('.pluginContainer').each(function () {
                var hasVisible = false;
                $(this).find('.marketOverload').each(function () {
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
        $('.marketOverload').on('click', function () {
            $('#md_modal2').dialog({title: "{{Market Jeedom}}"});
            $('#md_modal2').load('index.php?v=d&modal=update.display&type=' + $(this).attr('data-market_type') + '&id=' + $(this).attr('data-market_id') + '&repo=market').dialog('open');
        });
        $('#bt_marketCollapse').on('click',function(){
           $('.panel-collapse').each(function () {
              if (!$(this).hasClass("in")) {
                  $(this).css({'height' : '' });
                  $(this).addClass("in");
              }
           });
           $('#bt_marketCollapse').hide();
           $('#bt_marketUncollapse').show()
        });
        $('#bt_marketUncollapse').on('click',function(){
           $('.panel-collapse').each(function () {
              if ($(this).hasClass("in")) {
                  $(this).removeClass("in");
              }
           });
           $('#bt_marketUncollapse').hide();
           $('#bt_marketCollapse').show()
        });
        $('#bt_resetSearchLimit').on('click', function () {
            $('#md_modal').load($(this).attr('data-href'));
        });
        $('#bt_SearchLimit').on('click', function () {
            $('#md_modal').load($(this).attr('data-href'));
        });
    });

    function marketFilterRepo() {
        var pluginValue = '';
        var currentSearchValue = $('#in_search').val().toLowerCase();
        $('.marketOverload').show();
        $('.marketOverload').each(function () {
            if (currentSearchValue != '') {
                pluginValue = $(this).attr('data-name').toLowerCase();
                if (pluginValue.indexOf(currentSearchValue) == -1) {
                    $(this).hide();
                }
            }
        });
        $('.pluginContainer').packery();
    };
</script>
