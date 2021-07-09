{strip}
	{if $rating > 0}
		<li class="mr2 v-align-m"><i class="fa fa-star gold" aria-hidden="true"></i> </li>
		<li class="rating-block__rating-item--number fw600 v-align-m">{number_format(round($rating/20,1), 1,".","")}</li>
	{/if}
{/strip}