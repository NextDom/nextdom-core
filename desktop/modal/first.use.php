<?php
if (!isConnect()) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<legend>
    Mes premiers pas dans NextDom
    <a class='btn btn-default btn-xs pull-right' id='bt_doNotDisplayFirstUse'><i class="fa fa-eye-slash"></i> Ne plus afficher</a>
</legend>
<div id="div_alertFirstUse"></div>
   <p class="alert-success"> {{  Bienvenue dans}} <?php echo config::byKey('product_name'); ?> {{, et merci d'avoir choisi cet outil pour votre habitat connecté. Une nouvelle fonctionnalité fait son apparition, vous pouvez désormait migrer votre installation jeedom sur nextdom.:}}<br>
    {{  Rien de plus simple il suffit de cliquer sur le bouton ci-dessous}} </p>
<br/><br/>
  <div class="row text-center">
    <div class="col-xs-12">
        <a href="index.php?v=d&p=migration">
            <i class="fa fa-upload" style="font-size:12em;"></i><br/>
            {{Migration de jeedom vers NextDom}}
        </a>
    </div>
</div>
<br><br>
  </legend>
<div id="div_alertFirstUse"></div>
    <p class="alert-info">{{Voici 3 guides pour bien débuter avec}} <?php echo config::byKey('product_name'); ?> :</p>
<br/><br/>

<div class="row">
    <div class="col-xs-4 text-center">
        <a href="https://nextdom.org/start" target="_blank">
            <i class="fa fa-picture-o" style="font-size:12em;"></i><br/>
            {{Guide de démarrage}}
        </a>
</div>
<div class="col-xs-4 text-center">
    <a href="https://nextdom.github.io/documentation/premiers-pas/fr_FR/index" target="_blank">
        <i class="fa fa-check-square" style="font-size:12em;"></i><br/>
        {{Documentation de démarrage}}
    </a>
</div>
<div class="col-xs-4 text-center">
    <a href="https://nextdom.github.io/documentation" target="_blank">
        <i class="fa fa-book" style="font-size:12em;"></i><br/>
        {{Documentation}}
    </a>
</div>
</div>

<script>
    $('#bt_doNotDisplayFirstUse').on('click', function () {
        nextdom.config.save({
            configuration: {'nextdom::firstUse': 0},
            error: function (error) {
                notify("Core", error.message, 'error');
            },
            success: function () {
                notify("Core", '{{Sauvegarde réussie}}', 'success');
            }
        });
});
</script>
