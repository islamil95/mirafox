{include file="header.tpl"}
{strip}
	{Helper::registerFooterJsFile("/js/mainfox.js"|cdnBaseUrl)}

    {Helper::registerFooterJsFile("/js/bootstrap.min.js"|cdnBaseUrl)}
    {Helper::registerFooterJsFile("/js/bootstrap.modal.min.js"|cdnBaseUrl)}

	{Helper::registerFooterJsFile("/js/dist/kwork-manage.js"|cdnBaseUrl)}

    {Helper::printCssFile("/css/bootstrap.modal.css"|cdnBaseUrl)}
    {Helper::printCssFile("/css/dist/kwork-manage.css"|cdnBaseUrl)}

	{include file="fox_error7.tpl"}
	<div class="m-hidden">
		{control name="user_top" USERID=$actor->id uname=$actor->username desc="level" fullname=$actor->fullname live_date=$actor->live_date rating=$actor->cache_rating profilepicture=$actor->profilepicture cover=$actor->cover contentType="newKwork"}
	</div>
	<div class="centerwrap pt20">
		{include file="manage_kworks/head.tpl"}

		{* Пока показываем только админам *}
		{if $actor && $actor->role == "admin"}
			{if !$isKworkBookInfoClosed}
				{include file='kwork/kwork_book.tpl'}
			{/if}
		{/if}
		<div class="clearfix"></div>
		{if $total eq "0"}
			{include file="manage_kworks/list_empty.tpl"}
		{else}
			{include file="manage_kworks/list.tpl"}
		{/if}
		<div class="clear"></div>
		<div class="t-align-c">
			{insert name=paging_block assign=pages value=a data=$pagingdata}
			{$pages}
			<div class="clear"></div>
			<div class="mt20"></div>
		</div>
	</div>
{/strip}
{include file="popup/kwork_change_name.tpl"}
{include file="footer.tpl"}
