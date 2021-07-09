{strip}
	<div class="pull-right m-pull-reset mb10">
		<div class="dib v-align-m mr10">{'Показать'|t}</div>
		<select class="dib v-align-m count-order-in-page-js mw150px select-styled select-styled--thin" data-page="{$s}" style="width: auto;">
			<option value="10" {if $page_limit == 10}selected{/if}>10</option>
			<option value="20" {if $page_limit == 20}selected{/if}>20</option>
			<option value="50" {if $page_limit == 50}selected{/if}>50</option>
		</select>
	</div>
{/strip}
