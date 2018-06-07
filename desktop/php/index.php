<?php

global $NEXTDOM_INTERNAL_CONFIG;
global $homeLink;
global $title;
global $language;
global $eventjs_plugin;

use NextDom\Helper\PrepareView;
use NextDom\Helper\Status;
use NextDom\NextDomUtils;

if (Status::isRecueMode() && !in_array(init('p'), array('custom', 'backup', 'cron', 'connection', 'log', 'database', 'editor', 'system'))) {
    $_GET['p'] = 'system';
}
include_file('core', 'authentification', 'php');
Status::initConnectState();

$configs = config::byKeys(array('enableCustomCss', 'language', 'nextdom::firstUse'));

// Détermine la page courante
if (Status::isConnect()) {
    $homePage = explode('::', $_SESSION['user']->getOptions('homePage', 'core::dashboard'));
    if (count($homePage) == 2) {
        if ($homePage[0] == 'core') {
            $homeLink = 'index.php?v=d&p=' . $homePage[1];
        } else {
            $homeLink = 'index.php?v=d&m=' . $homePage[0] . '&p=' . $homePage[1];
        }
        if ($homePage[1] == 'plan' && $_SESSION['user']->getOptions('defaultPlanFullScreen') == 1) {
            $homeLink .= '&fullscreen=1';
        }
    } else {
        $homeLink = 'index.php?v=d&p=dashboard';
    }
}
if (Status::isRecueMode()) {
    $homeLink = 'index.php?v=d&p=system&rescue=1';
}

// Informations générales
$eventjs_plugin = [];
$title = 'NextDom';
if (init('p') == '' && Status::isConnect()) {
    redirect($homeLink);
}
$page = '';
if (Status::isConnect() && init('p') != '') {
    $page = init('p');
    $title = ucfirst($page) . ' - ' . $title;
}
$language = $configs['language'];

// Initialisation des plugins
$plugin = null;
if (!Status::isRecueMode()) {
    $plugin = PrepareView::initMenus($NEXTDOM_INTERNAL_CONFIG);
}

PrepareView::showHeader();

// Affichage du contenu
?>
<body>
<?php
// Affichage de l'écran de connexion
if (!Status::isConnect()) {
    include_file('desktop', 'connection', 'php');
} else {
    // Affichage normal d'une page
    include_file('desktop', 'nextdom', 'css');
    NextDomUtils::sendVarsToJS(array(
        'userProfils' => $_SESSION['user']->getOptions(),
        'user_id' => $_SESSION['user']->getId(),
        'user_isAdmin' => Status::isConnectAdmin(),
        'user_login' => $_SESSION['user']->getLogin(),
        'nextdom_firstUse' => $configs['nextdom::firstUse']
    ));

    if (count($eventjs_plugin) > 0) {
        foreach ($eventjs_plugin as $value) {
            try {
                include_file('desktop', 'event', 'js', $value);
            } catch (Exception $e) {
                log::add($value, 'error', 'Event JS file not found');
            }
        }
    }
    PrepareView::showMenu();
    ?>
    <main class="container-fluid" id="div_mainContainer">
        <div id="div_alert"></div>
        <div id="div_pageContainer">
            <?php
            try {
                if (!nextdom::isStarted()) {
                    PrepareView::showAlertMessage('{{NextDom est en cours de démarrage, veuillez patienter. La page se rechargera automatiquement une fois le démarrage terminé.}}');
                }
                if ($plugin !== null && is_object($plugin)) {
                    include_file('desktop', $page, 'php', $plugin->getId());
                } else {
                    include_file('desktop', $page, 'php');
                }
            } catch (Exception $e) {
                ob_end_clean();
                PrepareView::showAlertMessage(displayException($e));
            } catch (Error $e) {
                ob_end_clean();
                PrepareView::showAlertMessage(displayException($e));
            }
            ?>
        </div>
        <div id="md_modal"></div>
        <div id="md_modal2"></div>
        <div id="md_pageHelp" title="Aide">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#div_helpWebsite" data-toggle="tab">{{Générale}}</a></li>
                <li><a href="#div_helpSpe" data-toggle="tab">{{Détaillée}}</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="div_helpWebsite"></div>
                <div class="tab-pane" id="div_helpSpe"></div>
            </div>
        </div>
        <div id="md_reportBug" title="{{Ouverture d'un ticket}}"></div>
    </main>
    <?php
}
?>
</body>
</html>
