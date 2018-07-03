<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<div id="backup">
    <div class="row row-overflow">
        <div class="col-sm-6">
            <legend><i class="fa fa-folder-open"></i>  {{Sauvegardes locales}}</legend>
            <form class="form-horizontal">
                <fieldset>
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


<?php include_file("desktop", "migration", "js");?>
