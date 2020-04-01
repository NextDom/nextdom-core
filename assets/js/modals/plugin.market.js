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
* @Email   <admin@nextdom.org>
* @Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
*/
$(document).ready(function () {
    initDataModal();
    initInstallationButtons();
    initPluginCarousel();
    $('#close-button').click(function () {
        $('#md_modal').dialog('close');
    });
});

/* Initialise la fenêtre modale du plugin */
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
    $('#plugin-name').text(currentPlugin['name']);

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
}

/* Initialise les boutons d'installation */
function initInstallationButtons() {
    var defaultBranch = currentPlugin['defaultBranch'];

    $('#install-plugin').click(function () {
        installPlugin(currentPlugin['defaultBranch']);
    });
    $('#default-branch-information').text(defaultBranch);
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
            $('#install-plugin').hide();
            var installedBranch = currentPlugin['installedBranchData']['branch'];
            $('#default-branch-information').text(installedBranch);
            initBranchesChoice(currentPlugin['branchesList'], installedBranch);
            if (currentPlugin['installedBranchData']['needUpdate'] === true) {
                $('#update-plugin').click(function () {
                    updatePlugin(currentPlugin['installedBranchData']['id'], false);
                });
            }
            else {
                $('#update-plugin').hide();
            }
        }
    }
    else {
        $('#remove-plugin').hide();
        $('#update-plugin').hide();
        $('#config-plugin').hide();
    }
}

/* Evènement du bouton de mise à jour des branches */
function initBranchesUpdate(defaultBranchChoice) {
    nextdom_market.get({
        post_success: function(data) {
            initBranchesChoice(data['result'], defaultBranchChoice);
        },
        params: 'branches',
        data: {source: currentPlugin['sourceName'], fullName: currentPlugin['fullName']}
    });
}

/* Initalise le bouton de choix des branches
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
        $('#install-plugin-advanced').css('display', 'inline-block');
    } else {
        $('#get-branches-informations').css('display', 'none');
        $('#install-plugin-advanced').css('display', 'inline-block');
        $('#install-plugin-advanced button').addClass("disabled");
    }
}

/* Lance l'installation du plugin
 *
 * @param branch Nom de la branche GitHub à installer
 */
function installPlugin(branch) {
    nextdom.update.install(
        {
            update: {
                logicalId: currentPlugin['id'],
                configuration: {
                    user: currentPlugin['gitId'],
                    repository: currentPlugin['gitName'],
                    version: branch
                },
                source: 'github'
            },
            post_success: function() {
                window.location.replace('/index.php?v=d&p=plugin&id=' + currentPlugin['id']);
            }
        }
    );
}

/* Lance l'installation du plugin
 *
 * @param pluginId Identifiant du plugin
 */
function removePlugin(pluginId) {
    nextdom.update.remove({
        id: pluginId,
        post_success: function() {
            reloadWithMessage(1);
        }
    })
}

/* Initialise le carousel pour les screenshots */
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
            var itemToAdd = $('<div class="' + itemClassList + '"><img src="' + screenshots[screenshotIndex] + '"/></div>');
            $('#plugin-screenshots .carousel-inner').append(itemToAdd);
            $('#plugin-screenshots').carousel();
        }
    }
}
