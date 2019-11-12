function %%FCT_NAME%% (btn) {
    $(btn).tooltip('destroy');
    $(btn).closest('.input-group').remove();
}