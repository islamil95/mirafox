{strip}
	<span class="f13 m-f11 analytics-value analytics-value--{$level}">
		{if $level == "excellent"}
			{"отлично"|t}
		{elseif $level == "good"}
            {"хорошо"|t}
        {elseif $level == "satisfactorily"}
            {"средне"|t}
        {elseif $level == "bad"}
            {"плохо"|t}
		{/if}
	</span>
{/strip}