{strip}
    {if $want->id|array_key_exists:$newOffers}
		<a class="link-color" href="{absolute_url route="view_offers_all" params=["id" => $want->id]}#offers-anchor">
            {if $newOffers[$want->id]["isread"]}
                {$newOffers[$want->id]["isread"]}
                {if !$newOffers[$want->id]["unread"]}
					<span class="m-visible">&nbsp;{declension count=$newOffers[$want->id]["isread"] form1="предложение" form2="предложения" form5="предложений"}</span>
				{/if}
            {/if}
            {if $newOffers[$want->id]["isread"] && $newOffers[$want->id]["unread"]}
				&nbsp;+&nbsp;
            {/if}
            {if $newOffers[$want->id]["unread"]}
                {$newOffers[$want->id]["unread"]} {declension count=$newOffers[$want->id]["unread"] form1="новое" form2="новых" form5="новых"}
				<span class="m-visible">&nbsp;{declension count=$newOffers[$want->id]["unread"] form1="предложение" form2="предложения" form5="предложений"}</span>
            {/if}
		</a>
    {else}
		<span class="m-hidden nowrap">{'Пока нет'|t}</span>
    {/if}
{/strip}