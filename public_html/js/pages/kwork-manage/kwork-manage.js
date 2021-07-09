$(document).ready(function() {
	$(".change-kwork-name-js").click(function (e) {
		e.preventDefault();
		var kworkId = $(this).attr("rel");
		var kworkName = he.decode($(this).parent().find("a").find("span").html());

		$(".modal-kwork-change-name .kwork-name").val(kworkName);
		$(".modal-kwork-change-name .kwork-id").val(kworkId);

		resetRenameKworkError();
		$(".modal-kwork-change-name").modal("show");
	});

	$(".modal-kwork-change-name .js-kwork-change-name-cancel").click(function (e) {
		e.preventDefault();
		closeRenameKworkModal()
	});

	$(".modal-kwork-change-name .save-new-name").click(function (e) {
		e.preventDefault();
		resetRenameKworkError();

		var name = $(".modal-kwork-change-name .kwork-name").val();
		var kworkId = $(".modal-kwork-change-name .kwork-id").val();
		var sourceEl = $("#kwork_name_" + kworkId).find("span");

		if (sourceEl.html() == name) {
			closeRenameKworkModal();
		}
		if (name != "") {
			$.ajax({
				url: "/set_kwork_name",
				type: "POST",
				data: {kwork_id: kworkId, name: name},
				success: function (response) {
					if (response.success) {
						sourceEl.html(name);
						closeRenameKworkModal();
					} else {
						showRenameKworkError(response.data[0].text);
					}
				}
			});
		} else {
			showRenameKworkError("Название услуги не может быть пустым");
		}
	});

	function closeRenameKworkModal() {
		$(".modal-kwork-change-name .kwork-name").val("");
		$(".modal-kwork-change-name .kwork-id").val(0);
		$(".modal-kwork-change-name").modal("hide");
		resetRenameKworkError();
	}

	function resetRenameKworkError() {
		$(".modal-kwork-change-name .form-entry-error").html("").addClass("hidden");
	}

	function showRenameKworkError(error) {
		$(".modal-kwork-change-name .form-entry-error").html(error).removeClass("hidden");
	}
});