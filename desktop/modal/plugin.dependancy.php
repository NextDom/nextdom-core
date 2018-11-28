<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin_id = init('plugin_id');
sendVarToJs('plugin_id', $plugin_id);
if (!class_exists($plugin_id)) {
    die();
}
$plugin = plugin::byId($plugin_id);
$dependancy_info = $plugin->dependancy_info();
?>
<table class="table table-bordered table-condensed">
    <thead>
        <tr>
            <th>{{Nom}}</th>
            <th>{{Statut}}</th>
            <th>{{Installation}}</th>
            <th>{{Dernière installation}}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{Local}}</td>
            <td class="dependancyState">
                <?php
switch ($dependancy_info['state']) {
    case 'ok':
        echo '<span class="label label-success label-sticker-sm">{{OK}}</span>';
        break;
    case 'nok':
        echo '<span class="label label-danger label-sticker-sm">{{NOK}}</span>';
        break;
    case 'in_progress':
        echo '<span class="label label-primary label-sticker-sm"><i class="fas fa-spinner fa-spin"></i>&nbsp;&nbsp;{{Installation en cours}}';
        if (isset($dependancy_info['progression']) && $dependancy_info['progression'] !== '') {
            echo ' - ' . $dependancy_info['progression'] . ' %';
        }
        if (isset($dependancy_info['duration']) && $dependancy_info['duration'] != -1) {
            echo ' - ' . $dependancy_info['duration'] . ' min';
        }
        echo '</span>';
        break;
    default:
        echo '<span class="label label-warning label-sticker-sm">' . $dependancy_info['state'] . '</span>';
        break;
}
?>
            </td>
            <td>
                <a class="btn btn-warning btn-sm launchInstallPluginDependancy"><i class="fas fa-bicycle">&nbsp;&nbsp;</i>{{Relancer}}</a>
            </td>
            <td class="td_lastLaunchDependancy">
                <?php echo $dependancy_info['last_launch'] ?>
            </td>
        </tr>
    </tbody>
</table>
<script>
    function refreshDependancyInfo(){
        var nok = false;
        nextdom.plugin.getDependancyInfo({
            id : plugin_id,
            success: function (data) {
                switch(data.state) {
                    case 'ok':
                    $('.dependancyState').empty().append('<span class="label label-success label-sticker-sm">{{OK}}</span>');
                    break;
                    case 'nok':
                    nok = true;
                    $("#div_plugin_dependancy").closest('.box').removeClass('box-success box-info').addClass('box-danger');
                    $('.dependancyState').empty().append('<span class="label label-danger label-sticker-sm">{{NOK}}</span>');
                    break;
                    case 'in_progress':
                    nok = true;
                    $("#div_plugin_dependancy").closest('.box').removeClass('box-success box-danger').addClass('box-info');
                    var html = '<span class="label label-primary label-sticker-sm"><i class="fas fa-spinner fa-spin"></i>&nbsp;&nbsp;{{Installation en cours}}';
                    if(isset(data.progression) && data.progression !== ''){
                        html += ' - '+data.progression+' %';
                    }
                    if(isset(data.duration) && data.duration != -1){
                        html += ' - '+data.duration+' min';
                    }
                    html += '</span>';
                    $('.dependancyState').empty().append(html);
                    break;
                    default:
                    $('.dependancyState').empty().append('<span class="label label-warning label-sticker-sm">'+data.state+'</span>');
                }
                $('.td_lastLaunchDependancy').empty().append(data.last_launch);
                if(!nok){
                    $("#div_plugin_dependancy").closest('.box').removeClass('box-danger box-info').addClass('box-success');
                }
                if(nok){
                    setTimeout(refreshDependancyInfo, 5000);
                }
            }
        });
    }
    refreshDependancyInfo();

    $('.launchInstallPluginDependancy').on('click',function(){
        nextdom.plugin.dependancyInstall({
            id : plugin_id,
            error: function (error) {
                notify("Erreur", error.message, 'error');
            },
            success: function (data) {
                $("#div_plugin_dependancy").load('index.php?v=d&modal=plugin.dependancy&plugin_id='+plugin_id);
            }
        });
    });
</script>
