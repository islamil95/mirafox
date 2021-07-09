{* Список пакетных услуг *}
{strip}
	{foreach $uniquePackageItemIds as $uniqueItem}
		{assign var="item" value=$package->items[$uniqueItem->packageItemType][$uniqueItem->pi_id]}
		{if $item->value > 0 || $item->value|strlen}
			<div class="package_card_vert_option">
				<div class="package_card_vert_option__title ml6">
					<div class="package_card_vert_option__icon">
						<img src="{"/greengalka.png"|cdnImageUrl}" width="11" alt="" style="margin-left: -3px">
					</div>

       				{if $item->type == 'int' && $item->value|is_numeric}
                    	{assign var=formattedValue value=$item->value|zero}
					{else}
                        {assign var=formattedValue value=$item->value}
                    {/if}

                    {if $item->can_lower && $item->value > 1 && $uniqueItem->packageItemType == 'custom'}
                        {assign var=boldValue value="до "|cat:$formattedValue}
                    {else}
                        {assign var=boldValue value=$formattedValue}
					{/if}

					{insert name=declension value=a assign=name count=$item->value form1=$item->name_1 form2=$item->name_2 form3=$item->name_5}
					&nbsp;
					{if $item->type == 'int'}
						{$name|replace:"[VALUE]":$boldValue}
						{if $uniqueItem->packageItemType == 'custom'}: {$boldValue}{/if}
					{elseif $item->type == 'label'}
						{$item->name}
					{else}
						{$item->name}: {$item->value}
					{/if}
				</div>
			</div>
		{/if}
	{/foreach}

	{foreach $uniquePackageItemIds as $uniqueItem}
		{assign var="item" value=$package->items[$uniqueItem->packageItemType][$uniqueItem->pi_id]}
		{if !($item->value > 0 || $item->value|strlen) && $uniqueItem->required}
			<div class="package_card_vert_option">
				<div class="package_card_vert_option__title ml6 inactive">
					<div class="package_card_vert_option__icon">
						<img src="{"/greengalka-disabled.png"|cdnImageUrl}" width="11" alt="" style="margin-left: -3px">
					</div>
					&nbsp;{$uniqueItem->name}
				</div>
			</div>
		{/if}
	{/foreach}
{/strip}