export default class TrackHistory {
	constructor(instance) {
		this.instance = instance;
		this.progress = instance.querySelector('.progress-line');
		this.parent = null;
		this.maxHeight = 0;
		this.height = 0;
	}

	initProgress() {
		this.toggler = this.instance.querySelector('.track--progress__toggle');
		if (this.progress.querySelectorAll('.progress-line--item').length > 5) {
			this.toggler.classList.remove('hide');
		}
		this.initTooltips();
	}

	initTooltips(){
		$(this.instance).find('.tooltipster').each(function(){
			$(this).tooltipster(TOOLTIP_CONFIG);
		});
	}
	
	init() {
		this.initProgress();
	}
}