(function ($) {
	var KworkCarousel = function (el, userOptions) {
		var obj = this,
			instanceID = Math.floor(Math.random() * 100) + Date.now();

		userOptions = (userOptions === undefined) ? {} : userOptions;
		var defaultOptions = {
			carousel: true,
			items: 4,
			multiple: false,
			slidesContainer: el,
			slides: '.cusongsblock',
			wrapperClass: 'kwork-carousel-wrapper',
			itemClass: 'kwork-carousel-item',
			storageClass: 'kwork-carousel-storage',
			nextNavClass: 'kwork-carousel-next',
			prevNavClass: 'kwork-carousel-prev',
			animateDuration: 80,
			margin: 10,
			storageInMemory: true,
			nav: true,
			responsive: false,
			staticFirst: false
		};

		var options = $.extend(defaultOptions, userOptions);

		obj.currentItem = 0;
		obj.responsiveOptions = -1;
		obj.visibleItems = 0;
		obj.totalItems = 0;
		obj.animation = false;
		obj.container = $(options.slidesContainer);
		obj.nextFix = false;
		obj.prevFix = false;

		obj.init = function () {
			if (options.responsive) {
				obj.setupResponsive();
				$(window).on('resize', function () {
					if (!obj.setupResponsive() || !obj.resize()) {
						return;
					}
					if (options.nav) {
						obj.buildNav();
					}
				});
			}


			if (!obj.buildWrapper()) {
				return;
			}
			if (options.nav) {
				obj.buildNav();
			}
		};

		obj.buildWrapper = function () {
			obj.container.addClass('kwork-carousel-main-container');
			obj.items = obj.container.find(options.slides);
			obj.totalItems = obj.items.length;

			obj.visibleItems = options.items;

			obj.buildStorage();

			var itemIndex = 0;
			obj.items.each(function () {
				$(this).data('kwork-carousel-item-index', itemIndex);
				itemIndex++;
			});

			if (obj.visibleItems >= obj.totalItems || !options.carousel) {
				obj.container.append(obj.items);
				return false;
			}
			if (options.multiple) {
				obj.totalItems = obj.totalItems - (obj.totalItems % obj.visibleItems);
				if (obj.totalItems <= obj.visibleItems) {
					for (var idx = 0; idx < obj.totalItems; idx++) {
						obj.container.append(obj.items[idx]);
					}

					return false;
				}
			}

			obj.addTouchListeners();

			obj.wrapper = $('<div>').addClass(options.wrapperClass).attr('id', 'kwork-carousel-' + instanceID);

			for (var i = 0; i < obj.visibleItems; i++) {
				var itemPlace = $('<div>').addClass(options.itemClass);
				itemPlace.data('kwork-carousel-item-place-index', i);
				if (options.margin) {
					itemPlace.css('margin-right', options.margin);
				}
				obj.wrapper.append(itemPlace);
			}

			obj.wrapper.find('.' + options.itemClass).each(function (i) {
				var itemPlace = $(this);
				obj.items.each(function () {
					if ($(this).data('kwork-carousel-item-index') == i) {
						obj.moveItemToItemplace(itemPlace, $(this), true);
					}
				});
			});


			obj.container.prepend(obj.wrapper);

			return true;
		};

		obj.buildNav = function () {

			var next = $('<a>')
				.attr('href', '#')
				.addClass(options.nextNavClass)
				.addClass('kwork-carousel-nav-button');
			var prev = $('<a>')
				.attr('href', '#')
				.addClass(options.prevNavClass)
				.addClass('kwork-carousel-nav-button');

			next.append(
				$('<span>')
					.addClass('kwork-carousel-nav-button-inner')
					.append(
						$('<i>').addClass('kwork-carousel-nav-button-icon')
					)
			);
			prev.append(
				$('<span>')
					.addClass('kwork-carousel-nav-button-inner')
					.append(
						$('<i>').addClass('kwork-carousel-nav-button-icon')
					)
			);

			next.on('click', obj.next);
			prev.on('click', obj.prev);

			var nav = $('<div>').addClass('kwork-carousel-nav');

			nav.append(prev);
			nav.append(next);
			obj.nav = nav;
			obj.wrapper.parent().addClass('has-nav').append(nav);
		};

		obj.buildStorage = function () {
			obj.storage = $('<div>').addClass(options.storageClass).attr('id', 'kwork-carousel-storage' + instanceID).hide();
			obj.items.removeClass('kwork-carousel-item-active');
			obj.storage.append(obj.items);

			if (!options.storageInMemory) {
				obj.storage.appendTo(obj.container);
			}
		};

		obj.next = function () {
			if (obj.animation) {
				return false;
			}

			obj.nextFix = false;
			obj.animation = true;

			var nextItem = obj.currentItem + obj.visibleItems;
			if (obj.prevFix) {
				nextItem = nextItem - (obj.visibleItems - obj.totalItems % obj.visibleItems);
				obj.prevFix = false;
			}

			if (nextItem >= obj.totalItems) {
				nextItem = nextItem - obj.totalItems;
			} else if (options.staticFirst && nextItem + obj.visibleItems > obj.totalItems && obj.totalItems % obj.visibleItems) {
				nextItem = nextItem - (obj.visibleItems - obj.totalItems % obj.visibleItems);
				obj.nextFix = true;
				if (nextItem < 0) {
					nextItem = nextItem + obj.totalItems;
				}
			}

			// реверс
			nextItem = nextItem + obj.visibleItems - 1;
			if (nextItem >= obj.totalItems) {
				nextItem = nextItem - obj.totalItems;
			}

			obj.list(nextItem, true);

			return false;
		};

		obj.prev = function () {
			if (obj.animation) {
				return false;
			}
			obj.prevFix = false;
			obj.animation = true;

			var nextItem = obj.currentItem - obj.visibleItems;

			if (obj.nextFix) {
				nextItem = nextItem + (obj.visibleItems - obj.totalItems % obj.visibleItems);
				obj.nextFix = false;
			}

			if (nextItem < 0) {
				nextItem = nextItem + obj.totalItems;
				if (options.staticFirst && obj.totalItems % obj.visibleItems) {
					nextItem = nextItem + obj.visibleItems - obj.totalItems % obj.visibleItems;
					obj.prevFix = true;
				}
			}

			obj.list(nextItem, false);

			return false;
		};

		obj.list = function (nextItem, reverse) {

			var delayIndex = 0;
			var itemPlaces = obj.wrapper.find('.' + options.itemClass);

			if (reverse) {
				itemPlaces = $(itemPlaces.get().reverse());
			}

			var animatedCounter = itemPlaces.length;

			itemPlaces.each(function () {
				var itemPlace = $(this);
				var item = null;

				obj.items.each(function () {
					if ($(this).data('kwork-carousel-item-index') == nextItem) {
						item = $(this);
					}
				});

				setTimeout(function () {
					if (item) {
						obj.moveItemToItemplace(itemPlace, item);
					}
					animatedCounter--;
					if (!animatedCounter) {
						obj.animation = false;
						obj.wrapper.trigger('animationEnd');
						obj.currentItem = obj.wrapper.find(options.slides).first().data('kworkCarouselItemIndex');
					}
				}, options.animateDuration * delayIndex);

				delayIndex++;

				if (reverse) {
					nextItem--;
					if (nextItem < 0) {
						nextItem = nextItem + obj.totalItems;
					}

				} else {
					nextItem++;
					if (nextItem >= obj.totalItems) {
						nextItem = nextItem - obj.totalItems;
					}
				}
			});
		};

		obj.moveItemToItemplace = function (itemPlace, item, itemPlaceIsClear) {
			if (!itemPlaceIsClear) {
				itemPlace.children().first().removeClass('kwork-carousel-item-active').appendTo(obj.storage);
			}

			if (item.hasClass('kwork-carousel-item-active')) {
				var itemClone = item
					.clone()
					.removeClass('kwork-carousel-item-active')
					.addClass('kwork-carousel-item-clone');
				itemPlace.append(itemClone);


				obj.wrapper.one('animationEnd', function () {
					itemPlace.children().first().remove();
					item.addClass('kwork-carousel-item-active');
					itemPlace.append(item);
				});

			} else {
				item.addClass('kwork-carousel-item-active');
				itemPlace.append(item);
			}
		};

		obj.setupResponsive = function () {
			var viewport = obj.viewport(),
				overwrites = options.responsive,
				match = -1;

			options = $.extend(defaultOptions, userOptions);

			$.each(overwrites, function (breakpoint) {
				if (breakpoint <= viewport && breakpoint > match) {
					match = Number(breakpoint);
				}
			});

			if (match != obj.responsiveOptions) {
				obj.responsiveOptions = match;
				if (match != -1) {
					options = $.extend({}, options, overwrites[match]);
				}
				return true;
			}

			return false;
		};

		obj.resize = function () {
			obj.totalItems = obj.items.length;
			obj.items.removeClass('kwork-carousel-item-active');
			obj.storage.append(obj.items);

			obj.visibleItems = options.items;

			if (obj.wrapper) {
				obj.wrapper.remove();
			}

			if (obj.nav) {
				obj.wrapper.parent().removeClass('has-nav');
				obj.nav.remove();
			}

			obj.removerTouchListeners();

			if (obj.visibleItems >= obj.totalItems || !options.carousel) {
				obj.container.append(obj.items);
				return false;
			}
			if (options.multiple) {
				obj.totalItems = obj.totalItems - (obj.totalItems % obj.visibleItems);
				if (obj.totalItems <= obj.visibleItems) {
					for (var idx = 0; idx < obj.totalItems; idx++) {
						obj.container.append(obj.items[idx]);
					}

					return false;
				}
			}

			obj.addTouchListeners();

			obj.wrapper = $('<div>').addClass(options.wrapperClass).attr('id', 'kwork-carousel-' + instanceID);

			for (var i = 0; i < obj.visibleItems; i++) {
				var itemPlace = $('<div>').addClass(options.itemClass);
				itemPlace.data('kwork-carousel-item-place-index', i);
				if (options.margin) {
					itemPlace.css('margin-right', options.margin);
				}
				obj.wrapper.append(itemPlace);
			}

			obj.currentItem = 0;

			obj.wrapper.find('.' + options.itemClass).each(function (i) {
				var itemPlace = $(this);
				obj.items.each(function () {
					if ($(this).data('kwork-carousel-item-index') == i) {
						obj.moveItemToItemplace(itemPlace, $(this), true);
					}
				});
				i++;
			});

			obj.container.prepend(obj.wrapper);

			return true;
		};

		obj.getScrollbarWidth = function () {
			var outer = document.createElement("div");
			outer.style.visibility = "hidden";
			outer.style.width = "100px";
			outer.style.msOverflowStyle = "scrollbar";

			document.body.appendChild(outer);

			var widthNoScroll = outer.offsetWidth;
			outer.style.overflow = "scroll";

			var inner = document.createElement("div");
			inner.style.width = "100%";
			outer.appendChild(inner);

			var widthWithScroll = inner.offsetWidth;

			outer.parentNode.removeChild(outer);

			return widthNoScroll - widthWithScroll;
		}

		obj.viewport = function () {
			var width;
			if ($(window).width()) {
				width = $(window).width();
			} else if (window.innerWidth) {
				width = window.innerWidth;
			} else if (document.documentElement && document.documentElement.clientWidth) {
				width = document.documentElement.clientWidth;
			} else {
				throw 'Can not detect viewport width.';
			}
			return width + obj.getScrollbarWidth();
		};

		obj.addTouchListeners = function () {
			obj.container.swipe({
				swipeLeft: function (event, direction, distance, duration, fingerCount, fingerData) {
					if (distance < 150) {
						return;
					}
					obj.next();
				},
				swipeRight: function (event, direction, distance, duration, fingerCount, fingerData) {
					if (distance < 150) {
						return;
					}
					obj.prev();
				}
			});

		};

		obj.removerTouchListeners = function () {
			obj.container.swipe("destroy");
		};

		obj.init();
	};

	// Регистрируем плагин
	$.fn.kworkCarousel = function (options) {
		return this.each(function () {
			var element = $(this);

			// Return early if this element already has a plugin instance
			if (element.data('kworkcarousel')) return;

			// Pass options and element to the plugin constructer
			var kworkCarousel = new KworkCarousel(this, options);

			// Store the plugin object in this element's data
			element.data('kworkcarousel', kworkCarousel);
		});
	};

})(jQuery);