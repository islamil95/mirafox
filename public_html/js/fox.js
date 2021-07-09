var preloader_timeout_ajax;
var disableCurrentClick = false;
var csSendQueue = JSON.parse(localStorage.getItem('csSendQueue') || '[]');

window.touchIsUsed = false;

if (typeof Object.assign != 'function') {
	// Must be writable: true, enumerable: false, configurable: true
	Object.defineProperty(Object, 'assign', {
		value: function assign(target, varArgs) { // .length of function is 2
			'use strict';
			if (target == null) { // TypeError if undefined or null
				throw new TypeError('Cannot convert undefined or null to object');
			}

			var to = Object(target);

			for (var index = 1; index < arguments.length; index++) {
				var nextSource = arguments[index];

				if (nextSource != null) { // Skip over if undefined or null
					for (var nextKey in nextSource) {
						// Avoid bugs when hasOwnProperty is shadowed
						if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
							to[nextKey] = nextSource[nextKey];
						}
					}
				}
			}
			return to;
		},
		writable: true,
		configurable: true,
	});
}

function SendData(data) {
	this.data = data || {};
};
SendData.prototype.append = function(key, value) {
	if (!(key in this.data)) {
		this.data[key] = [];
	}
	this.data[key].push(value);
};
SendData.prototype.serializeForm = function(el) {
	var dataArray = $(el).serializeArray();
	var _self = this;
	_.forEach(dataArray, function(v, k) {
		_self.append(v.name, v.value);
	});
}
SendData.prototype.getFormData = function() {
	var formData = new FormData();
	$.each(this.data, function(k, v) {
		$.each(v, function(k2, v2) {
			formData.append(k, v2);
		});
	});
	formData = formDataFilter(formData);
	return formData;
}
SendData.prototype.get = function(name) {
	if (name in this.data) {
		return this.data[name][0];
	}	
}

function csUpdateSendQueue() {
	localStorage.setItem('csSendQueue', JSON.stringify(csSendQueue));
}

function csSendQueueProgress() {
	if (csSendQueue.length < 1) {
		return;
	}
	try {
		var currentSend = csSendQueue[0];
		if (Math.floor(Date.now() / 1000) - currentSend.time > 60 * 10) {
			csSendQueue = [];
			csUpdateSendQueue();
			return;
		}
		var sendData = new SendData(currentSend.data);
		var formData = sendData.getFormData();
		var xhr = new XMLHttpRequest();
		xhr.open('post', currentSend.action, true);
		xhr.onload = function(r) {
			csSendQueue.shift();
			csUpdateSendQueue();
			csSendQueueProgress();
			if (window.conversationPage) {
				if (xhr.status == 200) {
					try {
						result = JSON.parse(xhr.responseText);
						if (result.MID || result.id) {
							window.bus.$emit('bgSendMessages', [result]);
						}
					} catch(e) {}
				}
			}
		};
		xhr.send(formData);
		csQueueSended = true;
	} catch(e) {
		csSendQueue = [];
		csUpdateSendQueue();
	}
}

csSendQueueProgress();

window.popupJscrollPane = false;
var Utils = (function(){
	var isActiveWindow = false;
	var timeOffset = Math.floor(Date.now() / 1000) - window.serverTime;

	return {
		toBool: function(n) {
			return (n == 'true' || n == '1');
		},
		// Вычислить текущее время на сервере
		getServerTime: function() {
			return Math.floor(Date.now() / 1000) - timeOffset;
		},
		// Спарсить ответ сервера
		parseServerResponse: function(r) {
			var data = {};
			try {
				data = JSON.parse(r.responseText || '{}');
			} catch(e) {}
			if (typeof data != 'object') {
				data = {};
			}
			return data;
		},
		// Проверка на пустоту
		empty: function(mixed_var) {
			var undef, key, i, len;
			var emptyValues = [undef, null, false, 0, '', '0'];
			for (i = 0, len = emptyValues.length; i < len; i++) {
				if (mixed_var === emptyValues[i]) {
					return true;
				}
			}
			if (typeof mixed_var === 'object') {
				for (key in mixed_var) {
					return false;
				}
				return true;
			}
			return false;
		},
		// Оставить только уникальные элементы массива
		uniqueArray: function(A) {
			if (this.empty(A)) return [];
			var n = A.length,
				k = 0,
				B = [];
			for (var i = 0; i < n; i++) {
				var j = 0;
				while (j < k && B[j] !== A[i]) j++;
				if (j == k) B[k++] = A[i];
			}
			return B;
		},
		declOfNum: function(number, titles) {
			var cases = [2, 0, 1, 1, 1, 2];
			return titles[ (number%100>4 && number%100<20)? 2 : cases[(number%10<5)?number%10:5] ];
		},
		declOfNumEn: function(number, titles) {
			return titles[ number == 1 ? 0 : 1 ];
		},
        numberFormat: function( number, decimals, dec_point, thousands_sep ) {
            var i, j, kw, kd, km;

            if( isNaN(decimals = Math.abs(decimals)) ){
                decimals = 2;
            }
            if( dec_point == undefined ){
                dec_point = ",";
            }
            if( thousands_sep == undefined ){
                thousands_sep = ".";
            }

            i = parseInt(number = (+number || 0).toFixed(decimals)) + "";

            if( (j = i.length) > 3 ){
                j = j % 3;
            } else{
                j = 0;
            }

            km = (j ? i.substr(0, j) + thousands_sep : "");
            kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);
            kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : "");

            return km + kw + kd;
		},
		// Определяем ширину скролла у браузера
		getScrollBarWidth: function() {
			var divWidth = 100,
				$outer = jQuery('<div>').css({visibility: 'hidden', width: divWidth, overflow: 'scroll'}).appendTo('body'),
				widthWithScroll = jQuery('<div>').css({width: '100%'}).appendTo($outer).outerWidth();

			$outer.remove();

			return divWidth - widthWithScroll || 0;
        },
        getWindowOffset: function () {
            return ($(window).height() ^ 0) + ($(window).scrollTop() ^ 0);
        },
        replaceQueryParam: function(param, newVal, search) {
            var regex = new RegExp("([?;&])" + param + "[^&;]*[;&]?");
            var query = search.replace(regex, "$1").replace(/&$/, '');
            return (query.length > 2 ? query + "&" : "?") + (newVal ? param + "=" + newVal : '');
        },
        debounce: function (func, wait, immediate) {
            var timeout;
            return function () {
                var context = this, args = arguments;
                var later = function () {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            }
        },
        throttle: function (fn, threshold, scope, firstDelay) {
            threshold || (threshold = 250);
            var last = 0,
                deferTimer;
            return function () {

                if (firstDelay && !last) {
                    last = +new Date;
                }

                var context = scope || this;

                var now = +new Date,
                    args = arguments;

                if ((firstDelay || last) && now < last + threshold) {
                    clearTimeout(deferTimer);
                    deferTimer = setTimeout(function () {
                        if (firstDelay) {
                            last = 0;
                        } else {
                            last = now;
                        }
                        fn.apply(context, args);
                    }, threshold);
                } else {
                    last = now;
                    fn.apply(context, args);
                }
            };
        },
        initActiveWindowListener: function() {
	        if(document.hidden !== undefined && !document.hidden){
		        isActiveWindow = true;
            }
            $(window).blur(function(){
                isActiveWindow = false;
            });
            $(window).focus(function(){
                isActiveWindow = true;
            });
        },
        isActiveWindow: function() {
            return isActiveWindow;
        },
			isSafariBrowser: function(){
				return navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1 &&  navigator.userAgent.indexOf('Android') == -1;
			},
			isEmptyObject: function (obj) {
				for (var i in obj) {
					if (obj.hasOwnProperty(i)) {
						return false;
					}
				}
				return true;
			},
        updateQueryString: function(key, value, url) {
            if (!url) url = window.location.href;
            var regExp = new RegExp("([?&])" + key + "=.*?(&|#|$)(.*)", "gi"),
                hash;

            if (regExp.test(url)) {
                if (typeof value !== 'undefined' && value !== null) {
                    return url.replace(regExp, '$1' + key + "=" + value + '$2$3');
                } else {
                    hash = url.split('#');
                    url = hash[0].replace(regExp, '$1$3').replace(/(&|\?)$/, '');
                    if (typeof hash[1] !== 'undefined' && hash[1] !== null) {
                        url += '#' + hash[1];
                    }
                    return url;
                }
            } else {
                if (typeof value !== 'undefined' && value !== null) {
                    var separator = url.indexOf('?') !== -1 ? '&' : '?';
                    hash = url.split('#');
                    url = hash[0] + separator + key + '=' + value;
                    if (typeof hash[1] !== 'undefined' && hash[1] !== null) {
                        url += '#' + hash[1];
                    }
                }

                return url;
            }
        },
        priceFormat: function (price, priceItemLang, thousandsDelimiter) {
            if (!priceItemLang) {
                priceItemLang = lang;
            }
            if (thousandsDelimiter === undefined) {
                thousandsDelimiter = " ";
            }
            if (priceItemLang === "ru") {
                return Utils.numberFormat(price, 0, ",", thousandsDelimiter);
            } else {
            	price = price + '';
                if ((price ^ 0).toString() === price.toString()) {
                    return Utils.numberFormat(price, 0, ".", thousandsDelimiter);
                } else {
                    return Utils.numberFormat(price, 2, ".", thousandsDelimiter);
                }
            }
        },
		// Если число в экспонентном формате выводим в десятичном формате в виде строки
		bigNumberToString: function(number) {
			var data = String(number).split(/[eE]/);
			if (data.length == 1) return data[0];

			var z = '',
			sign = number < 0 ? '-' : '',
			str = data[0].replace('.', ''),
			mag = Number(data[1]) + 1;

			if (mag < 0) {
				z = sign + '0.';
				while (mag++) z += '0';
				return z + str.replace(/^\-/, '');
			}
			mag -= str.length;
			while (mag--) z += '0';
			return str + z;
		},
        addCurrencySign: function (formattedPrice, priceItemLang, ruSign) {
            if (!priceItemLang) {
                priceItemLang = lang;
            }

            if (ruSign === undefined) {
            	if (lang === "ru") {
		            ruSign = "руб.";
	            } else {
            		ruSign = "RUR"
	            }
            }
            if (priceItemLang === "ru") {
                return formattedPrice + " " + ruSign;
            } else {
                return "$" + formattedPrice;
            }
        },
        priceFormatWithSign: function (price, priceItemLang, thousandsDelimiter, ruSign) {
		    var formattedPrice = Utils.priceFormat(price, priceItemLang, thousandsDelimiter);
            return Utils.addCurrencySign(formattedPrice, priceItemLang,  ruSign);
        },
        getUrlParameter: function(sParam) {
            var sPageURL = decodeURIComponent(window.location.search.substring(1)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

            for (i = 0; i < sURLVariables.length; i++) {
                sParameterName = sURLVariables[i].split('=');

                if (sParameterName[0] === sParam) {
                    return sParameterName[1] === undefined ? true : sParameterName[1];
                }
            }
        },
		cdnBaseUrl: function(path) {
			return (config.cdn.baseUrl || '') + path;
		},
		cdnAdminUrl: function(path) {
			return (config.cdn.adminUrl || '') + path;
		},
		cdnImageUrl: function(path) {
			return (config.cdn.imageUrl || '') + path;
		}
    }
})();

Utils.initActiveWindowListener();

var GTM = (function () {
	var _pushDataLayer = function (data) {
		var dataLayer = window.dataLayer = window.dataLayer || [];
		dataLayer.push(data);
	};

	var _pushDataLayers = function (data) {
		data.forEach(function(dataLayer) {
			_pushDataLayer(dataLayer)
		})
	};

	return {
		pushDataLayer: _pushDataLayer,
		pushDataLayers: _pushDataLayers
	}
})();

function forceRegisterPopup() {
	if (typeof window.registerPopupCount !='undefined' && window.registerPopupCount < 2) {
		window.registerPopupCount++;
		Cookies.set('registerPopupCount', window.registerPopupCount, { expires: 30, path: '/', SameSite: 'Lax' });
		show_signup();
	}
}

$(document).ready(function(){
	anchorInit();

	document.addEventListener('touchstart', function() {
		window.touchIsUsed = true;
	});

	window.registerPopupCount = 0;
	var userId = USER_ID || 0;
	userId = parseInt(userId);
});
/**
 * @module
 */
var Api = (function () {
    var SEND_URL = '/api/';

    /**
     *
     * @param {String} method
     * @param {Object} params
     * @param {Object} callback
     * @private
     */
    var _request = function (method, params, callback) {
        $.post(SEND_URL + method, params, function (response) {
            if (response.success !== true) {
                console.error('method ' + method + ' has error', response);
            }

            callback.call(this, response.data);
        }, 'json');
    };

    return {
        request: _request
    }
})();

(function($) {
    $.fn.hasScrollBar = function() {
        return this.get(0) ? this.get(0).scrollHeight > this.innerHeight() : false;
    };
})(jQuery);

var kMetric = (function () {
    var _userIsChecked,
        _metricId;

    var _setUser = function () {
        if (_userIsChecked) {
            window.removeEventListener('scroll', _setUser);
            return;
        }

        _userIsChecked = true;

        var params = {
			'referrer': document.referrer,
		};
        $.post('/api/metric/setfirstvisit', params, function (response) {
            if (response.success) {
                _userIsChecked = true;
                if (typeof(yaCounter32983614) !== 'undefined'){
                    yaCounter32983614.params({'user_id': _metricId});
                }
            }

            window.removeEventListener('scroll', _setUser);
        }, 'json');
    };

    var _addInitEvents = function () {
        window.addEventListener('scroll', _setUser);
    };

    return {
        init: function (userChecked, metricId) {
            _userIsChecked = userChecked;
            _metricId = metricId;

            if (!_userIsChecked) {
                _addInitEvents();
            }
        },
        getUserIsChecked: function () {
            return _userIsChecked;
        }
    }
})();

(function ($) {

    var _measure = function(fn){
        var $el = $(this).clone(false);
        $el.css({visibility: 'hidden', position:   'absolute'});
        $el.appendTo('body');
        var result = fn.apply($el);
        $el.remove();

        return result;
    };

    var _measure = function(fn){
        var $el = $(this).clone(false);
        $el.css({visibility: 'hidden', position:   'absolute'});
        $el.appendTo('body');
        //copyComputedStyle(this, $el[0]);
        var result = fn.apply($el);
        $el.remove();

        return result;
    };

    var _setTooltipDimensions = function (tooltip) {
        var $that = $(this);
        if ($that.data('proportion')) {
            _setByProportion($(tooltip), $that.data('proportion'));
        }

        var $tooltip = $(tooltip),
            hPos = $tooltip.data('position'),
            tooltipWidth = _measure.call(tooltip, function () {
                if ($that.data('proportion')) {
                    _setByProportion(this, $that.data('proportion'));
                }

                return this.outerWidth();
            }),
            tooltipHeight = _measure.call(tooltip, function () {
                if ($that.data('proportion')) {
                    _setByProportion(this, $that.data('proportion'));
                }

                return this.outerHeight();
            }),
            parentCenter = $(this).offset().left + parseInt($(this).css('margin-left')) + $(this).outerWidth() / 2,
            parentRight = $(this).offset().left + parseInt($(this).css('margin-left')) + $(this).outerWidth();

        _setVertical.call(this, $tooltip);
        _setHorizontal.call(this, $tooltip);

        var maxRightPosition = 0,
            tooltipPosition = 0;
        switch (hPos) {
            case 'center':
                maxRightPosition = parentCenter + tooltipWidth / 2 + 50;
                tooltipPosition = parentCenter - tooltipWidth / 2;
                break;
            case 'right':
                maxRightPosition = parentCenter - tooltipWidth + 50;
                tooltipPosition = parentCenter - tooltipWidth + 30;
                break;
            case 'right-fill':
                maxRightPosition = parentRight - tooltipWidth;
                tooltipPosition = parentRight - tooltipWidth;
                break;
            case 'left-fill':
				maxRightPosition = parentCenter + tooltipWidth + 50 + 25;
				tooltipPosition = parentCenter - 25;
				break;
			case 'left':
            default:
                maxRightPosition = parentCenter + tooltipWidth + 50;
                tooltipPosition = parentCenter - 30;

        }

        if (maxRightPosition > $(window).width()) {
            tooltipPosition = $(document).width() - tooltipWidth -10;
        }

        if (tooltipPosition < 0) {
            tooltipPosition = 0;
        }
        var tooltipLeft = tooltipPosition;
        if ($tooltip.data('aroundPosition') == 'right') {

            if (!$that.data('proportion')) {
                $tooltip.css('width', maxRightPosition - tooltipPosition +50 + 'px');
            }
            var cornerTop = (-tooltipHeight / 2) + 10;
            $tooltip.find('.js-tooltip-block-corner__inner').css({'top': cornerTop + 'px'});

            tooltipLeft = $tooltip.offset().left;

        } else if($tooltip.data('aroundPosition') == 'top'){
            var shift = 0;
            $tooltip.parents("*").each(function(i){
                if($(this).css('position') == 'relative'){
                    shift = $(this).offset().left - 2;
                    return false;
                }
            });
            $tooltip.css({'left': (tooltipPosition - shift) + 'px'});

            var left = parentCenter - tooltipPosition;
            var cornerLeft = left - 40;
            $tooltip.find('.js-tooltip-block-corner__inner').css({'left': cornerLeft + 'px'});

        } else if ($tooltip.data('aroundPosition') === 'left') {
            if (!$that.data('proportion')) {
                $tooltip.css('width', maxRightPosition - tooltipPosition + 'px');
            }
            var cornerTop = (-tooltipHeight / 2) + 10;
            $tooltip.find('.js-tooltip-block-corner__inner').css({'top': cornerTop + 'px'});

            tooltipLeft = $tooltip.offset().left;
        } else {
            $tooltip.css({'left': (tooltipPosition) + 'px'});
            var left = parentCenter - tooltipPosition;
            var cornerLeft = left - 30;
            $tooltip.find('.js-tooltip-block-corner__inner').css({'left': cornerLeft + 'px'});
        }

        var width = tooltipWidth;
        if (tooltipLeft + tooltipWidth > $(window).width()) {
            width = $(document).width() - tooltipLeft-10;
        }
        if ($that.data('proportion')) {
            $tooltip.outerWidth(width);
        } else {
            $tooltip.css('width', width + 'px');
        }
    };

    var _setByProportion = function (tooltip, proportion) {
        var baseWidth = tooltip.width();
        var needleProportion;

        if (proportion === true) {
            needleProportion = (4 / 2); //default proportion
        } else {
            proportion = proportion.split(':');
            needleProportion = (proportion[0] / proportion[1]);
        }

        var wTry = baseWidth;
        var step = 10;
        var currentProportion = tooltip.width() / tooltip.height();
        while (currentProportion > needleProportion) {
            wTry -= step;
            tooltip.width(wTry);
            currentProportion = tooltip.width() / tooltip.height();
        }

        var i = 0;
        while(!_canSqueeze(tooltip) && i < 50){
            wTry += step;
            tooltip.width(wTry);
            i++;
        }

        if (!_canSqueeze(tooltip)) {
            tooltip.find('.js-tooltip-block__text').css({
                'word-break': 'break-all',
                'overflow-x': 'inherit'
            });
        }
    };

    var _canSqueeze = function(tooltip){
        var $text = tooltip.find('.js-tooltip-block__text');
        $text.css('overflow-x', 'auto');
        return $text[0].scrollWidth <= $text.width();
    };

    var _setVertical = function($tooltip){
        var $that = $(this);
        var outerHeight = _measure.call($tooltip[0], function () {
                if ($that.data('proportion')) {
                    _setByProportion(this, $that.data('proportion'));
                }
                return this.outerHeight();
            }),
            parentOffsetTop = $(this).offset().top,
            scrollTop = $('body').scrollTop();

        var position = {};
        if ($tooltip.data('aroundPosition') == 'right' || $tooltip.data('aroundPosition') == 'left') {
            position.vertical = 'right';
        } else {
            position.vertical = 'top';
        }

        var tooltipTop;
        if (position.vertical == 'top') {
            if (parentOffsetTop - outerHeight < scrollTop) {
                tooltipTop = $(this).position().top + $(this).outerHeight() + 10;
                $tooltip.removeClass('tooltip-block--top').addClass('tooltip-block--bottom');
            } else {
                tooltipTop = $(this).position().top + parseInt($(this).css('margin-top')) - outerHeight - 10;
                $tooltip.css({'top': tooltipTop + 'px'});
                $tooltip.removeClass('tooltip-block--bottom').addClass('tooltip-block--top');
            }
            $tooltip.css({'top': tooltipTop + 'px'});
        }else if (position.vertical == 'right'){
            tooltipTop = $(this).position().top + $(this).outerHeight() / 2 - outerHeight / 2;
            $tooltip.css({'top': tooltipTop + 'px'});
        }
    };

    var _setHorizontal = function($tooltip){
        var tooltipLeft;
        var remove;
        var add;

        if ($tooltip.data('aroundPosition') == 'top') {
        }else if ($tooltip.data('aroundPosition') == 'right'){
            tooltipLeft = $(this).position().left + $(this).outerWidth() + 10;
            remove = 'tooltip-block--bottom tooltip-block--top';
            add = 'tooltip-block--right';
        }else if ($tooltip.data('aroundPosition') == 'left'){
            tooltipLeft = $(this).position().left - 10 - $tooltip.outerWidth();
            remove = 'tooltip-block--bottom tooltip-block--top';
            add = 'tooltip-block--left';
        }

        $tooltip.css({'left': tooltipLeft + 'px'});
        $tooltip.removeClass(remove).addClass(add);
    };

    var _needHide = function (e, block) {
        if(typeof $(block).data('tooltip') !== 'undefined'){
            var $tooltip = $(block).data('tooltip').tooltip;
            return !$(e.relatedTarget).closest('.js-tooltip-block').filter($tooltip).length;
        }

        return $(e.relatedTarget).filter(block).length == 0;
    };

    var methods = {
        init: function (options) {
            return this.each(function () {

                var $this = $(this),
                    text = $(this).find('.js-tooltip-text').length?$(this).find('.js-tooltip-text').html():$this.data('tooltipText'),
                    tooltipClass = $this.data('tooltipClass')?$this.data('tooltipClass'):'',
                    tooltipBlockClass = $this.data('tooltipBlockClass')?$this.data('tooltipBlockClass'):'',
                    tooltipTheme = $this.data('tooltipTheme')?$this.data('tooltipTheme'):'light',
                    data = $this.data('tooltip'),
                    $tooltip = $('<div />', {
                        html: '<div class="js-tooltip-block__text ' + tooltipClass + '">' + text + '</div>'
                    }).addClass('js-tooltip-block tooltip-block').addClass('tooltip-block_theme_' + tooltipTheme).addClass(tooltipBlockClass);
                var $corner = $('<div>').addClass('tooltip-block__corner')
                    .html('<div class="tooltip-block-corner__inner js-tooltip-block-corner__inner">');
                $tooltip.append($corner);

                if ($this.data('tooltipHidden')) return this;

                if (!data) {
                    $(this).data('tooltip', {
                        target: $this,
                        tooltip: $tooltip
                    });

                    $(this).bind({
                        'mouseenter.tooltip': methods.show,
						'mouseleave.tooltip': methods.hide,
                    });
                }
            });
        },
        destroy: function () {

            return this.each(function () {

                var $this = $(this),
                    data = $this.data('tooltip');

                $(window).unbind('.tooltip');
                data.tooltip.remove();
                $this.removeData('tooltip');

            })

        },
        show: function () {
        	if ($('.js-tooltip-block').length) return;

            $(this).trigger('beforeshow.tooltip');

            var $tooltip = $(this).data('tooltip').tooltip;
            if($(this).data('tooltipBlockClass')){
                $('.' + $(this).data('tooltipBlockClass')).remove();
            }

            $tooltip.show();
            $(this).before($tooltip);
            $tooltip.bind({
				'mouseleave.tooltip': methods.hide
			});
			if ($(window).width() < 575) {
				$('.modal .js-tooltip').data('tooltipAroundPosition', 'top'); // меняем положение тултипов в модальном окне добавления опции к кворку
			}
            $tooltip.data('position', typeof $(this).data('tooltipPosition') != 'undefined' ? $(this).data('tooltipPosition') : 'left');
            $tooltip.data('aroundPosition', typeof $(this).data('tooltipAroundPosition') != 'undefined' ? $(this).data('tooltipAroundPosition') : 'top');
            $tooltip.data('animation', typeof $(this).data('tooltipAnimation') != 'undefined' ? $(this).data('tooltipAnimation') : false);
            $tooltip.data('delay', typeof $(this).data('tooltipDelay') != 'undefined' ? $(this).data('tooltipDelay') : false);
            $(this).data('currentTooltip', $tooltip);
            $(this).trigger('show.tooltip');
            _setTooltipDimensions.apply(this, $tooltip);
            $(this).trigger('aftershow.tooltip');

            if($tooltip.data('animation')){
                $tooltip.css('opacity', 0);
                $tooltip.animate({opacity: 1}, 200);
            }
            if ($tooltip.data('delay'))
            {
                $tooltip.css('opacity', 0);
                $tooltip.delay(parseFloat($tooltip.data('delay')) * 1000).animate({opacity: 1}, 0);
            }
        },
        get: function (){
            return $(this).data('tooltip').tooltip;
        },
        hide: function (e) {
            if(!_needHide(e, this)){
                return;
            }

            var $tooltip;
            if($(this).data('currentTooltip')) {
                $(this).trigger('hide.tooltip');
                $tooltip = $(this).data('currentTooltip');
            }else{
                $(this).next().trigger('hide.tooltip');
                $tooltip = $(this).next().data('currentTooltip');
			}
			$tooltip.data('delayHide', typeof $(this).data('tooltipDelayHide') != 'undefined' ? $(this).data('tooltipDelayHide') : false);
            if ($tooltip.data('animation')) {
                $tooltip.animate({opacity: 0}, 200, function () {
                    $tooltip.removeAttr('style');
                    $tooltip.remove();
                });
            } else {
            	$tooltip = $tooltip.data('delayHide') ? $tooltip.delay(parseFloat($tooltip.data('delayHide')) * 1000) : $tooltip;
            	$tooltip.animate({opacity: 0}, 10, function () {
                    $tooltip.removeAttr('style');
                    $tooltip.remove();
                });
            }
        },
        update: function (content) {
            var $tooltip = $($(this).data('currentTooltip'));
            $tooltip.find('.js-tooltip-block__text').html(content);
            _setTooltipDimensions.apply(this, $tooltip);
        },
        setContent: function(content){
            var $tooltip = $(this).data('tooltip').tooltip;
            $tooltip.find('.js-tooltip-block__text').html(content);
            _setTooltipDimensions.apply(this, $tooltip);
        }
    };

    $.fn.tooltip = function (method) {

        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error(t('Метод с именем {{0}} не существует для jQuery.tooltip', [method]));
        }

    };

})(jQuery);

(function ($) {

    var _setPosition = function($preloader){
        var parentOffsetTop = $(this).offset().top,
            parentOffsetLeft = $(this).offset().left,
            scrollTop = $('body').scrollTop();
        var top, left;

        left = parentOffsetLeft + $(this).outerWidth()/2;

        if(parentOffsetTop + $(this).height() > scrollTop + $(window).height()){
            top = scrollTop + $(window).height() - parentOffsetTop;
        }else{
            top = $(this).height();
        }

        top = parentOffsetTop + top / 2;
        $preloader.offset({top: top, left: left});
        $(this).prepend($preloader);
    };

    var _setWhiteBlock = function(){
        var $block = $('<div style="background-color: white; opacity: 0.5;position:absolute;z-index: 2;">');
        $block.width($(this).width()).height($(this).height());

        $(this).prepend($block);
    };

    var methods = {
        init: function (options) {
            return this.each(function () {
            });
        },
        show: function () {
            var $this = $(this),
                preloaderClass = $this.data('preloaderClass')?$this.data('preloaderClass'):'',
                $preloader = $('<div class="preloader__ico" />').addClass(preloaderClass);

            if($(this).data('preloaderOpacity') == true){
                _setWhiteBlock.call(this);
            }

            _setPosition.call(this, $preloader);
        }
    };

    $.fn.preloader = function (method) {

        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error(t('Метод с именем {{0}} не существует для jQuery.tooltip', [method]));
        }

    };

})(jQuery);

function DataLayer() {
  this.dataLayer = [];

  this.add = function(item) {
    this.dataLayer.push(item);
  };

  this.get = function() {
    return this.dataLayer;
  };
};
var dL = new DataLayer();

/**
 * Добавляет кол-ва активных заказов к пунктам меню про эти самые заказы.
 * Делается это с клиента в момент первого визуального отображения самих меню,
 * по соображениям разумности - пользователь может никогда меню не открывать,
 * а мы ему все равно запросы на кол-во...
 *
 * @param {boolean} [force=false] - принудительно обновить кол-во заказов
 */
function setActiveOrdersCount(force) {
	var force = force || false;
	var $body = $('body');

	if (!$body.data('has_active_orders_count') || true === force) {
		$body.data('has_active_orders_count', true);
		$.ajax({
			url: '/api/order/getorderscount',
			method: 'post',
			dataType: 'json',
			success: function (response) {
				if (false !== response) {
					if (response.asPayer) {
						setItemActiveOrdersCount('.has-orders-count-as-payer', response.asPayer);
					}
					if (response.asWorker) {
						setItemActiveOrdersCount('.has-orders-count-as-worker', response.asWorker);
					}
					if (response.payerWants) {
						setItemActiveOrdersCount('.js-has-wants-count-as-payer', response.payerWants);
					}
				}
			},
            error: function (){
	            $body.data('has_active_orders_count', false);
            }
		});
	}
}

/**
 * Добавление или замена количества активных заказов в меню
 * @param selector Селектор пункта меню в котором меняем
 * @param count Количество активных заказов которое устанавливаем
 */
function setItemActiveOrdersCount(selector, count) {
	var $menuItem = $(selector);

	if ($menuItem.length) {
		$menuItem.each(function (index, el) {
			var $el = $(el);
			var $counter = $el.find('.user-active-orders-count');
			if ($counter.length) {
				$counter.html(count);
			} else {
				$el.append('<span class="user-active-orders-count">' + count + '</span>');
			}
		});
	}
}

function addRefParam() {
	var params = getGetParams();

    return params['ref'] ? '&ref=' + params['ref'] : '';
}

function openTabWithdrawMoney() {
	$('.btn-group-js').hide();
	$("#can-withdraw-tooltip").removeClass('hidden');
	$('.withdraw-money-form-js').show();
	$(".balance-tooltip-wrapper").removeClass("hidden");
	$(".balance-placeholder").addClass("hidden");
}

$(document).ready(function() {
    $('.js-tooltip').tooltip();

    $('.kwork_birthday_badge.js-tooltip')
        .mouseover(function(){
            $(this).addClass('kwork_birthday_badge_hover');
        })
        .mouseout(function(){
            $(this).removeClass('kwork_birthday_badge_hover');
        });

    // Выпадающий список
    $(".droparrow").on('mouseenter',function() {
        var $this = $(this);
        if ($this.hasClass('usernamebox')) {
            setActiveOrdersCount();
        }
        $this.find('.dropdownbox').fadeIn(200, 'swing');
    }).on('mouseleave',function() {
        if ($('.tooltipster-base:hover').length != 0) {
            ('.tooltipster-base').addClass('dropdownbox-tooltip');
            return false
        }else{
            var $this = $(this);
            // For tablet view user menu
            if(isMobile() && $this.hasClass('usernamebox')){
                $this.find('>a').data('clicked', false);
            }
            $this.find('.dropdownbox').hide();
        }
    });

    $(".dropdownbox-tooltip").on('mouseleave',function() {
        if ($('.droparrow:hover').length != 0) {
            return false
        }else{
            var $this = $(".droparrow");
            // For tablet view user menu
            if(isMobile() && $this.hasClass('usernamebox')){
                $this.find('>a').data('clicked', false);
            }
            $this.find('.dropdownbox').hide();
        }
    });

	// For tablet view user menu
	$(".droparrow.usernamebox > a").click(function(e){
	    var el = $(this);
	    if(isMobile() && !el.data('clicked')){
	        el.data('clicked', true);
		    e.preventDefault();
        }
    });

    $(".dropdown dt a").click(function() {
        $(this).parent().parent().find("dd ul").toggle();
        return false;
    });
    $(".dropdown dd ul li a").click(function(e) {
        var text = $(this).html();
        $(this).parent().parent().parent().parent().find("dt a span").html(text);
        $(this).parent().parent().parent().parent().find("dd ul").hide();
        //e.preventDefault();
    });
    function getSelectedValue(id) {
        return $("#" + id).find("dt a span.value").html();
    }
    $(document).bind('click', function(e) {
        var $clicked = $(e.target);
        if (! $clicked.parents().hasClass("dropdown"))
            $(".dropdown dd ul").hide();
    });
	var time_show_extras;
	var time_hide_extras;
	$('.order-extras').hide();
	$('body').on('mouseenter', ".hoverMe", function() {
		$('.need-gray').addClass('gray');
		$('.order-extras').addClass('open');
		$('.order-extras').removeClass('om-close');
	});
	$('body').on('mouseleave', ".hoverMe", function() {
		$('.need-gray').removeClass('gray');
		$('.order-extras').removeClass('open');
		$('.order-extras').addClass('om-close');
		$('.chosen-container.chosen-container-single.chosen-container-single-nosearch.chosen-container-active').removeClass('chosen-with-drop').removeClass('chosen-container-active');
	});
	$('body').on('mouseenter', ".sidebarArea .order-more-block-btn, .portfolio-buy .order-more-block-btn", function() {
		var el = $(this);
		clearTimeout(time_hide_extras);
		time_hide_extras = setTimeout(function () {
			clearTimeout(time_show_extras);
			el.find('.need-gray').addClass('gray');
			el.find('.order-extras').addClass('open');
			el.find('.order-extras').removeClass('om-close');
		}, 300);
	});
    $(".order-extras-list").on(
        "webkitAnimationEnd oanimationend msAnimationEnd animationend",
        function() {
            if($(this).height() > 0){
                $(this).addClass("no-overflow");
            }
        }
    );
	$('body').on('mouseleave', ".order-more-block-btn", function() {
		var that = this;
		time_show_extras = setTimeout(function(){
		clearTimeout(time_hide_extras);
		$('.need-gray').removeClass('gray');
		$('.order-extras').removeClass('open');
		$('.order-extras').addClass('om-close');
			$(that).find('.order-extras .order-extras-list').removeClass('no-overflow');
			$('.chosen-container.chosen-container-single.chosen-container-single-nosearch.chosen-container-active').removeClass('chosen-with-drop').removeClass('chosen-container-active');
		}, 0);
	});
    $('.contactUser').hide();
    $(".contactPopup").on('click',function() {
        $('.contactUser').fadeIn();
        $('html, body').animate({
            scrollTop: $(".contactUser").offset().top
        }, 800);
        return false;
    });
    $(".reviews-more .show-all-text").on('click',function() {
            $(this).toggleClass('active');
            $(this).parents('.reviews-more_block').find('p').toggleClass('show-all');
        });
    // $('.balance-refill-btn-js').click(function(){
    //    $(this).parent().hide();
    //    $('.balance-refill-form-js').show();

 //     })
    $('.withdraw-money-btn-js').click(function(){
		openTabWithdrawMoney();
    });

    if (window.location.hash === '#withdrawMoney') {
		openTabWithdrawMoney();
	}

    $('.hide-form-btn-js').click(function(){
		$("#can-withdraw-tooltip").addClass('hidden');
        $('.balance-refill-form-js').hide();
        $('.withdraw-money-form-js').hide();
        $('.btn-group-js').show();
		$(".balance-placeholder").removeClass("hidden");
		$(".balance-tooltip-wrapper").addClass("hidden");
    });



    $(document).on('keydown paste','.maxLength-js',function(){
        $(this).val($(this).val().slice(0,$(this).attr('data-max')-1))
    });


    if($(".only-number").length > 0){
        $(".only-number").ForceNumericOnly();
    }
    if($('.main-slider').length>0){
            $('.main-slider').on('init', function(){
                $('.main-block-slider .centerwrap ').fadeIn();
            });
        $('.main-slider').slick({
             centerMode: true,
             variableWidth: true,
             slidesToShow: 1,
             infinite: true
        })
    }

    $('.block-select-options_search-form_close').bind('click',function(){
        $('.block-select-options_search-form').hide();
        $('.block-select-options_btn').show();
    });
    $('.block-select-options_btn_search-active').bind('click',function(){
        $('.block-select-options_search-form').show();
        $('.block-select-options_btn').hide();
    });

    $(document).on('click','.promo-header-js',function(){
        $('.b-promo-top').toggleClass('active');
        var date = new Date();
        var days = 360;
        date.setTime(date.getTime() + (days*24*60*60*1000));
        setCookie('show_promo_header',1,{ expires: date,path:'/', SameSite:'Lax' });
    });
    $(document).on('click','.hide-btn-promo-js',function(){
        var date = new Date();
        var days = 360;
        date.setTime(date.getTime() + (days*24*60*60*1000));
        setCookie('show_promo_header',0,{ expires: date,path:'/', SameSite:'Lax' });
    });

    $(document).on('hover','.select-user-type-js',function(){
        if($(this).hasClass('select-user-type_customer')){
            $('.user-menu-payer').show();
             $('.user-menu-worker').hide();
        }
        else{
            $('.user-menu-payer').hide();
            $('.user-menu-worker').show();
        }
    });
    var userType = ($('[name=userType]#1').prop('checked'))?1:2;
    var changeUserTypeTimeout = false;
    var selectedUserType = false;
    $(document).on('mouseenter','.select-user-type-js',function(){
        if($(this).hasClass('select-user-type_customer')){
            changeUserTypeTimeout = setTimeout(function(){
                $('.user-menu-payer').show();
                $('.user-menu-worker').hide();
                $('[name=userType]#1').prop('checked',true);
                selectedUserType = 1;
                changeUserTypeTimeout = false;
            },200);
        }
        else{
            changeUserTypeTimeout = setTimeout(function(){
                $('.user-menu-payer').hide();
                $('.user-menu-worker').show();
                $('[name=userType]#2').prop('checked',true);
                selectedUserType = 2;
                changeUserTypeTimeout = false;
            },200);
        }
    });
    $('.select-user-type-js').on('mouseleave',function(){
        clearTimeout(changeUserTypeTimeout);
        changeUserTypeTimeout = false;
    });
    $('.dropdownbox-profile li').on('click', function(){
       if(selectedUserType)
       {
           changeUserType(selectedUserType, false);
       }
    });
    $('.dropdownbox-profile').hover(function(){},function(){
        if(selectedUserType && userType == 2)
        {
            $('.user-menu-payer').hide();
            $('.user-menu-worker').show();
            $('[name=userType]#2').prop('checked',true);
        }
        else
        {
            if(selectedUserType && userType == 1)
            {
                $('.user-menu-payer').show();
                $('.user-menu-worker').hide();
                $('[name=userType]#1').prop('checked',true);
            }
        }
        selectedUserType = userType;
    });
    // блокировка формы после сабмита
    $(document).on('submit', 'form:not(.ajax-disabling)', function ()
    {
        $(this).find(':submit').attr('disabled', 'disabled').addClass('sleep1000');
        setTimeout(function() { $('.sleep1000').removeAttr('disabled').removeClass('sleep1000'); }, 1000);
    });

    if($('textarea[data-autoresize]').length>0){
        $.each($('textarea[data-autoresize]'), function() {
            var offset = this.offsetHeight - this.clientHeight;

            var resizeTextarea = function(el) {
                $(el).css('height', 'auto').css('height', el.scrollHeight + offset);
            };
            $(this).on('keyup input', function() { resizeTextarea(this); }).removeAttr('data-autoresize');
        });
    }

	var lastScrollLeft = 0;
	var upButtonShowed = false;
    $(window).scroll(function(){
		var scrollLeft = $(window).scrollLeft();
		if (scrollLeft != lastScrollLeft) { 
			$('.header').css("margin-left",-$(window).scrollLeft());
			lastScrollLeft = scrollLeft;
		}
        if ($(this).scrollTop() > 100) {
			if (!upButtonShowed) {
				$('.scrollup').stop().fadeIn();
				upButtonShowed = true;
			}
        } else {
			if (upButtonShowed) {
				$('.scrollup').stop().fadeOut();
				upButtonShowed = false;
			}
        }
	});
	
	
	var siteFooter = $('.footer');
	$(window).on('scroll resize', function() {
		if (siteFooter.length > 0) {
			var footerScroll = siteFooter.offset().top - $(window).height() + 20;
			if ($(this).scrollTop() > footerScroll) {
				$('.scrollup').addClass('scrollup_sticky');
			} else {
				$('.scrollup').removeClass('scrollup_sticky');
			}
		}
	});

    $('.scrollup').on('click', function() {
        $('html, body').animate({ scrollTop: 0 }, 400);
        return false;
    });

    /**
     * Загрузка дополнительных сайтов для продвижения ссылок в таблицу
     */
    $('#more-kwork-links-sites').click(function () {
        var link = $(this);
        var loaderImage = link.parent().find('img');
        link.addClass('hidden');
        loaderImage.removeClass('hidden');
        $.ajax({
            url: '/api/kwork/getkworksites',
            data: {
                kworkId: link.data('id'),
                showHosts: link.data('showHosts')
            },
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                loaderImage.addClass('hidden');
                if (response.success) {
                    var tbody = $('.kwork-links-sites-table').find('tbody');
                    tbody.append(response.html);
                    if (tbody.find('tr').length < link.data('total')) {
                        link.parent().find('span').removeClass('hidden');
                    }
                }
            },
            error: function () {
                loaderImage.addClass('hidden');
                link.removeClass('hidden');
            }
        });
    });

    /**
     * Очистка сохранненных фильтров пользователя
     */
    $('#group_tester_clear_filters').click(function (e) {
        e.preventDefault();
        $.ajax({
            url: '/api/kwork/clearactorfilter',
            success: function () { window.location.reload(); }
        });
    });

});

function toggleBalanceRefillPopup() {
    $('.dropdownbox-balance').toggle();
}


(function ($) {

	var _options = {
		textLinkShow: t('Показать подсказку')
	};
    var methods = {
        init: function (options) {
			if(options){
				_options = options;
			}
            return this.each(function () {
                var $infoBlock = $(this);
                var infoType = $infoBlock.data('type');

                $(this).find('.js-info-block__link').bind('click.infoBlock', function(){
                    if($infoBlock.data('hiddenBlock') != true){
                        $infoBlock.infoBlock('hide');
                    }else{
                        $infoBlock.infoBlock('show');
                    }
                });

                $infoBlock.find('.info-block__text:not(".disable-height")').css('height', $infoBlock.find('.info-block__text').height());
                if(localStorage.getItem(infoType + ':info-block--hidden') == 1){
                    $infoBlock.infoBlock('hide');
                }

                $infoBlock.css('opacity', '1');

                setTimeout(function(){
                    $infoBlock.addClass('info-block_animation');
                }, 400);
            });
        },
        destroy: function () {
            return this.each(function () {
                $(window).unbind('.infoBlock');
            })

        },
        show: function () {
            $(this).data('hiddenBlock', false);
            $(this).find('.js-info-block__link').text(t('Скрыть подсказку'));
            $(this).removeClass('info-block_rolled');
            localStorage.setItem($(this).data('type') + ':info-block--hidden', 0);
            $(this).trigger('show.infoBlock');
        },

        hide: function () {
            $(this).data('hiddenBlock', true);
            $(this).find('.js-info-block__link').text(_options.textLinkShow);
            $(this).find('.info-block__ico-image');
            $(this).addClass('info-block_rolled');
            localStorage.setItem($(this).data('type') + ':info-block--hidden', 1);
            $(this).trigger('hide.infoBlock');
        }
    };

    $.fn.infoBlock = function (method, options) {

        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, [options]);
        } else {
            $.error(t('Метод с именем {{0}} не существует для jQuery.infoBlock', [method]));
        }

    };

})(jQuery);

$(document).on('touchstart','.nav-fox', function (e) {
    if(e.target != this) return;
    mobile_menu_hide();
});

function mobile_menu_toggle() {
	var $nav = $('#foxmobilenav');

	setActiveOrdersCount();

	$('html').addClass('show-mobile-menu');
	$('.foxmenubutton').css({visibility: 'hidden'});
	$('body').css({overflow: 'hidden'});
	$nav.show();
	// $('.all_page').css({position:'relative'})
	setTimeout(function () {
		$('.mobile-menu-hide').css({left: '82%'}).fadeIn();

	}, 100);
	$('.fox-dotcom-mobile-dropdown').animate({left: '0px'}, 300);
}
function mobile_menu_hide() {
    if (isMobile()) {
        $('html').removeClass('show-mobile-menu')
        $('.foxmenubutton').removeAttr('style');
        $('.mobile-menu-hide').hide();
        $('.fox-dotcom-mobile-dropdown').animate({left: '-220px'}, 300);
        $('.mobile-menu-hide').animate({left: '0'}, 300);
        $('.header, .header-topfreelancer').animate({left: '0px'}, 300, function () {
                $('.all_page').css({position: 'static'})
                $('#foxmobilenav').hide();
                $('body').css({overflow: ''});
            }
        );
    }
}

$.fn.ForceNumericOnly =
function () {
    return this.each(function () {
        $(this).on('keydown keyup drop change blur paste', function (evt) {
			setTimeout(function() {
				var input = evt.target;
				input.value = input.value.replace(/[^\d]/g,'');
			}, 0)
		});
    });
};
function setCookie(name, value, options) {
    options = options || {};
	options.secure = true;

    var expires = options.expires;

    if (typeof expires == "number" && expires) {
        var d = new Date();
        d.setTime(d.getTime() + expires*1000);
        expires = options.expires = d;
    }
    if (expires && expires.toUTCString) {
        options.expires = expires.toUTCString();
    }

    value = encodeURIComponent(value);

    var updatedCookie = name + "=" + value;

    for(var propName in options) {
        updatedCookie += "; " + propName;
        var propValue = options[propName];
        if (propValue !== true) {
            updatedCookie += "=" + propValue;
        }
    }
	updatedCookie += '; SameSite=Lax';
    document.cookie = updatedCookie;
}


var deleteCookie = function(name) {
    document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/; SameSite=Lax';
};


// возвращает cookie с именем name, если есть, если нет, то undefined
function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

/**
 * Показ попапа пополнения баланса
 *
 * @param amount Сумма которая будет сразу в поле пополнения
 * @param from На что требуется пополнения баланса kwork|project|inbox
 * @param payment_id Идентифифкатор созданной новой операции пополнения
 * @param orderId Идентификатор заказа который связан с пополнением
 */
function show_balance_popup(amount, from, payment_id, orderId) {
    /*Показываем попап пополнения баланса*/
    var amountBlock = '';
    if (typeof(amount) !== 'undefined'){
        amountBlock = 'value="' + amount + '"';
    }
    var fromBlock = '';
    if (typeof(from) !== 'undefined'){
        fromBlock = '<input name="from_type" value="' + from +'" type="hidden" />';
    }
    var orderIdBlock = "";
    orderId = parseInt(orderId);
    if (!isNaN(orderId) && orderId > 0) {
	    orderIdBlock = '<input name="order_id" value="' + orderId +'" type="hidden" />';
    }
    var paymentIdBlock = '';
    var balanceSwitchToCheck = '';
    if (typeof(payment_id) !== 'undefined'){
        paymentIdBlock = '<input id="payment_id" name="payment_id" value="' + payment_id +'" type="hidden" />';
        balanceSwitchToCheck = 'return balance_popup_switch_to_check(this);';
	}
    var bankLabel = t('Банковские карты');
    var billOption = '';
    if (IS_BILL_ENABLE) {
        bankLabel = t('Банковские карты и безнал');
        billOption = t('<option value="bank">Безнал для юрлиц и ИП</option>');
    }
    var paymentHeader = "<i class=\"icon ico-mastercardVisa\"></i>";
    if (actor_lang == 'ru') {
        paymentHeader = "<div class=\"balance-popup__table-title\">Способ</div>";
    }
    var methods = "Credit & Debit Cards <input type=\"hidden\" value=\"card\" name=\"unitpay_type\"/>";

	var	payPalIsUsed = false;
	var paypal = '';
    if (typeof PAYPAL_ENABLED !== 'undefined' && PAYPAL_ENABLED) {
		payPalIsUsed = true;
    	if (actor_lang == 'ru') {
			paypal = '<optgroup label="Оплата через PayPal">'+
				'<option value="paypal">PayPal</option>'+
				'</optgroup>';
		} else {
    		methods = 'Credit & Debit Cards&nbsp;&nbsp;<input type="radio" name="method" value="card" checked>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>Paypal&nbsp;&nbsp;<input type="radio" name="method" value="paypal">';
		}
	}

    if (actor_lang == 'ru') {
	    var paymore_prefix = "";
	    var gtm_event = "PaymentSystemUnitpay";
	    var phone_methods = "";
	    var alfaClick = "";
	    if (USER_REFILL_SYSTEM === 2) {
		    paymore_prefix = "paymore_";
		    gtm_event = "PaymentSystemKassa";
	    } else {
	    	alfaClick = '<option value="alfaClick">Альфа-Клик</option>';
	    	phone_methods = '<optgroup label="Оплата с телефона">' +
			    '<option value="mc:mts">МТС</option>' +
			    '<option value="mc:mf">Мегафон</option>' +
			    '<option value="mc:beeline">Билайн</option>' +
			    '<option value="mc:tele2">Теле 2</option>' +
			    '</optgroup>';
	    }
		gtm_event = ' data-gtm-event="' + gtm_event + '" ';
	    methods = '<select name="unitpay_type" class="js-balance-popup-select balance-popup__input text styled-input font-OpenSans f14 w240">' +
		    '<optgroup label="Банковские карты">' +
		    '<option value="' + paymore_prefix + 'card" ' + gtm_event + 'selected>Банковская карта</option>' +
		    '<option value="bank">Безнал для юрлиц и ИП</option>' +
		    alfaClick +
		    '</optgroup>' +
		    '<optgroup label="Электронный кошелек">' +
		    '<option value="' + paymore_prefix + 'webmoney"' + gtm_event + '>WebMoney</option>' +
		    '<option value="' + paymore_prefix + 'yandex"' + gtm_event + '>Яндекс.Деньги</option>' +
		    '<option value="' + paymore_prefix + 'qiwi"' + gtm_event + '>QIWI Кошелек</option>' +
		    '</optgroup>' +
		    phone_methods +
		    paypal +
		    '</select>';
    }
	var payment = "";
	if($(".user-payment-method").val()){
		payment = " hidden";
	}
	var paymentChoose;
	if (!payPalIsUsed || actor_lang == 'ru') {
		paymentChoose = '<tr style="height: 40px;">' +
			'<td class="w19p">' +
				paymentHeader +
			'</td>' +
			'<td width="310">' +
				methods +
			'</td>' +
		'</tr>';
	} else {
		paymentChoose = '<tr>' +
			'<td colspan="2">' +
				'<input type="hidden" value="card" name="unitpay_type" />' +
				'<input class="js-pay-method" type="hidden" name="method" value="" />' +
				'<div class="bl-title"><span>Choose payment method</span></div>' +
				'<div class="bl-methods">' +
					'<div class="bl-card" data-method="card" data-name="Unitpay">' +
						'<div></div>' +
						'<div><span>Credit or Debit Card</span></div>' +
					'</div>' +
					'<div class="bl-paypal" data-method="paypal" data-name="PayPal">' +
						'<div></div>' +
						'<div><span>PayPal&trade; <span>(no fee)</span></span></div>' +
					'</div>' +
				'</div>' +
			'</td>' +
		'</tr>';
	}
	var errorOutput = '', securityTip = '', amountMode = '';
	var titleText = (actor_lang == 'ru' ? 'Пополнение баланса' : t('Пополнение баланса'));
	var descText = (actor_lang == 'ru' ? 'Пополняйте с запасом, чтобы<br>всегда заказывать в один клик' : t('Пополняйте с запасом, чтобы<br>всегда заказывать в один клик'));
	var confirmText  = (actor_lang == 'ru' ? 'Пополнить баланс' : t('Пополнить баланс'));
	if (payPalIsUsed && actor_lang != 'ru') {
		titleText = 'Deposit Funds';
		descText = 'Deposit more funds and use them later to place orders in one click';
		confirmText = 'Confirm and Pay';
		errorOutput = '<div class="bl-error hidden">Choose payment method</div>';
		securityTip = '<div class="bl-security-tip">' +
			'<div><i class="fa fa-lock"></i> <span>SSL</span> secured payment</div>' +
			'<div>Your information is protected by 256-bit SSL encryption</div>' +
		'</div>';
		amountMode = ' dollar';
	}

	// #8617 - Показывать ли уведомление "...платежная система Юнитпей перестает работать с WMR..."
	var _wmrPopupBalanceNotice = Cookies.get('wmrBalanceNoticeViewed') && ((Math.floor(Date.now() / 1000) - Cookies.get('wmrBalanceNoticeViewed')) > 86400)
		? ''
		: '<div class="js-wmr-popup-balance__notice hidden color-gray f12 lh16 mt10 mb10">В связи с тем, что WebMoney ограничила работу с кошельками WMR, пополнение возможно только с кошелька WMZ.</div>';

    var content = '' +
            '<h1 class="popup__title">' + titleText + '</h1>'+
            '<hr class="gray mt20 balance-popup__line">'+
            '<div class="balance-popup__description">' +
				descText +
            '</div>'+
            '<div class="balance-refill-form-js" id="foxForm">'+
                '<form id="popup_balance_refill_form" class="js-popup-balance__form m-text-center" method="post" action="' + ORIGIN_URL + '/balance">'+
                    '<div id="balance_popup_content">' +
                        '<table'+ ((payPalIsUsed && actor_lang != 'ru') ? ' class="paypal-mode"' : '') + '>' +
                            paymentChoose +
                            '<tr class="bl-amount' + amountMode + '" style="height: 40px;">' +
                                '<td class="w19p"><div class="balance-popup__table-title">' + (actor_lang == 'ru' ? 'Сумма' : t('Сумма')) + '</div></td>' +
                                '<td>' +
                                    '<input name="action_type" value="refill" type="hidden" />'+
                                    fromBlock +
                                    paymentIdBlock +
									orderIdBlock +
                                    '<input name="sum" class="balance-popup__input text styled-input only-number js-balance-popup_input" ' + amountBlock + ' placeholder="' + (actor_lang == 'ru' ? 'Введите сумму' : t('Введите сумму')) + '" required>'+
                                '</td>' +
                            '</tr>' +
							'<tr class="eu-card-block hidden ' + payment + '" style="height: 40px;">' +
								'<td class="w19p"><div class="balance-popup__table-title"></div></td>' +
								'<td>' +
									'<div class="choose-card-type">' +
										'<p><b>' + t('Регион выпуска карты') + '</b></p>' +
										'<p><label for="type-epayment"><input class="payment-system" name="paymentSystem" type="radio" value="epayments" id="type-epayment" /><span>' + t('Европейский союз') + '</span></label></p>' +
										'<p><label for="type-unitpay"><input class="payment-system" name="paymentSystem" type="radio" value="unitpay" id="type-unitpay" /><span>' + t('Другое') + '</span></label></p>' +
									'</div>' +
									'<div class="region-error hidden red-error">' + t('Пожалуйста выберите регион') + '</div>' +
								'</td>' +
							'</tr>' +
                            '<tr class="bill_result_popup_block hidden" style="height: 40px;">' +
                                '<td class="w19p"><div class="balance-popup__table-title">' + (actor_lang == 'ru' ? 'Итого' : t('Итого')) + '</div></td>' +
                                '<td class="bill_result_popup_amount t-align-l"></td>' +
                            '</tr>' +
                        '</table>' +
                        '<div class="js-popup-balance__notice hidden color-gray f12 lh16 mt10 mb10">' + 'Пополнение по безналичному переводу возможно на сумму не менее 10 000 руб. Комиссия при пополнении счета через банк составляет ' + BILL_COMISSION + '%</div>'+ _wmrPopupBalanceNotice +
                        '<div class="balance_refill_wait_block" style="display: none;">' +
                            '<div>' + t('Ожидание подтверждения платежной системы...') + '</div>' +
                            '<div style="height: 50px;position:relative;"><div class="preloader__ico preloader__ico_small" style="top: 5px; left: 189px;"></div></div>' +
                        '</div>' +
					'</div>' +
					"<input type=\"submit\" class=\"js-popup-balance__submit popup-balance__submit popup-balance__submit-disabled hugeGreenBtn GreenBtnStyle h50 pull-reset wMax v-align-m\" value=\"" + confirmText + "\" onclick=\"if (typeof(yaCounter32983614) !== 'undefined'){ yaCounter32983614.reachGoal('START-REFILL');} " + balanceSwitchToCheck + "\">" +
					errorOutput +
					securityTip +
				'</form>' +
            '</div>';
            show_popup(content, 'balance-popup balance-popup-modal');

	$('.popup-balance__submit').on('click', function (e) {
		e.preventDefault();
		GTM.pushDataLayer({
			'event': 'checkoutOption',
			'ecommerce': {
				'checkout_option': {
					'actionField': {
						'step': 2,
						'option': 'ConfirmAndPay'
					}
				}
			}
		});

		var $gtm_event = $('.js-balance-popup-select option:selected').data('gtm-event') || null;
		if ($gtm_event) {
			GTM.pushDataLayer({
				'event': $gtm_event
			});
		}

		setTimeout(function () {
			$('#popup_balance_refill_form').submit();
		}, 1000);
	});

			$(".only-number").ForceNumericOnly();

			var chooseMethod = function(name, saved) {
				$('.js-pay-method').val(name);
				$('.balance-popup-modal .bl-error').addClass('hidden');
				$('.balance-popup-modal .bl-methods > div').removeClass('active').addClass('inactive');
				var card = $('.balance-popup-modal .bl-methods > div[data-method="' + name + '"]');
				card.addClass('active').removeClass('inactive');
				if (saved) {
					card.addClass('saved');
				}
				if (name == 'card') {
					$('.balance-popup-modal .bl-security-tip').show();
				} else {
					$('.balance-popup-modal .bl-security-tip').hide();
				}
				var modeName = card.data('name');
				$('.balance-popup-modal .js-popup-balance__submit').removeClass('disabled').prop('disabled', false);
			};

			if (payPalIsUsed && actor_lang != 'ru') {
				$('.balance-popup-modal .js-popup-balance__submit').addClass('disabled').prop('disabled', true);
				var lastMethod = $(".user-payment-saved").val();
				if (lastMethod) {
					chooseMethod(lastMethod, true);
				}

				$('.balance-popup-modal .bl-methods > div').on('click', function(e) {
					chooseMethod($(e.delegateTarget).data('method'));
				});

				$('#popup_balance_refill_form').on('submit', function(e) {
					var method = $('.js-pay-method').val();
					if (!method) {
						$('.balance-popup-modal .bl-error').removeClass('hidden');
						e.preventDefault();
						return false;
					}
				});
			}

            $('.js-balance-popup-select').on('change', function(){
				if($(this).val() != 'card'){
					$(".eu-card-block").addClass("hidden");
				}else{
					if(!$(".user-payment-method").val()){
						$(".eu-card-block").removeClass("hidden");
					}
				}

                if($(this).val() == 'bank'){
                    $('.js-popup-balance__submit').val('Выписать счет').data('block-check', 1);
                    $('.js-popup-balance__form').attr('action', ORIGIN_URL + '/bill_get');
                    $('.js-popup-balance__notice').removeClass('hidden');
                    $('.js-wmr-popup-balance__notice').addClass('hidden');
                    $('.balance-popup__input').addClass('js-min').data('min-value', 10000);
                    if (actor_lang == 'ru') {
						$('.bill_result_popup_amount').text(getBillAmount($(".js-popup-balance__form input[name='sum']").val()) + ' руб.');
					} else {
						$('.bill_result_popup_amount').text(t('{{0}} руб.', [getBillAmount($(".js-popup-balance__form input[name='sum']").val())]));
					}
                    $('.bill_result_popup_block').removeClass('hidden');
                } else {
                    $('.js-popup-balance__submit').val((actor_lang == 'ru' ? 'Пополнить баланс' : confirmText)).data('block-check', 0);
                    $('.js-popup-balance__form').attr('action', ORIGIN_URL + '/balance');
                    $('.js-popup-balance__notice').addClass('hidden');
                    $('.js-wmr-popup-balance__notice').addClass('hidden');
                    $('.balance-popup__input').removeClass('js-min');
                    $('.bill_result_popup_block').addClass('hidden');
					if (['paymore_webmoney','webmoney'].includes($(this).val())) {
						$('.js-wmr-popup-balance__notice').removeClass('hidden');
						// #8617 - Выставляем метку о просмотренном уведомлении "...платежная система Юнитпей перестает работать с WMR..."
						if (!Cookies.get('wmrBalanceNoticeViewed')) {
							Cookies.set('wmrBalanceNoticeViewed', Math.floor(Date.now() / 1000), {
								expires: 90,
								path: '/',
								SameSite: 'Lax'
							});
						}
					}
                }
            });
}


function showBillFormPopup(sum) {
    $('.popup').remove();
    $.ajax({
        url:'/api/bill/getform',
        type:'post',
        data:{'sum': sum},
        dataType:'json',
        success:function(response) {
            if (response.result === 'success') {
            	var popupClass = '';
            	if (jQuery.browser.ios === true) {
					popupClass = 'popup-bill_ios';
				}
                show_popup(response.html, 'bill-form-popup', '', popupClass, true);
                billFormSetEvents();
            }
        }
    });
}


function validationBalancePopup() {
	var $button = $('.js-popup-balance__submit'),
		$input =  $('.js-balance-popup_input');

	if($input.val() && $input.val().length) {
		$button.removeClass('popup-balance__submit-disabled');
		return true;
	} else {
		$button.addClass('popup-balance__submit-disabled');
		return false;
	}
}


var timeout = 0;

$(document).on('keyup paste blur', '.js-balance-popup_input', function(){
	if (actor_lang == 'ru') {
		$('.bill_result_popup_amount').text(getBillAmount($(this).val()) + ' руб.');
	} else {
		$('.bill_result_popup_amount').text(t('{{0}} руб.', [getBillAmount($(this).val())]));
	}

	clearTimeout(timeout);
	timeout = setTimeout(function() {
		var valid = validationBalancePopup();
	}, 100)
});

$(document).on('submit', 'form', function(e){
	var proceed = true;
	if ($(this).find('input.js-min').length > 0) {
		$(this).find('input.js-min').each(function(){
			if ($(this).val() < $(this).data('min-value')) {
				$(this).addClass('min-value-error');
				e.preventDefault();
				proceed = false;
				return false;
			}else{
				$(this).removeClass('min-value-error');
			}
		});
	}
	if (!proceed) {
		return false;
	}
	if ($(this).attr('id') === 'popup_balance_refill_form') {
		if(!validationBalancePopup()) {
			return false;
		}
		/* Временно скрываем
		if(!$(".payment-system:checked").val() && !$(".user-payment-method").val()){
			$(".region-error").removeClass("hidden");
			e.preventDefault();
			return false;
		}else{
			$(".region-error").addClass("hidden");
		} */
		if ($(this).find('.js-balance-popup-select').val() === 'bank') {
			showBillFormPopup($(this).find("input[name='sum']").val());
			e.preventDefault();
			return false;
		}
		// обязательно после проверки на bank, если есть paymentId то отправлять форму не нужно, она отправляется функцией balance_popup_switch_to_check
		if($(this).find('#payment_id').length) {
			e.preventDefault();
			return false;
		}
	}
});

var recentFormToSubmit = false;
var balancePopupInterval = false;
var balanceRefillWindow = false;
function balance_popup_switch_to_check(submitButton){
    if ($(submitButton).data('block-check') == 1) {
        return true;
    }
    var formData = $("#popup_balance_refill_form").clone();
    var selectedType = $("#popup_balance_refill_form").find('.js-balance-popup-select').val();
    $(formData).hide();
    $("#balance_popup_content table").hide();
    $(submitButton).hide();
    $("#balance_popup_content .balance_refill_wait_block").show();

    clearInterval(balancePopupInterval);
    balancePopupInterval = setInterval(function(){
        var paymentId = $("#balance_popup_content table #payment_id").val();
        if(paymentId){
	        check_balance_payment_payed(paymentId);
        } else {
	        clearInterval(balancePopupInterval);
        }
    }, 10000);

    balanceRefillWindow = window.open("/");
    $(balanceRefillWindow.document.body).append('<div>'+t('Перенаправление на страницу платежной системы...')+'</div>');
    $(formData).find('.js-balance-popup-select').val(selectedType);
    $(balanceRefillWindow.document.body).append(formData);
    $(balanceRefillWindow.document.body).find("#popup_balance_refill_form").submit();
    return false;
}

function check_balance_payment_payed(paymentId){
    $.ajax({
        url:'/api/user/ispaymentpayed',
        type:'post',
        data:{'payment_id': paymentId},
        dataType:'json',
        success:function(response) {
            if (response.result == 'success') {
                balanceRefillWindow.close();
                $(recentFormToSubmit).submit();
                clearInterval(balancePopupInterval);
            }
        }
    });
}

$(document).on('mouseup touchend', function() {
	if (disableCurrentClick) {
		setTimeout(function() {
			disableCurrentClick = false;
		}, 200);
	}
});

$(document).on('click','.login-js',function(e){
	if (disableCurrentClick) {
		return false;
	}
	/* Показываем попап входа */
	e.preventDefault();
    if($(this).parents('form').attr('data-submit-post') === 'true'){
        show_login(true);
    }
    else{
        show_login();
    }

})

function getTrackClientId() {
    try {
        var trackers = ga.getAll();
        var i, len;
        for (i = 0, len = trackers.length; i < len; i += 1) {
            if (trackers[i].get('trackingId') === 'UA-68703836-1') {
                return trackers[i].get('clientId');
            }
        }
    } catch(e) {

    }
    return 'false';
}
function getRecaptchaKey() {
    return '6LdX9CATAAAAAARb0rBU8FXXdUBajy3IlVjZ2qHS';
}

function getRecaptchaField() {
    if (typeof (isNeedShowRecaptcha) === 'undefined' || isNeedShowRecaptcha === false) {
        return '';
    }
    return '<div class="g-recaptcha form-entry" data-sitekey="' + getRecaptchaKey() + '"></div><script>reinitRecaptcha();</script>';
}

function getRecaptchaFieldRegisterPopup() {
    return '<div class="g-recaptcha form-entry" data-sitekey="' + getRecaptchaKey() + '"></div><script>reinitRecaptcha(true);</script>';
}

function reinitRecaptcha(forseShow) {
    var isForseShow=false;
	if (typeof (forseShow) !== 'undefined' && forseShow) {
		isForseShow = true;
	}
	if ((typeof (isNeedShowRecaptcha) === 'undefined' || isNeedShowRecaptcha === false) && isForseShow === false) {
		return '';
	} else {
		if (typeof (grecaptcha) === 'undefined') {
			var script = document.createElement('script');
			var src = "https://www.google.com/recaptcha/api.js";
			src += "?onload=onloadRecapcha&render=explicit";
			if (lang) {
				src += "&hl=" + lang;
			}
			script.src = src;
			script.async = false;
			document.head.appendChild(script);
		} else {
			onloadRecapcha();
		}
    }
}

/**
 * Функция, выполняемая после загрузки скрипта recaptcha
 */
function onloadRecapcha() {
	$('.g-recaptcha').each(function(){
		if (!$(this).data('fixed')) {
			var recaptchaWidgetId = $(this).data('recaptchaWidgetId');
			if (typeof recaptchaWidgetId === 'undefined') {
				var recaptchaWidgetId = grecaptcha.render($(this)[0], {sitekey: getRecaptchaKey()});
				$(this).data('recaptchaWidgetId', recaptchaWidgetId);
			}
			grecaptcha.reset($(this).data('recaptchaWidgetId'));
		}
	});
}

/**
 * Обновить капчу
 * @param recaptcha_show Надо ли показывать капчу
 */
function processRecapchaShow(recaptcha_show) {
	if (recaptcha_show) {
		isNeedShowRecaptcha = true;
	}
	if ((typeof(isNeedShowRecaptcha) !== 'undefined' && isNeedShowRecaptcha) && !$('.g-recaptcha').is("div")) {
		var captchaBlock = getRecaptchaField();
		$("div.recaptcha_holder").before(captchaBlock);
	} else {
		reinitRecaptcha();
	}
}

/**
 * Получаем исходник формы авторизации
 *
 * @param actionAfter
 * @param defaultEmail
 * @param isIndexPage
 * @return {string}
 */
function get_login_html(actionAfter, defaultEmail, isIndexPage) {
	if (typeof(yaCounter32983614) !== 'undefined') {
		yaCounter32983614.reachGoal('LOGIN-START');
	}

	var actionAfterBlock = '';
	if (typeof(actionAfter) !== 'undefined' && actionAfter != '') {
		actionAfterBlock = '<input type="hidden" name="action_after" value="' + actionAfter + ' ">';
		onclickBlock = 'onclick="show_signup(\'' + actionAfter +  '\'); return false;"';
	} else {
		onclickBlock = 'onclick="show_signup(); return false;"';
	}

	if (isIndexPage == 1) {
		onclickBlock = 'onclick="showSignUpForm(); return false;"';
	}

	var actionUrlBlock = '';
	if (IS_MIRROR) {
		if (CANONICAL_ORIGIN_URL != ORIGIN_URL && CANONICAL_ORIGIN_URL != (ORIGIN_URL + '/login') && CANONICAL_ORIGIN_URL != (ORIGIN_URL + '/signup')) {
			actionUrlBlock = 'action="' + CANONICAL_ORIGIN_URL + '"';
		} else {
			actionUrlBlock = 'action="' + ORIGIN_URL + '"';
		}
	}

	var socialLogin = "";
	var formFooter = '';
	var popupFooter = '';
	if (lang == 'en') {
		socialLogin = ''
			+ '<a href="' + ORIGIN_URL + '/login_soc?type=fb" style="max-width:100%" class="fb" onclick="return socialFbLogin();" title="' + t('Войти через Facebook') + '">'
				+ '<span>' + '<i class="fa fa-facebook-official"></i>' + t('Facebook') + '</span>'
			+ '</a>';
	} else {
		socialLogin = ''
			+ '<a href="' + ORIGIN_URL + '/login_soc?type=fb" class="fb pull-right" onclick="return socialFbLogin();" title="' + t('Войти через Facebook') + '">'
				+ '<span>' + '<i class="fa fa-facebook-official"></i>' + t('Facebook') + '</span>'
			+ '</a>'
			+ '<a href="' + ORIGIN_URL + '/login_soc?type=vk" class="vk" onclick="return socialVkLogin();" title="' + t('Войти через Вконтакте') + '">'
				+ '<span>' + '<i class="fa fa-vk"></i>' + t('ВКонтакте') + '</span>'
			+ '</a>';

		formFooter = ''
			+ '<div class="popup-footer">'
			+ '<span class="color-gray">' + t('Не зарегистрированы?') + ' </span>'
			+ '<a class="color-text underline f14 cur" ' + onclickBlock + '>' + t('Зарегистрироваться') + '</a>'
			+ '</div>';

		popupFooter = ''
			+ '<div class="flex-column__footer m-visible">'
			+ '<span class="color-gray">' + t('Нет аккаунта?') + ' </span>'
			+ '<a class="green-btn popup__button_theme_footer" ' + onclickBlock + '>' + t('Зарегистрироваться') + '</a>'
			+ '</div>';
	}

	var trackClientId = getTrackClientId();
	var trackClientBlock = '<input type="hidden" name="track_client_id" value="' + trackClientId + '" />';
	var captchaBlock = getRecaptchaField();

	var foxForm = ''
	+ '<div id="foxForm">'
		+ '<form id="form-login" ' + actionUrlBlock + '>'
			+ actionAfterBlock
			+ trackClientBlock
			+ '<div class="popup-form-container-step1">'
				+ '<div>'
					+ '<div class="form-entry mb10">'
						+ '<input ' + (typeof defaultEmail == 'string' ? ' value="' + defaultEmail + '" ' : '') + 'class="js-signin-input text styled-input wMax h40 lh40 f15 noInvalidOutline" placeholder="' + t('Электронная почта или логин') + '" id="l_username" name="l_username" tabindex="1" type="email" >'
						+ '</div>'
						+ '<div class="form-entry">'
							+ '<input class="js-signin-input text styled-input wMax f15 h40 lh40" id="l_password" placeholder="' + t('Пароль') + '" name="l_password" size="30" tabindex="2" type="password" />'
						+ '</div>'
						+ captchaBlock
						+ '<div class="recaptcha_holder"></div>'
						+ '<div class="form-entry">'
							+ '<button class="green-btn mt0 h45 popup__button popup__button_theme_orange" formnovalidate>'
								+ '<span class="no-style">' + t('Войти') + '</span>'
							+ '</button><br>'
							+ '<div class="color-orange f14 form-entry-error mt10 m-mt0"></div>'
							+ '<input type="hidden" name="jlog" id="jlog" value="1" />'
						+ '</div>'
						+ '<input type="hidden" name="r" />'
					+ '</div>'
					+ '<div class="clearfix popup__additional-links">'
						+ '<a href="/forgotpassword" class="dib f14 color-text underline pull-right" style="line-height:24px;">'
							+ t('Забыли пароль?')
						+ '</a>'
						+ '<div class="pull-left options m-hidden">'
							+ '<input class="checkbox dib" id="l_remember_me" name="l_remember_me" type="checkbox" value="1" checked />'
							+ '<label class="f14 mb0" for="l_remember_me">'
								+ t('Запомнить меня')
							+ '</label>'
						+ '</div>'
					+ '</div>'
					+ '<div class="form-entry-middle-popup t-align-c mb5"><span>' + t('или войти через') + '</span></div>'
					+ '<div class="t-align-c s-btn s-btn_no-bg clearfix">'+ socialLogin + '</div>'
				+ '</div>'
				+ formFooter
				+ '<div class="clear"></div>'
		+ '</form>'
	+ '</div>';

	return html = ''
		+ '<div class="flex-column">'
			+ '<div class="flex-column__content">'
				+ '<div class="m-visible">'
			 		+ '<a href="javascript:;" onclick="mobile_menu_toggle();" class="popup__menu_theme_mobile">' + '<i class="fa fa-bars"></i>' + '</a>'
					+ '<a href="javascript:;" class="kwork-icon icon-close popup__close_theme_mobile popup-close-js popup-instant-close-js"></a>'
					+ '<div class="popup__title_theme_mobile">' + t('Авторизация') + '</div>'
					+ '<div class="popup__logo"></div>'
				+ '</div>'
				+ '<div class="m-hidden">'
					+ '<h1 class="popup__title">' + t('Вход') + '</h1>'
					+ '<hr class="gray mt15 mb15 popup__hr">'
				+ '</div>'
				+ foxForm
			+ '</div>'
			+ popupFooter
		+ '</div>';
}

/**
 * авторизация через вконтакте
 * @returns {boolean}
 */
function socialVkLogin() {
	if (typeof(yaCounter32983614) !== 'undefined'){ yaCounter32983614.reachGoal('LOGIN'); }
	return true;
}

/**
 * авторизация через facebook
 * @returns {boolean}
 */
function socialFbLogin() {
	if (typeof(yaCounter32983614) !== 'undefined'){ yaCounter32983614.reachGoal('LOGIN'); }
	return true;
}

/**
 * регистрация через вконтакте
 * @returns {boolean}
 */
function socialVkSignUp() {
	if (typeof(yaCounter32983614) !== 'undefined') yaCounter32983614.reachGoal('SIGNUP');
	return true;
}

//блокировка/разблокировка скроллбара документа
function changeBodyScrollbar(type) {
	var body = $('body');
	if (type === 'lock') {
		body.addClass('compensate-for-scrollbar-m');
	} else if (type === 'unlock') {
		body.removeClass('compensate-for-scrollbar-m');
	}
}

/**
 * регистрация через facebook
 * @returns {boolean}
 */
function socialFbSignUp() {
	if (typeof(yaCounter32983614) !== 'undefined') yaCounter32983614.reachGoal('SIGNUP');
	return true;
}
function show_login(actionAfter, successCallback, defaultEmail){
    var html = get_login_html(actionAfter, defaultEmail);

    //блокируем скролл документа на мобильных
	changeBodyScrollbar('lock');

	//вызываем попап
    show_popup(html,'popup-login_content','','popup-login');

	$('.popup #l_username').trigger('focus');

    if(typeof successCallback == 'function'){
        $('#form-login').data('successCallback', successCallback);
    }
}

/**
 * Получаем код окна регистрации
 *
 * @param actionAfter
 * @param isWorker
 * @param registerWithCaptcha
 * @param isIndexPage
 * @param isSimple
 * @param simpleText
 * @return {string}
 */
function get_signup_html(actionAfter, isWorker, registerWithCaptcha, isIndexPage, isSimple, simpleText){
	if (typeof(yaCounter32983614) !== 'undefined') {
		yaCounter32983614.reachGoal('REG-START');
	}

	var actionAfterBlock = '';
	if (typeof(actionAfter) !== 'undefined' && actionAfter != '') {
		actionAfterBlock = '<input type="hidden" name="action_after" value="' + actionAfter + '">';
		onclickBlock = 'onclick="show_login(\'' + actionAfter + '\'); return false;"';
	} else {
		onclickBlock = 'onclick="show_login(); return false;"';
	}
	if (isIndexPage == 1) {
		onclickBlock = 'onclick="showAuthForm(); return false;"';
	}

	var actionUrlBlock = '';
	if (IS_MIRROR) {
		if (CANONICAL_ORIGIN_URL != ORIGIN_URL) {
			actionUrlBlock = 'action="' + CANONICAL_ORIGIN_URL + '"';
		} else {
			actionUrlBlock = 'action="' + ORIGIN_URL + '"';
		}
	}

	var trackClientId = getTrackClientId();
	var trackClientBlock = '<input type="hidden" name="track_client_id" value="' + trackClientId + '" />';
	var captchaBlock = '';
	if (registerWithCaptcha) {
		captchaBlock = ''
			+ '<div class="form-entry signup-step-2-js signup-captcha-field" style="display:none;">'
				+ getRecaptchaFieldRegisterPopup() + '<div class="error"></div>'
			+ '</div>';
	}

	var socialLogin = "";
	var contriesHtml = '', countrySelectBlock = '';
	if (lang == 'en') {
			socialLogin = ''
				+ "<a href='" + ORIGIN_URL + "/login_soc?type=fb&usertype=" + (isWorker ? '2' : '1') + "' style='max-width:100%' class='fb' onclick=\"return socialFbSignUp()\" title=\'" + t('Войти через Facebook') + "\'><span>" + t('Facebook') + "</span></a>";

		// countryList
		$.each(countryList, function(k, v) {
			contriesHtml += '<option value="' + v.id + '">' + v.name_en + '</option>';
		});
		if (contriesHtml) {
			countrySelectBlock += ''
				+ '<div class="country-select-block signup-step-2-js" style="display: none;">'
					+ '<div class="form-entry signup-country-field">'
						+ '<div class="signup-country-field-row">'
							+ '<div>Choose your country</div>'
							+ '<span class="kwork-icon icon-down-arrow cur m-visible"></span>'
							+ '<select class="input input_size_s" name="country" data-placeholder=" ">'
								+ '<option value="">Choose your country</option>'
								+ contriesHtml
							+ '</select>'
						+ '</div>'
						+ '<div class="error"></div>'
					+ '</div>'
					+ '<div class="form-entry signup-country-warning" style="display: none;">'
						+ '<i class="icon ico-warningSmall"></i> You can start working right now. But withdrawal to your country will be available in 2 months. Before this earned money will be saved on your account.'
						+ '</div>'
				+ '</div>';
		}
	} else {
		socialLogin = ''
			+ "<a href='" + ORIGIN_URL + "/login_soc?type=fb&usertype=" + (isWorker ? '2' : '1') + "' class='fb pull-right' onclick=\"return socialFbSignUp()\" title=\'" + t('Войти через Facebook') + "\'><span>" + t('Facebook') + "</span></a>"
			+ "<a href='" + ORIGIN_URL + "/login_soc?type=vk&usertype=" + (isWorker ? '2' : '1') + "' class='vk' onclick=\"return socialVkSignUp()\" title=\'" + t('Войти через Вконтакте') + "\'><span>" + t('ВКонтакте') + "</span></a>";
	}

	setMobileClass();

	// FORM
	var form = ''
		+ '<form method="post" id="form-signup" ' + actionUrlBlock + '>'
			+ actionAfterBlock
			+ trackClientBlock
			+ '<div class="popup-form-container-step1">';

	if (!isSimple) {
		// для простой формы убираем элементы выбора Buyer / Seller
		form += ''
			+ '<div class="select-user-type mb20 clearfix signup-step-1-js mt17">'
				+ '<div class="dib pull-right' + (isWorker ? ' active' : '') + '">'
					+ '<input name="userType" type="radio" onclick="changeSocLink(2);" id="2" value="2" class="worker" ' + (isWorker ? 'checked' : '') + '/>'
					+ '<label for="2">' + t('Продавец') + '</label>'
				+ '</div>'
				+ '<div class="dib' + (isWorker ? '' : ' active') + '">'
					+ '<input name="userType" type="radio" onclick="changeSocLink(1);" id="1" value="1" class="payer" ' + (isWorker ? '' : 'checked') + ' />'
					+'<label for="1">' + t('Покупатель') + '</label>'
				+ '</div>'
			+ '</div>';
	} else {
		// для простой регистрации тип по умолчанию "Покупатель"
		if (simpleText) {
			form += '<div class="text-center f14 mb15 signup-simple-text">' + simpleText + '</div>';
		}
		var userRoles = isWorker != undefined && isWorker != null && isWorker === true ? 2 : 1;
		form += '<input type="hidden" name="userType" value="' + userRoles + '">';
	}

	form += ''
				+ '<div>'
					+ '<div id="foxForm">'
						+ '<div class="form-entry signup-step-1-js" >'
							+ '<input class="text styled-input wMax f15 h40 lh40" id="user_email" placeholder="' + t('Электронная почта') + '" name="user_email" size="30" type="email" required />'
					+ '</div>'
					+ '<div class="form-entry signup-step-2-js signup-login-field" style="display:none;">'
						+ '<div class="preloader__ico preloader__ico_small"></div>'
						+ '<input class="text h40 lh40 styled-input wMax f15" placeholder="' + t('Ваш логин (будет виден всем)') + '" name="user_username" size="15" type="text" />'
						+ '<div class="error"></div>'
					+ '</div>'
					+ '<div class="form-entry signup-step-2-js signup-password-field" style="display:none;">'
						+ '<input class="text h40 lh40 styled-input wMax f15" placeholder="' + t('Пароль') + '" id="user_password" name="user_password" size="30" type="password" autocomplete="off" />'
						+ '<div class="error"></div>'
					+ '</div>'
					+ countrySelectBlock
					+ '<div class="form-entry signup-promo-field" style="display:none;">'
						+ '<input class="text h40 lh40 styled-input wMax f15" placeholder="' + t('Введите промокод') + '" id="user_promo" name="user_promo" size="30" type="text" autocomplete="off"/>'
						+ '<div class="error"></div>'
					+ '</div>'
					+ captchaBlock
					+ '<div class="recaptcha_holder"></div>'
					+ '<div class="form-entry mb0">'
						+ '<button type="button" class="mt0 green-btn popup__button h45 next-signup-js signup-step-1-js popup__button_theme_next">' + t('Далее') + '</button>'
						+ '<a class="mt10 mb15 signup-step-2-js signup-promo-placeholder signup-promo-placeholder-js" style="display:none;">' + t('У меня есть промокод') + '</a>'
						+ '<input type="submit" value="' + t('Зарегистрироваться') + '" class="green-btn popup__button_reg h45 signup-step-2-js" style="display:none;">'
						+ '<br class="m-visible">'
						+ '<div class="color-orange font-OpenSans f14 form-entry-error mt10"></div>'
						+ '<input type="hidden" name="jsub" id="jsub" value="1" />'
					+ '</div>'
				+ '</div>'
				+ '<div class="terms-links m-visible">'
					+ t('Регистрируясь, вы принимаете<br> {{0}}Правила сайта{{1}} и {{2}}Договор-оферту{{3}}', ['<a href="/terms_of_service" target="_blank" class="color-text underline">', '</a>', '<a href="/terms" target="_blank" class="color-text underline">', '</a>'])
				+ '</div>'
				+ '<div class="form-entry-middle-popup font-OpenSans t-align-c signup-step-1-js mt35 mb5 m-hidden">'
					+ '<span>' + t('или войти через') + '</span>'
				+ '</div>'
				+ '<div class="t-align-c s-btn clearfix signup-step-1-js m-hidden">'
					+ socialLogin
				+ '</div>'
			+ '</div>'
		+ '</div>'
		+ '<div class="popup-footer signup-step-1-js">'
			+ '<span class=" color-gray">' + t('Уже зарегистрированы?') + ' </span>'
			+ '<a class="color-text underline" ' + onclickBlock + ' style="cursor:pointer;">' + t("Войти в {{0}}", [CURRENT_APP_DESCRIPTION]) + '</a>'
		+ '</div>'
		+ '<div class="popup-footer f12 ta-center signup-step-2-js"  style="display:none;">'
			+ t('Регистрируясь, вы принимаете {{0}}Пользовательское соглашение{{1}} и соглашаетесь на email-рассылки', ["<a href=\"/terms\" target=\"_blank\" class=\"color-text underline\">", "</a>"])
		+ '</div>'
		+ '<div class="clear"></div>'
	+ '</form>';
	// end FORM

	var html = ''
		+ '<div class="flex-column">'
			+ '<div class="flex-column__content">'
				+'<a href="javascript:;" onclick="mobile_menu_toggle();" class="popup__menu_theme_mobile m-visible">'
					+ '<i class="fa fa-bars"></i>'
				+ '</a>'
				+ '<a href="javascript:;" class="kwork-icon icon-close popup__close_theme_mobile popup-close-js popup-instant-close-js m-visible"></a>'
				+ '<div class="popup__title_theme_mobile m-visible">' + t('Регистрация') + '</div>'
				+ '<div class="popup__logo m-visible"></div>'
				+ '<h1 class="popup__title m-hidden">' + t('Регистрация') + '</h1>'
				+ '<hr class="gray mt15 mb15 popup__hr m-hidden">'
				+ form
			+ '</div>'
			+ '<div class="flex-column__footer m-visible">'
				+ '<span class="color-gray">' + t('Уже зарегистрированы?') + ' </span>'
				+ '<a class="green-btn popup__button_theme_orange popup__button_theme_footer" ' + onclickBlock + '>' + t('Войти') + '</a>'
			+ '</div>'
		+ '</div>';

	if (typeof(isSimple) != "undefined" && isSimple == 1) {
		html += '<input type="hidden" id="is_simple_signup" value="1">';
	}

	return html;
}

function setSelectedPackageType(selectedPackageType){
	if (typeof(selectedPackageType) != "undefined" && selectedPackageType !== null && selectedPackageType != '') {
		tempSelectedPackageType = selectedPackageType;
	} else {
		tempSelectedPackageType = "";
	}
}

function show_simple_signup(actionAfter, selectedPackageType) {
	show_signup(actionAfter, 0, null, 1, selectedPackageType);
	$("div.popup-form-container-step1").css('min-height', 200); // подправим высоту простой формы регистрации
}

/**
 * @param e
 */
function checkCountriesWithoutWithdrawal(e) {
	var t = $(e.delegateTarget);
	var sw = $('.popup .signup-country-warning');
	if ($.inArray(parseInt(t.val()), countriesWithoutWithdrawal) !== -1) {
		sw.show();
	} else {
		sw.hide();
	}
}

/**
 * @param actionAfter
 * @param isWorker
 * @param registerWithCaptcha
 * @param isSimple
 * @param selectedPackageType
 * @param simpleText Текст сообщения если isSimple = true
 */
function show_signup(actionAfter, isWorker, registerWithCaptcha, isSimple, selectedPackageType, simpleText){
	var html = get_signup_html(actionAfter, isWorker, registerWithCaptcha, 0, isSimple, simpleText);

	setSelectedPackageType(selectedPackageType);

	//блокируем скролл документа на мобильных
	// changeBodyScrollbar('lock');

	show_popup(html, 'popup-signup_content', '', 'popup-signup');
	initSingupHandlers();

	var $selects = $('.popup .signup-country-field select');
	if (jQuery.fn.chosenCompatible) {
		$selects.chosenCompatible("destroy");
		$selects.chosenCompatible({
			width: '55%',
			disable_search: true
		}).change(function (e) {
			checkCountriesWithoutWithdrawal(e);
		});
	} else {
		$selects.on('change', function (e) {
			checkCountriesWithoutWithdrawal(e);
		});
	}

	$('.signup-country-field').find('.chosen-results').unbind('mousewheel');

    $('#form-signup').find('a.vk, a.fb').each(function(i) {
        $(this).attr('href', $(this).attr('href') + addRefParam());
    });
}

function show_wm_info_popup(sum){
    var html = ''+
        '<div class=" m-center-block m-text-center mt30">'+
            '<h1 class="font-OpenSansBold f36 mb10"></h1>'+
            '<div class="mb20 mt20">' + t('В связи с тем, что трудности WebMoney в обслуживании клиентов продолжаются, и наблюдаются не только <a target="_blank" href="https://www.gazeta.ru/business/news/2016/05/11/n_8617313.shtml">в России</a>,') +
        t('но и <a target="_blank" href="http://interfax.com.ua/news/economic/382057.html">на Украине</a>, осуществлять выводы на кошельки WM становится все сложнее. Для того чтобы сохранить возможность вывода на WebMoney для наших пользователей, мы вынуждены выбирать оптимальные варианты и подстраиваться под меняющиеся условия платежных систем. В связи изменениями в тарифной сетке платежной системы с 14 ноября 2016 года комиссия при выводе средств на кошельки WebMoney составляет 4%%.') +
                                '<p class="mb20 mt20">' + t('Мы по-прежнему рекомендуем вам использовать для вывода средств Qiwi-кошельки. Также спешим сообщить, что по многочисленным просьбам пользователей с 14 ноября появилась возможность вывода денежных средств на карты MasterCard и VISA.') + '</p>' +
                                '</div>' +
                                '<p class="mb20 mt20">' + t('По всем возникающим вопросам вы можете обращаться в <a href = "/contact">Службу поддержки.</a>') + '</p>' +
         '</div>'+
        '<div class="clear"></div>';
    show_popup(html, 'l-popup');
}

/**
 * Открытие попапа после добавления/редактивания задания на бирже
 * @param status
 */
function showWantStatusPopup(status) {
	var html = '';

	html += '<h1 class="popup__title">' + t('Поздравляем! Ваше задание ' + (status === 'active' ? 'опубликовано' : 'создано')) + '</h1>' +
		'<hr class="gray mt15 mb15 popup__hr">';

	html += t('Сотни исполнителей увидят ваше задание на бирже. Что дальше?');

	window.history.pushState({urlPath:'manage_projects'}, '', 'manage_projects');
	show_popup(html, 'popup-projects_content', '', 'popup-projects');
}

function moder_reject_validate(target)
{
    var reason = target.find('#reason').val();
    reason = reason.replace(/ /gi,'');
    if(reason.length == 0)
    {
        alert(t('Введите причину отклонения'));
        return false;
    }
    return true;
}
function show_change_email_popup(email){

    var html = ''+
    '<h1 class="popup__title">' + t('Подтверждение email') + '</h1>'+
    '<hr class="gray mt20" style="margin-bottom:10px;">'+
    '<i class="icon ico-error mr10 pull-left"></i>'+
    t('<b>Похоже, что ваш почтовый ящик не принимает email-сообщения, которые отправляются с сайта.</b><br><br>Это может быть связано с двумя причинами:')+
    '<ul class="mb10"><li class="mt5" style="margin-left:10%;">' + t('У вас сменился адрес электронной почты') + '</li><li class="mt5" style="margin-left:10%;">' + t('Ваш спам фильтр не пропускает наши сообщения') + '</li></ul>'+
    t('<b>Что делать дальше?</b> Впишите свой корректный электронный адрес и нажмите на кнопку "Подтвердить email"<br><br>')+
    '<div id="foxForm">'+
        '<form id="form-change-email" method="post">'+
            '<div>'+
                '<div class="form-entry">'+
                    '<input class="text styled-input wMax f15" placeholder="' + t('Электронная почта') + '" id="user_email" name="user_email" tabindex="1" type="email" value="'+ email +'"/>'+
                '</div>'+
                '<div class="row form-entry">'+
                    '<input type="submit" value="' + t('Подтвердить email') + '" class="hugeGreenBtn GreenBtnStyle h50 pull-reset wMax dib-imp v-align-m " formnovalidate /><br>'+
                    '<div class=" color-orange font-OpenSans f14 form-entry-error mt10"></div>'+
                    '<input type="hidden" name="jlog" id="jlog" value="1" />'+
                '</div>'+
            '</div>'+
            '<div class="clear"></div>'+

        '</form>'+
    '</div>';
    show_popup(html, "popup-email-confirm", true);
}

var genInputId = new IDGenerator('_id');
var formData = new FormData;
function showNameFilesPopup(obj) {
    haveFiles = true;
    if (obj.files && obj.files[0]) {
        var counter = $(".file-item", "#list-files_popup").size();
        var cnt = Object.keys(obj.files).length;
        // В safari Object.keys(obj.files) добавляет в конец массива 'length'
        {
            if(Object.keys(obj.files)[cnt-1] == 'length'){
                cnt--;
            }
        }
        for (var i = 0; i < cnt; i++){
            if(!(counter + i + 1 <= config.files.maxCount)){
                alert(t('Превышено максимальное количество файлов.'));
                obj.value = '';
                return false;
            }
            if (obj.files[i].size > config.files.maxSizeReal) {
                alert(t('Размер файла не должен превышать {{0}} МБ', [config.files.maxSize]));
                obj.value = '';
                return false;
            }
        }
        for (var i = 0; i < cnt; i++){
            var fileName = obj.files[i].name;
            var size = obj.files[i].size;
            var len = fileName.length;
            var symb3 = fileName.substr(len - 3, len).toLowerCase();
            var symb4 = fileName.substr(len - 4, len).toLowerCase();
            var ico = '';
            if ($.inArray(symb3, ['doc','xls','rtf','txt']) != -1 || $.inArray(symb4, ['docx','xlsx']) != -1) {
                    ico =  'doc';
            }else if ($.inArray(symb3, ['zip','rar']) != -1){
                    ico =  'zip';
            }else if ($.inArray(symb3, ['png','jpg','gif','psd']) != -1 || $.inArray(symb4, ['jpeg']) != -1) {
                    ico =  'image';
            }else if ($.inArray(symb3, ['mp3','wav','avi']) != -1){
                    ico =  'audio';
            }else{
                    ico = 'zip';
            }
            var id = genInputId.getID();
            obj.id = 'fileInputPopup' + id;
            obj.className = 'fileInputs';
            var rightText = "";
            rightText = "<a class='remove-file-link' onclick=\"delNameFilesPopup('" + id + "')\"></a>";
            html = "<div class='mb5 file-item' id='fileNamePopup" + id + "'><i class='ico-file-"+ico+" dib v-align-m'></i><span class='dib v-align-m ml10 "+(allow?"":" color-red")+"'>" + fileName + "</span>"+rightText+"</div>";
            $("#list-files_popup").append(html);
            html = "<input id='fileInputPopup" + id + "' type='hidden' name='inputName_" + id + "' value ='sizeFile" + fileName + size + "'>";
            $("#list-files_popup").append(html);
            formData.append('fileInputPopup[]',obj.files[i]);
        }
        $(obj).after('<input onchange="showNameFilesPopup(this)" name="fileInputPopup[]" type="file" multiple/>');
        $(obj).hide();
    }
}
function delNameFilesPopup(id)
{
    $('#fileNamePopup'+id).remove();
    $('#fileInputPopup'+id).remove();
}

$(document).on('click','.short-login-js',function(e){
    /*Показываем короткую форму входа*/
    e.preventDefault();
    show_login();
});

$(document).on('click', '.signup-js', function (e) {
	/*при клике вызывать форму регистрации*/
	e.preventDefault();
	var isWorker = $(this).is('.worker');
	$.getJSON('/api/ban/disallowfreeregister', function (registerWithCaptcha) {
		show_signup('', isWorker, registerWithCaptcha);
	});

});
$(document).on('click','.start-sell-js',function(e){
    if (typeof(yaCounter32983614) !== 'undefined') {
        yaCounter32983614.reachGoal('REG-START-SELL');
    }
});
$(document).on('click', '.next-signup-js', function () {
	/*При клике далее на попапе регистрации*/
	var email = $(this).parents('form').find('#user_email').val();
	var target = $(this);
	var badMailHost = checkBadEmailDomains(email);
	if (validateEmail(email) && badMailHost !== false && badMailHost.length > 1) {
		target.parents('form').find('.form-entry-error').text(t('{{0}} не принимает сообщения от Kwork. Используйте другой email, пожалуйста', [upstring(badMailHost)]));
		return false;
	}
	if (email.length > 0 && validateEmail(email)) {
		$.ajax({
			type: "GET",
			url: '/api/user/checkemail?email=' + email,
			dataType: 'json',
			success: function (data, textStatus, xrf) {
				if (!data.success) {

					var is_simple_signup = $("#is_simple_signup");
					if (is_simple_signup.length && is_simple_signup.val()) {
						var form = $("#form-signup");
						if (semaforUserSignup === false) {
							doUserSimpleSignup(form);
							return false;
						}
					}

					target.parents('form').find('.form-entry-error').text('');
					target.parents('form')
						.find('.popup-form-container-step1')
						.removeClass('popup-form-container-step1')
						.addClass('popup-form-container-step2');
					$('.signup-step-1-js').hide();
					var userType = $('.popup #form-signup input[name="userType"]:checked');
					if(userType.attr('value')=='1')
						$('.popup .country-select-block').remove();
					$('.signup-step-2-js').show();
				}
				else {
					if (data.in_stop_list) {
						target.parents('form').find('.form-entry-error').text(t('Извините, данный адрес почты занесен в стоп-список. Регистрация с ним невозможна.'));
						return false;
					} else {
						var actionAfter = target.closest('form').find('input[name="action_after"]').val();
						show_login(actionAfter);
						$('#l_username').val(email);
						$('#l_password').focus();
						if (!data.currentAppAttached && data.userApp) {
							var warningHtml = "<div class='sign-up-application-warning mb5'>"
								+ "<i class='fa fa-warning'></i> Введите пароль от вашего аккаунта на " + data.userApp + ". Это позволит иметь единый профиль, общий баланс и настройки."
								+ "</div>";
							$(warningHtml).insertBefore("input[name=l_password]");
						}
                        else if (data.currentAppAttached && data.userApp) {
                            var warningHtml = "<div class='sign-up-application-warning mb5'>"
                                + "<i class='fa fa-warning'></i> Пользователь с таким email уже есть в системе. Введите пароль."
                                + "</div>";
                            $(warningHtml).insertBefore("input[name=l_password]");
						}
						else {
							$("div.sign-up-application-warning mb5").remove();
						}
					}
				}
			}
		});
	}
	else {
		$(this).parents('form').find('.form-entry-error').text(t('Введите email'));
	}
});

$(document).on('submit','#form-login',function(e){
    e.preventDefault();
    var form = $(this);
    $.ajax( {
        type: "POST",
        url: '/api/user/login',
        data: form.serialize(),
        success: function(data) {
            if(data.success){
                successAuth(data);
            }else{
				form.find('.form-entry-error').text(data.error);
				processRecapchaShow(data.recaptcha_show);
            }
        }
    } );

    function successAuth(data) {
		if(typeof form.data('successCallback') == 'function'){
			form.data('successCallback').call(this, data);
		}else{
			var result = data;

			if(result.action_after === 'order'){
				if (tempSelectedPackageType.length) {
					make_package_order(post_id, tempSelectedPackageType, 0);
				} else {
					$('#newextformside').submit();
				}
			}else if(result.action_after === 'mirror_redirect'){
				window.location.href = ORIGIN_URL + '/?mirror=' + result.token + addRefParam();
			}else if(result.action_after === 'index_redirect'){
				window.location.href = ORIGIN_URL;
			}else if(result.action_after === 'change_domain') {
				window.location.href = result.redirect;
			}else if(result.action_after === 'support') {
				return true;
			}
			else {
				window.location.reload();
			}
		}
	}
});

// скрывать ошибку о неудачной авторизации при повторном вводе данных
$(document).on('input', '.js-signin-input', function () {
	var $form = $(this).parents('#form-login');
	var $error = $form.find('.form-entry-error');

	$error.text('');
});

$(document).on('submit','#form-change-email',function(e){
    e.preventDefault();
    var form = $(this);
    $.ajax( {
        type: "POST",
        url: '/api/user/changeemail',
        data: form.serialize(),
        success: function( data) {
            if(data.success){
              var result = data;
              window.location.replace(result.redirect);
            }else{
                form.find('.form-entry-error').text(data.error);
            }
        }
    } );
});

var semaforUserSignup = false;
var resetSemaforUserSignup = false;
function getGaGetParam() {
	if (typeof ga !== "undefined") {
		return ga.getAll()[0].get('linkerParam');
	}
	return "";
}

function doUserSignup(form, signtype) {
	semaforUserSignup = true;
	resetSemaforUserSignup = setTimeout(function () {
		semaforUserSignup = false;
	}, 10000);

	var ajax_url = '/api/user/signup';
	if (typeof(signtype) !== 'undefined' && signtype == 'simple') {
		ajax_url = '/api/user/simplesignup';
	}

	$.ajax({
		type: "POST",
		url: ajax_url,
		data: form.serialize(),
		success: function (data, textStatus, xrf) {
			semaforUserSignup = false;
			clearTimeout(resetSemaforUserSignup);
			var result = data;
			if (result.success) {
				if (result.action_after === 'order') {
					if (tempSelectedPackageType.length) {
						make_package_order(post_id, tempSelectedPackageType, 0);
					} else {
						$('#newextformside').submit();
					}
				} else if (result.action_after === 'mirror_redirect') {
					_ga = getGaGetParam();
					if (IS_MIRROR && _ga !== "") {
						var sep = result.redirect.indexOf("?") >= 0 ? "&" : "?";
						url = ORIGIN_URL + result.redirect + sep + "mirror=" + result.token + addRefParam() + "&" + _ga;
					} else {
						url = ORIGIN_URL + "/?mirror=" + result.token + addRefParam();
					}
					window.location.href = url;
				} else if (result.action_after === 'index_redirect') {
					_ga = IS_MIRROR?"/?"+getGaGetParam():"";
					window.location.href = ORIGIN_URL+_ga;
				} else {
					window.location.reload();
				}
			} else {
				form.find('.has-error').removeClass('has-error');
				form.find('.error').text('');
				$.each(result.errors, function(k, v) {
					var erDiv = form.find('.signup-' + k + '-field');
					if (erDiv.length > 0) {
						erDiv.find('.styled-input').addClass('has-error');
						erDiv.find('.error').text(v);
					} else {
						form.find('.form-entry-error').text(v);
					}
				});
				processRecapchaShow(result.recaptcha_show);
			}
		}
	});
}

function doUserSimpleSignup(form) {
	doUserSignup(form, "simple");
}

$(document).on('submit', '#form-signup', function (e) {
	var form = $(this);
	e.preventDefault();
	if (semaforUserSignup === false) {
		doUserSignup(form);
	}
});

$(document).on("click", ".oauth_btn", function (e) {
	e.preventDefault();
	$("#oauth_skip").val($(this).data("skip"));
	$("#oauth_email_form").trigger("submit");
});

$(document).on("submit", "#oauth_email_form", function (e) {
	var form = $(this);
	e.preventDefault();
	$.ajax({
		type: "POST",
		url: "/oauth_email",
		data: form.serialize(),
		success: function (data) {
			var result = data;
			if (result.success) {
				if (result.data.redirect !== "") {
					window.location.href = ORIGIN_URL + result.data.redirect;
				}
				if (result.data.message) {
					show_message('notice', result.data.message);
				}
				return;
			}

			if (result.data) {
				form.find('.has-error').removeClass('has-error');
				form.find('.error').text('');
				$.each(result.data, function (k, v) {
					if (k === "message") {
						show_message('error', v);
					} else {
						var erDiv = form.find('.signup-' + k + '-field');
						if (erDiv.length > 0) {
							erDiv.find('.styled-input').addClass('has-error');
							erDiv.find('.error').text(v.error);
						} else {
							form.find('.form-entry-error').text(v.error);
						}
					}
				});
			}
		}
	});
});

$(document).on('submit','#newextformside',function(e){
    var form = $(this);
    e.preventDefault();
    $.ajax( {
        type: "POST",
        url: '/api/order/create',
        data: form.serialize(),
        success: function( data, textStatus, xrf ) {
			if (data.dataLayer) {
				GTM.pushDataLayers(data.dataLayer);
			}

			var result = data;
			if (result.success) {
				setTimeout(function () {
					window.location.replace(result.redirect);
				}, 1000);

				return;
			} else {
				if (result.error == 'purse') {
					setTimeout(function () {
						window.location.replace(result.redirect);
					}, 1000);

					return;
				} else {
					show_message('error', t('Произошла ошибка. Пожалуйста, попробуйте еще раз.'));
				}
			}
            isMakeOrder = false;
            $('.make-order-disable').removeAttr('disabled').removeClass('make-order-disable');
        }
    } );
});

function popupCloseHandler(e, that) {
	var body = $('body');
	var popup = $(that).parents('.popup');

	if ($(that).hasClass('overlay-disabled')) {
		return false;
	}

	unlockBodyForPopup();

	if (popup.hasClass('popup-signup') || popup.hasClass('popup-login') || popup.hasClass('popup-user')) {
		if ($(that).hasClass('overlay') && body.hasClass('is_mobile')) {
			return false;
		}
		popup.hide();

		//разлокируем скролл документа на мобильных
		changeBodyScrollbar('unlock');
	} else {
		popup.trigger( 'popup.remove' );
		popup.remove();
	}
	
	$( window ).off( "resize", resize_popup );
}

$(document).on('mousedown touchstart','.popup-instant-close-js', function(e) {
	disableCurrentClick = true;
	popupCloseHandler(e, this);
});

$(document).on('click', '.popup .overlay, .popup-close-js', function(e) {
	popupCloseHandler(e, this);
});

/**
 * Блокируем body для прокрутки для popup
 * задаем отступ для скроллбара
 */
function lockBodyForPopup() {
	jQuery('body')
		.addClass('compensate-for-scrollbar')
		.css({
			'padding-right': Utils.getScrollBarWidth()
		});

	$('.header-workbay')
		.css({
			'padding-right': Utils.getScrollBarWidth()
		});
}

/**
 * Снимаем блокировку body для popup
 */
function unlockBodyForPopup() {
	jQuery('body')
		.removeClass('compensate-for-scrollbar')
		.css({
			'padding-right': 0
		});

	$('.header-workbay')
		.css({
			'padding-right': 0
		});
}

/**
 * Показываем лоудер
 */
function showDefaultLoader() {
	if(!$('body .default-loader-wrapper').length) {
		$('body').append(
			'<div class="default-loader-wrapper">'
				+ ' <div class="default-loader">'
					+ '<div class="ispinner ispinner--gray ispinner--animating ispinner--large">'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
						+ '<div class="ispinner__blade"></div>'
					+ '</div>'
				+ '</div>'
			+'</div>'
		);
	}
}

/**
 * Удаляем лоудер
 */
function hideDefaultLoader() {
	$('body').find('.default-loader-wrapper').remove();
}


function show_popup(content, popupContentClass, responsive, popupClass, overlayDisabled, showX, popupContentStyle){
	var is_show_login = false;

	mobile_menu_hide();

	if ($('.popup').hasClass("popup-signup") || $('.popup').hasClass("popup-login")) {
		is_show_login = true;
	}
	$('.popup').remove();

	if (typeof popupContentClass == 'undefined') {
		popupContentClass = '';
	}
	if (typeof popupContentStyle == 'undefined') {
		popupContentStyle = '';
	}
	if (typeof popupClass == 'undefined') {
		popupClass = '';
	}
	if (typeof showX == 'undefined') {
		showX = true;
	}

	var html = '';
	var responsiveClass = '';
	if (typeof responsive != 'undefined' && responsive == true) {
		responsiveClass = 'js-popup--responsive';
	}

	var overlayDisabledHtml = "";
	if (typeof overlayDisabled !== 'undefined' && overlayDisabled) {
		overlayDisabledHtml = " overlay-disabled";
	}

	var popupMobileClass = '';
	if (popupClass === 'popup-signup' || popupClass === 'popup-login') {
		popupMobileClass = ' popup_has-mobile-version';
	}

	var wrapper = false,
		wrapperStart = '<div class="popup__wrapper">',
		wrapperEnd = '</div>';

	if(typeof popupClass !== 'undefined' && popupClass === "popup--center") {
		wrapper = true;
	}

	html += '<div class="popup ' + popupClass + ' ' + responsiveClass + popupMobileClass + '">' +
				'<div class="overlay' + overlayDisabledHtml +'"></div>'+
				(wrapper ? wrapperStart : '') +
				'<div class="popup_content ' + popupContentClass + '" style="'+ popupContentStyle + '">'+
					'<div class="popup_content_inner">'; /* Нужно для ресайза */

	if (showX === true) {
		html += '<div class="pull-right popup-close-js popup-close cur">X</div>';
	}

	html += content;
	html += '</div>'+ (wrapper ? wrapperEnd : '') + '</div>' + '</div>';
	var $popup = $(html);

	// добавим к основным элементам информацию о том, что они находятся в попапе
	$popup.find("input, textarea, button").addClass("js-inPopup");

	$('body').append($popup);
	if (responsive) {
		setPopupWidth($popup);
	}

	var height_popup = $('.popup .popup_content').height() +
		parseInt($('.popup .popup_content').css('padding-top')) +
		parseInt($('.popup .popup_content').css('padding-bottom')) +
		parseInt($('.popup .popup_content').css('margin-top'));
	/* В старых мобильных браузерах position: fixed не работает */
	if ($(document).width() < 768 || isMobile()) {
		/* На мобильных окно у нас скролится вместе со страницей, поэтому показываем на уровне видимости */
		$('body').css('overflow', 'hidden');
		$('.popup').css({ top: $(document).scrollTop() + 'px' });
	}
	$( window ).on( "resize", resize_popup );
    /* Если больше высоты страницы */
	// if ($(window).height() < height_popup) {
	// 	$('.popup .popup_content').css({
	// 		'max-height': height_popup - (height_popup - $(window).height()) - parseInt($('.popup .popup_content').css('margin-top')),
	// 		'width': parseInt($('.popup .popup_content').css('width')) + 15,
	// 		'overflow-y': 'auto'
	// 	});
	// }
	if (popupClass == 'popup-signup' || popupClass == 'popup-login') {
		if (is_show_login) {
			$('.popup').show();
		} else {
			$('.popup').fadeIn(200);
		}
	}

	lockBodyForPopup();
}
function resize_popup() {
	// $('.popup').length если вдруг окно закроеся не функцией popupCloseHandler
	if (($(document).width() < 768 || isMobile()) && $('.popup').length) {
		/* На мобильных окно у нас скролится вместе со страницей, поэтому показываем на уровне видимости */
		$('body').css('overflow', 'hidden');
		$('.popup').css({ top: $(document).scrollTop() + 'px' });
	} else {
		$('body').css('overflow', '');
		$('.popup').css({ top: '' });
	}
}
function resize_popup_height() {
	if ($('.popup .popup_content').size() > 0) {
		$('.popup .popup_content').height(
			$('.popup .popup_content .popup_content_inner')[0].scrollHeight
		);
	}
}

function popup_error(msg) {
	var html = '<h1 class="popup__title">' + t('Ошибка') + '</h1>';
	html += '<hr class="gray mt15 mb15">';
	html += '<p>' + msg + '</p>';
	show_popup(html);
}

function remove_popup() {
	$('.popup').remove();
}

function setPopupWidth($popup) {
	var width = $popup.find('.js-popup__inner-content').width();
	if ($popup.find('.js-popup__inner-content').hasScrollBar()) {
		width += Utils.getScrollBarWidth();
	}

	$popup.find('.popup_content').width(width);
	$popup.find('.js-popup__inner-content').width(width);
	$popup.find('.popup_content').css('max-width', 'inherit');
}

function show_message(type, text, name) {
	name = name ? name : '';

	closeMessage();
	var html = "<div id='fox_notification_block'><div class='fox_" + type +  "' data-name='" + name + "'>" +
		"<p>" + text + "</p>" +
		"</div></div>";
	$(".all_page").prepend(html);
}

function closeMessage() {
	$("#fox_notification_block > div").remove();
}

function closeMessageByName(messageName) {
		$("#fox_notification_block > div").filter('[data-name="' + messageName + '"]').remove();
}

/**
 * Получение сообщений трека
 * @param orderId
 */
function getMessageTrackByOrderId(orderId) {
	$.ajax({
		url: '/track/action/readpagemsg',
		type: 'post',
		data: {
			'orderId': orderId,
		},
		dataType:'json',
		success:function(response) {
			if (response.data.pageMsg) {
				show_message(response.data.pageMsg.type, response.data.pageMsg.content, response.data.pageMsg.name)
			} else {
				closeMessage();
			}
		}
	});
}

/**
 * Review Module
 * @type {{init, load}}
 */
var ReviewsModule = (function(currentUserId) {
	var _params = {
		id: 0,
		type: 'positive',
		onPage: 0,
		onPageStart: 0,
		total: {positive: 0, negative: 0},
		offset: {positive: 0, negative: 0},
		entity: 'user',
		counter: 0
	};
	var _reviewCache = {
		positive: '',
		negative: ''
	};

	var $moreBtn;

	var _getType = function(){
		return _params.type;
	};

	var _fillReviews = function(response){
		_params.offset[_getType()] += _params.onPage;
		$('.gig-reviews-list').append(response.html).show().css('display', 'block');
		$moreBtn.hide();
		$('.js-show-more-reviews').hide();

		_showNextButton();
		clearTimeout(preloader_timeout_ajax);
		$('.more-btn-reviews #more-text').removeClass('disabled');
		$('.reviews_order_preloader-js').addClass('hidden');

		_initReviewForm();
	};

	var _showNextButton = function(){
		var showCount = _params.offset[_getType()];
		if(_params.total[_getType()] > showCount) {
			$moreBtn.show();
			$('.js-show-more-reviews').show();
		} else {
			// Если нет флага о том, что показываются все отзывы
			if (!$('.gig-reviews-list').hasClass('gig-reviews-list__show_full')) {
				// Если отзывы загружены все и нет скрытых двух (для мобильных)
				if (showCount - 2 >= _params.total[_getType()]) {
					// Показываем все
					$('.gig-reviews-list').addClass('gig-reviews-list__loaded_full').addClass('gig-reviews-list__show_full');
				} else {
					// Показываем кнопку для мобильных, что показать скрытые отзывы
					$('.gig-reviews-list').addClass('gig-reviews-list__loaded_full');
					$moreBtn.show();
					$('.js-show-more-reviews').show();
				}
			}
		}
	};

	var _loadMoreReviews = function (offset, type) {
		var data = {
			offset: offset,
			type: type,
			id: _params.id,
			entity: _params.entity
		};
		$.get('/api/rating/loadreviews', data, _fillReviews, 'json');
	};

	var _checkMobileFinalReviews = function () {
		if ($('.gig-reviews-list-full').hasClass('gig-reviews-list__loaded_full')) {
			$('.gig-reviews-list-full').addClass('gig-reviews-list__show_full');
			$('.js-show-more-reviews').hide();
			return true;
		}
		return false;
	};

	var _setCache = function(){
		if(_getType() == 'positive'){
			_reviewCache['negative'] = $('.gig-reviews-list').html();
		}else{
			_reviewCache['positive'] = $('.gig-reviews-list').html();
		}
	};

	var  _changeTab = function(){
		var type =  $(this).data('type');
		if($('.reviews-tab__item.active').data('type') == type) {
			return false;
		}
		if(_params.total[type] == 0) {
			return false;
		}
		if(type == 'negative' && !parseInt(currentUserId) > 0) {
			show_signup();
			return false;
		}
		$moreBtn.hide();
		$('.js-show-more-reviews').hide();
		$('.reviews-tab__item').removeClass('active');
		$(this).addClass('active');
		_params.type = type;

		_setCache();

		$('.gig-reviews-list').html('');
		$('.gig-reviews-list').removeClass('gig-reviews-list__show_full').removeClass('gig-reviews-list__loaded_full');
		if(_reviewCache[type].length) {
			$('.gig-reviews-list').append(_reviewCache[type]);
			if (_params.total[type] <= _params.offset[type]) {
				$moreBtn.hide();
				$('.js-show-more-reviews').hide();
				$('.gig-reviews-list').addClass('gig-reviews-list__show_full').addClass('gig-reviews-list__loaded_full');
			} else {
				$moreBtn.show();
				$('.js-show-more-reviews').show();
			}

			_initReviewForm();

			return false;
		}
		preloader_timeout_ajax = setTimeout(function(){
			$('.reviews_order_type .reviews_order_preloader-js').removeClass('hidden')
		},250)
		_loadMoreReviews(0, type);
	};

	var _initReviewForm = function () {
		if ($('.js-message-body').length) {
			window.initReviewForm();
		}
	};

	return {
		reset: function() {
			_params.offset.positive = 5;
			_params.offset.negative = 5;
			_showNextButton();
		},

		init: function(params) {
			$moreBtn = $('.more-btn-reviews');

			for(var i in params) {
				_params[i] = params[i];
			}

			_params.total.positive = $('#pos').data('count')^0;
			_params.total.negative = $('#neg').data('count')^0;
			_params.type =  $('.reviews-tab__item.active').data('type');
			if (!_params.offset[_getType()]) {
				_params.offset[_getType()] = _params.onPageStart;
			} else {
				_params.offset[_getType()] = _params.onPage;
			}

			$('.more-btn-reviews #more-text').on('click', function(){
				// Если проверка запущена когда все загружено, то выходим
				if (_checkMobileFinalReviews()) { return true; }

				if (!parseInt(currentUserId) > 0 && _params.counter > 0) {
					show_signup();
					return false;
				}

				if (!parseInt(currentUserId) > 0 && _params.counter === 0){
					_params.counter = 1;
				}

				if ($(this).hasClass('disabled')) {
					return false;
				}

				$(this).addClass('disabled');
				preloader_timeout_ajax = setTimeout(function(){
					$('.more-btn-reviews .reviews_order_preloader-js').css({"visibility":"visible"})
				},250)

				var offset = _params.offset[_getType()];
				_loadMoreReviews(offset, _getType());
			});

			//показываем все отзывы на мобильных девайсах
			$('.js-show-more-reviews').on('click', function () {
				$('.more-btn-reviews #more-text').trigger('click');
			});

			$('.reviews-tab__item').on('click', _changeTab);

			_showNextButton();

			//выделение отзыва
			var hash = window.location.hash.substring(1);

			if (hash && $('#' + hash).length) {
				$('#' + hash).addClass('gig-review-select');
			}
		},
		load: function(offset, type){
			_loadMoreReviews(offset, type);
		}
	}
})(USER_ID);

$(document).ready(function(){
	if (jQuery.fn.jScrollPane) {
		$('.cart-popup .block-popup_body').jScrollPane({autoReinitialise: true});
	}
	$('body').on('mousewheel', function(e) {
		if ($(e.target).closest('.cart-popup .block-popup_body, .message .block-popup').length) return false;
	});
});

$(document).on('click','.cart-popup_count_plus',function(){
    event.preventDefault();
    var current = parseInt($(this).parents('.cart-popup_count').find('input').val(), 10);
    if (current == 10) {
        return false;
    }
    current = current+1;
    $(this).parents('.cart-popup_count').find('input').val(current);
    update_cart(true);
});

$(document).on('click','.cart-popup_count_minus',function(){
    event.preventDefault();
    var current = parseInt($(this).parents('.cart-popup_count').find('input').val(), 10);
    if(current==1){
        return false;
    }
    current = current-1;
    $(this).parents('.cart-popup_count').find('input').val(current);

    update_cart(true);
});

function update_cart(){
    var total_price = 0;
    $('.header .cart-popup .cart-popup_row:not(.cart-popup_head)').each(function(){
    	var suffix = (actor_lang === 'ru') ? '-ru' : '-en';
		var priceStr = $(this).find('.cart-popup__sum').attr('data-price' + suffix);
        priceStr = priceStr.replace(/\s+/g,'');
        if(actor_lang === 'en') {
            var price = Math.round(parseFloat(priceStr) * 100) / 100;
        } else {
            var price = parseInt(priceStr, 10);
        }
        total_price = total_price + price;
    });
    $('.cart-popup_total-js').text(Utils.priceFormat(total_price, lang));

    setCartCountNotify();

	var extraCnt = $('.block-popup .block-popup_body .jspPane .block-popup_body_container .cart-popup__extras').not('.hidden').length;
	if (extraCnt > 0) {
		$('.block-popup .cart-popup__extras').removeClass('hidden');
		$('.block-popup.noextra').removeClass('noextra');
	}
}

function setCartCountNotify(){
    var countStr = $('.header .cart-popup .cart-popup_row:not(.cart-popup_head)').length;
    var count = parseInt(countStr);
    if(count == 0){
        $('.cart-popup_notify').text(countStr);
        $('.cart-popup_notify').css('opacity', '0');
        return false;
    }

    var oldCountStr = $('.cart-popup_notify').text();
    var oldCount = parseInt(oldCountStr);
    if(oldCount == count || (oldCountStr == '+' && count > 9)){
        return false;
    }

    $('.cart-popup_notify').css('opacity', '0');
    if(count > 9){
        countStr = '+';
        $('.cart-popup_notify').css('font-size', '10px');
    }else{
        $('.cart-popup_notify').css('font-size', '8px');
    }
    setTimeout(function(){
        $('.cart-popup_notify').text(countStr);
        $('.cart-popup_notify').css('opacity', '1');
    },100);

}
function refreshCartScroll() {
    if($('.cart-popup .block-popup .cart-popup_row:not(.cart-popup_head)').length === 0){
        $('.cart-popup').addClass('cart-popup_empty');
        $('.cart-popup_head').addClass('hidden');
    }
    else{
        $('.cart-popup_head').removeClass('hidden');
        $('.cart-popup').removeClass('cart-popup_empty');
        var height = 0;
        $.each($('.cart-popup .block-popup .cart-popup_row:not(.cart-popup_head)'), function(key, value){
            if (key <= 3) { //количество показываемых блоков - 1
                height += $(this).outerHeight();
            }
        });
        if($('.cart-popup .block-popup .cart-popup_row:not(.cart-popup_head)').length > 4){
            height = height - 1;
        }
        $('.cart-popup .block-popup_body').css({height: height + 'px'})
    }

    var pane = $('.cart-popup .block-popup_body');
    var api = pane.data('jsp');
    api.reinitialise();
}


function notActorCreateNewCartItem(obj, isPackage) //Добавление в корзину элементов, для не залогиненого пользователя
{
    show_login();//Открываем форму логина
    CartModule.setData('IsCartItem', true);
    if (isPackage) {
        CartModule.setData('package', obj);
    }
}

$(document).on('mouseenter', '.massage', function() {
	$('.dropdownbox').hide();

	var this_element = $(this);
	var $popup = this_element.find('.block-popup');
	$popup.fadeIn(200, 'swing');
	if (this_element.hasClass('js-notice-other') && $popup.hasClass('wating')) {
		loadNotify($popup);
	}

	if (this_element.hasClass('js-notice-inbox') && $popup.hasClass('wating')) {
		$.get('/api/user/checknotify', function (response) {
			if (response.success) {
				$popup.removeClass('wating');
				NotifyModule.updateInboxMessage($('.js-notice-inbox'), response.data.dialog_data, response.data.new_message);
			}
		}, 'json');
	}

	//если jscrollPane инициализировался криво, переинициалищируем его
	if ($('.js-notice-other  .block-popup').css('display') != 'none' && this_element.hasClass('js-notice-other')) {
		if (window.popupJscrollPane && window.popupJscrollPane.data() && window.popupJscrollPane.data().jsp) {
			window.popupJscrollPane.data().jsp.destroy();
			window.popupJscrollPane = false;
		}
		window.popupJscrollPane = $($popup).find('.foxNotifBox_other').css({'overflow': 'inherit'}).jScrollPane();
	}
}).on('mouseleave','.massage',function() {
	$(this).find('.block-popup').hide();
});

function delete_confirm(that) {
    var count = $('.checkbox:checked').size();
    if(count == 0)
        return false;
    var hasOffers = $('.checkbox.has-offers:checked').size();
    var html = ''+
        '<div>' +
            '<h1 class="popup__title f26">' + t('Подтверждение удаления') + '</h1>'+
            '<hr class="gray" style="margin-bottom:32px;">'+
            '<div class="dib w100p">' +
                '<p class="f15 pb10 ml10">' + t('Удалить кворк?') + '</p>';
    if (hasOffers > 0) {
        html += '<p class="f15 pb50 ml10">' + t('Внимание! Ваши ранее отправленные предложения этого кворка по проектам будут удалены.') + '</p>';
    }

    html += '<button class="popup__button white-btn popup-close-js">' + t('Отменить') + '</button>';

    if(lang === actor_lang && lang === 'ru' && !disable_actor_en && !disable_en && that.data('twin')){
        html += ''+
                '<button class="popup__button red-btn pull-right" onclick="gigs_delete(1);">' + t('Удалить все') + '</button>'+
                '<button class="popup__button red-btn" onclick="gigs_delete(2);">' + t('Удалить русский') + '</button>'+
                '<button class="popup__button red-btn pull-right" onclick="gigs_delete(3);">' + t('Удалить английский') + '</button>';
    }else{
        html += '<button class="popup__button red-btn pull-right" onclick="gigs_delete(4);">' + t('Удалить') + '</button>';
    }
    html += '</div>'+
        '</div>';
    show_popup(html, '', false, 'popup--center');
}

/**
 * Подтверждение удаления для черновика
 *
 * @param {int} draftId Id Черновика
 */
function delete_draft_confirm(draftId) {
	var html = ''
		+ '<div>'
			+ '<h1 class="popup__title f26">'
				+ t('Подтверждение удаления')
			+ '</h1>'
			+ '<hr class="gray" style="margin-bottom: 32px;">'
			+ '<div style="display: inline-block; width: 100%;">'
				+ '<p class="f15 pb10 ml10">' + t('Вы действительно хотите удалить черновик?') + '</p>'
				+ '<button class="popup__button red-btn delete-draft" onclick="draft_delete(' + draftId + ')">'
					+ t('Удалить')
				+ '</button>'
				+ '<button class="popup__button white-btn popup-close-js pull-right">'
					+ t('Отменить')
				+ '</button>'
			+ '</div>'
		+ '</div>';

	show_popup(html);
}

function suspend_kwork_offer_confirm(that) {
    var count = $('.checkbox:checked').size();
    if(count == 0)
        return false;

    var html = ''+
        '<div>' +
            '<h1 class="popup__title">' + t('Подтверждение остановки') + '</h1>'+
            '<hr class="gray" style="margin-bottom:32px;">'+
            '<div style="display:inline-block;width:100%;">' +
            '<p class="f15 pb50 ml10">' + t('Внимание! Ваши ранее отправленные предложения этого кворка по проектам будут удалены.') + '</p>';

    if(lang === actor_lang && lang === 'ru' && !disable_actor_en && !disable_en && that.data('twin')){
        html += ''+
                '<button class="hoverMe popup__button RedBtnStyle w160 mt20 pull-left f16" style="height:40px;" onclick="gigs_suspend(1);">' + t('Остановить все') + '</button>' +
                '<button class="hoverMe popup__button RedBtnStyle w160 mt20 pull-right f16" style="height:40px;" onclick="gigs_suspend(2);">' + t('Только русские') + '</button>' +
                '<button class="hoverMe popup__button RedBtnStyle w160 mt20 pull-left f16" style="height:40px;" onclick="gigs_suspend(3);">' + t('Только английские') + '</button>';
    } else {
        gigs_suspend(4);
        return false;
    }
    html += ''+
                '<button class="hoverMe white-btn w160 mt20 pull-right popup-close-js f16" style="height:40px;">' + t('Отменить') + '</button></div>'+
            '</div>'+
        '</div>';
    show_popup(html);
}
function suspend_kwork_offer_confirm_2(that) {
    var count = $('.checkbox:checked').size();
    if(count == 0)
        return false;

    var html = ''+
        '<div>' +
            '<h1 class="popup__title">' + t('Подтверждение остановки') + '</h1>'+
            '<hr class="gray" style="margin-bottom:32px;">'+
            '<div style="display:inline-block;width:100%;">';
            if(lang === actor_lang && lang === 'ru' && !disable_actor_en && !disable_en && that.data('twin')){
                html += '<p class="f15 pb50 ml10">' + t('Выберите тип кворков для приостановки.') + '</p>' +
                '<button class="hoverMe popup__button RedBtnStyle w160 mt20 pull-left f16" style="height:40px;" onclick="gigs_suspend(1);">' + t('Остановить все') + '</button>' +
                '<button class="hoverMe popup__button RedBtnStyle w160 mt20 pull-right f16" style="height:40px;" onclick="gigs_suspend(2);">' + t('Только русские') + '</button>' +
                '<button class="hoverMe popup__button RedBtnStyle w160 mt20 pull-left f16" style="height:40px;" onclick="gigs_suspend(3);">' + t('Только английские') + '</button>';
            }else {
                html += '<button class="hoverMe popup__button RedBtnStyle w160 mt20 pull-left f16" style="height:40px;" onclick="gigs_suspend(4);">' + t('Остановить') + '</button>';
            }
                html += '<button class="hoverMe white-btn w160 mt20 pull-right popup-close-js f16" style="height:40px;">' + t('Отменить') + '</button></div>'+
            '</div>'+
        '</div>';
    show_popup(html);
}
function activate_kwork_confirm(){
    var count = $('.checkbox:checked').size();
    if(count == 0)
        return false;

    var html = ''+
        '<div>' +
            '<h1 class="popup__title">' + t('Подтверждение активации кворков') + '</h1>'+
            '<hr class="gray" style="margin-bottom:32px;">'+
            '<div style="display:inline-block;width:100%;">';
            if(lang === actor_lang && lang === 'ru' && !disable_actor_en && !disable_en){
                html += '<p class="f15 pb50 ml10">' + t('Выберите тип кворков для активации.') + '</p>' +
                '<button class="hoverMe popup__button RedBtnStyle w160 mt20 pull-left f16" style="height:40px;" onclick="gigs_activate(1);">' + t('Активировать все') + '</button>' +
                '<button class="hoverMe popup__button RedBtnStyle w160 mt20 pull-right f16" style="height:40px;" onclick="gigs_activate(2);">' + t('Только русские') + '</button>' +
                '<button class="hoverMe popup__button RedBtnStyle w160 mt20 pull-left f16" style="height:40px;" onclick="gigs_activate(3);">' + t('Только английские') + '</button>';
            }else{
                html += '<button class="hoverMe popup__button RedBtnStyle w160 mt20 pull-left f16" style="height:40px;" onclick="gigs_activate(4);">' + t('Активировать') + '</button>';
            }
                html += '<button class="hoverMe white-btn w160 mt20 pull-right popup-close-js f16" style="height:40px;">' + t('Отменить') + '</button></div>'+
            '</div>'+
        '</div>';
    show_popup(html);
}
function change_kwork_offer_confirm(goto) {
    var html = ''+
        '<div>' +
            '<h1 class="popup__title">' + t('Подтверждение редактирования') + '</h1>'+
            '<hr class="gray" style="margin-bottom:32px;">'+
            '<div style="display:inline-block;width:100%;">' +
                '<p class="f15 pb50 ml10">' + t('Внимание! Ваши ранее отправленные предложения этого кворка по проектам будут удалены.') + '</p>' +
                '<button class="hoverMe popup__button RedBtnStyle w160 mt20 pull-left f16" style="height:40px;" onclick="location.href=\'' + goto + '\'">' + t('Редактировать') + '</button>' +
                '<button class="hoverMe white-btn w160 mt20 pull-right popup-close-js f16" style="height:40px;">' + t('Отменить') + '</button></div>'+
            '</div>'+
        '</div>';
    show_popup(html);
}
function delete_request_confirm(id) {
    var html = ''+
        '<div class="js-popup__inner-content" style="width:400px">' +
            '<form class="js-project-want" action="/projects/manage/delete" method="post">' +
                '<input type="hidden" name="id" value="' + id + '" />' +
                '<input type="hidden" name="action" value="delete" />' +
                '<h1 class="popup__title f26">' + t('Подтверждение удаления') + '</h1>'+
                '<hr class="gray" style="margin-bottom:32px;">'+
                '<div style="display:inline-block;width:100%;">' +
                    '<p class="f15 pb50 ml10">' + t('Удалить проект?') + '</p>' +
					'<div class="d-flex d-flex-m-reset justify-content-between flex-row-reverse">' +
                    '<button class="popup__button red-btn">' + t('Удалить') + '</button>'+
                    '<button class="popup__button white-btn pull-right popup-close-js" onclick="return false;">' + t('Отменить') + '</button></div>'+
					'</div>' +
                '</div>'+
            '</form>' +
        '</div>';
    show_popup(html, 'popup-project-want', true, 'popup-valign-center');
}

function stop_request_confirm(id) {

    var html = ''+
        '<div class="js-popup__inner-content" style="width:400px">' +
            '<form class="js-project-want" action="/projects/manage/stop" method="post">' +
                '<input type="hidden" name="id" value="' + id + '" />' +
                '<input type="hidden" name="action" value="stop" />' +
                '<h1 class="popup__title">' + t('Подтверждение остановки') + '</h1>'+
                '<hr class="gray" style="margin-bottom:32px;">'+
                '<div style="display:inline-block;width:100%;">' +
                    '<p class="f15 pb50 ml10">'+
						t('Вы подтверждаете остановку проекта? Продавцы не смогут больше добавлять в него свои предложения.') +
					'</p>'+
					'<div class="d-flex d-flex-m-reset justify-content-between flex-row-reverse">' +
                    '<button class="popup__button red-btn">' + t('Остановить') + '</button>'+
                    '<button class="popup__button white-btn pull-right popup-close-js" onclick="return false;">' + t('Отменить') + '</button></div>'+
					'</div>' +
                '</div>'+
            '</form>' +
        '</div>';
    show_popup(html, 'popup-project-want', true, 'popup-valign-center');
}
function restart_request_confirm(token) {
	$.ajax({
		url: '/projects/manage/restart?id=' + token,
		type: 'get',
		data: $(this).serialize(),
		dataType: 'json',
		complete: function() {
			document.location.reload();
		},
	});
}
function can_not_order_popup(catName, catLink) {
    var html = ''+
        '<div class="js-popup__inner-content" style="width:400px">' +
                '<div class="clear" style="margin-bottom:32px;"></div>'+
                '<div style="display:inline-block;width:100%;">' +
                    '<p class="f15 pb20 ml10">' + t('Продавец временно не продает данный кворк. Посмотрите другие кворки из раздела “<a itemprop="item" href="{{0}}"><span itemprop="name">{{1}}</span></a><meta itemprop="position" content="2" />”. Вы также можете воспользоваться поиском вверху страницы.', [catLink, catName]) + '</p>'+
                '</div>'+
        '</div>';
    show_popup(html, '', true);
}
function declension(count, form1, form2, form5, language) {
	if (language == undefined || language == '') {
		language = null;
	}

	var language = language || lang || 'ru';
	var count = count ? count : 0;
	if (lang == 'ru') {
		var number = Math.abs(count) % 100;
		if (number > 10 && number < 20)
				return form5;

		number = number % 10;
		if (number > 1 && number < 5)
			return form2;

		if (number == 1)
				return form1;

		return form5;
	} else {
		var number = Math.abs(count);
		if (number == 1)
			return form1;
		return form5;
	}
}

function upstring(string){
    var first = string[0].toUpperCase();
    return first + string.substr(1);
}

function toggleRisksAttention(){
    if ($('.risks-block .menulist:hidden')) {
        $('body').prepend('<div class="risks-overlay"></div>');
        $(document).on('click','.risks-block .popup-close, .risks-overlay',function(){
           $('.risks-overlay').remove();
           $('.risks-block .menulist, .risks-block .menubox').hide();
        });
        $('.risks-block .menulist, .risks-block .menubox').show();
        checkRevertRisk();
    }else{
        $('.risks-overlay').remove();
        $('.risks-block .menulist, .risks-block .menubox').hide();
    }
}
$(function(){
   $(document).on('scroll', checkRevertRisk);
   $(document).on('scroll', checkRevertRequiredInfo);
});

function checkRevertRisk(){
    if(!$('.risks-block').length){
        return;
    }

    var windowHeight = $(window).height(),
        scrollTop = $(document).scrollTop(),
        hintHeight = $('.risks-block .menulist').outerHeight(),
        hintTop = $('.risks-block').offset().top + $('.risks-block').height();
    if(hintTop + hintHeight + 20 > scrollTop + windowHeight){
        $('.risks-block .menulist, .risks-block .menubox').addClass('revert');
        $('.risks-block .menulist').css({'top': '-'+ hintHeight +'px'});
    }else{
        $('.risks-block .menulist, .risks-block .menubox').removeClass('revert');
        $('.risks-block .menulist').css({'top': '10px'});
    }
}
function toggleInfoRequired(){
    if ($('.requiredInfo-block .menulist:hidden')) {
        $('body').prepend('<div class="requiredInfo-overlay"></div>');
        $(document).on('click','.requiredInfo-block .popup-close, .requiredInfo-overlay',function(){
           $('.requiredInfo-overlay').remove();
           $('.requiredInfo-block .menulist, .requiredInfo-block .menubox').hide();
        });
        $('.requiredInfo-block .menulist, .requiredInfo-block .menubox').show();
        checkRevertRequiredInfo();
    }else{
        $('.requiredInfo-overlay').remove();
        $('.requiredInfo-block .menulist, .requiredInfo-block .menubox').hide();
    }
}
function checkRevertRequiredInfo(){
    if(!$('.requiredInfo-block').length){
        return;
    }

    var windowHeight = $(window).height();
    var scrollTop = $(document).scrollTop();
    var hintHeight = $('.requiredInfo-block .menulist').outerHeight();
	if (hintHeight > windowHeight)
	{
		$('.requiredInfo-block .menulist').height(windowHeight * 0.6);
		$('.requiredInfo-block .menulist').css({'overflow-y': 'auto'});
		hintHeight = windowHeight * 0.6;
	}
	var hintTop = $('.requiredInfo-block').offset().top + $('.requiredInfo-block').height();
    if(hintTop + hintHeight + 20 > scrollTop + windowHeight){
        $('.requiredInfo-block .menulist, .requiredInfo-block .menubox').addClass('revert');
        $('.requiredInfo-block .menulist').css({'top': '-'+ hintHeight +'px'});
    }else{
        $('.requiredInfo-block .menulist, .requiredInfo-block .menubox').removeClass('revert');
        $('.requiredInfo-block .menulist').css({'top': '10px'});
    }
}
function getGetParams()
{
	var url = window.location.href;
	if(url.indexOf('?') == -1)
		return [];

	var results = [];
	var queryStr = url.split('?', 2)[1];
	var ps = new URLSearchParams(queryStr);
	ps.forEach(function (value, key) {
		results[key] = value;
	})

	return results;
}

function implodeURI(params, baseUrl) {
	var result = "";
	var url = baseUrl || null;
	if(!url) {
		var splitedUrl = window.location.href.split('?');
		if(splitedUrl.length == 1) {
			url = window.location.href;
		} else {
			url = splitedUrl[0];
		}
	}
	result = $.param(params);
	result = url + '?' + result;
    return result;
}

/**
 * функция собирает url для каталога который подменяется при выборе параметров фильтра
 * @param params - массив параметров
 */
function implodeCatalogUrl(params) {
	var result = "";
	var attributeAlias = "";

	for (var key in params) {
		if (params[key] == '') {
			continue;
		} else if (key === "attribute_alias") {
			attributeAlias = params[key];
			continue;
		}
		result += '&' + key + '=' + params[key];
	}
	if (attributeAlias) {
		attributeAlias = "/" + attributeAlias;
	}
	result = window.catalogUrl + attributeAlias + '?' + result.substr(1);
	return result;
}

/**
 * Закгрузить кворки после нажатия кнопки "Показать еще"
 * @param showMoreClick
 */
function loadKworks(showMoreClick, callback) {
	var userId = USER_ID || 0;
	userId = parseInt(userId);

	var url = window.location.href;

	if (typeof CatFilterModule == 'undefined' || !CatFilterModule.getIsInited()) {
		var params = getGetParams();
	} else {
		var params = CatFilterModule.getParams();
	}

	params['page'] = parseInt(params['page']);
	if (!params['page'] || params['page'] < 1)
		params['page'] = 1;
	if (typeof weightMinute == 'undefined') {
		weightMinute = '';
	}

	if (showMoreClick && userId < 1) {
		if ('page' in params && params['page'] > 1) {
			show_signup();
			return;
		}
	}

	var shownIds = $('.js-kwork-card').map(function() {
		return $(this).data('id')
	}).get();
	var item_per_page = $('.kwork-card-data-wrap').data('kworks-per-page');

	if (item_per_page > 0) {
		params['page'] = Math.round(shownIds.length / item_per_page) + 1;
	}

	$('.loadKworks').addClass('onload');

	if ($(window).width() < 768) {
		$('.preloader_kwork').show();
	}

	$.ajax({
		url: url,
		type: 'post',
		data: {
			'page': params['page'],
			'weightMinute': weightMinute,
			'excludeIds': shownIds.join(",")
		},
		dataType:'json',
		success:function(response) {
			$('.loadKworks').removeClass('onload');
			$('.preloader_kwork').hide();
			jQuery('.loader').hide();

			var newUrl = implodeURI(params);
			window.history.pushState({urlPath:newUrl},"",newUrl);

			if (response.paging.page * response.paging.items_per_page >= response.paging.total) {
				$('.loadKworks').addClass('hidden');

				if (typeof weightMinute !== 'undefined' && weightMinute !== '') {
					showMoreButtonPopup();
				}
				if ($('.request_block-js')){
					$('.request_block-js').removeClass('hidden');
				}

			} else {
				if (callback) {
					callback();
				}
			}
			if (typeof sdisplay !== 'undefined' && sdisplay == "list") {
				$(".cusongslist").append(response.html);
			} else {
				$(".cusongsblock").last().after(response.html);
			}
			$('.js-tooltip').tooltip();
		}
	});
}

/**
 * Закгрузить портфолио после нажатия кнопки "Показать еще"
 * @param showMoreClick
 */
function loadPortfolios(showMoreClick, callback, requestUrl, requestFromPopup) {
	var url = requestUrl || window.location.href;
	var userId = USER_ID || 0;
	var $button = $(".loadPortfolios");
	var params = getGetParams();
	var shownIds = $(".js-portfolio-card").map(function() {
		return $(this).data('id')
	}).get();
	var item_per_page = $('.portfolio-card-data-wrap').data('portfolio-per-page');

	userId = parseInt(userId);

	if (typeof CatFilterModule !== 'undefined' && !CatFilterModule.getIsInited()) {
		params = CatFilterModule.getParams();
	}

	params['page'] = parseInt(params['page']);
	if (!params['page'] || params['page'] < 1) {
		params['page'] = 1;
	}
	if (typeof weightMinute === 'undefined') {
		var weightMinute = "";
	}

	if (item_per_page > 0) {
		params['page'] = Math.round(shownIds.length / item_per_page) + 1;
	}

	$button.addClass('onload');
	var newUrl = null;
	window.loadMoreXhr = $.ajax({
		url:url,
		type:'post',
		data:{'page':params['page'], 'weightMinute': weightMinute, 'excludeIds': shownIds},
		dataType:'json',
		success:function(response) {
			newUrl = implodeURI(params, url.split('?')[0]);
			if (!requestFromPopup) {
				window.history.pushState({urlPath:newUrl},"",newUrl);
			}
			if (response.paging.page * response.paging.items_per_page >= response.paging.total) {
				var $requestBlock = $('.request_block-js');
				$button.addClass('hidden');
				if (typeof weightMinute !== 'undefined' && weightMinute !== '') {
					showMoreButtonPopup();
				}
				if($requestBlock) {
					$requestBlock.removeClass('hidden');
				}
			}
			$(".cusongslist").append(response.html);
			if (requestFromPopup) {
				var firstCardId = response.paging.ids.split(',')[0];
				portfolioCard.getPortfolio(firstCardId, true);
			}
			$('.js-tooltip').tooltip();
		},
		complete: function() {
			window.loadMoreXhr = null;
			$button.removeClass('onload');
			if (callback) {
				callback(newUrl);
			}
		},
	});
}

/*функционал для открытия подменю на тачскриновых устройствах*/
$(document).on('click', '.category-menu__list_item', function(e) {
	var $el = $(this);
	var isClick = $el.attr('data-click');
	var enableMobileFirstClick = $el.hasClass('mobile-enable-first-click');
	var hasTouch = 'ontouchstart' in window;
	if ((isMobile() || hasTouch) && !isClick && !enableMobileFirstClick) {
		e.preventDefault();
		$el.attr('data-click', 'true');
	}
});
/*конец функционал для открытия подменю на тачскриновых устройствах*/

$(document).on('click','.faq_sub-cat h2,.faq_page-block .faq_cat',function(){
    var faqParentEl = $(this).parent();
    faqParentEl.toggleClass('active');
});

// Событие выпадающего списка в меню
$(document).on('touchstart', function(e) {
	var $listElements = $('.category-menu__list, .cart-popup, .js-notice-other, .droparrow, .lang-selector');
	if (!$listElements.is(e.target) && $listElements.has(e.target).length === 0) {
		$('.dropdownbox, .menubox').hide();
		if ($('.page-basket').length === 0) {
			$('.block-popup').hide();
		}
		$('.category-menu__list_item').removeAttr('data-click');
	}
});
$(document).on('mouseenter touchstart','.sub-menu-parent>li',function(e) {
	var hasTouch = 'ontouchstart' in window;
	if (e.type == 'mouseenter' && (isMobile() || hasTouch)) { // for touch screen
		$('.category-menu__list_item').removeAttr('data-click');
		e.stopPropagation();
		e.preventDefault();
		return false;
	}
	var all_menu = $(".dropdownbox, .menubox");
	var this_menu = $(this).find('.menubox').get(0);
	all_menu.each(function (index, el) {
		if (el !== this_menu) {
			$(el).stop(true, true).hide();
		}
	});
	$(this_menu).stop(true, true).fadeIn(400, 'swing');
}).on('mouseleave','.sub-menu-parent>li',function(e) {
	var hasTouch = 'ontouchstart' in window;
	if (e.type == 'mouseleave' && (isMobile() || hasTouch)) { // for touch screen
		e.stopPropagation();
		e.preventDefault();
		return false;
	}
	$('.sub-menu-parent').find('.menubox').stop(true, true).hide();
	$('.category-menu__list_item').removeAttr('data-click');
});

function closeEvent(target, id, isConfirm) {
	if (!isConfirm) {
		var isClose = confirm(t('Вы уверены что хотите закрыть это уведомление?'));
		if (!isClose) {
			return false;
		}
	}

    $.ajax( {
        type: "GET",
        url: '/api/event/delete?eventId=' + id,
        success: function(data) {
            var result = data;
            if(result.success){
                target.closest(".event-message").remove();
            }
        }
    } );
}

function closeEvent_IE(id){
    var isClose = confirm(t('Вы уверены что хотите закрыть это уведомление?'));
    if(!isClose)
        return false;

    var date = new Date();
    var days = 360;
    date.setTime(date.getTime() + (days*24*60*60*1000));
    setCookie('isIE_browser', true, { expires: date,path:'/', SameSite: 'Lax'});
    var form = $('#event_' + id);
    if (form.length > 0) {
        form.remove();
    }

}

var checkFormModule = (function () {

    return {
        checkForm: function (form) {
            var success = true;
            $(form).find('input.js-required, textarea.js-required, select.js-required').each(function () {

                if(!$(this).is(':visible') && !$(this).hasClass('styled-select')){
                    return;
                }

                $(this).removeClass('input-error');
                if ($(this).val().length == 0) {
                    success = false;
                    if($(this).hasClass('styled-select') && !$(this).is(':visible')){
                        $(this).siblings('.chosen-container').find('.chosen-single').addClass(checkFormModule.gerErrorClass(this));
                    }
                    else{
                        $(this).addClass(checkFormModule.gerErrorClass(this));
                    }

                }
            });

            return success;
        },
        gerErrorClass: function(el){
            var tagName = $(el).prop("tagName").toLowerCase();
            return tagName + '-styled--error';
        }
    }
})();

$(function () {
    $(document).on('submit', 'form', function () {
        if (!checkFormModule.checkForm(this)) {
            return false;
        }
    });

    $(document).on('change, input', '.js-required', function () {
        var tagName = $(this).prop("tagName").toLowerCase();
        if ($(this).val()) {
            $(this).removeClass(checkFormModule.gerErrorClass(this));
        }else{
            $(this).addClass(checkFormModule.gerErrorClass(this));
        }
    });
});



var NotifyModule = (function () {
    var $_noticeBlocks = {};

    var _lastTracks = {};

    var _updateNotifyIcons = function () {
        $.get('/api/user/checknotify', function (response) {
            if (response.success != true) {
                return;
            }

            if (_tracksIsChanged(response.data.last_tracks)) {
                _lastTracks = response.data.last_tracks;
                _updateNotifyTracks();
            }

            _setNotifyIcons(response.data);
        }, 'json');
    };

    var _tracksIsChanged = function (newTracks) {
        return JSON.stringify(newTracks) != JSON.stringify(_lastTracks);
    };

	var _showIconText = function (el, number, isRed, ignorePainting, warningClass, shortNumber) {
		if (ignorePainting === undefined || ignorePainting === null || ignorePainting === "") {
			ignorePainting = undefined;
		}
		if (shortNumber === undefined || shortNumber === null || shortNumber === "") {
			shortNumber = false;
		}

		var iconRed = false;
		if (isRed !== undefined && isRed === true) {
			iconRed = true;
		}
		if (number > 0) {
			if (number > 9 && shortNumber) {
				el.addClass('f10').text("+");
			} else {
				el.text(number);
			}
			if (ignorePainting !== true) {
				if (iconRed) {
					el.addClass(warningClass);
				} else if(el.hasClass(warningClass)) {
					el.removeClass(warningClass);
				}
			}
			el.show();
		} else {
			el.hide();
		}
	};
	var _updateInboxMessage = function (el, data, newCount) {
		el.find(".notifications-content-block").html('');
		if (newCount) {
			for (i = 0; i < data.length; i++) {

				if( i >= 8) {
					var msg = t('Показаны %s последних сообщений из %s');
					msg = msg.replace("%s", 8);
					msg = msg.replace("%s", data.length);
					var item = $(document.createElement('div')).addClass('message-count-tip').html(msg);
					el.find(".notifications-content-block").append(item);
					break;
				}

				var item = $(document.createElement('a')).addClass('inbox-message-link').attr('href', data[i].link);
				var image = $(document.createElement('span')).addClass('userimage').append(data[i].avatar);
				image.appendTo(item);
				$(document.createElement('span')).addClass('inbox-message-user-name').html(data[i].text).appendTo(item);
				el.find(".notifications-content-block").append(item);

			}
		} else {
			for (i = 0; i < data.length; i++) {
				var item = $(document.createElement('span')).addClass('inbox-message-link').addClass('empty-inbox-data nowrap').html(data[i].text);
				el.find(".notifications-content-block").append(item);
			}
		}
	};

	var _setNotifyIcons = function (data) {
		window.notifyIsLoad = false;
		if (typeof data.new_message !== 'undefined') {
			var massageCounterEl = $_noticeBlocks.inbox.find('.js-notice__link').find('.massage_counter');

			var isWarningMsg = undefined;
			var ignorePainting = undefined;
			if (data.warning_inbox_count && parseInt(data.warning_inbox_count) > 0) {
				isWarningMsg = true;
			} else if (data.warning_inbox_count && parseInt(data.warning_inbox_count) === 0) {
				isWarningMsg = false;
			} else {
				ignorePainting = true;
			}

			_showIconText(massageCounterEl, data.new_message, isWarningMsg, ignorePainting, "message_counter_warning", true);
			_showIconText($('.js-notice-mobile__message-counter'), data.new_message, isWarningMsg, ignorePainting, "notify-number-block_warning");
			_updateInboxMessage($_noticeBlocks.inbox, data.dialog_data, data.new_message, "message_counter_warning");
		}

		var notificationPopup = $('.js-notice-other').find('.block-popup');
		notificationPopup.addClass('wating').html('');
		if (Utils.isActiveWindow() && notificationPopup.is(':visible')) {
			loadNotify(notificationPopup);
		}

		if (typeof data.notify_unread_count !== 'undefined') {
			var notityCounterEl = $_noticeBlocks.other.find('.js-notice__link').find('.massage_counter');
			_showIconText(notityCounterEl, data.notify_unread_count, data.notify_has_red, undefined, "message_counter_warning", true);
			_showIconText($('.js-notice-mobile__other-counter'), data.notify_unread_count, data.notify_has_red, undefined, "notify-number-block_warning");
		}
	};

	var _updateNotifyTracks = function () {
		$.get('/new_notify', function (response) {
			if (response != '0') {
				_setNotifyTracks(response);
			}
		});
	};

	var _setNotifyTracks = function (data) {
		$_noticeBlocks.other.find('.block-popup').removeClass('wating').html($(data).html());
		$_noticeBlocks.other.find('.foxNotifBox_other').css({'overflow': 'inherit'}).jScrollPane();
		if($_noticeBlocks.other.find('.foxNotifBox_other .jspVerticalBar').length > 0)
			$_noticeBlocks.other.find('.foxNotifBox_other hr').css({'margin-right': '0'});
	};

    return {
        init: function (pullEnable) {
            $_noticeBlocks = {
                'inbox': $('.js-notice-inbox'),
                'other': $('.js-notice-other'),
            };
        },
        setNotifyIcons: _setNotifyIcons,
		updateInboxMessage: _updateInboxMessage
    }
})();

$(function(){
    if($('.needCheckNotify').val() == 1){
        NotifyModule.init(PULL_MODULE_ENABLE);
        if(PULL_MODULE_ENABLE){
            PullModule.on(PULL_EVENT_NOTIFY, NotifyModule.setNotifyIcons);
        }
    }
});

function IDGenerator(prefix){
    this.prefix = prefix;
    this.num = 1;
    this.getID = function(){
        return this.prefix+(this.num++);
    }

}

var changeSocLink = function(userType){
    $("#form-signup .vk, #fox_signup_form .vk").each(function(i) {
		$(this).attr('href', ORIGIN_URL + '/login_soc?type=vk&usertype=' + userType + addRefParam());
	});
    $("#form-signup .fb, #fox_signup_form .fb").each(function(i) {
		$(this).attr('href', ORIGIN_URL + '/login_soc?type=fb&usertype=' + userType + addRefParam());
	});
};

function getDuration(days, count, rate) {
    return Math.ceil(days + (count - 1) * days * rate);
}

$(document).on('click','.js-hide-after-press',function(event){
   $(this).hide();
});


//////
function shortenedTextGetLessSpan(){
    return '<span class="js-shortened-text-less link link_local link_arrow link_arrow_blue link_arrow_up ml10">' + t('Свернуть') + '</span>';
}
function shortenedTextGetMoreSpan(customAction){
    var customActionBlock = '';
    if (typeof(customAction) !== 'undefined'){
        customActionBlock = 'onclick="' + customAction + '"';
    }
    return '<span class="js-shortened-text-more link link_local link_arrow link_arrow_blue link_arrow_down ml10" ' + customActionBlock + '>' + t('Развернуть') + '</span>';
}
function shortenedTextReplace(obj, button){
    var prevTextBlock = $(obj).parent('.js-shortened-text');
    var replaceText = $(prevTextBlock).data('replaceText');

    var $blockClone = $(prevTextBlock).clone();
    $blockClone.find('.js-shortened-text-less, .js-shortened-text-more').remove();
    var curText = $blockClone.html();

    $(prevTextBlock).html(replaceText);
    $(prevTextBlock).data('replaceText', curText);
    if (button === 'less') {
        $(prevTextBlock).append(shortenedTextGetLessSpan());
    }else {
        $(prevTextBlock).append(shortenedTextGetMoreSpan());
    }
}
function initiateShortenedTextMore(){
    $('.js-shortened-text-more').on('click', function(){
        $(this).parent().parent().parent().parent().find('.query-seen_block').removeClass('hide');
        $(this).parent().parent().parent().find('.files-list').removeClass('hide');
        shortenedTextReplace(this, 'less');
        initiateShortenedTextLess();
    });
}
function initiateShortenedTextLess(){
    $('.js-shortened-text-less').on('click', function(){
        $(this).parent().parent().parent().find('.files-list').addClass('hide');
        shortenedTextReplace(this, 'more');
        initiateShortenedTextMore();
    });
}
function initShortenedText(){
    $('.js-shortened-text').each(function(i){
        if ($(this).attr('data-replace-text').replace(/<[^>]+>/g,'').length > $(this).attr('data-min-length') || $(this).parent().parent().find('.files-list').length > 0) {
            if ($(this).attr('data-custom-action') == '') {
                $(this).append(shortenedTextGetMoreSpan());
            }else{
                $(this).append(shortenedTextGetMoreSpan($(this).attr('data-custom-action')));
            }
        }
    });
    initiateShortenedTextMore();
}

$(document).ready(function(){
    initShortenedText();
});
/////

function wantAddView(wantId){
    $.ajax( {
        type: "POST",
        url: '/api/offer/addview?wantId=' + wantId,
        success: function( data, textStatus, xrf ) {
            $(".js-card-"+wantId+" .query-seen_block ").removeClass("hide");
        }
    });
}
(function($) {
    $.fn.textAreaMaxLength = function(maxLength) {
        var checkMaxLength = function (el) {
            var maxlength = el.maxLength;
            if ($(el).val().length > maxlength) {
                var newDesc = el.oldDesc + $(el).val().substr(0, maxlength - el.oldDesc.length);
                $(el).val(newDesc);
            }
            el.oldDesc = $(el).val();
        };

        return this.each(function () {
            this.maxLength = maxLength;
            this.oldDesc = '';

            var module = this;
            $(this).keyup(function () {
                checkMaxLength(module);
            });
            $(this).change(function () {
                checkMaxLength(module);
            });
        });
    }
})(jQuery);

function about_text_hide(){
    if($(window).width()<=767){
        if($('.b-about-text .b-about-text_container .b-about-text_container_text').height()>54){
            $('.toggle-about-text').show();
        }
    }

}
$(document).on('click touchstart','.toggle-about-text',function(){
    $('.b-about-text').toggleClass('active');
    if($(this).hasClass('toggle-about-text_hide')){
        $('html, body').scrollTop($('.b-about-text').offset().top-100);
    }
    return false;
})


$(document).on('touchstart','.js-tooltip-block',function(){
    if(!$(event.target).is("a")){
        $(this).remove();
    }
})


$(document).ready(function () {
	/*Определяем мобильное ли устройство*/
	about_text_hide();
})
$(document).on('click touchstart','.render_desktop-js',function(){
    var date = new Date();
    var days = 360;
    date.setTime(date.getTime() + (days*24*60*60*1000));
    setCookie('only_desktop_version', true, { expires: date,path:'/',SameSite:'Lax'});
    setTimeout(function(){
        /*на всякий случай делаем задержку перед перезакгрзкой*/
        window.location.replace(setGetParameter('desktop','true'));
    },300)

})

function setGetParameter(paramName, paramValue)
{
    var url = window.location.href;
    var hash = location.hash;
    url = url.replace(hash, '');
    if (url.indexOf(paramName + "=") >= 0)
    {
        var prefix = url.substring(0, url.indexOf(paramName + "="));
        var suffix = url.substring(url.indexOf(paramName + "="));
        suffix = suffix.substring(suffix.indexOf("=") + 1);
        suffix = (suffix.indexOf("&") >= 0) ? suffix.substring(suffix.indexOf("&")) : "";
        url = prefix + paramName + "=" + paramValue + suffix;
    }
    else
    {
        if (url.indexOf("?") < 0)
            url += "?" + paramName + "=" + paramValue;
        else
            url += "&" + paramName + "=" + paramValue;
    }
    return url + hash;
}

$(document).on('click touchstart','.render_mobile-js',function(){


    deleteCookie('only_desktop_version');
    setTimeout(function(){
        /*на всякий случай делаем задержку перед перезакгрзкой*/
        location.reload(true);
    },300)

})


$(document).on('ready',function() {
    function slickInit(selectir) {
        if (!selectir) return false;

        jQuery(selectir).slick({
            arrows: false,
            responsive: [
            {
                breakpoint: 768,
                settings: {
                    dots: true,
                    infinite: true,
                    speed: 300,
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    autoplay: true,
                    autoplaySpeed: 3000,
                    arrows: false,
                }
            },
            {
                breakpoint: 5000,
                settings: "unslick"
            }
          ]
        });
    }
    if ($('.index-advantage-block').length > 0) slickInit('.index-advantage-block');
    if ($('.real-case').length > 0) slickInit('.real-case');
    if ($('.landing-how-it-works .icons').length > 0) slickInit('.landing-how-it-works .icons');

    //показываем слайдер на мобильных экранах при ресайзе
	var allPageEl = $('.all_page');
    if (allPageEl.hasClass('is_index') || allPageEl.hasClass('is_land') || allPageEl.hasClass('is_cat')) {
        $(window).resize(function () {
            var w = $(window).width();
            if (w < 768) {
                var indexAdvantageBlock = $('.index-advantage-block');
                var realCase = $('.real-case');
                var landingHowItWorksIcons = $('.landing-how-it-works .icons');

                if (indexAdvantageBlock.length > 0 && indexAdvantageBlock.hasClass('slick-initialized') === false) {
                    slickInit('.index-advantage-block');
                }
                if (realCase.length > 0 && realCase.hasClass('slick-initialized') === false) {
                    slickInit('.real-case');
                }
                if (landingHowItWorksIcons.length > 0 && landingHowItWorksIcons.hasClass('slick-initialized') === false) {
                    slickInit('.landing-how-it-works .icons');
                }
            }
        });
    }

	//перестраиваем меню на экранах до 940px
	// if ($('.header').hasClass('is_index')) {
		//запоминаем изначальное меню, чтобы вернуть его при ресайзе окна
		var catMenuThin = $('.header_top .cat-menu-thin');
		var catMenuThinDefault = catMenuThin.html();

		var w = $(window).width();
		if (w < 940) {
			//перестраиваем меню, уменьшая количество видимых пунктов до 5
			catMenuRebuild(catMenuThin, 5);
		} 
		$(window).resize(function() {
			var w = $(this).width();
			if (w < 940) {
				//перестраиваем меню, уменьшая количество видимых пунктов до 5
				catMenuRebuild(catMenuThin, 5);
			}
			else {
				if (catMenuThin.hasClass('is-mobile')) {
					//возвращаем изначальное меню
					catMenuThin.html(catMenuThinDefault).removeClass('is-mobile');
				}
			}
		});
	// }
});

//перестраиваем меню на экранах до 940px
function catMenuRebuild(list, items) {
	var ul			= $(list);
	var li			= ul.children('li:not(".cat-menu_item_more")');
	var itemsCount	= li.length;
	var itemsMore	= '';

	if (itemsCount > items && ul.hasClass('is-mobile') === false) {
		//добавляем списку класс is-mobile для избежания перестройки при ресайзе
		ul.addClass('is-mobile');

		var i = 0;
		li.each(function() {
			i++;

			if (i > items) {
				//вырезаем лишние пункты меню и заносим их в переменную itemsMore
				var link;
				if (link = $(this).children('.category-menu__list_item')) {
					itemsMore += '\r\n' +
						'<li><a href="' + link.attr('href') + '">' + link.text() + '</a></li>';
					$(this).remove();
				}
			}
		});

		//добавляем пункт меню "Еще", если его нет
		if (ul.children('li.cat-menu_item_more').length === 0) {
			list.append('<li class="cat-menu_item_more">' +
				'<span class="f14 cat-menu-thin_more pt5">'+t('Еще')+ '</span>' +
				'<div class="menubox" style="display: none;">\n' +
				'<div class="menulist last">\n' +
				'<ul></ul>' +
				'</div>' +
				'</div>'
			);
		}

		//дописываем удаленные пункты меню в "Еще"
		ul.children('li.cat-menu_item_more').find('ul').prepend(itemsMore);
	}
}

function scrollToAnchor(aid){
    var aTag = $("a[name='"+ aid +"']");
    $('html,body').animate({scrollTop: aTag.offset().top},'slow');
}

function getElementTopToScroll($el) {
	return $el.offset().top - ($(window).height() - $el.outerHeight(true)) / 2;
}

$(document).on('keydown', '.js-alt-send', function(e){
    if (e.ctrlKey && e.keyCode == 13) {
        $(this).closest('form').trigger('submit');
    }
});

$(function(){
	$('.js-show-cart').click(function() {
		$('html, body').animate({scrollTop:0}, 'slow', function() {
			$('.cart-popup').find('.block-popup').fadeIn(200, 'swing');
			$('.cart-popup .block-popup_body').jScrollPane();
			update_cart();
			refreshCartScroll();
			$('.cart-popup .cart-popup_icon').addClass('active');
		});
	});
	$('.js-abandoned-basket__close').on('click', function() {
		$(this).closest('.abandoned-basket').slideUp(300);
		$.post('/api/cart/closeabandonednotify', function() {});
	});
});

$(function() {
	/* Show/hide menu on swipe */
	if (isMobile() && $(window).width() < 768 && navigator.userAgent.indexOf('KworkMobileAppWebView') === -1 && jQuery.fn.swipe) {
		$('body').swipe({
			swipeLeft: function() {
				if ($('.header .foxmenubutton').is(':visible')){
				   mobile_menu_hide()
				}
			},
			 swipeRight: function() {
				 if ($('.header .foxmenubutton').is(':visible')){
				    mobile_menu_toggle()
				 }
			},
            excludedElements: $.fn.swipe.defaults.excludedElements + ', button, input, select, textarea, a, *[contenteditable=true]',
			threshold:250
		});
	}
});

$(function () {
	if(window.location.pathname === '/track' && window.location.hash) {
		var hash = window.location.hash;

		if ('scrollRestoration' in history) {
			history.scrollRestoration = 'manual';
		}

		var $target = jQuery(hash);

		if($target.length) {
			setTimeout(function() {
				$('html, body').animate({
					scrollTop: $target.offset().top - 100
				}, 400, function () {
					history.replaceState(null, null, ' ');
				});
			}, 200);
		}
	}
})

$(document).on('submit', '#addextrastop, #addpackagestop, .upgradepackagestop, .js-reserve-stage-form, .worker_extra_suggestion_form, .js-project-want, #js-tips-send-form', function(e){
	e.preventDefault();
	recentFormToSubmit = $(this);

	var submit_button = $(this).find('button');
	submit_button.prop('disabled', true).addClass('disabled');

	var formData = $(this).serializeArray();

	// delete empty value for old Safari
	for (var i in formData) {
		if (formData[i]['value'] === "") {
			delete formData[i];
		}
	}

	if($(this).hasClass('js-reserve-stage-form')) {
		window.redirectInit = true;
	}

	$.ajax({
		url:$(this).attr('action'),
		type:'post',
		data: formData,
		dataType:'json',
		success:function(response) {
			if (response.result == 'success') {
				if (typeof(response.redirectUrl) !== 'undefined') {
					if(response.redirectUrl.indexOf('#') >= 0) {
						document.location.href = response.redirectUrl;
						document.location.reload();
					} else {
						document.location.href = response.redirectUrl;
					}
				}else{
					document.location.reload();
				}
			}
			else if (response.status == "error") {
				if ($('.field-error', recentFormToSubmit).length) {
					$('.field-error', recentFormToSubmit).html(response.message).show();
				} else {
					alert(response.message);
				}
			}
			else {
				if (response.error === 'funds') {
					show_balance_popup(response.difference, '', response.payment_id, response.orderId);
				}
				submit_button.prop('disabled', false).removeClass('disabled');
			}
		},
		error: function(){
			submit_button.prop('disabled', false).removeClass('disabled');
		}
	});
});

$(document).on('input','.js_clearingInput', function() {
    if($(this).val() != "") {
        $(this).parent().find('.js_clearBtn').show();
    }
    else {
        $(this).parent().find('.js_clearBtn').hide();
    }
});
$(document).on('click touchstart','.js_clearBtn', function() {
    $(this).hide();
    $(this).parent().find('.js_clearingInput').val('');
});
$(document).ready(function(){
    $('.js-poll-notify__close').on('click', function(){
        $(this).closest('.abandoned-basket').slideUp(300);
        $.post('/api/user/closepollnotify', function(){

        });
    });
    $('.abandoned-basket').each(function(i){
        $(this).css('bottom', (i * ($(this).height() + 1)) + 'px');
    });
    if ($('.abandoned-basket').length > 1) {
        $('.scrollup').css('bottom', ($('.abandoned-basket').length * $('.scrollup').height()) + 'px');
    }
});

function SlideModule(){
    var _$slider;
    var _count;
    var _deepLoadIframe;
	var _mode;

    var _initSlider = function(settings){
        _deepLoadIframe = 2;
        _setCount(settings.sliderCount)
        _$slider.slick(settings);
        _count = _$slider.slick('getSlick').slideCount;
        _startLazyLoad();
		_mode = settings.mode;

    };

    var _setEvents = function(){
        _$slider.on('beforeChange', function(event, slick, currentSlide, nextSlide){
            if(player){
             player.pauseVideo();
        }
            _lazyLoadBg(nextSlide);
        });


		if(_mode === "desktop"){
			window.onkeydown = pressed;
			function pressed(e) {
				if ($('.popup-portfolio').length) {
					return;
				}

				if(e.keyCode === 37){
					_$slider.slick("slickPrev");
				}
				if(e.keyCode === 39){
					_$slider.slick("slickNext");
				}
			}
		}
    };

    var _setCount = function(isCount){
        if(isCount){
            _$slider.on('init reInit afterChange', function (event, slick, currentSlide, nextSlide) {
                var i = (currentSlide ? currentSlide : 0) + 1;
                if(slick.slideCount>1){
                    $('.kwork_slider_mobile_counter').html('<b>'+i+'</b>' + ' из ' + '<b>'+slick.slideCount+'</b>')
                }
            });
        }
    }

    var _lazyLoadIframe = function(index){
        var iframes = $('.sliderItem[data-index=' + index + ']').find('iframe');
            if(iframes.length > 0){
                iframes.each(function(indx, element){
                    var $el = $(element);
                    if($el.prop('src').length > 0)
                        return;
                    var src = $el.data('lazy');
                    if(!src)
                        return;
                    $el.attr('data-lazy', '');
                    $el.prop('src', src);
                });
            }
    };

    var _lazyLoadBg = function(index){
        var els = $('.sliderItem[data-index=' + index + ']').find('.lazy-bg');
            if(els.length > 0){
                els.each(function(indx, element){
                    var $el = $(element);
                    if($el.css('background-image') != 'none')
                        return;
                    var src = $el.data('lazy');
                    if(!src)
                        return;
                    $el.attr('data-lazy', '');
                    $el.css('background-image', 'url(' + src + ')');
					$el.onload = function()
					{
						_lazyLoadIframe(index);
						_lazyLoadSlide(index);
					};
                });
            }
    };

    var _lazyLoadSlide = function (index) {
        var $currentSlide = $('.sliderItem[data-index=' + index + ']');
        var $el = $currentSlide.find('.js-sliderImage-picture');
        if (!$el.length) {
            return;
        }

        var src = $el.data('lazy');
        if (!src) {
            return;
        }

        $el.hide();

        var img = new Image();
        img.onload = function () {
            $el.show();
            $el.show();
            $el.attr('src', src);
        };

        img.src = src;

        $el.attr('data-lazy', '');

    };

    var _startLazyLoad = function(){
        _lazyLoadBg(0);
        _lazyLoadSlide(0);
        _lazyLoadIframe(0);
        var index = 1;
        while(index >= _deepLoadIframe){
            _lazyLoadIframe(index);
            _lazyLoadIframe(_count - index);
            index++;
        }
    };

    return{
        init: function(options){
            _$slider = $(options.sliderName);
            _initSlider(options.settings);
            _setEvents();
            _setCount(options.settings.sliderCount);
        }
    }
}

var player;
$(document).on('click','.play-video-js',function(){
    var current_element = $(this)
    var video_id = $(this).attr('data-video-id');
    var container = $(this).parents('.kwork-slider_videoWrapper').find('#player')[0]
    player = new YT.Player(container, {
        height: '433',
        width: '650',
        videoId: video_id,
        events: {
            'onReady': onPlayerReady
        }
    });
})
function onPlayerReady(event) {
	if(isNotMobile()){
		event.target.playVideo();
	}

}

$(document).on('click', '.signup-promo-placeholder-js', function(){
    $(this).hide();
    $(this).parent().parent().find('.signup-promo-field').show();
});

var loginCheckTimeout = null, loginCheckPost = null;

function generateRandomKey(len) {
	var length = len || 20;
	var text = '';
	var chars = 'abcdefghijklmnopqrstuvwxyz0123456789';

	for (var i = 0; i < length; i++) {
		text += chars.charAt(Math.floor(Math.random() * chars.length));
	}
	return text;
}

function initSingupHandlers() {
	var form = $('#form-signup');

	form.find('.signup-login-field .styled-input').on('input', function(e) {
		clearTimeout(loginCheckTimeout);
		if(loginCheckPost) {
			loginCheckPost.abort();
		}

		var tg = $(e.target);
		var text = tg.val();
		var clientError = '', mustFix = false;
		if(text.length > 20) {
			text = text.substr(0, 20);
			tg.val(text);
			clientError = t('Логин не может быть длиннее {{0}} символов', [userLoginLength]);
		}
		if(!/^[a-zA-Z0-9-_]*$/i.test(text)) {
			clientError = t('Логин может содержать только латинские буквы, цифры и знаки - и _');
			mustFix = true;
		}
		form.find('.signup-login-field .error').text(clientError);
		if (mustFix) {
			form.find('.signup-login-field input').addClass('has-error');
			form.find('.signup-login-field .preloader__ico').css({'display': 'none'});
			return;
		}
		form.find('.signup-login-field input').removeClass('has-error');
		form.find('.signup-login-field .preloader__ico').css({'display': 'block'});

		loginCheckTimeout = setTimeout(function() {
			var error = '';
			if(text.length < 4) {
				error = t('Логин должен быть не короче 4-х символов');
			}
			if(error != '') {
				form.find('.signup-login-field .error').text(error);
				form.find('.signup-login-field input').addClass('has-error');
				form.find('.signup-login-field .preloader__ico').css({'display': 'none'});
			} else {
				loginCheckPost = $.post('/api/user/checklogin', {jsub: '1', login: text}, function(r) {
					form.find('.signup-login-field .preloader__ico').css({'display': 'none'});
					if(r.success) {
						form.find('.signup-login-field input').removeClass('has-error');
						form.find('.signup-login-field .error').text(clientError);
					} else {
						form.find('.signup-login-field .error').text(t('Этот логин уже используется.'));
						form.find('.signup-login-field input').addClass('has-error');
					}
				});
			}
		}, 1000);
	});
}

function getQuickPrice(price){
    var minPrice = MIN_PRICE;
    if(actor_lang == 'ru' && lang != 'ru'){
        minPrice = minPrice * CURRENCY_RATE * 100;
        minPrice = Math.round(minPrice) / 100.0;
    }
    var newPrice;
    newPrice = Math.floor(price / 2);
    if (newPrice < minPrice) {
        newPrice = minPrice;
    }
    return newPrice;
}

function getQuickTime(time){
    var minTime = 1;
    var quickTime;
    quickTime = Math.round(1.0972 * Math.pow(time, 0.6305));
    if (quickTime < minTime) {
        quickTime = minTime;
    }
    if (quickTime === 2) {
        quickTime = 1;
    }
    if (time === 4 || time === 5) {
        quickTime = 2;
    }
    if (time === 7) {
        quickTime = 3;
    }
    return quickTime;
}
var notifyIsLoad = false;
function loadNotify(insertTo) {
	if (notifyIsLoad !== false) {
		return false;
	}
	notifyIsLoad = true;
	$.get('/new_notify', function (data) {
		data = $(data).html();
		var notificationBlock = $(insertTo);
		notificationBlock.html(data).promise().done(function() {
			$(insertTo).removeClass('wating');
			window.popupJscrollPane = notificationBlock.find('.foxNotifBox_other').css({'overflow': 'inherit'}).jScrollPane();

			if (notificationBlock.find('.jspVerticalBar').length > 0) {
				notificationBlock.find('hr').css({'margin-right': '0'});
			}

			notificationBlock.on('click', 'a[data-type="want"]', function() {
				Api.request('notify/setreadbytype', {type: 'want_connects_added'}, function(res) {});
			});
		});
	});
}


function showPortfolioLimitedInfo(currentLimit, expandedLimit) {
	var html = '<h1 class="popup__title">' + t('Расширить лимит очереди') + '</h1>\n\
	<hr class="gray mt20 balance-popup__line">\n\
	\n\
	<div class="pb10"><strong>' + t('Загрузка работ в профиль и лимит очереди') + '</strong></div><div class="pb10">' + t('В некоторых творческих разделах (иллюстрации, веб-дизайн, логотипы и т.д.) есть возможность загрузить выполненные работы в свой профиль. После того, как вы сдали кворк покупателю, и он был оплачен, на странице заказа появляется предложение загрузить выполненную работу в профиль. Она будет отображаться вместе с отзывом покупателя.') + '</div>\n\
\n\
	<div class="pb10">' + t('Используйте эту возможность не только для привлечения новых покупателей, но и для расширения лимита очереди.') + '</div>\n\
\n\
	<div class="pb10 pt20"><strong>' + t('Как отследить, сколько работ загружено?') + '</strong></div>\n\
\n\
	<div class="pb10">' + t('Смотрите это на странице <a href="/manage_kworks">Мои кворки</a> в окошке нужного кворка. Там будет указан процент загруженных работ за последние 30 дней.') + '</div>';
	show_popup(html, 'popup-portfolio_limited', '', 'popup-portfolio_limited');
	return false;
}
function hidePortfolioLimitedNotice(target) {
	$(target).closest('.event-message').hide();
	$.get('/api/portfolio/hidelimitnotice');
}

/**
 * Обрезание текста. В конце проставляет многоточие.
 */
$(function () {
    $('.js-multi-elipsis').each(function(){
		multiEllipsis($(this));
    });
});

function multiEllipsis($block) {
	var $div = $block.children('div').eq(0);
	if ($div.length < 1) {
		return;
	}
	var $textEl = $div.find('.js-multi-elipsis-text').eq(0);
	if ($textEl.length < 1) {
		$textEl = $div;
	}
	var blockHeight = $block.height();
	
	// emoji заменяем на шорткод
	if (window.emojiReplacements) {
		$textEl.html(window.emojiReplacements.spanToshortcode($div.html()));
	}

	$textEl.text(function (index, text) {
		return text.trim();
	});

	while ($div.height() > blockHeight) {
		var changed = false;
		$textEl.text(function (index, text) {
			var newText = text.replace(/(?:\n\n|\r\r)/g, '\n');
			newText = newText.replace(/\s*(\S+)$/, '...');
			if (text != newText) {
				changed = true;
			}
			return newText;
		});
		if (!changed) {
			break;
		}
	}
	
	// Заменяем код на emoji
	if (window.emojiReplacements) {
		$textEl.html(window.emojiReplacements.shortcodeToSpan($div.html()));
	}
	
}

function hidePackageLimitedNotice(target) {
    $(target).closest('.event-message').hide();
    $.get('/api/package/hidelimitnotice');
}

function reorderError(response) {
    switch(response.error) {
        case 'wrong_kwork_id':
            alert(t('Кворк не найден'));
            break;

        case 'login':
            location.assign('/login');
            break;

        case 'purse':
            show_balance_popup(response.purse_amount, 'kwork', undefined, response.orderId);
            break;

        case 'wrong_package_type':
            alert(t('Неправильный тип пакета'));
            break;

        case 'wrong_language':
            alert(t('Неправильный язык'));
            break;

        case 'lang_mismatch':
            alert(t('Неправильный язык'));
            break;

        default:
            alert(t('Неизвестная ошибка'));
    }
}
$(document).on('click', '.js-reorder', function () {
    var kworkId = $(this).data('kworkId');
    var packageType = $(this).data('package');
    var stagesType = $(this).data('stages-type');
    var isQuick = !!$(this).data('quick');

	var button = $(this);

	orderCreate({
		kworkId: kworkId,
		packageType: packageType,
		stagesType: stagesType,
		isQuick: isQuick,
		button: button,
	});
});

/**
 * "Заказать еще" заказ
 * @param fParams
 */
function orderCreate(fParams) {
	fParams.button.prop('disabled', true).addClass('disabled');
	if (fParams.stagesType) {
		$('.js-track-stage-add-link').click();
		fParams.button.prop('disabled', false).removeClass('disabled');
	} else if (fParams.packageType) {
		var params = {
			kworkId: fParams.kworkId,
			packageType: fParams.packageType,
			isQuick: fParams.isQuick ? 1 : 0
		};
		$.post('/api/order/packagecreate', params, function (response) {
			if (response.dataLayer) {
				GTM.pushDataLayers(response.dataLayer);
			}

			if (response.success === true) {
				setTimeout(function () {
					window.location = response.redirect;
				}, 1000);
			} else {
				fParams.button.prop('disabled', false).removeClass('disabled');
				reorderError(response);
			}
		}, 'json');
	} else {
		var params = {
			EPID: fParams.kworkId,
			order_quick: fParams.isQuick ? '1' : ''
		};
		$.post('/api/order/create', params, function (response) {
			if (response.dataLayer) {
				GTM.pushDataLayers(response.dataLayer);
			}

			if (response.success === true) {
				setTimeout(function () {
					window.location = response.redirect;
				}, 1000);
			} else {
				fParams.button.prop('disabled', false).removeClass('disabled');
				reorderError(response);
			}
		}, 'json');
	}
}

function nl2br (str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br/>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
}

$(function () {
    if (typeof ion !== 'undefined') {
        ion.sound({
            sounds: [
                {
                    name: "new_message",
                    preload: false
                }
            ],
            volume: 0.2,
            path: "/js/libs/sounds/",
            preload: true
        });

	    if (typeof(Storage) !== "undefined") {
		    var tabId = Math.random();
		    localStorage.setItem("soundTabId", tabId);
		    setInterval(function(){ localStorage.setItem("soundTabId", tabId); }, 3000);
        }
		if (PULL_MODULE_ENABLE) {
			PullModule.on(PULL_EVENT_NEW_INBOX, function () {
				if (MESSAGE_SOUND_ENABLE == 1) {
					if (typeof(Storage) === "undefined" || localStorage.getItem("soundTabId") == tabId) {
						ion.sound.play("new_message");
					}
				}
			});
		}
    }
});
function setMessageSound(target) {
    MESSAGE_SOUND_ENABLE = (MESSAGE_SOUND_ENABLE == 1)?0:1;
    $(".message_sound_block .js-message-sound-ico").toggleClass("hidden");
    $.ajax({
        url:"/api/inbox/setmessagesound",
        data:{message_sound:MESSAGE_SOUND_ENABLE},
        dataType:"json",
        type:"post",
        success:function(response) {
            if(response.status === "success") {
                localStorage.setItem('change-sound-message', MESSAGE_SOUND_ENABLE);
            }
            else {
                MESSAGE_SOUND_ENABLE = (MESSAGE_SOUND_ENABLE == 1)?0:1;//Если произошла ошибка, вернуть в предыдущее состояние
            }
        },
        error:function(response) {
            MESSAGE_SOUND_ENABLE = (MESSAGE_SOUND_ENABLE == 1)?0:1;//Если произошла ошибка, вернуть в предыдущее состояние
        }
    });
}

$(window).bind('storage', function (e) {
    if(e.originalEvent.key === 'change-sound-message'){
        MESSAGE_SOUND_ENABLE = e.originalEvent.newValue;
        $(".message_sound_block .js-message-sound-ico").toggleClass("hidden");
    }
});

function getBillAmount(amount){
    var sum = parseInt(amount);
    var sumComission = 0;
    if (sum > 0) {
        sumComission = Math.round(sum + (sum * parseFloat(BILL_COMISSION)) / 100);
    }
    return sumComission;
}
function ValidInputsModule (){
    var _selectors = {
        inputError: '.input-error',
        input: '.input_field-js',
        inputName: '.input_name-js',
        inputEmail: '.input_email-js',
        inputMessage: '.input_message-js',
        form: '.send_contact_message-js',
    };


    _checkInputs = function(){
        var isError = false;

        var nameError = _checkName();
        if(nameError.length > 0){
            isError = true;
            _showError(_selectors.inputName, nameError);

        }

        var messageError = _checkMessage();
        if(messageError.length > 0){
            isError = true;
            _showError(_selectors.inputMessage, messageError);
        }

        var emailError = _checkEmail();
        if(emailError.length > 0){
            isError = true;
            _showError(_selectors.inputEmail, emailError);
        }

        if(isError){
            return false;
        }
    };

    _showError = function(selector, text){
        $(selector).find(_selectors.inputError).text(text);
        $(selector).find(_selectors.inputError).show();
    }

    _checkName = function(){
        var val = $(_selectors.inputName + ' input').val();
        if(val.length == 0){
            return t('Нужно ввести имя');
        }
        return '';
    };

    _checkMessage = function(){
        var val = $(_selectors.inputMessage + ' textarea').val();
        if(val.length == 0){
            return t('Нужно ввести сообщение');
        }
        return '';
    };

    _checkEmail = function(){
        var val = $(_selectors.inputEmail + ' input').val();
        if (val === undefined) {
        	return '';
		}
        if(val.length == 0){
            return t('Нужно ввести email');
        }
        if(!validateEmail(val)){
            return t('Неверный формат email');
        }
        return '';
    };

    _scrollToError = function(selector) {
        if ($(selector).length > 0) {
            $('html,body').animate({scrollTop: $(selector).offset().top}, 800);
        }
    };

    _clearError = function(el){
        var $errorCon = $(el).closest(_selectors.input).find(_selectors.inputError);
        $errorCon.text("");
        $errorCon.hide();
        return true;
    };

    var _setEvents = function(){
        $(_selectors.form).on('submit', _checkInputs);

        $(_selectors.input + ' input, ' + _selectors.input + ' textarea').each(function(i, el){
            $(el).keyup(function(){_clearError(el)});
        });
    };

    return {
        init: function(){
            _setEvents();
            _scrollToError(_selectors.inputError + ':visible');
        },
		showError: _showError
    };
};

if (typeof USER_KWORK_BLOCKED !== 'undefined') {
    $(document).on('click', '.js-blocked-kworks', function () {
        var html = '<div class="b-popup-content">\
			<div>\
	        <div class="ico-i-small" style="position: absolute;"></div>\
	        <div style="margin-left: 40px" class="f14">';
		if(USER_KWORK_BLOCKED.blockType === "abuse"){
			html = t('Ваши кворки заблокированы за штрафные баллы.<br>Время разблокировки указано в уведомлении о разблокировке.');
		}
		if(USER_KWORK_BLOCKED.blockType === "admin" || USER_KWORK_BLOCKED.blockType === "take_away"){
			html = t('Ваши кворки заблокированы до {{0}}', [USER_KWORK_BLOCKED.dueDate]) + '<br>' +
                        t('Причина: {{0}}', [USER_KWORK_BLOCKED.reason]);
		}
	html += '</div>\
	</div>\
	';
        show_popup(html);
        return false;
    });
}
/**
 * jQuery-модуль
 * Ползволяет устанавливать иконку online/offline через вебсокеты
 */
(function ($) {
	var ONLINE_TIME = 300;
	var IMAGE_PATH = '/images';

	var _setOfflineTimeout = function(){
		var onlineTimer = setTimeout(function () {
			if ($(this).data('withText')) {
				$(this).html('<i class="dot-user-status dot-user-offline_dark"></i> Офлайн');
			} else {
				$(this).removeClass('dot-user-online');
				$(this).addClass('dot-user-offline');
			}
			$(this).data('isOnline', false);
		}, ONLINE_TIME * 1000);
		$(this).data('onlineTimerId', onlineTimer);
	};

	var _setOnlineIcon = function (response) {
		if (response && (response.userId ^ 0) !== ($(this).data('userId') ^ 0)) {
			return;
		}

		clearTimeout($(this).data('onlineTimerId'));
		_setOfflineTimeout.apply(this);

		//Отправляем в модуль
		document.dispatchEvent(new CustomEvent('user-status', {
			detail: {
				status: response.status,
				user_id: response.userId
			}
		}));
		if (response.status === "online") {
			if ($(this).data('withText')) {
				$(this).html('<i class="dot-user-status dot-user-online"></i> ' + t('Онлайн'));
			} else {
				$(this).removeClass('dot-user-offline dot-user-offline_dark');
				$(this).addClass('dot-user-online');
			}
		} else {
			if ($(this).data('withText')) {
				$(this).html('<i class="dot-user-status dot-user-offline"></i> ' + t('Оффлайн'));
			} else {
				$(this).removeClass('dot-user-online');
				$(this).addClass("dot-user-offline");
			}
		}

		$(this).data('isOnline', true);
	};

	var _add = function () {
		var userId = $(this).data('userId');

		if (PULL_MODULE_ENABLE) {
			PullModule.addOnlineUserChannel(userId);
			PullModule.on(PULL_EVENT_IS_ONLINE, _setOnlineIcon.bind(this));
		}
		if ($(this).data('isOnline')) {
			_setOfflineTimeout.apply(this);
		}
	};

	var methods = {
		init: function () {
			return this.each(_add);
		},
		add: _add
	};

	$.fn.onlineWidget = function (method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error(t('Метод с именем {{0}} не существует для jQuery.onlineWidget', [method]));
		}
	};
})(jQuery);
/**
 * jQuery-модуль
 * Получить значение полей формы в виде объекта 
 */
(function ($) {
	$.fn.serializeSendData = function () {
        var res = {};
        this.find("[name]").each(function () {
             res[this.name] = [this.value];
        });
        return res;
    };
})(jQuery);

function hideTechnicalWorksNotification(target) {
    Api.request("user/hidetechnicalworksnotification");
    $(target).closest('.event-message').hide();
}

function checkBadEmailDomains(email) {
	var badDomains = ['@hotmail.com', '@outlook.com'];
	var isBadEmail = false;
	badDomains.forEach(function (item, i, arr) {
		if (email.indexOf(item) > 0) {
			isBadEmail = item.substring(1);
		}
	});
	return isBadEmail;
}

function addT(base, translate) {
	translates[base] = translate;
}
function t(base, params) {
	if (typeof translates != 'undefined' && typeof translates[base] != 'undefined') {
		return replacePlaceHolders(translates[base], params);
	}
	return replacePlaceHolders(base, params);
}
function replacePlaceHolders(template, placeholders) {
    var result = template;
    if (typeof placeholders != 'undefined') {
        for (var i = 0; i < placeholders.length; i++) {
            result = result.replace('{{' + i + '}}', placeholders[i]);
        }
    }
    //Поскольку перевод может использоваться и для шаблона тут делается замена %%
    return result.replace("%%", "%");
}
/**
 * Аналог функции in_array в php для Javascript
 * @param {string|int} needle
 * @param {array} haystack
 * @param {bool} strict
 * @returns {Boolean}
 */
function in_array(needle, haystack, strict) {	// Checks if a value exists in an array
	//
	// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	var found = false, key, strict = !!strict;
	for (key in haystack) {
		if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {
			found = true;
			break;
		}
	}
	return found;
}

/**
 * Проверка email согласно формата RFC 822
 * @param {string} email
 * @return bool
 */
function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/i;
    return re.test(email);
}

/**
 * Сделать первую букву заглавной
 * @returns {String}
 */
String.prototype.ucFirst = function () {
	var str = this;
	if (str.length) {
		str = str.charAt(0).toUpperCase() + str.slice(1);
	}
	return str;
};

$(document).on('change','.count-order-in-page-js',function () {
    if($(this).val().length>0){
        var updateParams = {
            limit: $(this).val()
        };
        if($(".order-search-field-js").val() != ""){
            updateParams['search'] = $(".order-search-field-js").val();
        }
        window.location.href = '?' + getUpdatedUrlParamsString(updateParams);
    }
});


// Выпадающий список выбора языка
$(document).ready(function (){
	$(".lang-selector")
		.mouseenter(function(){
			$(this).find(".lang-selector-link").addClass("active");
			$(this).find(".block-popup").fadeIn(200, 'swing');
		})
		.mouseleave(function(){
			$(this).find(".lang-selector-link").removeClass("active");
			$(this).find(".block-popup").hide();
		});
});

function clearPriceStr(str) {
    if(typeof str == "string") {
        str = str.replace(/\s/g, "").replace(",",".");
    }
    return str;
}

function showInworkInfo(target) {
	target.find('img').toggleClass('rotate180');
	$(".js-block-inwork-info").toggleClass("hidden");

}
function hideInworkInfo() {
	$(".js-link-show-inwork-info").find('img').removeClass('rotate180');
	$(".js-block-inwork-info").addClass("hidden");
}

$(document).on('click', '.bages-show-all', function() {
	$('.bages-container').toggleClass('max-height');
	$(this).remove();
});

$(document).on('click', '.kworks-show-all', function() {
	$('.user-kwork-list-container').removeClass('user-kwork-list');
	$(this).addClass('hidden');
	$('.kworks-hide-all').removeClass('hidden');
});

$(document).on('click', '.kworks-hide-all', function() {
	$('.user-kwork-list-container').addClass('user-kwork-list');
	$(this).addClass('hidden');
	$('.kworks-show-all').removeClass('hidden');
});

$(function(){
	function generateMoreBlock() { // создаем блок с кнопкой в "Мои кворки" при динамическом изменении размера окна
		$('<div class="mt10 m-text-center js-more-block"><a href="#show_all" class="kworks-show-all fs16 fw600" onclick="return false;">Показать все кворки продавца</a><a href="#hide_all" class="kworks-hide-all fs16 fw600 hidden" onclick="return false;">Скрыть кворки продавца</a></div>').insertAfter($('.user-kwork-list-container'));
		$('.user-kwork-list-container').addClass('user-kwork-list');
	}

	$(window).on('load resize', function(){
		var kworkBlockCount = $('.user-kwork-list-container .cusongsblock').length,
			windowWidth = $(window).width(),
			moreBtn = $('.kworks-show-all'),
			moreBlock = $('.js-more-block');
			
		if (moreBtn.length == 0) {
			if (kworkBlockCount > 6 && windowWidth >= 751 && windowWidth < 987) {
				generateMoreBlock();
			}
			if (kworkBlockCount > 8 && windowWidth >= 987 && windowWidth < 1229) {
				generateMoreBlock();
			}
		} else {
			if (windowWidth >= 751 && windowWidth < 987 && kworkBlockCount <= 6) {
				moreBlock.hide();
			} else if (windowWidth >= 987 && windowWidth < 1229 && kworkBlockCount <= 8) {
				moreBlock.hide();
			} else if (windowWidth >= 1230 && kworkBlockCount <= 10) {
				moreBlock.hide();
			} else {
				moreBlock.show();
			}
		}
	});
});

$(document).on('click', '.inbox-allow-request-notification button', function() {
    var container = $(this).closest('.inbox-allow-request-notification');
    var inboxId = $(container).data('id');
    $.ajax({
        type: 'POST',
        url: '',
        async: true,
        data: {
            action: 'allowRequest',
            inboxId: inboxId,
            isAccept: ($(this).hasClass('accept') ? 1 : 0)
        },
        success: function(r) {
            $(container).next('span').removeClass('hidden');
            $(container).remove();
        }
    }, 'json');
});

function makeOrderShortLink(id) {
	return ORIGIN_URL + '/z/' + parseInt(id + '', 10).toString(36);
}

function replaceTextMessage(message) {
	var newmsg = message.replace(/<br\s*[\/]?>/gi, '').replace(/<b>/g, '[b]').replace(/<\/b>/g, '[/b]');
	if (message.match(/<noindex>(.+?)<\/noindex>/g)) {
		newmsg = newmsg.replace(/<noindex>(.+?)<\/noindex>/g, '$1').replace(/<a.*?>(.+?)<\/a>/g, '$1');
	}
	return newmsg.replace(/<a[^>]+?href\s*?=\s*?"([^\"]+)".*?<\/a>/g, '$1');
}

function replaceTextMessageHtml(message) {
	var newmsg = message.replace(/\n|\n\r/gi, '').replace(/<b>/g, '[b]').replace(/<\/b>/g, '[/b]');
	if (message.match(/<noindex>(.+?)<\/noindex>/g)) {
		newmsg = newmsg.replace(/<noindex>(.+?)<\/noindex>/g, '$1').replace(/<a.*?>(.+?)<\/a>/g, '$1');
	}
	return newmsg.replace(/<a[^>]+?href\s*?=\s*?"([^\"]+)".*?<\/a>/g, '$1');
}

var linkOnceClicked = false;
$(document).on("click", "a.js-link-once", function () {
	if(!linkOnceClicked){
		linkOnceClicked = true;
		return true;
	}
	return false;
});

// Проверять вводимый текст на наличие стоп-слов (#4444)
var StopwordsModule = {

	_config: {
		defaultSelector: '.js-stopwords-check',
		defaultWarningsContainerClass: 'js-stopwords-warning-container'
	},

	_components: {
		commission: {
			pattern: /(?:^|[^а-яa-z])(комисс?[а-я]{2,3}|fees?)(?![а-яa-z])|(?:^|[^\d])20\s?%/gi,
			message: t('Не следует обсуждать в переписке с покупателем комиссию сервиса. Комиссия Kwork обеспечивает развитие проекта и безопасность сделок. Каждая успешная сделка в системе увеличивает ваши продажи.')
		},
		contacts: {
			pattern: /(?:^|[^а-яa-z@])(whatsapp|whatsap|вотсап[а-я]{0,2}|телеграм[а-я]{0,2}|теллеграм[а-я]{0,2}|телеграмм[а-я]{0,2}|telegram|telegramm|viber|вайбер[а-я]{0,2}|вибер[а-я]{0,2}|скайп[а-я]{0,2}|skype|vk\.com)(?![а-яa-z])|(?:^|[^\d])\+?[\d]{11}(?!\d)|@[\w]+\.[\w]{2,4}(?![а-яa-z])|(?=.*?\d{3}( |-|.)?\d{4})((?:\+?(?:1)(?:\1|\s*?))?(?:(?:\d{3}\s*?)|(?:\((?:\d{3})\)\s*?))\1?(?:\d{3})\1?(?:\d{4})(?:\s*?(?:#|(?:ext\.?))(?:\d{1,5}))?)\b/gi,
			message: t("Общение и заказы за пределами Kwork несут высокий риск мошенничества! Передача контактных данных запрещена <a href='https://kwork.ru/terms_of_service#2.4.10' target='_blank'>правилами сайта</a>."),
            warningMessage: t("Не сообщайте контакты без крайней необходимости. Общение и заказы за пределами Kwork несут высокий риск мошенничества. А внутри Kwork - проходят гладко и безопасно.")
		}
	},

	_exclude: /^(почти|комиссар|комисар)$/i,

	_exists: function (component, text) {
		var matches = text.match(component.pattern);
		if (matches) {
			matches = matches.filter(function(n){return !StopwordsModule._exclude.test(n.trim())});
		}
		return (matches) ? (matches.length > 0) : false;
	},

	_getWarningContainer: function (element) {
		var warningContainer = $('<div/>', {class: this._config.defaultWarningsContainerClass});
		var form = element.closest('form');
		if (form.length !== 0) {
			var submitButton = form.find('input[type=submit]');
			if (submitButton.length !== 0) {
				warningContainer.insertBefore(submitButton);
			} else {
				var errorContainer = form.find('.js-stopwords-error-wrapper');
				if (errorContainer.length > 0) {
					errorContainer.append(warningContainer);
				} else {
					form.append(warningContainer);
				}
			}
		} else {
			warningContainer.insertAfter(element);
		}
		return warningContainer;
	},

	_getWarningBlock: function (component, onlyWarning) {
		return $('<div/>', {class: 'js-stopwords-warning'}).append(
			$('<div/>', {class: 'position-r mt10'}).append(
				$('<div/>', {class: 'position-a'}).html('<i class="icon ico-warningSmall"></i>'),
				$('<div/>', {class: 'dib v-align-t fs12 lh15 color-red pl30'}).html(onlyWarning ? (component.warningMessage ? component.warningMessage : component.message) : component.message)
			)
		);
	},

	_toggleWarnings: function (warningContainer, found, onlyWarning) {
		warningContainer.empty();
		for (var i = 0; i < found.length; i++) {
			warningContainer.append(this._getWarningBlock(found[i], onlyWarning));
		}
	},

	_test: function (text) {
		var found = [];
		for (var c in this._components) {
			if (this._components.hasOwnProperty(c)) {
				var component = this._components[c];
				if (this._exists(component, text)) {
					found.push(component);
				}
			}
		}
		return found;
	},

	_testContacts: function (text) {
		var found = [];

		//временное отключение проверки на стоп-слова
		return found;

		for (var c in this._components) {
			if (this._components.hasOwnProperty(c)) {
				if (c != "contacts") {
					continue;
				}

				var component = this._components[c];
				if (this._exists(component, text)) {
					found.push(component);
				}
			}
		}
		return found;
	},

	init: function(selector) {
		selector = (typeof selector !== 'undefined') ? selector : this._config.defaultSelector;

		$(document).on('input', selector, function() {
			var element = $(this),
				text = (element.hasClass('trumbowyg-editor')) ? element.text() : element.val(),
				found = StopwordsModule._test(text),
			    warningContainer = (this.dataset.stopwordsWarningContainer) ? $(this.dataset.stopwordsWarningContainer) : $('.' + StopwordsModule._config.defaultWarningsContainerClass),
    			onlyWarning = $(this).hasClass("js-stopwords-check-warning");

			if (warningContainer.length === 0) {
				warningContainer = StopwordsModule._getWarningContainer(element);
			}

			StopwordsModule._toggleWarnings(warningContainer, found, onlyWarning);
		});
	}
};

StopwordsModule.init();

/**
 * Инициализация функционала копирования ссылки на якорь
 */
function anchorInit() {
	var hash = window.location.hash;
	$('.anchor-init').each(function () {
		var anchor = $(this);
		anchor.append("<div class='js-tooltip anchor-init-obj  fa-anchor' onclick='copyAnchor(this)'  data-tooltip-text='" + t('Скопировать') + "'></div>");
		if (hash == ("#" + anchor.attr("id"))) {
			anchor.parents('.faq_page-block_item').addClass('active');
			anchor.parents('.faq_sub-cat').addClass('active');
		}
	});
	var item = $(document.getElementById(hash.replace("#", "")));
	if (item.length) {
		setTimeout(function () {
			if ($('.faq_page-block').length) {
				$(window).scrollTop(item.position().top + $('.header').height() + 20);
			} else {
				$(window).scrollTop(item.position().top - $('.header').height() - 10);
			}
		}, 50);
	}
}


/**
 * Копирует из элемента idи создает ссылку якорь на этот элемент
 * @param elm
 */
function copyAnchor(elm) {
	var obj = $(elm);
	var url = window.location.protocol + "//" + window.location.host + window.location.pathname;
	var hash = obj.parent().attr('id');
	copyTextToClipboard(url + "#" + hash);
	obj.tooltip('update', t('Скопировано'));
	obj.tooltip('show');
	event.stopPropagation();
}

/**
 * Скопировать текст в буффер обмена
 * @param text
 */
function copyTextToClipboard(text, successCallback, errorCallback) {
	var textArea = document.createElement("textarea");
	textArea.style.position = 'fixed';
	textArea.style.top = 0;
	textArea.style.left = 0;
	textArea.style.width = '2em';
	textArea.style.height = '2em';
	textArea.style.padding = 0;
	textArea.style.border = 'none';
	textArea.style.outline = 'none';
	textArea.style.boxShadow = 'none';
	textArea.style.background = 'transparent';
	textArea.value = text;
	document.body.appendChild(textArea);
	textArea.select();
	try {
		var successful = document.execCommand('copy');

		if (successCallback) {
			successCallback();
		}
	}
	catch (err) {
		console.log('Oops, unable to copy');

		if (errorCallback) {
			errorCallback();
		}
	}
	document.body.removeChild(textArea);
}

function isMobile() {
	return (window.innerWidth < 768);
}

function setMobileClass() {
	if (!isMobile()) {
		if (document.body.classList.contains('is_mobile')) {
			triggerScreenTypeChanged(false);
		}
		document.body.classList.remove('is_mobile');
	} else {
		if (!document.body.classList.contains('is_mobile')) {
			triggerScreenTypeChanged(true);
		}

		document.body.classList.add('is_mobile');
	}
}

function triggerScreenTypeChanged(isMobile) {
	$('body').trigger("screenTypeChanged", [isMobile]);
}

$(window).on('resize', function(){
    setMobileClass();
});

$(document).ready(function(){
    setMobileClass();
});

function isNotMobile() {
	return !isMobile();
}

// Обновление описания промо для кворка
jQuery(function() {
	initPromoCounter();
	$('body').on('click', '[name="update_promo"]', function() {
		var $el = $(this);
		var $form = $el.closest('.kwork_promo');
		var kworkId = $form.attr('data-kwork-id');
		var promoText = $form.find('[name="promo_text"]').val();

		updatePromo(kworkId, promoText);
	});
	$('.kwork_promo_wrapper [name="promo_text"]').keyup(function() {
		var $parent = $(this).closest('.kwork_promo_wrapper');
		var characterCount = $(this).val().length;
		var current = $parent.find('.promo_counter_current');

		current.text(characterCount);
	});
});
function initPromoCounter() {
	var $allPromo = $('.kwork_promo_wrapper .promo_text');
	$allPromo.each(function() {
		var $el = $(this);
		var textLength = $el.find('[name="promo_text"]').val().length;
		var $counter = $el.find('.promo_counter_current');
		$counter.text(textLength);
	});
};
function kworkPromoEdit(kworkId) {
	var $el = $('.kwork_promo[data-kwork-id="' + kworkId + '"]');
	var $promoEditBlock = $el.find('.kwork_promo_edit');
	var $promoAddBlock = $el.find('.kwork_promo_add');

	$promoEditBlock.hide();
	$promoAddBlock.fadeIn();
};
function kworkPromoSuccess(kworkId, bonusText) {
	var $el = $('.kwork_promo[data-kwork-id="' + kworkId + '"]');
	var $promoEditBlock = $el.find('.kwork_promo_edit');
	var $promoAddBlock = $el.find('.kwork_promo_add');
	var $promoInModeration = $promoEditBlock.find('.kwork_promo_in_moderation');

	$promoAddBlock.hide();
	$promoEditBlock.find('.kwork_promo_content_edit').text(removePlusAtBeginingString(bonusText));
	$promoEditBlock.fadeIn();
	$promoInModeration.show();
};
function updatePromo(kworkId, bonusText) {
	if (!kworkId) return;

	var $el = $('.kwork_promo[data-kwork-id="' + kworkId + '"]');
	var $btn = $el.find('[name="update_promo"]');

	function startLoader() {
		var loader = '<div class="promo_loader"><span class="preloader__ico"></span></div>';
		$btn.replaceWith(loader);
	};
	function stopLoader() {
		$el.find('.promo_loader').replaceWith($btn);
	};

	startLoader();
	$.ajax({
		type: "POST",
		url: '/manage_kworks',
		dataType: 'json',
		data: {
			'action': 'add_bonus',
			'kworkId': kworkId,
			'bonusText': bonusText
		},
		success: function(data) {
			if (data.response) {
				if (bonusText) kworkPromoSuccess(kworkId, bonusText);
			} else {
				console.log('error');
			}
			stopLoader();
		}
	});
};

function removePlusAtBeginingString(text) {
	var textClean = $.trim(text);
	return $.trim(textClean.replace(/^(\+)/,""));
}

/**
 * Окно с предупреждением о времени хранения файлов
 */
jQuery(function ($) {
    var popupText = t('Срок хранения файлов на Kwork ограничен 6 месяцами!');

    $(document).on('click', '.js-popup-file', function () {
        if (window.fileRetentionPeriodNoticeCount < 0) {
            return;
        }

        var popupHref = $(this).attr('href'),
            html = '<p class="mt7 mb20">' + popupText + '</p>' +
                '<div class="field mb20">' +
                    '<label class="checkbox checkbox-nomore noselect">' +
                        '<input class="checkbox__input" name="checkbox[]" id="popup-file-checkbox" type="checkbox">' +
                        '<span class="checkbox__label">' + t('Больше не показывать') + '</span>' +
                    '</label>' +
                '</div>' +
                '<div class="popup-footer t-align-c">' +
                    '<a href="javascript;;" class="popup__button green-btn mt0 w20p-desktop" id="popup-file-close" onclick="window.open(\'' + popupHref + '\'); return false;">' +
                        '<span class="dib v-align-m pt5">ОК</span>' +
                    '</a>' +
                '</div>';

        show_popup(html, '', true, 'popup-about-file');

        $('#popup-file-close').click(function () {
            var url = '/increase_retention_period_notice_count';
            if ($('#popup-file-checkbox').prop('checked')) {
                url += '?stop=true';
            }

            $.getJSON(url, function (response) {
                if (response.success) {
                    window.fileRetentionPeriodNoticeCount = response.data.count;
                }
            });

			$('body').css('overflow', '');
			$(this).parents('.popup').remove();
        });

        return false;
    });
});

var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9+/=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/rn/g,"n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}


/**
 * #5118 Статусы продавца «Занят» и «Принимаю заказы»
 */
$(function () {
	var titles = {
		free: t('Принимаю заказы'),
		busy: t('Занят')
	};

	var $status  = $('.js-worker-status');
	var $help    = $('.js-worker-status-help');
	var $tooltip = $('.js-tooltip-status');

	var statusIsSending = false;

	/**
	 * Переключение статуса
	 */
	$(document).on('click', '.js-worker-status-switch-container', function () {
		var $switch = null;
		var el = $(this);
		if (el.hasClass('js-worker-status-switch')) {
			$switch = el;
		} else {
			$switch = el.find('.switch__track');
		}
		var status = $switch.data('status');
		var newStatus = status === 'free' ? 'busy' : 'free';

		if (el.hasClass('tooltipster') && newStatus == 'free' && window.touchIsUsed && !window.forceClick) {
			return;
		}
		window.forceClick = false;

		var switchAll = $switch.data('switch-all');
		var hasOffers = $switch.data('has-offers');
		var checkOffers = $switch.data('check-offers');
		var that = this;

		window.switchWorkerStatus = function (button) {
			if (statusIsSending) {
				return false;
			}
			statusIsSending = true;

			var switchEl = $(that).closest('.js-switch').find('.switch__input');
			switchEl.prop('checked', !switchEl.prop('checked'));

			var params = { status: newStatus };

			if (typeof switchAll !== 'undefined') {
				params.switch_all = switchAll;
			}

			// Кнопка может находится в попапе, тогда после выполнения запроса
			// попап нужно будет закрыть
			var $popupClose = $switch.closest('.popup').find('.popup-close-js').eq(0);
			if (button) {
				$popupClose = $popupClose.add($(button).closest('.popup').find('.popup-close-js').eq(0));
			}
			
			// Действие которое должно произойти, после обработки ajax запроса user/switchworkerstatus
			var finishAction = function() {
				if (location.pathname === '/manage_kworks') {
					location.assign(location.pathname);
				}
			};

			Api.request("user/switchworkerstatus", params, function (data) {
				$popupClose.click();
				if (data.error) {
					show_popup(data.error, '', false, 'popup_error');
					$('.popup_error').on('popup.remove', function() {
						finishAction();
					});
				} else {
					if (data.status === 'none') {
						$status.remove();
						$help.remove();
						$tooltip.remove();
						$('.logoutheader .userdata').removeClass('has-status');
						alert(data.message);
					} else {
						$status.removeClass('worker-status-' + status)
							.addClass('worker-status-' + data.status)
							.text(titles[data.status])
							.data('status', data.status);

						var tooltipActive = $('.js-tooltip-' + data.status);
							$tooltip.addClass('hidden');
							tooltipActive.removeClass('hidden');
					}
					finishAction();
				}
				statusIsSending = false;
			});
		};

		if (newStatus === 'busy') {
			var html =
				'<div>' +
					'<h1 class="popup__title">' + t('Подтверждение остановки') + '</h1>' +
					'<hr class="gray" style="margin-bottom: 32px;">'+
					'<div style="display: inline-block; width: 100%;">' +
						'<p class="f15 pb50 ml10">' + t('Внимание! Ваши ранее отправленные предложения по проектам будут удалены. Подтвердите остановку кворков.') + '</p>' +
						'<button class="hoverMe popup__button RedBtnStyle w160 mt20 pull-left f16" style="height: 40px;" onclick="switchWorkerStatus(this);">' + t('Остановить') + '</button>' +
						'<button class="hoverMe white-btn w160 mt20 pull-right popup-close-js f16" style="height: 40px;">' + t('Отменить') + '</button>' +
					'</div>' +
				'</div>';

			if (hasOffers) {
				show_popup(html);
			} else if (checkOffers) {
				Api.request("user/checkworkeroffers", {}, function (data) {
					if (data.has_offers) {
						show_popup(html);
					} else {
						switchWorkerStatus();
					}
				});
			} else {
				switchWorkerStatus();
			}
		} else {
			switchWorkerStatus();
		}
	});

	/**
	 * Показ попапа с подсказкой о статусах
	 */
	$help.click(function () {
		Api.request("user/getworkerstatushelp", {}, function (data) {
			if (data.html) {
				var html = data.html +
					'<div class="popup-footer t-align-c">' +
						'<a class="popup__button green-btn mt0 w20p-desktop popup-close-js"><span class="dib v-align-m pt5">ОК</span></a>' +
					'</div>';
				show_popup(html, '', true, 'popup-help');
			}
		});
	});

	$(document).on('click', '.js-worker-status-switch-type-all', function () { saveSwitchAll('1'); });
	$(document).on('click', '.js-worker-status-switch-type-only', function () { saveSwitchAll('0'); });

	/**
	 * Сохранение настройки активации
	 */
	function saveSwitchAll(value) {
		Api.request("user/setworkerstatusswitchall", { value: value }, function (data) {
			if (data.error) {
				alert(data.error);
			}
		});
	}
});

// TOOLTIPSTER

var TOOLTIP_CIRCLE = ".tooltip_circle, .btn-title_right";
var TOOLTIP_CIRCLE_HOVER = "tooltip_circle_hover";
var TOOLTIP_ICON = ".icon-custom-help";
var TOOLTIP_ICON_HOVER = "icon-custom-help_hover";

/**
 * Конфиг для подсказок плагина - tooltipster
 * http://iamceege.github.io/tooltipster/#options
 */
var TOOLTIP_CONFIG = {
	animationDuration: 200,
	distance: 4,
	contentAsHTML: true,
	interactive: true,
	contentCloning: true,
	side: 'top',
	theme: 'tooltipster-light',
	minIntersection: 14,
	zIndex: 9999,
	delay: [50, 90],
	trigger: 'custom',
	triggerOpen: {
		mouseenter: true,
		touchstart: true
	},
	triggerClose: {
		mouseleave: true,
		scroll: true,
		tap: true
	},
	functionInit: function(instance, helper) {
		var $origin = $(helper.origin),
			_data = {},
			_maxWidth = instance.option('maxWidth'); // default null

		if ($origin.attr() && $origin.attr()['data'] && $origin.attr()['data']['tooltip']) {
			_data = $origin.attr()['data'] && $origin.attr()['data']['tooltip'];
		}

		// Задаем опции через атрибуты [data-tooltip-*]
		// Кастомные опции
		if (_data) {
			$.each(_data, function(option, value) {
				switch (option) {
					case 'text':
						if (!instance.content() && value)
							instance.content(value);
						break;
					case 'theme':
						if (value == 'dark') {
							instance.option('theme', 'tooltipster-borderless');
							instance.option('minIntersection', 12);
						} else if (value == 'dark-minimal') {
							instance.option('theme', 'tooltipster-borderless-minimal');
							instance.option('minIntersection', 8);
						}
						break;
					case 'width':
						_maxWidth = value;
						break;
					case 'destroy':
						if (value == true) {
							instance.option('functionAfter', function(instance, helper) {
								tooltipsterFunctionAfter(instance, helper);
								instance.destroy();
							});
						}
						break;
					case 'child':
						if (value == true) {
							instance.option('functionBefore', function(instance, helper) {
								tooltipsterFunctionBefore(instance, helper);
							});
						}
						break;
					case 'uncheckonmobile':
						if (value == true) {
							instance.option('functionBefore', function(instance, helper) {
								if (window.touchIsUsed) {
									$(instance.content()[0]).find('input').prop('checked', false);
								}
							});
						}
						break;
					case 'target':
						instance.option('functionPosition', function(instance, helper, position) {
							var origin = $(helper.origin);
							var originOffset = origin.offset();
							var child = origin.find(value);
							if (child.length) {
								var childOffset = child.offset();
								position.coord.top += childOffset.top - originOffset.top;
								position.target = childOffset.left + child.outerWidth() / 2;
							}
							return position;
						});
						break;
					case 'mhidden': {
						instance.option('functionBefore', function(instance, helper) {
							if ($(window).width() < 768) {
								return false;
							}
						});
					}
					default:
						instance.option(option, value);
				}
			});
		}

		// Выставляем максимальную ширину
		var _content = instance.content();

		if (_content && (_maxWidth === undefined || _maxWidth === null)) {
			var _length = '';

			if (typeof _content == 'object') {
				_content = jQuery(_content[0]).html();

			}
			_content += '';
			_length = _content.replace(/<[^>]+>/g,'').length;

			if (_length > 40 && _length < 100) _maxWidth = 220;
			if (_length > 100 && _length < 200) _maxWidth = 280;
			if (_length > 200) _maxWidth = 400;
		}
		if (_maxWidth) {
			instance.option('maxWidth', _maxWidth);
		}
	},
	functionBefore: function(instance, helper) {
		$.each($.tooltipster.instances(), function(i, instance) {
			instance.close();
		});
		tooltipsterFunctionBefore(instance, helper);
	},
	functionAfter: function(instance, helper) {
		tooltipsterFunctionAfter(instance, helper);
	}
};
// Подсветить (?)
function tooltipsterFunctionBefore(instance, helper) {
	var $origin = $(helper.origin);
	if ($origin.is(TOOLTIP_CIRCLE)) $origin.addClass(TOOLTIP_CIRCLE_HOVER);
	else if ($origin.is(TOOLTIP_ICON)) $origin.addClass(TOOLTIP_ICON_HOVER);

}
// Убрать подсветку (?)
function tooltipsterFunctionAfter(instance, helper) {
	var $origin = $(helper.origin);
	if ($origin.is(TOOLTIP_CIRCLE)) $origin.removeClass(TOOLTIP_CIRCLE_HOVER);
	else if ($origin.is(TOOLTIP_ICON)) $origin.removeClass(TOOLTIP_ICON_HOVER);
}


jQuery(function() {
	if (jQuery.fn.tooltipster) {
		jQuery('.tooltipster').tooltipster(TOOLTIP_CONFIG);
		// Навешиваем подсказку на все новые элементы
		jQuery('body').on('mouseenter', '.tooltipster:not(.tooltipstered)', function () {
			jQuery(this)
				.tooltipster(TOOLTIP_CONFIG)
				.tooltipster('open');
		});
	}
});

// TOOLTIPSTER END


/**
 * #5268 Поле ввода объема только цифры, разделение на порядки
 */
$(function () {
	$(document).on('keydown', '.js-only-integer', function (e) {
		// Allow: backspace, delete, tab, escape, enter and .
		if ($.inArray(e.keyCode, [46, 8, 9, 27, 13]) !== -1 ||
			// Allow: Ctrl/cmd+A
			(e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
			// Allow: Ctrl/cmd+C
			(e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
			// Allow: Ctrl/cmd+X
			(e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
			// Allow: home, end, left, right
			(e.keyCode >= 35 && e.keyCode <= 39)) {
			// let it happen, don't do anything
			return;
		}
		// Ensure that it is a number and stop the keypress
		if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
			e.preventDefault();
		}
	});

	$(document).on('keyup', '.js-only-integer', function (e) {
		var $input = $(this);
		var num = $input.val().replace(/(\s)/g, '');

		// Если число начинается с 0, то 0 удаляется
		if (num && $input.data('type') === 'integer') {
			num = parseInt(num, 10).toString();
		}

		if (num !== '') {
			var max = $input.data('maxValue');
			if (max !== undefined && num > max) {
				num = max.toString();
			}
		}

		$input.val(num.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 "));
	});
});

// Костыль для Ильи,
// при тапе не показывать подрубрики у рабрик на главной странице
jQuery(document).on('touchstart', '.top-category', function() {
	var $currentCategory = jQuery(this);
	$currentCategory.find('.top-category-content').hide();
});

// Скрытие кворка

/**
 * Скрыть/вернуть из скрытых кворков
 * @param {int} id ID Кворка/пользователя
 * @param {string} type Выполняемое действие:
 *	add - добавить в скрытые;
 *	del - вернуть из скрытых;
 * @param {string} action Тип скрытия:
 *	kwork - кворк;
 *	user - пользователь;
 */
function customHide(id, type, action) {
	if (
		id == null || id == undefined ||
		action == null || action == undefined ||
		type == null || type == undefined
	) {
		return false;
	}

	var url = '/hidden/hide?id=' + id + '&type=' + type + '&action=' + action;
	jQuery.post(url, function() {
		// done
	});

	return false;
}

/**
 * Подгружает карточку кворка в конец списка при
 * скрытии кворка
 * @param blockType mixded - id  блока-родителя, нужен для разделения
 * по категориям(другие кворки продавца[1], категории[2], популярные[3], поиск[4], избранные[5], похожие кворки[6])
 */
function loadOnceKworkByType(blockType) {
	switch (blockType){
		case 1:
			loadOnceOtherKworkSeller();
			break;
		case 2:
			loadOnceKwork();
			break;
		case 3:
			loadOnceKworkPopular();
			break;
		case 4:
			loadOnceKwork();
			break;
		case 5:
			loadOnceKworkBookmarks();
			break;
		case 6:
			loadOnceKworkSimilar();
			break;
		case 7:
			loadOnceKworkLastBuyed();
			break;
		default:
			break;
	}

}

/**
 * Подгрузка одного кворка
 * вместо скрытого на странице
 * каталога и странице поиска
 * или подгрузка нескольких
 * вместо купленных на странице ссылок
 */
function loadOnceKwork(kworksCount, callback){
	var url = window.location.href;
	if (typeof CatFilterModule == 'undefined') {
		var params = getGetParams();
	} else {
		var params = CatFilterModule.getParams();
	}
	params['page'] = parseInt(params['page']);
	if (typeof weightMinute == 'undefined') {
		weightMinute = '';
	}
	// @TODO: здесь и в похожих местах нужно брать id всех карточек в слайдере
	var shownIds = $('.js-kwork-card').map(function() { return $(this).data('id')}).get();
	var item_per_page = $('.kwork-card-data-wrap').data('kworks-per-page');
	if(item_per_page) {
		params['page'] = Math.round(shownIds.length / item_per_page);
	}
	var data = {'page':params['page'], 'weightMinute': weightMinute, 'excludeIds': shownIds.join(",")};
	if(kworksCount) {
		data.kworksCount = kworksCount;
	} else {
		data.isHidden = 1;
	}
	$.ajax({
		url:url,
		type:'post',
		data: data,
		dataType:'json',
		success:function(response) {
			if(sdisplay == "list") {
				$(".newfox").last().after(response.html);
			} else {
				$(".cusongsblock").last().after(response.html);
			}
			$('.js-tooltip').tooltip();
		},
		complete: function(response) {
			if(typeof callback == 'function') {
				callback();
			}
		}
	});
}

/**
 * Подгрузка одного кворка
 * вместо скрытого на странице
 * кворка в "Сейчас заказывают"
 */
function loadOnceKworkLastBuyed() {
	var excludeKworkIds = [];
	//сюда надо записать id со страницы
	$('.ordered-kworks .js-kwork-card').each(function() {
		var id = $(this).data('id');
		if(id) {
			excludeKworkIds.push(id);
		}
	});
	var catId = $('.ordered-kworks').data('catid');
	$.ajax({
		url: '/category/last_buyed_kworks',
		type:'post',
		data:{'excludeIds': excludeKworkIds.join(','), 'categoryId': catId},
		success:function(response)
		{
			$('.ordered-kworks').append(response);
		}
	});
}

/**
 * Подгрузка одного кворка
 * вместо скрытого на странице
 * кворка в слайдере "похожие кворки"
 */
function loadOnceKworkSimilar() {
	var excludeKworkIds = [];
	//сюда надо записать id со страницы
	$('.similar-kwork-wrap .kwork-small-carousel .js-kwork-card, .other-seller-kwork-wrap .kwork-small-carousel .js-kwork-card').each(function(){
		var id = $(this).data('id');
		if(id) {
			excludeKworkIds.push(id);
		}
	});
	$.ajax({
		url:'/api/kwork/getoncesimilarkwork',
		type:'post',
		data:{'excludeIds': excludeKworkIds, 'postId': post_id},
		dataType:'json',
		success:function(response)
		{
			$('.kwork-small-carousel .kwork-carousel-wrapper .kwork-carousel-item:empty').last().append(response.html);
		}
	});
}
/**
 * Подгрузка одного кворка
 * вместо скрытого на странице
 * кворка в слайдере "другие кворки продавца"
 */
function loadOnceOtherKworkSeller() {
	var excludeKworkIds = [];
	$('.other-seller-kwork-wrap .kwork-small-carousel .js-kwork-card').each(function(){
		var id = $(this).data('id');
		if(id) {
			excludeKworkIds.push(id);
		}
	});
	$.ajax({
		url:'/api/kwork/getonceotherkworkseller',
		type:'post',
		data:{'excludeIds': excludeKworkIds, 'postId': post_id},
		dataType:'json',
		success:function(response)
		{
			$('.kwork-small-carousel .kwork-carousel-wrapper .kwork-carousel-item:empty').last().append(response.html);
		}
	});
}

/**
 * Подгрузка одного кворка
 * вместо скрытого на главной
 * странице в слайдере "популярные кворки"
 */
function loadOnceKworkPopular(){
	var excludeKworkIds = [];
	$('.js-kwork-carousel-container-popular .js-kwork-card').each(function(){
		var id = $(this).data('id');
		if(id) {
			excludeKworkIds.push(id);
		}
	});
	$.ajax({
		url:'/api/kwork/getoncekworkpopular',
		type:'post',
		data:{'excludeIds': excludeKworkIds},
		dataType:'json',
		success:function(response)
		{
			$('.js-kwork-carousel-container-popular .kwork-carousel-wrapper .kwork-carousel-item:empty').last().append(response.html);
		}
	});
}

/**
 * Подгрузка одного кворка
 * вместо скрытого на главной
 * странице в слайдере "избранные кворки"
 */
function loadOnceKworkBookmarks() {
	var excludeKworkIds = [];
	excludeKworkIds = window.carousel['favorites'];

	if (!excludeKworkIds) return;

	$.ajax({
		url: '/api/kwork/getoncekworkbookmark',
		type: 'post',
		data: {'excludeIds': excludeKworkIds},
		dataType: 'json',
		success: function(response) {
			$('.js-kwork-carousel-container-favorites .js-kwork-card').last().after(response.html);
		}
	});
}

jQuery(function () {
	// Скрытие кворка в карточке кворка
	jQuery('body').on('click', '.js-kwork-hidden, .js-kwork-hidden-card', function() {
		var $currentElement = jQuery(this);
		var kworkId = $currentElement.data('id');
		var action = $currentElement.data('action');
		var blockType = $('.js-kwork-card').find('[data-id="' + kworkId + '"]').closest('.kwork-card-data-wrap').data('kwork-load-category');

		jQuery('.js-kwork-control[data-id="' + kworkId + '"] .icon-eye-slash').toggleClass('hidden');
		jQuery('.js-trig-status').toggleClass('hidden');
		jQuery('.js-fav-count').toggleClass('hidden');

		customHide(kworkId, 'kwork', action);

		if ($currentElement.hasClass('js-kwork-hidden-card')) {
			var $card = $('.js-heart-block[data-id="' + kworkId + '"]');

			function toggleHidden(kworkId) {
				$card.find('.tooltipster .kwork-icon').toggleClass('hidden');
			}

			// Скрыть кворк
			if (action == 'add') {
				disableHeartActive();

				// Страница - Скрытые кворки и продавцы
				if (window.isHiddenPage) {
					toggleHidden(kworkId);
				} else {
					jQuery('.cusongsblock[data-id="' + kworkId + '"], .newfox.js-kwork-card[data-id="' + kworkId + '"]').each(function() {
						var $card = jQuery(this);
						var isCarousel = ($card.closest('.kwork-carousel, .kwork-small-carousel').length > 0) ? true : false;

						// Если карточка в слайдере анимация скрытия будет отличаться
						if (isCarousel) {
							animateHiddenCardForCarousel(kworkId);
						} else {
							animateHiddenCard(kworkId, function() {
								loadOnceKworkByType(arguments[0]);
							}, blockType);
						}
					});
				}
			}
			// Убрать из скрытых
			if (action == 'del') {
				// Страница - Скрытые кворки и продавцы
				if (window.isHiddenPage) {
					toggleHidden(kworkId);
				}
			}
		} else {
			// Страница просмотра кворка
			if (action == 'add') {
				var $bookmark = $('.js-bookmark').filter('[data-pid="' + kworkId + '"]');
				var $favCount = $('.js-fav-count');

				// Убрать из избранных, если добавили в скрытые
				if ($bookmark.hasClass('active')) {
					$bookmark.removeClass('active');
					var newCnt = ($favCount.html() * 1) - 1;
					$favCount.html(newCnt);

					disableHeartActive();
				}
			}
		}

		function disableHeartActive() {
			var $kworkControls = jQuery('.kwork-controls[data-id="' + kworkId + '"]');
			$kworkControls.find('.js-icon-heart.active, .js-icon-heart-card.active').addClass('hidden');
			$kworkControls.find('.js-icon-heart:not(.active), .js-icon-heart-card:not(.active)').removeClass('hidden');
			$('.js-heart-block[data-id="' + kworkId + '"]').removeClass('active');
		}
	});

	// Добавить в избранные
	jQuery('body').on('click', '.js-icon-heart-card', function () {
		var $currentElement = jQuery(this);
		var kworkId;

		if (jQuery(window).width() < 768) {
			jQuery('.tooltipster-box .tooltipstered').tooltipster('disable');
		}

		if ($currentElement.hasClass('icon-heart-card')) {
			if (isMobile()) return;
			kworkId = $currentElement.closest('.cusongsblock, .newfox').data('id');
		} else {
			kworkId = $currentElement.closest('.kwork-controls').data('id');
		}
		var $bookmark = $('.js-bookmark[data-pid="' + kworkId + '"], .js-heart-block[data-id="' + kworkId + '"]');

		$('.js-kwork-control[data-id="' + kworkId + '"] .js-icon-heart-card').toggleClass('hidden');
		if ($bookmark.hasClass('active')) {
			bookmark(kworkId, false);
		} else {
			bookmark(kworkId, true);
		}
		return false;
	});
});

/**
 * Анимированное скрытие карточки кворка
 * @param {int} kworkId ID Кворка
 * @param callback вызов callback после анимации
 * @param callbackPrams параметры callback
 * @returns {boolean}
 */
function animateHiddenCard(kworkId, callback, callbackPrams) {
	var _duration = 300; // milliseconds
	var $currentCard = jQuery('.js-kwork-card[data-id="' + kworkId + '"]');

	if ($currentCard.lenght <= 0) return false;

	$currentCard.css({
		'transition': 'all ' + (_duration / 1000) + 's ease-in-out',
		'transform': 'scale(0)',
		'opacity': '0.3'
	});

	// tooltip
	jQuery('.tooltipster-base').remove();

	setTimeout(function() {
		$currentCard.remove();
		if (callback)
			callback(callbackPrams);
	}, _duration + 50);
};

/**
 * Анимированное скрытие карточки кворка для слайдера
 * @param {int} kworkId ID Кворка
 */
function animateHiddenCardForCarousel(kworkId) {
	var _duration = 300; // milliseconds
	var $currentCard = jQuery('.js-kwork-card[data-id="' + kworkId + '"]');

	if ($currentCard.lenght <= 0) return false;

	$currentCard.append('<div class="cardDisabled"></div>');

	$currentCard.css({
		'transition': 'all ' + (_duration / 1000) + 's ease-in-out',
		'opacity': '0.3'
	});

	// tooltip
	jQuery('.tooltipster-base').remove();
};

window.bookmarkXhr = {};

/**
 * Убрать спиннер при загрузке изображения
 * @param object e Событие
 */
function removeISpinner(e) {
	var el = $(e.target);
	if (el.length <= 0) {
		return;
	}
	var container = el.closest('.ispinner-container');
	if (container.length <= 0) {
		return;
	}
	var spinner = container.find('.thumbnail-img-load');
	if (spinner.length <= 0) {
		return;
	}
	spinner.remove();
}

/**
 * Добавить/убрать кворк из избранных
 * @param {int} kworkId ID кворка
 * @param {bool} active Действие:
 * 	true - добавить в избранные;
 * 	false - убрать из избранных;
 */
function bookmark(kworkId, active) {
	var url = '/bookmark?id=' + kworkId + '&do=';
	if (active) {
		url += 'add';
	} else {
		url += 'rem';
	}
	if (kworkId in window.bookmarkXhr) {
		window.bookmarkXhr[kworkId].xhr.abort();
	}
	var currentXhr = $.post(url, $(this).serialize(), function (data) {
		$('.js-bookmark[data-pid="' + kworkId + '"], .js-heart-block[data-id="' + kworkId + '"]').toggleClass('active', active);
		delete window.bookmarkXhr[kworkId];
	});
	window.bookmarkXhr[kworkId] = {
		xhr: currentXhr,
		state: active,
	};
};
/**
 * Получение разделителя разрядов
 *
 * @param string
 * @returns {string}
 */
function hasMark(string) {
	if (string.indexOf(",") !== -1) {
		return ",";
	} else if (string.indexOf(".") !== -1) {
		return ".";
	}
}

$(function () {
	/**
	 * Ввод только цифр и разделителей разрядов
	 */
	$('.js-only-numeric').on('keydown', function(e){
		// Allow: backspace, delete, tab, escape, enter and .
		if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 188]) !== -1 ||
			// Allow: Ctrl/cmd+A
			(e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
			// Allow: Ctrl/cmd+C
			(e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
			// Allow: Ctrl/cmd+X
			(e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
			// Allow: home, end, left, right
			(e.keyCode >= 35 && e.keyCode <= 39)) {
			// let it happen, don't do anything
			return;
		}
		// Ensure that it is a number and stop the keypress
		if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
			e.preventDefault();
		}
	}).on('keyup', function(e) {
		var input = $(this);
		var digits = input.val().replace(/(\s)/g, '');
		var mark = hasMark(digits);
		if (mark) {
			var digitGroups = digits.split(mark);
			if (digitGroups.length > 1) {
				digits = parseInt(digitGroups[0]).toString() + mark + digitGroups[1].charAt(0);
			}
		} else {
			digits = parseInt(digits).toString();
		}

		var number = parseFloat(digits.replace(',','.'));
		if (isNaN(number)) {
			digits = "";
		}

		var max = input.attr("data-max");
		if (max > 0 && number > max) {
			digits = max.toString();
		}

		$(this).val(digits.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 "));
	});



	//раскрытие/скрытие подробного текста подсказки продавцам
	$(document).on('click', '.request-not-correspond', function () {
		$(this).find('.request-not-correspond__title-icon').toggleClass('request-not-correspond__title-icon_rotate');
		$(this).children('.request-not-correspond__more-text').slideToggle(150);
	});

	$(document).on('change', '.is_mobile .select-user-type input[name="userType"]', function () {
		var parent = $(this).closest('.dib');
		$('.is_mobile .select-user-type .active').removeClass('active');
		if ($(this).prop('checked')) {
			parent.addClass('active');
		} else {
			parent.removeClass('active');
		}
	});
});

// Используется в пакетных кворках
// вынесено для общего использования (+ в портфолио)
jQuery(function() {
	$('body').on('click', '.js_package_card_vert__toggleInfo', function () {
		if ($(this).parent().find('.package_card_vert_info').css('display') == 'block') {
			return;
		}
		$(this).parent().parent().find('.package_card_vert_info').slideUp('fast');
		$(this).parent().parent().find('.package_card_vert__arrow').show();
		$(this).parent().find('.package_card_vert_info').slideDown('fast');
		$(this).parent().find('.package_card_vert__arrow').hide();
	});
});

jQuery.fn.scrollTo = function(elem, duration) {
	$(this).animate({
		scrollTop: $(this).scrollTop() - $(this).offset().top + $(elem).offset().top
	}, duration || 200);

	return this;
};

/**
 * Округляем стоимость кворка с фиксированным объемом
 * кратно 50 руб. / $1
 *
 * @param basePrice - базовая цена
 * @param volume - введенный объем
 * @param multiplier - исходный объем
 * @param lang - язык сайта
 * @param sitePage - страница сайта, где используется расчет
 * @returns {number}
 */
function kworkCostRound(basePrice, volume, multiplier, lang, sitePage) {
	var priceResult = 0;
	var price = volume * basePrice / multiplier;
	var floorPrice = Math.floor(price);
	var ceilPrice = Math.ceil(price);
	var fraction = price - floorPrice;
	var part = 0;
	var newRemainder = 0;

	if (lang === "ru") {
		var remainder = floorPrice % 100;
		if (fraction > 0) {
			remainder += 1;
		}
		if (remainder < ceilPrice) {
			part = ceilPrice - remainder;
		}
		if (remainder > 0 && remainder <= 50) {
			newRemainder = 50;
		} else if (remainder > 50) {
			newRemainder = 100;
		}
	} else {
		part = floorPrice;
		if (fraction > 0) {
			newRemainder = 1;
		}
	}

	priceResult = part + newRemainder;
	if (sitePage === 'view' && priceResult < basePrice) {
		priceResult = basePrice;
	}

	return priceResult;
}

/**
 * Получить округленное время выполнения для произвольного объёма
 * аналог функции OrderVolumeManager::getVolumedDuration
 *
 * @param days - Кол-во дней для базового объёма
 * @param base - Базовый объём кворка
 * @param volume - Заказанный произвольный объём
 * @param sitePage - страница сайта, где используется расчет
 * @returns {*}
 */
function getVolumedDuration(days, base, volume, sitePage) {
	if (typeof volume === 'string') {
		volume = volume.replace(/[\s]/gim, '');
	}
	var up = volume - base,
		duration;

	if (up > 0) {
		var percent = up * 100 / base;
		var daysUp = days * percent / 100;
		duration = roundOrderTime(days + daysUp);
	} else {
		duration = roundOrderTime(days / base * volume);
	}

	if (sitePage === 'view' && duration < days) {
		duration = days;
	}

	return duration;
}

/**
 * Округление времени заказа
 *
 * @param time
 * @returns {*}
 */
function roundOrderTime(time) {
	var floorValue = Math.floor(time),
		fraction = time - floorValue,
		result;

	if (fraction > 2/24) {
		result = floorValue + 1;
	} else {
		result = floorValue;
	}

	return result;
}

/**
 * Получить объем кворка в выбранных в кворке единицах
 *
 * @param timeSelect
 * @param volume
 * @returns {number}
 */
function getVolumeInKworkType(timeSelect, volume) {
	var baseTime = timeSelect.data('baseTime');
	var additionalTime = timeSelect.find('option:selected').data('additionalTime') || timeSelect.data('additionalTime');

	if (additionalTime > 0) {
		volume = volume * additionalTime / baseTime;
		if (volume === 0) {
			volume = 1;
		}
	}

	return volume;
}

/**
 * Получить максимальный объем кворка в выбранных в кворке единицах
 *
 * @param timeSelect
 * @param volume
 * @returns {number}
 */
function getMaxVolumeInKworkType(timeSelect, volume) {
	var baseTime = timeSelect.data('baseTime');
	var additionalTime = timeSelect.find('option:selected').data('additionalTime') || timeSelect.data('additionalTime');

	if (additionalTime > 0) {
		volume = volume * baseTime / additionalTime;
		if (volume === 0) {
			volume = 1;
		}
	}

	return volume;
}

/**
 * Удаляет количество кворков из get-запроса для рубрик с фиксированным объемом
 *
 * @param form
 * @returns string
 */
function checkKworkFormData(form) {
	var volumeOrder = jQuery('.js-volume-order:first'),
		formData = form.serialize(),
		kworkCountIndex;

	if (volumeOrder.length === 0) {
		return formData;
	} else {
		formData = form.serializeArray();
		kworkCountIndex = formData.map(function(el) {
			return el.name;
		}).indexOf('kworkcnt');
		if (kworkCountIndex !== -1) {
			formData.splice(kworkCountIndex, 1);
			formData = $.param(formData);
			return formData;
		}
	}

	return formData;
}

/**
 * Показывает попап при отсутствии переписки между пользователяем, либо редиректит в чат при наличии оной
 * @param hasConversation
 * @param chatRedirectUrl
 * @param conversationUserId
 * @param conversationMessage
 */
function firstConversationMessage(hasConversation, chatRedirectUrl, conversationUserId, conversationMessage) {
	if (hasConversation) {
		document.location.href = chatRedirectUrl;
		return false;
	}

	var popup = jQuery('#js-popup-individual-message__container');
	var popupFormMessage = popup.find('#message_body');

	popup.find('.js-msgto').val(conversationUserId);
	popup.find('.js-chat-redirect-url').attr('data-url', chatRedirectUrl);
	if (conversationMessage.length) {
		popupFormMessage.val(conversationMessage);
		setTimeout(function () {
			popupFormMessage.trigger('input');
		}, 200);
	}
	individualMessageModule.showMessage();
}

jQuery(function() {
	// Копируем в буфер ссылку на проект
	jQuery('body').on('click', '.wantsClipboard', function() {
		var $this = jQuery(this);
		var $thisLinks = $this.attr('data-clipboard');

		copyTextToClipboard($thisLinks, function() {
			$this
				.tooltipster('open')
				.tooltipster('content', t('Ссылка скопирована'));
		}, function() {
			$this
				.tooltipster('open')
				.tooltipster('content', t('Ваш браузер не поддерживает копирование в буфер обмена'));
		});
	});

	// Регистрация для "Предложить услугу"
	jQuery('body').on('click', '.offer-signup-js', function (e) {
		/* при клике вызывать форму регистрации */
		e.preventDefault();
		$.getJSON('/api/ban/disallowfreeregister', function (registerWithCaptcha) {
			show_signup('', true, registerWithCaptcha, true, '', t('Зарегистрируйтесь, чтобы отправить свое предложение и получить эту работу.'));
		});

	});

	//ввод только цифр
	$(".js-input-number").on("change keyup input click", function () {
		this.value.match(/[^0-9]/g, "") && (this.value = this.value.replace(/[^0-9]/g, ""));
	});
});

function openWindow(title, address) {
	var popupWidth = window.innerHeight / 2 > 700 ? window.innerHeight / 2 : 700;
	var popupHeight = window.innerHeight / 1.5 > 550 ? window.innerHeight / 1.5 : 550;
	var config = "width=" + popupWidth + ",";
	config += "height=" + popupHeight + ",";
	config += "top=" + ((screen.height - popupHeight) / 2) + ",";
	config += "left=" + ((screen.width - popupWidth) / 2) + ",";
	config += "resizable=yes,status=yes,location=no";
	window.open(address, title, config);
}

//Строку в DomElement
function parseHTML(str) {
	let tmp = document.implementation.createHTMLDocument();
	tmp.body.innerHTML = str;
	return tmp.body.children;
}
