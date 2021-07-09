require('appJs/text.js');

import ValidatableForm from 'appJs/validatable-form.js';

$(document).ready(() => {
	let form = $('#offer_kwork_form');
	if (form.length > 0) {
		window.offerForm = new ValidatableForm('#offer_kwork_form', {
			onUpdate: window.OfferIndividualModule.validateIndividualKwork,
		});
	}
});
