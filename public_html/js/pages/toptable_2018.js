function loadWorkers() {
	var container = $('.workers');
	var buttonMore = $('.workers').parent().find('.loadKworks');
	$(buttonMore).addClass('onload');
	$.ajax({
		method: 'post',
		dataType: 'html',
		data: {
			action: 'loadWorkers',
			offset: buttonMore.data('offset')
		},
		success: function(response) {
			buttonMore.closest('.more-div').remove();
			container.append(response);
		}
	});
}

function loadPayers() {
	var container = $('.payers');
	var buttonMore = $('.payers').parent().find('.loadKworks');
	$(buttonMore).addClass('onload');
	$.ajax({
		method: 'post',
		dataType: 'html',
		data: {
			action: 'loadPayers',
			offset: buttonMore.data('offset')
		},
		success: function(response) {
			buttonMore.closest('.more-div').remove();
			container.append(response);
		}
	});
}