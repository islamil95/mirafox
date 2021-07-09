{include file="header.tpl"}
{strip}
	{block name="scripts"}{/block}
	{block name="styles"}{/block}
	{include file="fox_error7.tpl"}
	{* @TODO: Костыль по умолчанию на мобильных устройствах шапка не видна*}
{*	{if $actor}*}
{*		<div class="{$userTopClass|default:"m-hidden"}">*}
{*			{include file="user_top.tpl" USERID=$actor->id uname=$actor->username desc={$userDescriptionType|default:"type"} fullname=$actor->fullname live_date=$actor->live_date rating=$actor->cache_rating profilepicture=$actor->profilepicture cover=$actor->cover}*}
{*		</div>*}
{*	{/if}*}
	{block name="content"}{/block}
{/strip}
{include file="footer.tpl"}
