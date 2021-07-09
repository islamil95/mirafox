<div id="sort-type-{$selectName}" class="js-sort-by-block sort-by_block {if $hide}hidden{/if}">
	<div class="select-style-original none-style">
		<span class="js-sort-by-title sort-by_title">{$values[$activeValue]}</span>
		<select class="js-sort-by" name="{$selectName}">
			{foreach $values as $k => $v}
				<option class="sort-option" value="{$k}" {if $activeValue == $k}selected{/if}>{$v}</option>
			{/foreach}
		</select>
	</div>
</div>