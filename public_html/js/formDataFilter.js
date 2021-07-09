// iOS 11.3 Safari / macOS Safari 11.1 empty <input type="file"> XHR bug workaround.

var formDataFilter = function(formData) {
	var isSafari = navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1 &&  navigator.userAgent.indexOf('Android') == -1;
	
	if (!isSafari) {
		return formData;
	}

	if (!(window.FormData && formData instanceof window.FormData)) return formData;
	if (!formData.keys) return formData; // unsupported browser
	
	var newFormData = new window.FormData();
	
	Array.from(formData.entries()).forEach(function(entry) {
		var value = entry[1];
		if (value instanceof window.File && value.name === '' && value.size === 0) {
			newFormData.append(entry[0], new window.Blob([]), '');
		} else {
			newFormData.append(entry[0], value);
		}
	});

	return newFormData;
}