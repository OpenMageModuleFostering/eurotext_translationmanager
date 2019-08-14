document.observe('dom:loaded', function () {

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

varienGridMassaction.prototype.selectAll = function () {
    this.gridIds.split(',').each(function (key) {
        this.checkedString = varienStringArray.add(key, this.checkedString);
    }.bind(this));
    this.checkCheckboxes();
    this.updateCount();
    this.clearLastChecked();
    return false;
};

varienGridMassaction.prototype.unselectAll = function () {
    this.gridIds.split(',').each(function (key) {
        this.checkedString = varienStringArray.remove(key, this.checkedString);
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
    if (this.containerId == "productGrid") {
        productGridJsObject.reloadParams.category_id = null;
    }
    this.reload(this.addVarToUrl(this.filterVar, ''));
};
