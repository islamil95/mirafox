export default class LinkChecker {
	constructor(instance, inputSelector = '#input-url', submitSelector = '.submit-url') {
		this.instance = instance;
		this.input = this.instance.querySelector(inputSelector);
		this.submit = this.instance.querySelector(submitSelector);
		this.error = this.instance.querySelector('.invalidurl');
	}

	init() {
		if (!this.submit) {
			return false;
		}
		this.submit.addEventListener('click', evt => {
			this.submitUrl()
		});
		this.input.addEventListener('input', e => {
			this.hideError();
		})
	}

	submitUrl() {
		this.submit.classList.add('processing');
		let link = this.input.value;

		if (this.validURL(link)) {
			this.sendRequest(link)
		} else {
			this.showError();
			this.submit.classList.remove('processing');
		}
	}

	hideError() {
		this.error.innerHTML = '';
	}

	showError() {
		alert('Введите правильную ссылку!');
	}

	sendRequest(link) {
		console.warn('Implement this method')
	}

	validURL(str) {
		let pattern = new RegExp('^(https?:\\/\\/)?' + // protocol
			'((([а-яёa-z\\d]([а-яёa-z\\d-]*[а-яёa-z\\d])*)\\.)+[а-яёa-z\\d-]{2,}|' + // domain name
			'((\\d{1,3}\\.){3}\\d{1,3}))' + // OR ip (v4) address
			'(\\:\\d+)?(\\/[-a-z@\\d%_.~+]*)*' + // port and path
			'(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
			'(\\#[-а-яёa-z\\d_]*)?$', 'i'); // fragment locator
		return !!pattern.test(str);
	}
}