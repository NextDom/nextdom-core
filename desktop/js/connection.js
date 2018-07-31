$('#in_login_username').on('focusout change keypress',function(){
        nextdom.user.useTwoFactorAuthentification({
            login: $('#in_login_username').value(),
            error: function (error) {
                notify("Erreur", error.message, 'error');
            },
            success: function (data) {
                if(data == 1){
                    $('#div_twoFactorCode').show();
                }else{
                    $('#div_twoFactorCode').hide();
                }
            }
        });
    });
    $('#bt_login_validate').on('click', function() {
        nextdom.user.login({
            username: $('#in_login_username').val(),
            password: $('#in_login_password').val(),
            twoFactorCode: $('#in_twoFactorCode').val(),
            storeConnection: $('#cb_storeConnection').value(),
            error: function (error) {
                notify("Erreur", error.message, 'error');
                $('.veen').animateCss('shake');
            },
            success: function (data) {
                if($('#in_login_username').val() == $('#in_login_password').val()){
                    $('#phrase_login_btn').html('{{Votre mot de passe doit être changé.<br/>Pour plus de sécurité.}}');
                    $('#titre_login_btn').html('{{Information importante :}}');
                    $('.veen .wrapper').addClass('move');
                    $('.body').css('background','linear-gradient(360deg, rgba(147,204,1,0.6), rgba(147,204,1,1))');
                    $('.login-btn').css('color','#000000');
                    $(".veen .login-btn button").removeClass('active');
                    $(this).addClass('active');
                }else{
                    // TODO: Problème de l'animation
                    window.location.href = 'index.php?v=d';
                    $('.veen').animateCss('bounceOut', function(){
                        $('.veen').hide();
                        window.location.href = 'index.php?v=d';
                    });
                }
            }
        });
    });
    $('#bt_change_validate').on('click', function() {
        if($('#in_change_password').val() == $('#in_change_passwordToo').val()){
            nextdom.user.get({
                error: function (data) {
                    notify("Erreur", error.message, 'error');
                },
                success: function (data){
                    var user = data;
                    user.password = $('#in_change_password').val();
                    nextdom.user.saveProfils({
                        profils: user,
                        error: function (error) {
                            notify("Erreur", error.message, 'error');
                            $('.veen').animateCss('shake');
                        },
                        success : function (){
                            window.location.href = 'index.php?v=d';
                            // TODO: Problème de l'animation
                            $('.veen').animateCss('bounceOut', function(){
                            $('.veen').hide();
                            window.location.href = 'index.php?v=d';
                        });
                        }
                    })
                }
            });
        }else{
            notify("Erreur", 'Les deux mots de passe ne sont pas identiques', 'error');
        }
    });
    $('#in_login_password').keypress(function(e) {
          if(e.which == 13) {
             $('#bt_login_validate').trigger('click');
        }
     });
    $('#in_twoFactorCode').keypress(function(e) {
      if(e.which == 13) {
        $('#bt_login_validate').trigger('click');
      }
    });
    $('#in_change_passwordToo').keypress(function(e) {
      if(e.which == 13) {
        $('#bt_change_validate').trigger('click');
      }
     });
// ADD //
$(document).ready(function(){
    $(".veen .login-btn button").click(function(){
        $('.veen .wrapper').removeClass('move');
        $('.body').css('background','#ff4931');
        $(".veen .rgstr-btn button").removeClass('active');
        $(this).addClass('active');
    });
    window.setTimeout(function(){
        //$('.veen').removeClass('animated');
        $('.veen').removeClass('zoomIn');
        //$('.btn_help').removeClass('animated');
        $('.btn_help').removeClass('bounceInUp');
    }, 5000);
    window.setTimeout(function(){
        window.setInterval(function(){
            $('.btn_help').animateCss('shake');
            window.setTimeout(function(){
                $('.btn_help').removeClass('shake');
            },3000);
            },5000);
    }, 10000);
    
});
