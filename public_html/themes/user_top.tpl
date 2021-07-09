{strip}
	<div class="userbanner" style="background: url({"/$cover"|cdnCoverUrl}) no-repeat center center; background-size: cover;">
		<div class="centerwrap clearfix userbanner__inner">
			{if $USERID}
				<div class="pb30 mt40 sm-text-center m-p0 userbanner__content">
					<div>
						{include file="user_avatar.tpl" profilepicture=$profilepicture username=$uname size="big" class="s80"}
					</div>
					<div class="userbannertext ml15 sm-pull-reset m-text-center m-m0">
						<div class="userbanner__status-wrapper">
							{if Translations::isDefaultLang()}
								<h1 {if $fullname ne ''}class="mt0 m-m0 m-w100"{/if}>{$uname|stripslashes}</h1>
							{else}
								<h1 {if $fullnameen ne ''}class="mt0 m-m0 m-w100"{/if}>{$uname|stripslashes}</h1>
							{/if}
							{insert name=is_online assign=is_online value=a userid=$USERID}
							<div class="m-mt10 f14 ml14 m-ml0 color-lightGray">
								{if $is_online eq 1}
									<i class="dot-user-status dot-user-online"></i> {'Онлайн'|t}
								{else}
									{insert name=last_online_ago assign=last_online value=a time=$live_date}
									<i class="dot-user-status dot-user-offline"></i> {'Оффлайн'|t} <span class="f12">({'последний визит:'|t} {$last_online|timeLeft})</span>
								{/if}
							</div>
						</div>

						{if Translations::isDefaultLang()}
							{if $fullname ne ''}
								<div class="color-white mb10 m-mt10">{$fullname|stripslashes}</div>
							{/if}
						{else}
							{if $fullnameen ne ''}
								<div class="color-white mb10 m-mt10">{$fullnameen|stripslashes}</div>
							{/if}
						{/if}

						<div class="color-white d-flex justify-content-center justify-content-md-start userbanner-block-level">
							<div class="cusongsblock-panel__rating mti3">
								<ul class="rating-block dib v-align-m">
									{control name=rating_stars rating=$rating}
								</ul>
							</div>

							{insert name=user_level assign=lev value=a userid=$USERID}

							<p class="color-white {if $rating > 0}ml10{/if}">
								{if $desc == "level"}
									{if $lev eq 1}{'Новичок'|t}{/if}
									{if $lev eq 2}{'Продвинутый'|t}{/if}
									{if $lev eq 3}{'Профессионал'|t}{/if}
								{/if}
								{if $desc == "type" && $actor}
									{if $actor->type == "payer"}
										{'Покупатель'|t}
									{else}
										{'Продавец'|t}
									{/if}
								{/if}
								{if $ucityid || $uCountryId}
									<img class="user-top_location" src="{"/location.png"|cdnImageUrl}" alt="Местоположение"/>
								{/if}
							</p>

							{insert name=city_id_to_name value=a assign=usercc id=$ucityid countryId=$uCountryId}

							<span class="ml5">{$usercc}</span>
						</div>
					</div>
				</div>
			{/if}

			{if $contentType eq "profile"}
				<div class="sidebarArea mt25 m-mt10">
					{if $actor}
						{if $actor->id == $USERID}
							<div class="profile-cover-block">
								<i class="tooltipster icon ico-photo ico-photo_hover profile-cover-photo_button" data-tooltip-side="left" data-tooltip-text="{'Загрузить шапку профиля'|t}" onclick="showChangeProfileCover();"></i>
							</div>
							<a class="hugeGreenBtn hoverMe GreenBtnStyle h50" href="{$baseurl}/settings">{'Настройки'|t}</a>
						{else}
							{if $privateMessageStatus === true}
								<a class="hugeGreenBtn hoverMe GreenBtnStyle h50" href="{$baseurl}/{insert name=get_seo_convo value=a assign=cvseo username=$uname|stripslashes}{$cvseo}">{'Написать сообщение'|t}</a>
							{/if}
						{/if}
					{/if}
				</div>
			{elseif $contentType eq "newKwork"}
				<div class="sidebarArea ">
					<a class="js-blocked-kworks hugeGreenBtn hoverMe GreenBtnStyle h50 wMax mt60" href="{$baseurl}/new">{'Создать кворк'|t}</a>
				</div>
			{elseif $contentType eq "viewProfile"}
				<div class="sidebarArea mt25 m-mt10">
					<div class="profile-cover-block">
						<div class="tooltipster profile-cover-photo_button ico-photo ico-photo_hover icon" data-tooltip-side="left" data-tooltip-text="{'Загрузить шапку профиля'|t}" onclick="showChangeProfileCover();"></div>
					</div>
					<a class="hugeGreenBtn hoverMe GreenBtnStyle h50 m-m0" href="{$baseurl}/{insert name=get_seo_profile value=a username=$actor->username|stripslashes}">{'Посмотреть профиль'|t}</a>
				</div>
			{/if}
		</div>
	</div>
{/strip}