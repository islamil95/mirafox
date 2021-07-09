{strip}
<select class="btn outline-only white" onchange="javascript:location.href = this.value;">
	<option value="">{'Подкатегории'|t}</option>
		{foreach item=cat from=$scats name=list}

			<option value="{$baseurl}/{$catalog}/{$cat.seo|lower|stripslashes}">{$cat.name|stripslashes}</option>
		{/foreach}
</select>
{/strip}