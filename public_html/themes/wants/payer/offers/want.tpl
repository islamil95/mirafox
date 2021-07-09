{strip}
	{if ($want->category_id == 59 && $want->price_limit == 750) || $want->price_limit == 500}
		{$priceName = 'Цена:'|t}
	{else}
		{$priceName = 'Цена до:'|t}
	{/if}
	{$wantStatus=$want->getAltStatusHint()}
	<div class="card project-card">
		<div class="card__content">
			<div class="mb15">
				<div class="wants-card__header">
					<div class="wants-card__header-title first-letter breakwords">
						{$want->name}

						{if $isUserWant}
							{include file="wants/payer/want_share.tpl"}
						{/if}
					</div>
					<div class="wants-card__header-right-block m-hidden">
						<div class="wants-card__header-controls">
							{if $want->price_limit > 0}
								<div class="wants-card__header-price wants-card__price m-hidden">
									<span class="fs12">{$priceName}</span> {include file="utils/currency.tpl" total=$want->price_limit lang=$want->lang}
								</div>
							{/if}
							{if $isUserWant}
								{include file="wants/payer/manage/block_control.tpl"}
							{/if}
						</div>
						<div class="wants-card__header-status">
							<span class="status-block btn-title btn-title_{$wantStatus.color} first-letter">{$wantStatus.title|t}</span>
						</div>
					</div>
				</div>
				<div class="mt10 pb5">
					<div class="wish_name f14 first-letter br-with-lh break-word lh22">
						<div>
							{$want->desc|replace_full_urls|strip_nl|nl2br}
						</div>
					</div>
				</div>
				{include file="wants/common/want_files.tpl" page="project"}
				<div class="m-visible">
					<div class="d-flex mt10">
						{if $want->price_limit > 0}
							<div class="wants-card__header-price wants-card__price">
								<span class="fs12">{$priceName}</span> {include file="utils/currency.tpl" total=$want->price_limit lang=$want->lang}
							</div>
						{/if}
						<div class="ml-auto"></div>
						{if $isUserWant}
							<div class="wants-card__header-controls">
								{include file="wants/payer/manage/block_control.tpl"}
							</div>
						{/if}
						<div class="wants-card__header-status" style="position: relative; top: -2px;">
							<span class="status-block btn-title btn-title_{$wantStatus.color} first-letter">{$wantStatus.title|t}</span>
						</div>
					</div>
				</div>
			</div>
			{include file="wants/common/want_payer_statistic.tpl" user=$want->user}

			<div class="m-p0">
				<div class="clear m-visible mt10"></div>
				{if $isUserWant}
					<div class="color-gray project_card--informers project_card--informers-justify">
						{* Автор проекта *}
						<div>
							{if $want->getSumOrderCount() > 0}
								<div class="project_card--informer">
									{insert name=declension value=a assign=kworkDecl count=$want->getSumOrderCount() form1="кворк" form2="кворка" form3="кворков"}
									<a class="link f13 fw700" href="{absolute_url route="payer_orders"}">
										{$want->getSumOrderCount()|intval} {'заказ'|tn:$want->getSumOrderCount()}
									</a>
									<span class="kwork-icon icon-custom-help tooltipster ml5 project_card--informer_help" data-tooltip-text="{'Вы заказали %s %s по этому проекту'|t:($want->getSumOrderCount()|intval):$kworkDecl}"></span>
								</div>
							{/if}
						</div>
						<div>
							{if $want->views_dirty}
								<div class="project_card--informer">
									<span class="kwork-icon icon-eye"></span>
									{$want->views_dirty} {declension count=$want->views_dirty form1="просмотр" form2="просмотра" form5="просмотров"}
								</div>
							{/if}
							<div class="project_card--informer first-letter">
								{include file="wants/payer/manage/block_date.tpl"}
							</div>
						</div>
					</div>
				{else}
					{if $showButtonOfferService = $want->status === WantManager::STATUS_ACTIVE}
						{* Проверяем предлагал ли уже услугу*}
						{foreach $offers as $offer}
							{if $offer->user->USERID == $actor->USERID}
								{$showButtonOfferService = false}
								{break}
							{/if}
						{/foreach}
					{/if}
					<div class="color-gray project_card--informers project_card--informers-justify">
						<div class="project_card--informers_column">
							<div class="project_card--informers_row">
								{* Все остальные пользователи *}
								{insert name=countdown_short value=a assign=timeLeft time=(strtotime($want->date_expire)) type="deadline"}

								{if $want->status !== WantManager::STATUS_ACTIVE}
									<div class="project_card--informer ml15 w512-ml0">
										<strong>{'Проект закрыт'|t}</strong>
									</div>
								{elseif $timeLeft}
									<div class="project_card--informer ml15 w512-ml0">
										{'Осталось:'|t} {$timeLeft}
									</div>
								{/if}

								{if !$want->isArchive()}
									<div class="project_card--informer ml15 w512-ml0">
										{'Предложений:'|t} {$want->kwork_count}
									</div>
								{/if}

								{if $want->getSumOrderCount() > 0}
									<div class="project_card--informer ml15 w512-ml0">
										{insert name=declension value=a assign=userDecl count=$want->getSumOrderCount() form1="фрилансера" form2="фрилансеров" form3="фрилансеров"}
										{$want->getSumOrderCount()|intval} {'заказ'|tn:$want->getSumOrderCount()}
										<span class="kwork-icon icon-custom-help tooltipster ml5 project_card--informer_help" data-tooltip-text="{'Покупатель сделал заказ у %s %s'|t:($want->getSumOrderCount()):$userDecl}"></span>
									</div>
								{/if}
								{if $showUserWantsList}
							</div><div class="project_card--informers_row">
									<div class="project_card--informer">
										<a href="{absolute_url route="wants_user_list" params=["username"=>$want->user->username|lower]}">Открытые проекты {$want->user->username}</a>
									</div>
								{/if}
							</div>
						</div>
						<div class="project_card--buttons">
							{if $showButtonOfferService}
								{include file="wants/worker/wants_btn_offer_service.tpl"}
							{elseif $actor}
								<a class="projects-offer-btn orange-btn mr15 m-mr0 m-mb5 js-sms-verification-action" href="{absolute_url route="new_project" params=["id" => $want->id]}">
									<div class="wMax">{'Опубликовать похожий проект'|t}</div>
								</a>
								<a class="projects-offer-btn green-btn" href="{absolute_url route="projects_worker"}">
									<div class="wMax">{'Смотреть все проекты'|t}</div>
								</a>
							{/if}
						</div>
					</div>
				{/if}
			</div>
		</div>
	</div>
{/strip}