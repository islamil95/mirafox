<div class="mt10">
	<label class="editgigformtitle"
		   for="pass">{'Банковская карта'|t}</label>
	<div class="mti3 block-state-active clearfix"
		 style="background: none;">
		<i class="icon ico-mastercardVisa pull-left"></i>
		<div class="settings-solar-card-info">
            {if $p.card_id == '' || $p.card_number == ''}
                {if $canChangePurse}
                    {if time() > strtotime($p.next_card_link_available)}
						<a href="javascript:void(0);"
						   class="js-solar-card-verify f16">{'Привязать карту'|t}</a>
						{if $withdrawSystem !== User\UserWithdrawSystemManager::SOLAR}
							{$tooltip = 'Вы можете получать заработанные средства сразу на вашу карту. Вывод средств осуществляется платежными системами Solar Staff и Paymore.org, обеспечивающими высокий уровень безопасности платежей. Чтобы выводить средства на карту, необходимо выполнить одноразовую привязку карты к аккаунту. Карты России и стран СНГ привязываются как рублевые карты. Если валюта вашей карты отлична от рубля, она будет зачислена с конвертацией по курсу на дату платежа.'|t}
						{else}
							{$tooltip = 'Вы можете получать заработанные средства сразу на вашу карту. Вывод средств осуществляется платежной системамой Solar Staff, обеспечивающей высокий уровень безопасности платежей. Чтобы выводить средства на карту, необходимо выполнить одноразовую привязку карты к аккаунту. Карты России и стран СНГ привязываются как рублевые карты. Если валюта вашей карты отлична от рубля, она будет зачислена с конвертацией по курсу на дату платежа.'|t}
						{/if}
						<span class="tooltip_circle dib tooltipster tooltip_circle--hover tooltip_circle--light"
							  style="display: inline-block; margin-left: 5px;"
							  data-tooltip-text="{$tooltip}"
							  data-tooltip-side="right">?</span>
						<br/>
						<span class="color-gray f12">{'Потребуется подтверждение по номеру телефона'|t}</span>

                    {else}
						<span class="color-gray f12">{'Привязать новую карту можно не ранее'|t} {$p.next_card_link_available|date}</span>
                    {/if}
                {else}
					<span class="color-gray f12">{'В целях безопасности привязка карты возможна'|t} {$dateCanChangeAllPurse|date}</span>
                {/if}
            {else}
				<div class="color-green">{$p.card_number}</div>
                {if $p.isNeedFillForeignSolarData}
                    {$_specify = '<a href="javascript: showSettingForeignCard();">'|cat:{'указать'|t}|cat:'</a>'}
					<p class="dib">{'Необходимо %s дополнительные данные'|t:$_specify} </p>
                {else}
                    {if !$hasActiveCardWithdraw && $canChangePurse}
                        {if time() > strtotime($p.next_card_link_available)}
                            {if $p.zip && $p.isNeedForeignSolarData}
								<a href="javascript: showSettingForeignCard();"
								   class="mr15">{'Изменить данные'|t}</a>
                            {/if}
							<a href="javascript:void(0);"
							   class="js-solar-card-reverify">{'Изменить карту'|t}</a>
							<div class="block-state-active_tooltip block-help-image-js"
								 style="right: -275px; top: -35px;">
								<p class="bold">{'Внимание!'|t}</p>
								<p>{"В целях безопасности при изменении привязанной карты, вы не сможете отправлять заявку на вывод в течение 1 недели."|t}</p>
							</div>
                        {else}
							<div class="mb5">
								<span class="color-gray f12">{'Сменить номер карты можно будет не ранее'|t} {$p.next_card_link_available|date}</span>
							</div>
                            {if $p.zip && $p.isNeedForeignSolarData}
								<a href="javascript: showSettingForeignCard();">{'Изменить данные'|t}</a>
                            {/if}
                        {/if}
                    {else}
                        {if $hasActiveCardWithdraw}
							<div class="mb5">
								<span class="color-gray f12">{'Нельзя сменить номер карты, если есть активные заявки на вывод'|t}</span>
							</div>
                        {elseif $canChangePurse}
							<div class="mb5">
								<span class="color-gray f12">{'Сменить номер карты можно будет не ранее'|t} {$p.next_card_link_available|date}</span>
							</div>
                        {elseif !$canChangePurse}
							<div class="mb5">
								<span class="color-gray f12">{'Изменить реквизиты можно будет'|t} {$dateCanChangeAllPurse|date}</span>
							</div>
                        {/if}

                        {if $p.zip && $p.isNeedForeignSolarData}
							<a href="javascript: showSettingForeignCard();">{'Изменить данные'|t}</a>
                        {/if}
                    {/if}
                {/if}
            {/if}
            {if $p.card_id != '' && $canChangePurse && !$hasActiveCardWithdraw}
				<div class="settings-solar-staff_remove-card-block">
					<a href="javascript:removeCardConfirmation();"
					   class="link-color">{'Удалить'|t}</a>
				</div>
            {/if}
            {if !$canChangePurse}
				<div class="block-state-active_tooltip block-help-image-js"
					 style="right: -275px; top: -35px;">
					<p>{"В целях безопасности после смены данных для входа в систему изменение реквизитов вывода будет доступно только"|t}
					<p>
						<strong>{$dateCanChangeAllPurse|date}</strong>
				</div>
            {/if}
			<div class="js-password-confirm-error-field color-red"{if $isDuplicateCard} style="display: block"{/if}>
                {'Реквизиты карты уже используются в системе'|t}
			</div>
		</div>
		<div class="block-state-active_tooltip block-help-image-js"
			 style="right: -275px; top: -35px;">
			<p class="bold">{'Внимание!'|t}</p>
			<p>{"В целях безопасности при привязке карты, вы не сможете отправлять заявку на вывод в течение 1 недели."|t}</p>
		</div>
	</div>
</div>