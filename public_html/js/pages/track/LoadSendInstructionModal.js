require('appJs/bootstrap.js');
import SendInstructionModal from './send-instruction-modal';

export default class LoadSendInstructionModal {

	constructor() {
		this.message = '';
	}

	init() {
		if ($('#send-instruction-modal .send-instruction-modal').length > 0) {
			return;
		}
		this.message = $('.js-send-instruction-block-text').html();
		this.files = $('.js-send-instruction-block-files').html();
		if(document.querySelector('#send-instruction-modal')){
			window.sendInstuctionModal = null;
			window.sendInstuctionModal = new Vue({
				el: '#send-instruction-modal',
				components: {
					'send-instruction-modal': SendInstructionModal
				},
				data: {
					similar: (typeof similarOrderData !== 'undefined') ? similarOrderData : null,
					message: this.message,
					instructionFiles: this.files,
				},
			});

		}

		document.addEventListener('new-tracks-loaded',evt=>{
			this.init();
		})
	}
}