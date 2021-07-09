{extends file="track/view/system.tpl"}

{block name="message"}
	<div class="f15 mt15 js-order-done">
		<div class="f15 mt15">
			{$title}
			{if $isDoneConvAllow}
				{"Вы можете продолжить общение с покупателем на %sстранице диалога%s или перейти к %sсвоим заказам%s"|t:$conversationUrl:"</a>":"<a href=\"/manage_orders\">":"</a>"}
			{/if}
		</div>
		{$text}
		{if $disablePortfolio}
			<br><br>
			{"К сожалению, вы не можете загрузить результат данного заказа в портфолио,%sпоскольку покупатель не согласился на открытое размещение материалов данного заказа"|t:"<br>"}
		{/if}
	</div>
{/block}

{block name="additionalMessage"}
	<div class="f15 mt15">
	</div>
{/block}