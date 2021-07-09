{strip}
	<div class="d-none">
		<div class="{$class}">
			<div class="small-tooltip">
				{if $title}
					<div class="title">
						{$title|t}
					</div>
				{/if}
				<div class="desc">
					{if $level == 1}
						<span>{'Сделан как минимум 1 заказ.'|t} </span>
					{elseif $level == 2 || $badge == 14}
						<span>{'Сделано не менее %s покупок.'|t:7} </span>
					{elseif $level == 3 || $badge == 15}
						<span>{'Сделано более %s покупок.'|t:20} </span>
					{elseif $level == 4 || $badge == 16}
						<span>{'Сделано более %s покупок.'|t:50} </span>
					{elseif $level == 5 || $badge == 17}
						<span>{'Сделано более %s покупок.'|t:100} </span>
					{/if}
					{if $super || $badge == 18}
						{if $badge && $badge != 18}
							<br /><br />
							<div class="title">
								Суперпокупатель
							</div>
						{/if}
						<span>{'Сделал не менее %s покупок за последние %s месяца.'|t:12:3} </span>
					{/if}
				</div>
			</div>
		</div>
	</div>
{/strip}