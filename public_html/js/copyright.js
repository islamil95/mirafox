$(document).ready(function () {
	var dialogFiles = new FileUploader({
		files: {},
		selector: "#load-files-copyright",
		input: {
			name: "support_files"
		},
		buttonDisabled: '.js-uploader-button-disable',
	});
	$("#foxForm").on("submit", function () {
		if (!dialogFiles.canSave()) {
			var $submitCopyrightMessage = $("#submit-copyright-message");
			var interval = setInterval(function () {
				if (dialogFiles.canSave()) {
					clearInterval(interval);
					$submitCopyrightMessage.prop("disabled", false);
					$submitCopyrightMessage.trigger("click");
				}
			}, 200);
			$submitCopyrightMessage.prop("disabled", true);
			$submitCopyrightMessage.val(t("Загрузка файлов..."));
			return false;
		}
	});
	var inputValidator = new ValidInputsModule();
	inputValidator.init();
});