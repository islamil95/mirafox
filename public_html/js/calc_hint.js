
var descAreaHint = (function() {

	var $input = $hint = min = max = null;

	var _init = function(inputElement, hintElement, minValue, maxValue) {
		if(inputElement){
			$input =  $(inputElement);
		}
		if(hintElement){
			$hint =  $(hintElement);
		}
		if(minValue){
			min =  minValue;
		}
		if(maxValue){
			max = maxValue;
		}
		if($input.length){
			$input.keyup(_update);
			$input.change(_change);
			$input.textAreaMaxLength(max);
			_update();
		}
	};

	var _update = function () {
		$hint.removeClass('color-red');

		var used = $input.val().length;

		if (used < min) {
			$hint.html(t('{{0}} из {{1}} минимум', [used, min]));
		} else {
			$hint.html(t('{{0}} из {{1}} максимум', [used, max]));
		}
	};

	var _change = function () {
		var used = $input.val().length;

		if (used < min || used > max) {
			$hint.addClass('color-red');
		}
	};

	return {
		init:_init
	};

})();
