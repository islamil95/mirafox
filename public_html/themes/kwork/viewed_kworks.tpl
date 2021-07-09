{include file="header.tpl"}
{strip}
	<div class="bodybg pt20 foxbookmarks">
		<div class="lg-centerwrap centerwrap m-margin-reset">
			<div class="cusongs">
				<h1 class="f32">{'Просмотренные кворки'|t}</h1>
				{if $kworks}
					<div class="cusongslist cusongslist_4_column viewed-kworks c4c">
						{include file="fox_bit.tpl" posts=$kworks	}
						<div class="clear"></div>
					</div>
					{*Здесь была пагинация, она не используется*}
				{/if}
				<div class="clear"></div>
				<div class="clear" style="padding-bottom:20px;"></div>
			</div>
		</div>
	</div>
{/strip}
{include file="footer.tpl"}