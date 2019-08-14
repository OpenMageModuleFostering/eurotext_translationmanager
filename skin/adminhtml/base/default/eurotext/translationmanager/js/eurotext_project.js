document.observe('dom:loaded', function () {
    hideTabWhenCheckboxIsChecked('productmode', 'project_tabs_products_section');
    hideTabWhenCheckboxIsChecked('categorymode', 'project_tabs_categories_section');
    hideTabWhenCheckboxIsChecked('cmsmode', 'project_tabs_cms_block_section');
    hideTabWhenCheckboxIsChecked('cmsmode', 'project_tabs_cms_page_section');
    hideTabWhenCheckboxIsChecked('templatemode', 'project_tabs_transaction_email_section');
    hideTabWhenCheckboxIsChecked('langfilesmode', 'project_tabs_translate_file_section');

    // disable export button on project change
    new PeriodicalExecuter(function () {
        var exportButton = $('export_button');
        if (!$$('#project_tabs .tab-item-link.changed').size() || !exportButton) {
            return;
        }
        exportButton.addClassName('disabled').writeAttribute('disabled');
        var txt = Translator.translate('Please save the project before exporting it!');

        $('messages').update('<ul class="messages"><li class="notice-msg"><ul><li>' + txt + '</li></ul></li></ul>');
    }, 0.05);


    Validation.add(
        Translator.translate('validate-storeview-different'),
        Translator.translate('Please choose two different store views.'),
        {
            notEqualToField: 'storeview_src'
        }
    );
});

function hideTabWhenCheckboxIsChecked(checkboxId, tabId) {
    var elem = $(checkboxId);
    if (elem.checked) {
        $(tabId).up().hide();
    } else {
        $(tabId).up().show();
    }

    $(checkboxId).observe('click', function (event) {
        var elem = event.element();
        if (elem.checked) {
            $(tabId).up().hide();
        } else {
            $(tabId).up().show();
        }
    });
}

serializerController.prototype.orig_initialize = serializerController.prototype.initialize;
serializerController.prototype.initialize = function (hiddenDataHolder, predefinedData, inputsToManage, grid, reloadParamName) {
    this.orig_initialize(hiddenDataHolder, predefinedData, inputsToManage, grid, reloadParamName);

    // add reference to serializer
    this.grid.serializerController = this;
};

$(document).observe('uploader:fileError', function () {
    varienLoaderHandler.handler.onComplete();
});

$(document).observe('uploader:start', function () {
    varienLoaderHandler.handler.onCreate({options: {loadArea: ''}});
});

$(document).observe('uploader:fileSuccess', function (event) {
    window.location.href = event.memo.response.evalJSON().importExtractUrl;
});

varienGridMassaction.prototype.selectVisible = function () {
    this.getCheckboxesValues().each(function (key) {
        this.checkedString = varienStringArray.add(key, this.checkedString);
    }.bind(this));
    this.checkCheckboxes();
    this.updateCount();
    this.clearLastChecked();
    return false;
};

varienGridMassaction.prototype.oldCheckCheckboxes = varienGridMassaction.prototype.checkCheckboxes;
varienGridMassaction.prototype.checkCheckboxes = function () {
    this.oldCheckCheckboxes();
    var serializerController = this.grid.serializerController;
    if (serializerController) {
        var predefinedData = this.checkedString.split(',');
        serializerController.gridData = serializerController.getGridDataHash(predefinedData);
        serializerController.hiddenDataHolder.value = serializerController.serializeObject();
    }
};

varienGrid.prototype.resetFilter = function () {
    productGridJsObject.reloadParams.category_id = null;
    this.reload(this.addVarToUrl(this.filterVar, ''));
};
