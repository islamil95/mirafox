function getUrlParams() {
	var params = {};
	var sPageURL = decodeURIComponent(window.location.search.substring(1));
	var sURLVariables = sPageURL.split('&');

	for (var i = 0; i < sURLVariables.length; i++) {
        sParameter = sURLVariables[i].split('=');
        if (sParameter[1] === '') continue;
        params[sParameter[0]] = sParameter[1];
	}
	return params;
}

function getUpdatedUrlParamsString(paramsObject) {
    var newParams = paramsObject;
    if (typeof paramsObject == 'string') {
        newParams = {};
        var paramsRaw = paramsObject.split('&');
        for (var i = 0; i < paramsRaw.length; i++) {
            var param = paramsRaw[i].split("=");
            if (param[1] === '') continue;
            newParams[param[0]] = param[1];
        }
    }
    var params = Object.assign(getUrlParams(), newParams);
    return $.param(params);
}