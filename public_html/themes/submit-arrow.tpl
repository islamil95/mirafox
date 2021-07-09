<div {if $buttonId}id="{$buttonId}"{/if} class="disabled btn-send-message message-submit-button submit-arrow box-submit{if !$oldMode} blue-mode{/if}{if $hasTooltip} tooltipster"{if !$isChat} data-tooltip-mhidden="true" data-tooltip-side="right"{/if} data-tooltip-content="#message-send-switch-tooltip" data-tooltip-theme="light{/if}">
	<button type="submit" class="cur">
		{if !$oldMode}
			<i class="fl-send-arrow"></i>
		{else}
			<svg xmlns="http://www.w3.org/2000/svg" width="26" height="22" viewBox="0 0 63 54">
				<path fill="#090" fill-rule="nonzero" d="M0 54l63-27L0 0v21l45 6-45 6z"/>
			</svg>
		{/if}
	</button>
</div>
