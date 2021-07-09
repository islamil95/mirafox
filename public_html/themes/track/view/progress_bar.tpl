{$percentLoop = ($loop) ? $loop : 100}

<ul class="loadbar{if !$trackMID && !$disableSelect} enable{/if}">
	{section name=percent start=0 loop=$percentLoop step=10}
		<li>
			<div data-track-id="{$trackMID}"
				 data-value="{$smarty.section.percent.index+10}"
				 title="{$smarty.section.percent.index+10}%"
				 class="progress-bar{if $smarty.section.percent.index+10 <= $progress} progress-bar-fill min-active-value{/if}">
			</div>
		</li>
	{/section}
</ul>
