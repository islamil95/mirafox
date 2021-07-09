{strip}
	{insert name=is_online_from_array assign=is_online value=a posts=$posts}
{if $carousel}
	<div class="kwork-small-carousel">
		{/if}
		{section name=i loop=$posts}
			{if $smarty.section.i.index == 5 && $pageName == "view" && $pageSpeedMobile}{break}{/if}
			{control name="_blocks/kwork/kwork_card" kwork=$posts[i] is_online=$is_online user_kwork_marks=$user_kwork_marks show_birthday_badges=$show_birthday_badges}
			{if $wantBanner && $currentpage <= 1 && $smarty.section.i.total < 24 && $smarty.section.i.last}
				<a href="/manage_projects" class="cusongsblock js-kwork-card cards-layout-item kl-card-banner" style="background-image: url('{"/banners/wants_banner_1.svg"|cdnImageUrl}')" data-id="0"></a>
			{/if}
		{/section}
		{if $carousel}
	</div>
{/if}
	{if $isCustomRequest}
        {*Внимание работать будет только на странице профиля пользователя, т.к. зависит от попапа в шаблоне individual_message.tpl*}
		{include file="custom_request.tpl"}
	{/if}
{literal}
	<script>
		{/literal}{if $pageName == "land" && UserManager::isAdmin()}{literal}

		var exclude_add = function (pid, el) {
			if (!confirm(t('Не показывать этот кворк на этом лендинге?')))
				return false;

			$(el).addClass('preloader__ico');

			$.post('{/literal}{$baseurl}{literal}/api/land/updateexcludelist',
				{
					'kwork_id': pid,
					'land_id': {/literal}{if is_array($land)}{$land['id']}{else}{$land->id}{/if}{literal},
					'type': 'add'
				},
				function (response) {
					$(el).removeClass('preloader__ico');
					if (response.success == true) {
						$(el).addClass('active');
					}
				}, 'json');
		};

		var exclude_remove = function (pid, el) {
			$(el).addClass('preloader__ico');
			$.post('{/literal}{$baseurl}{literal}/api/land/updateexcludelist',
				{
					'kwork_id': pid,
					'land_id': {/literal}{if is_array($land)}{$land['id']}{else}{$land->id}{/if}{literal},
					'type': 'remove'
				},
				function (response) {
					$(el).removeClass('preloader__ico');
					if (response.success == true) {
						$(el).removeClass('active');
					}
				}, 'json');
		};

		function exclude_update(pid, el) {
			if ($(el).hasClass('active')) {
				exclude_remove(pid, el);
			} else {
				exclude_add(pid, el);
			}
			return false;
		}
		{/literal}{/if}{literal}
	</script>
{/literal}
{/strip}
