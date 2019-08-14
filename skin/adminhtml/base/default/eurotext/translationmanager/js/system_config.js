document.observe('dom:loaded', function () {
    if (!$('eurotext_config')) {
        return;
    }

    if ($('eurotext_config_register_url').readAttribute('value')) {
        $$('#eurotext_user_settings input').each(function (element) {
            element.addClassName('required-entry');
        });
    }
});

