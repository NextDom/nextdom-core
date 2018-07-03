<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<div id="backup">
  <div class="row row-overflow">
	<h3 class="col-sm-12  control-label text-center">{{Bienvenue sur la page de migration de NextDom}}</h3><br><br><br>
  <p class="col-sm-6  control-label  alert-info">{{La fonction migration permet d'utiliser une sauvegarde jeedom pour migrer vers nextdom}}<br>
  il vous suffit simplement de rentrer le chemin d'acces vers les sauvegardes (si les sauvegardes sont deportées sur un NAS.ou vous pouvez également les uploader sur nextdom.</p>
     <div class="col-sm-12">
          <div class="col-sm-6">
            <legend><i class="fa fa-folder-open"></i>  {{Sauvegardes locales}}</legend>
            <form class="form-horizontal">
                <fieldset>
                                                                               <div class="form-group">
                        <label class="col-sm-4 col-xs-6 control-label">{{Emplacement des sauvegardes}}</label>
                        <div class="col-sm-4 col-xs-6">
                            <input type="text" class="configKey form-control" data-l1key="backup::path" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-xs-6 control-label">{{Sauvegardes disponibles}}</label>
                        <div class="col-sm-6 col-xs-6">
                            <select class="form-control" id="sel_restoreBackup"> </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-xs-6 control-label">{{Migrer la sauvegarde}}</label>
                        <div class="col-sm-4 col-xs-6">
                            <a class="btn btn-warning" id="bt_migrateNextDom"><i class="fa fa-refresh fa-spin" style="display : none;"></i> <i class="fa fa-file"></i> {{Migrer}}</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-xs-6 control-label">{{Supprimer la sauvegarde}}</label>
                        <div class="col-sm-4 col-xs-6">
                            <a class="btn btn-danger" id="bt_removeBackup"><i class="fa fa-trash-o"></i> {{Supprimer}}</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-xs-6 control-label">{{Envoyer une sauvegarde}}</label>
                        <div class="col-sm-8 col-xs-6">
                            <span class="btn btn-default btn-file">
                                <i class="fa fa-cloud-upload"></i> {{Envoyer}}<input id="bt_uploadBackup" type="file" name="file" data-url="core/ajax/nextdom.ajax.php?action=backupupload&nextdom_token=<?php echo ajax::getToken(); ?>">
                            </span>
                        </div>
                    </div>
                </fieldset>
            </form>
            <div class="form-actions" style="height: 20px;">
                <a class="btn btn-success" id="bt_saveBackup"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
            </div>
        </div>
        <div class="col-sm-6">
            <legend><i class="fa fa-info-circle"></i>  {{Informations}}</legend>
            <pre id="pre_backupInfo" style="overflow: scroll;"></pre>
        </div>
      </div>
    </div>
</div>


<?php include_file("desktop", "migration", "js");?>
