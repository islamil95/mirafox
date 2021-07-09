<div class="{if $config.track.isFocusGroupMember} track--item {/if} tr-track step-block-order_item pt7i">
    {if $config.track.isFocusGroupMember}
		<div class="track--item__sidebar">
			<div class="track--item__sidebar-image {$missingColor}">
				<svg width="25" height="25" viewBox="0 0 25 25">
					<use xlink:href="#{$missingIcon}"></use>
				</svg>
			</div>
		</div>
    {/if}
    {if $config.track.isFocusGroupMember}
	<div class="track--item__main">
        {else}
		<div class="t-align-c">
            {/if}
            {if $config.track.isFocusGroupMember}
				<div class="track--item__title">
					<h3 class="f15 bold">
                        {$title}
					</h3>
				</div>
            {else}
				<i class="{$missingIcon}"></i>
				<h3 class="track-{$missingColor} pt10 fw600 mb10">
                    {$missingTitle}
				</h3>
            {/if}
            {if $config.track.isFocusGroupMember}
			<div class="track--item__content">
                {/if}
				<div class="send-instruction-block {if in_array($order->source_type, [\OrderManager::SOURCE_WANT_PRIVATE, \OrderManager::SOURCE_INBOX_PRIVATE])}send-instruction-block__individual{/if}">
					<div class="d-flex align-items-center">
                        {if !in_array($order->source_type, [\OrderManager::SOURCE_WANT_PRIVATE, \OrderManager::SOURCE_INBOX_PRIVATE])}
							<div class="block-circle block-circle-40 block-circle_orange bold fs20 lh40 white">!</div>
							<div class="bold ml10 f15 send-instruction-block_title">{$missingText}</div>
                        {/if}
					</div>
					<div class="ml50 f15">
						<div class="js-send-instruction-block-text send-instruction-block__text">
                            {$order->kwork->ginst|stripslashes|html_entity_decode:$smarty.const.ENT_QUOTES:'utf-8'}
						</div>
					</div>
				</div>

				<div class="safe-container" data-name="send-instruction">
					<div id="send-instruction-modal" class="send-instruction">
						<send-instruction-modal></send-instruction-modal>
						<div class="send-instruction__button">
							<button class="js-send-instruction-link btn-track green-btn btn-flex">
                                {'Отправить информацию продавцу'|t}
							</button>
						</div>
					</div>
				</div>
                {if $config.track.isFocusGroupMember}
			</div>
            {/if}
		</div>
	</div>
