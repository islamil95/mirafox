{extends file="page_with_user_header.tpl"}

{block name="content"}
	<div class="lg-centerwrap centerwrap pt20">
		<h1 class="semibold f32">{'Биржа проектов'|t}</h1>
		{include file="wants/worker/offers/list.tpl"}
	</div>
{/block}