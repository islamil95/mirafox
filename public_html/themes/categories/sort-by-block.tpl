<div id="sort-type" class="sort-by_block">
	<label>{$title}</label>
	<div class="select-style-original none-style">
		<span class="sort-by_title">{$values[$activeValue]}</span>
		<select class="js-sort-by" name="{$selectName}">
			{foreach $values as $k => $v}
				<option class="sort-option" value="{$k}" {if $activeValue == $k}selected{/if}>{$v}</option>
			{/foreach}
		</select>
	</div>
</div>