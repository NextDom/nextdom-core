    $(document).ready(function () {
        var navListItems = $('div.setup-panel div a');
        var allWells = $('.setup-content');
        var allNextBtn = $('.nextBtn');
    //    var allReturnBtn = $('.returnBtn');

        allWells.hide();

        navListItems.click(function (e) {
            e.preventDefault();
            var target = $($(this).attr('href'));
            var item = $(this);

            if (!item.hasClass('disabled')) {
                navListItems.removeClass('btn-primary').addClass('btn-default');
                item.addClass('btn-primary');
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
                    notify("Core", '{{Mot de passe changé avec success}}', 'success');
                    NextStep("#toStep3");
                }
            });
        } else {
            notify("Erreur", "Les deux mots de passe ne sont pas identiques", "error")
        }
    });

    $("#toStep4").click(function () {
        var username = $('#in_login_username_market').val();
        var password = $('#in_login_password_market').val();
        var address = 'https://jeedom.com/market';
        jeedom.config.save({
            configuration: {'market::username': username},
            error: function (error) {
                notify("Core", data.message, "error");
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
    $("#skipStep4").click(function () {
        NextStep("#toStep4");
    });

    $("#toStep5").click(function () {
        NextStep("#toStep5");
    }
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