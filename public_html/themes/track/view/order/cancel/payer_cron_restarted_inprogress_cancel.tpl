{extends file="track/view/system.tpl"}

{block name="additionalMessage"}
{if $isNeedProjectLink}
{strip}
		<div class="f15 mt10">
			<b>
				{'Разместите'|t} <a href="{absolute_url route="manage_projects"}">{'новый заказ на бирже'|t}</a>.
			</b> {'Десятки исполнителей будут готовы выполнить ваше задание.'|t}
		</div>
{/strip}
{/if}
{/block}