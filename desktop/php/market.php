<?php

namespace NextDom;

use NextDom\Helpers\Status;
use NextDom\Helpers\DataStorage;
use NextDom\Helpers\Utils;

Status::initConnectState();
Status::isConnectedAdminOrFail();

global $NEXTDOM_INTERNAL_CONFIG;
$sourcesList = array();
foreach ($NEXTDOM_INTERNAL_CONFIG['nextdom_market']['sources'] as $source) {
    // TODO: Limiter les requêtes
    if (\config::byKey('nextdom_market::' . $source['code']) == 1) {
        $sourcesList[] = $source;
    }
}

Utils::sendVarsToJS(
    array(
        'github' => \config::byKey('github::enable'),
        'sourcesList' => $sourcesList,
        'moreInformationsStr' => __("Plus d'informations", __FILE__),
        'updateStr' => __("Mettre à jour", __FILE__),
        'updateAllStr' => __("Voulez-vous mettre à jour tous les plugins ?", __FILE__),
        'updateThisStr' => __("Voulez-vous mettre à jour ce plugin ?", __FILE__),
        'installedPluginStr' => __("Plugin installé", __FILE__),
        'updateAvailableStr' => __("Mise à jour disponible", __FILE__)
    )
);

// Affichage d'un message à un utilisateur
if (isset($_GET['message'])) {
    $messages = [
        __('La mise à jour du plugin a été effecutée.', __FILE__),
        __('Le plugin a été supprimé', __FILE__)
    ];

    $messageIndex = intval($_GET['message']);
    if ($messageIndex < count($messages)) {
        \message::add('core', $messages[$messageIndex]);
    }
}

\include_file('desktop', 'Market/market', 'js');
//\include_file('desktop', 'Market/market', 'css');

?>
<div class="row">
    <div class="col-sm-12 col-md-12">
        <?php if (count($sourcesList) > 1 && \config::byKey('nextdom_market::show_sources_filters')) : ?>
            <div class="market-filters row">
                <div id="market-filter-src" class="btn-group col-sm-12">
                    <?php
                    foreach ($sourcesList as $source) {
                        $name = $source['name'];
                        echo '<button type="button" class="btn btn-primary" data-source="' . $name . '">' . $name . '</button >';
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="market-filters row">
            <div class="form-group btn-group col-sm-12 col-md-6 col-lg-3">
                <button id="market-filter-installed" class="btn btn-primary">{{Installés}}</button>
                <button id="market-filter-notinstalled" class="btn btn-primary">{{Non installés}}</button>
            </div>
            <div class="form-group col-sm-12 col-md-6 col-lg-3">
                <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-search"></i></div>
                    <input type="text" class="form-control" id="market-search" placeholder="{{Rechercher}}"/>
                </div>
            </div>
            <div class="form-group col-sm-12 col-md-6 col-lg-2">
                <select class="form-control" id="market-filter-category">
                    <option value="all">{{Toutes les Catégories}}</option>
                    <option value="security">{{Sécurité}}</option>
                    <option value="automation protocol">{{Protocole domotique}}</option>
                    <option value="programming">{{Programmation}}</option>
                    <option value="organization">{{Organisation}}</option>
                    <option value="weather">{{Météo}}</option>
                    <option value="communication">{{Communication}}</option>
                    <option value="devicecommunication">{{Objets communicants}}</option>
                    <option value="multimedia">{{Multimédia}}</option>
                    <option value="wellness">{{Bien-être}}</option>
                    <option value="monitoring">{{Monitoring}}</option>
                    <option value="health">{{Santé}}</option>
                    <option value="nature">{{Nature}}</option>
                    <option value="automatisation">{{Automatisme}}</option>
                    <option value="energy">{{Energie}}</option>
                </select>
            </div>
            <div class="form-group col-sm-12 col-md-6 col-lg-4">
                <div id="admin-buttons" class="btn-group">
                    <button id="mass-update" class="btn btn-primary">
                        <i class="fa fa-download"></i> {{Mise(s) à jour}}
                        <span class="badge">0</span>
                    </button>
                    <button id="refresh-markets" class="btn btn-primary">
                        <i class="fa fa-refresh"></i> {{Rafraîchir}}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="market-div" class="row">

</div>
<div class="modal fade" id="market-modal" tabindex="-1" role="dialog" aria-labelledby="market-modal-title"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="market-modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="market-modal-content">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{Fermer}}</button>
                <button type="button" id="market-modal-valid" class="btn btn-primary"></button>
            </div>
        </div>
    </div>
</div>