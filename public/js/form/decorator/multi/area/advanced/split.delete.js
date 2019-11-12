function %%FCT_NAME%% (btn) {
    $(btn).tooltip('destroy');
    $(btn).closest('.textarea-group').remove();
}