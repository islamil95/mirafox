{extends file="page_with_user_header.tpl"}

{block name="styles"}
	{Helper::printCssFile('/css/dist/projects.css')}
	{Helper::registerFooterCssFile("/css/bootstrap.modal.css")}
{/block}
{Helper::registerFooterJsFile('/js/bootstrap.modal.min.js')}
{Helper::registerFooterJsFile("/js/dist/projects.js")}
{Helper::registerFooterJsFile("/js/chosen.jquery.js")}
{Helper::registerFooterJsFile('/js/projects.js')}

{if $actor && !$actor->isVirtual}
	{Helper::registerFooterJsFile("/js/wants.js")}
	{Helper::registerFooterJsFile("/js/libs/withinviewport.js")}
	{Helper::registerFooterJsFile("/js/libs/jquery.withinviewport.min.js")}
{/if}

{block name="content"}
	<div class="centerwrap page-projects">
		<div class="page-projects__header">
			<h1 class="f32">Проекты покупателя</h1>
			{include file="wants/worker/wants_connect_info.tpl"}
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
					{include file="wants/worker/wants_payer_list.tpl"}
				</div>
			</div>
		</div>
	</div>
{/block}
