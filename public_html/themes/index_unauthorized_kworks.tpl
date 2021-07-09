{strip}
	<div class="centerwrap m-visible">
		<div class="clearfix mt20 mb10">
			<a href="{$baseurl}/categories" class="pull-right mt3">{'Смотреть все'|t} <i class="fa fa-caret-right"></i></a>
			<h2 class="f18  pull-left">{'Топовые рубрики'|t}</h2>
		</div>
		<div class="category-tree category-tree_index">
			<div class="category">
				<a href="{$baseurl}/{$catalog}/design" class="color-text db">
					<i class="icon ico-design v-align-m"></i>
					<span class="dib w75p ml12 v-align-m">
					<span class="bold f18 dib">{'Дизайн'|t}</span><br>
					<span class="f14 mt3 dib lh16 color-gray">{'Логотипы, веб-дизайн, визитки'|t}</span>
				</span>
				</a>
			</div>
			<div class="category">
				<a href="{$baseurl}/{$catalog}/programming" class="color-text db">
					<i class="icon ico-code v-align-m"></i>
					<span class="dib w75p ml12 v-align-m">
					<span class="bold f18 dib">{'Разработка и IT'|t}</span><br>
					<span class="f14 mt3 dib lh16 color-gray">{'Доработка, сайт под ключ'|t}</span>
				</span>
				</a>
			</div>
			{if App::isMirror()}
				<div class="category">
					<a href="{$baseurl}/{$catalog}/promotion" class="color-text db">
						<i class="icon ico-marketing v-align-m ico"></i>
						<span class="dib w75p ml12 v-align-m">
							<span class="bold f18 dib">{'Маркетинг и реклама'|t}</span><br>
							<span class="f14 mt3 dib lh16 color-gray">{'Реклама, продвижение, PR'|t}</span>
						</span>
					</a>
				</div>
			{else}
				<div class="category">
					<a href="{$baseurl}/{$catalog}/seo" class="color-text db">
						<i class="ico-seo v-align-m icon"></i>
						<span class="dib w75p ml12 v-align-m">
							<span class="bold f18 dib">{'SEO и трафик'|t}</span><br>
							<span class="f14 mt3 dib lh16 color-gray">{'Аудиты, ссылки, трафик'|t}</span>
						</span>
					</a>
				</div>
			{/if}
		</div>
		<div class="mt40 m-hidden"></div>
		<div class="mt20 m-visible"></div>
		<h2 class="f26 t-align-c  m-f18">{'Популярные кворки'|t}</h2>
	</div>
	<div class="lg-centerwrap centerwrap main-wrap m-m0">
		{if $posts|count GT 0}
			<div class="cusongs">
				<div class="cusongslist cusongslist_5_column pb0">
					{include file="fox_bit.tpl"}
					<div class="clear"></div>
				</div>
				<div class="index-page_more-button" style="text-align:center;">
					<button onclick='loadKworks(true);' class='loadKworks'>{'Показать еще'|t}</button>
				</div>
				<div class="auth-form_placeholder"></div>
				<div class="clear"></div>
			</div>
		{/if}
		<div class="clear"></div>
	</div>
{/strip}