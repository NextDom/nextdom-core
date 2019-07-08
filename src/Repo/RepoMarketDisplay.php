<?php

namespace NextDom\Repo;

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\UpdateManager;

AuthentificationHelper::isConnectedAsAdminOrFail();

if (Utils::init('id') != '') {
    $market = RepoMarket::byId(Utils::init('id'));
}
if (Utils::init('logicalId') != '' && Utils::init('type') != '') {
    $market = RepoMarket::byLogicalIdAndType(Utils::init('logicalId'), Utils::init('type'));
}
if (!isset($market)) {
    throw new CoreException('404 not found');
}

$market_array = Utils::o2a($market);
$market_array['rating'] = $market->getRating();
$update = UpdateManager::byLogicalId($market->getLogicalId());
Utils::sendVarToJS('market_display_info', $market_array);
?>

<link rel="stylesheet" href="/vendor/node_modules/fancybox/dist/css/jquery.fancybox.css">
<link rel="stylesheet" href="/vendor/node_modules/slick-carousel/slick/slick.css">
<link rel="stylesheet" href="/vendor/node_modules/slick-carousel/slick/slick-theme.css">
<script src="/assets/3rdparty/bootstrap-rating/bootstrap-rating.js"></script>
<script src="/vendor/node_modules/slick-carousel/slick/slick.js"></script>
<script src="/vendor/node_modules/fancybox/dist/js/jquery.fancybox.js"></script>

<div class='row' style='padding-top: 10px; padding-bottom: 10px;position: relative; top: -10px;'>
    <div class='col-sm-2'>
        <center>
            <?php
            $default_image = 'public/img/NextDom_NoPicture_Gray.png';
            switch ($market->getType()) {
                case 'widget':
                    $default_image = '/public/img/NextDom_Widget_Gray.png';
                    break;
                case 'plugin':
                    $default_image = '/public/img/NextDom_Plugin_Gray.png';
                    break;
                case 'script':
                    $default_image = '/public/img/NextDom_Script_Gray.png';
                    break;
            }
            $urlPath = ConfigManager::byKey('market::address') . '/' . $market->getImg('icon');
            echo '<img src="' . $default_image . '" data-original="' . $urlPath . '"  class="lazy img-responsive" style="height : 150px;"/>';
            ?>
        </center>
    </div>
    <div class='col-sm-4'>
        <input class="form-control marketAttr" data-l1key="id" style="display: none;">
        <div class="marketAttr form-group" data-l1key="name" placeholder="{{Nom}}" style="font-size: 2em;font-weight: bold;"></div>
        <div class="span_author cursor form-group text-medium text-bold text-gray" data-author="<?php echo $market->getAuthor(); ?>">{{Développé par}} <?php echo $market->getAuthor(); ?></div>
        <?php
        if ($market->getCertification() == 'Officiel') {
            echo '<div class="form-group text-normal text-bold text-gray">{{Officiel}}</div>';
        }
        if ($market->getCertification() == 'Conseillé') {
            echo '<div class="form-group text-normal text-bold text-gray">{{Conseillé}}</div>';
        }
        if ($market->getCertification() == 'Legacy') {
            echo '<div class="form-group text-normal text-bold text-gray">{{Legacy}}</div>';
        }
        if ($market->getCertification() == 'Obsolète') {
            echo '<div class="form-group text-normal text-bold text-critical">{{Obsolète}}</div>';
        }
        if ($market->getCertification() == 'Premium') {
            echo '<div style="form-group text-normal text-bold text-gray">{{Premium}}</div>';
        }
        if ($market->getCertification() == 'Partenaire') {
            echo '<div style="form-group text-normal text-bold text-gray">{{Partenaire}}</div>';
        }
        global $NEXTDOM_INTERNAL_CONFIG;
        if (isset($NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$market->getCategorie()])) {
            echo '<div class="form-group text-normal text-bold text-gray"><i class="fa ' . $NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$market->getCategorie()]['icon'] . '"></i> ' . $NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$market->getCategorie()]['name'] . '</div>';
            Utils::sendVarToJS('market_display_info_category', $NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$market->getCategorie()]['name']);
        } else {
            echo '<div class="form-group text-normal text-bold text-gray">' . $market->getCategorie() . '</div>';
            Utils::sendVarToJS('market_display_info_category', $market->getCategorie());
        }
        ?>
    </div>
    <div class='col-sm-6'>
        <div class='form-group'>
            <?php
            if ($market->getPurchase() == 1) {
                $allowVersion = $market->getAllowVersion();
                foreach ($allowVersion as $branch) {
                    if ($market->getStatus($branch) == 1) {
                        echo '<a class="btn btn-default bt_installFromMarket spacing-right" data-version="' . $branch . '" data-market_logicalId="' . $market->getLogicalId() . '" data-market_id="' . $market->getId() . '" ><i class="fas fa-plus-circle spacing-right"></i>{{Installer}} ' . $branch . '</a>';
                    }
                }
            } else if ($market->getPrivate() === 1) {
                echo '<div class="alert alert-info">{{Ce plugin est pour le moment privé. Vous devez attendre qu\'il devienne public ou avoir un code pour y accéder}}</div>';
            } else {
                if (ConfigManager::byKey('market::apikey') != '' || (ConfigManager::byKey('market::username') != '' && ConfigManager::byKey('market::password') != '')) {
                    $purchase_info = RepoMarket::getPurchaseInfo();
                    if (isset($purchase_info['user_id']) && is_numeric($purchase_info['user_id'])) {
                        if ($market->getCost() > 0 && $market->getPurchase() != 1) {
                            echo '<a class="btn btn-action spacing-right" href="https://www.jeedom.com/market/index.php?v=d&p=profils#buyHistory" target="_blank"><i class="fas fa-eur spacing-right"></i>{{Code promo}}</a>';
                        }
                        if ($market->getCertification() === 'Premium') {
                            echo '<a class="btn btn-default spacing-right" target="_blank" href="mailto:supportpro@jeedom.com"><i class="fas fa-envelope spacing-right"></i>{{Nous Contacter}}</a>';
                        } else {
                            echo '<a class="btn btn-default" target="_blank" href="' . ConfigManager::byKey('market::address') . '/index.php?v=d&p=purchaseItem&user_id=' . $purchase_info['user_id'] . '&type=plugin&id=' . $market->getId() . '"><i class="fas fa-shopping-cart spacing-right"></i>{{Acheter}}</a>';
                        }
                    } else {
                        echo '<div class="alert alert-info">{{Cet article est payant. Vous devez avoir un compte sur le market et avoir renseigné les identifiants market dans NextDom pour pouvoir l\'acheter}}</div>';
                    }
                } else {
                    echo '<div class="alert alert-info">{{Cet article est payant. Vous devez avoir un compte sur le market et avoir renseigné les identifiants market dans NextDom pour pouvoir l\'acheter}}</div>';
                }
            }
            if (is_object($update)) {
                ?>
                <a class="btn btn-danger" id="bt_removeFromMarket" data-market_id="<?php echo $market->getId(); ?>"><i class="fas fa-minus-circle spacing-right"></i>{{Supprimer}}</a>
            <?php }
            echo '</div>';
            echo '<div class="form-group"><i class="fas fa-credit-card spacing-left spacing-right"></i>';
            if ($market->getCertification() === 'Premium') {
                echo '<span data-l1key="rating" style="font-size: 1.5em;">{{Nous Contacter}}</span>';
            } else {
                if ($market->getCost() > 0) {
                    if ($market->getCost() != $market->getRealCost()) {
                        echo '<span class="form-group" data-l1key="rating" style="font-size: 1em;text-decoration:line-through;">' . number_format($market->getRealCost(), 2) . ' €</span> ';
                    }
                    echo '<span class="form-group" data-l1key="rating" style="font-size: 1.2em;">' . number_format($market->getCost(), 2) . ' € TTC</span>';
                } else {
                    echo '<span class="form-group" data-l1key="rating" style="font-size: 1.2em;">{{Gratuit}}</span>';
                }
            }
            echo '</div>';
            ?>
        </div>
    </div>
    <?php
    if ($market->getCertification() !== 'Officiel' && $market->getCertification() !== 'Premium' && $market->getCertification() !== 'Legacy') {
        echo '<div class="alert alert-warning">{{Attention ce plugin n\'est pas un plugin officiel en cas de soucis avec celui-ci (direct ou indirect) toute demande de support peut être refusée}}</div>';
    }
    $compatibilityHardware = $market->getHardwareCompatibility();
    if (is_array($compatibilityHardware) && count($compatibilityHardware) > 0 && isset($compatibilityHardware[NextDomHelper::getHardwareName()]) && $compatibilityHardware[NextDomHelper::getHardwareName()] != 1) {
        echo '<div class="alert alert-danger">{{Attention ce plugin ne semble pas être compatible avec votre système}}</div>';
    }
    ?>
    <div style="display: none;width : 100%" id="div_alertMarketDisplay"></div>

    <?php if (count($market->getImg('screenshot')) > 0) {
        ?>
        <div class="market-slick">
            <div class="variable-width" style="height : 200px;">
                <?php
                foreach ($market->getImg('screenshot') as $screenshot) {
                    echo '<div class="item" >';
                    echo '<a class="fancybox cursor" href="' . ConfigManager::byKey('market::address') . '/' . $screenshot . '" rel="group" >';
                    echo '<img data-lazy="' . ConfigManager::byKey('market::address') . '/' . $screenshot . '" style="height : 200px;" />';
                    echo '</a>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    <?php }
    ?>
    <div class='row form-group'>
        <div class='col-sm-6'>
            <legend>{{Description}}
                <a class="btn btn-default btn-xs pull-right" target="_blank" href="<?php echo $market->getDoc() ?>"><i class="fas fa-book spacing-right"></i>{{Documentation}}</a>
                <a class="btn btn-default btn-xs pull-right" target="_blank" href="<?php echo $market->getChangelog() ?>"><i class="fas fa-list spacing-right"></i>{{Changelog}}</a>
            </legend>
            <span class="marketAttr" data-l1key="description" style="word-wrap: break-word;white-space: -moz-pre-wrap;white-space: pre-wrap;"></span>
        </div>
        <div class='col-sm-6'>
            <legend>{{Compatibilité plateforme}}</legend>
            <?php
            if ($market->getHardwareCompatibility('diy') == 1) {
                echo '<img src="public/img/logo_diy.png" style="width:60px;height:60px;" />';
            }
            if ($market->getHardwareCompatibility('rpi') == 1) {
                echo '<img src="public/img/logo_rpi12.png" style="width:60px;height:60px;" />';
            }
            if ($market->getHardwareCompatibility('docker') == 1) {
                echo '<img src="public/img/logo_docker.png" style="width:60px;height:60px;" />';
            }
            if ($market->getHardwareCompatibility('miniplus') == 1) {
                echo '<img src="public/img/logo_nextdomboard.png" style="width:60px;height:60px;" />';
            }
            ?>
        </div>
    </div>
    <div class='row form-group'>
        <div class='col-sm-6'>
            <legend>{{Avis}}</legend>
            <div class='row'>
                <div class='col-sm-6'>
                    <center>
                        <span class="marketAttr" data-l1key="rating" style="font-size: 4em;"></span>/5
                    </center>
                </div>
                <div class='col-sm-6'>
                    <?php if (ConfigManager::byKey('market::apikey') != '' || (ConfigManager::byKey('market::username') != '' && ConfigManager::byKey('market::password') != '')) { ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">{{Ma Note}}</label>
                            <div class="col-sm-8">
                                <span><input style="display:none;" type="number" class="rating" id="in_myRating" data-max="5" data-empty-value="0" data-min="1" data-clearable="Effacer" value="<?php echo $market->getRating('user') ?>"/></span>
                            </div>
                        </div><br/>
                    <?php }
                    ?>
                </div>
            </div>
        </div>
        <div class='col-sm-6'>
            <legend>{{Utilisation}}</legend>
            <span class="marketAttr" data-l1key="utilization" style="word-wrap: break-word;white-space: -moz-pre-wrap;white-space: pre-wrap;"></span>
        </div>
    </div>
    <div class='row form-group'>
        <div class='col-sm-12'>
            <legend>{{Informations complementaires}}</legend>
            <div class='col-sm-2'>
                <label class="control-label">{{Taille}}</label><br/>
                <span><?php echo $market->getParameters('size'); ?></span>
            </div>
            <div class='col-sm-2'>
                <label class="control-label">{{Lien}}</label><br/>
                <?php if ($market->getLink('video') != '' && $market->getLink('video') != 'null') { ?>
                    <a class="btn btn-default btn-xs" target="_blank" href="<?php echo $market->getLink('video'); ?>"><i class="fa fa-youtube"></i> Video</a><br/>
                <?php }
                ?>
                <?php if ($market->getLink('forum') != '' && $market->getLink('forum') != 'null') { ?>
                    <a class="btn btn-default btn-xs" target="_blank" href="<?php echo $market->getLink('forum'); ?>"><i class="fa fa-users"></i> Forum</a><br/>
                <?php }
                ?>
            </div>
            <div class='col-sm-2'>
                <label class="control-label">{{Installation}}</label>
                <span class="marketAttr"><?php echo $market->getNbInstall() ?></span>
            </div>

            <div class='col-sm-1'>
                <label class="control-label">{{Type}}</label><br/>
                <span class="marketAttr" data-l1key="type"></span>
            </div>
            <div class='col-sm-2'>
                <label class="control-label">{{Langue disponible}}</label><br/>
                <?php
                echo '<img src="public/img/francais.png" width="30" />';
                if ($market->getLanguage('en_US') == 1) {
                    echo '<img src="public/img/anglais.png" width="30" />';
                }
                if ($market->getLanguage('de_DE') == 1) {
                    echo '<img src="public/img/allemand.png" width="30" />';
                }
                if ($market->getLanguage('sp_SP') == 1) {
                    echo '<img src="public/img/espagnol.png" width="30" />';
                }
                if ($market->getLanguage('ru_RU') == 1) {
                    echo '<img src="public/img/russe.png" width="30" />';
                }
                if ($market->getLanguage('id_ID') == 1) {
                    echo '<img src="public/img/indonesien.png" width="30" />';
                }
                if ($market->getLanguage('it_IT') == 1) {
                    echo '<img src="public/img/italien.png" width="30" />';
                }
                ?>
            </div>
            <div class='col-sm-3'>
                <label class="control-label">{{Dernière mise à jour le}}</label><br/>
                <?php echo $market->getDatetime('stable') ?>
            </div>
        </div>

    </div>

    <style>
        .slick-prev:before, .slick-next:before {
            color: #707070;
        }
    </style>
    <script>

      $("img.lazy").lazyload({
        event: "sporty"
      });
      $("img.lazy").trigger("sporty");

      $(document).unbind('click.fb-start');
      $(".fancybox").fancybox({
        autoHeight: true,
      });

      $('.variable-width').slick({
        dots: true,
        speed: 300,
        accessibility: true,
        infinite: true,
        lazyLoad: 'ondemand',
        slidesToShow: 3,
        slidesToScroll: 1
      });

      $('body').setValues(market_display_info, '.marketAttr');

      $('#div_alertMarketDisplay').closest('.ui-dialog').find('.ui-dialog-title').text('Market NextDom - ' + market_display_info_category);

      $('.marketAttr[data-l1key=description]').html(linkify(market_display_info.description));
      $('.marketAttr[data-l1key=utilization]').html(linkify(market_display_info.utilization));

      $('#bt_paypalClick').on('click', function () {
        $(this).hide();
      });


      $('.bt_installFromMarket').on('click', function () {
        var id = $(this).attr('data-market_id');
        var logicalId = $(this).attr('data-market_logicalId');
        nextdom.repo.install({
          id: id,
          repo: 'market',
          version: $(this).attr('data-version'),
          error: function (error) {
            notify('Core', error.message, 'error');
          },
          success: function (data) {
            if (market_display_info.type == 'plugin') {
              bootbox.confirm('{{Voulez-vous aller sur la page de configuration de votre nouveau plugin ?}}', function (result) {
                if (result) {
                  loadPage('index.php?v=d&p=plugin&id=' + logicalId);
                }
              });
            }
            if (typeof refreshListAfterMarketObjectInstall == 'function') {
              refreshListAfterMarketObjectInstall()
            }
            notify("Core", '{{Objet installé avec succès}}', "success");
          }
        });

      });

      $('#bt_removeFromMarket').on('click', function () {
        var id = $(this).attr('data-market_id');
        nextdom.repo.remove({
          id: id,
          repo: 'market',
          error: function (error) {
            notify('Core', error.message, 'error');
          },
          success: function (data) {
            showLoadingCustom();
            window.location.reload();
          }
        });
      });

      $('#in_myRating').on('change', function () {
        var id = $('.marketAttr[data-l1key=id]').value();
        nextdom.repo.setRating({
          id: id,
          repo: 'market',
          rating: $(this).val(),
          error: function (error) {
            notify('Core', error.message, 'error');
          }
        });
      });

      $('.span_author').off('click').on('click', function () {
        $('#md_modal2').dialog('close');
        $('#md_modal').dialog({title: "{{Market}}"});
        $('#md_modal').load('index.php?v=d&modal=update.list&type=plugin&repo=market&author=' + encodeURI($(this).attr('data-author'))).dialog('open');
      });
    </script>
