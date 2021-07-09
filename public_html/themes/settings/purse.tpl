<div class="mt10">
	<label class="editgigformtitle" for="{$type}_purse">{$purseName}</label>
	<div class="mti3 block-state-active clearfix" style="background: none;">
		<i class="icon {$iconClass} pull-left"></i>
		<div class="settings-solar-card-info{if empty($number)} active{/if}" data-type="{$type}" data-system="{$withdrawSystem}">
			<div class="settings-solar-input">
				<input class="text {$onlyNumberClass} f16 js-payments-settings-input js-{$type}-input{if !$canChangePurse} read-o{/if}" {if !$canChangePurse} readonly disabled{/if}
					   id="{$type}_purse"
					   name="{$type}_purse"
					   type="text"
					   value="{$secureNumber|stripslashes}"
					   autocomplete="off"
					   placeholder="{$hint}"
				>
			</div>
			<div class="color-green">{$secureNumber|stripslashes}</div>
			<div class="settings-solar-links">
				<a href="javascript:;" onclick="editSolar(this);">{'Изменить'|t}</a>
				<a href="javascript:;" onclick="removeSolar('{$type}')" class="link-color ml15">{'Удалить'|t}</a>
			</div>
			<span id="{$type}_purse-error" class="hidden color-red js-{$type}-error-field"></span>
		</div>
		{if $canChangePurse}
			<div class="block-state-active_tooltip block-help-image-js" style="right: -275px; top: -35px;">
				<p class="bold">{'Внимание!'|t}</p>
				<p>{"В целях безопасности при добавлении нового или изменении номера кошелька, вы не сможете отправлять заявку на вывод в течение 1 недели."|t}</p>
			</div>
		{else}
			<div class="block-state-active_tooltip block-help-image-js" style="right: -275px; top: -35px;">
				<p class="bold">{'Внимание!'|t}</p>
				<p>{'В целях безопасности после смены данных для входа в систему изменение реквизитов вывода будет доступно только'|t}</p>
				<p>
					<strong>{$dateCanChangeAllPurse|date}</strong></p>
			</div>
		{/if}
	</div>
</div>