{strip}
<div id="classifications_moder_list">
	{foreach from=$classifications item=classification}
		{if $classification->getParentId() && $classification->getChildren()}
			<hr class="gray">
			<div class="clearfix">
				{assign var=isChangedClassification value=$patchData && !in_array($classification->getId(),$patchData.attributesIds) }
				<span class="{if $isChangedClassification}patch-outline__yellow{/if}">{$classification->getTitle()}</span>
				<div class="pull-right font-OpenSans f12 t-align-r classification-more-info"
					 style="width: 80%">
					{foreach name=attributes from=$classification->getChildren() item=attribute}
						{assign var=isChangedAttribute value=$patchData && !in_array($attribute->getId(),$patchData.attributesIds) }
						<span class="{if $isChangedAttribute}patch-outline__yellow{/if}">{$attribute->getTitle()}</span>
						{if !$smarty.foreach.attributes.last}, {/if}
					{/foreach}
				</div>
				<hr class="gray">
			</div>
		{/if}
	{/foreach}
</div>
{/strip}