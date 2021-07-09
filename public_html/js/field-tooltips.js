function showKworkTooltip(tooltipClass, posElement) {
	if (tooltipClass) {
		var $tooltip = $(this).find(tooltipClass);
	} else {
		var $tooltip = $(this).find('.field-tooltip:first');
	}
	$tooltip.fadeIn(300);

	var defTop = $(this).offset().top;

	var $corner = $tooltip.find('.field-tooltip__corner');

	$corner.css('top', '65px');
	$tooltip.css('top', '0');

	var tooltipHeight = $tooltip.height(),
		tooltipTop;

	var minOffset = ($('body').scrollTop() ^ 0) + 84 + 20;

	var tooltipMaxTop = Math.max(minOffset, defTop - 65);

	var maxOffset = Math.min(Utils.getWindowOffset() - 20, defTop + tooltipHeight);
	var tooltipMinTop = tooltipHeight - (maxOffset - defTop);

	if (tooltipMaxTop + tooltipHeight > Utils.getWindowOffset() - 20) {
		tooltipTop = tooltipMinTop;
	} else {
		tooltipTop = defTop - tooltipMaxTop;
	}

	tooltipTop = (-tooltipTop);

	if (tooltipTop > -150 && tooltipTop < -130) {
		tooltipTop = -150;
	}

	$tooltip.css('top', tooltipTop + 'px');

	var cornerTop = $corner.offset().top;

	if (cornerTop < defTop) {
		$corner.css({top: (defTop - cornerTop + 65) + 'px'});
	} else if (cornerTop > defTop + $(this).height()) {
		cornerTop = Math.min(minOffset - $tooltip.offset().top, defTop + $(this).outerHeight(true) - minOffset);
		$corner.css({top: cornerTop + 'px'});
	} else {
		$corner.css({top: +'65px'});
	}
	var windowTop = $(window).scrollTop() + 30;
	if ($(posElement).length) {
		var posElementTop = $(posElement).offset().top - $tooltip.height()/2 ;
		tooltipTop = Math.abs(posElementTop-defTop);
		$tooltip.css('top',tooltipTop + 'px');
		$corner.css({top: $tooltip.height()/2+(10) + 'px'});
	}else if ($tooltip.offset().top <= windowTop) {
		$tooltip.css('top', (windowTop - $tooltip.offset().top) + 'px');
	}
	if (tooltipTop <= -150 || $(posElement).length) {
		$tooltip.find('.field-tooltip__corner').addClass('field-tooltip__corner_alternative');
	} else {
		$tooltip.find('.field-tooltip__corner').removeClass('field-tooltip__corner_alternative');
	}
}

function tooltipShow(e) {
	let showFromWidth = parseInt($(e).data('show-from-width'), 10);
	if (showFromWidth && window && window.innerWidth <= showFromWidth) {
		return;
	}
	// Для случая вложенных тултип-активаторов
	$(e).parents('.kwork-save-step__field-value_tooltip').each(function () {
		// Находим всех родителей текущего тултипа которые также являются активаторами
		var parent = $(this);
		// Сбрасываем им задержанную функцию появления
		// Т.к. они могут быть забиндены после текущего - делаем это с небольшой задержкой - асинхронно
		setTimeout(function(){
			clearTimeout(parent.data('timer') ^ 0);
			// И закрываем сами тултипы если они уже показаны
			parent.find('.field-tooltip:first').hide();
			}, 0);

	});
	var timer = setTimeout(function () {
		if (!$(e).find('.field-tooltip').hasClass('field-tooltip-permanent')) {
			$('.field-tooltip-permanent').fadeOut(150);
		}
		showKworkTooltip.apply(e);
	}, 200);
	$(e).data('timer', timer);
}

function tooltipHide(e) {
	if ($(e).find('.field-tooltip').hasClass('field-tooltip-permanent')) {
		return false;
	}
	clearTimeout($(e).data('timer') ^ 0);
	$(e).find('.field-tooltip').fadeOut(150);
	var $permanentTooltip = $('.field-tooltip-permanent');
	if ($permanentTooltip.length === 1) {
		$('.field-tooltip-permanent').fadeIn(150);
	}
	// Для случая вложенных тултипов - находим тултипы более высоких уровней
	// если они есть показываем их при скрытии текущего
	$(e).parents('.kwork-save-step__field-value_tooltip').each(function () {
		tooltipShow(this);
	});
}