{strip}
	<div class="offers-sort">
		<span class="nofollow">{'Сортировать по:'|t}</span>
		<a rel="nofollow" href="{absolute_url route="view_offers_all" params=["id" => $want->id, "s" => "date"]}"
		   {if $sortType == "date"}class="active"{/if}>
			{'Дате'|t}
		</a>
		<a rel="nofollow" href="{absolute_url route="view_offers_all" params=["id" => $want->id, "s" => "rating"]}"
		   {if $sortType == "rating"}class="active"{/if}>
			{'Рейтингу'|t}
		</a>
	</div>
{/strip}