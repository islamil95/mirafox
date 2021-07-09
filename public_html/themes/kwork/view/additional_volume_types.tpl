{strip}
	<div class="additional_volume_types">
		<select name="additional_volume_type_id" class="js-additional-volume-types select-styled select-styled--thin long-touch-js f15 dib w80i ml5" data-base-time="{if $volumeType.contains_value}{$volumeType.contains_value}{else}1{/if}">
			<option value="{$volumeType.id}" selected data-additional-time="0">
				{$volumeType.name_short}
			</option>
			{foreach $additionalVolumeTypes as $additionalVolumeType}
				{if $additionalVolumeType.id != $volumeType.id}
					<option value="{$additionalVolumeType.id}" data-additional-time="{if $additionalVolumeType.contains_value > 0}{$additionalVolumeType.contains_value}{else}1{/if}">
						{$additionalVolumeType.name_short}
					</option>
				{/if}
			{/foreach}
		</select>
	</div>
{/strip}