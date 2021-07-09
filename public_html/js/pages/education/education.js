const classes = {
	'button': '.js-education-download-button',
	'radio': '.js-education-download-radio',
	'counter': '.js-education-download-counter',
	'wrapper': '.js-education-download',
	'parent': '.js-education-lessons-item',
	'label': '.js-education-lessons-label',
	'recently': 'js-education-recently-downloaded',
};
let counters = {};

/**
 * Счетчики
 */
function getCounters() {
	jQuery(classes.wrapper).each(function () {
		const _this = jQuery(this);
		const fileName = _this.find(classes.button).data('file-name');
		const counterVal = _this.find(classes.counter).data('count');

		counters[fileName] = counterVal;
	});
}

/**
 * Событие скачивания урока
 */
function increaseDownloadCounter() {
	const _this = jQuery(this);
	const wrapper = _this.closest(classes.wrapper);
	const counter = wrapper.find(classes.counter);
	const fileName = wrapper.find(classes.button).data('file-name');
	const lessonLabel = _this.closest(classes.parent);

	if (wrapper.hasClass(classes.recently) === false) {

		counters[fileName]++;
		let counterVal = counters[fileName];

		counter
			.attr('data-count', counterVal)
			.html(
				Utils.numberFormat(counterVal, 0, '.', ' ') +
				' ' +
				declension(counterVal, t('раз'), t('раза'), t('раз')));

		lessonLabel.find(classes.label).fadeIn(150);
		wrapper.addClass(classes.recently)
	}
}

/**
 * Смена расшерения файла урока
 */
function changeDownloadType() {
	const _this = jQuery(this);
	const downloadType = _this.val();
	const downloadButton = _this.closest(classes.wrapper).find(classes.button);
	const fileName = downloadButton.data('file-name');

	downloadButton.attr('href', base_url + '/kwork_book/files/' + fileName + '/' + downloadType);
}

jQuery(function () {
	getCounters();
	jQuery(classes.radio).on('click', changeDownloadType);
	jQuery(document).on('click', classes.button, _.throttle(increaseDownloadCounter, 500));
});