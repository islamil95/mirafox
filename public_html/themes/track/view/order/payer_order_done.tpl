{extends file="track/view/system.tpl"}

{block name="additionalMessage"}
	<div class="f15 {if !$config.track.isFocusGroupMember} mt15{/if}">
		{"Оставьте, пожалуйста, свой отзыв о работе продавца. От отзывов зависит, как высоко будут отображаться кворки продавца в каталоге и поиске."|t}
	</div>
	{* TODO 6699 тестовая кнопка разрешения портфолио *}
	{include file="track/view/portfolio/portfolio_allow_button.tpl"}
{/block}