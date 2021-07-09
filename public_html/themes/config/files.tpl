{if !$config["files"]}
	{append var="config" value=[
		"maxCount" => (int)App::config("files.max_count"),
		"maxSizeReal" => (int)App::config("files.max_size"),
		"maxSize" => round(App::config("files.max_size") / 1048576)
	] index="files" scope=root}
{/if}
<script>{to_js name="config.files" var=$config.files}</script>