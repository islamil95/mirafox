{strip}
	{if $rating > 0}
		<div class="mr2 dib"><i class="fa fa-star gold" aria-hidden="true"></i></div>
		<div class="text-orange fw600 f18 dib">{number_format(round($rating/20,1), 1,".","")}</div>
	{/if}
{/strip}