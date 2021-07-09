{strip}
	<div class="js-links-filters{if !$isKworkLinksSitesAttribute} hidden{/if}">
		<div class="mt15 kworks-search-filter popup-filter__group">
			<div class="card__content-header popup-filter__group-title">
				<strong>{"Количество ссылок в кворке"|t}</strong>
			</div>
			<div class="other-filter-inputs-block card__content-body">
				<div class="other-filter-input__box">
					<input title="" class="other-filter-input" placeholder="{"От "|t}" value="" autocomplete="off"
							type="number" data-max="3000" data-name="lcount" data-type="from" name="">
					<div class="other-filter-input__clear hidden"></div>
				</div>
				<div class="other-filter-input__box ml10">
					<input title="" class="other-filter-input" placeholder="{"До "|t}" value="" autocomplete="off"
							type="number" data-max="3000" data-name="lcount" data-type="to">
					<div class="other-filter-input__clear hidden"></div>
				</div>
				<input type="hidden" name="lcount" value="">
			</div>
		</div>
		{if \Translations::isDefaultLang()}
			<div class="mt15 kworks-search-filter popup-filter__group">
				<div class="card__content-header popup-filter__group-title">
					<strong>{"ИКС площадок"|t}</strong>
					<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16">
						<span class="tooltipster tooltip_circle tooltip_circle--light tooltip_c	ircle--hover tooltip_circle--scale-16"
								data-tooltip-side="right"
								data-tooltip-text="<p>{"Индекс качества сайта по версии Яндекса."|t}</p>
						<ul class='mb5 list-marked'>
							<li>{"до 50 – сайт начального уровня"|t}</li>
							<li>{"от 50 до 500 – хороший сайт"|t}</li>
							<li>{"от 500 – мощный сайт"|t}</li>
						</ul>
						<p>{"Не менее 50%% площадок будут иметь указанный вами показатель ИКС."|t}</p>"
								data-tooltip-theme="dark">?</span>
					</span>
				</div>
				<div class="other-filter-inputs-block card__content-body">
					<div class="other-filter-input__box">
						<input title="" class="other-filter-input" placeholder="{"От "|t}" value="" autocomplete="off"
								type="number" data-name="lsqi" data-type="from">
						<div class="other-filter-input__clear hidden"></div>
					</div>
					<div class="price-filter-input__box ml10">
						<input title="" class="other-filter-input" placeholder="{"До "|t}" value="" autocomplete="off"
								type="number" data-name="lsqi" data-type="to">
						<div class="other-filter-input__clear hidden"></div>
					</div>
					<input type="hidden" name="lsqi" value="">
				</div>
			</div>
		{/if}
		<div class="mt15 kworks-search-filter popup-filter__group">
			<div class="card__content-header popup-filter__group-title">
				<strong>{"Majestic CF"|t}</strong>
					<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16">
						<span class="tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover tooltip_circle--scale-16"
								data-tooltip-side="right"
								data-tooltip-text="<p>{"Показатель, определяющий количество ссылок на сайте и их силу при продвижении в Google."|t}</p>
							<ul class='mb5 list-marked'>
								<li>{"до 5 – сайт начального уровня"|t}</li>
								<li>{"от 6 до 24 – хороший сайт"|t}</li>
								<li>{"от 25 до 100 – мощный сайт"|t}</li>
							</ul>"
								data-tooltip-theme="dark">?</span>
					</span>
			</div>
			<div class="other-filter-inputs-block card__content-body">
				<div class="other-filter-input__box">
					<input title="" class="other-filter-input" placeholder="{"От "|t}" value="" autocomplete="off"
							type="number" data-max="100" data-name="lmajestic" data-type="from">
					<div class="other-filter-input__clear hidden"></div>
				</div>
				<div class="price-filter-input__box ml10">
					<input title="" class="other-filter-input" placeholder="{"До "|t}" value="" autocomplete="off"
							type="number" data-max="100" data-name="lmajestic" data-type="to">
					<div class="other-filter-input__clear hidden"></div>
				</div>
				<input type="hidden" name="lcount" value="">
			</div>
		</div>
		<div class="mt15 kworks-search-filter popup-filter__group">
			<div class="card__content-header popup-filter__group-title">
				<strong>{"Траст площадок"|t}</strong>
				<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16">
					<span class="tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover tooltip_circle--scale-16"
							data-tooltip-side="right"
							data-tooltip-text="<p>{"Показатель качества ссылочного профиля сайта."|t}</p>
						<ul class='mb5 list-marked'>
							<li>{"до 30 – обычный сайт"|t}</li>
							<li>{"от 31 до 50 – мощный сайт"|t}</li>
							<li>{"от 51 до 100 – очень мощный сайт"|t}</li>
						</ul>
						<p>{"Не менее 50%% площадок будут иметь указанный вами Траст."|t}</p>"
							data-tooltip-theme="dark">?</span>
				</span>
			</div>
			<div class="other-filter-inputs-block card__content-body">
				<div class="other-filter-input__box">
					<input title="" class="other-filter-input" placeholder="{"От "|t}" value="" autocomplete="off"
							type="number" data-max="100" data-name="ltrust" data-type="from">
					<div class="other-filter-input__clear hidden"></div>
				</div>
				<div class="price-filter-input__box ml10">
					<input title="" class="other-filter-input" placeholder="{"До "|t}" value="" autocomplete="off"
							type="number" data-max="100" data-name="ltrust" data-type="to">
					<div class="other-filter-input__clear hidden"></div>
				</div>
				<input type="hidden" name="ltrust" value="">
			</div>
		</div>
		<div class="mt15 kworks-search-filter popup-filter__group">
			<div class="card__content-header popup-filter__group-title">
				<strong>{"Спам площадок"|t}</strong>
				<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16">
					<span class="tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover tooltip_circle--scale-16"
							data-tooltip-side="right"
							data-tooltip-text="<p>{"Относительное количество исходящих ссылок с сайта."|t}</p>
						<ul class='mb5 list-marked'>
							<li>{"до 7 – чистый сайт с минимальным количеством исходящих ссылок"|t}</li>
							<li>{"от 7 до 12 – сайт с допустимым количеством исходящих ссылок"|t}</li>
							<li>{"от 12 до 100 – сайт с повышенным количеством исходящих ссылок"|t}</li>
						</ul>
						<p>{"Не менее 50%% площадок будут иметь указанный вами показатель Спама."|t}</p>"
							data-tooltip-theme="dark">?</span>
				</span>
			</div>
			<div class="other-filter-inputs-block card__content-body">
				<div class="other-filter-input__box">
					<input title="" class="other-filter-input" placeholder="{"От "|t}" value="" autocomplete="off"
							type="number" data-max="100" data-name="lspam" data-type="from">
					<div class="other-filter-input__clear hidden"></div>
				</div>
				<div class="price-filter-input__box ml10">
					<input title="" class="other-filter-input" placeholder="{"До "|t}" value="" autocomplete="off"
							type="number" data-max="100" data-name="lspam" data-type="to">
					<div class="other-filter-input__clear hidden"></div>
				</div>
				<input type="hidden" name="lspam" value="">
			</div>
		</div>
		<div class="mt15 kworks-search-filter popup-filter__group">
			<div class="card__content-header popup-filter__group-title">
				<strong>{"Трафик площадок в сутки"|t}</strong>
				<span class="wrap-tooltip_circle wrap-tooltip_circle--scale-16">
					<span class="tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover tooltip_circle--scale-16"
							data-tooltip-side="right"
							data-tooltip-text="<p>{"Не менее 50%% площадок будут иметь указанный вами Трафик."|t}</p>"
							data-tooltip-theme="dark">?</span>
				</span>
			</div>
			<div class="other-filter-inputs-block card__content-body">
				<div class="other-filter-input__box">
					<input title="" class="other-filter-input" placeholder="{"От "|t}" value="" autocomplete="off"
							type="number" data-name="ltraffic" data-type="from">
					<div class="other-filter-input__clear hidden"></div>
				</div>
				<div class="price-filter-input__box ml10">
					<input title="" class="other-filter-input" placeholder="{"До "|t}" value="" autocomplete="off"
							type="number" data-name="ltraffic" data-type="to">
					<div class="other-filter-input__clear hidden"></div>
				</div>
				<input type="hidden" name="ltraffic" value="">
			</div>
		</div>

		<div class="popup-filter__group expandable {if $linksLanguages["count"] < 2 && !$linksLanguages["selectLanguage"]}hidden{/if}">
			<h3 class="popup-filter__group-title m-visible">{'Язык площадок'|t}: <span>
			{foreach from=$linksLanguages["langArr"] key=code item=text}
				{if $linksLanguages["selectLanguage"] eq $code}
					{$text|t}
				{/if}
			{/foreach}
			</span>
				<div class="kwork-icon icon-down-arrow"></div>
			</h3>
			<div class="card__content-column">
				<div class="card__content-header">
					<strong>{'Язык площадок'|t}</strong>
				</div>
				<div class="card__content-body">
					<a href="javascript: void(0);" class="filter-clear">{'Сбросить'|t}</a>
					{foreach from=$linksLanguages["langArr"] key=code item=text name=llanguage}
						{if $linksLanguages[$code]}
							<div {if !$smarty.foreach.llanguage.first}class="m-mt10"{/if} >
								<input name="llanguage" class="js-kwork-filter-input styled-radio"
										id="llanguage_{$code}" type="radio" value="{$code}"
										{if $linksLanguages["selectLanguage"] eq $code} checked="checked" {/if}>
								<label for="llanguage_{$code}">{$text|t}</label>
							</div>
						{/if}
					{/foreach}
				</div>
			</div>
		</div>
	</div>
{/strip}