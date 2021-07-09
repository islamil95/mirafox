$(document).ready(function() {
	var rolldownTextTitle = $('.rolldown-text-title');
	
	function rolldown(t) {
		t.toggleClass('rolled');
		$(t.data('content')).toggleClass('rolled');
	};
	
	rolldownTextTitle.each(function(k, v) {
		var title = $(v), content = $($(v).data('content'));
		title.on('click', function(e) {
			rolldown($(e.delegateTarget));
		});
		if(content.data('noRollupLink') != true) {
			$('<div class="rollup-text">Свернуть</div>').appendTo(content).on('click', function() {
				rolldown(title);
			});
		};
	});
});