{foreach from=$translateLangsKwork item=lang name=foo}
	<div class="kwrok-tranlation-language">
		{"с %s на %s"|t:$lang.from_lang:$lang.to_lang}
	</div>
{/foreach}