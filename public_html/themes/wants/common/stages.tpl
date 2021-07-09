
<div class="offer-individual__label mb0 fw600">{'Задачи'|t}
	{if $page === 'track'}
		<span class="tooltipster kwork-icon icon-custom-help" data-tooltip-content=".offer-individual__label-tooltip-stages"></span>
		<div class="hidden">
			<div class="offer-individual__label-tooltip-stages">
				<p>{'В названии задачи отразите: что, когда и в каком объеме будет выполнено.'|t}</p>
			</div>
		</div>
	{else}
		<span class="tooltipster kwork-icon icon-custom-help" data-tooltip-content=".offer-individual__label-tooltip-stages"></span>
		<div class="hidden">
			<div class="offer-individual__label-tooltip-stages">
				<p class="fw700">{'Задачи'|t}</p>
				<p class="mb10">{'Если заказ объемный и длительный, разделите его на задачи. Каждая задача включает в себя задание, которое выполняется и оплачивается отдельно.'|t}</p>
				<p class="fw700">{'Название задачи'|t}</p>
				<p>{'В названии задачи отразите: что, когда и в каком объеме будет выполнено.'|t}</p>
			</div>
		</div>
	{/if}
</div>

<div class="js-stages offer-individual__stages"></div>
<div class="offer-individual__bottom">
	<a href="javascript:;" class="js-stage-add disable offer-individual__add-stage">
		<img src="{"/plus.png"|cdnImageUrl}" width="18" height="18" class="mr5" alt="">
		{'Добавить задачу'|t}
	</a>
	<div class="js-stage-total-price-wrap offer-individual__total-price">
		<div class="js-stage-total-price offer-individual__total">
			{'Итого цена'|t}:
			<strong class="js-offer-total-price"></strong>
		</div>

		{if $actorType === UserManager::TYPE_PAYER}
			<div class="js-stage-total-price-add-payer-block hidden offer-individual__price-add-payer">
				<span class="js-stage-total-price-add-payer offer-individual__price-add-payer-value">+0</span>
				<span class="tooltipster kwork-icon icon-custom-help" data-tooltip-text=""></span>
			</div>
		{/if}

		{if $actorType === UserManager::TYPE_WORKER}
		<div class="js-stage-total-price-commission offer-individual__price-desc hidden"></div>
		{/if}
	</div>
	<div class="js-stage-total-price-error offer-individual__total-price-error"></div>
</div>
<div class="js-stage-limit hidden offer-individual__stages-limit">
	{'Вы также сможете добавить задачи после старта заказа.'|t}
</div>

{if $actorType === UserManager::TYPE_PAYER}
	<div class="js-stages-duration stages-duration">
		<div class="stages-duration-current">
			<div class="offer-sprite offer-sprite-clock m-hidden"></div>
			<div>
				<strong>{'Общий срок заказа:'|t}</strong>&nbsp;<span class="js-stages-duration-value"></span>
			</div>
		</div>
		<div class="js-stages-duration-change stages-duration-change hidden">
			<label for="stages-duration-change" class="stages-duration-change__label"></label>
			<div class="stages-duration-change__select chosen14">
				<select id="stages-duration-change" class="js-stages-duration-change-select input input_size_s" name="days" data-direction="auto"></select>
			</div>
		</div>
		<div class="js-stages-duration-change-error stages-duration-change__error"></div>
	</div>
{/if}

<div class="js-stage-default">
{include file="wants/common/stage.tpl"}
</div>
