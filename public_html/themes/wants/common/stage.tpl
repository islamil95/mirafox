<div class="offer-individual__stage{if $spellcheck} vf-block{/if}" data-error-output=".js-stage-name" data-id="" style="display: none;">
	<div class="offer-individual__stage-info">
		<div class="offer-individual__stage-number">
			<span class="js-stage-number"></span>
			<select class="js-stage-number-select select-styled select-styled--thin hidden" data-target="number"></select>
		</div>
		<div class="offer-individual__stage-name">
			{if $spellcheck}
				<div contenteditable="true" type="text" value="" name="" data-target="title" spellcheck="false" class="js-content-editor vf-field single-line single-line-input control-en styled-input input hidden contenteditable-single-line" data-field-id="1" data-no-hint="1" data-placeholder="{'Название задачи'|t}" data-max="80"></div>
			{/if}
			<input type="text" value="" name="" data-target="title" class="js-stage-name control-en styled-input input hidden{if $spellcheck} js-content-storage{/if}" placeholder="{'Название задачи'|t}" maxlength="80">
			<div class="js-stage-name-text offer-individual__stage-text"></div>
		</div>
		<div class="offer-individual__stage-price">
			<div class="offer-individual__stage-price-currency usd">$</div>

			<input type="text"
				   value=""
				   data-target="payer_price"
				   name=""
				   placeholder=""
				   class="js-stage-price styled-input input hidden lh20">
			<div class="js-stage-price-text offer-individual__stage-text offer-individual__stage-text--price"></div>

			<div class="offer-individual__stage-price-currency rouble">Р</div>

		</div>
		<div class="stage-controls">
			<span class="js-stage-save stage-controls__save tooltip hidden" data-tooltip-position="left" data-tooltip-text="{'Сохранить задачу'|t}"><i class="kwork-icon icon-check-mark"></i></span>
			<span class="js-stage-edit stage-controls__edit tooltip" data-tooltip-position="left" data-tooltip-text="{'Изменить задачу'|t}"><i class="kwork-icon icon-pencil"></i></span>
			<span class="js-stage-delete stage-controls__delete tooltip hidden" data-tooltip-position="right" data-tooltip-text="{'Удалить задачу'|t}"><i class="kwork-icon icon-close"></i></span>
		</div>
	</div>
	<div class="js-stage-error offer-individual__stage-error">
		<span data-target="number"></span><span{if $spellcheck} class="vf-error"{/if} data-target="title"></span><span class="mt5" data-target="payer_price"></span>
	</div>
</div>
