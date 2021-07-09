{strip}
	{include file='functions.tpl'}
	{insert name=is_online_from_array assign=is_online value=a posts=$posts}
	{if $actor}
		{insert name=user_kwork_mark_array assign=user_kwork_marks value=a posts=$posts}
	{/if}
	{section name=i loop=$posts}
		{$posts[i].userRatingCount = UserManager::getUserRatingCount($posts[i])}
		<div class="newfox js-kwork-card" data-id="{$posts[i].PID}">
			<div class="newfoximg">
				<a href="{$baseurl}{$posts[i].url}" onclick="if (typeof (yaCounter32983614) !== 'undefined') {
							yaCounter32983614.reachGoal('SHOW-KWORK');
							return true;
						}"><img src="{$purl}/t2/{$posts[i].photo}"
							{photoSrcset("t2", $posts[i].photo)}
							alt="{$imgAltTitle|stripslashes} {$imgNumber++} - {Translations::getCurrentHost()}"
							width="180" height="120" />
				</a>

				{if $posts[i].bonus_text && $posts[i].bonus_moderate_status == 1 && \App::config('promo_show_badges') == 1}
					<div class="kwork_birthday_badge tooltipster"
						data-tooltip-text="Купите этот кворк и участвуйте в<br> <a href='/newyear'>розыгрыше iPhone 8</a>"
						data-tooltip-theme="dark"
					><img src="{"/promo/newyear_2018/badges_01.png"|cdnImageUrl}" alt=""/></div>
				{/if}
				{if $posts[i].bonus_text && $posts[i].bonus_moderate_status == 1 && \App::config('promo_show_bonus') == 1}
					<div class="cusongsblock_promo">+ {$posts[i].bonus_text}</div>
				{/if}
			</div>
			<div class="newfoxdetails clearfix">
				<div class="clearfix mh100">
					<div class="price pull-right color-green">
						{call name=kwork_price kwork=$posts[i] actor=$actor}
					</div>
					<h3 class="h3-title">
						<a href="{$baseurl}{$posts[i].url}" {if strlen($posts[i].gtitle) > 50}title="{$posts[i].gtitle|stripslashes|upstring}"{/if} onclick="if (typeof (yaCounter32983614) !== 'undefined') {
							yaCounter32983614.reachGoal('SHOW-KWORK');
							return true;
						}"><span class="first-letter dib"> {$posts[i].gtitle|stripslashes|mb_truncate:300} </span></a>
					</h3>
					<div class="scriptomembittitle">
						<a class="pull-left"
							 href="{$baseurl}/{insert name=get_seo_profile value=a username=$posts[i].username|stripslashes}">
							{if $is_online[$posts[i].USERID]}
								<i class="dot-user-status dot-user-online"></i>
							{else}
								<i class="dot-user-status dot-user-offline"></i>
							{/if} {$posts[i].username|stripslashes|truncate:40:"...":true}
						</a>
						<div class="mt3 otherdetails pull-left ml10">
							<ul style="float: none; display: inline-block; vertical-align: middle">
								{if $posts[i].userRating > 0}
									<li class="mr2 v-align-m"><i class="fa fa-star gold" aria-hidden="true"></i> </li>
									<li class="rating-block__rating-item--number fw600 v-align-m">{number_format(round($posts[i].userRating/20,1), 1,".","")}</li>
								{/if}
								{if $posts[i].userRatingCount > 0}
									<li class="rating-block__count">({$posts[i].userRatingCount|shortDigit})</li>
								{/if}
							</ul>
						</div>
					</div>&nbsp;
				</div>
				<div class="otherdetails clearfix">
					<!--добавить класс active к блоку Favorites-block для инферсии наведения-->
					{if $actor && $actor->id != $posts[i].USERID}
						<div data-id="{$posts[i].PID}" class="js-heart-block pull-right {if $posts[i].isBookmark}active{/if}">
							<div class="tooltipster dib" data-tooltip-content=".kwork-control-{$posts[i].PID}">
								{if $posts[i].isHidden}<span class="kwork-icon icon-eye-slash icon-eye-slash-card active"></span>{/if}
								<span class="js-icon-heart-card cur kwork-icon icon-heart icon-heart_hover icon-heart-card {if $posts[i].isHidden}hidden{/if}"></span>
								<div style="display: none;">
									<div class="js-kwork-control kwork-controls kwork-control-{$posts[i].PID}" data-id="{$posts[i].PID}">
										<div class="kwork-control">
											<span class="js-icon-heart-card kwork-icon icon-heart tooltipster {if $posts[i].isBookmark}hidden{/if}" data-tooltip-mhidden="true" data-tooltip-child="true" data-tooltip-theme="dark-minimal" data-tooltip-text="{'Добавить в избранное'|t}"></span>
											<span class="js-icon-heart-card kwork-icon icon-heart tooltipster active {if !$posts[i].isBookmark}hidden{/if}" data-tooltip-mhidden="true" data-tooltip-child="true" data-tooltip-theme="dark-minimal" data-tooltip-text="{'Удалить из избранного'|t}"></span>
										</div>
										<div class="kwork-control">
											<span class="js-kwork-hidden-card kwork-icon icon-eye-slash tooltipster {if $posts[i].isHidden}hidden{/if}" data-id="{$posts[i].PID}" data-action="add" data-tooltip-mhidden="true" data-tooltip-child="true" data-tooltip-theme="dark-minimal" data-tooltip-text="{'Скрыть кворк'|t}"></span>
											<span class="js-kwork-hidden-card kwork-icon icon-eye-slash tooltipster active {if !$posts[i].isHidden}hidden{/if}" data-id="{$posts[i].PID}" data-action="del" data-tooltip-mhidden="true" data-tooltip-child="true" data-tooltip-theme="dark-minimal" data-tooltip-text="{'Вернуть из скрытых'|t}"></span>
										</div>
									</div>
								</div>
							</div>
						</div>
					{elseif $actor && $actor->id == $posts[i].USERID}
						<div class="Favorites-block pull-right">
							<div class="signout-fav-div">
								<span class="kwork-icon icon-heart icon-heart-card icon-heart_hover tooltipster" data-tooltip-text="{'Вы не можете заносить свои кворки в Избранное'|t}"></span>
							</div>
						</div>
					{else}
						<div class="Favorites-block pull-right signup-js">
							<div class="signout-fav-div">
								<span class="kwork-icon icon-heart icon-heart-card icon-heart_hover tooltipster" data-tooltip-text="{'Вы сможете заносить кворки в Избранное, когда <a class=\'login-js cur\'>авторизуетесь</a>'|t}" data-tooltip-mhidden="true"></span>
							</div>
						</div>
					{/if}
					{if $user_kwork_marks[$posts[i].PID]}
						<div class="user_kwork_mark_block">
							<span class="kwork-icon {$user_kwork_marks[$posts[i].PID]|userKworkMark:'class'} tooltipster" data-tooltip-position="center" data-tooltip-text="{$user_kwork_marks[$posts[i].PID]|userKworkMark:'text'}"></span>
						</div>
					{/if}
					{if $posts[i].rating >= KworkManager::BEST_RATING}
						<a href="{$baseurl}{$posts[i].url}" onclick="if (typeof (yaCounter32983614) !== 'undefined') {
							yaCounter32983614.reachGoal('SHOW-KWORK');
							return true;
						}"><span class="fox-express">{'Высший рейтинг'|t}</span></a>
					{/if}
						<div class="pull-left mt2">
							<i class="icon-time v-align-m"></i>
							<span class="dib v-align-m ml5 font-OpenSans f13 color-gray">
								<span class="big">{$posts[i].days|stripslashes} {insert name=declension value=a assign=days count=$posts[i].days form1="{'день'|t}" form2="{'дня'|t}" form3="{'дней'|t}"}{$days} {'на выполнение'|t} </span>
							</span>
						</div>
						<div class="pull-left mt2 ml15">
							<i class="icon-people v-align-m"></i>
							<span class="dib v-align-m ml5 font-OpenSans f13 color-gray">{$posts[i].quecount|stripslashes} {'в очереди'|t}</span>
						</div>
				</div>
			</div>
			<div class="clear"></div>
		</div>
	{/section}
{/strip}