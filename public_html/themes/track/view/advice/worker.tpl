{strip}
	<div class="request-not-correspond request-not-correspond_theme_order js-request-type-{$track->type} request-not-correspond_double_false" style="display: none">
		<div class="request-not-correspond__title request-not-correspond__title_theme_flex bold">
			<div class="request-not-correspond__title-item">
				{'Покупатель требует больше, чем описано в кворке?'|t}
				<i class="ico-arrow-down request-not-correspond__title-icon"></i>
			</div>
		</div>
		<div class="request-not-correspond__more-text" style="display: none;">
			<p>{'Небольшие доработки лучше сделать бесплатно, но что, если заказчик просит слишком много?'|t}</p>
			<ol class="request-not-correspond__list">
				<li class="request-not-correspond__list-item">{'Убедитесь, что в описании вашего кворка есть четкие ограничения, под которые не попадают требования покупателя. Если это не так, отредактируйте описание, чтобы ссылаться на него в будущем в спорных ситуациях (но для данного заказа условия уже не меняются).'|t}</li>
				<li class="request-not-correspond__list-item">{'Покажите покупателю описание кворка, вежливо поясните, что его требования превышают объем кворка.'|t}</li>
				<li class="request-not-correspond__list-item">{'<b>Самый важный шаг!</b> Тут же вежливо предложите покупателю доп. опцию, которая покрывает его пожелания (если такой опции еще нет, отправьте покупателю предложение индивидуальной опции). Если он согласится, вы заработаете больше, если нет, то, как минимум, вероятность негатива с его стороны будет ниже, чем в случае вашего отказа от работы.'|t}</li>
			</ol>
			<p><b>{'Сравнение: покупатель требует слишком много (работа выполнена хорошо)'|t}</b></p>
			<div class="request-not-correspond__table-responsive">
				<table class="request-not-correspond__table">
					<thead>
					<tr>
						<th class="request-not-correspond__table-cell request-not-correspond__table-cell_theme_header">&nbsp;</th>
						<th class="request-not-correspond__table-cell request-not-correspond__table-cell_theme_header">{'Откажусь делать<br> (худший вариант)'|t}</th>
						<th class="request-not-correspond__table-cell request-not-correspond__table-cell_theme_header">{'Сделаю бесплатно'|t}</th>
						<th class="request-not-correspond__table-cell request-not-correspond__table-cell_theme_header">{'Сделаю за доплату'|t}</th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td class="request-not-correspond__table-cell request-not-correspond__table-cell_theme_left">{'Настроение покупателя'|t}</td>
						<td class="request-not-correspond__table-cell">
							<img class="request-not-correspond__table-icon" src="{"/smile_1.png"|cdnImageUrl}" alt="">
						</td>
						<td class="request-not-correspond__table-cell">
							<img class="request-not-correspond__table-icon" src="{"/smile_5.png"|cdnImageUrl}" alt="">
						</td>
						<td class="request-not-correspond__table-cell">
							<img class="request-not-correspond__table-icon" src="{"/smile_3.png"|cdnImageUrl}" alt="">
						</td>
					</tr>
					<tr>
						<td class="request-not-correspond__table-cell request-not-correspond__table-cell_theme_left">{'Рейтинг на Kwork'|t}</td>
						<td class="request-not-correspond__table-cell">
							<div class="request-not-correspond__table-icon-wrapper">
								<img class="request-not-correspond__table-icon" src="{"/smile_1.png"|cdnImageUrl}" alt="">
								<span class="request-not-correspond__table-icon-note">*</span>
							</div>
						</td>
						<td class="request-not-correspond__table-cell">
							<img class="request-not-correspond__table-icon" src="{"/smile_5.png"|cdnImageUrl}" alt="">
						</td>
						<td class="request-not-correspond__table-cell">
							<img class="request-not-correspond__table-icon" src="{"/smile_5.png"|cdnImageUrl}" alt="">
						</td>
					</tr>
					<tr>
						<td class="request-not-correspond__table-cell request-not-correspond__table-cell_theme_left">{'Заработок'|t}</td>
						<td class="request-not-correspond__table-cell">
							<img class="request-not-correspond__table-icon" src="{"/smile_3.png"|cdnImageUrl}" alt="">
						</td>
						<td class="request-not-correspond__table-cell">
							<img class="request-not-correspond__table-icon" src="{"/smile_3.png"|cdnImageUrl}" alt="">
						</td>
						<td class="request-not-correspond__table-cell">
							<img class="request-not-correspond__table-icon" src="{"/smile_5.png"|cdnImageUrl}" alt="">
						</td>
					</tr>
					<tr>
						<td class="request-not-correspond__table-cell request-not-correspond__table-cell_theme_left">{'Арбитраж'|t}</td>
						<td class="request-not-correspond__table-cell"><strong class="color-red">{'Часто'|t}</strong></td>
						<td class="request-not-correspond__table-cell"><strong class="color-green">{'Нет'|t}</strong></td>
						<td class="request-not-correspond__table-cell"><strong class="color-green">{'Нет'|t}</strong></td>
					</tr>
					<tr>
						<td class="request-not-correspond__table-cell request-not-correspond__table-cell_theme_left">{'В итоге мое настроение'|t}</td>
						<td class="request-not-correspond__table-cell">
							<img class="request-not-correspond__table-icon" src="{"/smile_1.png"|cdnImageUrl}" alt="">
						</td>
						<td class="request-not-correspond__table-cell">
							<img class="request-not-correspond__table-icon" src="{"/smile_5.png"|cdnImageUrl}" alt="">
						</td>
						<td class="request-not-correspond__table-cell">
							<img class="request-not-correspond__table-icon" src="{"/smile_5.png"|cdnImageUrl}" alt="">
						</td>
					</tr>
					</tbody>
				</table>
			</div>
			<p><small><em>{'*В случае отрицательного отзыва или проигранного арбитража (вероятность чего высока)'|t}</em></small></p>
		</div>
	</div>
{/strip}