jQuery(function ($) {

	function init() {
		$(document).off('click', '.js-phases-progress .loadbar.enable .progress-bar:not(.min-active-value)');
		$(document).on('click', '.js-phases-progress .loadbar.enable .progress-bar:not(.min-active-value)', progressBarClick);
        $(document).on("click", ".js-order-progress .progress-bar:not(.min-active-value)", function () {
           setReportProgress($(this).data('value'), $(this).data('track-id'));
        });
        $(document).on("mouseenter", ".js-order-progress .progress-bar:not(.min-active-value)", function () {
           showPotentialReportProgress($(this).data('value'), $(this).data('track-id'));
        });
        $(document).on("mouseleave", ".js-order-progress .loadbar.enable", function () {
            showCurrentReportProgress($(this).find('.progress-bar:not(.min-active-value)').data('track-id'));
        });
	}

	/* Прогрессбары этапов */
	function progressBarClick() {
		var $bar = $(this);
		var $item = $bar.closest('.js-phases-item');
		var value = $bar.data('value');
		var wasFilled = false;
		var currentPercent = $item.find('.js-progress-value').text();

		$item.addClass("stage-changed");

		if (value == 10 && $bar.hasClass('progress-bar-fill') && currentPercent == 10) {
			wasFilled = true;
		}

		$item.find('.progress-bar').each(function () {
			var bar = $(this);
			if (bar.data('value') <= value) {
				bar.addClass('progress-bar-fill');
			} else {
				bar.removeClass('progress-bar-fill');
			}
		});

		if (wasFilled) {
			$bar.removeClass('progress-bar-fill');
			value = 0;
		}

		$item.data('progress', value)
			.find('.js-progress-value').text(value);

		var $report = $item.closest('.new-report, .report-message');
		if ($report.length) {
			var index = $item.data('index');
			saveProgressToForm($report, index, value)
		}
	}

	function saveProgressToForm($report, index, value) {

		$report.find('[name="phase-' + index + '"]').val(value);

		var stagesProgress = [];
		$report.find('.stage-changed').each(function () {
			var $phase = $(this);
			var id = $phase.data('id');
			var progress = $phase.data('progress');
			if (id) {
				stagesProgress.push({id: id, progress: progress});
			}
		});
		$report.find('input[name=stages]').val(JSON.stringify(stagesProgress));
	}

	/* Прогрессбары одиночного заказа */
    function setReportProgress(value, track_id) {
        var parentWrapper = $("#track-id-" + track_id);
        if (parentWrapper.find('.loadbar.enable').length) {
            parentWrapper.find("input[name='isExecutedOn']").val(value);
            parentWrapper.find(".js-progress-value").text(value);
            parentWrapper.find(".progress-bar").each(function () {
                var bar = $(this);
                if (bar.data("value") <= value) {
                    bar.addClass('progress-bar-fill');
                } else {
                    bar.removeClass('progress-bar-fill');
                }
                if (value == 100 && bar.data("value") == 100) {
                    var cloneForm = $("#worker_check_form").clone();
                    $("#track-form").find(".track-page-group-btn .green-btn").click();
                    $("#track-form").find(".track-page-group-btn").hide();
                    cloneForm.find(".sm-text-center").remove();
                    cloneForm.find("#message_body2").attr("placeholder", t("Отправьте выполненную работу на проверку"));
                    cloneForm.find("#message_body2").val(parentWrapper.find("#kwork_report_message").val());
                    cloneForm.removeClass("track-form-js");
                    $('<input>').attr({
                        type: 'hidden',
                        value: (track_id) ? track_id : "new",
                        name: 'inprogress_report'
                    }).appendTo(cloneForm);
                    cloneForm.show();
                    parentWrapper.find(".append-form-wrapper").html(cloneForm);
                    parentWrapper.find(".exist-form-wrapper").addClass('hide');
                } else {
                    $("#track-form").find(".track-page-group-btn").show();
                    parentWrapper.find(".append-form-wrapper").html("");
                    parentWrapper.find(".exist-form-wrapper").removeClass('hide');
                }
            })
        }


    }
    function showPotentialReportProgress(value, track_id) {
        var parentWrapper = $("#track-id-" + track_id);
        if (parentWrapper.find('.loadbar.enable').length) {
            parentWrapper.find(".js-progress-value").text(value);
        }
    }

    function showCurrentReportProgress(track_id) {
        var parentWrapper = $("#track-id-" + track_id);
        if (parentWrapper.find('.loadbar.enable').length) {
            var curValue = parentWrapper.find("input[name='isExecutedOn']").val();
            parentWrapper.find(".js-progress-value").text(curValue);
        }
    }


	init();
});
