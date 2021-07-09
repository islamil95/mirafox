/**
 * Класс EditMessageTrack редактирует сообщение в чате заказа
 * редактирование происходит в элементе текстового сообщения, 
 * вместо текста появляется форма для редактирования
 *
 * @param {number} orderId - Id заказа.
 * @param {number} messageId - Id сообщения.
 */
EditMessageTrack = function (orderId, messageId) {	
	var _self = this;
	
	this.orderId = orderId;
	this.messageId = messageId;
	
	// Для хранения редактируемого сообщения
	this.oldMessage = '';
	
	// Для хранения начального набора файлов
	this.oldLoadFileList = [];
	
	// Таймер эможи, что бы запускать замену кода на изображение раз в секунду
	this.emojiShortcodeTimer = undefined;
	
	// Редерим кнопку для выбора и вставки эможи
	// TODO: Проверка на isFocusGroupMember на время тестирования. После - удалить содержимое проверки
	if (config.track.isFocusGroupMember) {
		var resEmojiBtn = Vue.compile('<emoji-btn positionPanel="left" @change="onChange"></emoji-btn>')
		this.emojiBtnObject = new Vue({
			render: resEmojiBtn.render,
			methods: {
				onChange(code) {
					_self.insertEmoji(code);
				}
			}
		}).$mount();
	}
	
	// Загрузчик файлов
	var fileUploader = Vue.extend(fileUploaderComponent);
	this.fileUploaderObject = new fileUploader({
		propsData: {
			name: 'desctop-'+this.messageId,
			limitCount: config.track.fileMaxCount || config.files.maxCount,
			limitSize: config.track.fileMaxSize || config.files.maxSize,
			withMiniature: true,
			secondUserId: parseInt(window.Track.opponentId),
		}
	}).$mount();
	
	this.lastScrollbarWidth = 0;
	
	// ссылка на jquery объект сообщения
	this.updateElement();
	
	this.textBox = undefined;
	this.emojiBtn = undefined;
	this.confirmBtn = undefined;
	this.cancelBtn = undefined;

	// блок цитируемого сообщения
	this.quoteBlock = undefined;
	// изначальный id цитируемого сообщения, для хранения
	this.quoteOldValue = undefined;
	// кнопка "удаление" цитируемого сообщения
	this.quoteRemove = undefined;

	this.isBeginEdit = false;
};

/**
 * Отслеживаем события: подтверждения, отмены, изменения списка файлов, изменения текста, удаление цитаты
 * 
 */
EditMessageTrack.prototype._setEvents = function () {
	var _self = this;		
	
	// TODO:7584 Удалить после тестирования
	_self.textBox.on('input', function() {
		if (_self.accessEdit()) {
			_self.confirmBtn.show();
		} else {
			_self.confirmBtn.hide();
		}
	});
	
	_self.textBox.on('tbwchange', function() {
		if (_self.accessEdit()) {
			_self.confirmBtn.show();
		} else {
			_self.confirmBtn.hide();
		}
		// TODO:7584 Проверка на isFocusGroupMember на время тестирования. После - убрать проверку
		if (config.track.isFocusGroupMember) {	
			_self.updateScrollbarPadding();
		}
	});
	
	// TODO:7584 Проверка на isFocusGroupMember на время тестирования. После - убрать проверку
	if (config.track.isFocusGroupMember) {
		this.textBox.on('tbwinit', function() {
			_self.updateScrollbarPadding();
		});
	}
	
	window.bus.$on('fileUploader-desctop-' + this.messageId + '.change', function() {
		if (_self.accessEdit()) {
			_self.confirmBtn.show();
		} else {
			_self.confirmBtn.hide();
		}
	});
	// "удаление" цитаты
	_self.quoteRemove.on('click', function (e) {
		e.preventDefault();
		_self.quoteBlock.hide();
		_self.quoteBlock.data('quote-id', 0);
		_self.confirmBtn.show();
		e.stopPropagation();

	});
	_self.confirmBtn.on("click", function() {
		_self.send();
		
	});
	_self.cancelBtn.on("click", function() {
		_self.endEdit();
		
	});
};

/**
 * Удаляем обработчики событий
 * 
 */
EditMessageTrack.prototype._offEvents = function () {
	this.textBox.off("input");
	window.bus.$off('fileUploader-desctop-' + this.messageId + '.change');
	this.confirmBtn.off("click");
	this.cancelBtn.off("click");
	this.quoteRemove.off("click");
};

/**
 * Проверка для отображения и скрытия кнопки подтверждения редактирования
 *
 */
EditMessageTrack.prototype.accessEdit = function () {
	var loadFileList = [];
	_.forEach(this.fileUploaderObject.files, function(v, k) {
		if (v.result != 'deleted') {
			loadFileList.push(v.file_id);
		}
	});
	var message = he.decode(this.textBox.val());
	// Если нет изменений или поля пусты
	if (
		(_.isEqual(this.oldLoadFileList, loadFileList) && this.oldMessage == message && this.quoteBlock.data('quote-id') == this.quoteOldValue)
		|| (loadFileList.length < 1 && message == '')
	) {
		return false;
	}
	return true;
};


/**
 * Функция отправляет на сервер отредактированное сообщение
 * 
 */
EditMessageTrack.prototype.send = function() {
	var _self = this;
	// Если изменений нет, запрещаем отправку изменений
	if(!_self.accessEdit()) {
		return false;
	}
	// Заполнить объект данными для отправки на сервер
	var formData = new FormData();
	var textBoxVal = _self.textBox.val();
	if (_self.textBox.hasClass('trumbowyg-textarea')) {
		textBoxVal = window.emojiReplacements.preSubmitMessage(textBoxVal);
	}
	formData.append("action", "edit");
	formData.append("trackId", _self.messageId);

	var message = _self.textBox.val();
	message = message.replace(/(\r)?\n/gm, '\r\n');

	formData.append("message", textBoxVal);
	
	formData.append("orderId", _self.orderId);
	formData.append("is_ajax", true);
	formData.append("need_ajax", true);
	formData.append("quoteId", _self.quoteBlock.data('quote-id'));
	// Заполнить список файлов
	$.each(this.fileUploaderObject.files, function(k, v) {
		if (v.result == 'success') {
			formData.append('conversations-files-edit[new][]', v.file_id);
		} else if (v.result == 'deleted') {
			formData.append('conversations-files-edit[delete][]', v.file_id);
		}
	});
		
	axios({
		method: "post",
		url: "/track/action/edit",
		data: formData,
		config: {
			headers: {
				"Content-Type": "multipart/form-data",
			}
		}
	}).then(function(response) {
		// Отключить режим редактирования
		_self.endEdit();
		var result = response.data || {};
		
		if (result.sidebarFiles !== null) {
			$('#sidebar-files-container').html(result.sidebarFiles);
		}

		// Обновление редактируемое сообщение
		if ('tracks' in result) {
			window.appTracks.$refs.trackList.applyContent(result.tracks, null, true);
		}
	});
};

EditMessageTrack.prototype.updateElement = function(message, files) {
	this.trackElement = $("#track-id-" + this.messageId);
};

/**
 * Функция включает режим редактирования
 * 
 * @param {number} message - текст сообщения.
 * @param {array} files - файлы сообщения.
 */
EditMessageTrack.prototype.beginEdit = function(message, files) {	
	this.isBeginEdit = true;
	
	var _self = this;
	
	this.updateElement();

	window.appTracks.$refs.trackList.setEdited(this.messageId, true);
	_self.trackElement.addClass("is-edit");
	
	// Скрываем область с текстом сообщения 
	_self.trackElement.find(".step-block-order__text").addClass('hidden');
	
	// Копируем форму в сообщения
	_self.trackElement.find(".step-block-order__wrap-edit-form").html($('.js-message-edit-form-template').html());
	
	// Изначальные файлы
	_self.oldLoadFileList = [];
	_.forEach(files, function(v, k) {
		_self.oldLoadFileList.push(v.id);
	});
	
	// Заполняем файлами
	_self.fileUploaderObject.clearFiles();
	_self.fileUploaderObject.applyFileList(files);
	
	// Показываем fileUploader
	_self.trackElement.find(".message-edit-files").append(_self.fileUploaderObject.$el);
	// Показываем emoji
	// TODO:7584 Проверка на isFocusGroupMember на время тестирования. После - убрать проверку
	if (config.track.isFocusGroupMember) {		
		_self.emojiBtn = _self.trackElement.find('.wrap-emoji-btn');
		_self.emojiBtn.append(_self.emojiBtnObject.$el);
	}
	
	// Текстовая область
	_self.textBox = _self.trackElement.find(".text-box");
	// TODO:7584 Проверка на isFocusGroupMember на время тестирования. После - удалить содержимое проверки else
	if (config.track.isFocusGroupMember) {
		_self.oldMessage = _self.messageFromTrumbowyg(message);	
		_self.textBox.val(_self.oldMessage);
		initEmojiTrumbowyg(_self.textBox);
	} else {	
		_self.oldMessage = he.decode(message);		
		_self.textBox.val(he.decode(message));
		
	}
	
	_self.confirmBtn = _self.trackElement.find(".message-confirm-edit");
	_self.cancelBtn = _self.trackElement.find(".message-cancel-edit");

	// блок цитируемого сообщения
	_self.quoteBlock = _self.trackElement.find('.js-message-quote');
	_self.quoteOldValue = _self.quoteBlock.data('quote-id');
	// отображает "удалить" цитируемое сообщение
	_self.quoteRemove = _self.trackElement.find('.js-message-quote-remove');
	_self.quoteRemove.show();
	
	// Регестрируем события
	_self._setEvents();
};

/**
 * Функция отключает режим редактирования
 * 
 */
EditMessageTrack.prototype.endEdit = function() {
	// Если помечено что мы в режиме редактирования
	if(this.isBeginEdit) {
		window.appTracks.$refs.trackList.setEdited(this.messageId, false);
		this.trackElement.removeClass("is-edit");
		
		this._offEvents();
		
		// Скрываем область с текстом сообщения 
		this.trackElement.find(".step-block-order__text").removeClass('hidden');
		
		// Очищаем файлы
		this.fileUploaderObject.clearFiles();
		
		// Убираем форму
		this.trackElement.find(".step-block-order__wrap-edit-form").html('');
		
		this.textBox = undefined;
		this.emojiBtn = undefined;
		this.confirmBtn = undefined;
		this.cancelBtn = undefined;

		this.quoteBlock.show();
		this.quoteBlock.data('quote-id', this.quoteOldValue);
		this.quoteRemove.hide();
		this.quoteBlock = undefined;
		this.quoteOldValue = undefined;
		this.quoteRemove = undefined;
		this.fileUploaderObject.$destroy();

		this.isBeginEdit = false;
		
		this.lastScrollbarWidth = 0;
	}
	
};

/**
 * Функция вставляет эможи в текстовую область
 * 
 * @param {string} code - код эможи.
 */
EditMessageTrack.prototype.insertEmoji = function(code) {	
	// Находится в track.js
	emojiInsertToTrumbowyg(this.textBox, code);
};

/**
 * Обрабатываем сообщение для вставки в редактор, вместо br импользуем строку обернутую в div
 * 
 * @param {string} message - текс сообщения
 */
EditMessageTrack.prototype.messageFromTrumbowyg = function(message) {
	var res = '';
	var resArr = message.replace(/\r?\n/g, '<br>').split('<br>');
	for(var i=0; i<resArr.length; i++) {
		if(resArr[i] != '') {
			res += '<div>' + resArr[i] + '</div>';
		} else {
			res += '<div><br/></div>';
		}
	}
	return res;
};

/**
 * Вычисляем позицию кнопки эможи с полем когда есть скролл и когда нет
 * 
 */
EditMessageTrack.prototype.updateScrollbarPadding = function() {
	var $desktopMessageField = this.textBox.siblings(".trumbowyg-editor");
	var scrollbarWidth = $desktopMessageField[0].offsetWidth - $desktopMessageField[0].clientWidth;
	if (scrollbarWidth == this.lastScrollbarWidth) {
		return;
	}
	this.lastScrollbarWidth = scrollbarWidth;
	
	$desktopMessageField.css('padding-right', (scrollbarWidth + 55) + ' !important');	
	this.emojiBtn.css('right', scrollbarWidth + 6);
};
