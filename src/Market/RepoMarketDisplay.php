<?php

namespace NextDom\Repo;

use NextDom\Enums\AjaxParams;
use NextDom\Enums\ConfigKey;
use NextDom\Enums\JeedomMarketCert;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\AuthentificationHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\UpdateManager;

AuthentificationHelper::isConnectedAsAdminOrFail();

if (Utils::init(AjaxParams::ID) != '') {
    $market = RepoMarket::byId(Utils::init('id'));
}
if (Utils::init(AjaxParams::LOGICAL_ID) != '' && Utils::init(AjaxParams::TYPE) != '') {
    $market = RepoMarket::byLogicalIdAndType(Utils::init(AjaxParams::LOGICAL_ID), Utils::init(AjaxParams::TYPE));
}
if (!isset($market)) {
    throw new CoreException('404 not found');
}

$marketInformations = Utils::o2a($market);
$marketInformations['rating'] = $market->getRating();
$update = UpdateManager::byLogicalId($market->getLogicalId());
Utils::sendVarToJS('market_display_info', $marketInformations);
$marketCertification = $market->getCertification();

switch ($market->getType()) {
    case 'widget':
        $defaultImage = '/public/img/NextDom_Widget_Gray.png';
        break;
    case 'plugin':
        $defaultImage = '/public/img/NextDom_Plugin_Gray.png';
        break;
    case 'script':
        $defaultImage = '/public/img/NextDom_Script_Gray.png';
        break;
    default:
        $defaultImage = 'public/img/NextDom_NoPicture_Gray.png';
        break;
}
$urlPath = ConfigManager::byKey(ConfigKey::MARKET_ADDRESS) . '/' . $market->getImg('icon');

$certificationClass = [
    JeedomMarketCert::OFFICIAL => 'official',
    JeedomMarketCert::ADVISED => 'advised',
    JeedomMarketCert::LEGACY => 'legacy',
    JeedomMarketCert::OBSOLETE => 'obsolete',
    JeedomMarketCert::PREMIUM => 'premium',
    JeedomMarketCert::PARTNER => 'partner'
];

?>
<style>
    .centered {
        text-align: center;
    }
</style>
<div class="row form-group">
    <div class="col-sm-2 centered">
        <?php
        echo '<img src="' . $defaultImage . '" data-original="' . $urlPath . '"  class="lazy img-responsive" style="height: 150px;"/>';
        ?>
    </div>
    <div class='col-sm-4'>
        <input class="form-control marketAttr" data-l1key="id" style="display: none;">
        <div class="marketAttr form-group market-modale-name" data-l1key="name"></div>
        <div class="span_author cursor form-group market-modale-author" data-author="<?php echo $market->getAuthor(); ?>">{{Développé par}} <?php echo $market->getAuthor(); ?></div>
        <?php
        if ($marketCertification !== '' && array_key_exists($marketCertification, $certificationClass)) {
            echo '<div class="form-group market-modale-certification market-' . $certificationClass[$marketCertification] . '">' . $marketCertification . '</div>';
        }
        global $NEXTDOM_INTERNAL_CONFIG;
        if (isset($NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$market->getCategorie()])) {
            echo '<div class="form-group market-modale-category"><i aria-hidden="true" class="fa ' . $NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$market->getCategorie()]['icon'] . '"></i> ' . $NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$market->getCategorie()]['name'] . '</div>';
            Utils::sendVarToJS('market_display_info_category', $NEXTDOM_INTERNAL_CONFIG['plugin']['category'][$market->getCategorie()]['name']);
        } else {
            echo '<div class="form-group market-modale-category">' . $market->getCategorie() . '</div>';
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
                        echo '<a class="btn btn-default bt_installFromMarket spacing-right" data-version="' . $branch . '" data-market_logicalId="' . $market->getLogicalId() . '" data-market_id="' . $market->getId() . '" ><i aria-hidden="true" class="fas fa-plus-circle spacing-right"></i>{{Installer}} ' . $branch . '</a>';
                    }
                }
            } else if ($market->getPrivate() === 1) {
                echo '<div class="alert alert-info">{{Ce plugin est pour le moment privé. Vous devez attendre qu\'il devienne public ou avoir un code pour y accéder}}</div>';
            } else {
                if (ConfigManager::byKey('market::apikey') != '' || (ConfigManager::byKey('market::username') != '' && ConfigManager::byKey('market::password') != '')) {
                    $purchase_info = RepoMarket::getPurchaseInfo();
                    if (isset($purchase_info['user_id']) && is_numeric($purchase_info['user_id'])) {
                        if ($market->getCost() > 0 && $market->getPurchase() != 1) {
                            echo '<a class="btn btn-action spacing-right" href="https://www.jeedom.com/market/index.php?v=d&p=profils#buyHistory" target="_blank"><i aria-hidden="true" class="fas fa-eur spacing-right"></i>{{Code promo}}</a>';
                        }
                        if ($marketCertification === JeedomMarketCert::PREMIUM) {
                            echo '<a class="btn btn-default spacing-right" target="_blank" href="mailto:supportpro@jeedom.com"><i aria-hidden="true" class="fas fa-envelope spacing-right"></i>{{Nous Contacter}}</a>';
                        } else {
                            echo '<a class="btn btn-default" target="_blank" href="' . ConfigManager::byKey('market::address') . '/index.php?v=d&p=purchaseItem&user_id=' . $purchase_info['user_id'] . '&type=plugin&id=' . $market->getId() . '"><i aria-hidden="true" class="fas fa-shopping-cart spacing-right"></i>{{Acheter}}</a>';
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
                <a class="btn btn-danger" id="bt_removeFromMarket" data-market_id="<?php echo $market->getId(); ?>"><i aria-hidden="true" class="fas fa-minus-circle spacing-right"></i>{{Supprimer}}</a>
            <?php }
            echo '</div>';
            echo '<div class="form-group"><i aria-hidden="true" class="fas fa-credit-card spacing-left spacing-right"></i>';
            if ($marketCertification === JeedomMarketCert::PREMIUM) {
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
    if ($marketCertification !== JeedomMarketCert::OFFICIAL && $marketCertification !== JeedomMarketCert::PREMIUM && $marketCertification !== JeedomMarketCert::LEGACY) {
        echo '<div class="alert alert-warning">{{Attention ce plugin n\'est pas un plugin officiel en cas de soucis avec celui-ci (direct ou indirect) toute demande de support peut être refusée}}</div>';
    }
    $compatibilityHardware = $market->getHardwareCompatibility();
    if (is_array($compatibilityHardware) && count($compatibilityHardware) > 0 && isset($compatibilityHardware[NextDomHelper::getHardwareName()]) && $compatibilityHardware[NextDomHelper::getHardwareName()] != 1) {
        echo '<div class="alert alert-danger">{{Attention ce plugin ne semble pas être compatible avec votre système}}</div>';
    }
    ?>

    <?php if (count($market->getImg('screenshot')) > 0) {
        ?>
        <div class='row form-group' style="height : 200px;">
            <div id="plugin-carousel" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner">
                    <?php
                    $index = 0;
                    foreach ($market->getImg('screenshot') as $screenshot) {
                        if ($index == 0) {
                            echo '<div class="item active">';
                        } else {
                            echo '<div class="item">';
                        }
                        echo '<img src="' . ConfigManager::byKey(ConfigKey::MARKET_ADDRESS) . '/' . $screenshot . '" style="height : 200px;">';
                        echo '<div class="carousel-caption"></div>';
                        echo '</div>';
                        $index++;
                    }
                    ?>
                    <a class="left carousel-control text_color" href="#plugin-carousel" data-slide="prev">
                        <span class="fa fa-angle-left"></span>
                    </a>
                    <a class="right carousel-control text_color" href="#plugin-carousel" data-slide="next">
                        <span class="fa fa-angle-right"></span>
                    </a>
                </div>
            </div>
        </div>
    <?php }
    ?>
    <div class='row form-group'>
        <div class='col-sm-6'>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i aria-hidden="true" class="fas fa-info-circle"></i>{{Description}}</h3>
                </div>
                <div class="box-body">
                    <form class="form-horizontal">
                        <fieldset>
                            <span class="marketAttr market-description" data-l1key="description"></span>
                    </form>
                </div>
                <div class="box-footer">
                    <form class="form-horizontal">
                        <a class="btn btn-primary pull-left" target="_blank" href="<?php echo $market->getDoc() ?>"><i aria-hidden="true" class="fas fa-book spacing-right"></i>{{Documentation}}</a>
                        <a class="btn btn-default pull-right" target="_blank" href="<?php echo $market->getChangelog() ?>"><i aria-hidden="true" class="fas fa-list spacing-right"></i>{{Changelog}}</a>
                    </form>
                </div>
            </div>
        </div>
        <div class='col-sm-6'>
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i aria-hidden="true" class="fas fa-drafting-compass"></i>{{Compatibilité plateforme}}</h3>
                </div>
                <div class="box-body">
                    <form class="form-horizontal">
                        <fieldset>
                            <?php
                            $oneCompatibility = 0;
                            $compatibilityIcons = [
                                'diy' => 'diy',
                                'rpi' => 'rpi12',
                                'docker' => 'docker',
                                'miniplus' => 'nextdomboard',
                            ];

                            foreach ($compatibilityIcons as $hardware => $icon) {
                                if ($market->getHardwareCompatibility($hardware) == 1) {
                                    echo '<img src="/public/img/logo_' . $icon . '.png" class="market-compatibility"/>';
                                    $oneCompatibility = 1;
                                }
                            }
                            if ($oneCompatibility == 0) {
                                echo '<img src="/public/img/logo_notset.png" class="market-compatibility"/>';
                            }
                            ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class='row form-group'>
        <div class='col-sm-6'>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i aria-hidden="true" class="fas fa-comments"></i>{{Avis}}</h3>
                </div>
                <div class="box-body market-modale-body">
                    <form class="form-horizontal">
                        <fieldset>
                            <div class="col-sm-6 centered">
                                <span class="marketAttr market-modale-rating" data-l1key="rating"></span>/5
                            </div>
                            <div class="col-sm-6 centered">
                                <?php if (ConfigManager::byKey('market::apikey') != '' || (ConfigManager::byKey('market::username') != '' && ConfigManager::byKey('market::password') != '')) { ?>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">{{Ma Note}}</label>
                                    </div>
                                    <div class="form-group">
                                        <span><input style="display:none;" type="number" class="rating" id="in_myRating" data-max="5" data-empty-value="0" data-min="1" data-clearable="Effacer" value="<?php echo $market->getRating('user') ?>"/></span>
                                    </div>
                                <?php }
                                ?>
                            </div>
                    </form>
                </div>
            </div>
        </div>
        <div class='col-sm-6'>
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i aria-hidden="true" class="fas fa-business-time"></i>{{Utilisation}}</h3>
                </div>
                <div class="box-body market-modale-body">
                    <form class="form-horizontal">
                        <fieldset>
                            <span class="marketAttr market-description" data-l1key="utilization"></span>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class='row form-group'>
        <div class='col-sm-12'>
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title"><i aria-hidden="true" class="fas fa-barcode"></i>{{Informations complementaires}}</h3>
                </div>
                <div class="box-body market-modale-body">
                    <form class="form-horizontal">
                        <fieldset>
                            <div class='col-sm-2'>
                                <label class="control-label">{{Taille}}</label><br/>
                                <span><?php echo $market->getParameters('size'); ?></span>
                            </div>
                            <div class='col-sm-2'>
                                <label class="control-label">{{Lien}}</label><br/>
                                <?php if ($market->getLink('video') != '' && $market->getLink('video') != 'null') { ?>
                                    <a class="btn btn-default btn-xs" target="_blank" href="<?php echo $market->getLink('video'); ?>"><i aria-hidden="true" class="fa fa-youtube"></i> Video</a><br/>
                                <?php }
                                ?>
                                <?php if ($market->getLink('forum') != '' && $market->getLink('forum') != 'null') { ?>
                                    <a class="btn btn-default btn-xs" target="_blank" href="<?php echo $market->getLink('forum'); ?>"><i aria-hidden="true" class="fa fa-users"></i> Forum</a><br/>
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
                                echo '<img src="/public/img/flags/francais.png" width="30" />';
                                if ($market->getLanguage('en_US') == 1) {
                                    echo '<img src="/public/img/flags/anglais.png" width="30" />';
                                }
                                if ($market->getLanguage('de_DE') == 1) {
                                    echo '<img src="/public/img/flags/allemand.png" width="30" />';
                                }
                                if ($market->getLanguage('sp_SP') == 1) {
                                    echo '<img src="/public/img/flags/espagnol.png" width="30" />';
                                }
                                if ($market->getLanguage('ru_RU') == 1) {
                                    echo '<img src="/public/img/flags/russe.png" width="30" />';
                                }
                                if ($market->getLanguage('id_ID') == 1) {
                                    echo '<img src="/public/img/flags/indonesien.png" width="30" />';
                                }
                                if ($market->getLanguage('it_IT') == 1) {
                                    echo '<img src="/public/img/flags/italien.png" width="30" />';
                                }
                                ?>
                            </div>
                            <div class='col-sm-3'>
                                <label class="control-label">{{Dernière mise à jour le}}</label><br/>
                                <?php echo $market->getDatetime('stable') ?>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>

      $("img.lazy").lazyload({
        event: "sporty"
      });
      $("img.lazy").trigger("sporty");

      $(document).unbind('click.fb-start');

      $('body').setValues(market_display_info, '.marketAttr');

      $('.ui-dialog').find('.ui-dialog-title').text('Market NextDom - ' + market_display_info_category);

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
