{if !$config["track"]}
	{append var="config" value=[
		"isFocusGroupMember" => in_array($actor->id, App::config("track.testers")),
		"fileMaxCount" => (int)App::config("track.files.max_count")
	] index="track" scope=root}
{/if}
<script>{to_js name="config.track" var=$config.track}</script>