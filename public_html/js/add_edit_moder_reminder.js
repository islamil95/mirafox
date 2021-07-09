var AddEditModerReminderModule = (function () { // Добавление\редактирование напоминалки
	var $ = jQuery;

	var _selectors = {
		onAddLink: '.add_moder_reminder_link-js', // Открыть попап на добавление
		onEditLink: '.edit_moder_reminder_link-js', // Открыть попап на изменение
		onDeleteLink: '.delete_moder_reminder_link-js', // Удалить напоминалку
		item: '.reminder_item-js', // Контейнер одной напоминалки (содержит необходимые data атрибуты)
		save: '.save-reminder-js' // Контрол сохранения напоминалки
	};

	var _inputContainers = {
		type: '.reminder_type-js',
		text: '.reminder_text-js',
		reminderId: '.reminder_id-js',
		kworkId: '.reminder_kwork-js',
		entityId: '.reminder_entity-js',
		htype: '.reminder_htype-js'
	};

	var _kworkId;

	var _showAddPopup = function () {
		_showPopup(
			'/moder/kwork/reminder/popup',
			{kwork_id: _kworkId},
			'Поставить напоминание', this);
	};

	var _showEditPopup = function () {
		var $row = $(this).closest(_selectors.item);
		var id = $row.data('reminderId');

		_showPopup(
			'/moder/kwork/reminder/edit',
			{reminder_id: id},
			'Редактировать напоминалку', this);
	};

	var _showPopup = function (url, params, title, obj) {
		$.post(url,
			params,
			function (response) {
				if (response.success) {
					var options = {
						title: title,
						content: response.body,
						scrollable: false
					};
					showPopup(options, true);
					_setPopupEvents();
				} else {
					alert(response.message);
				}
			}, 'json');
	};

	var _save = function () {
		var params = {};

		params.reminder_id = $(_inputContainers.reminderId + ' input').val();
		params.kwork_id = $(_inputContainers.kworkId + ' input').val();
		params.entity_id = $(_inputContainers.entityId + ' input').val();

		params.type = $(_inputContainers.htype + ' input').val();
		if (!params.type) {
			params.type = $(_inputContainers.type + ' input:checked').val();
		}

		params.text = $(_inputContainers.text + ' textarea').val();

		$.post('/moder/kwork/reminder/save', params,
			function (response) {
				console.log("save ");
				console.log(response);
				if (response.success) {
					location.reload();
				} else {
					alert(response.message);
				}
			}, 'json');
	};

	var _delete = function () {
		var $row = $(this).closest(_selectors.item);
		var id = $row.data('reminderId');

		if (!confirm("Удалить напоминалку?")) {
			return false;
		}

		var params = {
			reminder_id: id
		};

		$.post('/moder/kwork/reminder/delete',
			params,
			function (response) {
				if (response.success) {
					location.reload();
				} else {
					alert(response.message);
				}
			}, 'json');
	};

	/**
	 * События до инициализации попапа
	 * @private
	 */
	var _setEvents = function () {
		$(_selectors.onAddLink).click(_showAddPopup);
		$(_selectors.onEditLink).click(_showEditPopup);
		$(_selectors.onDeleteLink).click(_delete);
	};

	/**
	 * События после инициализации попапа
	 * @private
	 */
	var _setPopupEvents = function () {
		$(_selectors.save).click(_save);
	};

	return {
		init: function (options) {
			_kworkId = options.kworkId;
			_setEvents();
		}
	};
})();