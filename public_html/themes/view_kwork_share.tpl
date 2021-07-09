{strip}
	<div class="gray-bg-border p15-20 mt20 t-align-c page-more-kwork-share" data-counters="no">
		<div class="ta-center">
			{if $actor}
				<div>{'Поделись партнерской ссылкой на кворк'|t}</div>
			{else}
				<div>{'Расскажите друзьям об этом кворке'|t}</div>
			{/if}
			{include file="social-widget.tpl"}
			{if $actor}
				<input class='wMax styled-input mt10 ta-center' readonly value="{$baseurl}?ref={$actor->id}" />
			{/if}
		</div>
	</div>
{/strip}