{strip}
	{if !$selected}
		{$selected = 0}
	{/if}
	{if $disabled}
		{if !is_array($disabled)}
			{$disabled = [$disabled]}
		{/if}
	{else}
		{$disabled = []}
	{/if}
	{if !isset($classes) || !is_array($classes)}
		{$classes = []}
	{/if}

	<select
		{if $id} id="{$id}"{/if}
		{if $class} class="{$class}"{/if}
		{if $name} name="{$name}"{/if}
	>
		<option value=""{if !$selected} selected="selected"{/if} disabled="disabled">Срок</option>
		{foreach from=$durations item=duration}
			<option value="{$duration}"
				{if isset($classes[$duration])} class="{$classes[$duration]}"{/if}
				{if in_array($duration, $disabled)} disabled="disabled"{/if}
				{if $duration == $selected} selected="selected"{/if}
			>
			{if $duration === 45}
				1.5 месяца
			{elseif ($duration >= 30 && $duration % 15 === 0)}
				{Helper::getCountWithUnit($duration / 30, ["месяц", "месяца", "месяцев"])}
			{elseif ($duration === 14 || $duration === 21)}
				{$duration / 7} недели
			{else}
				{Helper::getCountWithUnit($duration, ["день", "дня", "дней"])}
			{/if}
			</option>
		{/foreach}
	</select>
{/strip}