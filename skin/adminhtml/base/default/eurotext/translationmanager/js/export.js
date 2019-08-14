function exportProject(url, successUrl, formkey, project_id, offset, step) {
    var postdata = {
        form_key: formkey,
        project_id: project_id,
        offset: offset,
        step: step
    };

    new Ajax.Request(url, {
            parameters: postdata,
            onSuccess: function (response) {
                var jsonData = null;

                try {
                    jsonData = response.responseJSON;
                } catch (e) {
                    location.reload();
                    return;
                }

                if (jsonData != null) {
                    var statusList = $$('.eurotext_export_status')[0];
                    var statusMsg = jsonData.status_msg ? jsonData.status_msg : Translator.translate('Please wait …');
                    var statusCode = jsonData.status_code;

                    if (statusCode == 'ok' || statusCode == 'success') {
                        statusList.insert({bottom: '<li class="' + statusCode + '">' + statusMsg + '</li>'});

                        if (jsonData.finished == "0") {
                            exportProject(url, successUrl, formkey, project_id, jsonData.offset, jsonData.step);
                        } else {
                            var text = Translator.translate('Back to Project');
                            var button = '<button type="button" onclick="location.href=\'' + successUrl + '\'" style=""><span><span>' + text + '</span></span></button>';
                            statusList.insert({bottom: '<li class="success">' + button + '</li>'})
                        }
                    } else {
                        statusList.insert({bottom: '<li class="' + statusCode + '">' + statusMsg + '</li>'});
                        statusList.insert({bottom: '<li class="' + statusCode + '"><a onclick="location.reload();">' + Translator.translate("Try again") + '</a></li>'});
                    }
                    window.scrollTo(0, document.body.scrollHeight);
                }
            }
        }
    );
}
