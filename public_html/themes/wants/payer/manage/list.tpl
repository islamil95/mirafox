{extends file="page_with_user_header.tpl"}
{block name="content"}
	{Helper::printCssFile("/css/dist/manage-projects.css"|cdnBaseUrl)}
	{Helper::printJsFile("/js/dist/manage-projects.js"|cdnBaseUrl)}
	<div class="centerwrap clearfix pt20 m-pt100 manage-projects-page">

		<div class="project-title-wrapper{if $archivedCount>0} project-title-wrapper_theme_archive{/if}">
			<h1 class="f32 m-text-center">
				{'Мои проекты'|t}
			</h1>
			<a href="{absolute_url route="new_project"}"
			   class="pull-right green-btn v-align-m btn-hide-sm mt-1 projects-add-button js-sms-verification-action{if $status != "archived" && $wantsCount == 0} hidden{/if}">
                {'Создать задание'|t}
			</a>
		</div>

		{if $wants->count() > 0 || $archivedCount > 0}
			{if $archivedCount > 0}
				<ul class="b-tab order-types-menu mb20">
					<li class="b-tab_item {if $status != "archived"}active{/if}">
						<a href="{$baseurl}/manage_projects">
							<span class="descr">{'Актуальные'|t}</span>
                            {if $wantsCount > 0}
								<span class="b-tab_item_number m-hidden">{$wantsCount}</span>
								<span class="b-tab_item_number-m m-visible">({$wantsCount})</span>
							{/if}
						</a>
					</li>
					<li class="b-tab_item {if $status == "archived"}active{/if}">
						<a href="{$baseurl}/manage_projects?status=archived">
							<span class="descr">{'Архив'|t}</span>
							<span class="b-tab_item_number m-hidden">{$archivedCount}</span>
							<span class="b-tab_item_number-m m-visible">({$archivedCount})</span>
						</a>
					</li>
				</ul>
			{/if}
			<div class="projects-list">
                {if $status == "archived" || ($status != "archived" && $wantsCount > 0)}
				<table class="table-style projects-list__table">
					<thead>
					<tr>
						<th>{'Проект'|t}</th>
						<th>{'Цена до'|t}</th>
						<th>{'Предложения'|t}</th>
						<th>{'Заказы'|t}</th>
						{if $status != "archived"}
							<th class="projects-list__table-th-mobile">{'Статус'|t}</th>
						{/if}
						<th class="projects-list__table-th-mobile">{'Управлять'|t}</th>
					</tr>
					</thead>
					<tbody>
					{foreach $wants as $want}
						{include file="wants/payer/manage/want_item.tpl"}
					{/foreach}
					</tbody>
				</table>
				{/if}
                {if $wants->count() > 4}
					<div class="m-hidden {if $wants->links()|strlen > 1}projects-list__button_position_absolute{else}projects-list__button_position_relative{/if}">
						<a href="{absolute_url route="new_project"}"
						   class="pull-right green-btn v-align-m btn-hide-sm projects-add-button js-sms-verification-action">
                            {'Создать задание'|t}
						</a>
					</div>
                {/if}
			</div>
			{insert name=paging_block assign=pages value=a data=$pagingdata}
            {if $wants->links()}
				<div class="mb30">
                    {$wants->links()}
				</div>
            {/if}
			<div{if $status == "archived" || ($status != "archived" && $wantsCount > 0)} class="m-visible"{/if}>
                {include file="wants/payer/manage/create_want_small.tpl"}
			</div>
		{else}
			{include file="wants/payer/manage/create_want_block.tpl"}
		{/if}
	</div>
	{if $wantStatus}{literal}
	<script>
		jQuery(function () {
			showWantStatusPopup('{/literal}{$wantStatus}{literal}');
		});
	</script>
	{/literal}{/if}
{/block}