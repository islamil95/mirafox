{$firstReservedStage = $order->getNotReservedStages()->first()->id}
{$logRecordsStage = $logRecords[$stage->id]}
{*выделяем первую предстоящую задачу для покупателя, если нет активных задач*}
{* выделяем цветом задачу "на проверке" *}
<div class="js-track-stage js-tooltip-update track-stage
{if isAllowToUser($order->USERID) && !$order->hasReservedStages() && $firstReservedStage == $stage->id && $order->isCanStagesReserved()} track-stage--highlight{/if}
{if isAllowToUser($order->USERID) && $stage->isNotReserved() && $order->isCanStagesReserved()} js-track-stage-edit-link track-stage--edit{/if}
{if isAllowToUser($order->USERID) && $stage->isCheck() && !$hideButtonsStage} track-stage--highlight{/if}
{if isAllowToUser($order->worker_id) && $logRecordsStage && $logRecordsStage['unread']} track-stage--highlight track-stage--changed{/if}
{if isAllowToUser($order->worker_id) && $logRecordsStage['action'] == Model\OrderStages\ChangeLog::ACTION_ADD && $logRecordsStage['unread']} js-track-changed-new{/if}
{if isAllowToUser($order->worker_id) && $logRecordsStage['action'] == Model\OrderStages\ChangeLog::ACTION_DELETE && $logRecordsStage['unread']} js-track-stage-deleted track-stage--deleted{/if}"
	 data-stage-id="{$stage->id}"
	 data-order-id="{$order->OID}"
	 data-payer-price="{$stage->payer_price}"
	 data-is-check="{if isAllowToUser($order->USERID) && $stage->isCheck()}0{else}1{/if}"
>
<div class="track-stage__container">
	<div class="track-stage__icon-edit">
		<i class="kwork-icon icon-pencil"></i>
	</div>
	<div class="track-stage__number">
	{* для удаленной задачи не показываем номер задачи *}
	{if $logRecordsStage['action'] != Model\OrderStages\ChangeLog::ACTION_DELETE}
		<div class="js-stage-number">{$stage->number}</div>
		{if $logRecordsStage['action'] == Model\OrderStages\ChangeLog::ACTION_EDIT && $logRecordsStage['updated']|count === 1 && in_array(\Model\OrderStages\OrderStage::FIELD_NUMBER, $logRecordsStage['updated'])}
			<div class="track-stage__changed tooltipster" data-tooltip-text="{'Изменена последовательность задачи.'|t} {$logRecordsStage['date']}">
				{'ред.'|t}
			</div>
		{/if}
	{/if}
	</div>
	<div class="track-stage__title">
		<div class="js-stage-name track-stage__name">{$stage->title}</div>

		{if $stage->isReserved()}
			<div class="track-stage__progress-bar">

				{include file="track/view/progress_bar.tpl" progress=$stage->progress disableSelect=true}

				<div class="track-stage__progress-bar-value"><span class="js-stage-progress">{$stage->progress}</span>%</div>

				{if $stage->isReserved()
					&& isAllowToUser($order->worker_id)
					&& $order->in_work
					&& $order->isInprogressForWorker()
					&& !$stage->isCheck()
					&& !$checkboxInprogressCheck}
					<div class="track-stage__progress-bar-link">
						<span class="js-track-stage-change-progress-link link_local">{'Изменить'|t}</span>
					</div>
				{/if}

				<span class="tooltipster kwork-icon icon-custom-help" data-tooltip-content=".track-stages-progress-bar-tooltip"></span>
			</div>
		{/if}

		{if $stage->paid_date}
			<div class="track-stage__date-end">{$stage->paid_date|date:"j M, H:i"}</div>
		{/if}

		{if $logRecordsStage['action'] == Model\OrderStages\ChangeLog::ACTION_EDIT && $logRecordsStage['updated']|count === 1 && in_array(\Model\OrderStages\OrderStage::FIELD_TITLE, $logRecordsStage['updated'])}
		<div class="track-stage__changed tooltipster" data-tooltip-text="{'Изменено название задачи.'|t} {$logRecordsStage['date']}">
			{'ред.'|t}
		</div>
		{/if}
		{if $logRecordsStage['action'] == Model\OrderStages\ChangeLog::ACTION_EDIT && $logRecordsStage['updated']|count > 1}
			{$tooltipText = ''}
			{if in_array(\Model\OrderStages\OrderStage::FIELD_NUMBER, $logRecordsStage['updated'])}
				{$tooltipText = $tooltipText|cat:'последовательность, '}
			{/if}
			{if in_array(\Model\OrderStages\OrderStage::FIELD_TITLE, $logRecordsStage['updated'])}
				{$tooltipText = $tooltipText|cat:'название, '}
			{/if}
			{if in_array(\Model\OrderStages\OrderStage::FIELD_PAYER_AMOUNT, $logRecordsStage['updated'])}
				{$tooltipText = $tooltipText|cat:'стоимость '}
			{/if}
		<div class="track-stage__changed tooltipster" data-tooltip-text="Изменены {$tooltipText|regex_replace:'/, $/':''} задачи. {$logRecordsStage['date']}">
			{'ред.'|t}
		</div>
		{/if}
		{if isAllowToUser($order->worker_id) && $logRecordsStage['action'] == Model\OrderStages\ChangeLog::ACTION_ADD && $logRecordsStage['unread']}
			<div class="track-stage__changed">
				{'новая'|t}
			</div>
		{/if}
		{if isAllowToUser($order->worker_id) && $logRecordsStage['action'] == Model\OrderStages\ChangeLog::ACTION_DELETE && $logRecordsStage['unread']}
			<div class="track-stage__changed">
				{'удал.'|t}
			</div>
		{/if}
	</div>
	<div class="track-stage__status-price">
		<div class="track-stage__price">
			{if isAllowToUser($order->USERID)}
				{$price = $stage->payer_price}
			{else}
				{$price = $stage->worker_price}
			{/if}

			{include file="utils/currency.tpl" total=$price currencyId=$order->currency_id lang=''}

			{if $logRecordsStage['action'] == Model\OrderStages\ChangeLog::ACTION_EDIT && $logRecordsStage['updated']|count === 1 && in_array(\Model\OrderStages\OrderStage::FIELD_PAYER_AMOUNT, $logRecordsStage['updated'])}
				<div class="track-stage__changed tooltipster" data-tooltip-text="{'Изменена стоимость задачи.'|t}  {$logRecordsStage['date']}">
					{'ред.'|t}
				</div>
			{/if}
		</div>
		<div class="track-stage__status">
			{if $stage->isNotReserved()}
				<span class="color-gray">{'Не поступала'|t}</span>

				{if isAllowToUser($order->USERID)}
					{* подсказка для первого предстоящей задачи, если нет активных задач*}
					{if !$order->hasReservedStages() && $firstReservedStage == $stage->id}
						{$tooltipText = {'Зарезервируйте оплату по задаче, чтобы продавец продолжил работу по заказу.'|t}}
					{else}
						{$tooltipText = {'Внесите оплату заранее, чтобы ускорить выполнение заказа. Продавец приступит к новой задаче сразу после завершения предыдущей, а в некоторых случаях сможет работать над несколькими задачами параллельно.'|t}}
					{/if}
				{else}
					{* подсказка для первого предстоящей задачи, если нет активных задач*}
					{if !$order->hasReservedStages() && $firstReservedStage == $stage->id}
						{$tooltipText = {'Дождитесь, когда покупатель внесет оплату следующей задачи. Только после этого продолжайте работу над заказом.'|t}}
					{else}
						{$tooltipText = {'Покупатель еще не зарезервировал деньги под задачу. Не начинайте работу над этой задачей.'|t}}
					{/if}
				{/if}
			{elseif $stage->isReserved()}
				<span class="check-mark-green"></span><span class="colorGreen">{'В резерве'|t}</span>

				{if isAllowToUser($order->USERID)}
					{$tooltipText = {'Вы внесли оплату под эту задачу. Продавец может работать над ней. После завершения работы над задачей деньги будут переведены продавцу.'|t}}
				{else}
					{$tooltipText = {'Покупатель отправил деньги Kwork. Можно работать над задачей. По его завершении и проверке покупателем, деньги будут переведены на ваш баланс.'|t}}
				{/if}
			{elseif $stage->isPaid()}
				<span class="check-mark-green-double"></span><span class="colorGreen">{'Переведена'|t}</span>

				{if isAllowToUser($order->USERID)}
					{$tooltipText = {'Деньги переведены на баланс продавца.'|t}}
				{else}
					{$tooltipText = {'Деньги переведены на ваш баланс.'|t}}
				{/if}
			{elseif $stage->isRefund()}
				<span class="color-gray">{'Возвращена'|t}</span>

				{if isAllowToUser($order->USERID)}
					{$tooltipText = {'Задача отменена. Деньги возвращены вам.'|t}}
				{else}
					{$tooltipText = {'Задача отменена. Деньги возвращены покупателю.'|t}}
				{/if}
			{/if}

			{if !isAllowToUser($order->USERID)}
				{$tooltipText = "<p>{$tooltipText}</p><p>{'Указана чистая оплата за минусом комиссии Kwork, то есть та, которая поступит на ваш баланс при выполнении проекта.'|t}</p>"}
			{/if}
			<span class="tooltipster kwork-icon icon-custom-help" data-tooltip-text="{$tooltipText}"></span>
		</div>
	</div>
	{if
		(!$hideButtonsStage && isAllowToUser($order->USERID) && $stage->isNotReserved() && $order->isCanStagesReserved() && !$order->hasReservedStages() && $firstReservedStage == $stage->id)
		|| (!$hideButtonsStage && isAllowToUser($order->USERID) && $stage->isReserved() && $stage->isCheck())
		|| (!$hideButtonsStage && isAllowToUser($order->worker_id) && !$showOnlyButtonPay && $stage->isReserved() && !$order->in_work && $order->isInprogressForWorker() && !$stage->isCheck())
	}
		{$addClass = 'track-stage__status-order--button'}
	{/if}
	<div class="track-stage__status-order {$addClass}">
	{if $checkboxInprogressCheck}
		{* чекбокс отправки задачи на проверку *}
		<input id="stage-inprogress-check-checkbox-{$stage->id}"
			   class="js-stage-inprogress-check-checkbox styled-checkbox" type="checkbox" value="{$stage->id}">
		<label for="stage-inprogress-check-checkbox-{$stage->id}">&nbsp;</label>
	{elseif !$hideButtonsStage}
		{* в попапе подтверждения задач не нужен этот блок *}
		{if $stage->isNotReserved()}
			{if isAllowToUser($order->USERID) && $order->isCanStagesReserved()}
				<form class="js-reserve-stage-form"
					  action="{absolute_url route="reserve_stage" params=["orderStageId" => $stage->id]}">
					{if !$order->hasReservedStages() && $firstReservedStage == $stage->id}
						<button class="js-reserve-stage-link js-stages-button green-btn"
								type="button">{'Требуется оплата'|t}</button>
					{else}
						<a href="javascript:;"
						   class="js-reserve-stage-link js-stages-button status-order-text link-color">{'Зарезервировать оплату'|t}</a>
					{/if}
				</form>
			{elseif !isAllowToUser($order->USERID) && !$order->hasReservedStages() && $firstReservedStage == $stage->id}
					<span class="status-order-text">{'Ожидается оплата'|t}</span>
			{/if}
		{/if}
		{if isAllowToUser($order->USERID) && $stage->isReserved() && $stage->isCheck()}
			<button class="js-stage-inprogress-confirm-link-top js-stages-button green-btn"
					data-stage-id="{$stage->id}">{'Подтвердить'|t}</button>
		{/if}
		{if !$showOnlyButtonPay && $stage->isReserved() && isAllowToUser($order->worker_id) && !$order->in_work && $order->isInprogressForWorker() && !$stage->isCheck()}
			<button class="green-btn" onclick="js_sendInWork();" type="submit">{'Взять в работу'|t}</button>
		{/if}
		{if is_array($stagesInArbitrageIds) && in_array($stage->id, $stagesInArbitrageIds)}
			{if $lastArbitrageTrack->user_id == $order->USERID}
				{if isAllowToUser($order->USERID)}
					{$tooltipTextArbitration={'Вы перевели задачу в Арбитраж'|t}}
				{else}
					{$tooltipTextArbitration={'Задача передана в Арбитраж'|t}}
				{/if}
			{elseif $lastArbitrageTrack->user_id == $order->worker_id}
				{if isAllowToUser($order->worker_id)}
					{$tooltipTextArbitration={'Вы перевели задачу в Арбитраж'|t}}
				{else}
					{$tooltipTextArbitration={'Задача передана в Арбитраж'|t}}
				{/if}
			{else}
				{$tooltipTextArbitration={'Задача передана в Арбитраж'|t}}
			{/if}

			<div class="status-order-text">{'В арбитраже'|t}
				<span class="tooltipster kwork-icon icon-custom-help" data-tooltip-text="{$tooltipTextArbitration}"></span>
			</div>
		{/if}
	{/if}
	</div>
</div>
</div>
