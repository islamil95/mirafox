var SET_READ_INTERVAL = 300;
var SET_ITEMS_TO_READ_DURATION = 100;
var SET_ITEM_TO_READ_DURATION = 1500;

var TITLE_UPDATE_INTERVAL = 1000;

// @todo: это по сути набросок универсального механизма прочитанности.
// Базовые вещи оставить здесь, добавить настройки, отнаследоваться в треках и личке.
// В более идеальном мире - сделать механизм прочтения универсальным от сервера до клиента,
// чтобы прочитанность ставилась через один метод для любых типов сообщений-треков.
// Сейчас этого ничего делать не стал, потому что личку надо перерабатывать - легко ломается.
function ReadTrackService(options) {
	this.lastItemId = 0;

	this.unreadCount = 0;
	this.attentionTimer = 0;
	this.onlineTimer = 0;
	this.itemsToRead = [];
	this.title = $('title').text();

	this.orderId = 0;
	this.opponentId = 0;

	this.itemIdPrefix = options.itemIdPrefix;

	var _self = this;

	$(document).ready(function () {
		_self.setEvents();
	});

	$(window).load(function () {
		_self.setUnreadPageCount();
		if (_self.unreadCount > 0) {
			_self.setTitleAttention();
			_self.updateMessagesCircleCount();
		}
	});
}

/**
 * Инициализация.
 *
 * @param options
 */
ReadTrackService.prototype.init = function (options) {
	this.orderId = options.orderId;
	this.opponentId = options.opponentId;
};

/**
 * Обновить занчок новых сообщений (new-messages-circle)
 */
ReadTrackService.prototype.updateMessagesCircleCount = function () {
	var unreadMessages = $('.unread:not(.out)');
	var messagesCount = unreadMessages.length;

	unreadMessages.each(function() {
		var $item = $(this);

		if ($item.is(':within-viewport-bottom')) {
			messagesCount--;
		}
	});

	window.bus.$emit('updateMessagesCount', messagesCount);
};

/**
 * Установить уведомление в заголовке окна браузера о количестве новых сообщений
 * @private
 */
ReadTrackService.prototype.setTitleAttention = function () {
	var _self = this;

	if (_self.attentionTimer) {
		return;
	}

	var i = 0;
	_self.attentionTimer = setInterval(function () {
		if (i % 2) {
			_self.updateTitleUnreadCount();
		}
		else {
			$('title').text('*** ' + _self.title);
		}
		i++;
	}, TITLE_UPDATE_INTERVAL);
};

/**
 * Убрать уведомление в заголовке окна браузера о количестве новых сообщений
 * @private
 */
ReadTrackService.prototype.clearTitleAttention = function () {
	var _self = this;
	if (_self.attentionTimer) {
		clearInterval(_self.attentionTimer);
		_self.attentionTimer = 0;
	}
	_self.updateTitleUnreadCount();
};

/**
 * Посчитать количество непрочитанных сообщений
 */
ReadTrackService.prototype.setUnreadPageCount = function () {
	this.unreadCount = $('.unread:not(.out)').length;
};

/**
 * Обновить количество непрочитанных сообщений в заголовке окна браузера
 */
ReadTrackService.prototype.updateTitleUnreadCount = function () {
	var newTitle = this.title;

	this.setUnreadPageCount();

	if (this.unreadCount) {
		newTitle = '[' + this.unreadCount + '] ' + newTitle;
	}

	$('title').text(newTitle);
	if (!this.unreadCount) {
		clearInterval(this.attentionTimer);
	}
};

/**
 * Название дикое, потому что метод должен собрать сообщения для прочтения
 * с задержкой собирания для каждого отдельного сообщения.
 */
ReadTrackService.prototype.setUnreadItemTimeoutsToGarbage = function () {
	var _self = this;

	if (_self.unreadCount === 0) {
		return;
	}

	_self.clearTitleAttention();

	$('.unread:not(.out)').each(function() {
		var $item = $(this);

		if ($item.is(':within-viewport-bottom')) {
			if ($item.data('readTimeout')) {
				return;
			}

			$item.data('readTimeout', setTimeout(function () {
				_self.itemsToRead.push(_self.getItemId($item));
			}, SET_ITEM_TO_READ_DURATION));
		}
		else {
			_self._resetItemTimer($item);
		}
	});
};

/**
 * Отмечает сообщение на странице прочитанным.
 *
 * @param $item
 * @private
 */
ReadTrackService.prototype._setReadItem = function($item, itemId) {
	var id = $item.data('trackId');
	if (id) {
		window.appTracks.$refs.trackList.setItemRead(id);
	}
	$item.removeClass('unread');
};

/**
 * Отметить исходящие сообщения прочитанными
 *
 * @param {Array} itemIds id прочитанных сообщений
 * @private
 */
ReadTrackService.prototype._setReadOutItems = function (itemIds) {
	var _self = this;

	if (itemIds) {
		itemIds = itemIds.map(function (val) {
			return val ^ 0;
		});
	}

	$('.unread.out').each(function () {
		var $item = $(this);
		var itemId = _self.getItemId($item);
		if (!itemIds || itemIds.indexOf(itemId) !== -1) {
			if($item.is(".moder")) {
				$item.find(".message-icons > *:not(.moder-remove)").hide();
			} else {
				$item.find(".message-icons").hide();
			}
			_self._setReadItem($item, itemId);
		}
	});
};

/**
 * Пометить сообщения прочитанными.
 *
 * @param response
 * @private
 */
ReadTrackService.prototype._onReadItems = function (response) {
	if (this.opponentId != response.fromUserId) {
		return;
	}

	this._setReadOutItems(response.itemIds);
};

/**
 * Сбрасывает таймер задержки прочтения для отдельного сообщения.
 *
 * @param $item
 * @private
 */
ReadTrackService.prototype._resetItemTimer = function($item) {
	clearTimeout($item.data('readTimeout') ^ 0);
	$item.data('readTimeout', 0);
};

/**
 * Отправить запрос на чтение сообщений
 * @private
 */
ReadTrackService.prototype.sendReadItems = function () {
	var params = {
		itemIds: this.itemsToRead,
		orderId: this.orderId
	};

	Api.request('track/readtracks', params, function () {
	});
	if (config && config.track && config.track.isFocusGroupMember) {
		//and this goes to TrackManager
		document.dispatchEvent(new CustomEvent('track-manager-handle', {
			detail: {
				action: "removeUnreadLines",
				data: {}
			}
		}));
	}
	this.itemsToRead = [];
};

/**
 * Возвращает id сообщения.
 *
 * @param $item
 * @returns {number}
 */
ReadTrackService.prototype.getItemId = function($item) {
	return $item.data('track-id') ^ 0;
};

/**
 * Устанавливает обработчики событий.
 */
ReadTrackService.prototype.setEvents = function () {
	var _self = this;
	if (PULL_MODULE_ENABLE) {
		PullModule.on(PULL_EVENT_READ_TRACK, function (response) {
			_self._onReadItems(response);
		});
	}

	$('.js-alt-send').on('focus click', function () {
		_self.clearTitleAttention();
	});

	var throttleSetMessagesToRead = Utils.throttle(function() {
		_self.setUnreadItemTimeoutsToGarbage();
	}, SET_ITEMS_TO_READ_DURATION, false, true);

	throttleSetMessagesToRead();

	$(window).bind("scroll focus", function () {
		_self.updateMessagesCircleCount();

		if (Utils.isActiveWindow()) {
			if (_self.unreadCount > 0) {
				Utils.throttle(function() {
					_self.clearTitleAttention();
				}, SET_ITEMS_TO_READ_DURATION);
			}

			_self.clearTitleAttention();
			throttleSetMessagesToRead();
		}
	});

	$(document).on("click", function () {
		_self.clearTitleAttention();
	});

	$(window).load(function () {
		setInterval(function () {
			if (!_self.itemsToRead.length || !Utils.isActiveWindow()) {
				return;
			}

			var maxId = Math.max.apply(null, _self.itemsToRead);
			$('.unread:not(.out):not(.moder)').each(function () {
				var $item = $(this);
				var itemId = _self.getItemId($item);
				if (itemId <= maxId) {
					_self._setReadItem($item);
					_self._resetItemTimer($item);
				}
			});

			$.each(_self.itemsToRead, function (i, itemId) {
				_self._setReadItem($(_self.itemIdPrefix + itemId));
			});

			_self.setUnreadPageCount();
			_self.updateTitleUnreadCount();
			_self.sendReadItems();

			_self.itemsToRead = [];
		}, SET_READ_INTERVAL);
	});
};
