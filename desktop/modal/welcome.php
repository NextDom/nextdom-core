<?php
if (!isConnect()) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>

    <a class='btn btn-default btn-xs pull-right' id='bt_doNotDisplayWelcome'><i class="fas fa-eye-slash"></i> Ne plus afficher</a>

</br>
<div class="callout callout-success" style="margin-bottom: 0!important;">
    <h4><i class="fas fa-thumbs-up"></i> Félicitations:</h4>
    {{  Bienvenue dans}} <?php echo config::byKey('product_name'); ?> {{, merci d'avoir choisi cet outil pour votre habitat connecté.}}<br>      </div>
<br>
<div class="row">
    <div class="col-md-5 col-md-offset-1">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Plugins populaires</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
                    <ol class="carousel-indicators">
                        <li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
                        <li data-target="#carousel-example-generic" data-slide-to="1" class=""></li>
                        <li data-target="#carousel-example-generic" data-slide-to="2" class=""></li>
                        <li data-target="#carousel-example-generic" data-slide-to="3" class=""></li>
                    </ol>
                    <div class="carousel-inner">
                        <div class="item active">
                            <img src="https://github.com/NextDom/plugin-AndroidRemoteControl/raw/master/docs/images/AndroidRemoteControl_icon.png" style="height:200px" alt="First slide">
                            <div class="carousel-caption">
                            </div>
                        </div>
                        <div class="item">
                            <img src="https://github.com/NextDom/plugin-PulseAudio/raw/master/docs/images/PulseAudio_icon.png" style="height:200px" alt="First slide">
                            <div class="carousel-caption">
                            </div>
                        </div>
                        <div class="item">
                            <img src="https://github.com/NextDom/plugin-Ftpd/raw/master/plugin_info/ftpd_icon.png" style="height:200px" alt="First slide">
                            <div class="carousel-caption">
                            </div>
                        </div>
                        <div class="item">
                            <img src="https://github.com/NextDom/plugin-Multiloc/raw/develop/plugin_info/Multiloc_icon.png" style="height:200px" alt="First slide">
                            <div class="carousel-caption">
                            </div>
                        </div>
                    </div>
                    <a class="left carousel-control" href="#carousel-example-generic" data-slide="prev">
                        <span class="fa fa-angle-left"></span>
                    </a>
                    <a class="right carousel-control" href="#carousel-example-generic" data-slide="next">
                        <span class="fa fa-angle-right"></span>
                    </a>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <div class="col-md-5">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Migration jeedom -> NextDom</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row text-center">
                    <div class="col-xs-12">
                        <a href="index.php?v=d&p=migration">
                            <i class="fas fa-upload" style="font-size:12em;"></i><br/><br/>
                            {{Migration de jeedom vers NextDom}}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3 col-md-offset-1">
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fas fa-book"></i>
                <h3 class="box-title">Documentations</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <p class="lead">Informez-vous pour mieux maitriser</p>
                <p class="text-muted"> La lecture de la documentation est fastidieuse mais c'est une opération essentielle pour bien maitriser votre solutlion domotique<br/></p>

                <a href="http://www.netxdom.org/docs" class="text-green">accéder a la documentation</a>

            </div>
            <!-- /.box-body -->
        </div>
    <div class="col-md-3">
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fas fa-comments"></i>
                <h3 class="box-title">Forum</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <p class="lead">Trouver de l'aide sur notre forum</p>

                <p class="text-muted">Le forum NextDom est un moyen simple et efficace de trouver la solution a votre probleme.<br/>
                    Il y a surement une autre personne qui a déja rencontré le meme probleme que vous.<br/>
                    Si ce n'est pas le cas, posez vous question, la communautée y répondra au plus vite</p>

                <a href="http://www.netxdom.org/forum" class="text-green">accéder au forum</a>

            </div>
            <!-- /.box-body -->
        </div>
    </div>
    <div class="col-md-3">
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fas fa-rss-square"></i>
                <h3 class="box-title">Blog</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <p class="lead">Restez informé</p>
                <p class="text-muted">retrouvez toute l'actualité de NextDom sur notre blog.</p>

                <a href="http://www.netxdom.org/blog" class="text-green">accéder au forum</a>

            </div>
            <!-- /.box-body -->
        </div>
    </div>
</div>

<script>
    $('#bt_doNotDisplayWelcome').on('click', function () {
        nextdom.config.save({
            configuration: {'nextdom::Welcome': 0},
            error: function (error) {
                notify("Core", error.message, 'error');
            },
            success: function () {
                notify("Core", '{{Sauvegarde réussie}}', 'success');
            }
        });
    });
</script>

<style>
    .ui-dialog .ui-dialog-content {background-color: #ecf0f5}
</style>
