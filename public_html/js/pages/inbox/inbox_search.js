var InboxSearchModule = (function () {
    var _selectors = {
        block:  '.inbox-search',
        form:   '.inbox-search-form',
        input:  '.inbox-search-input',
        reset:  '.inbox-search-reset',
        submit: '.inbox-search-submit',
        notMessageText: '.not_messages-text',
        listBlock: '.message_list-block',
    };

    var $block  = $();
    var $form   = $();
    var $input  = $();
    var $reset  = $();
    var $submit = $();

    var _search = false;

    function _init() {
        $block  = $(_selectors.block);
        $form   = $(_selectors.form);
        $input  = $(_selectors.input);
        $reset  = $(_selectors.reset);
        $submit = $(_selectors.submit);

        if ($input.val() !== '') {
            $reset.show();
        }

        $input.keyup(_keyup);

        $form.submit(_submit);

        $reset.click(_reset);
    }

    function _keyup(event) {
        var query = $input.val();
        $reset.toggle(query !== '');
    }

    function _submit(event) {
        event.preventDefault();

        var query = $input.val();

        if (query !== '') {
            var params = { query: query };
            _getResults(params);
        }
    }

    function _reset() {
        $input.val('');
        $reset.hide();

        if (_search) {
            var url = location.protocol + '//' + location.host + location.pathname;
            location.assign(url);
        }
    }

    function _getResults(params) {
        var url = location.protocol + '//' + location.host + location.pathname;

        params.search = 1;
        
        $.post(url, params, function (response) {
            if (response.success) {
                _search = true;

                $(_selectors.notMessageText).remove();
                $(_selectors.listBlock).html(response.html);

                _initAjaxLinks(params.query);
            } else {
                alert('Ошибка');
            }
        }, 'json');
    }

    function _initAjaxLinks(query) {
        var $sort = $(_selectors.listBlock + ' .table-style_sort-up')
                 .add(_selectors.listBlock + ' .table-style_sort-down');
        $sort.click(function (event) {
            event.preventDefault();

            var href = $(this).prop('href');
            var url = parseURL(href);

            var params = {
                query: query,
                a: typeof url.searchObject.a !== 'undefined' ? url.searchObject.a : 0,
            };

            _getResults(params);
        });

        $(_selectors.listBlock + ' .paging a').click(function (event) {
            event.preventDefault();

            var href = $(this).prop('href');
            var url = parseURL(href);

            _getResults(url.searchObject);
        });
    }

    function parseURL(url) {
        var parser = document.createElement('a'),
            searchObject = {},
            queries, split, i;

        // Let the browser do the work
        parser.href = url;

        // Convert query string to object
        queries = parser.search.replace(/^\?/, '').split('&');

        for (i = 0; i < queries.length; i++) {
            split = queries[i].split('=');
            searchObject[split[0]] = split[1];
        }

        return {
            protocol: parser.protocol,
            host: parser.host,
            hostname: parser.hostname,
            port: parser.port,
            pathname: parser.pathname,
            search: parser.search,
            searchObject: searchObject,
            hash: parser.hash
        };
    }

    return {
        init: _init,
    };
})();

$(function () {
    InboxSearchModule.init();
});
