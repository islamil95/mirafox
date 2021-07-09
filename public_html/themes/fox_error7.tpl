{strip}

{if $error ne "" && is_string($error)}
	{$class = 'fox_error'}
	{$text = $error}
{elseif $message ne "" && is_string($message)}
	{$class = 'fox_success'}
	{$text = $message}
{elseif $snotice ne "" && is_string($snotice)}
	{$class = 'fox_notice'}
	{$text = $snotice}
{elseif $attention ne "" && is_string($attention)}
	{$class = 'fox_attention'}
	{$text = $attention}
{/if}

{if $class && $text}
<div class='{$class}'>
	<div class="centerwrap lg-centerwrap">
		<p>{if $class === 'fox_attention'}<i class="icon ico-attention_white v-align-m mr5"></i>{/if}{$text}</p>
	</div>
</div>
{/if}

{/strip}
