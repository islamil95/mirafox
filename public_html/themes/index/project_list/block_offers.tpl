{strip}
    {if $want->newOffers}
		<a class="link-color" href="{absolute_url route="view_offers_all" params=["id" => $want->id]}#offers-anchor">
            {if $want->newOffers["isread"]}
                {$want->newOffers["isread"]}
                {if !$want->newOffers["unread"]}
					<span class="m-visible">&nbsp;{declension count=$want->newOffers["isread"] form1="предложение" form2="предложения" form5="предложений"}</span>
				{/if}
            {/if}
            {if $want->newOffers["isread"] && $want->newOffers["unread"]}
				&nbsp;+&nbsp;
            {/if}
            {if $want->newOffers["unread"]}
                {$want->newOffers["unread"]} {declension count=$want->newOffers["unread"] form1="новое" form2="новых" form5="новых"}
				<span class="m-visible">&nbsp;{declension count=$want->newOffers["unread"] form1="предложение" form2="предложения" form5="предложений"}</span>
            {/if}
		</a>
    {else}
		<span class="m-hidden nowrap">{'Пока нет'|t}</span>
    {/if}
{/strip}