/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

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
*
* @Support <https://www.nextdom.org>
* @Email <admin@nextdom.org>
* @Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
*/

var tab = null;
var editor = [];
var GENERAL_TAB = 'generaltab';
var PROGRAM_TAB = 'scenariotab';
var SC_CLIPBOARD = null;
var BLOC_FOCUS = null;
var ACTION_FOCUS = null;
var BLOC_LAST_FOCUS = false;
var BLOC_CHANGE_COLOR = true;
var currentExpression = null;
var undoStack = new Array();
var undoStackPosition = -1;
var undoLimit = 15;

/**
 * List of colors for scenario elements
 * @type {string[]}
 */
var listColor = ['#16a085', '#27ae60', '#2980b9', '#745cb0', '#f39c12', '#d35400', '#c0392b', '#2c3e50', '#7f8c8d'];
var listColorStrong = ['#12846D', '#229351', '#246F9E', '#634F96', '#D88811', '#B74600', '#A53026', '#1D2935', '#687272'];
var colorIndex = 0;

/* Space before is normal */
var autoCompleteCondition = [
    { value : '#IP#', label : ' #IP# : IP interne de NextDom' },
    { value : '#hostname#', label : ' #hostname# : Nom de la machine NextDom' },
    { value : '#date#', label : ' #date# : Jour et mois. Attention, le premier nombre est le mois. (ex : 1215 pour le 15 décembre)' },
    { value : '#seconde#', label : ' #seconde# : Seconde courante (sans les zéros initiaux, ex : 6 pour 08:07:06)' },
    { value : '#minute#', label : ' #minute# : Minute courante (sans les zéros initiaux, ex : 7 pour 08:07:06)' },
    { value : '#heure#', label : ' #heure# : Heure courante au format 24h (sans les zéros initiaux, ex : 8 pour 08:07:06 ou 17 pour 17:15)' },
    { value : '#heure12#', label : '#heure12# : Heure courante au format 12h (sans les zéros initiaux, ex : 8 pour 08:07:06)' },
    { value : '#jour#', label : ' #jour# : Jour courant (sans les zéros initiaux, ex : 6 pour 06/07/2017)' },
    { value : '#njour#', label : ' #njour# : Numéro du jour de 0 (dimanche) à 6 (samedi)' },
    { value : '#sjour#', label : ' #sjour# : Nom du jour de la semaine (ex : Samedi)' },
    { value : '#semaine#', label : ' #semaine# : Numéro de la semaine (ex : 51)' },
    { value : '#mois#', label : ' #mois# : Mois courant (sans les zéros initiaux, ex : 7 pour 06/07/2017)' },
    { value : '#smois#', label : ' #smois# : Nom du mois (ex : Janvier)' },
    { value : '#annee#', label : ' #annee# : Année courante' },
    { value : '#time#', label : ' #time# : Heure et minute courante (ex : 1715 pour 17h15)' },
    { value : '#timestamp#', label : ' #timestamp# : Nombre de secondes depuis le 1er janvier 1970' },
    { value : '#profil#', label : ' #profil# : profil de l\'utilisateur ayant déclenché le scénario (peut être vide)' },
    { value : '#query#', label : ' #query# : interaction ayant déclenché le scénario' },
    { value : '#trigger#', label : ' #trigger# : Peut être le nom de la commande qui a déclenché le scénario, (api) si le lancement a été déclenché par l\'API, (schedule) si il a été lancé par une programmation, (user) si il a été lancé manuellement' },
    { value : 'average(commande,periode)', label : ' average(commande,periode) : Donnent la moyenne de la commande sur la période (period=[month,day,hour,min] ou expression PHP)' },
    { value : 'averageBetween(commande,start,end)', label : ' averageBetween(commande,start,end) : Donnent la moyenne entre les 2 bornes demandées (sous la forme Y-m-d H:i:s ou expression PHP)' },
    { value : 'avg(commande1,commande2,commandeN)', label : ' avg(commande1,commande2,commandeN) : Renvoie la moyenne des valeurs' },
    { value : 'collectDate(cmd,[format])', label : ' collectDate(cmd,[format]) : Renvoie la date de la dernière donnée pour la commande donnée en paramètre, le 2ème paramètre optionnel permet de spécifier le format de retour (détails ici). Un retour de -1 signifie que la commande est introuvable et -2 que la commande n\'est pas de type info' },
    { value : 'color_gradient(couleur_debut,couleur_fin,valuer_min,valeur_max,valeur)', label : ' color_gradient(couleur_debut,couleur_fin,valuer_min,valeur_max,valeur) : Renvoi une couleur calculé par rapport à valeur dans l\'intervalle couleur_debut/couleur_fin. La valeur doit etre comprise entre valeur_min et valeur_max' },
    { value : 'convertDuration(secondes)', label : ' convertDuration(secondes) : Permet de convertir des secondes en j/h/mn/s' },
    { value : 'duration(commande, valeur, période)', label : ' duration(commande, valeur, période) : Donnent la durée en minutes pendant laquelle l\'équipement avait la valeur choisie sur la période (period=[month,day,hour,min] ou expression PHP)' },
    { value : 'durationbetween(commande,valeur,start,end)', label : ' durationbetween(commande,valeur,start,end) : Donnent la durée en minutes entre les 2 bornes demandées (sous la forme Y-m-d H:i:s ou expression PHP)' },
    { value : 'eqEnable(equipement)', label : ' eqEnable(equipement) : Renvoie l\'état de l\'équipement. -2 si l\'équipement est introuvable, 1 si l\'équipement est actif et 0 s\'il est inactif' },
    { value : 'floor(time/60)', label : ' floor(time/60) : Permet de convertir des secondes en minutes, ou des minutes en heures (floor(time/3600) pour des secondes en heures)' },
    { value : 'formatTime(time)', label : ' formatTime(time) : Permet de formater le retour d\'une chaine #time#' },
    { value : 'lastBetween(commande,start,end)', label : ' lastBetween(commande,start,end) : Donne la dernière valeur enregistrée pour l\'équipement entre les 2 bornes demandées (sous la forme Y-m-d H:i:s ou expression PHP)' },
    { value : 'lastChangeStateDuration(commande,value)', label : ' lastChangeStateDuration(commande,value) : Donne la durée en secondes depuis le dernier changement d\'état à la valeur passée en paramètre. Retourne -1 si aucun historique n\'existe ou si la valeur n\'existe pas dans l\'historique. Retourne -2 si la commande n\'est pas historisée' },
    { value : 'lastCommunication(equipment,[format])', label : ' lastCommunication(equipment,[format]) : Renvoie la date de la dernière communication pour l\'équipement donnée en paramètre, le 2ème paramètre optionnel permet de spécifier le format de retour (détails ici). Un retour de -1 signifie que l\'équipment est introuvable' },
    { value : 'lastScenarioExecution(scenario)', label : ' lastScenarioExecution(scenario) : Donne la durée en secondes depuis le dernier lancement du scénario, renvoi 0 si le scénario n\'existe pas' },
    { value : 'lastStateDuration(commande,value)', label : ' lastStateDuration(commande,value) : Donne la durée en secondes pendant laquelle l\'équipement a dernièrement eu la valeur choisie. Retourne -1 si aucun historique n\'existe ou si la valeur n\'existe pas dans l\'historique. Retourne -2 si la commande n\'est pas historisée' },
    { value : 'min(commande,periode)', label : ' min(commande,periode) : Donnent le minimum de la commande sur la période (period=[month,day,hour,min] ou expression PHP)' },
    { value : 'minBetween(commande,start,end)', label : ' minBetween(commande,start,end) : Donnent le minimum de la commande entre les 2 bornes demandées (sous la forme Y-m-d H:i:s ou expression PHP)' },
    { value : 'median(commande1,commande2,commandeN)', label : ' median(commande1,commande2,commandeN) : Renvoie la médiane des valeurs' },
    { value : 'max(commande,periode)', label : ' max(commande,periode) : Donnent le maximum de la commande sur la période (period=[month,day,hour,min] ou expression PHP)' },
    { value : 'maxBetween(commande,start,end)', label : ' maxBetween(commande,start,end) : Donnent le maximum de la commande entre les 2 bornes demandées (sous la forme Y-m-d H:i:s ou expression PHP)' },
    { value : 'name(type,commande)', label : ' name(type,commande) : Permet de récuperer le nom de la commande, de l\'équipement ou de l\'objet. Type vaut soit cmd, eqLogic ou object' },
    { value : 'odd(valeur)', label : ' odd(valeur) : Permet de savoir si un nombre est impair ou non. Renvoie 1 si impair 0 sinon' },
    { value : 'rand(MIN,MAX)', label : ' rand(MIN,MAX) : Donne un nombre aléatoire de MIN à MAX' },
    { value : 'randText(texte1;texte2;texteN)', label : ' randText(texte1;texte2;texteN) : Permet de retourner un des textes aléatoirement (séparer les texte par un ; ). Il n\'y a pas de limite dans le nombre de texte' },
    { value : 'randomColor(min,max)', label : ' randomColor(min,max) : Donne une couleur aléatoire compris entre 2 bornes ( 0 => rouge, 50 => vert, 100 => bleu)' },
    { value : 'round(valeur,[decimal])', label : ' round(valeur,[decimal]) : Donne un arrondi au-dessus, [decimal] nombre de décimales après la virgule' },
    { value : 'scenario(scenario)', label : ' scenario(scenario) : Renvoie le statut du scénario. 1 en cours, 0 si arrêté et -1 si désactivé, -2 si le scénario n\'existe pas et -3 si l\'état n\'est pas cohérent. Pour avoir le nom "humain" du scénario, vous pouvez utiliser le bouton dédié à droite de la recherche de scénario' },
    { value : 'stateChanges(commande,valeur,période)', label : ' stateChanges(commande,valeur,période) : Donnent le nombre de changements d\'état (vers une certaine valeur si indiquée, ou au total sinon) sur la période (period=[month,day,hour,min] ou expression PHP)' },
    { value : 'stateChangesBetween(commande,valeur,start,end)', label : ' stateChangesBetween(commande,valeur,start,end) : Donnent le nombre de changements d\'état (vers une certaine valeur si indiquée, ou au total sinon) entre les 2 bornes demandées (sous la forme Y-m-d H:i:s ou expression PHP)' },
    { value : 'stateDuration(commande)', label : ' stateDuration(commande) : Donne la durée en secondes depuis le dernier changement de valeur. Retourne -1 si aucun historique n\'existe ou si la valeur n\'existe pas dans l\'historique. Retourne -2 si la commande n\'est pas historisée' },
    { value : 'statistics(commande,calcul,période)', label : ' statistics(commande,calcul,période) : Donnent le résultat de différents calculs statistiques (sum, count, std, variance, avg, min, max) sur la période (period=[month,day,hour,min] ou expression PHP)' },
    { value : 'statisticsBetween(commande,calcul,start,end)', label : ' statisticsBetween(commande,calcul,start,end) : Donnent le résultat de différents calculs statistiques (sum, count, std, variance, avg, min, max) entre les 2 bornes demandées (sous la forme Y-m-d H:i:s ou expression PHP)' },
    { value : 'tag(montag,[defaut])', label : ' tag(montag,[defaut]) : Permet de récupérer la valeur d\'un tag ou la valeur par défaut si il n\'existe pas' },
    { value : 'tendance(commande,periode)', label : ' tendance(commande,periode) : Donne la tendance de la commande sur la période (period=[month,day,hour,min] ou expression PHP)' },
    { value : 'time_between(time,start,end)', label : ' time_between(time,start,end) : Permet de tester si un temps est entre deux valeurs avec time=temps (ex : 1530), start=temps, end=temps. Les valeurs start et end peuvent être à cheval sur minuit' },
    { value : 'time_diff(date1,date1[,format])', label : ' time_diff(date1,date1[,format]) : Permet de connaître la différence entre 2 dates (les dates doivent être au format AAAA/MM/JJ HH:MM:SS). Par défaut (si vous ne mettez rien pour format), la méthode retourne le nombre total de jours. Vous pouvez lui demander en secondes (s), minutes (m), heures (h). Exemple en secondes time_diff(2018-02-02 14:55:00,2018-02-25 14:55:00,s)' },
    { value : 'time_op(time,value)', label : ' time_op(time,value) : Permet de faire des opérations sur le temps, avec time=temps (ex : 1530) et value=valeur à ajouter ou à soustraire en minutes' },
    { value : 'trigger(commande)', label : ' trigger(commande) : Permet de connaître le déclencheur du scénario ou de savoir si c\'est bien la commande passée en paramètre qui a déclenché le scénario' },
    { value : 'triggerValue(commande)', label : ' triggerValue(commande) : Permet de connaître la valeur du déclencheur du scénario' },
    { value : 'variable(mavariable,defaut)', label : ' variable(mavariable,defaut) : Récupère la valeur d\'une variable ou de la valeur souhaitée par défaut' },
    { value : 'value(commande)', label : ' value(commande) : Renvoie la valeur d\'une commande si elle n\'est pas donnée automatiquement par NextDom (cas lors du stockage du nom de la commande dans une variable)' },
    { value : 'valueDate(cmd,[format])', label : ' valueDate(cmd,[format]) : Renvoie la date de la dernière donnée pour la commande donnée en paramètre, le 2ème paramètre optionnel permet de spécifier le format de retour (détails ici). Un retour de -1 signifie que la commande est introuvable et -2 que la commande n\'est pas de type info' }
];
autoCompleteAction = [
  { value : 'alert', label : ' alert : Permet d\'afficher un petit message d\'alerte sur tous les navigateurs qui ont une page NextDom d\'ouvert. Vous pouvez en plus choisir 4 niveaux d\'alerte' },
  { value : 'ask', label : ' ask : Action qui permet à NextDom de faire une demande puis de stocker la réponse dans une variable. Cette action est bloquante et ne finit que si NextDom reçoit une réponse ou si le timeout est atteint. Pour le moment cette action n\'est compatible qu\'avec les plugins SMS, Slack, SARAH et Telegram.' },
  { value : 'delete_variable', label : ' delete_variable : Supprimer une variable' },
  { value : 'equipement', label : ' equipement : Permet de modifier les proriétés visible/invisible actif/inactif d\'un équipement' },
  { value : 'event', label : ' event : Permet de pousser une valeur dans une commande de type information de maniere arbitraire' },
  { value : 'gotodesign', label : ' gotodesign : Sur tous les navigateurs qui affichent un design, le remplace par celui demandé' },
  { value : 'icon', label : ' icon : Permet d\'affecter une icône au scénario' },
  { value : 'log', label : ' log : Permet de rajouter un message dans les logs' },
  { value : 'message', label : ' message : Permet d\'ajouter une message dans le centre de message' },
  { value : 'nextdom_poweroff', label : ' nextdom_poweroff : Envoi l\'ordre à NextDom de s\'éteindre' },
  { value : 'nextdom_reboot', label : ' nextdom_reboot : Envoi l\'ordre à NextDom de redémarrer' },
  { value : 'popup', label : ' popup : Permet d\'afficher un popup qui doit absolument être validé sur tous les navigateurs qui ont une page NextDom ouverte.' },
  { value : 'remove_inat', label : ' remove_inat : Permet de supprimer la programmation de tous les blocs DANS et A du scénario' },
  { value : 'report', label : ' report : Permet d\'envoyer, par une commande message, un rapport d\'une vue, d\'un design ou d\'un panel en PNG/PDF/JPEG/SVG.' },
  { value : 'scenario', label : ' scenario : Permet le contrôle des scénarios' },
  { value : 'scenario_return', label : ' scenario_return : Retourne un texte ou une valeur pour une interaction par exemple' },
  { value : 'sleep', label : ' sleep : Pause de x seconde(s)' },
  { value : 'stop', label : ' stop : Arrête le scénario' },
  { value : 'tag', label : ' tag : Permet d\'ajouter/modifier un tag (le tag n\'existe que pendant l\'execution en cours du scénario à la difference des variables qui survive à la fin du scénario' },
  { value : 'variable', label : ' variable : Création/modification d\'une variable ou de la valeur d\'une variable' },
  { value : 'wait', label : ' wait : Attend jusqu\'à ce que la condition soit valide (maximum 2h)' }
];

/* Containers variables */
var pageContainer = $('#div_pageContainer');
var modalContainer = $('#md_modal');
var scenarioContainer = $('#div_scenarioElement');
var undoBtnSpan = $('#bt_undo').find('span');
var redoBtnSpan = $('#bt_redo').find('span');

// Page init
loadInformations();
initEvents();

/**
 * Load informations in all forms of the page
 */
function loadInformations() {
    loadFromUrl();
    setTimeout(function () {
        $('.scenarioListContainer').packery();
    }, 100);
    $(document).ready(function () {
        modifyWithoutSave = false;
        $(".bt_cancelModifs").hide();
    });
}

/**
 * Init events on the profils page
 */
function initEvents() {
    initListEvents();
    initGeneralFormEvents();
    initScenarioEditorEvents();
    initModalEvents();
}

/**
 * Init modal events
 */
function initModalEvents() {
    // Links modal open button
    $('#bt_graphScenario').off('click').on('click', function () {
        modalContainer.dialog({title: "{{Graphique de lien(s)}}"});
        modalContainer.load('index.php?v=d&modal=graph.link&filter_type=scenario&filter_id=' + $('.scenarioAttr[data-l1key=id]').value()).dialog('open');
    });

    // Log modale open button
    $('#bt_logScenario').off('click').on('click', function () {
        modalContainer.dialog({title: "{{Log d'exécution du scénario}}"});
        modalContainer.load('index.php?v=d&modal=scenario.log.execution&scenario_id=' + $('.scenarioAttr[data-l1key=id]').value()).dialog('open');
    });

    // Template modale open button
    $('#bt_templateScenario').off('click').on('click', function () {
        modalContainer.dialog({title: "{{Template de scénario}}"});
        modalContainer.load('index.php?v=d&modal=scenario.template&scenario_id=' + $('.scenarioAttr[data-l1key=id]').value()).dialog('open');
    });
}

/**
 * Init events of the list
 */
function initListEvents() {
    // Scenario click to show details and programmation
    $('.scenarioDisplayCard').off('click').on('click', function () {
        loadScenario($(this).attr('data-scenario_id'), GENERAL_TAB);
    });

    // Category panel collapsing/uncollapsing change
    $('.accordion-toggle').off('click').on('click', function () {
        setTimeout(function () {
            $('.scenarioListContainer').packery();
        }, 100);
    });

    // Scenario list go back button
    $('#bt_scenarioThumbnailDisplay').off('click').on('click', function () {
        loadPage('index.php?v=d&p=scenario');
    });

    // All scenario state change button
    $("#bt_changeAllScenarioState").off('click').on('click', toggleAllScenariosState);

    // New scenario add button
    $("#bt_addScenario").off('click').on('click', addScenario);
}

/**
 * Init events of the general tab
 */
function initGeneralFormEvents() {
    // Param changed : page leaving lock by msgbox
    pageContainer.off('change', '.scenarioAttr').on('change', '.scenarioAttr', function () {
        if (!lockModify) {
            modifyWithoutSave = true;
            $(".bt_cancelModifs").show();
        }
    });
    pageContainer.off('change', '.expressionAttr').on('change', '.expressionAttr', function () {
        if (!lockModify) {
            modifyWithoutSave = true;
            $(".bt_cancelModifs").show();
        }
    });
    pageContainer.off('change', '.elementAttr').on('change', '.elementAttr', function () {
        if (!lockModify) {
            modifyWithoutSave = true;
            $(".bt_cancelModifs").show();
        }
    });
    pageContainer.off('change', '.subElementAttr').on('change', '.subElementAttr', function () {
        if (!lockModify) {
            modifyWithoutSave = true;
            $(".bt_cancelModifs").show();
        }
    });
    $("#div_scenarioElement").off('change').on('change', function () {
        if (!lockModify) {
            modifyWithoutSave = true;
            $(".bt_cancelModifs").show();
        }
    });

    // Cancel modifications
    $('.bt_cancelModifs').off('click').on('click', function () {
        loadFromUrl();
    });

    // Choose icon in scenario form
    $('#bt_chooseIcon').off('click').on('click', function () {
        chooseIcon(function (_icon) {
            $('.scenarioAttr[data-l1key=display][data-l2key=icon]').empty().append(_icon);
        });
    });

    // Reset timeout
    $('#bt_resetTimeout').off('click').on('click', function () {
        $(this).siblings(".slider").value(0);
    });

    // Group autocomplete in scenario form
    $('.scenarioAttr[data-l1key=group]').autocomplete({
        source: function (request, response, url) {
            $.ajax({
                type: 'POST',
                url: 'src/ajax.php',
                data: {
                    target: 'Scenario',
                    action: 'autoCompleteGroup',
                    term: request.term
                },
                dataType: 'json',
                global: false,
                error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                },
                success: function (data) {
                    if (data.state !== 'ok') {
                        notify('Erreur', data.result, 'error');
                        return;
                    }
                    response(data.result);
                }
            });
        },
        minLength: 1,
    });

    // Scenario panel collasping
    $('#bt_scenarioCollapse').off('click').on('click',function(){
       $('#accordionScenario .panel-collapse').each(function () {
          if (!$(this).hasClass("in")) {
              $(this).css({'height' : '' });
              $(this).addClass("in");
          }
       });
       $('#bt_scenarioCollapse').hide();
       $('#bt_scenarioUncollapse').show()
    });

    // Scenario panel uncollasping
    $('#bt_scenarioUncollapse').off('click').on('click',function(){
       $('#accordionScenario .panel-collapse').each(function () {
          if ($(this).hasClass("in")) {
              $(this).removeClass("in");
          }
       });
       $('#bt_scenarioUncollapse').hide();
       $('#bt_scenarioCollapse').show()
    });

    // Save button
    $("#bt_saveScenario").off('click').on('click', saveScenario);

    // Delete button
    $("#bt_delScenario").off('click').on('click', deleteScenario);

    // Test button
    $("#bt_testScenario").off('click').on('click', testScenario);

    // Copy button
    $("#bt_copyScenario").off('click').on('click', copyScenario);

    // Stop button
    $("#bt_stopScenario").off('click').on('click', stopScenario);

    // Variables display button
    $(".bt_displayScenarioVariable").off('click').on('click', function () {
        modalContainer.dialog({title: "{{Variables des scénarios}}"});
        modalContainer.load('index.php?v=d&modal=dataStore.management&type=scenario').dialog('open');
    });

    // Expression test modale display button
    $('.bt_showExpressionTest').off('click').on('click', function () {
        modalContainer.dialog({title: "{{Testeur d'expression}}"});
        modalContainer.load('index.php?v=d&modal=expression.test').dialog('open');
    });

    // Summary modal display button
    $('.bt_showScenarioSummary').off('click').on('click', function () {
        modalContainer.dialog({title: "{{Résumé scénario}}"});
        modalContainer.load('index.php?v=d&modal=scenario.summary').dialog('open');
    });

    // Bloc add modale element choose
    $('#in_addElementType').off('change').on('change', function () {
        $('.addElementTypeDescription').hide();
        $('.addElementTypeDescription.' + $(this).value()).show();
    });

    // Programmation tab click
    $('#bt_scenarioTab').off('click').on('click', function () {
        setTimeout(function () {
            setEditor();
            initTextAreaAutosize();
        }, 100);
    });

    // Cron programmation help button
    pageContainer.off('click', '.helpSelectCron').on('click', '.helpSelectCron', function () {
        var el = $(this).closest('.schedule').find('.scenarioAttr[data-l1key=schedule]');
        nextdom.getCronSelectModal({}, function (result) {
            el.value(result.value);
        });
    });

    // Launching mode selection
    $('.scenarioAttr[data-l1key=mode]').off('change').on('change', function () {
        var mode = $(this).value();
        if (mode === 'schedule' || mode === 'all') {
            $('.scheduleDisplay').show();
            $('#bt_addSchedule').show();
        } else {
            $('.scheduleDisplay').hide();
            $('#bt_addSchedule').hide();
        }
        if (mode === 'provoke' || mode === 'all') {
            $('.provokeDisplay').show();
            $('#bt_addTrigger').show();
        } else {
            $('.provokeDisplay').hide();
            $('#bt_addTrigger').hide();
        }
    });

    // Launching trigger add button
    $('#bt_addTrigger').off('click').on('click', function () {
        addTrigger('');
    });

    // Launching schedule add button
    $('#bt_addSchedule').off('click').on('click', function () {
        addSchedule('');
    });

    // Launching trigger remove button
    pageContainer.off('click', '.bt_removeTrigger').on('click', '.bt_removeTrigger', function (event) {
        $(this).closest('.trigger').remove();
    });

    // Launching schedule remove button
    pageContainer.off('click', '.bt_removeSchedule').on('click', '.bt_removeSchedule', function (event) {
        $(this).closest('.schedule').remove();
    });

    // Cmd choose for launching trigger
    pageContainer.off('click', '.bt_selectTrigger').on('click', '.bt_selectTrigger', function (event) {
        var el = $(this);
        nextdom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
            el.closest('.trigger').find('.scenarioAttr[data-l1key=trigger]').value(result.human);
        });
    });

    // Variable choose for launching trigger
    pageContainer.off('click', '.bt_selectDataStoreTrigger').on('click', '.bt_selectDataStoreTrigger', function (event) {
        var el = $(this);
        nextdom.dataStore.getSelectModal({cmd: {type: 'info'}}, function (result) {
            el.closest('.trigger').find('.scenarioAttr[data-l1key=trigger]').value(result.human);
        });
    });

    // Launching trigger remove button
    pageContainer.off('click', '.openUsedBy').on('click', '.openUsedBy', function (event) {
        loadScenario($(this).attr('data-scenario_id'), GENERAL_TAB);
    });
}

/**
 * Init events on the scenario editor
 */
function initScenarioEditorEvents() {
    // Bloc add button
    pageContainer.off('click', '.bt_addScenarioElement').on('click', '.bt_addScenarioElement', function (event) {
        var elementDiv = $(this).closest('.element');
        if (elementDiv.html() === undefined) {
            elementDiv = scenarioContainer;
        }
        var expression = false;
        if (ACTION_FOCUS || BLOC_FOCUS) {
            if (BLOC_LAST_FOCUS) {
                if (BLOC_FOCUS.parent().parent().hasClass('expression')) {
                    elementDiv = BLOC_FOCUS.parent().parent();
                    expression = true;
                } else {
                    elementDiv = BLOC_FOCUS;
                }
            } else {
                elementDiv = ACTION_FOCUS;
                expression = true;
            }
        } else {
            if ($(this).hasClass('fromSubElement')) {
                elementDiv = $(this).closest('.subElement').find('.expressions').eq(0);
                expression = true;
            }
        }
        $('#md_addElement').modal('show');

        // Bloc add validation button
        $("#bt_addElementSave").off('click').on('click', function (event) {
            $('#md_addElement').modal('hide');
            clearRedoStack();
            if (ACTION_FOCUS || BLOC_FOCUS) {
                if (expression) {
                    newDiv=addExpression({type: 'element', element: {type: $("#in_addElementType").value()}});
                } else {
                    $('#div_scenarioElement .span_noScenarioElement').remove();
                    newDiv=addElement({type: $("#in_addElementType").value()});
                }
                $(newDiv).insertAfter(elementDiv);
            } else {
                if (expression) {
                    elementDiv.append(addExpression({type: 'element', element: {type: $("#in_addElementType").value()}}));
                } else {
                    $('#div_scenarioElement .span_noScenarioElement').remove();
                    elementDiv.append(addElement({type: $("#in_addElementType").value()}));
                }
            }
            setEditor();
            setAutocomplete();
            updateSortable();
            setInputExpressionsEvent();
            setUndoStack();
        });
    });

    // Bloc remove button
    pageContainer.off('click', '.bt_removeElement').on('click', '.bt_removeElement', function (event) {
        clearRedoStack();
        if ($(this).closest('.expression').length !== 0) {
            $(this).closest('.expression').remove();
        } else {
            $(this).closest('.element').remove();
        }
        setUndoStack();
    });

    // Bloc action add button
    pageContainer.off('click', '.bt_addAction').on('click', '.bt_addAction', function (event) {
        clearRedoStack();
        $(this).closest('.subElement').children('.expressions').append(addExpression({type: 'action'}));
        setAutocomplete();
        updateSortable();
        setUndoStack();
    });

    // Bloc add buttons
    pageContainer.off('click','.fromSubElement').on( 'click','.fromSubElement ', function (event) {
        var elementType = $(this).attr('data-type');
        var elementDiv = $(this).closest('.element');
        if (elementDiv.html() === undefined) {
            elementDiv = scenarioContainer;
        }
        var expression = false;
        if ($(this).hasClass('fromSubElement')) {
            elementDiv = $(this).closest('.subElement').find('.expressions').eq(0);
            expression = true;
        }
        clearRedoStack();
        if (expression) {
            elementDiv.append(addExpression({type: 'element', element: {type: elementType}}));
        } else {
            $('#div_scenarioElement .span_noScenarioElement').remove();
            elementDiv.append(addElement({type: elementType}));
        }
        setEditor();
        setAutocomplete();
        updateSortable();
        setInputExpressionsEvent();
        setUndoStack();
    })

    // Bloc else button
    pageContainer.off('click', '.bt_addSinon').on('click', '.bt_addSinon', function (event) {
        if ($(this).children("i").hasClass('fa-chevron-right')) {
            $(this).children("i").removeClass('fa-chevron-right').addClass('fa-chevron-down');
            $(this).closest('.subElement').next().css('display', 'table');
        }
        else {
            if ($(this).closest('.subElement').next().children('.expressions').children('.expression').length > 0) {
                alert("{{Le bloc Sinon ne peut être supprimé s'il contient des éléments}}");
            }
            else {
                $(this).children("i").removeClass('fa-chevron-down').addClass('fa-chevron-right');
                $(this).closest('.subElement').next().css('display', 'none');
            }
        }
    });

    // Bloc action expression clear
    pageContainer.off('click', '.bt_removeExpression').on('click', '.bt_removeExpression', function () {
        clearRedoStack();
        $(this).closest('.expression').remove();
        updateSortable();
        setUndoStack();
    });

    // Bloc action expression cmd choose button
    pageContainer.off('click', '.bt_selectCmdExpression').on('click', '.bt_selectCmdExpression', function () {
        selectCmdExpression($(this), $(this).closest('.expression'));
    });

    // Bloc action expression key word choose button
    pageContainer.off('click', '.bt_selectOtherActionExpression').on('click', '.bt_selectOtherActionExpression', function (event) {
        var expression = $(this).closest('.expression');
        nextdom.getSelectActionModal({scenario: true}, function (result) {
            expression.find('.expressionAttr[data-l1key=expression]').value(result.human);
            nextdom.cmd.displayActionOption(expression.find('.expressionAttr[data-l1key=expression]').value(), '', function (html) {
                clearRedoStack();
                expression.find('.expressionOptions').html(html);
                initTextAreaAutosize();
                setUndoStack();
            });
        });
    });

    // Bloc action expression scenario choose button
    pageContainer.off('focusout', '.expression .expressionAttr[data-l1key=expression]').on('focusout', '.expression .expressionAttr[data-l1key=expression]', function (event) {
        var el = $(this);
        if (el.closest('.expression').find('.expressionAttr[data-l1key=type]').value() === 'action') {
            var expression = el.closest('.expression').getValues('.expressionAttr');
            if (el.value() !== currentExpression) {
                currentExpression = el.value();
                nextdom.cmd.displayActionOption(el.value(), init(expression[0].options), function (html) {
                    el.closest('.expression').find('.expressionOptions').html(html);
                    initTextAreaAutosize();
                });
            }
        }
    });

    // Bloc action expression equipement choose button
    pageContainer.off('click', '.bt_selectEqLogicExpression').on('click', '.bt_selectEqLogicExpression', function (event) {
        var expression = $(this).closest('.expression');
        nextdom.eqLogic.getSelectModal({}, function (result) {
            if (expression.find('.expressionAttr[data-l1key=type]').value() === 'action') {
                expression.find('.expressionAttr[data-l1key=expression]').value(result.human);
            }
            if (expression.find('.expressionAttr[data-l1key=type]').value() === 'condition') {
                expression.find('.expressionAttr[data-l1key=expression]').atCaret('insert', result.human);
            }
        });
    });

    // Bloc action expression change
    pageContainer.off('focusout', '.expression .expressionAttr[data-l1key=expression]').on('focusout', '.expression .expressionAttr[data-l1key=expression]', function (event) {
        var el = $(this);
        if (el.closest('.expression').find('.expressionAttr[data-l1key=type]').value() === 'action') {
            var expression = el.closest('.expression').getValues('.expressionAttr');
            nextdom.cmd.displayActionOption(el.value(), init(expression[0].options), function (html) {
                el.closest('.expression').find('.expressionOptions').html(html);
                initTextAreaAutosize();
            });
        }
    });

    // Bloc condition scenario choose button
    pageContainer.off('click','.bt_selectScenarioExpression').on('click','.bt_selectScenarioExpression',  function (event) {
        var expression = $(this).closest('.expression');
        nextdom.scenario.getSelectModal({}, function (result) {
            if (expression.find('.expressionAttr[data-l1key=type]').value() == 'action') {
                expression.find('.expressionAttr[data-l1key=expression]').value(result.human);
            }
            if (expression.find('.expressionAttr[data-l1key=type]').value() == 'condition') {
                expression.find('.expressionAttr[data-l1key=expression]').atCaret('insert', result.human);
            }
        });
    });

    // Bloc action repetition button
    pageContainer.off('click', '.subElementAttr[data-l1key=options][data-l2key=allowRepeatCondition]').on('click', '.subElementAttr[data-l1key=options][data-l2key=allowRepeatCondition]', function () {
        if (parseInt($(this).attr('value')) === 0) {
            $(this).attr('value', 1);
            $(this).html('<i class="fas fa-ban text-danger"></i>');
        } else {
            $(this).attr('value', 0);
            $(this).html('<i class="fas fa-sync-alt">');
        }
    });

    // Bloc dragging
    pageContainer.off('mouseenter', '.bt_sortable').on('mouseenter', '.bt_sortable', function () {
        var expressions = $(this).closest('.expressions');
        scenarioContainer.sortable({
            items: ".sortable",
            opacity: 0.7,
            forcePlaceholderSize: true,
            forceHelperSize: true,
            placeholder: "sortable-placeholder",
            tolerance: "intersect",
            grid: [30, 15],
            update: function (event, ui) {
                clearRedoStack();
                if (ui.item.findAtDepth('.element', 2).length === 1 && ui.item.parent().attr('id') === 'div_scenarioElement') {
                    ui.item.replaceWith(ui.item.findAtDepth('.element', 2));
                }
                if (ui.item.hasClass('element') && ui.item.parent().attr('id') !== 'div_scenarioElement') {
                    ui.item.replaceWith(addExpression({
                        type: 'element',
                        element: {html: ui.item.clone().wrapAll("<div/>").parent().html()}
                    }));
                }
                if (ui.item.hasClass('expression') && ui.item.parent().attr('id') === 'div_scenarioElement') {
                    scenarioContainer.sortable("cancel");
                }
                if (ui.item.closest('.subElement').hasClass('noSortable')) {
                    scenarioContainer.sortable("cancel");
                }
                updateSortable();
                setUndoStack();
            },
            start: function (event, ui) {
                if (expressions.find('.sortable').length < 3) {
                    expressions.find('.sortable.empty').show();
                }
            }
        });
        scenarioContainer.sortable("enable");
    });

    // Bloc dropping
    pageContainer.off('mouseout', '.bt_sortable').on('mouseout', '.bt_sortable', function () {
        scenarioContainer.sortable("disable");
    });

    // Undo button
    $('#bt_undo').off('click').on('click', function (event) {
        undo();
        BLOC_LAST_FOCUS = null;
    });

    // Redo button
    $('#bt_redo').off('click').on('click', function (event) {
        redo();
        BLOC_LAST_FOCUS = null;
    });

    // Set memory button
    $('#bt_memoSet').off('click').on('click', function (event) {
        setMemoryStack();
    });

    // Reset memory button
    $('#bt_memoReset').off('click').on('click', function (event) {
        resetMemoryStack();
    });

    // Recall memory button
    $('#bt_memoRecall').off('click').on('click', function (event) {
        recallMemoryStack();
    });

    // Bloc focusing
    scenarioContainer.off('focus', ':input').on('focus', ':input', function() {
        blocFocusing($(this),false);
        if ($(this).closest(".expression").find(".expressionAttr[data-l1key='type']").filter(function() { return this.value == 'condition' }).length==0) {
            BLOC_LAST_FOCUS = false;
        } else {
            BLOC_LAST_FOCUS = true;
        }
    })
    scenarioContainer.off('click', '.scenario-title').on('click', '.scenario-title', function() {
        blocFocusing($(this),true);
    })
    scenarioContainer.off('click', '.scenario-action-bloc').on('click', '.scenario-action-bloc', function() {
        blocFocusing($(this),true);
    })


    // Bloc copy / cut
    $('#bt_copyBloc').off('click').on('click', function () {
        blocCopy(BLOC_FOCUS);
        BLOC_CHANGE_COLOR=true;
        $('.bt_ScenarioBloc').show();
        $('.bt_ScenarioAction').hide();
    });
    $('#bt_cutBloc').off('click').on('click', function (event) {
        blocCopy(BLOC_FOCUS,true);
        BLOC_CHANGE_COLOR=false;
        $('.bt_ScenarioBloc').show();
        $('.bt_ScenarioAction').hide();
    });

    // Action copy / cut
    $('#bt_copyAction').off('click').on('click', function (event) {
        blocCopy(ACTION_FOCUS);
        BLOC_CHANGE_COLOR=true;
        $('.bt_ScenarioBloc').hide();
        $('.bt_ScenarioAction').show();
    });
    $('#bt_cutAction').off('click').on('click', function (event) {
        blocCopy(ACTION_FOCUS,true);
        BLOC_CHANGE_COLOR=false;
        $('.bt_ScenarioBloc').hide();
        $('.bt_ScenarioAction').show();
    });

    // Bloc paste / replace
    $('#bt_pasteBloc').off('click').on('click', function (event) {
        blocPaste();
    });
    $('#bt_replaceBloc').off('click').on('click', function (event) {
        blocPaste(true);
    });

    // Action paste / replace
    $('#bt_pasteAction').off('click').on('click', function (event) {
        actionPaste();
    });
    $('#bt_replaceAction').off('click').on('click', function (event) {
        actionPaste(true);
    });

    // Bloc/Action move down
    $('#bt_moveBlocDown').off('click').on('click', function (event) {
        if (BLOC_FOCUS || ACTION_FOCUS) {
            if (BLOC_LAST_FOCUS) {
                if (BLOC_FOCUS.next().length !== 0) {
                    BLOC_FOCUS.insertAfter(BLOC_FOCUS.next())
                } else {
                    let BLOC_PARENT = BLOC_FOCUS.parent().parent();
                    if (BLOC_PARENT.hasClass("expression") && BLOC_PARENT.next().length !== 0) {
                        BLOC_PARENT.insertAfter(BLOC_PARENT.next())
                    }
                }
            } else {
                if (ACTION_FOCUS.next().length !== 0) {
                    ACTION_FOCUS.insertAfter(ACTION_FOCUS.next())
                }
            }
        }
    });

    // Bloc/Action move up
    $('#bt_moveBlocUp').off('click').on('click', function (event) {
        if (BLOC_FOCUS || ACTION_FOCUS) {
            if (BLOC_LAST_FOCUS) {
                if (BLOC_FOCUS.prev().length !== 0) {
                    BLOC_FOCUS.insertBefore(BLOC_FOCUS.prev())
                } else {
                    let BLOC_PARENT = BLOC_FOCUS.parent().parent();
                    if (BLOC_PARENT.hasClass("expression") && BLOC_PARENT.prevAll().length > 1) {
                        BLOC_PARENT.insertBefore(BLOC_PARENT.prev())
                    }
                }
            } else {
                if (ACTION_FOCUS.prevAll().length > 1) {
                    ACTION_FOCUS.insertBefore(ACTION_FOCUS.prev())
                }
            }
        }
    });

    // Groupe change
    $('#scenarioGroupListAttr').off('change').on('change', function (event) {
        if ($(this).value() == "NEW") {
            $('#scenarioGroupAttr').value("");
            $('#scenarioGroupAttr').parent().show();
        } else {
            $('#scenarioGroupAttr').parent().hide();
            $('#scenarioGroupAttr').value($(this).value());
        }
    });
}

/**
 * Event on scenario card click
 */
function loadScenario(scenarioId, tabToShow) {
    $('#scenarioThumbnailDisplay').hide();
    printScenario(scenarioId);
    urlUpdate(scenarioId);
    updateUrlTab();
}

/**
 * Navigator url update with id
 *
 * @param scenarioId scenario id
 */
function urlUpdate(scenarioId) {
    var currentUrl = document.location.toString();
    // Mise à jour d'URL
    if (currentUrl.indexOf('id=') === -1) {
        var hashIndex = currentUrl.indexOf('#');
        var updatedUrl = '';
        if (hashIndex === -1) {
            history.pushState({}, null, currentUrl + '&id=' + scenarioId);
        }
        else {
            updatedUrl = currentUrl.substr(0, hashIndex);
            updatedUrl += '&id=' + scenarioId;
            updatedUrl += currentUrl.substr(hashIndex);
        }
        history.pushState({}, null, updatedUrl);
    }
}

/**
 * Enable/Disable all scenarios
 */
function toggleAllScenariosState() {
    nextdom.config.save({
        configuration: {
            enableScenario: $("#bt_changeAllScenarioState").attr('data-state')
        },
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function () {
            loadPage('index.php?v=d&p=scenario');
        }
    });
}

/**
 * Add scenario (prompt for scenario name)
 */
function addScenario() {
    bootbox.prompt("Nom du scénario ?", function (result) {
        if (result !== null) {
            nextdom.scenario.save({
                scenario: {name: result},
                error: function (error) {
                    notify('Core',error.message,"error");
                    },
                success: function (data) {
                    modifyWithoutSave = false;
                    $('#scenarioThumbnailDisplay').hide();
                    $('#bt_scenarioThumbnailDisplay').hide();
                    printScenario(data.id);
                    urlUpdate(data.id);
                    updateUrlTab();
                }
            });
        }
    });
}

/**
 * Delete scenario
 */
function deleteScenario() {
    bootbox.confirm('{{Etes-vous sûr de vouloir supprimer le scénario}} <span style="font-weight: bold ;">' + $('.scenarioAttr[data-l1key=name]').value() + '</span> ?', function (result) {
        if (result) {
            nextdom.scenario.remove({
                id: $('.scenarioAttr[data-l1key=id]').value(),
                error: function (error) {
                    notify('Erreur', error.message, 'error');
                },
                success: function () {
                    modifyWithoutSave = false;
                    resetUndo();
                    loadPage('index.php?v=d&p=scenario');
                    notify('Info', '{{Suppression effectuée avec succès}}', 'success');
                }
            });
        }
    });
}

/**
 * Test the scenario
 */
function testScenario() {
    nextdom.scenario.changeState({
        id: $('.scenarioAttr[data-l1key=id]').value(),
        state: 'start',
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function () {
            notify('Info', '{{Lancement du scénario réussi}}', 'success');
        }
    });
}

/**
 * Copy current scenario in another (prompt for name of the new scenario)
 */
function copyScenario() {
    bootbox.prompt("Nom du scénario ?", function (result) {
        if (result !== null && result !== '') {
            nextdom.scenario.copy({
                id: $('.scenarioAttr[data-l1key=id]').value(),
                name: result,
                error: function (error) {
                    notify('Erreur', error.message, 'error');
                },
                success: function (data) {
                    $('#scenarioThumbnailDisplay').hide();
                    $('#bt_scenarioThumbnailDisplay').hide();
                    printScenario(data.id);
                }
            });
        }
    });
}

/**
 * Stop current scenario
 */
function stopScenario() {
    nextdom.scenario.changeState({
        id: $('.scenarioAttr[data-l1key=id]').value(),
        state: 'stop',
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function () {
            notify('Info', '{{Arrêt du scénario réussi}}', 'success');
        }
    });
}

/**
 * Initialise sortables items
 */
function updateSortable() {
    $('.element').removeClass('sortable');
    $('#div_scenarioElement > .element').addClass('sortable');
    $('.subElement .expressions').each(function () {
        if ($(this).children('.sortable:not(.empty)').length > 0) {
            $(this).children('.sortable.empty').hide();
        } else {
            $(this).children('.sortable.empty').show();
        }
    });
}

/**
 * Initialise event on else button toggle
 */
function updateElseToggle() {
    $('.subElementElse').each(function () {
        if ($(this).parent().css('display') === 'table') $(this).parent().prev().find('.bt_addSinon:first').children('i').removeClass('fa-chevron-right').addClass('fa-chevron-down');
    });
}

/**
 * Initialise code mirror on code element
 */
function setEditor() {
    $('.elementAttr[data-l1key=type][value=code]').each(function () {
        let editorTmp = $(this).closest('.element').find('.expressionAttr[data-l1key=expression]');
        if (editorTmp.attr('id') == undefined ) {
            editorTmp.uniqueId();
            let currentId = editorTmp.attr('id');
            setTimeout(function () {
                editor[currentId] = CodeMirror.fromTextArea(document.getElementById(currentId), {
                    lineNumbers: true,
                    mode: 'text/x-php',
                    matchBrackets: true,
                    viewportMargin: Infinity
                });
            }, 1);
        } else {
            editor[editorTmp.attr('id')].refresh();
        }
    });
    setTimeout(function () {
        // Unloack modification
        modifyWithoutSave = false;
        lockModify = false;
        $(".bt_cancelModifs").hide();
    }, 2000);
}

/**
 *
 * @param val
 * @returns {*}
 */
function splitAutocomplete(val) {
    return val.split(/\s/);
}

/**
 *
 * @param term
 * @returns {*}
 */
function extractLastAutocomplete(term) {
    return splitAutocomplete(term).pop();
}

/**
 * Initialise autocomplete fields
 */
function setAutocomplete() {
    $('.expression').each(function () {
        if ($(this).find('.expressionAttr[data-l1key=type]').value() === 'condition') {
            $(this).find('.expressionAttr[data-l1key=expression]').autocomplete({
                source: function (request, response) {
                    response($.ui.autocomplete.filter(
                        autoCompleteCondition, extractLastAutocomplete(request.term)));
                },
                classes: {
                    "ui-autocomplete": "autocomplete"
                },
                autoFocus: true,
                minLength: 1,
                focus: function () {
                    return false;
                },
                select: function (event, ui) {
                    let terms = this.value.trim();
                    if (terms == "") {
                        terms = ui.item.value.trim();
                    } else {
                        terms = terms.replace(extractLastAutocomplete(terms),"");
                        terms = terms + ui.item.value;
                    }
                    this.value = terms;
                    return false;
                }
            });
        }
        if ($(this).find('.expressionAttr[data-l1key=type]').value() === 'action') {
            $(this).find('.expressionAttr[data-l1key=expression]').autocomplete({
                source: autoCompleteAction,
                classes: {
                    "ui-autocomplete": "autocomplete"
                },
                autoFocus: true,
                minLength: 1,
                select: function (event, ui) {
                    this.value = ui.item.value.trim();
                },
                close: function (event, ui) {
                    $(this).trigger('focusout');
                }
            });
        }
    });
}

/**
 * Show the scenario
 * @param scenarioId
 */
function printScenario(scenarioId) {
    nextdom.scenario.update[scenarioId] = function (_options) {
        if (_options.scenario_id = !pageContainer.getValues('.scenarioAttr')[0]['id']) {
            return;
        }
        updateScenarioDisplay(scenarioId, _options);
    };
    nextdom.scenario.get({
        id: scenarioId,
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function (data) {
            colorIndex = 0;
            $('.scenarioAttr').value('');
            if (data.name) {
                document.title = data.name + ' - NextDom';
            }
            $('#scenarioName').html(data.name);
            $('.scenarioAttr[data-l1key=object_id] option:first').attr('selected', true);
            $('.scenarioAttr[data-l1key=object_id]').val('');
            pageContainer.setValues(data, '.scenarioAttr');
            $('#scenarioGroupListAttr').value($('.scenarioAttr[data-l1key=group]').val());
            data.lastLaunch = (data.lastLaunch == null) ? '{{Jamais}}' : data.lastLaunch;
            $('#span_lastLaunch').text(data.lastLaunch);
            scenarioContainer.empty();
            $('.provokeMode').empty();
            $('.scheduleMode').empty();
            $('.scenarioAttr[data-l1key=mode]').trigger('change');
            for (var i in data.schedules) {
                $('#div_schedules').schedule.display(data.schedules[i]);
            }
            updateScenarioDisplay(scenarioId, data);
            nextdom.scenario.update[scenarioId](data);
            if (data.isActive !== 1) {
                var inGoing = $('#in_going');
                inGoing.text('{{Inactif}}');
                inGoing.removeClass('label-danger');
                inGoing.removeClass('label-success');
            }
            if ($.isArray(data.trigger)) {
                for (var triggerIndex in data.trigger) {
                    if (data.trigger[triggerIndex] !== '' && data.trigger[triggerIndex] != null) {
                        addTrigger(data.trigger[triggerIndex]);
                    }
                }
            } else {
                if (data.trigger !== '' && data.trigger != null) {
                    addTrigger(data.trigger);
                }
            }
            if ($.isArray(data.schedule)) {
                for (var scheduleIndex in data.schedule) {
                    if (data.schedule[scheduleIndex] !== '' && data.schedule[scheduleIndex] != null) {
                        addSchedule(data.schedule[scheduleIndex]);
                    }
                }
            } else {
                if (data.schedule !== '' && data.schedule != null) {
                    addSchedule(data.schedule);
                }
            }
            if(!isset(data.timeout)){
                $('.scenarioAttr[data-l1key=timeout]').value(0);
            }
            $('.usedBy').empty();
            for (var i in data.scenarioLinkBy.scenario) {
                addUsedBy(data.scenarioLinkBy.scenario[i],'.usedBy');
            }
            $('.usedIn').empty();
            for (var i in data.scenarioLinkIn.scenario) {
                addUsedBy(data.scenarioLinkIn.scenario[i],'.usedIn');
            }
            if (data.elements.length === 0) {
                scenarioContainer.append('<div class="span_noScenarioElement"><p class="alert alert-info">{{Pour programmer votre scénario, veuillez commencer par ajouter des blocs...}}</p></div>')
            }
            actionOptions = [];
            for (var i in data.elements) {
                scenarioContainer.append(addElement(data.elements[i]));
            }
            nextdom.cmd.displayActionsOption({
                params: actionOptions,
                async: false,
                error: function (error) {
                    notify('Erreur', error.message, 'error');
                },
                success: function (data) {
                    for (var i in data) {
                        $('#' + data[i].id).append(data[i].html.html);
                    }
                    $('#div_editScenario').show();
                    initTextAreaAutosize();
                    setAutocomplete();
                    updateElseToggle();
                    setEditor();
                    updateSortable();
                    setInputExpressionsEvent();
                    resetUndo();
                    setUndoStack();
                    setMemoryStack();
                    modifyWithoutSave = false;
                    $(".bt_cancelModifs").hide();
                }
            });
        }
    });
}

/**
 * Update the scenario display state
 * @param _id Scenario ID
 * @param _data Scenario datas
 */
function updateScenarioDisplay(_id, _data) {
    var scenarioStartBtn = $('#bt_testScenario');
    var scenarioStopBtn = $('#bt_stopScenario');
    var scenarioState = $('#span_ongoing');
    scenarioStartBtn.hide();
    scenarioStopBtn.hide();
    scenarioState.removeClass('label-danger label-info label-success label-warning label-default')
    if (isset(_data.isActive) && _data.isActive != 1) {
        scenarioState.text('{{Inactif}}');
        scenarioState.addClass('label-action');
    } else {
        switch (_data.state) {
            case 'error' :
                scenarioStartBtn.show();
                scenarioState.text('{{Erreur}}');
                scenarioState.addClass('label-warning');
                break;
            case 'on' :
                scenarioStopBtn.show();
                scenarioState.text('{{Actif}}');
                scenarioState.addClass('label-success');
                break;
            case 'in progress' :
                scenarioStopBtn.show();
                scenarioState.text('{{En cours}}');
                scenarioState.addClass('label-info');
                break;
            case 'stop' :
            default :
                scenarioStartBtn.show();
                scenarioState.text('{{Arrêté}}');
                scenarioState.addClass('label-danger');
        }
    }
}

/**
 * Save the scenario in the database
 */
function saveScenario() {
    var scenario = pageContainer.getValues('.scenarioAttr')[0];
    scenario.type = "expert";
    var elements = [];
    scenarioContainer.children('.element').each(function () {
        elements.push(getElement($(this)));
    });
    scenario.elements = elements;
    nextdom.scenario.save({
        scenario: scenario,
        error: function (error) {
            notify('Erreur', error.message, 'error');
        },
        success: function (data) {
            modifyWithoutSave = false;
            resetUndo();
            $(".bt_cancelModifs").hide();
            notify('Info', '{{Sauvegarde effectuée avec succès}}', 'success');
        }
    });
    $('#bt_scenarioThumbnailDisplay').show();
    SC_CLIPBOARD = null;
}

/**
 * Add trigger start element
 *
 * @param triggerCode
 */
function addTrigger(triggerCode) {
    var triggerHtml = '<div class="form-group col-xs-12 col-padding trigger">';
    triggerHtml += '<label class="control-label">{{Evénement}}</label>';
    triggerHtml += '<div class="input-group">';
    triggerHtml += '<span class="input-group-btn">';
    triggerHtml += '<a class="btn btn-danger btn-sm cursor bt_removeTrigger"><i class="fas fa-minus-circle"></i></a>';
    triggerHtml += '</span>';
    triggerHtml += '<input class="scenarioAttr input-sm form-control" data-l1key="trigger" value="' + triggerCode.replace(/"/g, '&quot;') + '" >';
    triggerHtml += '<span class="input-group-btn">';
    triggerHtml += '<a class="btn btn-default btn-sm cursor bt_selectTrigger" title="{{Choisir une commande}}"><i class="fas fa-list-alt"></i></a>';
    triggerHtml += '<a class="btn btn-default btn-sm cursor bt_selectDataStoreTrigger" title="{{Choisir une variable}}"><i class="fas fa-calculator"></i></a>';
    triggerHtml += '</span>';
    triggerHtml += '</div>';
    triggerHtml += '</div>';
    $('.provokeMode').append(triggerHtml);
}

/**
 * Add schedule start element
 *
 * @param scheduleCode
 */
function addSchedule(scheduleCode) {
    var scheduleHtml = '<div class="form-group col-xs-12 col-padding schedule">';
    scheduleHtml += '<label class="control-label">{{Programmation}}</label>';
    scheduleHtml += '<div class="input-group">';
    scheduleHtml += '<span class="input-group-btn">';
    scheduleHtml += '<a class="btn btn-danger btn-sm cursor bt_removeSchedule"><i class="fas fa-minus-circle"></i></a>';
    scheduleHtml += '</span>';
    scheduleHtml += '<input class="scenarioAttr input-sm form-control" data-l1key="schedule" value="' + scheduleCode.replace(/"/g, '&quot;') + '">';
    scheduleHtml += '<span class="input-group-btn">';
    scheduleHtml += '<a class="btn btn-default btn-sm cursor helpSelectCron"><i class="fas fa-question-circle"></i></a>';
    scheduleHtml += '</span>';
    scheduleHtml += '</div>';
    scheduleHtml += '</div>';
    $('.scheduleMode').append(scheduleHtml);
}

/**
 * Add schedule start element
 *
 * @param scenario
 */
function addUsedBy(scenario,section) {
    var usedByHtml = '<div class="form-group col-xs-6 col-xs-12 col-padding">';
    usedByHtml += '<div class="mix-group">';
    usedByHtml += '<span class="label label-default label-sticker">' + scenario.name + '</span>';
    if (scenario.isActive == true) {
        usedByHtml += '<span class="label label-success label-sticker-big badge">{{Actif}}</span>';
    } else {
        usedByHtml += '<span class="label label-danger label-sticker-big badge">{{Inactif}}</span>';
    }
    if (scenario.isVisible == true) {
        usedByHtml += '<span class="label label-success label-sticker-big badge">{{Visible}}</span>';
    } else {
        usedByHtml += '<span class="label label-danger label-sticker-big badge">{{Non Visible}}</span>';
    }
    usedByHtml += '<a class="btn btn-primary openUsedBy" data-scenario_id="' + scenario.id + '"><i class="fas fa-link"></i>{{Ouvrir}}</a>';
    usedByHtml += '</div>';
    usedByHtml += '</div>';
    $(section).append(usedByHtml);
}

/**
 * Get HTML data of a Condition expression
 * @param expressionData
 * @returns {string}
 */
function getConditionExpressionHTML(expressionData) {
    var htmlData = '';
    if (isset(expressionData.expression)) {
        expressionData.expression = expressionData.expression.replace(/"/g, '&quot;');
    }
    htmlData += '<div class="input-group input-group-sm no-border">';
    htmlData += '<textarea class="expressionAttr form-control scenario-text" data-l1key="expression" rows="1">' + init(expressionData.expression) + '</textarea>';
    htmlData += '<span class="input-group-btn">';
    htmlData += '<button type="button" class="btn btn-default cursor bt_selectCmdExpression tooltips" title="{{Rechercher une commande}}"><i class="fas fa-list-alt"></i></button>';
    htmlData += '<button type="button" class="btn btn-default cursor bt_selectScenarioExpression tooltips" title="{{Rechercher un scenario}}"><i class="fas fa-history"></i></button>';
    htmlData += '<button type="button" class="btn btn-default cursor bt_selectEqLogicExpression tooltips" title="{{Rechercher d\'un équipement}}"><i class="fas fa-cube"></i></button>';
    htmlData += '</span>';
    htmlData += '</div>';
    return htmlData;
}

/**
 * Get HTML data of an Element expression
 * @param expressionData
 * @returns {string}
 */
function getElementExpressionHTML(expressionData) {
    var htmlData = '';
    htmlData += '<div class="col-xs-12" style="padding-right: 0px; padding-left: 0px;">';
    if (isset(expressionData.element) && isset(expressionData.element.html)) {
        htmlData += expressionData.element.html;
    } else {
        var element = addElement(expressionData.element);
        if ($.trim(element) === '') {
            return '';
        }
        htmlData += element;
    }
    htmlData += '</div>';
    return htmlData;
}

/**
 * Get HTML data of an Action expression
 * @param expressionData
 * @returns {string}
 */
function getActionExpressionHTML(expressionData) {
    var htmlData = '';
    htmlData += '<div class="col-xs-1 scenario-action">';
    htmlData += '<i class="fas fa-sort bt_sortable"></i>';
    if (!isset(expressionData.options) || !isset(expressionData.options.enable) || parseInt(expressionData.options.enable) === 1) {
        htmlData += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour désactiver l\'action}}"/>';
    } else {
        htmlData += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="enable" title="{{Décocher pour désactiver l\'action}}"/>';
    }
    if (!isset(expressionData.options) || !isset(expressionData.options.background) || parseInt(expressionData.options.background) === 0) {
        htmlData += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="background" title="{{Cocher pour que la commande s\'exécute en parallèle des autres actions}}"/>';
    } else {
        htmlData += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="background" checked title="{{Cocher pour que la commande s\'exécute en parallèle des autres actions}}"/>';
    }
    var expressionTxt = init(expressionData.expression);
    if(typeof expressionTxt !== 'string'){
        expressionTxt = json_encode(expressionTxt);
    }
    htmlData += '</div>';
    htmlData += '<div class="col-xs-11 scenario-sub-group"><div class="input-group input-group-sm no-border">';
    htmlData += '<span class="input-group-btn">';
    htmlData += '<button class="btn btn-default bt_removeExpression" type="button" title="{{Supprimer l\'action}}"><i class="fas fa-minus-circle"></i></button>';
    htmlData += '</span>';
    htmlData += '<input class="expressionAttr form-control" data-l1key="expression" value="' + expressionTxt.replace(/"/g, '&quot;') + '" style="font-weight:bold;"/>';
    htmlData += '<span class="input-group-btn">';
    htmlData += '<button class="btn btn-default bt_selectOtherActionExpression" type="button" title="{{Sélectionner un mot-clé}}"><i class="fas fa-tasks"></i></button>';
    htmlData += '<button class="btn btn-default bt_selectCmdExpression" type="button" title="{{Sélectionner la commande}}"><i class="fas fa-list-alt"></i></button>';
    htmlData += '</span>';
    htmlData += '</div></div>';
    var actionOption_id = uniqId();
    htmlData += '<div class="col-xs-11 col-xs-offset-1 expressionOptions scenario-sub-group" id="' + actionOption_id + '">';
    htmlData += '</div>';
    actionOptions.push({
        expression: init(expressionData.expression, ''),
        options: expressionData.options,
        id: actionOption_id
    });
    return htmlData;
}

/**
 * Get HTML data of a Code expression
 * @param expressionData
 * @returns {string}
 */
function getCodeExpressionHTML(expressionData) {
    var htmlData = '';
    htmlData += '<div>';
    htmlData += '<textarea class="expressionAttr scenario-code-text form-control" data-l1key="expression">' + init(expressionData.expression) + '</textarea>';
    htmlData += '</div>';
    return htmlData;
}

/**
 * Get HTML data of an Comment expression
 * @param expressionData
 * @returns {string}
 */
function getCommentExpressionHTML(expressionData) {
    var htmlData = '';
    htmlData += '<textarea class="expressionAttr scenario-comment-text form-control" data-l1key="expression">' + init(expressionData.expression) + '</textarea>';
    return htmlData;
}

/**
 * Add an expression in scenario (sub)element
 * @param expressionToAdd
 * @returns {string}
 */
function addExpression(expressionToAdd) {
    if (!isset(expressionToAdd) || !isset(expressionToAdd.type) || expressionToAdd.type === '') {
        return '';
    }
    var sortable = 'sortable';
    if (expressionToAdd.type === 'condition') {
        sortable = 'noSortable';
    }
    var htmlData = '<div class="expression scenario-group ' + sortable + ' col-xs-12">';
    htmlData += '<input class="expressionAttr" data-l1key="id" type="hidden" value="' + init(expressionToAdd.id) + '"/>';
    htmlData += '<input class="expressionAttr" data-l1key="scenarioSubElement_id" type="hidden" value="' + init(expressionToAdd.scenarioSubElement_id) + '"/>';
    htmlData += '<input class="expressionAttr" data-l1key="type" type="hidden" value="' + init(expressionToAdd.type) + '"/>';
    switch (expressionToAdd.type) {
        case 'condition':
            htmlData += getConditionExpressionHTML(expressionToAdd);
            break;
        case 'element' :
            htmlData += getElementExpressionHTML(expressionToAdd);
            break;
        case 'action' :
            htmlData += getActionExpressionHTML(expressionToAdd);
            break;
        case 'code' :
            htmlData += getCodeExpressionHTML(expressionToAdd);
            break;
        case 'comment' :
            htmlData += getCommentExpressionHTML(expressionToAdd);
            break;
    }
    htmlData += '</div>';
    if (!lockModify) {
        modifyWithoutSave = true;
        $(".bt_cancelModifs").show();
    }
    return htmlData;
}

/**
 * Get the first expression HTML code
 * @param subElementData
 * @param expressionType
 * @returns {string}
 */
function addFirstExpressionHTML(subElementData, expressionType) {
    var expression = {type: expressionType};
    if (isset(subElementData.expressions) && isset(subElementData.expressions[0])) {
        expression = subElementData.expressions[0];
    }
    return addExpression(expression);
}

/**
 * Get all expression HTML code
 * @param subElementData
 * @returns {string}
 */
function addAllExpressionsHTML(subElementData) {
    var expressionsData = '';
    if (isset(subElementData.expressions)) {
        for (var expressionIndex in subElementData.expressions) {
            expressionsData += addExpression(subElementData.expressions[expressionIndex]);
        }
    }
    return expressionsData;
}

/**
 * Get HTML data of the If block
 *
 * @param subElementData
 * @returns {string}
 */
function  getIfSubElementHTML(subElementData) {
    var htmlData = '';
    htmlData += '<input class="subElementAttr" data-l1key="subtype" type="hidden" value="condition"/>';
    htmlData += '<div class="scenario-si">';
    htmlData += '<i class="fas fa-sort bt_sortable"></i>';
    var checked = '';
    if (!isset(subElementData.options) || !isset(subElementData.options.enable) || parseInt(subElementData.options.enable) === 1) {
        checked = ' checked="checked"';
    }
    htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" title="Décocher pour désactiver l\'élément" ' + checked + '/>';
    htmlData += '<span class="scenario-title">{{SI}}</span>';
    if (!isset(subElementData.options) || !isset(subElementData.options.allowRepeatCondition) || parseInt(subElementData.options.allowRepeatCondition) === 0) {
        htmlData += '<a class="btn btn-default btn-sm cursor subElementAttr tooltips scenario-btn-repeat" title="{{Autoriser ou non la répétition des actions si l\'évaluation de la condition est la même que la précédente}}" data-l1key="options" data-l2key="allowRepeatCondition" value="0"><i class="fas fa-sync-alt"></i></a>';
    } else {
        htmlData += '<a class="btn btn-default btn-sm cursor subElementAttr tooltips scenario-btn-repeat" title="{{Autoriser ou non la répétition des actions si l\'évaluation de la condition est la même que la précédente}}" data-l1key="options" data-l2key="allowRepeatCondition" value="1"><i class="fas fa-ban text-danger"></i></a>';
    }
    htmlData += '</div>';
    htmlData += '<div class="expressions scenario-condition">';
    htmlData += addFirstExpressionHTML(subElementData, 'condition');
    htmlData += '</div>';
    htmlData += '<div class="scenario-delete"><i class="fas fa-minus-circle pull-right cursor bt_removeElement"></i></div>';
    return htmlData;
}

/**
 * Get HTML data of an then block
 *
 * @param subElementData
 * @param elementColorIndex
 * @returns {string}
 */
function getThenSubElementHTML(subElementData, elementColorIndex) {
    var htmlData = '';
    htmlData += '<input class="subElementAttr" data-l1key="subtype" type="hidden" value="action"/>';
    htmlData += '  <div class="scenario-alors">';
    htmlData += '     <button class="btn btn-xs btn-default bt_addSinon scenario-expand" type="button" id="addSinon" data-toggle="dropdown" title="{{Afficher/masquer le bloc Sinon}}" aria-haspopup="true" aria-expanded="true">';
    htmlData += '       <i class="fas fa-chevron-right"></i>';
    htmlData += '     </button>';
    htmlData += '     <span class="scenario-title">{{ALORS}}</span>';
    htmlData += '     <div class="dropdown cursor">';
    htmlData += '       <button class="btn btn-sm btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">';
    htmlData += '         <i class="fas fa-plus-circle spacing-right"></i>{{Ajouter...}}';
    htmlData += '       </button>';
    htmlData += getAddButton();
    htmlData += '     </div>';
    htmlData += '   </div>';
    htmlData += '  <div class="expressions scenario-si-bloc" style="background-color: ' + listColor[elementColorIndex] + ';">';
    htmlData += '     <div class="sortable empty"></div>';
    htmlData += addAllExpressionsHTML(subElementData);
    htmlData += '</div>';
    return htmlData;
}

/**
 * Get HTML data of an Else block
 *
 * @param subElementData
 * @param elementColorIndex
 * @returns {string}
 */
function getElseSubElementHTML(subElementData, elementColorIndex) {
    var htmlData = '';
    htmlData += '<input class="subElementAttr subElementElse" data-l1key="subtype" type="hidden" value="action"/>';
    htmlData += '<div class="scenario-sinon">';
    htmlData += '<span class="scenario-title">{{SINON}}</span>';
    htmlData += '<div class="dropdown cursor">';
    htmlData += '<button class="btn btn-sm btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">';
    htmlData += '<i class="fas fa-plus-circle spacing-right"></i>{{Ajouter...}}';
    htmlData += '</button>';
    htmlData += getAddButton();
    htmlData += '</div>';
    htmlData += '</div>';
    htmlData += '<div class="expressions scenario-si-bloc" style="background-color: ' + listColor[elementColorIndex] + '; border-top :1px solid ' + listColorStrong[elementColorIndex] + '">';
    htmlData += '<div class="sortable empty"></div>';
    htmlData += addAllExpressionsHTML(subElementData);
    htmlData += '</div>';
    return htmlData;
}

/**
 * Get HTML data of a For block
 *
 * @param subElementData
 * @param elementColorIndex
 * @returns {string}
 */
function getForSubElementHTML(subElementData, elementColorIndex) {
    var htmlData = '';
    htmlData += '<input class="subElementAttr" data-l1key="subtype" type="hidden" value="condition"/>';
    htmlData += '<div class="scenario-for">';
    htmlData += '<i class="fas fa-sort bt_sortable"></i>';
    if (!isset(subElementData.options) || !isset(subElementData.options.enable) || parseInt(subElementData.options.enable) === 1) {
        htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour désactiver l\'élément}}"/>';
    } else {
        htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" title="{{Décocher pour désactiver l\'élément}}"/>';
    }
    htmlData += '<span class="scenario-title">{{DE 1 A}}</span>';
    htmlData += '</div>';
    htmlData += '<div class="expressions scenario-condition">';
    htmlData += addFirstExpressionHTML(subElementData, 'condition');
    htmlData += '</div>';
    htmlData += '<div class="scenario-delete"><i class="fas fa-minus-circle pull-right cursor bt_removeElement"></i></div>';
    return htmlData;
}

/**
 * Get HTML data of an In block
 *
 * @param subElementData
 * @param elementColorIndex
 * @returns {string}
 */
function getInSubElementHTML(subElementData, elementColorIndex) {
    var htmlData = '';
    htmlData += '<input class="subElementAttr" data-l1key="subtype" type="hidden" value="condition"/>';
    htmlData += '<div class="scenario-in">';
    htmlData += '<i class="fas fa-sort bt_sortable"></i>';
    if (!isset(subElementData.options) || !isset(subElementData.options.enable) || parseInt(subElementData.options.enable) === 1) {
        htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour désactiver l\'élément}}" style="margin-right : 0px;"/>';
    } else {
        htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" title="{{Décocher pour désactiver l\'élément}}" style="margin-right : 0px;"/>';
    }
    htmlData += '<span class="scenario-title">{{DANS}}</span>';
    htmlData += '<span class="scenario-unity">(en min)</span>';
    htmlData += '</div>';
    htmlData += '<div class="expressions scenario-condition">';
    htmlData += addFirstExpressionHTML(subElementData, 'condition');
    htmlData += '</div>';
    htmlData += '<div class="scenario-delete"><i class="fas fa-minus-circle pull-right cursor bt_removeElement"></i></div>';
    return htmlData;
}

/**
 * Get HTML data of an At block
 *
 * @param subElementData
 * @param elementColorIndex
 * @returns {string}
 */
function getAtSubElementHTML(subElementData, elementColorIndex) {
    var htmlData = '';
    htmlData += '<input class="subElementAttr" data-l1key="subtype" type="hidden" value="condition"/>';
    htmlData += '<div class="scenario-at">';
    htmlData += '<i class="fas fa-sort bt_sortable"></i>';
    if (!isset(subElementData.options) || !isset(subElementData.options.enable) || parseInt(subElementData.options.enable) === 1) {
        htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour désactiver l\'élément}}"/>';
    } else {
        htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" title="{{Décocher pour désactiver l\'élément}}"/>';
    }
    htmlData += '<span class="scenario-title">{{A}}</span>';
    htmlData += '<span class="scenario-unity-line">{{(Hmm)}}</span>';
    htmlData += '</div>';
    htmlData += '<div class="expressions scenario-condition">';
    htmlData += addFirstExpressionHTML(subElementData, 'condition');
    htmlData += '</div>';
    htmlData += '<div class="scenario-delete"><i class="fas fa-minus-circle pull-right cursor bt_removeElement"></i></div>';
    return htmlData;
}

/**
 * Get HTML data of a Do block
 *
 * @param subElementData
 * @param elementColorIndex
 * @returns {string}
 */
function getDoSubElementHTML(subElementData, elementColorIndex) {
    var htmlData = '';
    htmlData += '<input class="subElementAttr" data-l1key="subtype" type="hidden" value="action"/>';
    htmlData += '<div class="scenario-faire">';
    htmlData += '<span class="scenario-title">{{FAIRE}}</span>';
    htmlData += '<div class="dropdown cursor">';
    htmlData += '<button class="btn btn-sm btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">';
    htmlData += '<i class="fas fa-plus-circle spacing-right"></i>{{Ajouter...}}';
    htmlData += '</button>';
    htmlData += getAddButton();
    htmlData += '</div>';
    htmlData += '</div>';
    htmlData += '<div class="expressions scenario-condition" style="background-color: ' + listColor[elementColorIndex] + ';">';
    htmlData += '<div class="sortable empty"></div>';
    htmlData += addAllExpressionsHTML(subElementData);
    htmlData += '</div>';
    return htmlData;
}

/**
 * Get HTML data of a Code block
 *
 * @param subElementData
 * @param elementColorIndex
 * @returns {string}
 */
function getCodeSubElementHTML(subElementData, elementColorIndex) {
    var htmlData = '';
    htmlData += '<input class="subElementAttr" data-l1key="subtype" type="hidden" value="action"/>';
    htmlData += '<div class="scenario-code">';
    htmlData += '<i class="fas fa-sort bt_sortable"></i>';
    if (!isset(subElementData.options) || !isset(subElementData.options.enable) || parseInt(subElementData.options.enable) === 1) {
        htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour désactiver l\'élément}}"/>';
    } else {
        htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" title="{{Décocher pour désactiver l\'élément}}"/>';
    }
    htmlData += '<span class="scenario-title">{{CODE}}</span>';
    htmlData += '</div>';
    htmlData += '<div class="expressions" style="background-color: ' + listColor[elementColorIndex] + ';">';
    htmlData += addFirstExpressionHTML(subElementData, 'code');
    htmlData += '</div>';
    htmlData += '<div class="scenario-delete"><i class="fas fa-minus-circle pull-right cursor bt_removeElement"></i></div>';
    return htmlData;
}

/**
 * Get HTML data of a Comment block
 *
 * @param subElementData
 * @param elementColorIndex
 * @returns {string}
 */
function getCommentSubElementHTML(subElementData, elementColorIndex) {
    var htmlData = '';
    htmlData += '<input class="subElementAttr" data-l1key="subtype" type="hidden" value="comment"/>';
    htmlData += '<div class="scenario-comment">';
    htmlData += '<i class="fas fa-sort bt_sortable"></i>';
    htmlData += '</div>';
    htmlData += '<div class="expressions scenario-condition" style="background-color: ' + listColor[elementColorIndex] + ';">';
    htmlData += addFirstExpressionHTML(subElementData, 'comment');
    htmlData += '</div>';
    htmlData += '<div class="scenario-delete"><i class="fas fa-minus-circle pull-right cursor bt_removeElement"></i></div>';
    return htmlData;
}

/**
 * Get HTML data of an Action block
 *
 * @param subElementData
 * @param elementColorIndex
 * @returns {string}
 */
function getActionSubElementHTML(subElementData, elementColorIndex) {
    var htmlData = '';
    htmlData += '<input class="subElementAttr" data-l1key="subtype" type="hidden" value="action"/>';
    htmlData += '<div class="scenario-action-bloc">';
    htmlData += '<i class="fas fa-sort bt_sortable"></i>';
    if (!isset(subElementData.options) || !isset(subElementData.options.enable) || parseInt(subElementData.options.enable) === 1) {
        htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour désactiver l\'élément}}" style="margin-right : 0px;"/>';
    } else {
        htmlData += '<input type="checkbox" class="subElementAttr" data-l1key="options" data-l2key="enable" title="{{Décocher pour désactiver l\'élément}}" style="margin-right : 0px;"/>';
    }
    htmlData += '<span class="scenario-title">{{ACTION}}</span>';
    htmlData += '<div class="dropdown cursor">';
    htmlData += '<button class="btn btn-sm btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">';
    htmlData += '<i class="fas fa-plus-circle spacing-right"></i>{{Ajouter...}}';
    htmlData += '</button>';
    htmlData += getAddButton();
    htmlData += '</div>';
    htmlData += '</div>';
    htmlData += '<div class="expressions scenario-si-bloc" style="display:table-cell; background-color: ' + listColor[elementColorIndex] + ';">';
    htmlData += '<div class="sortable empty"></div>';
    htmlData += addAllExpressionsHTML(subElementData);
    htmlData += '</div>';
    htmlData += '<div class="scenario-delete"><i class="fas fa-minus-circle pull-right cursor bt_removeElement"></i></div>';
    return htmlData;
}

/**
 * Add an subelement to the scenario
 *
 * @param subElementToAdd
 * @param elementColorIndex
 * @returns {string}
 */
function addSubElement(subElementToAdd, elementColorIndex) {
    if (!isset(subElementToAdd.type) || subElementToAdd.type === '') {
        return '';
    }
    if (!isset(subElementToAdd.options)) {
        subElementToAdd.options = {};
    }
    var noSortable = '';
    if (subElementToAdd.type === 'if' || subElementToAdd.type === 'for' || subElementToAdd.type === 'code') {
        noSortable = 'noSortable';
    }
    var displayElse = 'table';
    if (subElementToAdd.type === 'else') {
        if (!isset(subElementToAdd.expressions) || subElementToAdd.expressions.length === 0) {
            displayElse = 'none';
        }
    }
    var subElementHTML = '<div class="subElement scenario-group ' + noSortable + '" style="display:' + displayElse + '">';
    subElementHTML += '<input class="subElementAttr" data-l1key="id" type="hidden" value="' + init(subElementToAdd.id) + '"/>';
    subElementHTML += '<input class="subElementAttr" data-l1key="scenarioElement_id" type="hidden" value="' + init(subElementToAdd.scenarioElement_id) + '"/>';
    subElementHTML += '<input class="subElementAttr" data-l1key="type" type="hidden" value="' + init(subElementToAdd.type) + '"/>';
    switch (subElementToAdd.type) {
        case 'if' :
            subElementHTML += getIfSubElementHTML(subElementToAdd);
            break;
        case 'then' :
            subElementHTML += getThenSubElementHTML(subElementToAdd, elementColorIndex);
            break;
        case 'else' :
            subElementHTML += getElseSubElementHTML(subElementToAdd, elementColorIndex);
            break;
        case 'for' :
            subElementHTML += getForSubElementHTML(subElementToAdd, elementColorIndex);
            break;
        case 'in' :
            subElementHTML += getInSubElementHTML(subElementToAdd, elementColorIndex);
            break;
        case 'at' :
            subElementHTML += getAtSubElementHTML(subElementToAdd, elementColorIndex);
            break;
        case 'do' :
            subElementHTML += getDoSubElementHTML(subElementToAdd, elementColorIndex);
            break;
        case 'code' :
            subElementHTML += getCodeSubElementHTML(subElementToAdd, elementColorIndex);
            break;
        case 'comment' :
            subElementHTML += getCommentSubElementHTML(subElementToAdd, elementColorIndex);
            break;
        case 'action' :
            subElementHTML += getActionSubElementHTML(subElementToAdd, elementColorIndex);
            break;
    }
    subElementHTML += '</div>';
    if (!lockModify) {
        modifyWithoutSave = true;
        $(".bt_cancelModifs").show();
    }
    return subElementHTML;
}

/**
 * Add link on add dropdown menu
 *
 * @returns {string}
 */
function getAddButton() {
    var htmlData = '';
    htmlData += '<ul class="dropdown-menu">';
    htmlData += '<li><a class="bt_addAction">{{Action}}</a></li>';
    htmlData += '<li><a class="fromSubElement" data-type="if">{{Bloc Si/Alors/Sinon}}</a></li>';
    htmlData += '<li><a class="fromSubElement" data-type="action">{{Bloc Action}}</a></li>';
    htmlData += '<li><a class="fromSubElement" data-type="for">{{Bloc Boucle}}</a></li>';
    htmlData += '<li><a class="fromSubElement" data-type="in">{{Bloc Dans}}</a></li>';
    htmlData += '<li><a class="fromSubElement" data-type="at">{{Bloc A}}</a></li>';
    htmlData += '<li><a class="fromSubElement" data-type="code">{{Bloc Code}}</a></li>';
    htmlData += '<li><a class="fromSubElement" data-type="comment">{{Bloc Commentaire}}</a></li>';
    htmlData += '</ul>';
    return htmlData;
}

/**
 * Get next color in the list
 *
 * @returns {number} Next color index in the list
 */
function getNextColorIndex() {
    colorIndex++;
    if (colorIndex > 4) {
        colorIndex = 0;
    }
    return colorIndex;
}

/**
 * Add an element to the scenario
 *
 * @param elementToAdd
 * @returns {string}
 */
function addElement(elementToAdd) {
    if (!isset(elementToAdd)) {
        return '';
    }
    if (!isset(elementToAdd.type) || elementToAdd.type === '') {
        return '';
    }

    var elementColorIndex = getNextColorIndex();
    var lightColor = listColor[elementColorIndex];
    var strongColor = listColorStrong[elementColorIndex];

    var elementHTML = '<div class="element" style="background-color:' + strongColor + ';border-color:' + lightColor + '">';
    elementHTML += '<input class="elementAttr" data-l1key="id" type="hidden" value="' + init(elementToAdd.id) + '"/>';
    elementHTML += '<input class="elementAttr" data-l1key="type" type="hidden" value="' + init(elementToAdd.type) + '"/>';
    if (isset(elementToAdd.subElements)) {
        for (var subElementIndex in elementToAdd.subElements) {
            elementHTML += addSubElement(elementToAdd.subElements[subElementIndex], elementColorIndex);
        }
    }
    else {
        switch (elementToAdd.type) {
            case 'if':
                elementHTML += addSubElement({type: 'if'}, elementColorIndex);
                elementHTML += addSubElement({type: 'then'}, elementColorIndex);
                elementHTML += addSubElement({type: 'else'}, elementColorIndex);
                break;
            case 'for':
                elementHTML += addSubElement({type: 'for'}, elementColorIndex);
                elementHTML += addSubElement({type: 'do'}, elementColorIndex);
                break;
            case 'in' :
                elementHTML += addSubElement({type: 'in'}, elementColorIndex);
                elementHTML += addSubElement({type: 'do'}, elementColorIndex);
                break;
            case 'at' :
                elementHTML += addSubElement({type: 'at'}, elementColorIndex);
                elementHTML += addSubElement({type: 'do'}, elementColorIndex);
                break;
            case 'code' :
                elementHTML += addSubElement({type: 'code'}, elementColorIndex);
                break;
            case 'comment' :
                elementHTML += addSubElement({type: 'comment'}, elementColorIndex);
                break;
            case 'action' :
                elementHTML += addSubElement({type: 'action'}, elementColorIndex);
                break;
        }
    }
    elementHTML += '</div>';
    if (!lockModify) {
        modifyWithoutSave = true;
        $(".bt_cancelModifs").show();
    }
    return elementHTML;
}

/**
 * Get element data (and subelements)
 * @param rootElement
 * @returns {*}
 */
function getElement(rootElement) {
    var element = rootElement.getValues('.elementAttr', 1);
    if (element.length === 0) {
        return;
    }
    element = element[0];
    element.subElements = [];

    rootElement.findAtDepth('.subElement', 2).each(function () {
        var subElement = $(this).getValues('.subElementAttr', 2);
        subElement = subElement[0];
        subElement.expressions = [];
        var expression_dom = $(this).children('.expressions');
        if (expression_dom.length === 0) {
            expression_dom = $(this).children('legend').findAtDepth('.expressions', 2);
        }
        expression_dom.children('.expression').each(function () {
            var expression = $(this).getValues('.expressionAttr', 3);
            expression = expression[0];
            if (expression.type === 'element') {
                expression.element = getElement($(this).findAtDepth('.element', 2));
            }
            if (subElement.type === 'code') {
                var id = $(this).find('.expressionAttr[data-l1key=expression]').attr('id');
                if (id !== undefined && isset(editor[id])) {
                    expression.expression = editor[id].getValue();
                }
            }
            subElement.expressions.push(expression);

        });
        element.subElements.push(subElement);
    });
    return element;
}

/**
 * Set the event of the expression input
 */
function setInputExpressionsEvent() {
    var inputExpressions = $('.expressionAttr[data-l1key=expression]');
    inputExpressions.off('keyup').on('keyup', function () {
        checkExpressionInput($(this));
    });
    inputExpressions.each(function () {
        checkExpressionInput($(this));
    });
}

/**
 * Check an input that contains expression and decorate on error
 *
 * @param inputElement JQuery object of the input to check
 */
function checkExpressionInput(inputElement) {
    if (!checkExpressionValidity(inputElement.val())) {
        inputElement.addClass('expression-error');
    }
    else {
        if (inputElement.hasClass('expression-error')) {
            inputElement.removeClass('expression-error');
        }
    }
}

/**
 * Check if the string is a valid NextDom expression
 *
 * @param stringToCheck String to check
 *
 * @returns {boolean} True if the string is valid
 */
function checkExpressionValidity(stringToCheck) {
    var validityCheckRegex = /((\w+|-?(\d+\.\d+|\.?\d+)|".*?"|'.*?'|#.*?#|\(|,|\)|!)[ ]*([!*+&|\-\/>=<]+|and|or|ou|et)*[ ]*)*/;
    var prohibedFirstsCharacters = ['*', '+', '&', '|', '-', '/', '>', '=', '<'];
    var prohibedLastsCharacters = ['!', '*', '+', '&', '|', '-', '/', '>', '=', '<'];
    var result = false;

    stringToCheck = stringToCheck.trim();
    if (validityCheckRegex.exec(stringToCheck)[0] === stringToCheck) {
        result = true;
        if (stringToCheck.length > 0) {
            if (prohibedFirstsCharacters.indexOf(stringToCheck[0]) !== -1) {
                result = false;
            }
            if (prohibedLastsCharacters.indexOf(stringToCheck[stringToCheck.length - 1]) !== -1) {
                result = false;
            }
        }
        var parenthesisStack = [];
        for (var i = 0; i < stringToCheck.length; ++i) {
            if (stringToCheck[i] === '(') {
                parenthesisStack.push('(');
            }
            else if (stringToCheck[i] === ')') {
                if (parenthesisStack.length === 0) {
                    result = false;
                    break;
                }
                if (parenthesisStack[parenthesisStack.length - 1] !== '(') {
                    result = false;
                    break;
                }
                parenthesisStack.pop();
            }
        }
        if (parenthesisStack.length > 0) {
            result = false;
        }
    }

    return result;
}

/**
 * Get HTML for numeric expression choice
 * @returns {string}
 */
function getNumericExpressionHTML(humanResult) {
    return '<div class="row"> ' +
        '<div class="col-md-12"> ' +
        '<form class="form-horizontal" onsubmit="return false;"> ' +
        '<div class="form-group"> ' +
        '<label class="col-xs-5 control-label" >' + humanResult + ' {{est}}</label>' +
        '<div class="col-xs-3">' +
        '<select class="conditionAttr form-control" data-l1key="operator">' +
        '<option value="==">{{égal}}</option>' +
        '<option value=">">{{supérieur}}</option>' +
        '<option value="<">{{inférieur}}</option>' +
        '<option value="!=">{{différent}}</option>' +
        '</select>' +
        '</div>' +
        '<div class="col-xs-4">' +
        '<input type="number" class="conditionAttr form-control" data-l1key="operande" />' +
        '</div>' +
        '</div>' +
        '<div class="form-group"> ' +
        '<label class="col-xs-5 control-label" >{{Ensuite}}</label>' +
        '<div class="col-xs-3">' +
        '<select class="conditionAttr form-control" data-l1key="next">' +
        '<option value="">rien</option>' +
        '<option value="ET">{{et}}</option>' +
        '<option value="OU">{{ou}}</option>' +
        '</select>' +
        '</div>' +
        '</div>' +
        '</div> </div>' +
        '</form> </div> </div>';
}

/**
 * Get HTML for string expression choice
 * @returns {string}
 */
function getStringExpressionHTML(humanResult) {
    return '<div class="row"> ' +
        '<div class="col-md-12"> ' +
        '<form class="form-horizontal" onsubmit="return false;"> ' +
        '<div class="form-group"> ' +
        '<label class="col-xs-5 control-label" >' + humanResult + ' {{est}}</label>' +
        '<div class="col-xs-3">' +
        '<select class="conditionAttr form-control" data-l1key="operator">' +
        '<option value="==">{{égale}}</option>' +
        '<option value="matches">{{contient}}</option>' +
        '<option value="!=">{{différent}}</option>' +
        '</select>' +
        '</div>' +
        '<div class="col-xs-4">' +
        '<input class="conditionAttr form-control" data-l1key="operande" />' +
        '</div>' +
        '</div>' +
        '<div class="form-group"> ' +
        '<label class="col-xs-5 control-label" >{{Ensuite}}</label>' +
        '<div class="col-xs-3">' +
        '<select class="conditionAttr form-control" data-l1key="next">' +
        '<option value="">{{rien}}</option>' +
        '<option value="ET">{{et}}</option>' +
        '<option value="OU">{{ou}}</option>' +
        '</select>' +
        '</div>' +
        '</div>' +
        '</div> </div>' +
        '</form> </div> </div>';
}

/**
 * Get HTML for binary expression choice
 * @returns {string}
 */
function getBinaryExpressionHTML(humanResult) {
    return '<div class="row"> ' +
        '<div class="col-md-12"> ' +
        '<form class="form-horizontal" onsubmit="return false;"> ' +
        '<div class="form-group"> ' +
        '<label class="col-xs-5 control-label" >' + humanResult + ' {{est}}</label>' +
        '<div class="col-xs-7">' +
        '<input class="conditionAttr" data-l1key="operator" value="==" type="hidden" />' +
        '<select class="conditionAttr form-control" data-l1key="operande">' +
        '<option value="1">{{Ouvert}}</option>' +
        '<option value="0">{{Fermé}}</option>' +
        '<option value="1">{{Allumé}}</option>' +
        '<option value="0">{{Eteint}}</option>' +
        '<option value="1">{{Déclenché}}</option>' +
        '<option value="0">{{Au repos}}</option>' +
        '</select>' +
        '</div>' +
        '</div>' +
        '<div class="form-group"> ' +
        '<label class="col-xs-5 control-label" >{{Ensuite}}</label>' +
        '<div class="col-xs-3">' +
        '<select class="conditionAttr form-control" data-l1key="next">' +
        '<option value="">{{rien}}</option>' +
        '<option value="ET">{{et}}</option>' +
        '<option value="OU">{{ou}}</option>' +
        '</select>' +
        '</div>' +
        '</div>' +
        '</div></div>' +
        '</form></div></div>';
}

/**
 * Show modal for command selection
 * @param elementData
 * @param expressionElement
 */
function selectCmdExpression(elementData, expressionElement) {
    var type = 'info';
    if (expressionElement.find('.expressionAttr[data-l1key=type]').value() === 'action') {
        type = 'action';
    }
    nextdom.cmd.getSelectModal({cmd: {type: type}}, function (result) {
        if (expressionElement.find('.expressionAttr[data-l1key=type]').value() === 'action') {
            expressionElement.find('.expressionAttr[data-l1key=expression]').value(result.human);
            nextdom.cmd.displayActionOption(expressionElement.find('.expressionAttr[data-l1key=expression]').value(), '', function (html) {
                clearRedoStack();
                expressionElement.find('.expressionOptions').html(html);
                initTextAreaAutosize();
                setUndoStack();
            });
        }
        if (expressionElement.find('.expressionAttr[data-l1key=type]').value() === 'condition') {
            var message = '';
            switch (result.cmd.subType) {
                case 'numeric':
                    message = getNumericExpressionHTML(result.human);
                    break;
                case 'string':
                    message = getStringExpressionHTML(result.human);
                    break;
                case 'binary':
                    message = getBinaryExpressionHTML(result.human);
                    break;
                default:
                    message = 'Aucun choix possible';
                    break;
            }
            bootbox.dialog({
                title: "{{Ajout d'une nouvelle condition}}",
                message: message,
                buttons: {
                    "Ne rien mettre": {
                        className: "btn-default",
                        callback: function () {
                            expressionElement.find('.expressionAttr[data-l1key=expression]').atCaret('insert', result.human);
                        }
                    },
                    success: {
                        label: "Valider",
                        className: "btn-primary",
                        callback: function () {
                            clearRedoStack();
                            var condition = result.human;
                            var operatorValue = $('.conditionAttr[data-l1key=operator]').value();
                            var operandeValue = $('.conditionAttr[data-l1key=operande]').value();
                            var nextValue = $('.conditionAttr[data-l1key=next]').value();
                            condition += ' ' + operatorValue;
                            if (result.cmd.subType === 'string') {
                                if (operatorValue === 'matches') {
                                    condition += ' "/' + operandeValue + '/"';
                                } else {
                                    condition += ' "' + operandeValue + '"';
                                }
                            } else {
                                condition += ' ' + operandeValue;
                            }
                            condition += ' ' + nextValue + ' ';
                            expressionElement.find('.expressionAttr[data-l1key=expression]').atCaret('insert', condition);
                            if (nextValue !== '') {
                                elementData.click();
                            }
                            setUndoStack();
                        }
                    },
                }
            });
        }
    });
}

/**
 * Load scenario with the URL data
 */
function loadFromUrl() {
    var scenarioIdFromUrl = getUrlVars('id');
    if (is_numeric(scenarioIdFromUrl)) {
        if (document.querySelectorAll('.scenarioDisplayCard[data-scenario_id=' + scenarioIdFromUrl + ']').length !== 0) {
            let url = document.location.toString();
            var tabCode = GENERAL_TAB;
            if (url.match('#')) {
                tabCode = url.split('#')[1];
            }
            loadScenario(scenarioIdFromUrl, tabCode);
        }
    }

}

/**
 * update URL and tab activation
 */
function updateUrlTab() {
    let url = document.location.toString();
    let tabCode = GENERAL_TAB;
    if (url.match('#')) {
        tabCode = url.split('#')[1];
        $('.nav-tabs a[href="#' + tabCode + '"]').tab('show');
    }
    $('.nav-tabs a').off('shown.bs.tab').on('shown.bs.tab', function (e) {
        window.location.hash = e.target.hash;
        updateUrlTab();
    });
    if (tabCode != PROGRAM_TAB) {
        $("#programActionBar").hide();
    } else {
        $("#programActionBar").show();
    }
    setTimeout(function () {
        setHeaderPosition(false);
    }, 100);
}

/**
 * Add undo state in stack
 */
function setUndoStack() {
    // Capture active state and push in stack
    let newStack = scenarioContainer.clone();
    undoStackPosition += 1;
    undoStack[undoStackPosition] = newStack;
    if (undoStack.length-1 > undoLimit) {
        undoStack = undoStack.slice(1, undoStackPosition + 1);
        undoStackPosition -= 1;
    }
    updateUndoBtn();
}

/**
 * Clear the stack at the actual history position
 */
function clearRedoStack() {
    undoStack = undoStack.slice(0, undoStackPosition + 1);
    undoStackPosition -= 1;
    setUndoStack();
}

/**
 * Undo state from stack
 */
function undo() {
    if (undoStackPosition > 0) {
        undoStackPosition -= 1;
        scenarioContainer.replaceWith(undoStack[undoStackPosition]);
        scenarioContainer = $('#div_scenarioElement');
        $('.element').removeClass("scenario-bloc-focused");
        $('.expression').removeClass("scenario-action-focused");
        updateUndoBtn();
        initScenarioEditorEvents();
    } else {
        notify("Attention", "La pile d'undo est vide", 'warning');
    }
}

/**
 * Redo state from stack
 */
function redo() {
    if (undoStackPosition < undoStack.length-1) {
        undoStackPosition += 1;
        scenarioContainer.replaceWith(undoStack[undoStackPosition]);
        scenarioContainer = $('#div_scenarioElement');
        $('.element').removeClass("scenario-bloc-focused");
        $('.expression').removeClass("scenario-action-focused");
        updateUndoBtn();
        initScenarioEditorEvents();
    } else {
        notify("Attention", "La pile de redo est vide", 'warning');
    }
}

/**
 * Reset undo/redo stack
 */
function resetUndo() {
    undoStack = new Array();
    undoStackPosition = -1;
    undoLimit = 15;
    undoBtnSpan.hide();
    redoBtnSpan.hide();
}

/**
 * Set state in memory
 */
function setMemoryStack() {
    let newStack = scenarioContainer.clone();
    memoryStack = newStack;
}

/**
 * Set state in memory
 */
function resetMemoryStack() {
    if (undoStack.length > 0) {
        undoStackPosition = undoStack.length-1;
        scenarioContainer.replaceWith(undoStack[undoStackPosition]);
        scenarioContainer = $('#div_scenarioElement');
        $('.element').removeClass("scenario-bloc-focused");
        $('.expression').removeClass("scenario-action-focused");
        initScenarioEditorEvents();
    }
}

/**
 * Recall state from memory
 */
function recallMemoryStack() {
    scenarioContainer.replaceWith(memoryStack);
    scenarioContainer = $('#div_scenarioElement');
    $('.element').removeClass("scenario-bloc-focused");
    $('.expression').removeClass("scenario-action-focused");
    initScenarioEditorEvents();
}

/**
 * Update display of undo/redo buttons
 */
function updateUndoBtn() {
    if (undoStackPosition > 0) {
        undoBtnSpan.show().html(undoStackPosition);
    } else {
        undoBtnSpan.hide();
    }
    if (undoStackPosition < undoStack.length-1) {
        redoBtnSpan.show().html(undoStack.length-1-undoStackPosition);
    } else {
        redoBtnSpan.hide();
    }
}

/**
 *
 * @param bloc input bloc ref
 * @param onlyBloc true = focus only the bloc not action
 */
function blocFocusing(bloc,onlyBloc) {
    $('.element').removeClass("scenario-bloc-focused");
    if (!onlyBloc) {
        $('.expression').removeClass("scenario-action-focused");
    }
    BLOC_FOCUS = bloc.closest('.element');
    BLOC_FOCUS.addClass("scenario-bloc-focused");
    if (!onlyBloc) {
        if (bloc.closest(".expression").find(".expressionAttr[data-l1key='type']").filter(function() { return this.value == 'condition' }).length==0) {
            ACTION_FOCUS = bloc.closest('.expression');
            ACTION_FOCUS.addClass("scenario-action-focused");
        } else {
            ACTION_FOCUS = null;
        }
    } else {
        BLOC_LAST_FOCUS = true;
    }
}

/**
 * Bloc/Action copy or cut
 *
 * @param blocType Type de bloc to copy/cut
 * @param blocCut TRUE = bloc cut
 * @param notStack TRUE = not save cut in stack
 */
function blocCopy(blocType,blocCut=false,notStack=false) {
    if (blocType) {
        SC_CLIPBOARD = blocType.clone();
        BLOC_LAST_CUT = false;
        // CTRL key = CUT
        if (blocCut) {
            BLOC_LAST_CUT = true;
            if (!notStack) {
                clearRedoStack();
            }
            blocType.remove();
            if (!notStack) {
                setUndoStack();
            }
        }
    } else {
        notify("Attention", "Aucune selection !", 'warning');
    }
}

/**
 * Bloc paste
 *
 * @param blocReplace TRUE = replace
 * @param blocAfter TRUE = paste after focus bloc
 * @param notClear TRUE = not clear stack
 */
 function blocPaste(blocReplace=false,blocAfter=true,notClear=false) {
    if (SC_CLIPBOARD && BLOC_FOCUS) {
        if (!notClear) {
          clearRedoStack();
        }
        let newColorIndex = getNextColorIndex();
        let newBloc = $(SC_CLIPBOARD).clone();
        if (!BLOC_LAST_CUT) {
            newBloc.find('input[data-l1key="id"]').attr("value", "");
            newBloc.find('input[data-l1key="scenarioElement_id"]').attr("value", "");
            newBloc.find('input[data-l1key="scenarioSubElement_id"]').attr("value", "");
        }
        if (BLOC_CHANGE_COLOR) {
            if (newBloc.hasClass('element')) {
                newBloc.css('background-color',listColorStrong[newColorIndex]);
                newBloc.find('.empty').parent().css('background-color',listColor[newColorIndex]);
            } else {
                newBloc.find('.element').css('background-color',listColorStrong[newColorIndex]);
                newBloc.find('.element').find('.empty').parent().css('background-color',listColor[newColorIndex]);
            }
        }
        if (BLOC_LAST_FOCUS) {
            if (newBloc.hasClass('expression') || BLOC_FOCUS.parent().hasClass('element')) {
                if (blocAfter) {
                    newBloc.insertAfter(BLOC_FOCUS);
                } else {
                    newBloc.insertBefore(BLOC_FOCUS);
                }
            } else {
                newDiv = '<div class="expression scenario-group sortable col-xs-12">';
                newDiv += '<input class="expressionAttr" data-l1key="type" style="display : none;" value="element">';
                newDiv += '<div class="col-xs-12" style="padding-right: 0px; padding-left: 0px;" id="insertHere">';
                newDiv += '</div>';
                newDiv += '</div>';
                if (blocAfter) {
                    if (BLOC_FOCUS.parent().parent().hasClass('expression')) {
                        $(newDiv).insertAfter(BLOC_FOCUS.parent().parent());
                    } else {
                        $(newDiv).insertAfter(BLOC_FOCUS);
                    }
                } else {
                    if (BLOC_FOCUS.parent().parent().hasClass('expression')) {
                        $(newDiv).insertBefore(BLOC_FOCUS.parent().parent());
                    } else {
                        $(newDiv).insertBefore(BLOC_FOCUS);
                    }
                }
                newBloc.appendTo('#insertHere');
                $('#insertHere').removeAttr('id');
            }
            // CTRL = REPLACE
            if (blocReplace) {
                BLOC_FOCUS.remove();
            }
        } else {
            if (newBloc.hasClass('expression')) {
                if (blocAfter) {
                    newBloc.insertAfter(ACTION_FOCUS);
                } else {
                    newBloc.insertBefore(ACTION_FOCUS);
                }
            } else {
                let newDiv = '<div class="expression scenario-group sortable col-xs-12">';
                newDiv += '<input class="expressionAttr" data-l1key="type" style="display : none;" value="element">';
                newDiv += '<div class="col-xs-12" style="padding-right: 0px; padding-left: 0px;" id="insertHere">';
                newDiv += '</div>';
                newDiv += '</div>';
                if (blocAfter) {
                    $(newDiv).insertAfter(ACTION_FOCUS);
                } else {
                    $(newDiv).insertBefore(ACTION_FOCUS);
                }

                newBloc.appendTo('#insertHere');
                $('#insertHere').removeAttr('id');
            }
            // CTRL = REPLACE
            if (blocReplace) {
                ACTION_FOCUS.remove();
            }
        }
        if (!BLOC_LAST_CUT) {
            $('.element').removeClass("scenario-bloc-focused");
            $('.expression').removeClass("scenario-action-focused");
            BLOC_FOCUS = null;
            ACTION_FOCUS = null;
        }
        updateSortable();
        setUndoStack();
    } else {
        notify("Attention", "Aucun bloc selectionné, ni copié !", 'warning');
    }
}

/**
 * Action paste
 *
 * @param actionReplace TRUE = replace
 * @param actionAfter TRUE = paste after focus bloc
 * @param notClear TRUE = not clear stack
 */
 function actionPaste(actionReplace=false,actionAfter=true,notClear=false) {
    if (SC_CLIPBOARD && (ACTION_FOCUS || BLOC_FOCUS)) {
        if (!notClear) {
          clearRedoStack();
        }
        let newBloc = $(SC_CLIPBOARD).clone();
        if (!BLOC_LAST_CUT) {
            newBloc.find('input[data-l1key="id"]').attr("value", "");
            newBloc.find('input[data-l1key="scenarioElement_id"]').attr("value", "");
            newBloc.find('input[data-l1key="scenarioSubElement_id"]').attr("value", "");
        }
        if (ACTION_FOCUS) {
            if (actionAfter) {
                newBloc.insertAfter(ACTION_FOCUS);
            } else {
                newBloc.insertBefore(ACTION_FOCUS);
            }
            // CTRL = REPLACE
            if (actionReplace) {
                ACTION_FOCUS.remove();
            }
        } else {
            if (actionAfter) {
                newBloc.insertAfter(BLOC_FOCUS.find(".empty").first());
            } else {
                newBloc.insertBefore(BLOC_FOCUS.find(".empty").first());
            }
            // CTRL = REPLACE
            if (actionReplace) {
                BLOC_FOCUS.remove();
            }
        }
        if (!BLOC_LAST_CUT) {
            $('.element').removeClass("scenario-bloc-focused");
            $('.expression').removeClass("scenario-action-focused");
            BLOC_FOCUS = null;
            ACTION_FOCUS = null;
        }
        updateSortable();
        setUndoStack();
    } else {
        notify("Attention", "Aucun bloc ni action selectionné(e)s, ni copié(e)s !", 'warning');
    }
}
