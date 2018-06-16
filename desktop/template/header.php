<?php

/* This file is part of NextDom.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

use NextDom\Helpers\Status;

global $title;
global $language;
global $configs;

$bootstrapTheme = '';
if (isset($_SESSION['user'])) {
    $bootstrapTheme = $_SESSION['user']->getOptions('bootstrap_theme');
}
$themeDir = NEXTDOM_ROOT . 'core/themes/'
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <link rel="shortcut icon" href="core/img/logo-nextdom-sans-nom-couleur-25x25.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <script>
        var clientDatetime = new Date();
        var clientServerDiffDatetime = (<?php echo strtotime('now'); ?> * 1000
        )
        -clientDatetime.getTime();
        var serverDatetime = <?php echo getmicrotime(); ?>;
        var io = null;
    </script>
    <?php
    // CSS
    if (!Status::isConnect()) {
        include_file('3rdparty', 'bootstrap/css/bootstrap.min', 'css');
    } else {
        $cssBootstrapToAdd = true;
        if (!Status::isRecueMode()) {
            $defaultBootstrapTheme = config::byKey('default_bootstrap_theme');
            if (file_exists($themeDir . $bootstrapTheme . '/desktop/' . $bootstrapTheme . '.css')) {
                include_file('core', $bootstrapTheme . '/desktop/' . $bootstrapTheme, 'themes.css');
                $cssBootstrapToAdd = false;
            } else {
                $defaultBootstrapTheme = config::byKey('default_bootstrap_theme');
                if (file_exists($themeDir . $defaultBootstrapTheme . '/desktop/' . $defaultBootstrapTheme . '.css')) {
                    include_file('core', $defaultBootstrapTheme . '/desktop/' . $defaultBootstrapTheme, 'themes.css');
                    $cssBootstrapToAdd = false;
                }
            }
        }
        if ($cssBootstrapToAdd) {
            include_file('3rdparty', 'bootstrap/css/bootstrap.min', 'css');
        }
    }
    include_file('core', 'icon.inc', 'php');
    include_file('3rdparty', 'roboto/roboto', 'css');
    include_file('desktop', 'commun', 'css');
    include_file('core', 'core', 'css');
    include_file('3rdparty', 'jquery.toastr/jquery.toastr.min', 'css');
    include_file('3rdparty', 'jquery.ui/jquery-ui-bootstrap/jquery-ui', 'css');
    include_file('3rdparty', 'jquery.utils/jquery.utils', 'css');
    include_file('3rdparty', 'font-noto/font-noto', 'css');
    include_file('3rdparty', 'jquery.tree/themes/default/style.min', 'css');
    include_file('3rdparty', 'datetimepicker/jquery.datetimepicker', 'css');
    include_file('3rdparty', 'jquery.cron/jquery.cron', 'css');
    include_file('3rdparty', 'jquery.contextMenu/jquery.contextMenu.min', 'css');
    include_file('3rdparty', 'jquery.tablesorter/theme.bootstrap', 'css');

    // Javascript
    include_file('3rdparty', 'jquery/jquery.min', 'js');
    ?>
    <script>
        NEXTDOM_AJAX_TOKEN = '<?php echo ajax::getToken() ?>';
        $.ajaxSetup({
            type: "POST",
            data: {
                nextdom_token: '<?php echo ajax::getToken() ?>'
            }
        })
    </script>
    <?php
    include_file('3rdparty', 'jquery.utils/jquery.utils', 'js');
    include_file('core', 'core', 'js');
    include_file('3rdparty', 'bootstrap/bootstrap.min', 'js');
    include_file('3rdparty', 'jquery.ui/jquery-ui.min', 'js');
    include_file('3rdparty', 'jquery.ui/jquery.ui.datepicker.fr', 'js');
    include_file('core', 'js.inc', 'php');
    include_file('3rdparty', 'bootbox/bootbox.min', 'js');
    include_file('3rdparty', 'highstock/highstock', 'js');
    include_file('3rdparty', 'highstock/highcharts-more', 'js');
    include_file('3rdparty', 'highstock/modules/solid-gauge', 'js');
    include_file('3rdparty', 'highstock/modules/exporting', 'js');
    include_file('3rdparty', 'highstock/modules/export-data', 'js');
    include_file('desktop', 'utils', 'js');
    include_file('3rdparty', 'jquery.toastr/jquery.toastr.min', 'js');
    include_file('3rdparty', 'jquery.at.caret/jquery.at.caret.min', 'js');
    include_file('3rdparty', 'jwerty/jwerty', 'js');
    include_file('3rdparty', 'jquery.packery/jquery.packery', 'js');
    include_file('3rdparty', 'jquery.lazyload/jquery.lazyload', 'js');
    include_file('3rdparty', 'jquery.sew/jquery.sew', 'css');
    include_file('3rdparty', 'codemirror/lib/codemirror', 'js');
    include_file('3rdparty', 'codemirror/lib/codemirror', 'css');
    include_file('3rdparty', 'codemirror/addon/edit/matchbrackets', 'js');
    include_file('3rdparty', 'codemirror/mode/htmlmixed/htmlmixed', 'js');
    include_file('3rdparty', 'codemirror/mode/clike/clike', 'js');
    include_file('3rdparty', 'codemirror/mode/php/php', 'js');
    include_file('3rdparty', 'codemirror/mode/xml/xml', 'js');
    include_file('3rdparty', 'codemirror/mode/javascript/javascript', 'js');
    include_file('3rdparty', 'codemirror/mode/css/css', 'js');
    include_file('3rdparty', 'jquery.tree/jstree.min', 'js');
    include_file('3rdparty', 'jquery.fileupload/jquery.ui.widget', 'js');
    include_file('3rdparty', 'jquery.fileupload/jquery.iframe-transport', 'js');
    include_file('3rdparty', 'jquery.fileupload/jquery.fileupload', 'js');
    include_file('3rdparty', 'jquery.tablesorter/jquery.tablesorter.min', 'js');
    include_file('3rdparty', 'jquery.tablesorter/jquery.tablesorter.widgets.min', 'js');
    include_file('3rdparty', 'datetimepicker/jquery.datetimepicker', 'js');
    include_file('3rdparty', 'jquery.cron/jquery.cron.min', 'js');
    include_file('3rdparty', 'jquery.contextMenu/jquery.contextMenu.min', 'js');
    include_file('3rdparty', 'autosize/autosize.min', 'js');
    if (!Status::isRecueMode() && $configs['enableCustomCss'] == 1) {
        if (file_exists(NEXTDOM_ROOT.'/desktop/custom/custom.css')) {
            include_file('desktop', '', 'custom.css');
        }
        if (file_exists(NEXTDOM_ROOT.'/desktop/custom/custom.js')) {
            include_file('desktop', '', 'custom.js');
        }
    }
    try {
        if (Status::isConnect()) {
            if (!Status::isRecueMode() && is_dir($themeDir . $bootstrapTheme . '/desktop')) {
                if (file_exists($themeDir . $bootstrapTheme . '/desktop/' . $bootstrapTheme . '.js')) {
                    include_file('core', $bootstrapTheme . '/desktop/' . $bootstrapTheme, 'themes.js');
                }
            }
            if (!Status::isRecueMode() && $_SESSION['user']->getOptions('desktop_highcharts_theme') != '') {
                try {
                    if (is_dir($themeDir . $bootstrapTheme . '/desktop')) {
                        if (file_exists($themeDir . $bootstrapTheme . '/desktop/' . $bootstrapTheme . '.js')) {
                            include_file('core', $bootstrapTheme . '/desktop/' . $bootstrapTheme, 'themes.js');
                        }
                    }
                } catch (Exception $e) {

                }
                if (!Status::isRecueMode() && $_SESSION['user']->getOptions('desktop_highcharts_theme') != '') {
                    try {
                        include_file('3rdparty', 'highstock/themes/' . $_SESSION['user']->getOptions('desktop_highcharts_theme'), 'js');
                    } catch (Exception $e) {

                    }
                }
            }
        }
    } catch (Exception $e) {

    }

    sendVarToJS('nextdom_langage', $language);
    ?>
    <!-- Inclusion à l'arrache ??? -->
    <script src="3rdparty/snap.svg/snap.svg-min.js"></script>
</head>
