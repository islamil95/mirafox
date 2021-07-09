{strip}
{Helper::printJsFile("/js/chosen.jquery.js"|cdnBaseUrl)}
<div class="static-page__block">
	<div class="white-bg-block centerwrap">
		<div class="pt20 m-visible"></div>
		<h1 class="f32">{'Стили сайта'|t}</h1>

		<h2>{'Цвета'|t}</h2>
		<br>
		<div class="clearfix">
			<div class="pull-left mr10 mb10 t-align-c" style="height:150px;">
				<div style="width:100px;height:100px;background:#ffa800;color:white;line-height:100px;">#ffa800</div>
				<p>{'Основной'|t}</p>
			</div>
			<div class="pull-left mr10 mb10 t-align-c" style="height:150px;">
				<div style="width:100px;height:100px;background:#000;color:white;line-height:100px;">#000</div>
				<p>{'Текст'|t}</p>
			</div>
			<div class="pull-left mr10 mb10 t-align-c" style="height:150px;">
				<div style="width:100px;height:100px;background:#457edb;color:white;line-height:100px;">#457edb</div>
				<p>Ссылка<br>наведенная</p>
			</div>
			<div class="pull-left mr10 mb10 t-align-c" style="height:150px;">
				<div style="width:100px;height:100px;background:#009900;color:white;line-height:100px;">#009900</div>
				<p>{'Зеленый'|t}</p>
			</div>
			<div class="pull-left mr10 mb10 t-align-c" style="height:150px;">
				<div style="width:100px;height:100px;background:#8a8a8a;color:white;line-height:100px;">#8a8a8a</div>
				<p>{'Серый текст'|t}</p>
			</div>
			<div class="pull-left mr10 mb10 t-align-c" style="height:150px;">
				<div style="width:100px;height:100px;background:#bbb;color:black;line-height:100px;">#bbb</div>
				<p>Линии/рамки</p>
			</div>
		
			<div class="pull-left mr10 mb10 t-align-c" style="height:150px;">
				<div style="width:100px;height:100px;background:#f6f6f6;color:black;line-height:100px;">#f6f6f6</div>
				<p>{'Фон сайта'|t}</p>
			</div>
				<div class="pull-left mr10 mb10 t-align-c" style="height:150px;">
				<div style="width:100px;height:100px;background:#FAFAFA;color:black;line-height:100px;">#FAFAFA</div>
				<p>{'Фон блоков'|t}</p>
			</div>
			
			<div class="pull-left mr10 mb10 t-align-c" style="height:150px;">
				<div style="width:100px;height:100px;background:#f15b5b;color:white;line-height:100px;">#f15b5b</div>
				<p>{'Красный'|t}</p>
			</div>
		</div>
		<br><br>
		<h2>{'Кнопки'|t}</h2>
		<br>
		<button class="hugeGreenBtn hoverMe GreenBtnStyle h50 pull-reset">{'Кнопка 50px'|t}</button><br>
		<button class="GreenBtnStyle h24">{'Кнопка 24px'|t}</button><br><br>
		<button class="white-btn">{'Белая кнопка'|t}</button><br><br>
		<button class="disabled white-btn">{'Белая кнопка disabled'|t}</button><br><br>
		
		<button class="orange-btn">{'Оранжевая кнопка'|t}</button><br><br>
		<button class="orange-btn inactive">{'Оранжевая при наведении'|t}</button><br><br>

		<div class="btn-group btn-group_2">
			<div class="green-btn">{'Группа кнопок'|t}</div>
			<div class="white-btn">{'Группа кнопок'|t}</div>
		</div>
		<br><br>
		<h2>{'Тултипы,подсказки'|t}</h2>
		<br>
		<span class="tooltipster dib" data-tooltip-text="{'Тултип'|t}">{'Тултип'|t}</span><br><br>
		<span class="tooltipster dib" 
			data-tooltip-side="right"
			data-tooltip-text="{'Тултип'|t}" 
			data-tooltip-theme="dark">{'Черный тултип справа'|t}</span><br><br>
		<span class="tooltipster tooltip_circle tooltip_circle--hover"
			data-tooltip-text="<div class='tooltip-ico-warning'></div>{'Подсказка'|t}">?</span>
		<br><br>
		<h2>{'Элементы форм'|t}</h2>
		<div class="pull-reset"><br>
			<select class="h25 styled" >
				<option>{'Стилизированный селект'|t}</option>
				<option>{'Опция 2'|t}</option>
				<option>{'Опция 3'|t}</option>
			</select>
		</div><br>
		<select  class="select-styled select-styled--thin w230 " >
			<option>{'Нестилизированный селект'|t}</option>
			<option>{'Опция 2'|t}</option>
			<option>{'Опция 3'|t}</option>
		</select>
		<br>
		<div>
			<input id="example-checkbox" class="styled-checkbox" type="checkbox">
			<label for="example-checkbox">{'Чекбокс'|t}</label>
		</div>
		<br>
		<div>
			<input checked="checked" name="example-radio" class="styled-radio" id="example-radio" type="radio" value="0">
			<label for="example-radio">radio</label>
			<br>
			<input name="example-radio" class="styled-radio" id="example-radio2" type="radio" value="0">
			<label for="example-radio2">radio 2</label>
		</div>
        <br>
        <strong>Checkbox и radio всегда должны иметь активные лейблы (выделяться при клике по тексту)!</strong>
		<br><br>
		<h2>{'Текст'|t}</h2>
		<br>
		<h1>{'Заголовок 1'|t}</h1>
		<h2>{'Заголовок 2'|t}</h2>
		<br>
		<p>Текст <b>Жирный текст</b> <i>Курсив</i></p>

		<br><br>
		<h2>{'Карточки'|t}</h2>
		<div style="background: #F6F6F6; width:90%;padding:20px;">
			<h3 class="mb10 mt10">{'Простая карточка'|t}</h3>
			<div class="card" style="width:400px">
				<div class="card__content">
					{'Это простая карточка, без всяких излишеств'|t}
				</div>
			</div>
			<h3 class="mb10 mt10">{'Второстепенная карточка'|t}</h3>
			<div class="card card_secondary" style="width:400px">
				<div class="card__content">
					{'Каждый продавец сталкивался с ситуацией, когда покупатель отказывался принимать работу'|t}
				</div>
			</div>
			<h3 class="mb10 mt10">{'Карточка с тенью'|t}</h3>
			<div class="card card_shadow" style="width:400px">
				<div class="card__content">
					{'Каждый продавец сталкивался с ситуацией, когда покупатель отказывался принимать работу, утверждая,'|t}
					{'что это «Не то, что нам надо!». За этим следует масса доработок, исправлений. А в худшем случае'|t}
					{'продавец вынужден начинать все сначала. Это срывает сроки, злит покупателей, а те в свою очередь'|t}
					{'оставляют негативные отзывы.'|t}
					{'Все из-за недопонимания: покупатель неточно сформулировал свои «хотелки», а Вы не поняли «боли'|t}»
					{'клиента. Сотрудничество не получилось.'|t}
				</div>
			</div>

			<h3 class="mb10 mt10">{'Карточка с хедерем и футером'|t}</h3>
			<div class="card" style="width:400px">
				<div class="card__header">
					{'Это хедер'|t}
				</div>
				<div class="card__content">
					{'Каждый продавец сталкивался с ситуацией, когда покупатель отказывался принимать работу, утверждая,'|t}
					{'что это «Не то, что нам надо!». За этим следует масса доработок, исправлений. А в худшем случае'|t}
					{'продавец вынужден начинать все сначала. Это срывает сроки, злит покупателей, а те в свою очередь'|t}
					{'оставляют негативные отзывы.'|t}
					{'Все из-за недопонимания: покупатель неточно сформулировал свои «хотелки», а Вы не поняли «боли'|t}»
					{'клиента. Сотрудничество не получилось.'|t}
					<div class="card card_secondary">
						<div class="card__content">{'Это второстепенная карточка внутри карточки'|t}</div>
					</div>
				</div>
				<div class="card__content card__content_separator">
					<div style="padding-bottom:15px">{'Каждый продавец сталкивался с ситуацией, когда покупатель отказывался принимать работу, утверждая,'|t}
						{'что это «Не то, что нам надо!». За этим следует масса доработок, исправлений. А в худшем случае'|t}
						{'продавец вынужден начинать все сначала. Это срывает сроки, злит покупателей, а те в свою очередь'|t}
						{'оставляют негативные отзывы.'|t}
						{'Все из-за недопонимания: покупатель неточно сформулировал свои «хотелки», а Вы не поняли «боли'|t}»
						{'клиента. Сотрудничество не получилось.'|t}
					</div>
					<div class="card__content-inner card__content-inner_separator">
						<label class="checkbox">
							<input class="checkbox__input" name="checkbox[]" type="checkbox">
							<span class="checkbox__label">{'Опция какая-то'|t}</span>
						</label>
					</div>
					<div class="card__content-inner card__content-inner_separator">
						<label class="checkbox">
							<input class="checkbox__input" name="checkbox[]" type="checkbox">
							<span class="checkbox__label">{'Опция какая-то'|t}</span>
						</label>
					</div>
					<div class="card__content-inner card__content-inner_separator">
						<label class="checkbox">
							<input class="checkbox__input" name="checkbox[]" type="checkbox">
							<span class="checkbox__label">{'Опция какая-то'|t}</span>
						</label>
					</div>
				</div>
				<div class="card__footer">
					<a class="green-btn mr5">{'Написать'|t}</a>
					{'Это футер, например, с кнопкой'|t}
				</div>
			</div>
		</div>
                <h2>{'Нормы курсора'|t}</h2>
                <div style="background: #F6F6F6; width:90%;padding:20px;">
                    <p>{'Простой текст - default'|t}</p>
                    <p><a href="#" class="link">Ссылка</a> - pointer</p>
                </div>
                <h2>{'Подсказка'|t}</h2> 
                <div class="block-state-active">
                        {'Навести для получения подсказки'|t}
                        <div class="block-state-active_tooltip">{'Подсказка'|t}</div>                    
                </div>
				
		<h2>{'Уведомления'|t}</h2>
			<div class="flash_notice foxwidth800" style="font-weight:normal; background:#edfbd8 url(/images/warning.gif) 12px 50% no-repeat"><p>{'Зеленое уведомление'|t}</p></div>
			<div class="flash_notice foxwidth800" style="font-weight:normal; background:#edfbd8 url(/images/warning.gif) 12px 50% no-repeat">
				<p>{'Зеленое уведомление с убогим крестиком'|t}</p>
				<a onclick="closeEvent(0);"><img class="warning_close" src="{"/warning_close.gif"|cdnImageUrl}" alt=""></a>
			</div>

		<h2>{'Нормы отступов'|t}</h2>
		<div>Между нижним элементом центрального контейнера и футером - 35px. для блоков инфо-страниц использовать class="static-page__block"</div>
		<div>{'Расстояния до картинки, до текста от боковых граней блока должно быть оп шаблону (15 или 16 пикселей как на карточке кворка)'|t}</div>
		<br>
		<div>
			<p class="mb10">Между абзацами расстояние 10px, вот такой ↓↓↓</p>
			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
		</div>
		<br>

		<h2>{'Иконки для страницы заказа'|t}</h2>
		<i class="ico-more-info"></i>
		<i class="ico-pay"></i>
		<i class="ico-pay-red"></i>

		<i class="ico-quest-info"></i>
		<i class="ico-reating-good"></i>
		<i class="ico-reating-bad"></i>
		<i class="ico-check"></i>
		<i class="ico-track-tip-info"></i>
		<i class="ico-quest-red-info"></i>
		<i class="ico-red-box"></i>
		<i class="ico-green-box"></i>
		<i class="ico-orange-box"></i>


		<i class="ico-red-rocket"></i>
		<i class="ico-green-rocket"></i>
		<i class="ico-close-info"></i>
		<i class="ico-reject-info"></i>
		<i class="ico-close"></i>
		<i class="ico-green-truck"></i>
		<i class="ico-green-mark-truck"></i>
		

		<i class="ico-red-extras"></i>
		<i class="ico-green-extras"></i>
		
		<br><br>
		<h2>{'Таблицы в мобильной версии'|t}</h2>
		<div>Теперь, для того чтоб таблица отобразилась в виде http://joxi.ru/gmv7dLLCLN09Y2 нужно обернуть ее<br>&lt;div class="scroll-table"&gt;&lt;/div&gt;<br>а внутри будет таблица без каких либо дополнительных тегов</div>
	</div>
</div>



{literal}



    <script>
        $(window).load(function(){
      
            $('.styled').chosen({width: "108px", disable_search: true});
        })

	</script>
{/literal}


{/strip}