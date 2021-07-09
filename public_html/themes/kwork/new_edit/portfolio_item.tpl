{strip}
	<div class="js-portfolio-item-wrapper add-photo__file-wrapper file-wrapper long-touch-js hidden {if $portfolio}js-existed-portfolio-item{/if}" data-position="{$position}">
		<input type="hidden" name="portfolio[{$position}][id]" value="{$portfolio.id}">
		<input type="hidden" name="portfolio[{$position}][position]" value="{$position}">
		<input type="hidden" name="portfolio[{$position}][delete]" class="js-portfolio-item-is-for-delete">

		<div class="file-wrapper-block-container js-file-wrapper-block-container">
			<div class="file-wrapper-block-rectangle" {if $portfolio && count($portfolio.images)}style="background: url('{$portfolio.images[0]->getUrlCatalog()}/t3/{$portfolio.images[0]->path}') 0% 0% / 100% no-repeat;"{elseif $portfolio && $portfolio.video}style="background: url('/images/play2.png') center center no-repeat;" data-youtube="{$portfolio.video}"{/if}></div>
		</div>
		<div style="text-align: center; height: 25px; padding-top: 5px;">
			<span class="js-portfolio-add-button button f14 font-OpenSans link-color dibi mt6i" data-init-text="{'Загрузить'|t}" data-edit-text="{'Изменить'|t}">
				{if $portfolio}{'Изменить'|t}{else}{'Загрузить'|t}{/if}
			</span>
			<div class="dib js-portfolio-delete {if !$portfolio}hidden{/if}">
				&nbsp;&nbsp;&nbsp;<span class="f14 font-OpenSans link-color">{'Удалить'|t}</span>
			</div>
		</div>

		{* modal-portfolio *}
		{$portfolioForm->renderForm("kwork", $kwork.id, $position, $kwork.category.portfolio_type, $order->portfolio)}

	</div>
{/strip}