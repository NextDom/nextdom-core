$('#bt_welcomeRecall').on('click', function () {
    $('#md_modal').dialog({title: "{{Bienvenue dans NextDom}}"});
    $("#md_modal").load('index.php?v=d&modal=welcome').dialog('open');
});