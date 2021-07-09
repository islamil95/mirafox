{strip}
	{* Добраляем данные конфига, если форма рендерится отдельно, для обновления через ajax *}
	{if $isFormRender}
		{include file="config/header.tpl"}
	{/if}

	<script>
		var cancelReasons = {$cancelReasons|json_encode};
		var options = {$options|json_encode};
		var optionPrices = {$optionPrices|json_encode};
		{if !empty($similarOrderData) && !empty($similarOrderData["similarOrderInfo"])}
			var similarOrderData = {$similarOrderData|json_encode};
		{/if}
	</script>

	{* Запоминаем id собеседника *}
	{if isAllowToUser($order->USERID)}
		{assign var="otherUserId" value=$order->worker_id}
	{else}
		{assign var="otherUserId" value=$order->USERID}
	{/if}

	{insert name=is_online assign=is_online value=a userid=$otherUserId}

	{* Форма для отправки информации покупателем (маскируется под карточку трека) *}
	{if $isMissingData}
		{include file="track/view/payer/missing_data.tpl"}
	{/if}

	{assign "type_form" "not-specified"}

	{* Блок для продавца с кнопками "Написать сообщение" / "Приступаю к работе" и подсказкой о необходимости взять заказ в работу. Показывается у заказов, которые ещё не взяты в работу *}
	{if isAllowToUser($order->worker_id) && $order->isInProgress() && $order->isNotInWork() && !$inCancelRequest}
		{include file="./worker_get_to_work.tpl"}
	{/if}

	{assign "hideForm" 0}
	{if isAllowToUser($order->worker_id) && $order->isInProgress() && $order->isNotInWork() && !$inCancelRequest}
		{assign "hideForm" 1}
	{/if}

	{* Если внутри .js-comment-box есть что показывать *}
	{if $canWriteMessage || $canWriteReview || $canSendPortfolio || $canReOrder || ($allowedCancel && $cancelReasons|@count)}
    	{$reservedStages = $order->getReservedStages()}
    	{$notReservedStages = $order->getNotReservedStages()}

		{* форма сообщения и кнопок *}
		<a class="message-form-anchor"></a>
		{* js-comment-box *}
		<div class="p15-20 white-bg-border noBorderTop js-comment-box mf-comment-box{if $extrasPanelVisible} with-extras-panel{/if}{if $canWriteMessage && !$canWriteReview} is-message{/if}{if $canWriteReview} is-review{/if}" {if $hideForm || (!$canWriteMessage && !$canReOrder && !$canWriteReview && (!$canSendPortfolio || $order->portfolio)) || ($isMissingData)}style="display:none;"{/if}>

			{if $canWriteMessage}
				{assign "type_form" "only-message"}
				{if isAllowToUser($order->worker_id) && $order->isInProgressForWorker() && $order->isInWork()}
					{assign "showWriteMessageButton" true}
					{if !($order->has_stages && $order->getReservedNotCheckStages()->count() == 0)}
						{assign "type_form" "pass-work"}
					{/if}
				{elseif $order->isDone()}
					{assign "type_form" "status-done"}
				{/if}
			{/if}

			{* Основная форма, отправка сообщения и всякие сопутствующие действия *}
			{if $canWriteMessage}

				{* Кнопки, которые показываются когда форма скрыта
				* Написать сообщение | Сдать выполненную работу
				* Заказать ещё | Связаться с продавцом *}
				{include file="./hidden_form_buttons.tpl"}

				<div class="clear"></div>

				{* Панель управления редактированием сообщения *}
				<div class="message-edit-panel hidden">
					<span class="message-edit-panel__status"><i class="kwork-icon icon-pencil"></i>{'Редактировать'|t} <span class="secont-word">{'сообщение'|t}</span></span>
				</div>

				{if isAllowToUser($order->USERID)}
					{assign var="otherUserName" value=$order->worker->username}
				{else}
					{assign var="otherUserName" value=$order->payer->username}
				{/if}

				{* Форма для сообщения *}
				{include file="./message_form.tpl"}

			{* Когда заказ закончен и сообщения отправлять нельзя *}
			{elseif $canReOrder}
				{include file="./reorder_buttons.tpl"}
			{/if}

			{* Форма для оставления отзыва покупателем *}
			{if $canWriteReview}
				{include file="./review_send.tpl"}
			{/if}

			{* Форма для загрузки работы портфолио продавцом *}
			{if $canSendPortfolio && !$order->portfolio}
				{include file="./portfolio_upload.tpl"}
			{/if}
		</div>

		{* Ссылки под формой *}
		{include file="./bottom_action_links.tpl"}
	{/if}
{/strip}

<script>
	var typeForm = '{if isset($type_form)}{$type_form}{else}empty{/if}';
	TrackUtils.startShow(typeForm);
</script>


{if $order->has_stages}
    {* Попап подтверждения задач как выполненых *}
    {include file="track/popup/stage_inprogress_confirm.tpl" jsClass='js-stage-inprogress-confirm-modal'  stages=$order->getReservedStages()}
    {* Попапы отправки задач на проверку *}
    {include file="track/popup/stage_inprogress_check.tpl"}
{else}
    {* Тело попапа о подтверждении заказа как выполненого покупателем из состяний "в работе" и "арбитраж" *}
    {include file="track/popup/confirm_inprogress_done.tpl"}
    {* Попап подтверждения заказа как выполненного *}
    {include file="track/popup/inprogress_confirm.tpl"}
{/if}

{* Bootstrap modal *}
{Helper::registerFooterCssFile("/css/bootstrap.modal.css"|cdnBaseUrl)}

{Helper::registerFooterJsFile("/js/bootstrap.min.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/bootstrap.modal.min.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/components/youtube-thumbnail.js"|cdnBaseUrl)}
