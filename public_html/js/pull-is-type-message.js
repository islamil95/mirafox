/**
 * Класс PullIsTypeMessage информирует пользователя о вводе сообщение собеседником.
 * и отправляет уведомление на сервер
 *
 * @param {number} selectorTextBox - селектор области для ввода текста.
 * @param {number} selectorInfoTypeMessage - селектор иконки информации о печати.
 * @param {number} userId - Id собеседника.
 * @param {number} orderId - Id заказа.
 */
PullIsTypeMessage = function (selectorTextBox, selectorInfoTypeMessage, userId, orderId) {
	this.selectorTextBox = selectorTextBox;
	this.selectorInfoTypeMessage = selectorInfoTypeMessage;
	//Таймер для скрытия иконки печати
	this.timer;	
	
	this.userId = userId;
	this.orderId = (orderId === undefined) ? 0 : orderId;
	
	//Для подсчета интервала между началом ввода сообщения и концом
	this.intervalType = {
		begin: 0,
		end: 0
	};
	
	//Данные, которые отправим на сервер, при вводе сообщения
	this.params = {
		recipientId: userId,
	}
	this.params.orderId = (orderId === undefined) ? 0 : orderId;
	
	this.init();
};
/**
 * Отслеживаем, уведомления с сервера и отслеживаем событие набора текста
 * 
 */
PullIsTypeMessage.prototype._setEvents = function () {
	var _self = this;
	if (PULL_MODULE_ENABLE) {
		PullModule.on(PULL_EVENT_IS_TYPING, function(data) {
			_self._pullIsTyping(data)
		});
	}	
	
	// Используем событие tbwchange так как в track.js искуственно вызывается событие input
	$(document).on("keypress", _self.selectorTextBox, function() {
		_self._typeText();
	});
};

/**
 * Показываем икноку, что собеседник печатает
 * 
 * @param {number} data.userId - Id собеседника.
 * @param {number} data.orderId - Id заказа.
 */
PullIsTypeMessage.prototype._pullIsTyping = function (data) {
	var _self = this;
	//Проверяем является ли событие пользователю
	if(data.userId !== _self.userId) {
		return false;
	}
	if(data.orderId !== _self.orderId) {
		return false;
	}
	//Показывает иконку печати
	$(_self.selectorInfoTypeMessage).show();
	//Очищаем таймер скрытия икноки
	clearTimeout(_self.timer);
	//Запускаем таймер заново
	_self.timer = setTimeout(function() { _self._timerEnd(); }, 10000);
};

/**
 * Отправляем информацию на сервер, что пользователь печатает
 * 
 */
PullIsTypeMessage.prototype._typeText = function () {
	//Фиксируем начальное время
	if(this.intervalType.begin === 0) {
		this.intervalType.begin = new Date();
	}
	//Фиксируем конечное время
	this.intervalType.end = new Date();
	//Если с начала набора тексто прошло 4 секунды отправляем на сервер
	if(this.intervalType.end - this.intervalType.begin > 4000) {
		//Фиксируем начальное время
		this.intervalType.begin = new Date();
		Api.request('inbox/typing', this.params, function() {});
	}
};

/**
 * Скрываем иконку печати, когда таймер истекает
 * 
 */
PullIsTypeMessage.prototype._timerEnd = function () {
	$(this.selectorInfoTypeMessage).hide();
};

/**
 * Ждем полной загрузки страницы, после регистрируем события
 * 
 */
PullIsTypeMessage.prototype.init = function () {
	var _self = this;
	$(document).ready(function () {
		_self._setEvents();
	});
};