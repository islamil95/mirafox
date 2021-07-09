{extends file="page_with_user_header.tpl"}

{block name="styles"}
	{Helper::printCssFile("/css/dist/projects.css"|cdnBaseUrl)}
	{Helper::registerFooterCssFile("/css/bootstrap.modal.css"|cdnBaseUrl)}
{/block}
{Helper::registerFooterJsFile("/js/bootstrap.modal.min.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/dist/projects.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/chosen.jquery.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/projects.js"|cdnBaseUrl)}

{if $actor && !$actor->isVirtual}
	{Helper::registerFooterJsFile("/js/wants.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/libs/withinviewport.js"|cdnBaseUrl)}
	{Helper::registerFooterJsFile("/js/libs/jquery.withinviewport.min.js"|cdnBaseUrl)}
{/if}

{block name="content"}
	<div class="centerwrap page-projects">

		<div class="page-projects__header">
			<h1 class="f32">{'Биржа проектов'|t}</h1>
		</div>

		{if $canAddOfferStatus !== true}
			{if $canAddOfferStatus == OfferManager::CANNOT_ADD_DESCRIPTION}
				{assign var="text" value={'Напишете несколько предложений о себе в Профиле.'|t}}
			{elseif $canAddOfferStatus == OfferManager::CANNOT_ADD_AVATAR}
				{assign var="text" value={'Загрузите аватар в Профиле.'|t}}
			{else}
				{assign var="text" value={'Загрузите аватар и напишите несколько предложений в поле "Информация о продавце".'|t}}
			{/if}
			<div class="js-warning-profile projects-info-block color-red" data-text-popup='<p class="mb5">{'Чтобы повысить доверие покупателей к вам и получать больше заказов, заполните информацию в Профиле.'|t}</p><p class="mb5">{$text}</p><p>{'Только после этого вы сможете предлагать услуги на Бирже.'|t}</p>'>
				<span class="block-circle block-circle-40 block-circle_red fs20 lh40 bold white">!</span>
				<span class="">{'Заполните профиль, чтобы предлагать услуги на Бирже.'|t} <a href="javascript:;" class="js-link-popup-warning-profile">{'Подробнее...'|t}</a></span>
			</div>
			<div class="clear"></div>
		{/if}

		<div class="wants-filter-content">
			
			<div class="wants-content">
				<div class="project-list js-project-list" data-preloader-class="preloader__ico_projects"
					 data-preloader-opacity="true">
					{include file="wants/worker/wants_list.tpl"}
				</div>
			</div>
		</div>

		<div class="js-popup-change-my-categories popup--fixed change-my-categories hidden">
			<div class="overlay overlay-disabled"></div>

			<div class="popup_content popup-w600">

				<h2 class="popup__title pr20">{'Какие услуги вы оказываете клиентам?'|t}</h2>
				<hr class="gray">
				<div class="js-popup-close pull-right popup-close cur">X</div>
				<div class="change-my-categories__form">
					<div class="change-my-categories__text"><strong>{'Выберите до 7 рубрик, чтобы быстро выводить нужные проекты на бирже и быть в курсе новых проектов'|t}</strong></div>
					<ul>
						{foreach $favouriteCategories as $category}
							<li class="js-my-category-item" data-category-id="{$category->category_id}"><span>{$category->name}</span><a href="javascript:;" class="js-link-delete-categories" title="{'Удалить'|t}"><i class="fa fa-times color-red"></i></a></li>
						{/foreach}
					</ul>
					<div class="js-result-delete-categories change-my-categories__result-delete"></div>

					{foreach $categoriesWithFavourite as $category}
						<div class="change-my-categories__item">
							<div class="js-link-change-categories change-my-categories__item-title {if $category->has_favourite}change-my-categories__item-title-open{/if}" data-category-id="{$category->id}">{$category->name}</div>
							<div data-category-id="{$category->id}" class="js-block-change-categories change-my-categories__item-sub" {if !$category->has_favourite}style="display: none;"{/if}>
								{if $category->cats}
									{foreach from=$category->cats item=$subcat name=category}
										{if $smarty.foreach.category.index % 2 == 1}
											{$side = 'right'}
										{else}
											{$side = 'left'}
										{/if}
										<div class="change-my-categories__item-input">
											<input class="js-change-categories" type="checkbox" id="change-categories-{$subcat->id}" data-category-id="{$subcat->id}" value="{$subcat->id}" {if $subcat->is_favourite}checked="checked"{/if}>
											<label class="tooltipster" data-tooltip-side="{$side}" data-tooltip-text="{'Можно выбрать не более 7 любимых рубрик'|t}" for="change-categories-{$subcat->id}">{$subcat->name}</label><br>
										</div>
									{/foreach}
								{/if}
							</div>
						</div>
					{/foreach}

					<div class="t-align-c">
						<a class="js-save-my-categories form-button green-btn btn--big m-wMax w250" href="javascript:;">{'Готово'|t}</a>
					</div>
				</div>
			</div>
		</div>

		<div class="js-popup-delete-categories delete-categories hidden" data-category-id="">
			<div class="overlay overlay-disabled"></div>
			<div class="popup_content">
				<h2 class="popup__title f18 pr20">{('Удалить рубрику из любимых?'|t)}</h2>
				<hr class="gray">
				<div class="js-popup-close pull-right popup-close cur">X</div>
				<div class="overflow-hidden">
					<button class="js-delete-my-categories popup__button red-btn">{'Удалить'|t}</button>
					<button class="popup__button white-btn js-popup-close pull-right">{'Отменить'|t}</button>
				</div>
			</div>
		</div>

	</div>
{/block}
