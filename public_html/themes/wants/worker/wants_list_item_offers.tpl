{extends file="wants/worker/wants_list_item.tpl"}
{* Шаблон используемый на странице "Ваши предложения" - в нем кнопка открытия своего предложения и само предложение*}
{block name="button"}
	<button class="green-btn projects-offer-btn js-show-offer">
		{'Мое предложение'|t}
	</button>
{/block}
