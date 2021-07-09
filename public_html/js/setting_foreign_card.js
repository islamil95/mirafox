var _settingForeignCardModal = '.js-setting-foreign-card-modal';

/**
 * Вызываем модальное окно редактирования данных,
 * для карты иностранного банка
 */
function showSettingForeignCard() {
	$(_settingForeignCardModal).modal('show');
}

/**
 * Проверяем, все ли поля были заполнены,
 * в зависимости от заполненности полей
 * блокируем или разблокируем кнопку отправки формы
 */
function checkFieldsForeignCard() {
	var $modal = $(_settingForeignCardModal);
	var $fields = $modal.find('[type="text"], [type="number"]');
	var $btn = $modal.find('[name="submit-setting-foreign"]');
	var hasEmpty = false;

	$fields.each(function() {
		var value = $(this).val();

		if (value.length === 0) {
			$btn.addClass('disabled').attr('disabled', 'disabled');
			hasEmpty = true;

			return true;
		}
	});

	if (hasEmpty === true) {
		return;
	}

	$btn.removeClass('disabled').removeAttr('disabled');
}

/**
 * Очистка формы наполнения доп. информацией
 * для иностранных карт
 */
function clearForeignCard() {
	var $modal = $(_settingForeignCardModal);
	var $fields = $modal.find('[type="text"], [type="number"]');

	$fields
			.val('')
			.removeClass('styled-input-empty');
	$fields
		.parent()
		.find('.error')
		.remove();
}

/**
 * Выставить данные для формы иностранных карт
 */
function setDataForeignCard() {
	var $modal = $(_settingForeignCardModal);
	var data = window.foreignCardData || {};

	if (Object.keys(data).length <= 0) {
		return;
	}

	$.each(data, function(k, v) {
		if (v) {
			$modal.find('[name="' + k + '"]').val(v);
		}
	});
}

$(function() {
	// Коллбэк открытия модального окна
	// редактирования иностранной карты
	$(_settingForeignCardModal).on('show.bs.modal', function() {
		clearForeignCard();
		setDataForeignCard();
		checkFieldsForeignCard();

		var hash = window.location.hash;
		if (hash && window.location.hash.indexOf('&openSettingForeignCard') !== -1) {
			window.location.hash = hash.replace(/&openSettingForeignCard/g, '');
		}
	});

	// Отслеживаем заполненность полей
	$(_settingForeignCardModal)
		.find('form')
		.on('input', '[type="text"], [type="number"]', function() {
			var $input = $(this);
			var value = $input.val();

			if (value.length > 0) {
				$input.removeClass('styled-input-empty');
				$input.parent().find('.error').remove();
			}

			checkFieldsForeignCard();
		});

	// Прослушиваем форму отправки доп. данных
	$('.js-setting-foreign-card')
		.off('submit')
		.on('submit', function(e) {
			e.preventDefault();

			var $modal = $(_settingForeignCardModal);
			var $btn = $modal.find('[type="submit"]');

			$modal.find('[type="text"], [type="number"]').removeClass('styled-input-empty');
			$modal.find('[type="text"], [type="number"]').parent().find('.error').remove();
			$btn.addClass('disabled').attr('disabled', 'disabled');

			var formData = $(this);
			$.post("/solar_foreign",
				formData.serialize(),
				function (response) {
					if (response.success) {
						document.location.reload(true);
					} else {
						if (response.data.errors) {
							$.each(response.data.errors, function(k, v) {
								var $input = $('[name="' + k + '"]');

								$input.addClass('styled-input-empty');
								$input.parent().append(
									tplErrorFieldsForeignCard(v)
								);
							});
						}
					}

					$btn.removeClass('disabled').removeAttr('disabled');
				}
			, "json");
		});
});

/**
 * Шаблон с выводом ошибки
 *
 * @param errorMsg
 */
function tplErrorFieldsForeignCard(errorMsg) {
	if (!errorMsg) {
		return;
	}

	return '<span class="error kwork-icon icon-custom-help tooltipster" data-tooltip-destroy="true" data-tooltip-text="' + errorMsg + '"></span>';
}

