{strip}
	<div class="clear"></div>
	<div class="track--sidebar">
		<div class="track--sidebar__head">
            {include file='functions.tpl'}
			{include file="track/user_info.tpl"}
            {include "track/sidebar_history.tpl"}
		</div>

		<div id="sidebar-files-container">
            {include file="track/sidebar_files.tpl"}
		</div>
		<div class="track--sidebar__questions mt20 toggler ">
				<div class="p15-20 track--sidebar__questions--title bgLightGray  border-gray d-flex align-items-center toggler--link">
					<svg width="22" height="22" viewBox="0 0 1409 1410" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M704.5 1375.45C1074.52 1375.45 1374.48 1075.28 1374.48 705C1374.48 334.718 1074.52 34.545 704.5 34.545C334.481 34.545 34.5205 334.718 34.5205 705C34.5205 1075.28 334.481 1375.45 704.5 1375.45Z" stroke="#DEDED5" stroke-width="43.7241" stroke-miterlimit="10"/>
						<path d="M646.026 872.79C646.026 838.668 652.931 807.507 666.88 779.307C680.829 751.107 697.455 727.278 717.04 707.82C736.484 688.362 755.928 669.609 775.514 651.42C794.958 633.372 811.725 613.35 825.674 591.354C839.623 569.358 846.527 545.952 846.527 520.854C846.527 480.528 832.719 450.072 805.243 429.486C777.768 408.9 742.402 398.748 699.287 398.748C613.76 398.748 560.782 433.857 540.633 504.216L479.06 469.671C495.123 423 523.021 387.75 563.036 363.639C603.052 339.669 648.422 327.543 699.287 327.543C760.437 327.543 812.57 344.463 855.263 378.162C898.097 412.002 919.372 459.519 919.372 520.713C919.372 550.605 912.468 578.523 898.519 604.326C884.57 630.129 867.944 652.548 848.359 671.724C828.915 690.9 809.33 709.794 789.885 728.688C770.441 747.441 753.674 769.296 739.725 793.971C725.776 818.646 718.872 845.013 718.872 872.79H646.026ZM723.24 1064.83C712.109 1075.97 698.582 1081.61 682.52 1081.61C666.457 1081.61 652.931 1075.97 641.799 1064.83C630.668 1053.69 625.173 1040.16 625.173 1024.08C625.173 1008.01 630.668 994.473 641.799 983.334C652.931 972.195 666.457 966.555 682.52 966.555C698.582 966.555 712.109 972.195 723.24 983.334C734.371 994.473 740.007 1008.01 740.007 1024.08C740.007 1040.16 734.371 1053.69 723.24 1064.83Z" fill="#FFA800"/>
					</svg>
					<span class="dib v-align-m ml10">{'Помощь'|t}</span>
					<i class="fa fa-chevron-down ml-auto"></i>
				</div>
				<div class="track--questions toggler--content">
                    {* Не все блоки отдельным шаблоном, потому что нужен вывод отдельного блока отдельно. *}
                    {foreach from=$helpBlocks item=helpBlock key=id name=helps}
                        {assign 'isLast' $smarty.foreach.helps.last}
                        {include file="track/help_block.tpl" block=$helpBlock id=$id  isLast=$isLast}
                    {/foreach}
				</div>
		</div>

	</div>
    {* Рекомендуем также заказать *}
    {if $recommendedKworks|count >= 1}
		<div id="recommend-blocks" class="recommend-block-track-page m-hidden">
			<div class="align-items-center bgLightGray border-gray d-flex mt20 track--sidebar__questions--title recommend-block__header  track--sidebar__questions cur">
				<svg width="22" height="22" viewBox="0 0 1409 1410" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M704.5 1375.45C1074.52 1375.45 1374.48 1075.28 1374.48 705C1374.48 334.718 1074.52 34.545 704.5 34.545C334.481 34.545 34.5205 334.718 34.5205 705C34.5205 1075.28 334.481 1375.45 704.5 1375.45Z" stroke="#DEDED5" stroke-width="43.7241" stroke-miterlimit="10"/>
					<path d="M1011.24 486.591H397.761V548.208H1011.24V486.591Z" fill="#FFA800"/>
					<path d="M1011.24 674.121H397.761V735.738H1011.24V674.121Z" fill="#FFA800"/>
					<path d="M1011.24 861.651H397.761V923.268H1011.24V861.651Z" fill="#FFA800"/>
				</svg>
				<span class="dib v-align-m ml10">{'Рекомендуем также заказать'|t}</span>
				<i class="fa {if $isShowRecommendations} fa-chevron-up{else} fa-chevron-down{/if} ml-auto color-gray"></i>
			</div>
			<div class="bgLightGray p15-20 recommend-block-track-page__container p0 {if !$isShowRecommendations}hidden{/if}">
                {foreach $recommendedKworks as $recommendedKwork}
                    {$recommendedKwork.userRatingCount = UserManager::getUserRatingCount($recommendedKwork)}
					<div class="cusongsblock">
						<div class="songperson cusongsblock__content">
							<a href="{$recommendedKwork.url}">
								<img src="{getImageT3Url($recommendedKwork.photo)}"
                                        {photoSrcset("t4", $recommendedKwork.photo)}
									 alt="{$imgAltTitle|stripslashes} {$imgNumber++} - {Translations::getCurrentHost()}"
									 width="230"
									 height="153">
							</a>
						</div>
						<div class="ta-left padding-content">
							<p>
								<a class="multiline-faded" href="{$recommendedKwork.url}"
								   title="{$recommendedKwork.gtitle}">
									<span class="first-letter breakwords dib">{$recommendedKwork.gtitle}</span>
								</a>
							</p>
                            {if $recommendedKwork.rating >= KworkManager::BEST_RATING}
								<div class="cusongsblock-toprated m-hidden clearfix">
									<div class="toprated-inner-white">
										<span class="fox-express">{'Высший рейтинг'|t}</span>
									</div>
								</div>
                            {/if}
							<div class="cusongsblock__panel">
								<div class="pull-right cusongsblock-panel__rating m-pull-reset">
                                    {if $recommendedKwork.userRating > 0}
										<ul class="rating-block cusongsblock-panel__rating-list dib">
											<li class="mr2 v-align-m"><i class="fa fa-star gold"
																		 aria-hidden="true"></i></li>
											<li class="rating-block__rating-item--number fw600 v-align-m">
                                                {number_format(round($recommendedKwork.userRating/20,1), 1,".","")}
											</li>
										</ul>
                                    {/if}
                                    {if $recommendedKwork.userRatingCount > 0}
										<span class="rating-block__count">({$recommendedKwork.userRatingCount|shortDigit})</span>
                                    {/if}
								</div>
								<div class="clear"></div>
							</div>
							<div class="userdata clearfix">
								<div class="price pull-right m-pull-right">
                                    {call name=kwork_price kwork=$recommendedKwork actor=$actor}
								</div>
								<div class="pull-left cusongsblock-panel__user-name m-visible {if $recommendedKwork.rating < KworkManager::BEST_RATING} w50p {/if}">
                                    {if $is_online[$recommendedKwork.USERID]}
										<i class="dot-user-status dot-user-online v-align-m"></i>
                                    {else}
										<i class="dot-user-status dot-user-offline v-align-m"></i>
                                    {/if}
									&nbsp;
									<a class="dark-link dib v-align-m oneline-faded {if $recommendedKwork.rating >= KworkManager::BEST_RATING} w100{else} w90p {/if}"
									   href="{absolute_url route="profile_view" params=["username" => $recommendedKwork.username]}"
									   title="{$recommendedKwork.username|stripslashes}">
                                        {$recommendedKwork.username|stripslashes}
									</a>
								</div>
							</div>
						</div>
					</div>
                {/foreach}
			</div>
		</div>
    {/if}
	</div>
{/strip}
