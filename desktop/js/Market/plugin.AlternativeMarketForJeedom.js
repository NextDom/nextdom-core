$(document).ready(function () {
    initDataModal();
    initInstallationButtons();
    initPluginCarousel();
    $('#close-button').click(function () {
        $('#md_modal').dialog('close');
    });
});

/**
 * Initialise la fenêtre modale du plugin
 */
function initDataModal() {
    var defaultBranch = currentPlugin['defaultBranch'];
    var fullName = currentPlugin['fullName'];
    $('#plugin-icon').attr('src', currentPlugin['iconPath']);
    $('#description-content').text(currentPlugin['description']);
    $('#author .list-info').text(currentPlugin['author']);
    $('#licence .list-info').text(currentPlugin['licence']);
    $('#category .list-info').text(currentPlugin['category']);
    $('#gitid .list-info').text(currentPlugin['gitId']);
    $('#gitrepo .list-info').text(currentPlugin['gitName']);

    if (currentPlugin['changelogLink'] === null) {
        $('#changelog-link').css('display', 'none');
    }
    else {
        $('#changelog-link').attr('href', currentPlugin['changelogLink']);
    }
    if (currentPlugin['documentationLink'] === null) {
        $('#documentation-link').css('display', 'none');
    }
    else {
        $('#documentation-link').attr('href', currentPlugin['documentationLink']);
    }
    $('#github-link').attr('href', 'https://github.com/' + fullName);
    $('#travis-badge').attr('href', 'https://travis-ci.org/' + fullName + '?branch=' + defaultBranch);
    $('#travis-badge img').attr('src', 'https://travis-ci.org/' + fullName + '.svg?branch=' + defaultBranch);
    $('#coveralls-badge').attr('href', 'https://coveralls.io/github/' + fullName + '?branch=' + defaultBranch);
    $('#coveralls-badge img').attr('src', 'https://coveralls.io/repos/github/' + fullName + '/badge.svg?branch=' + defaultBranch);
    $('#waffle-badge').attr('href', 'https://waffle.io/' + fullName);
    $('#waffle-badge img').attr('src', 'https://badge.waffle.io/' + fullName + '.svg?columns=all');
}

/**
 * Initialise les boutons d'installation
 */
function initInstallationButtons() {
    var defaultBranch = currentPlugin['defaultBranch'];

    $('#install-plugin').click(function () {
        installPlugin(currentPlugin['defaultBranch']);
    });
    $('#default-branch-information').text(branchStr + defaultBranch);
    if (currentPlugin['branchesList'].length > 0) {
        initBranchesChoice(currentPlugin['branchesList'], defaultBranch);
    }
    else {
        $('#get-branches-informations button').click(function () {
            initBranchesUpdate(currentPlugin['defaultBranch']);
        });
    }

    if (currentPlugin['installed']) {
        $('#config-plugin').attr('href', '/index.php?v=d&p=plugin&id=' + currentPlugin['id']);
        $('#remove-plugin').click(function () {
            removePlugin(currentPlugin['id']);
        });
        if (currentPlugin['installedBranchData'] !== false) {
            $('#install-plugin').parent().hide();
            var installedBranch = currentPlugin['installedBranchData']['branch'];
            $('#default-branch-information').text('Branche ' + installedBranch);
            initBranchesChoice(currentPlugin['branchesList'], installedBranch);
            if (currentPlugin['installedBranchData']['needUpdate'] === true) {
                $('#update-plugin').click(function () {
                    updatePlugin(currentPlugin['installedBranchData']['id'], false);
                });
            }
            else {
                $('#update-plugin').parent().hide();
            }
        }
    }
    else {
        $('#remove-plugin').parent().hide();
        $('#update-plugin').parent().hide();
        $('#config-plugin').parent().hide();
    }
}

/**
 * Evènement du bouton de mise à jour des branches
 */
function initBranchesUpdate(defaultBranchChoice) {
    $.post({
        url: 'plugins/AlternativeMarketForJeedom/core/ajax/AlternativeMarketForJeedom.ajax.php',
        data: {
            action: 'get',
            params: 'branches',
            data: {source: currentPlugin['sourceName'], fullName: currentPlugin['fullName']}
        },
        dataType: 'json',
        success: function (data, status) {
            initBranchesChoice(data['result'], defaultBranchChoice);
        },
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        }
    });
}

/**
 * Initalise le bouton de choix des branches
 *
 * @param branchesList Liste des branches
 */
function initBranchesChoice(branchesList, defaultBranchChoice) {
    if (branchesList.length > 1) {
        var ulList = $('#install-plugin-advanced .dropdown-menu');
        ulList.empty();
        for (var branchIndex = 0; branchIndex < branchesList.length; ++branchIndex) {
            var branchName = branchesList[branchIndex]['name'];
            if (branchName !== defaultBranchChoice) {
                var liItem = $('<li data-branch="' + branchName + '"><a href="#">' + installBranchStr + ': ' + branchName + '</a></li>');
                liItem.click(function () {
                    installPlugin($(this).data('branch'));
                });
                ulList.append(liItem);
            }
        }
        $('#get-branches-informations').css('display', 'none');
        $('#install-plugin-advanced').css('display', 'block');
    }
    else {
        $('#get-branches-informations').css('display', 'none');
        $('#install-plugin-advanced').css('display', 'block');
        $('#install-plugin-advanced button').addClass("disabled");
    }
}

/**
 * Lance l'installation du plugin
 *
 * @param branch Nom de la branche GitHub à installer
 */
function installPlugin(branch) {

    var data = {
        action: 'save',
        // Version de l'installation par URL
        // update: '{"logicalId":"' + currentPlugin['id'] + '","configuration":{"url":"' + currentPlugin['url'] + '/archive/' + branch + '.zip"},"source":"url"}'
        // Version de l'installation par GitHub
        update: '{"logicalId":"' + currentPlugin['id'] + '","configuration":{"user":"' + currentPlugin['gitId'] + '", "repository":"' + currentPlugin['gitName'] + '", "version":"' + branch + '"},"source":"github"}'
    };
    ajaxQuery('core/ajax/update.ajax.php', data, function () {
        window.location.replace('/index.php?v=d&p=plugin&id=' + currentPlugin['id']);
    });
}

/**
 * Lance l'installation du plugin
 *
 * @param pluginId Identifiant du plugin
 */
function removePlugin(pluginId) {
    var data = {
        action: 'remove',
        id: pluginId
    };
    ajaxQuery('core/ajax/update.ajax.php', data, function () {
        reloadWithMessage(1);
    });
}

/**
 * Initialise le carousel pour les screenshots
 */
function initPluginCarousel() {
    if (currentPlugin['screenshots'].length > 0) {
        $('#plugin-screenshots').show();
        var screenshots = currentPlugin['screenshots'];
        var first = true;
        for (var screenshotIndex = 0; screenshotIndex < screenshots.length; ++screenshotIndex) {
            var itemClassList = 'item';
            if (first === true) {
                itemClassList += ' active';
                first = false;
            }
            var itemToAdd = $('<div class="' + itemClassList + '"><img src="' + screenshots[screenshotIndex] + '"/></div>')
            $('#plugin-screenshots .carousel-inner').append(itemToAdd);
            $('#plugin-screenshots').carousel();
        }
    }
}