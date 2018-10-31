<?php
header('Content-type: text/cache-manifest');
require_once __DIR__ . "/core/php/core.inc.php";

$js_file = array(
    '/vendor/node_modules/highcharts/highcharts-more.js',
    '/vendor/node_modules/highcharts/highstock.js',
    '/vendor/node_modules/highcharts/themes/dark-blue.js',
    '/vendor/node_modules/highcharts/themes/dark-green.js',
    '/vendor/node_modules/highcharts/themes/dark-unica.js',
    '/vendor/node_modules/highcharts/themes/gray.js',
    '/vendor/node_modules/highcharts/themes/grid-light.js',
    '/vendor/node_modules/highcharts/themes/grid.js',
    '/vendor/node_modules/highcharts/themes/sand-signika.js',
    '/vendor/node_modules/highcharts/themes/skies.js',
    '/vendor/node_modules/jquery/dist/jquery.min.js',
    '/3rdparty/jquery.mobile/jquery.mobile.min.js',
    '/3rdparty/jquery.mobile/nativedroid2.js',
    '/vendor/node_modules/wowjs/dist/wow.min.js',
    '/vendor/node_modules/node-waves/dist/waves.min.js',
    '/3rdparty/jquery.utils/jquery.utils.js',
    '/vendor/node_modules/jquery-ui-dist/jquery-ui.min.js',
    'core/js/cmd.class.js',
    'core/js/private.class.js',
    'core/js/core.js',
    'core/js/eqLogic.class.js',
    'core/js/user.class.js',
    'core/js/history.class.js',
    'core/js/config.class.js',
    'core/js/nextdom.class.js',
    'core/js/object.class.js',
    'core/js/plugin.class.js',
    'core/js/view.class.js',
    'core/js/message.class.js',
    'core/js/scenario.class.js',
    'core/js/plan.class.js',
    'core/js/plan3d.class.js',
    '/vendor/node_modules/packery/dist/packery.pkgd.js',
);
if (file_exists(__DIR__ . '/mobile/custom/custom.js')) {
    $js_file[] = 'mobile/custom/custom.js';
}

$other_file = array(
    'core/php/icon.inc.php',
    '/vendor/node_modules/font-awesome/css/font-awesome.css',
    '/3rdparty/jquery.mobile/jquery.mobile.min.css',
    '/3rdparty/jquery.mobile/css/nativedroid2.css',
    '/3rdparty/jquery.mobile/css/fonts.css',
    '/3rdparty/jquery.mobile/css/flexboxgrid.min.css',
    '/3rdparty/jquery.mobile/css/material-design-iconic-font.min.css',
    '/vendor/node_modules/node-waves/dist/waves.css',
    '/3rdparty/jquery.utils/jquery.utils.css',
    '/public/css/nextdom.mob.css',
    '/vendor/node_modules/font-awesome/fonts/fontawesome-webfont.woff2',
    '/vendor/node_modules/font-awesome/css/font-awesome.min.css',
    '/vendor/node_modules/font-awesome5/css/fontawesome-all.min.css',
    '/3rdparty/jquery.mobile/images/ajax-loader.gif',
    '/public/img/icon-128x128.png',
    '/public/img/icon-25x25.png',
    '/vendor/node_modules/font-awesome5/webfonts/fa-solid-900.woff2',
    '/3rdparty/jquery.mobile/css/fonts/fontawesome-webfont.woff2?v=4.3.0',
    '/vendor/node_modules/roboto-fontface/fonts/roboto/Roboto-Black.woff',
    '/vendor/node_modules/roboto-fontface/fonts/roboto/Roboto-BlackItalic.woff',
    '/vendor/node_modules/roboto-fontface/fonts/roboto/Roboto-Bold.woff',
    '/vendor/node_modules/roboto-fontface/fonts/roboto/Roboto-BoldItalic.woff',
    '/vendor/node_modules/roboto-fontface/fonts/roboto/Roboto-Light.woff',
    '/vendor/node_modules/roboto-fontface/fonts/roboto/Roboto-LightItalic.woff',
    '/vendor/node_modules/roboto-fontface/fonts/roboto/Roboto-Medium.woff',
    '/vendor/node_modules/roboto-fontface/fonts/roboto/Roboto-MediumItalic.woff',
    '/vendor/node_modules/roboto-fontface/fonts/roboto/Roboto-Regular.woff',
    '/vendor/node_modules/roboto-fontface/fonts/roboto/Roboto-Thin.woff',
    '/vendor/node_modules/roboto-fontface/fonts/roboto/Roboto-ThinItalic.woff',
    '/vendor/node_modules/roboto-fontface/css/roboto/roboto-fontface.css',
    '/3rdparty/jquery.mobile/css/fonts/roboto/Roboto-Medium-webfont.woff',

);
if (file_exists(__DIR__ . '/mobile/custom/custom.css')) {
    $other_file[] = 'mobile/custom/custom.css';
}

$root_dir = __DIR__ . '/core/css/icon/';
foreach (ls($root_dir, '*') as $dir) {
    if (is_dir($root_dir . $dir) && file_exists($root_dir . $dir . '/style.css')) {
        $other_file[] = 'core/css/icon/' . $dir . 'style.css';
        foreach (ls($root_dir . $dir . '/fonts', '*') as $font) {
            $other_file[] = 'core/css/icon/' . $dir . 'fonts/' . $font;
        }
    }
}

foreach (ls(__DIR__ . '/core/themes') as $dir) {
    if (is_dir(__DIR__ . '/core/themes/' . $dir . '/mobile')) {
        if (file_exists(__DIR__ . 'core/themes/' . $dir . 'mobile/' . trim($dir, '/') . '.css')) {
            $other_file[] = 'core/themes/' . $dir . 'mobile/' . trim($dir, '/') . '.css';
        }
        if (file_exists(__DIR__ . '/core/themes/' . $dir . 'mobile/' . trim($dir, '/') . '.js')) {
            $other_file[] = 'core/themes/' . $dir . 'mobile/' . trim($dir, '/') . '.js';
        }
    }
}
?>
CACHE MANIFEST

CACHE:
<?php
echo '#LANG : ' . translate::getLanguage();
foreach (plugin::listPlugin(true) as $plugin) {
    foreach (ls(__DIR__ . '/plugins/' . $plugin->getId() . '/core/template/mobile', '*') as $file) {
        if (is_dir(__DIR__ . '/plugins/' . $plugin->getId() . '/core/template/mobile/' . $file)) {
            foreach (ls(__DIR__ . '/plugins/' . $plugin->getId() . '/core/template/mobile/' . $file, '*') as $file2) {
                if (is_dir(__DIR__ . '/plugins/' . $plugin->getId() . '/core/template/mobile/' . $file . $file2)) {
                    foreach (ls(__DIR__ . '/plugins/' . $plugin->getId() . '/core/template/mobile/' . $file . $file2, '*') as $file3) {
                        if (strpos($file3, '.js') !== false) {
                            $js_file[] = 'plugins/' . $plugin->getId() . '/core/template/mobile/' . $file . $file2 . $file3;
                        } elseif (strpos($file3, '.css') !== false || strpos($file3, '.png') !== false || strpos($file3, '.jpg') !== false || strpos($file3, '.ttf') !== false || strpos($file3, '.woff') !== false) {
                            $other_file[] = 'plugins/' . $plugin->getId() . '/core/template/mobile/' . $file . $file2 . $file3;
                        }
                    }
                } else if (strpos($file2, '.js') !== false) {
                    $js_file[] = 'plugins/' . $plugin->getId() . '/core/template/mobile/' . $file . $file2;
                } elseif (strpos($file2, '.css') !== false || strpos($file2, '.png') !== false || strpos($file2, '.jpg') !== false || strpos($file2, '.ttf') !== false || strpos($file2, '.woff') !== false) {
                    $other_file[] = 'plugins/' . $plugin->getId() . '/core/template/mobile/' . $file . $file2;
                }
            }
        } elseif (strpos($file, '.js') !== false) {
            $js_file[] = 'plugins/' . $plugin->getId() . '/core/template/mobile/' . $file;
        } elseif (strpos($file, '.css') !== false || strpos($file, '.png') !== false || strpos($file, '.jpg') !== false || strpos($file, '.ttf') !== false || strpos($file, '.woff') !== false) {
            $other_file[] = 'plugins/' . $plugin->getId() . '/core/template/mobile/' . $file;
        }
    }
    if ($plugin->getEventjs() == 1 && file_exists(__DIR__ . '/plugins/' . $plugin->getId() . '/mobile/js/event.js')) {
        $js_file[] = 'plugins/' . $plugin->getId() . '/mobile/js/event.js';
    }
    if ($plugin->getMobile() != '') {
        if (file_exists(__DIR__ . '/' . $plugin->getPathImgIcon())) {
            $other_file[] = $plugin->getPathImgIcon();
        }
        if (method_exists($plugin->getId(), 'mobileManifest')) {
            $plugin_id = $plugin->getId();
            try {
                $plugin_id::mobileManifest();
            } catch (Exception $e) {
                log::add($plugin_id, 'error', __('Erreur sur la fonction mobileManifest du plugin : ', __FILE__) . $e->getMessage());
            }
        }
        foreach (ls(__DIR__ . '/plugins/' . $plugin->getId() . '/mobile/js', '*.js') as $file) {
            echo "\n";
            if (file_exists(__DIR__ . '/plugins/' . $plugin->getId() . '/mobile/js/' . $file)) {
                echo '#' . md5_file(__DIR__ . '/plugins/' . $plugin->getId() . '/mobile/js/' . $file);
                echo "\n";
            }
            echo 'core/php/getJS.php?file=plugins/' . $plugin->getId() . '/mobile/js/' . $file . "\n";
        }
        foreach (ls(__DIR__ . '/plugins/' . $plugin->getId() . '/mobile/html', '*.html') as $file) {
            echo "\n";
            if (file_exists(__DIR__ . '/plugins/' . $plugin->getId() . '/mobile/html/' . $file)) {
                echo '#' . md5_file(__DIR__ . '/plugins/' . $plugin->getId() . '/mobile/html/' . $file);
                echo "\n";
            }
            echo 'index.php?v=m&ajax=1&p=' . substr($file, 0, -5) . '&m=' . $plugin->getId() . "\n";
        }
        foreach (ls(__DIR__ . '/plugins/' . $plugin->getId() . '/mobile/modal', '*.html') as $file) {
            echo "\n";
            if (file_exists(__DIR__ . '/plugins/' . $plugin->getId() . '/mobile/modal/' . $file)) {
                echo '#' . md5_file(__DIR__ . '/plugins/' . $plugin->getId() . '/mobile/modal/' . $file);
                echo "\n";
            }
            echo 'index.php?v=m&ajax=1&modal=' . substr($file, 0, -5) . '&m=' . $plugin->getId() . "\n";
        }
    }
}

foreach ($js_file as $file) {
    echo "\n";
    if (file_exists(__DIR__ . '/' . $file)) {
        echo '#' . md5_file(__DIR__ . '/' . $file);
        echo "\n";
    }
    echo 'core/php/getJS.php?file=' . $file;
    echo "\n";
    echo 'core/php/getJS.php?file=' . $file . '&md5=' . md5_file(__DIR__ . '/' . $file);
    echo "\n";
}
foreach ($other_file as $file) {
    echo "\n";
    if (file_exists(__DIR__ . '/' . $file)) {
        echo '#' . md5_file(__DIR__ . '/' . $file);
        echo "\n";
    }
    echo $file;
    echo "\n";
}
foreach (ls('mobile/js', '*.js') as $file) {
    echo "\n";
    if (file_exists(__DIR__ . '/mobile/js/' . $file)) {
        echo '#' . md5_file(__DIR__ . '/mobile/js/' . $file);
        echo "\n";
    }
    echo 'core/php/getResource.php?file=mobile/js/' . $file;
    echo "\n";
}
foreach (ls('mobile/html', '*.html') as $file) {
    echo "\n";
    if (file_exists(__DIR__ . '/mobile/html/' . $file)) {
        echo '#' . md5_file(__DIR__ . '/mobile/html/' . $file);
        echo "\n";
    }
    echo 'index.php?v=m&ajax=1&p=' . substr($file, 0, -5);
    echo "\n";
}

foreach (ls('mobile/modal', '*.html') as $file) {
    echo "\n";
    if (file_exists(__DIR__ . '/mobile/modal/' . $file)) {
        echo '#' . md5_file(__DIR__ . '/mobile/modal/' . $file);
        echo "\n";
    }
    echo 'index.php?v=m&ajax=1&modal=' . substr($file, 0, -5);
    echo "\n";
}

?>

NETWORK:
*

FALLBACK:
/ mobile/html/fallback.html
