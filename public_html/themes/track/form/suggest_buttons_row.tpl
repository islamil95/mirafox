<div style="background-color:#f0f0f0;" class="suggest-extras-wrapper">
	{if $order->getBetterPackages()}
		<span data-suggestion="package-level" class="pt5 pb5 pull-left ml10 cur noselect" onclick="suggestOptionsToggle($(this));" style="{if $order->isBetterPackageExtraSuggested()}display: none;{/if}">
			<img src="{"/plus.png"|cdnImageUrl}" width="16" height="16" class="icon rounded-circle v-align-m mr5 active" id="show" alt="" />
			<img src="{"/cancel_2_orange.png"|cdnImageUrl}" width="16" height="16" class="icon rounded-circle v-align-m mr5" id="hide" style="display:none;" alt="" />
			<span>{if !$config.track.isFocusGroupMember}{'Предложить повысить уровень пакета'|t}{else}{'Предложить повысить пакет'|t}{/if}</span>
		</span>
	{/if}
	<span data-suggestion="options" class="pt5 pb5 pull-right mr10 cur noselect" onclick="suggestOptionsToggle($(this));">
		<img src="{"/plus.png"|cdnImageUrl}" width="16" height="16" class="icon rounded-circle v-align-m mr5 active" id="show" alt="" /><img src="{"/cancel_2_orange.png"|cdnImageUrl}" width="16" height="16" class="icon rounded-circle v-align-m mr5" id="hide" style="display:none;" alt="" />
		<span>{'Предложить опции'|t}</span>
	</span>
	<div class="clearfix"></div>
</div>