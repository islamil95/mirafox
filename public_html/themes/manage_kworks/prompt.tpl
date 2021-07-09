{strip}
	<div class="info-block info-block_manage-kworks" data-type="manage_kworks" style="opacity: 0">
		<div class="info-block__ico v-align-m">
			<i class="info-block__ico-image icon ico-i-small relative"></i>
		</div>
		<br class="m-visible"><br class="m-visible">
		<div class="f13 sm-margin-reset">
			<div class="info-block__text">
				<div class="info-block__text-inner">
                                    {'Активные кворки показываются в каталоге и могут быть в любой момент заказаны.<br>'|t}
                                    {'Если вы не можете выполнять заказы, выберите свои кворки и нажмите "Остановить". '|t}
                                    {'Кворки скроются из каталога и не будут доступны для заказа. '|t}
                                    {'Рейтинг кворков будет сохранен. '|t}
                                    {'Чтобы вернуть выбранные кворки в продажу, нажмите "Активировать". <br>'|t}
                                    {'Для автоматической остановки кворков на выходные установите в <a href="/settings">Настройках</a> галку "Скрывать кворки на выходные"'|t}
				</div>
			</div>
			<a href="javascript:void(0)" class="js-info-block__link info-block__link link_local">{'Скрыть подсказку'|t}</a>
		</div>
	</div>
	{literal}
		<script>
				$('.info-block').infoBlock('', {textLinkShow: {/literal}{"'Как остановить продажи'"|t}{literal}});
		</script>
	{/literal}
{/strip}
