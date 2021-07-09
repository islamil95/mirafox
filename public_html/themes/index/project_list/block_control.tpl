{strip}
    {if in_array($want->status, [WantManager::STATUS_ACTIVE, WantManager::STATUS_NEW])}
        <a href="javascript:stop_request_confirm({$want->id})" class="btn-edit" title="{'Остановить'|t}">
            <span class="kwork-icon icon-pause"></span>
        </a>
    {elseif in_array($want->status, [WantManager::STATUS_STOP, \Model\Want::STATUS_USER_STOP])}
        <a href="javascript:restart_request_confirm('{$want->id}')" class="btn-edit" title="{'Перезапустить'|t}">
            <span class="kwork-icon icon-play"></span>
        </a>
    {else}
        <span class="projects-list__icons-item_empty"></span>
    {/if}
    <a href="{absolute_url route="edit_project" params=["id" => $want->id]}" class="btn-edit" title="{'Изменить'|t}">
        <span class="kwork-icon icon-pencil"></span>
    </a>
	<a href="javascript:delete_request_confirm({$want->id})" class="btn-edit" title="{'Удалить'|t}">
		<span class="kwork-icon icon-bin"></span>
	</a>
{/strip}