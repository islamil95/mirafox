import LinkChecker from './LinkChecker';

export default class Case2019LinkChecker extends LinkChecker {

	sendRequest(link) {
		axios.post('', {
			link: link
		}).then(response => {
			response = response.data;
			if (response.success) {
				this.instance.querySelector('.b-promo-page_form_title').innerHTML = response.data.title;
				this.instance.querySelector('.b-promo-page_text').classList.remove('hide');
				this.instance.querySelector('.promo-case-form').classList.add('hide');
				this.instance.querySelector('.b-promo-page_text').innerHTML = response.data.desc;
			} else{
				this.showError(response.data.desc)
			}
			this.submit.classList.remove('processing');

		}).catch(err => {
			console.warn(err);
			this.submit.classList.remove('processing');

		})
	}

	showError(message = 'Введите правильную ссылку!') {
		this.error.innerHTML = message
	}
}