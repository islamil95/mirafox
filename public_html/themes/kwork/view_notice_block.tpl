{strip}
{if $isSuspended && !UserManager::onQueueforUnblock($kwork.USERID)}
	{if $isAdmin || $isKworkUser}
		{control name="kwork\kwork_notice\kwork_notice_suspend" p=$kwork}
	{else}
		{control name="kwork\kwork_notice\kwork_notice_block" p=$kwork u=$u}
	{/if}
{else}
	{* Сообщение об необходимости обновить описание кворка*}
	{if $isNeedUpdate}
		{control name="kwork\kwork_notice\kwork_notice_need_update" PID=$kwork.PID}
	{/if}

	{* Сообщение об необходимости добавить языки перевода*}
	{if $isNeedUpdateTranslates}
		{control name="kwork\kwork_notice\kwork_notice_need_update_translates" PID=$kwork.PID}
	{/if}

	{* Сообщение об необходимости активировать кворк и кнопка *}
	{if $isKworkUser && $isStoppedKwork && $kwork.active != KworkManager::STATUS_REJECTED}
		{control name="kwork\kwork_notice\kwork_notice_activate" PID=$kwork.PID}
	{/if}

	{* Сообщение простому пользователю, гостю или модератору о невозможности сделать заказ*}
	{if !$isAdmin && !$isKworkUser && $notCanOrder}
		{* Разные сообщения для остановленного пользователем и заблокированного по другой причине кворка *}
		{if $isStoppedKwork}
			{control name="kwork\kwork_notice\kwork_notice_stop" p=$kwork u=$u}
		{elseif !$isModeratedKwork || $isPostModerKwork}
			{control name="kwork\kwork_notice\kwork_notice_block" p=$kwork u=$u}
		{/if}
	{/if}

	{* Сообщение админу *}
	{if $isAdmin && !$isKworkUser && ($notCanOrder || $notCanView)}
		{* Разные сообщения для остановленного пользователем и заблокированного по другой причине кворка *}
		{if $isStoppedKwork}
			{control name="kwork\kwork_notice\kwork_notice_stop" p=$kwork u=$u}
			{* Кворк удален *}
		{elseif $kwork.active == KworkManager::STATUS_DELETED}
			{control name="kwork\kwork_notice\kwork_notice_delete"}
			{* Кворк Заблокирован за штрафные баллы *}
		{elseif $kwork.active == KworkManager::STATUS_SUSPEND}
			{control name="kwork\kwork_notice\kwork_notice_suspend" p=$kwork}
			{*Кворк не доступен по другой причине*}
		{elseif !$isModeratedKwork && !$canRemoderation}
			{control name="kwork\kwork_notice\kwork_notice_block" p=$kwork u=$u}
		{/if}
	{/if}

	{* Кворк на модерации, показать модерам сообщение и кнопки для модерации, пользователям сообщения *}
	{if $isModeratedKwork}
		{if $canModer}
			{control name="kwork\kwork_notice\kwork_notice_action_moderated" p=$kwork}
		{elseif $isKworkUser}
			{if $isPostModerKwork}
				{control name="kwork\kwork_notice\kwork_notice_postmoderated"}
			{else}
				{control name="kwork\kwork_notice\kwork_notice_moderated"}
			{/if}
		{elseif $isModer}
			{control name="kwork\kwork_notice\kwork_notice_action_moder" p=$kwork}
		{elseif $kwork.active == KworkManager::STATUS_MODERATION}
			{control name="kwork\kwork_notice\kwork_notice_block" p=$kwork u=$u}
		{/if}
	{else}
		{* Кворк можно вернуть на модерацию, показать сообщение и кнопку возврата к модерации *}
		{if $canRemoderation || ($isKworkUser && $isModer)}
			{control name="kwork\kwork_notice\kwork_notice_action_moder" p=$kwork}
		{/if}
	{/if}

	{* Сообщение админу или владельцу кворка пользователю с причинами и пояснениями о модерации кворка *}
	{if $isKworkUser && !$isModer && $kwork.active == KworkManager::STATUS_REJECTED}
		{control name="kwork\kwork_notice\kwork_notice_rejected" reasons=$rejectReasons p=$kwork}
	{/if}
{/if}
{/strip}