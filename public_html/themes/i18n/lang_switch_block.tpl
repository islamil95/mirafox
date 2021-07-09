{strip}
	{* Вывод тултипа для вкладок выбора языка кворка *}
	{function tooltip_html lang=lang}
		<div class="field-tooltip_name language-tooltip {if $lang == Translations::EN_LANG}en-tooltip{else}ru-tooltip{/if} field-tooltip">
			<div class="field-tooltip__corner"></div>
			<div class="field-tooltip__image"></div>
			<div class="field-tooltip__text-block t-align-l">
				{if $lang == Translations::DEFAULT_LANG}
					<div class="field-tooltip__title"><b>Создание кворка на русском языке</b></div>
					<div class="field-tooltip__message">Опишите услугу на русском языке, чтобы привлечь покупателей с русскоязычной версии Kwork.ru.</div>
				{else}
					<div class="field-tooltip__title"><b>Создание кворка на английском языке</b></div>
					<div class="field-tooltip__message">Опишите услугу на английском языке, чтобы привлечь покупателей с западного рынка. Ваша услуга будет показываться в английской версии Kwork.com.</div>
				{/if}
			</div>
		</div>
	{/function}
	<div class="lang_switch_container{if $p.PID} d-none{/if}">
		<div class="kwork_lang_switch_wrapper">
			{if $p.PID}
				{* #5690 *}
				{if false}
					{if $p.lang == Translations::DEFAULT_LANG || $p.twin_id}
						<div class="tab {if $p.lang == Translations::DEFAULT_LANG}selected{/if} kwork-save-step__field-value kwork-save-step__field-value_tooltip wAuto">
							<div class="tab_c">
								<a href="{if $p.lang == Translations::DEFAULT_LANG}#{else}{$baseurl}{$p.twinUrl}{/if}">{'РУС'|t}</a>
							</div>
							{tooltip_html lang=Translations::DEFAULT_LANG}
						</div>
					{/if}
					<div class="tab en {if $p.lang == Translations::EN_LANG}selected{/if} kwork-save-step__field-value kwork-save-step__field-value_tooltip wAuto">
						<div class="tab_c">
							{if $p.lang == Translations::EN_LANG}
								<a href="#">{'EN (бета-версия)'|t}</a>{* EN *}
							{else}
								{if $p.twin_id}
									<a href="{$baseurl}{$p.twinUrl}">{'EN (бета-версия)'|t}</a>{* EN *}
								{else}
									<a href="{$baseurl}/new?twin_id={$p.PID}">{'EN (бета-версия)'|t}</a>{* EN добавить *}
								{/if}
							{/if}
						</div>
						{tooltip_html lang=Translations::EN_LANG}
					</div>
				{/if}
			{else}
				{if Translations::isDefaultLang()}
					<div class="tab {if $lang == Translations::DEFAULT_LANG}selected{/if}  kwork-save-step__field-value kwork-save-step__field-value_tooltip wAuto">
						<div class="tab_c">
							{if $twinId}
								<a href="{$baseurl}/edit?id={$twinId}">{'РУС'|t}</a>
							{else}
								<a href="{$baseurl}/new?lang={Translations::DEFAULT_LANG}">{'РУС'|t}</a>
							{/if}
						</div>
						{tooltip_html lang=Translations::DEFAULT_LANG}
					</div>
				{/if}
				<div class="tab en {if $lang != Translations::DEFAULT_LANG}selected{/if} kwork-save-step__field-value kwork-save-step__field-value_tooltip wAuto">
					<div class="tab_c">
						<a href="{$baseurl}/new?lang={Translations::EN_LANG}">
							{if Translations::isDefaultLang()}
								{'EN (бета-версия)'|t}{* EN добавить *}
							{else}
								{'EN (бета-версия)'|t}{* EN *}
							{/if}
						</a>
					</div>
					{tooltip_html lang=Translations::EN_LANG}
				</div>
			{/if}
			<div class="clear"></div>
		</div>
	</div>
{/strip}