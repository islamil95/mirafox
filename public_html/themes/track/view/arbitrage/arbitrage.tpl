{* арбитраж *}
{extends file="track/view/arbitrage/base.tpl"}

{block name="mainContent"}
	<div class="mt15">
		{if ($track->type == "admin_arbitrage_done")}
	<div class="mt15 f15 bold t-align-c">
			{if ($actor->type == "payer")}
				{'Заказ выполнен. Оплата за заказ переведена продавцу.'|t}
			{elseif ($actor->type == "worker")}
				{'Заказ выполнен. Оплата за заказ переведена на ваш баланс.'|t}
			{/if}
	</div>
		{elseif ($track->type == "admin_arbitrage_cancel")}
	<div class="mt15 f15 bold t-align-c">
			{if ($actor->type == "payer")}
				{'Заказ отменен. Средства возвращены на ваш баланс.'|t}
			{elseif ($actor->type == "worker")}
				{'Заказ отменен. Средства возвращены покупателю.'|t}
			{/if}
	</div>
		{elseif ($track->type == "admin_arbitrage_done_half")}
	<div class="mt15 f15 bold t-align-c">
			{if ($actor->type == "payer")}
				{'Заказ выполнен частично. По договоренности сторон часть оплаты переведена продавцу, а другая - возвращена на ваш баланс.'|t}
			{elseif ($actor->type == "worker")}
				{'Заказ выполнен частично. По договоренности сторон часть оплаты переведена на ваш баланс, а другая - возвращена покупателю.'|t}
			{/if}
	</div>
		{elseif ($track->type == "admin_arbitrage_stage_continue")}
			<div class="mt15 f15 bold t-align-c">
				{'Заказ может быть продолжен.'|t}
			</div>
			<div class="fw700 mt15 mb5">{'Статус оплаты задач по итогам арбитража:'|t}</div>
			{include file="track/view/stages/arbitrage_track_stages.tpl"}
		{elseif ($track->type == "admin_arbitrage_stage_cancel")}
			<div class="mt15 f15 bold t-align-c">
				{'Заказ отменен.'|t}
			</div>
			<div class="fw700 mt15 mb5">{'Статус оплаты задач по итогам арбитража:'|t}</div>
			{include file="track/view/stages/arbitrage_track_stages.tpl"}
		{elseif ($track->type == "admin_arbitrage_stage_done")}
			<div class="mt15 f15 bold t-align-c">
				{'Заказ выполнен.'|t}
			</div>
			<div class="fw700 mt15 mb5">{'Статус оплаты задач по итогам арбитража:'|t}</div>
			{include file="track/view/stages/arbitrage_track_stages.tpl"}
		{elseif (!is_null($article))}
			<div class="article">
				<div class="question d-flex align-items-center justify-content-start">
					<div class="attention">
						<i class="ico-info"></i>
					</div>					
					<div>{$article->question}</div>
				</div>
				<div class="answer">
					<div class="fr-view">{$article->answer_formatted|unescape:'html' nofilter}</div>
					{if (!is_null($article->rules))}
						<p><b>{'Правила Kwork для данной ситуации'|t}</b></p>
						<div class="fr-view">{$article->rules->answer_formatted|unescape:'html' nofilter}</div>
					{/if}
					{if ($article->arbitrage_task != "")}
						<p><b>{'Задача арбитража'|t}</b></p>
						<div class="fr-view">{$article->arbitrage_task|unescape:'html' nofilter}</div>
					{/if}
				</div>
			</div>
		{/if}
	</div>
	<hr class="gray mb-3">
	<div class="{if (!is_null($article))}t-align-l fw700{else}t-align-center{/if}">
	{if $track->type == "payer_check_arbitrage" || $track->type == 'payer_inprogress_arbitrage'}
		{if isAllowToUser($order->USERID)}
			{'Ваши аргументы'|t}:
		{else}
			{'Аргументы покупателя'|t}:
		{/if}
	{elseif $track->type == "worker_check_arbitrage" || $track->type == "worker_inprogress_arbitrage"}
		{if isAllowToUser($order->worker_id)}
			{'Ваши аргументы'|t}:
		{else}
			{'Аргументы продавца'|t}:
		{/if}
	{/if}
	</div>
	<div class="f15  {if !$config.track.isFocusGroupMember} mt15 {/if} t-align-l">
		{$track->message|bbcode|stripslashes|nl2br|htmlspecialchars_decode}
	</div>
	{if $track->files->isNotEmpty()}
		<div class="mt15">
		{foreach from=$track->files key=k item=file}
				<div id="id{$k}" class="mt5 file-item t-align-l mb10 {if (is_null($article))}ml80 m-ml0{/if}">
				{insert name=get_file_ico value=a assign=ico filename=$file->fname}
				<a href="{absolute_url route="file_download" params=["filePath" => $file->s,"fileName" => $file->fname]}"
				   target="_blank" class="color-text{if $retention_period_notice_count >= 0} js-popup-file{/if}">
					<i class="ico-file-{$ico} dib v-align-m"></i>
					<span class="dib v-align-m ml10 mw80p">{$file->fname}</span>
				</a>
			</div>
		{/foreach}
		</div>
	{/if}
	{if ($track->type == "payer_check_arbitrage" ||  $track->type == "payer_inprogress_arbitrage" || $track->type == "worker_check_arbitrage" || $track->type == "worker_inprogress_arbitrage")}
		<div class="f15 text-orange t-align-l  {if !$config.track.isFocusGroupMember} mt15 ml80 mr80 m-ml0 m-mr0 {/if}">
			<hr class="gray">
			<img src="{"/warning-circle.png"|cdnImageUrl}"
				 style="margin-bottom: -5px;" alt=""/><span
					style="margin-left: 10px">{if isNotAllowUser($track->user_id)}{'Пожалуйста, как можно скорее опишите свою позицию по спору, укажите свои аргументы, предоставьте доказательства.'|t}{else}{'Арбитраж в работе. Ожидайте решения.'|t}{/if}</span>
		</div>
	{/if}

{/block}