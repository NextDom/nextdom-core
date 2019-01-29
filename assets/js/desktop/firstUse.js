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
* @Email   <admin@nextdom.org>
* @Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
*/

$(document).ready(function () {
    var navListItems = $('div.setup-panel div a');
    var allWells = $('.setup-content');
    var allNextBtn = $('.nextBtn');
    var allReturnBtn = $('.returnBtn');

    allWells.hide();

    navListItems.click(function (e) {
        e.preventDefault();
        var target = $($(this).attr('href'));
        var item = $(this);

        if (!item.hasClass('disabled')) {
            navListItems.removeClass('primary-bg');
            navListItems.removeClass('btn-primary').addClass('btn-default');
            item.addClass('btn-primary');
            item.addClass('primary-bg');
            allWells.hide();
            target.show();
            target.find('input:eq(0)').focus();
        }
    });
    $('div.setup-panel div a.btn-primary').trigger('click');
});

$("#toStep2").click(function () {
    jeedom.user.login({
        username: "admin",
        password: "admin",
        error: function (error) {
            notify("Core", error.message, "error");
        },
        success: function (data) {
            NextStep("#toStep2");
        }
    });
});
$("#toStep3").click(function () {
    if ($('#in_change_password').val() !="") {
        if ($('#in_change_password').val() == $('#in_change_passwordToo').val()) {
            jeedom.user.get({
                error: function (data) {
                    notify("Core", data.message, "error");
                },
                success: function (data) {
                    var user = data;
                    user.password = $('#in_change_password').val();
                    jeedom.user.saveProfils({
                        profils: user,
                        error: function (error) {
                            notify("Core", error.message, "error");
                        },
                        success: function () {
                        }
                    });
                }
            });
            nextdom.config.save({
                configuration: {'nextdom::firstUse': 0},
                error: function (error) {
                    notify("Core", error.message, 'error');
                },
                success: function () {
                    notify("Core", '{{Mot de passe changé avec succès}}', 'success');
                    NextStep("#toStep3");
                }
            });
        } else {
            notify("Erreur", "Les deux mots de passe ne sont pas identiques !", "error")
        }
    } else {
        notify("Erreur", "Veuillez saisir un mot de passe ...", "error")
    }
});

$("#toStep4").click(function () {
    var username = $('#in_login_username_market').val();
    var password = $('#in_login_password_market').val();
    var address = 'https://jeedom.com/market';
    jeedom.config.save({
        configuration: {'market::username': username},
        error: function (error) {
            notify("Core", error.message, "error");
        },
        success: function (data) {
            jeedom.config.save({
                configuration: {'market::password': password},
                error: function (error) {
                    notify("Core", error.message, "error");
                },
                success: function (data) {
                    jeedom.repo.test({
                        repo: 'market',
                        error: function (error) {
                            notify("Core", error.message, "error");
                        },
                        success: function (data) {
                            NextStep("#toStep4");
                        }
                    });
                }
            });
        }
    });
});

$("#toStep5").click(function () {

    var radios = document.getElementsByName('theme');
    var config ="";
    for (var i = 0, length = radios.length; i < length; i++) {
                if (radios[i].value == "dark"){
                    console.log("dark");
                    config = {
                        'theme:color1' : '#33b8cc',
                        'theme:color2' : '#555555',
                        'theme:color3' : '#f4f4f5',
                        'theme:color4' : '#33B8CC',
                        'theme:color5' : '#ffffff',
                        'theme:color6' : '#f9fafc',
                        'theme:color7' : '#f4f4f5',
                        'theme:color8' : '#f4f4f5',
                        'theme:color9' : '#ecf0f5',
                        'theme:color10' : '#ffffff',
                        'theme:color11' : '#f5f5f5',
                        'theme:color12' : '#555555',
                        'theme:color13' : '#f5f5f5',
                        'theme:color14' : '#222d32',
                        'theme:color15' : '#ffffff'
                    }
        break;
                }
        }

        nextdom.config.save({
        configuration: config,
        error: function (error) {
            notify("Info", '{{theme parametré}}', 'success');
        }
    });
});

$("#backStep1").click(function () {
    BackStep("#backStep1");
});

$("#backStep2").click(function () {
    BackStep("#backStep2");
});

$("#backStep3").click(function () {
    BackStep("#backStep3");
});

$("#skipStep4").click(function () {
    NextStep("#toStep4");
});

$("#toStep5").click(function () {
    NextStep("#toStep5");
});

$("#finishConf").click(function () {
    location.reload();
});

function NextStep(_step) {
    var curStep = $(_step).closest(".setup-content");
    var curStepBtn = curStep.attr("id");
    var nextStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a");
    var curInputs = curStep.find("input[type='text'],input[type='url']");
    isValid = true;

    $(".form-group").removeClass("has-error");
    for (var i = 0; i < curInputs.length; i++) {
        if (!curInputs[i].validity.valid) {
            isValid = false;
            $(curInputs[i]).closest(".form-group").addClass("has-error");
        }
    }

    if (isValid) {
        nextStepWizard.removeAttr('disabled').trigger('click');
    }
}

function BackStep(_step) {
    var curStep = $(_step).closest(".setup-content");
    var curStepBtn = curStep.attr("id");
    var nextStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().prev().children("a");
    console.log(nextStepWizard);

    var curInputs = curStep.find("input[type='text'],input[type='url']");
    isValid = true;

    $(".form-group").removeClass("has-error");
    for (var i = 0; i < curInputs.length; i++) {
        if (!curInputs[i].validity.valid) {
            isValid = false;
            $(curInputs[i]).closest(".form-group").addClass("has-error");
        }
    }

    if (isValid) {
        nextStepWizard.removeAttr('disabled').trigger('click');
    }
}
