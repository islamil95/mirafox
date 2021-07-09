{strip}
<div class="orangewrap">
	<div class="centerwrap">
		<h1 class="color-white f38">{'Kwork для продавца'|t}</h1>
		<div class="fs22 mt10 fontf-pnl">{'Фриланс нового поколения в России'|t}</div>
		<div class="fs18 mt30 lh30 fontf-pnl m-f14">
			{'Вы фрилансер со стажем, и Вам надоело участвовать в бессмысленных торгах на заказ, а потом <br class="m-hidden">обосновывать стоимость своих услуг? Или Вы только начинаете продавать услуги и хотите избежать <br class="m-hidden">типичных проблем удалённой работы? Тогда Кворк - это ваш выбор!'|t}
		</div>
		{if isAuth()}
			<a href="/new" class="js-blocked-kworks white-btn mt40 mb50 big no-border f25">{'Создать кворк'|t}</a>
		{else}
			<a href="javascript:;" class="white-btn mt40 mb50 big no-border f25 {if Translations::isDefaultLang()}signup-js{else}short-login-js{/if} worker">{'Создать кворк'|t}</a>
		{/if}
	</div>
</div>
<div class="whitewrap pt30">
	<div class="centerwrap main-wrap mt0">
		<div class="ta-center">
			<div>
				<div class="item-2-column ta-left fs18">
					<h3 class="fontf-pnb f32 mb20">{'Что такое Kwork'|t}</h3>
					<div class="fontf-pnl fs16 lh24 pr33">
						{'Каждый продавец сталкивался с ситуацией, когда покупатель отказывался принимать работу, утверждая, что это &laquo;Не то, что нам надо!&raquo;. За этим следует масса доработок, исправлений. А в худшем случае продавец вынужден начинать все сначала. Это срывает сроки, злит покупателей, а те в свою очередь оставляют негативные отзывы.'|t}
						<br><br>
						{'Все из-за недопонимания: покупатель не точно сформулировал свои «хотелки», а вы не поняли «боли» клиента. Сотрудничество не получилось.'|t}
					</div>
				</div>
				<div class="item-2-column pic-man-big"></div>
			</div>
			<div>
				<div class="item-2-column pic-woman-big"></div>
				<div class="item-2-column ta-left fs18">
					<div class="fontf-pnb fs22 lh30 mb20 pr33 fw600">{'Возьмите инициативу на себя<br /> и предложите покупателям свои услуги'|t}</div>
					<div class="fontf-pnl fs16 pr33 lh24">{'Кто как не фрилансер лучше знает что он может сделать, за какой срок и за какую стоимость?<br /><br />Организуйте свой бизнес-процесс! Один раз разместите свои услуги (<b>кворки</b>) в магазине Kwork и получайте заказы постоянно. Опишите услугу, оцените время выполнения, приложите примеры работ, установите справедливую цену. И зарабатывайте!<br /><br />Выполняйте индивидуальные заказы по обращению покупателей или ищите новые заказы на Бирже. Просматривайте новые задачи заказчиков и предлагайте свои услуги. Все в ваших руках!'|t}</div>
				</div>
			</div>
			<hr class="gray mt40">
			<div class="how-it-works-b">
				<h2 class="fontf-pnb fs34 lh42 mt20 mb10">{'Попробуйте магазин фриланс-услуг'|t}</h2>
				<div class="fontf-pnl fs18">{'Разместите свои услуги и получайте заказы, не прикладывая усилий'|t}</div>
				<div class="item-3-column">
					<i class="icon icon_info-ispolnitel ico-arrow-1 sm-hidden"></i>
					<i class="icon icon_info-ispolnitel ico-add-with-rect"></i>
					<div class="ta-left">
						<div class="fontf-pnb mb15 fs18 fw600">{'Выставьте ваши услуги на продажу'|t}</div>
						<div class="fontf-pnl fs14 lh18">{'Опишите услуги, укажите стоимость и срок выполнения. Приложите примеры. 5 минут - и готово!'|t}</div>
					</div>
				</div>
				<div class="item-3-column">
					<i class="icon icon_info-ispolnitel ico-arrow-2 sm-hidden"></i>
					<i class="icon icon_info-ispolnitel ico-bell-square mt10"></i>
					<div class="ta-left">
						<div class="fontf-pnb mb15 fs18 fw600 mt-10">{'Получите заказ без усилий'|t}</div>
						<div class="fontf-pnl fs14 lh18">{'Покупатели увидят услугу в каталоге и сделают заказ. Вы получите email-уведомление.'|t}</div>
					</div>
				</div>
				<div class="item-3-column">
					<i class="icon icon_info-ispolnitel ico-ok-with-rect"></i>
					<div class="ta-left">
						<div class="fontf-pnb mb15 fs18 fw600">{'Выполните и сдайте работу'|t}</div>
						<div class="fontf-pnl fs14 lh18">{'Выполните и сдайте работу в срок. После проверки покупателем средства поступят на ваш баланс.'|t}</div>
					</div>
				</div>
			</div>
			<hr class="gray">
			<div>
				<h2 class="fontf-pnb fs34 lh42 mt20 mb10">{'И биржу проектов'|t}</h2>
				<div class="fontf-pnl fs18">{'Выбирайте интересные задачи заказчиков и отправляйте свои предложения'|t}</div>
				<div class="item-3-column">
					<i class="icon icon_info-ispolnitel ico-add-with-rect"></i>
					<div class="ta-left">
						<div class="fontf-pnb fs18 mb10 fw600">{'Отправляйте предложения по проектам покупателей'|t}</div>
						<div class="fontf-pnl aw240 sm-center-block lh18">{'Отслеживайте новые задания на <a href="/projects">Бирже</a> и отправляйте свои предложения. Выбирайте задания, которые устраивает вас по цене, и с которыми вы точно справитесь.'|t}</div>
					</div>
				</div>
				<div class="item-3-column">
					<i class="icon icon_info-ispolnitel ico-bell-square"></i>
					<div class="ta-left">
						<div class="fontf-pnb fs18 mb10 fw600">{'Получите и выполните заказ'|t}</div>
						<div class="fontf-pnl sm-center-block lh18">{'Приступайте к заказам как можно скорее, выполняйте работу качественно и в срок. Это позитивно отразится на рейтинге!'|t}</div>
					</div>
				</div>
				<div class="item-3-column">
					<i class="icon icon_info-ispolnitel ico-cover"></i>
					<div class="ta-left">
						<div class="fontf-pnb fs18 mb10 fw600">{'Сдайте работу и получите оплату'|t}</div>
						<div class="fontf-pnl sm-center-block lh18">{'Сдайте готовую работу заказчику. Оплата автоматически начислится на ваш баланс после проверки заказа покупателем.'|t}</div>
					</div>
				</div>
			</div>
			<hr class="gray">
			<div>
				<div class="item-2-column ta-left fs18">
					<h3 class="fontf-pnb f32 lh30 mb20">{'Работа с гарантией'|t}</h3>
					<div class="fontf-pnl fs16 lh24">
						{'Kwork исключает мошенничество. Все сделки проводятся через систему. Покупатель оплачивает услугу в момент заказа. Пока заказ выполняется, деньги находятся под протекцией Kwork, чтобы гарантировать вам оплату. Оплата будет начислена на ваш счет сразу после подтверждения выполнения заказа. Отправить запрос на вывод средств можно в любой момент после зачисления их на счет.'|t}
					</div>
				</div>
				<div class="item-2-column mt20">
					<div class="pic-woman3-big"></div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="centerwrap">
	<div class="ta-center">
		<div>
			<div class="item-3-column">
				<div class="ta-left">
					<div class="ico-circle mb20 fontf-pnb dib">1</div>
					<div class="fontf-pnb fs18 mb10 fw600">{'1 кворк - 1 услуга'|t}</div>
					<div class="fontf-pnl aw240 sm-center-block lh18">{'Никаких недопониманий. Покупая 1 кворк, покупатель может рассчитывать только на услуги, описанные в кворке. Нужны доработки? Предложите покупателю дополнительные платные опции.'|t}</div>
				</div>
			</div>
			<div class="item-3-column">
				<div class="ta-left">
					<div class="ico-circle mb20 fontf-pnb dib">2</div>
					<div class="fontf-pnb fs18 mb10 fw600">{'Фиксированная цена'|t}</div>
					<div class="fontf-pnl aw240 sm-center-block lh18">{'Никаких попыток «сбить» цену со стороны покупателя. Это просто невозможно в системе. Вы можете создавать кворки с разным количеством услуг и устанавливать соответствующие цены.'|t}</div>
				</div>
			</div>
			<div class="item-3-column">
				<div class="ta-left">
					<div class="ico-circle mb20 fontf-pnb dib">3</div>
					<div class="fontf-pnb fs18 mb10 fw600">{'Простой расчет'|t}</div>
					<div class="fontf-pnl aw240 sm-center-block lh18">{'Планируйте бюджет легко. Начиная работу, с каждой 1 000 рублей продаж вы получаете 800 рублей на счет. Чем больше заказов вы выполняете для одного покупателя, тем ниже ваша <a href="%s">комиссия</a>! После оборота в 30 000 руб. она снижается до 12%%, а с 300 000 руб. - до 5%%.'|t:'/faq?role=5&article=452'}</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="whitewrap pt0">
	<div class="centerwrap main-wrap pt20 work-with-guarantee-b">
		<div class="ta-center">
			<div class="become-pro">
				<div class="become-pro__image pic-man3-big"></div>
				<div class="become-pro__text ta-left fs18">
					<div class="fontf-pnb fs22 lh30 mb20 fw600">{'Стань профессионалом на Kwork и получай максимум заказов'|t}</div>
					<div class="fontf-pnl fs16 lh24">{'Kwork ценит ответственных и профессиональных исполнителей.<br /><br /><b>Никакого демпинга.</b> Вы сами формируете стоимость своих услуг.<br /><br /><b>Качество в цене.</b> Чем лучше вы выполняете работу, сдаете заказы в срок и не отменяете без уважительной причины - тем выше ваш рейтинг.<br /><br /><b>Чем выше рейтинг - тем больше заказов.</b> Кворки с высоким рейтингом от ответственных исполнителей ранжируются выше и получают больше заказов в магазине. У таких исполнителей больше коннектов на бирже, что дает возможность взять больше хороших заказов.<br /><br />Kwork заботится о том чтобы лучшие исполнители получали больше заказов.<br />Становись профессионалом на Kwork и зарабатывай больше!'|t}</div>
				</div>
			</div>
			<hr class="gray mt40">
			{if Translations::isDefaultLang()}
				<h2 class="fontf-pnb fs34 mb10">{'Отзывы продавцов'|t}</h2>
				<div class="fontf-pnl fs18">{'Фрилансеры ценят Kwork за то, что...'|t}</div>
				<div class="comments-panel">
					{foreach $reviews as $review name=reviewsList}
						{if ($smarty.foreach.reviewsList.index + 1) % 2 != 0}
							<div>
						{/if}
						<div class="comments-panel_item">
							<div>
								<img src="{"/medium/{$review->profilepicture}"|cdnMembersProfilePicUrl}"
									{userMediumPictureSrcset($review->profilepicture)}
									width="60" height="60" alt="{$review->username}" class="comments-panel_item_img"/>
								<div class="comments-panel_item_text">
									<a href="/user/{$review->username|lower}" class=" bold fs18 fontf-pnb">{$review->username}</a>
									<ul class="rating-block">
										{control name=rating_stars rating=$review->cache_rating}
									</ul>
									<div class="fontf-pnb">{$review->fullname}</div>
								</div>
							</div>
							<p>{$review->text|stripslashes|html_entity_decode|nl2br}</p>
						</div>
						{if ($smarty.foreach.reviewsList.index + 1) % 2 == 0}
							</div>
						{/if}
					{/foreach}
					<div class="comments-panel_link">
						<a href="/otzyvy-ispolniteley" class="fs16 underline dib mb30">{'Смотреть все отзывы'|t}</a>
					</div>
				</div>
			{/if}
		</div>
	</div>
</div>
<div class="ta-center mt20 mb40">
	<div class="fontf-pnb fs26">{'Начните прямо сейчас'|t}</div>
	<div class="dib">
		{if isAuth()}
			<a href="/new" class="js-blocked-kworks GreenBtnStyle big left-panel-button nowrap" style="height:50px;max-width:inherit;">{'Создать кворк'|t}</a>
		{else}
			<a href="javascript:;" class="GreenBtnStyle big left-panel-button {if Translations::isDefaultLang()}signup-js{else}short-login-js{/if} worker nowrap" style="height:50px;max-width:inherit;">{'Создать кворк'|t}</a>
		{/if}
	</div>
	<div class="mt15 fs18">
		{'или'|t} <a href="/projects" class="underline">{'Посмотреть проекты на Бирже'|t}</a>
	</div>
</div>
{/strip}