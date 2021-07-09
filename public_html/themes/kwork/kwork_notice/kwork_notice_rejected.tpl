{strip}
	{if !$hideWrapper}<div class="reject-info-wrapper">{/if}
	<div class="reject-info flex-wrap ">
		<div class="d-flex mw630px">
			<div class="reject-info__icon-wrapper">
				<div class="block-circle block-circle-50 block-circle_red">
					<img src="{"/icon_info.svg"|cdnImageUrl}" width="20" height="20" alt="">
				</div>
			</div>
			<div class="reject-info__content">
                {if $p.rejectFieldsString}
					<span class="reject-info__title">{'Кворк требует исправления в полях:'|t}</span>
					<span class="reject-info__reasons">{$p.rejectFieldsString}</span>
                {else}
					<span class="reject-info__title">{'Кворк требует исправления'|t}</span>
                {/if}
				<div class="mt6">
					<a href="{$baseurl}/edit?id={$p.PID}" class="reject-info__link">
                        {'Узнать подробнее и исправить'|t}
						<i class="fa fa-lg fa-angle-right ml7"></i>
					</a>
				</div>
			</div>
		</div>
		<div class="flex-1-0-100"> 
            {if $lastModerationInfo['status']=='reject'}
			<div class="mt10 mb10 font-OpenSans dib mw630px">
                {'Последний раз отклонен:'|t} {$lastModerationInfo['date_moder']|date}, модератором: <span>{$lastModerationInfo['moder_name']}</span>, {'по причинам:'|t}
                {assign var="lastReasonsInfoEnd" value=end($lastReasonsInfo)}
                {foreach $lastReasonsInfo as $reason}
					<span> {$reason['name']}{if $reason['id']!=$lastReasonsInfoEnd['id']},{/if}</span>
                {/foreach}
				<a title="{'Показать историю'|t}" href="javascript:openWindow('Статистика модерации','/administrator/gigs_history.php?kworkId={$p.PID}')" class="ml10 v-align-m">
					<div class="icon ico-search-icon"></div>
				</a>
			</div>
            {/if}
			{if is_array($reminders) && $reminders|count gt 0}
				<div class="reminder-list mw630px clearfix mb10">
                    {foreach from=$reminders item=reminder}
						<div class="reminder-item gray-bg-border p15-20 reminder_item-js mb10"
							 data-reminder-id="{$reminder.id}">
							<div class="reminder-content">
								<img class="reminder-icon mr5" src="{$adminurl}/images/error_msg_icon.gif" alt="">
								<b class="mr5">{if $reminder.moderFullname}{$reminder.moderFullname}{else}{$reminder.moderUsername}{/if}
									:</b>
                                {$reminder.text}
							</div>
						</div>
                    {/foreach}
				</div>
			{/if}
		</div>
	</div>
	{if !$hideWrapper}</div>{/if}
{/strip}