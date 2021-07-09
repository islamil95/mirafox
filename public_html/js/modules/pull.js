var PULL_EVENT_NOTIFY = 'notify';
var PULL_EVENT_DISCONNECT = 'disconnect';
var PULL_EVENT_NEW_INBOX = 'new_inbox';
var PULL_EVENT_READ_INBOX = 'read_inbox';
var PULL_EVENT_IS_ONLINE = 'is_online';
var PULL_EVENT_UPDATE_CART = 'update_cart';
var PULL_EVENT_INBOX_MESSAGE_EDIT = 'inbox_message_edit';
var PULL_EVENT_INBOX_MESSAGE_DELETE = 'inbox_message_delete';
var PULL_EVENT_NEW_ORDER_TRACK = 'new_order_track';
var PULL_EVENT_REFRESH_TRACK = 'refresh_track';
var PULL_EVENT_REMOVE_DRAFT = 'remove_draft';
var PULL_EVENT_READ_TRACK = 'read_track';
var PULL_EVENT_ORDER_UPDATED = 'order_updated';
var PULL_EVENT_TRACK_CHANGED = 'track_changed';
var PULL_EVENT_IS_TYPING = 'is_typing';
var PULL_EVENT_DIALOG_UPDATED = 'dialog_updated';
var PULL_EVENT_INBOX_SENT = 'inbox_sent';
var PULL_EVENT_FILE_MINIATURE_CREATED = 'file_miniature_created';
var PULL_EVENT_MESSAGE_SUBMIT_MODE_CHANGED = 'message_submit_mode_changed';

/**
 * @module
 *
 * @type {{start, on, onPub, off, extendWatch, connect, disconnect, addOnlineUserChannel}}
 */
var PullModule = (function (document, window) {

    var LOCAL_STORAGE_KEY = 'pull-channel';

    var USER_ONLINE_CHANNEL_PREFIX = 'online_user_';

    var EXPIRE_MINUTES = 30;

    var _options;

    /**
     * ID таймаут для обновления срока работы канала
     * @type {number}
     * @private
     */
    var _expireDateTimeout = 0;

    /**
     * ID таймаут для реконнекта при ошибке
     * @type {number}
     * @private
     */
    var _reconnectTimeout = 0;

    /**
     *
     * @param {PushStream}
     * @private
     */
    var _pushStream;

    /**
     * Имена пользовательских каналов
     * @type {object}
     * @private
     */
    var _onlineUserChannels = {};

    var _initPushStreamModule = function () {
        _pushStream = new PushStream({
            host: _options.host,
            port: window.location.port,
            modes: "websocket|longpolling",
            useSSL: window.location.protocol === "https:",
            autoReconnect: false,
        });

        _pushStream.onerror = function (error) {
            clearTimeout(_reconnectTimeout);
            _reconnectTimeout = setTimeout(function () {
                _connect();
            }, 2000);
        };

        _pushStream.onmessage = _onPushStreamReceived;

        _pushStream.addChannel('public');
        _pushStream.addChannel(_options.channelId);
        _connect();
    };

    /**
     * Инициализация модуля
     *
     * @param {Object} options
     * @param {string} options.host         Хост
     * @param {string} options.channelId    ID канала
     *
     * @private
     */
    var _start = function (options) {
        _options = options;

        if (!_options.channelId.length) {
            return false;
        }

        _initPushStreamModule();

        _updateStorageChannelId(_options.channelId);
        _setEvents();
    };

    /**
     * Включить связь с сервером
     * @private
     */
    var _connect = function () {
        _pushStream.connect();
    };
    var _disconnect = function () {
        _pushStream.disconnect();
        clearTimeout(_expireDateTimeout);
    };

    /**
     * Установка базовых событий
     *
     * @private
     */
    var _setEvents = function () {
        $(window).on('storage', function (event) {
            if (event.originalEvent.key === LOCAL_STORAGE_KEY) {
                var channelData = JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY));
                _setNewChannel(channelData.channel);
            }
        });

        _on(PULL_EVENT_DISCONNECT, function () {
            _disconnect();
        });
    };

    /**
     * Получение данных из стрима (COMET)
     *
     * @param {String} response
     * @param id
     * @param channel
     * @private
     */
    var _onPushStreamReceived = function (response, id, channel) {
        /**
         * @param {Object} responseJson
         * @param {String} responseJson.event
         * @param {*} responseJson.data
         *
         */
        var responseJson = JSON.parse(response);
        _emit(responseJson.event, responseJson.data, channel === 'public');
    };

    /**
     * Повесить обработчик на событие
     *
     * @param {String} eventName название события
     * @param {Object} callback
     *
     * @private
     */
    var _on = function (eventName, callback) {
        $(document).bind(_generateEventName(eventName), function (e) {
            var args = [];
            Array.prototype.push.apply(args, arguments);
            args.shift();
            callback.apply(document, args);
        });
    };

    var _onPublic = function (eventName, callback) {
        $(document).bind(_generateEventName(eventName, true), callback);
    };

    /**
     * Удалить подписку на событие
     *
     * @param {String} eventName название события
     * @param {Object} callback
     * @param {boolean} [isPublic] публичный канал
     *
     * @private
     */
    var _off = function (eventName, callback, isPublic) {
        $(document).unbind(_generateEventName(eventName, isPublic), callback);
    };

    /**
     * Затриггерить событие
     *
     * @param {String} eventName название события
     * @param {*} params параметры события
     * @param {boolean} [isPublic] публичный канал
     *
     * @private
     */
    var _emit = function (eventName, params, isPublic) {
        $(document).trigger(_generateEventName(eventName, isPublic), params);
    };

    /**
     * Сгенерировать имя события для пуш-уведомлений
     *
     * @param {String} eventName название события
     * @param {boolean} [isPub] публичный канал
     * @returns {string}
     *
     * @private
     */
    var _generateEventName = function (eventName, isPub) {
        if (isPub) {
            return eventName + '.pub.pull';
        } else {
            return eventName + '.pull';
        }
    };

    /**
     * Обновить канал
     * @param channelId
     * @private
     */
    var _updateStorageChannelId = function (channelId) {
        var now = new Date();
        now.setMinutes(now.getMinutes() + EXPIRE_MINUTES);

        var data = {
            channel: channelId,
            expired: now.getTime()
        };

        localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(data));

        _setNewChannel(data.channel);
    };

    /**
     * установить канал
     *
     * @param channelId
     * @private
     */
    var _setNewChannel = function (channelId) {
        if (_options.channelId === channelId) {
            return;
        }

        if (_options.channelId) {
            _pushStream.removeChannel(_options.channelId);
        }

        _options.channelId = channelId;
        _pushStream.addChannel(_options.channelId);
    };

    var addOnlineUserChannel = function (userId) {
        userId = userId ^ 0;
        var channelId = USER_ONLINE_CHANNEL_PREFIX + userId;

        if (_onlineUserChannels[channelId]) {
            return;
        }

        _disconnect();

        _pushStream.addChannel(channelId);
        _onlineUserChannels[channelId] = true;

        _connect();
    };

    return {
        start: _start,
        on: _on,
        onPub: _onPublic,
        off: _off,
        connect: _connect,
        disconnect: _disconnect,
        addOnlineUserChannel: addOnlineUserChannel
    }
})(document, window);
