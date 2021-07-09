{strip}
<div id="track-id-{$track->MID}"
	 class="{if $config.track.isFocusGroupMember} track--item {/if} tr-track step-block-order_item{if $isUnread} unread{/if}{if $track->getHide()} hide{/if} {$direction}"
	 data-track-id="{$track->MID}">
    {if $config.track.isFocusGroupMember}
		<div class="track--item__sidebar">
			<div class="track--item__sidebar-image {$color}">
				<svg width="25" height="25" viewBox="0 0 25 25">
					<use xlink:href="#{$icon}"></use>
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
					<h3 class="f15 bold lh-n">
                        {$title}
					</h3>
				</div>
            {else}
				<i class="{$icon}"></i>
				<h3 class="track-{$color} pt10 font-OpenSansSemi lh25">{$title}</h3>
            {/if}
            {if $config.track.isFocusGroupMember}
			<div class="track--item__content">
                {/if}
				<div class="card card_secondary track-item__info-block mt20">
					<div class="card__content lh22">
						<p class="mb10">
                            {'Закажите аналогичный кворк у 2-5 разных продавцов. Это даст:'|t}
						</p>
						<ul class="ml20">
							<li>
								<b>{'Качество.'|t}</b> {'Возможность выбрать лучшую работу и лучшего продавца из нескольких вариантов'|t}
							</li>
							<li>
								<b>{'Время.'|t}</b> {'Подстраховка на случай отказа продавца от заказа или выбора медленного продавца'|t}
							</li>
						</ul>
					</div>
				</div>
				<div class="mt10  {if $config.track.isFocusGroupMember} t-align-c {/if}">
                    {'<a href="/categories/%s">Перейти в раздел <b>%s</b></a><br>для заказа аналогичных кворков'|t:$order->kwork->kworkCategory->seo:{$order->kwork->kworkCategory->name|t}}
				</div>
                {if $config.track.isFocusGroupMember}
			</div>
            {/if}
		</div>
	</div>
    {/strip}
