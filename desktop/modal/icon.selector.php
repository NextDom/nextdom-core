<?php
if (!isConnect()) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>

<input class="form-control pull-right" placeholder="{{Rechercher}}" id="in_iconSelectorSearch" />
<?php
foreach (ls('public/icon', '*') as $dir) {
    if (is_dir('public/icon/' . $dir) && file_exists('public/icon/' . $dir . '/style.css')) {
        $css = file_get_contents('public/icon/' . $dir . '/style.css');
        $research = strtolower(str_replace('/', '', $dir));
        preg_match_all("/\." . $research . "-(.*?):/", $css, $matches, PREG_SET_ORDER);
        $height = (ceil(count($matches) / 14) * 40) + 80;
        echo '<div style="height : ' . $height . 'px;"><legend>{{' . str_replace('/', '', $dir) . '}}</legend>';

        $number = 1;
        foreach ($matches as $match) {
            if (isset($match[0])) {
                if ($number == 1) {
                    echo '<div class="row text-center">';
                }
                echo '<div class="col-xs-2 col-md-1 col-lg-1 divIconSel">';
                $icon = str_replace(array(':', '.'), '', $match[0]);
                echo '<span class="iconSel"><i class=\'icon ' . $icon . '\'></i></span><br/><span class="iconDesc">' . $icon . '</span></center>';
                echo '</div>';
                if ($number == 12) {
                    echo '</div>';
                    $number = 0;
                }
                $number++;
            }
        }
        echo "</div><br/>";
    }
}

?>
<?php
$matches="";
$css="";
$height=0;
$icon="";
if (is_dir('/vendor/node_modules/font-awesome/css/') && file_exists('/vendor/node_modules/font-awesome/css/font-awesome.css')) {
    $css = file_get_contents('/vendor/node_modules/font-awesome/css/font-awesome.css');
    preg_match_all("/\.fa" . "-(.*?):/", $css, $matches, PREG_SET_ORDER);
    $height = (ceil(count($matches) / 14) * 40) + 80;
    echo '<div><legend>{{font-awesome}}</legend>';

    $number = 1;
    foreach ($matches as $match) {
        if (isset($match[0])) {
            if ($number == 1) {
                echo '<div class="row text-center">';
            }
            echo '<div class="col-xs-2 col-md-1 col-lg-1 divIconSel">';
            $icon = str_replace(array(':', '.'), '', $match[0]);
            echo '<div class="divIconSel"><center><span class="iconSel"><i class="fa '.$icon.'"></i></span><br/><span class="iconDesc">'.str_replace("fa-", "", $icon).'</span></div>';
            echo '</div>';
            if ($number == 12) {
                echo '</div>';
                $number = 0;
            }
            $number++;
        }
    }
    echo "</div><br/>";
}
?>
<script>
    $('#in_iconSelectorSearch').on('keyup',function(){
        $('.divIconSel').show();
        var search = $(this).value();
        if(search != ''){
            $('.iconDesc').each(function(){
                if($(this).text().indexOf(search) == -1){
                    $(this).closest('.divIconSel').hide();
                }
            })
        }
    });
    $('.divIconSel').on('click', function () {
        $('.divIconSel').removeClass('iconSelected');
        $(this).closest('.divIconSel').addClass('iconSelected');
    });
    $('.divIconSel').on('dblclick', function () {
        $('.divIconSel').removeClass('iconSelected');
        $(this).closest('.divIconSel').addClass('iconSelected');
        $('#mod_selectIcon').dialog("option", "buttons")['Valider'].apply($('#mod_selectIcon'));
    });
</script>
