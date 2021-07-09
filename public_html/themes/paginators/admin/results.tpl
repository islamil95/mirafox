{strip}
    {'Показаны'|t} {if $results->total() gt 0}{$results->firstItem()} - {$results->lastItem()} {'из'|t} {/if}{$results->total()} {'результатов'|t}
{/strip}
