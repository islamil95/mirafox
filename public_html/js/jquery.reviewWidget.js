(function ($) {
	var _getType = function($parent){
		return $parent.data('params').type;
	};

	var _showNextButton = function($parent){
        var _params = $parent.data('params');
		var showCount = _params.offset[_getType($parent)];
		if(_params.total[_getType($parent)] > showCount) {
			$parent.find('.more-btn-blue').show();
		}
	};

	var _loadMoreReviews = function ($parent, offset, type) {
		$parent.find('.more-btn-blue').hide();
        var _params = $parent.data('params');
		var data = {
			offset: offset,
			type: type,
			id: _params.id,
			hideRate: 1, //виджет используется только в бирже а там скрываем
			entity: _params.entity
		};
		$.get('/api/rating/loadreviews', data, function(response){
            _params.offset[_getType($parent)] += _params.onPage;
            $parent.find('.gig-reviews-list').append(response.html);
            _showNextButton($parent);
            $parent.data('params', _params);
        }, 'json');
	};

	var _setCache = function($parent){
        var _reviewCache = $parent.data('reviewCache');
		if(_getType($parent) == 'positive'){
			_reviewCache['negative'] = $parent.find('.gig-reviews-list').html();
		}else{
			_reviewCache['positive'] = $parent.find('.gig-reviews-list').html();
		}
        $parent.data('reviewCache', _reviewCache);
	};
	var  _changeTab = function(){
		var type =  $(this).data('type');
        var $parent = $(this).closest('.js-reviews');
        var _params = $parent.data('params');
        var _reviewCache = $parent.data('reviewCache');
		if($parent.find('.reviews-tab__item.active').data('type') == type){
			return false;
		}
        
		if(_params.total[type] == 0){
			return false;
		}

		$parent.find('.reviews-tab__item').removeClass('active');
		$(this).addClass('active');
		_params.type = type;

		_setCache($parent);

		$parent.find('.gig-reviews-list').html('');
		if(_reviewCache[type].length){
			$parent.find('.gig-reviews-list').append(_reviewCache[type]);
			var showCount = _params.offset[type] + _params.onPage;
			if(_params.total[type] <= showCount) {
				$parent.find('.more-btn-blue').hide();
			}else{
				$parent.find('.more-btn-blue').show();
			}

			return false;
		}

		_loadMoreReviews($parent, 0, type);
	};
    
    var methods = {
        init: function (options) {
            return this.each(function () {
                var _params = {
                    id: 0,
                    type: 'positive',
                    onPage: 0,
                    onPageStart: 0,
                    total: {positive: 0, negative: 0},
                    offset: {positive: 0, negative: 0},
                    entity: 'kwork'
                };
                var _reviewCache = {
                    positive: '',
                    negative: ''
                };
                var $this = $(this);
                var $moreBtn = $this.find('.more-btn-blue');

                if(typeof $this.data('moreBtnText') != 'undefined'){
                    $moreBtn.find('.more-btn__text').text($this.data('moreBtnText'));
                }

                var params = $this.data('params');
                for(var i in params) {
                    _params[i] = params[i];
                }
                $this.data('params', _params);

                _params.total.positive = $this.find('#pos').data('count')^0;
                _params.total.negative = $this.find('#neg').data('count')^0;
                _params.type =  $this.find('.reviews-tab__item.active').data('type');
                if(!_params.offset[_getType($this)]){
                    _params.offset[_getType($this)] = _params.onPageStart;
                }else{
                    _params.offset[_getType($this)] = _params.onPage;
                }

                $this.data('params', _params);
                $this.data('reviewCache', _reviewCache);
                $moreBtn.on('click', function(){
                    var offset = _params.offset[_getType($this)];
                    _loadMoreReviews($this, offset, _getType($this));
                });

                $this.find('.reviews-tab__item').bind('click.reviewWidget', _changeTab);

                _showNextButton($this);
            })
        },
        load: function(offset, type){
			_loadMoreReviews($(this), offset, type);
		}
    };

    $.fn.reviewWidget = function (method) {

        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error(t('Метод с именем {{0}} не существует для jQuery.tooltip', [method]));
        }

    };

})(jQuery);