/**
 * Mixin содержит метод scrollTo для прокрутки страницы до элемента с заданным ид. Метод ждет,
 * пока элемент появится на экране, после чего прокручивает страницу до элемента,
 * учитывая при этом высоту меню (для десктоп и мобильной версии сайта)
 */

export default {

	methods: {

	    /**
	     * Прокрутить страницу до элемента
	     * @param {string} elementId
	     */
		scrollTo: function (elementId) {
			// Если элемент еще не появился на странице, подождать
			var element = document.getElementById(elementId);
			if (!element || element && element.offsetHeight == 0) {
				_.delay(this.scrollTo, 10, elementId);
			} else {
				// Прокрутить страницу до элемента
				element.scrollIntoView();
				// Откатить на высоту меню
				var menuHeight = $(".header_top").height();
				var scrolledY = window.scrollY;
				if (window.scrollY) {
					window.scroll(0, window.scrollY - menuHeight);
				}
			}	
		},

	},

};