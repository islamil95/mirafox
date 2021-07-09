var InboxListModule = (function() {

    var _selectors = {
        notMessageText: '.not_messages-text',
        listBlock: '.message_list-block',
        readButtons: '.table-inbox-read',
    };

    var _putList = function () {
        var url = _getUrl({action: 'getList'});
        $.get(url, function (response) {
            if(response.success) {
                $(_selectors.notMessageText).remove();
                $(_selectors.listBlock).html(response.html);
            }
        }, 'json');
    };

    var _onNewInbox = function () {
        _putList();
    };

    /**
     * Обработчик события изменения своего диалога
     * @private
     */
    var _onDialogUpdated = function () {
        _putList();
    };

    /**
     * Обработчик события изменения сообщения собеседником
     * @private
     */
    var _onMessageEdit = function (event) {
        var inboxId = parseInt(event.mid);
        if (inboxId) {
            var $row = $('.message_list-block tr[data-last-message-id="' + inboxId + '"]');
            if ($row.length) {
                _putList();
            }
        }
    };

    var _setEvents = function() {
        if (_isFirstPage()) { // Обновлять список только на первой странице
            $(document).ready(function () {
				if (PULL_MODULE_ENABLE) {
                    PullModule.on(PULL_EVENT_NEW_INBOX, _onNewInbox);
                    PullModule.on(PULL_EVENT_DIALOG_UPDATED, _onDialogUpdated);
                    PullModule.on(PULL_EVENT_INBOX_MESSAGE_EDIT, _onMessageEdit);
                    // При удалении сообщения придет событие dialog_updated
                }
            });
        }

        $(document).on('click', _selectors.readButtons, _readButtonClick);
    };

    var _readButtonClick = function(event) {
        var $button = $(this);

        var unread = $button.hasClass('unread');

        var params = {
            user_id: $button.data('userId'),
        };

        var endpoint = unread ? "/api/inbox/readdialog" : "/api/inbox/unreaddialog";

        $.post(endpoint, params, function (response) {
            if (response.success) {
                var $containers = $button.closest('tr').find('.m-table-inbox_info a, .m-table-inbox_text, td:last-child');

                if (unread) {
                    $containers.removeClass('bold');
                    $button.removeClass('unread');
                    $button.tooltipster('content', $button.data('read'));
                } else {
                    $containers.addClass('bold');
                    $button.addClass('unread');
                    $button.tooltipster('content', $button.data('unread'));
                }
            } else if (response.error) {
                alert(response.error);
            } else {
                alert('Ошибка');
            }
        }, 'json');
    }

    var isset = function (obj) {
        if (typeof (obj) !== 'undefined') return true;
        return false;
    };

    var _isFirstPage = function () {
        var $paging = $('.paging');
        if (!$paging.length) {
            return true;
        }

        return $($paging[0]).find('li:first-child a').hasClass('active');
    };

    var _getUrl = function (values) {
        if (!isset(values))
            values = {};
        var url = parse_url();
        for (var key in values) {
            if (values[key] == '') {
                delete url['args'][key];
            } else {
                url['args'][key] = values[key];
            }
        }

        var args = [];
        for (var key in url['args']) {
            if (typeof url['args'][key] == 'string') {
                args.push(key + '=' + encodeURIComponent(url['args'][key]));
            } else {
                for (var i = 0; i < url['args'][key].length; i++) {
                    args.push(key + '=' + encodeURIComponent(url['args'][key]));
                }
            }
        }

        return args.length > 0 ? url['host'] + '?' + args.join('&') : url['host'];
    };

    var parse_url = function (){
        // parse href link, returns object url { 'host':'', 'args':{} }
        // arguments is the assoc array key:value like { 'sortby':'price' }
        // multiple args like checkbox are set { 'ad_type': ['0', '1', '2'] }

        var url = {host: '', args: {}};
        var args = window.location.href.replace(/#.*/g, '').split('?');
        var params = isset(args[1]) ? args[1].split('&') : [];
        url['host'] = args[0];
        for (var i = 0; i < params.length; i++) {
            args = params[i].split('=');
            key = args[0];
            val = isset(args[1]) ? args[1] : '';
            if (key == '' || val == '')
                continue;
            // for multiple values like checkboxes
            if (isset(url['args'][key])) {
                if (typeof url['args'][key] == 'string') {
                    url['args'][key] = [url['args'][key], val];
                } else {
                    url['args'][key].push(val);
                }
            } else {
                url['args'][key] = val;
            }
        }

        return url;
    };

    return {
        init: function() {
            _setEvents();
        }
    };
})();

InboxListModule.init();
