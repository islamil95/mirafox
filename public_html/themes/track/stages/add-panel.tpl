{strip}
{if !$order->has_stages && isAllowToUser($order->USERID) && $order->isStagesEditable()}

{$reservedStages = $order->getReservedStages()}
{$notReservedStages = $order->getNotReservedStages()}

{* Максимальная общая стоимость предстоящих задач *}
{$customMaxPrice = \KworkManager::getCustomMaxPrice($order->getLang()) - $reservedStages->sum("payer_price") - $order->getPaidStages()->sum("payer_price")}
{* Минимальная стоимость задачи *}
{$stageMinPrice = $order->getStageMinPrice()}
{* Минимальная общая стоимость при добавлении задач *}
{$customMinPriceAdd = $order->getMinPrice()}
{* Максимальная общая стоимость при добавлении задач *}
{$customMaxPriceAdd = $customMaxPrice - $order->price}

	<div class="track-add-panel-stage pt10 pb10">
		<div class="track-add-panel-stage__inner">
			<div class="d-flex align-items-center">
				<div class="block-circle block-circle-24 block-circle_orange bold fs16 lh24 white">!</div>
				<div class="ml10 f13 lh20 v">
					<span class="bold">{'Хотите докупить услуги продавца или работать с ним на регулярной основе?'|t}</span> <span>{'Добавьте задачи в заказ'|t} <span class="tooltipster kwork-icon icon-custom-help" data-tooltip-width="500" data-tooltip-content=".track-add-panel-stage-tooltip"></span></span>
				</div>
			</div>
			<div class="track-add-panel-stage__wrap-btn">
				<a href="javascript:;" class="js-track-stage-add-link track-stages__add track-stages__add_small"
					 data-stages=''
					 data-order-id="{$order->OID}"
					 data-lang="{$order->getLang()}"
					 data-stage-min-price="{$stageMinPrice}"
					 data-custom-min-price="{$customMinPriceAdd}"
					 data-custom-max-price="{$customMaxPriceAdd}"
					 data-price="0"
					 data-duration='{$order->duration}'
					 data-initial-duration='{if $order->initial_duration}{$order->initial_duration}{else}{$order->duration}{/if}'
					 data-stages-max-increase-days="{$order->kwork->kworkCategory->max_days}"
					 data-stages-max-decrease-days="0"
					 data-control-en-lang="{$order->getLang() == Translations::EN_LANG}"
					 data-count-stages="{count($order->stages)}">
					<img src="{"/plus.png"|cdnImageUrl}" width="18" height="18" class="mr5" alt="">
					{'Добавить задачу'|t}
				</a>
			</div>
		</div>
		<div class="hidden">
			<div class="track-add-panel-stage-tooltip">
				<p>{'Добавлять задачи удобно в двух случаях:'|t}</p>
				<ol>
					<li>
						<p><strong>{'Вы хотите заказать доп. услуги у фрилансера'|t}</strong></p>
						<p>{'Например, вы заказали сайт, и пока над ним шла работа, захотели добавить на него форум. Вы договорились с исполнителем о цене и сроке.'|t}</p>
						<p>{'Теперь нажмите кнопку «Добавить задачу» и укажите ее название и стоимость. Тогда задача установки форума будет добавлена в заказ.'|t}</p>
					</li>
					<li>
						<p><strong>{'Вы хотите заказать услугу на длительный срок'|t}</strong></p>
						<p>{'Например, вы договорились о том, чтобы копирайтер каждую неделю добавлял на ваш блог по 3 новых статьи и еженедельно получал бы за это оплату.'|t}</p>
						<p>{'Для этого нажмите «Добавить задачу» и добавьте задач на несколько недель вперед с оговоренной стоимостью. Вам останется только периодически пополнять баланс и отмечать задачи выполненными, чтобы оплата переводилась фрилансеру.'|t}</p>
					</li>
				</ol>
				<p>{'Каждая задача выполняется и оплачивается отдельно.'|t}</p>
			</div>
		</div>
		{include file="track/view/stages/modal_edit_stages.tpl"}
	</div>
{/if}
{/strip}