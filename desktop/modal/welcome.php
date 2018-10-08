<?php
if (!isConnect()) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>

<div class="alert alert-success">
    <h4><i class="fas fa-thumbs-up">&nbsp;&nbsp;</i>{{Félicitations}}</h4>
    {{Bienvenue dans}} <?= config::byKey('product_name'); ?> {{, merci d\'avoir choisit cet outil pour votre habitat connecté.}}
    <span class='btn btn-legend btn-action btn-legend pull-right' id='bt_doNotDisplayWelcome'><i class="fas fa-eye-slash">&nbsp;&nbsp;</i>{{Ne plus afficher}}</span>
</div>

<div class="container-fluid">
	<div class="row">
		<div class="col-md-6">
			<div class="box box-solid">
				<div class="box-header with-border">
          <i class="fas fa-puzzle-piece"></i><h3 class="box-title">{{Plugins populaires}}</h3>
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
		<div class="col-md-6">
			<div class="box box-solid">
				<div class="box-header with-border">
          <i class="fas fa-rocket">&nbsp;&nbsp;</i>
					<h3 class="box-title">{{Migration Jeedom}}&nbsp;&nbsp;</h3>
          <i class="fas fa-caret-right">&nbsp;</i>
          <h3 class="box-title">{{NextDom}}</h3>
				</div>
				<!-- /.box-header -->
				<div class="box-body">
					<div class="row text-center">
						<div class="form-group col-xs-12">
							<a href="index.php?v=d&p=migration"><i class="fas fa-rocket" style="font-size:14.3em;color:#33B8CC"></i></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="container-fluid">
	<div class="row">
		<div class="col-md-4">
			<div class="box box-solid">
				<div class="box-header with-border">
					<i class="fas fa-book"></i>
					<h3 class="box-title">{{Documentations}}</h3>
				</div>
				<!-- /.box-header -->
				<div class="box-body">
					<p class="lead">{{Informez-vous pour mieux maitriser}}</p>
					<p class="text-muted">{{La lecture de la documentation est fastidieuse mais c'est une opération essentielle pour bien maitriser votre solution domotique.}}</p>
			    <p><a href="http://www.nextdom.org/docs" class="text-blue">{{Accéder à la documentation}}</a></p>
				</div>
				<!-- /.box-body -->
			</div>
		</div>
		<div class="col-md-4">
			<div class="box box-solid">
				<div class="box-header with-border">
					<i class="fas fa-comments"></i>
					<h3 class="box-title">{{Forum}}</h3>
				</div>
				<!-- /.box-header -->
				<div class="box-body">
					<p class="lead">{{Trouver de l'aide sur notre forum}}</p>
					<p class="text-muted">
					  {{Le forum NextDom est un moyen simple et efficace de trouver la solution a votre probleme.<br/>
						Il y a surement une autre personne qui a déja rencontré le meme probleme que vous.<br/>
						Si ce n'est pas le cas, posez vous question, la communautée y répondra au plus vite.}}</p>
					<p><a href="http://www.nextdom.org/forum" class="text-blue">{{Accéder au forum}}</a><p/>
				</div>
				<!-- /.box-body -->
			</div>
		</div>
		<div class="col-md-4">
			<div class="box box-solid">
				<div class="box-header with-border">
					<i class="fas fa-rss-square"></i>
					<h3 class="box-title">{{Blog}}</h3>
				</div>
				<!-- /.box-header -->
				<div class="box-body">
					<p class="lead">{{Restez informé}}</p>
					<p class="text-muted">{{Retrouvez toute l'actualité de NextDom sur notre blog.}}</p>
					<p><a href="http://www.nextdom.org/blog" class="text-blue">{{Accéder au blog}}</a></p>
				</div>
				<!-- /.box-body -->
			</div>
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
