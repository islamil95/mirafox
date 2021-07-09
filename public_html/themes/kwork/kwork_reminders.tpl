<div class="reminder-link-block pull-right mb20 mr1 pr20">
	<a class="reminder-link-btn add_moder_reminder_link-js"
	   title="Поставить напоминание">Поставить напоминание</a>
</div>
<div class="clear"></div>

{if is_array($reminders) && $reminders|count gt 0}
	<div class="reminder-list clearfix mb20">
		{foreach from=$reminders item=reminder}
			<div class="reminder-item gray-bg-border p15-20 reminder_item-js" data-reminder-id="{$reminder.id}">
				<div class="reminder-content">
					<img class="reminder-icon mr5" src="{"/images/error_msg_icon.gif"|cdnAdminUrl}" alt="">
					<b class="mr5">{if $reminder.moderFullname}{$reminder.moderFullname}{else}{$reminder.moderUsername}{/if}:</b>
					{$reminder.text}
				</div>
				<button type="button"
						class="reminder-btn btn btn-hover-red pull-right ml20 delete_moder_reminder_link-js">
					<span>Удалить</span>
				</button>
				<button type="button"
						class="reminder-btn btn btn-hover-orange pull-right ml20 edit_moder_reminder_link-js">
					<span>Редактирование</span>
				</button>
			</div>
		{/foreach}
	</div>
{/if}
{* Helper::registerFooterCssFile("/css/popup.css"|cdnBaseUrl) *}
{Helper::registerFooterCssFile("/css/reminder.css"|cdnBaseUrl)}
{Helper::registerFooterCssFile("/css/pages/support/show_reminder.css"|cdnAdminUrl)}
{Helper::registerFooterCssFile("/css/jquery-ui.min.css"|cdnAdminUrl)}
{Helper::registerFooterCssFile("/css/jquery-ui-timepicker-addon.css"|cdnAdminUrl)}

{Helper::registerFooterJsFile("/js/popup.js"|cdnAdminUrl)}
{Helper::registerFooterJsFile("/js/add_edit_moder_reminder.js"|cdnBaseUrl)}
{Helper::registerFooterJsFile("/js/jquery-ui.min.js"|cdnAdminUrl)}
{Helper::registerFooterJsFile("/js/jquery-ui-timepicker-addon.js"|cdnAdminUrl)}
<div class="clear"></div>
