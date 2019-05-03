<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

$type = init('type', null);
$categorie = init('categorie', null);
$name = init('name', null);
$author = init('author', null);
if ($name == 'false') {
    $name = null;
}
if ($author == null && $name === null && $categorie === null && init('certification', null) === null && init('cost', null) === null && $type == 'plugin') {
    $default = true;
    $markets = repo_market::byFilter(array(
        'status' => 'stable',
        'type' => 'plugin',
        'timeState' => 'popular',
    ));
    $markets2 = repo_market::byFilter(array(
        'status' => 'stable',
        'type' => 'plugin',
        'timeState' => 'newest',
    ));
    $markets = array_merge($markets, $markets2);
} else {
    $default = false;
    $markets = repo_market::byFilter(
        array(
            'status' => null,
            'type' => $type,
            'categorie' => $categorie,
            'name' => $name,
            'author' => $author,
            'cost' => init('cost', null),
            'timeState' => init('timeState'),
            'certification' => init('certification', null),
            'limit' => 100,
        )
    );
}

function buildUrl($_key, $_value) {
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

function displayWidgetName($_name) {
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

function displayWidgetType($_name) {
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

function displayWidgetSubtype($_name) {
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

<section class="content-header">
    <div class="action-bar">
        <div class="action-group">
            <a class="btn btn-danger btn-action-bar" href="index.php?v=d&p=plugin"><i class="fas fa-chevron-left spacing-right"></i>{{Retour}}</a>
            <?php if (init('type', 'plugin') == 'plugin') {?>
                <div class="btn-group">
                    <a class="btn btn-default bt_pluginFilterCost" data-filter="free">{{Gratuit}}</a>
                    <a class="btn btn-default bt_pluginFilterCost" data-filter="paying">{{Payant}}</a>
                    <a class="btn btn-primary bt_pluginFilterCost" data-filter=""><i class="fa fa-times"></i></a>
                </div>
                <div class="btn-group">
                    <a class="btn btn-default bt_pluginFilterCertification" data-filter="Officiel">{{Officiel}}</a>
                    <a class="btn btn-default bt_pluginFilterCertification" data-filter="Conseillé">{{Conseillé}}</a>
                    <a class="btn btn-default bt_pluginFilterCertification" data-filter="Legacy">{{Legacy}}</a>
                    <a class="btn btn-primary bt_pluginFilterCertification" data-filter=""><i class="fa fa-times"></i></a>
                </div>
                <div class="btn-group">
                    <a class="btn btn-default bt_pluginFilterInstall" data-filter="notInstall">{{Installé}}</a>
                    <a class="btn btn-default bt_pluginFilterInstall" data-filter="install">{{Non installé}}</a>
                    <a class="btn btn-primary bt_pluginFilterInstall" data-filter=""><i class="fa fa-times"></i></a>
                </div>
            <?php }?>
            <div class="btn-group">
                <div class="input-group" >
                    <?php
                    $oldSearch = '';
                    if ($name != '') {
                        $oldSearch = $name;
                    } else {
                        if ($author != '') {
                            $oldSearch = $author;
                        }
                    }
                    echo '<input type="text" class="form-control" id="pluginSearch" placeholder="Recherche globale..." data-value=' . $oldSearch .'>'
                    ?>
                    <div class="input-group-btn">
                        <a class="btn btn-success" id="pluginNameSearch"><i class="fas fa-search-plus"></i></a>
                        <a class="btn btn-success" id="authorSearch"><i class="fas fa-user"></i></a>
                        <a class="btn btn-action" id="resetSearch"><i class="fas fa-times"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="action-group">
          <div class="btn-group">
              <select class="form-control" id="sel_categorie">
                  <?php
                      if (init('categorie') == '') {
                          echo '<option value="" selected>{{Top & Nouveautés}}</option>';
                      } else {
                          echo '<option value="">{{Top & Nouveautés}}</option>';
                      }
                      if ($type !== null && $type != 'plugin') {
                          foreach (repo_market::distinctCategorie($type) as $id => $category) {
                              if (trim($category) != '' && is_numeric($id)) {
                                  echo '<option value="' . $category . '"';
                                  echo (init('categorie') == $category) ? 'selected >' : '>';
                                  echo $category;
                                  echo '</option>';
                              }
                          }
                      } else {
                          global $NEXTDOM_INTERNAL_CONFIG;
                          foreach ($NEXTDOM_INTERNAL_CONFIG['plugin']['category'] as $key => $value) {
                              echo '<option value="' . $key . '"';
                              echo (init('categorie') == $key) ? 'selected >' : '>';
                              echo $value['name'];
                              echo '</option>';
                          }
                      }
                  ?>
              </select>
          </div>
        </div>
    </div>
</section>
<?php
if ($name !== null && strpos($name, '$') !== false) {
    echo '<a class="btn btn-default" id="bt_returnMarketList" style="margin-top : 50px;" data-href=' . buildUrl('name', '') . '><i class="fa fa-arrow-circle-left spacing-right"></i>{{Retour}}</a>';
}
?>
<section class="content">
    <div class="box">
        <div class="box-header">
            <div class="form-group">
            <h3 class="box-title"><i class="fas fa-shopping-cart spacing-right"></i>{{Market Jeedom}}</h3>
            <?php
                if (config::byKey('market::username') != '') {
                    echo '<span class="label label-info pull-right label-sticker pull-right">' . config::byKey('market::username');
                    try {
                        repo_market::test();
                        echo ' <i class="fa fa-check"></i>';
                    } catch (Exception $e) {
                        echo ' <i class="fa fa-times"></i>';
                    }
                    echo '</span>';
                }
            ?>
            </div>
            <p class="alert alert-warning" style="margin-bottom:0px">{{Attention l'affichage est limité à 100 résultats, utilisez les filtres ou la recherche si ce que vous cherchez n'apparaît pas...}}</p>
        </div>
    </div>

    <?php
$categorie = '';
$first = true;
$nCategory = 0;
if ($default) {
    echo '<div class="pluginContainer">';
}
foreach ($markets as $market) {
    $update = update::byLogicalId($market->getLogicalId());
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
            echo '<div class="panel box">';
            echo '<a class="box-header with-border accordion-toggle" data-toggle="collapse" data-parent="" href="#config_' . $nCategory . '">';
            echo '<h3 class="box-title">';
            if (isset($NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$categorie])) {
                echo '<span class="accordion-toggle" data-toggle="collapse" data-parent="#accordionPlugin' . $nCategory . '" href="#config_none" style="text-decoration:none;" data-category="' . $nCategory . '"><i class="fa ' . $NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$categorie]['icon'] . ' spacing-right"></i> ' . ucfirst($NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$categorie]['name']) . '</span>';
            } else {
                echo '<span class="accordion-toggle" data-toggle="collapse" data-parent="#accordionPlugin' . $nCategory . '" href="#config_none" style="text-decoration:none;" data-category="' . $nCategory . '">' . ucfirst($categorie) . '</span>';
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

    $installClass = 'notInstall';
    if (!is_object($update)) {
        $installClass = 'install';
    }
    echo '<div class="market cursor ' . $installClass . '" data-install="' . $installClass . '" data-category="' . $market->getCategorie() . '" data-cost="' . $market->getCost() . '" data-certification="' . $market->getCertification() . '" data-name="' . $market->getName() . '" data-market_id="' . $market->getId() . '" data-market_type="' . $market->getType() . '">';
    if ($market->getType() != 'widget') {
        if ($market->getCertification() == 'Officiel') {
            $certificationClass = 'official';
        }
        if ($market->getCertification() == 'Conseillé') {
            $certificationClass = 'advised';
        }
        if ($market->getCertification() == 'Legacy') {
            $certificationClass = 'legacy';
        }
        if ($market->getCertification() == 'Obsolète') {
            $certificationClass = 'obsolete';
        }
        echo '<div class="market-certification market-' . $certificationClass . '">' . strtoupper($market->getCertification()) . '</div>';
    } else {
        if (strpos($market->getName(), 'mobile.') !== false) {
            echo '<i class="fas fa-mobile market-widget" title="{{Widget pour la version mobile}}"></i>';
        } else {
            echo '<i class="fas fa-desktop market-widget" title="{{Widget pour la version bureau}}"></i>';
        }
    }
    if (is_object($update)) {
        echo '<i class="fas fa-check market-install"></i>';
    }
    echo "<div>";
    $default_image = 'public/img/NextDom_NoPicture_Gray.png';
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
    }

    $urlPath = config::byKey('market::address') . '/' . $market->getImg('icon');
    echo '<img class="lazy market-icon" src="' . $default_image . '" data-original="' . $urlPath . '"/>';
    echo "</div>";

    echo '<span class="market-name">' . $market->getName().split('.').pop() . '</span>';
    echo '<span class="market-author"><i>{{par }}</i>' . $market->getAuthor() . '</span>';

    $note = $market->getRating();
    echo '<span class="market-rating">';
    for ($i = 1; $i < 6; $i++) {
        if ($i <= $note) {
            echo '<i class="fas fa-star"></i>';
        } else {
            echo '<i class="far fa-star"></i>';
        }
    }
    echo '</span>';
    if ($market->getCost() > 0) {
        echo '<span class="market-cost">';
        if ($market->getPurchase() == 1) {
            echo ' <i class="fa fa-check-circle"></i>';
        } else {
            if ($market->getCost() != $market->getRealCost()) {
                echo '<span style="text-decoration:line-through;">' . number_format($market->getRealCost(), 2) . ' €</span> ';
            }
            echo number_format($market->getCost(), 2) . ' €';
        }
        echo '</span>';
    } else {
        echo '<span class="market-cost">Gratuit</span>';
    }
    echo '</div>';
}
if ($default) {
    echo '</div>';
}
?>
</section>

<script>
    $(function () {
        $('.pluginContainer').packery();

        $("img.lazy").lazyload({
            event: "sporty"
        });
        $("img.lazy").trigger("sporty");
        initTableSorter();
        marketFilter();

        $('#pluginSearch').value($('#pluginSearch').attr('data-value'));

        $('.bt_pluginFilterCost').on('click', function () {
            $('.bt_pluginFilterCost').removeClass('btn-primary');
            $('.bt_pluginFilterCost').addClass('btn-default');
            $(this).addClass('btn-primary');
            $(this).removeClass('btn-default');
            marketFilter();
        });

        $('.bt_pluginFilterCertification').on('click', function () {
            $('.bt_pluginFilterCertification').removeClass('btn-primary');
            $('.bt_pluginFilterCertification').addClass('btn-default');
            $(this).addClass('btn-primary');
            $(this).removeClass('btn-default');
            marketFilter();
        });

        $('.bt_pluginFilterInstall').on('click', function () {
            $('.bt_pluginFilterInstall').removeClass('btn-primary');
            $('.bt_pluginFilterInstall').addClass('btn-default');
            $(this).addClass('btn-primary');
            $(this).removeClass('btn-default');
            marketFilter();
        });

        $('#sel_categorie').on('change', function () {
            loadPage('index.php?v=d&modal=update.list' + '&categorie=' + encodeURI($(this).value()));
        });

        $('#bt_returnMarketList').on('click', function () {
            $('#md_modal').load($(this).attr('data-href'));
        });

        $('.market').on('click', function () {
            $('#md_modal2').dialog({title: "{{Market NextDom}}"});
            $('#md_modal2').load('index.php?v=d&modal=update.display&type=' + $(this).attr('data-market_type') + '&id=' + $(this).attr('data-market_id')+'&repo=market').dialog('open');
        });

        $('#pluginNameSearch').on('click', function () {
            loadPage('index.php?v=d&modal=update.list' + '&name=' + encodeURI($('#pluginSearch').value()));
            $('#generalSearch').value('');
        });

        $('#authorSearch').on('click', function () {
            loadPage('index.php?v=d&modal=update.list' + '&author=' + encodeURI($('#pluginSearch').value()));
            $('#generalSearch').value('');
        });

        $('#resetSearch').on('click', function () {
            loadPage('index.php?v=d&modal=update.list');
            $('#generalSearch').value('');
        });

        $('.accordion-toggle').off('click').on('click', function () {
         setTimeout(function(){
           $('.pluginContainer').packery();
         },100);
       });
    });

    function marketFilter() {
        var filterCost = '';
        var filterCertification = '';
        var filterInstall = '';
        var pluginValue = '';
        $('.market').hide();
        $('.bt_pluginFilterCost').each(function () {
            if ($(this).hasClass("btn-primary")) {
                filterCost = $(this).attr('data-filter');
            }
        });
        $('.bt_pluginFilterCertification').each(function () {
            if ($(this).hasClass("btn-primary")) {
                filterCertification = $(this).attr('data-filter');
            }
        });
        $('.bt_pluginFilterInstall').each(function () {
            if ($(this).hasClass("btn-primary")) {
                filterInstall = $(this).attr('data-filter');
            }
        });
        var filterCategory = $('#sel_categorie').value();
        var currentSearchValue = $('#generalSearch').val().toLowerCase();
        $('.market').show();

        $('.market').each(function () {
            if (currentSearchValue != '') {
                pluginValue = $(this).attr('data-name').toLowerCase();
                if (pluginValue.indexOf(currentSearchValue) == -1) {
                    $(this).hide();
                }
            }

            if (filterCertification != '') {
                pluginValue = $(this).attr('data-certification');
                if (pluginValue.indexOf(filterCertification) == -1) {
                    $(this).hide();
                }
            }

            if (filterCost != '') {
                pluginValue = $(this).attr('data-cost');
                if ((pluginValue == 0 && filterCost == 'paying') || (pluginValue > 0 && filterCost == 'free')) {
                    $(this).hide();
                }
            }

            if (filterCategory != '') {
                pluginValue = $(this).attr('data-category');
                if (pluginValue.indexOf(filterCategory) == -1) {
                    $(this).hide();
                }
            }

            if (filterInstall != '') {
                pluginValue = $(this).attr('data-install');
                if (pluginValue.indexOf(filterInstall) == -1) {
                    $(this).hide();
                }
            }
        });
        $('.pluginContainer').packery();
    };
</script>
