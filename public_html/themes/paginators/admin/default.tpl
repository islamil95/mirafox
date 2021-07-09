{strip}
	{if $paginator->hasPages() }
        {* Ссылка первая и предыдущая *}
        {if !$paginator->onFirstPage() }
        	<a href="{reset(reset($elements))}">{"Первая"|t}</a>&nbsp;
            <a href="{$paginator->previousPageUrl()}">{"Предыдущая"|t}</a>&nbsp;
        {/if}

        {* Ссылки с номерами страниц *}
        {foreach item=element from=$elements}
            {* Троеточие, если ссылок слишком много *}
            {if is_string($element)}
                {$element}&nbsp;
            {/if}

            {* Ссылки с номерами страниц *}
            {if is_array($element)}
                {foreach item=url key=page from=$element}
                    {if $page == $paginator->currentPage()}
                        {$page}&nbsp;
                    {else}
                        <a href="{$url}">{$page}</a>&nbsp;
                    {/if}
                {/foreach}
            {/if}
        {/foreach}

        {* Ссылки следующая и последняя *}
        {if $paginator->hasMorePages()}
            <a href="{$paginator->nextPageUrl()}">{"Следующая"|t}</a>&nbsp;
            <a href="{end(end($elements))}">{"Последняя"|t}</a>&nbsp;
        {/if}
	{/if}
{/strip}
