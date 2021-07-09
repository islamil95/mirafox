{if $hints}
    <ul class="bad-words-ul">
    {foreach from=$hints key=key item=hint}
        <li>{$hint}</li>
    {/foreach}
    </ul>
{/if}