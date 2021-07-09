(function ($) {
	/*
	 * Валидация: Запрещаем вводить русские буквы
	 */
	var $input = $('#top-payer-contact input.text');
	var regex = new RegExp("^[А-Яа-яЁё]+$");
	// Валидация символов при вводе
	$input.on("keypress", function (e) {
		var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
		if (regex.test(key)) {
			e.preventDefault();
			return false;
		}
	});
	// Валидация при отправке формы
	$("#top-payer-contact").on("submit", function(e){
		var str = $input.val();
		// Проверяем строку побуквенно
		for(var i = 0; i <= str.length; i++) {
			if (regex.test(str[i])) {
				e.preventDefault();
				alert("Введены не корректные данные.");
				return false;
			}
		}
	})
})(jQuery);