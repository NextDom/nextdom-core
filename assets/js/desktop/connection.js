$('#in_login_username').on('focusout change keypress',function(){
    nextdom.user.useTwoFactorAuthentification({
        login: $('#in_login_username').value(),
        error: function (error) {
           notify('core',error.message, 'danger');
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
    tryLogin();
});

$('#in_login_password').keypress(function(e) {
    if(e.which == 13) {
        tryLogin();
    }
});

$('#in_twoFactorCode').keypress(function(e) {
    if(e.which == 13) {
        tryLogin();
    }
});

function tryLogin() {
    nextdom.user.login({
        username: $('#in_login_username').val(),
        password: $('#in_login_password').val(),
        twoFactorCode: $('#in_twoFactorCode').val(),
        storeConnection: $('#cb_storeConnection').value(),
        error: function (error) {
            notify('Core',error.message,'error');
        },
        success: function (data) {
            window.location.href = 'index.php?v=d';
        }
    });
}
