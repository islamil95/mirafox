{if $track->user_id == $actor->USERID}
	<div class="tr-message-status-bar {if !$config.track.isFocusGroupMember} ml-auto align-self-end {/if}">
		<div class="tr-message-status sended" title="{'Доставлено'|t}"></div>
		<div class="tr-message-status readed" title="{'Прочитано'|t}"></div>
	</div>
{/if}