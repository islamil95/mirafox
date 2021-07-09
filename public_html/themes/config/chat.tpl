{if !$config["chat"]}
    {append var="config" value=[
    "isFocusGroupMember" => UserManager::isTester($actor->id)
] index="chat" scope=root}
{/if}
<script>{to_js name="config.chat" var=$config.chat}</script>