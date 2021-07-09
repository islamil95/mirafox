{foreach $reeanList as $reason}
	<div class="wrap_reason pb10 {if $reason['item']['id'] == ModerManager::CLASSFIELD_REASON_ID}class-field-reason{/if}">
		{if $reason["item"]["name_user"] || $reason["item"]["description"]}
			<span class="tooltipster"
				data-tooltip-side="left"
				data-tooltip-text='{if $reason["item"]["name_user"]}{$reason["item"]["name_user"]|bbcode|nl2br}<br>{/if}{$reason["item"]["description"]|bbcode|nl2br}'
				data-tooltip-theme="dark">
				<input type="checkbox" name="reasons[]" value="{$reason['item']['id']}" {if in_array($reason['item']['id'], $selectedReasons)}checked{/if} class="topreason reasons-input styled-checkbox" id="reason_{$reason['item']['id']}" />
				<label for="reason_{$reason['item']['id']}" style="position:relative;">
					{$reason['item']['name']}
				</label>
			</span>
		{else}
			<input type="checkbox" name="reasons[]" value="{$reason['item']['id']}" {if in_array($reason['item']['id'], $selectedReasons)}checked{/if} class="topreason reasons-input styled-checkbox" id="reason_{$reason['item']['id']}" />
			<label for="reason_{$reason['item']['id']}" style="position:relative;">
				{$reason['item']['name']}
			</label>
		{/if}

		{if !empty($reason['childs'])}
			<div class="sub_reasons hidden">
				{foreach $reason['childs'] as $subReason}
					<div style="position:relative;">
						{if $subReason["name_user"] || $subReason["description"]}
							<span class="tooltipster"
								data-tooltip-side="left"
								data-tooltip-text='{if $subReason["name_user"]}{$subReason["name_user"]|bbcode|nl2br}<br>{/if}{$subReason["description"]|bbcode|nl2br}'
								data-tooltip-theme="dark">
								<input type="checkbox" name="sub_reasons[{$reason['item']['id']}][]" value="{$subReason['id']}" {if !empty($selectedSubReasons[$reason['item']['id']]) && in_array($subReason['id'], $selectedSubReasons[$reason['item']['id']])}checked{/if} class="styled-checkbox" id="sub_reason_{$subReason['id']}" />
								<label for="sub_reason_{$subReason['id']}">
									{$subReason['name']}
								</label>
							</span>
						{else}
							<input type="checkbox" name="sub_reasons[{$reason['item']['id']}][]" value="{$subReason['id']}" {if !empty($selectedSubReasons[$reason['item']['id']]) && in_array($subReason['id'], $selectedSubReasons[$reason['item']['id']])}checked{/if} class="styled-checkbox" id="sub_reason_{$subReason['id']}" />
							<label for="sub_reason_{$subReason['id']}">
								{$subReason['name']}
							</label>
						{/if}
					</div>
				{/foreach}
				<div class="sub_reason_error"></div>
			</div>
		{/if}
		
		{if in_array($reason['item']['id'], ModerManager::$categoryRids)}
			<div class="sub_reasons hidden">
			</div>
			<input type="hidden" name="sub_reasons[{$reason['item']['id']}]" id="h_set_package_category_error" value="" />
			<div class="sub_reason_error package_category_error"></div>
		{/if}

		{if $reason['item']['id'] == ModerManager::CLASSFIELD_REASON_ID}
			<div class="sub_reasons classfield-block"></div>
		{/if}
	</div>
{/foreach}