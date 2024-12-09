// General helper function for $.get ajax requests
function ajaxGet(uri, params) {
    params = typeof (params) == 'undefined' ? {} : params;
    var options = {
        type: 'GET',
        url: uri.indexOf(site_url) > -1 ? uri : site_url + uri
    };
    return $.ajax($.extend({}, options, params));
}

// General helper function for $.get ajax requests with dataType JSON
function ajaxGetJSON(uri, params) {
    params = typeof (params) == 'undefined' ? {} : params;
    params.dataType = 'json';
    return ajaxGet(uri, params);
}
function init_selectpicker() {
    appSelectPicker();
}