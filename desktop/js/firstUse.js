$(document).ready(function () {
    var navListItems = $('div.setup-panel div a'),
        allWells = $('.setup-content'),
        allNextBtn = $('.nextBtn'),
        allReturnBtn = $('.returnBtn');

    allWells.hide();

    navListItems.click(function (e) {
        e.preventDefault();
        var $target = $($(this).attr('href')),
            $item = $(this);

        if (!$item.hasClass('disabled')) {
            navListItems.removeClass('btn-primary').addClass('btn-default');
            $item.addClass('btn-primary');
            allWells.hide();
            $target.show();
            $target.find('input:eq(0)').focus();
        }
    });

    allNextBtn.click(function(){
        var curStep = $(this).closest(".setup-content"),
            curStepBtn = curStep.attr("id"),
            nextStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a"),
            curInputs = curStep.find("input[type='text'],input[type='url']"),
            isValid = true;

        $(".form-group").removeClass("has-error");
        for(var i=0; i<curInputs.length; i++){
            if (!curInputs[i].validity.valid){
                isValid = false;
                $(curInputs[i]).closest(".form-group").addClass("has-error");
            }
        }

        if (isValid)
            nextStepWizard.removeAttr('disabled').trigger('click');
    });

    allReturnBtn.click(function(){
        var curStep = $(this).closest(".setup-content"),
            curStepBtn = curStep.attr("id"),
            returnStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().prev().children("a"),
            curInputs = curStep.find("input[type='text'],input[type='url']"),
            isValid = true;

        $(".form-group").removeClass("has-error");
        for(var i=0; i<curInputs.length; i++){
            if (!curInputs[i].validity.valid){
                isValid = false;
                $(curInputs[i]).closest(".form-group").addClass("has-error");
            }
        }
    });
    $('div.setup-panel div a.btn-primary').trigger('click');
});
$("#toStep2").click(function(){
jeedom.user.login({
    username: "admin",
    password: "admin",
    error: function (error) {
        $('#div_alert').showAlert({message: error.message, level: 'danger'});
        $('.veen').animateCss('shake');
    },
    success: function (data) {
    }
});
});
$("#toStep3").click(function(){
    if($('#in_change_password').val() == $('#in_change_passwordToo').val()){
        jeedom.user.get({
            error: function (data) {
                notify("Erreur",data.message,"error")
            },
            success: function (data){
                var user = data;
                user.password = $('#in_change_password').val();
                jeedom.user.saveProfils({
                    profils: user,
                    error: function (error) {
                        notify("Erreur",error.message,"error")
                        $('.veen').animateCss('shake');
                    },
                    success : function (){
                        jeedom.config.load({
                            configuration: 'market::username',
                            error: function (error) {
                                notify("Erreur",error.message,"error")

                            },
                            success: function (data) {
                            }
                        });
                    }
                });
            }
        });
    }else{
        notify("Erreur","Les deux mots de passe ne sont pas identiques","error")
    }
});

$("#toStep5").click(function(){

});

$("#finishConf").click(function(){
    nextdom.config.save({
        configuration: {'nextdom::firstUse': 0},
        error: function (error) {
            notify("Core", error.message, 'error');
        },
        success: function () {
            notify("Core", '{{Sauvegarde réussie}}', 'success');
        }
    });
});
