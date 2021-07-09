$(document).ready(function() {
	$('.kwork-edit-tip').on('click', function(e) {
		var popup=$.popup(`
		<div class="kwork-popup__block kwork-edit-popup">
			<div class="kwork-popup__header">
				<div class="kwork-popup__title">Как повысить качество описания кворка?</div>
				<div class="kwork-popup__close js-close"></div>
			</div>
			<hr class="gray mt20 balance-popup__line">
			<p>Грамотный перевод кворков увеличивает шансы на успешные продажи. Есть несколько способов получить качественный перевод.</p>
			<br />
			<p><b>1 способ</b></p>
			<p>Заказать перевод у фрилансеров. На Kwork можно найти большое количество <a target="_blank" href="`+ORIGIN_URL+`/search?query=%D0%BF%D0%B5%D1%80%D0%B5%D0%B2%D0%BE%D0%B4+%D1%81+%D1%80%D1%83%D1%81%D1%81%D0%BA%D0%BE%D0%B3%D0%BE+%D0%BD%D0%B0+%D0%B0%D0%BD%D0%B3%D0%BB%D0%B8%D0%B9%D1%81%D0%BA%D0%B8%D0%B9&c=0">услуг по переводам</a>, заказать как перевод с нуля, так и редактирование написанного текста.</p>
			<br />
			<p><b>2 способ</b></p>
			<p><a rel="nofollow" target="_blank" href="https://www.grammarly.com">Установить плагин</a> Grammarly. Это бесплатный и удобный инструмент, который поможет улучшить качество письменного английского.</p>
			<br />
			<p><b>Что может Grammarly?</b></p>
			<ul>
				<li>Подсказывать исправления к грамматическим ошибкам</li>
				<li>Согласовывать формы глаголов</li>
				<li>Проверять уместность использования слов</li>
				<li>Предлагать синонимы, чтобы сделать письмо более точным</li>
			</ul>
			<br />
			<p>Плагин работает в браузерах Chrome, Safari и Firefox. Он встраивается в любое текстовое поле и проверяет введенный текст, например, описание кворка.</p>
			<p>Пример подсказок от Grammarly при создании кворка:</p>
			<img src="${Utils.cdnImageUrl("/kwork_edit_tip/1.png")}" />
			<p><b>Установка плагина в 3 шага.</b> <a class="kwork-popup__more" data-for=".kwork-popup__full" data-collapse-text="Скрыть">Подробнее…</a></p>
			<div class="kwork-popup__full">
				<br />
				<h2>Установка плагина</h2>
				<br />
				<p>Покажем на примере браузера Google Chrome.</p>
				<p><b>Шаг 1.</b> На сайте <a rel="nofollow" target="_blank" href="https://grammarly.com">https://grammarly.com</a> нажмите на зеленую кнопку "Add to Chrome".</p>
				<img src="${Utils.cdnImageUrl("/kwork_edit_tip/2.png")}" />
				<p>Установите расширения для браузера.</p>
				<img src="${Utils.cdnImageUrl("/kwork_edit_tip/3.png")}" />
				<p><b>Шаг 2.</b> Когда расширение для браузера будет установлено, справа от адресной строки нажмите на иконку Grammarly, чтобы активировать расширение.</p>
				<img src="${Utils.cdnImageUrl("/kwork_edit_tip/4.png")}" />
				<p><b>Шаг 3.</b> Зарегистрируйте аккаунт в Grammarly для использования всех его бесплатных функций.</p>
				<img src="${Utils.cdnImageUrl("/kwork_edit_tip/5.png")}" />
				<p>Готово! Теперь в текстовых полях вы увидите иконку Grammarly и подсказки по исправлению текстов на английском.</p>
			</div>
		</div>`);
		popup.find('.kwork-popup__more').more(popup);
	});
});
