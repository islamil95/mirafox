{strip}
	<div class="bgLightGray user-page-head">
		<div class="centerwrap lg-centerwrap clearfix overflow-visible">
			<div class="bgWhite user-col-container">
				<div class="user-col user-col-left m-text-center">
					<a name="#about" class="profile-tab-link"></a>
					<div class="user-col-profile">
                        {if $isWorker}
							<div class="user-avatar-container js-user-avatar-container">
								<div class="user-avatar-container_add-photo cur js-file-add-button file-upload-block">
									<div class="relative">
										{include file="user_avatar.tpl" profilepicture=$userProfile->profilepicture username=$userProfile->username size="large" class="user-avatar-image s200 js-user-avatar-image"}
										<canvas id="js-user-avatar-canvas" class="user-avatar-canvas"></canvas>
										<div class="upload-progress js-user-avatar-progress">
											<div></div>
										</div>
									</div>
									<img class="cur user-avatar_add-photo-button user-avatar_tooltip dib tooltipster" src="{"/icon_camera.png"|cdnImageUrl}" width="72" height="64" alt="" data-tooltip-text="{'Загрузить фотографию'|t}" data-tooltip-side="top" data-tooltip-interactive="false">
								</div>

								<input id="js-user-avatar-input" type="file"
									   class="js-file-input d-none"
									   accept=".jpeg, .jpg, .gif, .png">
							</div>
							<div class="add-photo_error js-add-photo_error"></div>
						{else}
							<div class="user-avatar-container">
                                {include file="user_avatar.tpl" profilepicture=$userProfile->profilepicture username=$userProfile->username size="large" class="user-avatar-image s200"}
							</div>
						{/if}

						<h1 class="user-username mt15 word-break-all"
							title="{$userProfile->username|stripslashes}">
								{$userProfile->username|stripslashes|truncate:20}
						</h1>
						{if $fullName}
							<div class="user-fullname mt15 m-visible">
								{$fullName} {* {if $firstLetterFromLastName}{$firstLetterFromLastName}{/if} *}
							</div>
						{/if}
						{if $profession}
							<div class="user-profession mt15 m-visible">
								{$profession}
							</div>
						{/if}
						<div class="m-visible mt15 t-align-l dib mx-auto">
							{if $userProfile->order_done_count > 0}
								<div class="user-rating fw600 f15">
									<div class="mr7 w47 dib t-align-r text-nowrap">
										{include file="rating_stars_inline.tpl" rating=$userProfile->cache_rating}
									</div>
									{$userProfile->getLevelAsText()}
								</div>
								<div class="user-statistics mt5 f15">
									<div class="orders-done text-nowrap">
										<span class="dib w47 t-align-r mr7 colorGreen fw600 f18">
											{$userProfile->order_done_count}
										</span>{declension count=$userProfile->order_done_count form1="{'заказ выполнен'|t}" form2="{'заказа выполнено'|t}" form5="{'заказов выполнено'|t}"}
									</div>
									<div class="responses-left mt5 text-nowrap">
										<span class="dib w47 t-align-r mr7 colorGreen fw600 f18">
											{$totalReviewsCount}
										</span>{declension count=$totalReviewsCount form1="{'отзыв оставлен'|t}" form2="{'отзыва оставлено'|t}" form5="{'отзывов получено'|t}"}
									</div>
									<div class="orders-statistics">
										<div class="position-r mt5 text-nowrap">
											<span class="dib w47 t-align-r mr7 colorGreen fw600 f18">
												{$userProfile->data->order_done_persent|round}%
											</span>{'заказов успешно сдано'|t}
											<span 
												class="tooltipster kwork-icon icon-custom-help icon-custom-help_size-18 ml5"
												data-tooltip-text="{'Процент повышается, когда продавец успешно выполняет заказы. Понижается, когда он отказывается от заказов по неуважительным причинам, или получает отрицательный отзыв.'|t}"
												data-tooltip-theme="dark"
											/>
										</div>
										<div class="mt5 text-nowrap">
											<span class="dib w47 t-align-r mr7 colorGreen fw600 f18">
												{$userProfile->data->order_done_intime_persent|round}%</span>
											{'заказов сдано вовремя'|t}
										</div>
										<div class="mt5 text-nowrap">
											<span class="dib w47 t-align-r mr7 colorGreen fw600 f18">
												{$userProfile->data->order_done_repeat_persent|round}%
											</span>{'повторных заказов'|t}
										</div>
									</div>
								</div>
							{/if}
							{if $showPayerLevel}
								{include file="user/small_tooltip.tpl" class="user-level-tooltip" level=$userProfile->data->payer_level super=$userProfile->data->is_super_payer}
								<div class="payer-level mt5 fw600 f15 d-flex align-items-center tooltipster" data-tooltip-content=".user-level-tooltip" data-tooltip-destroy="true" data-tooltip-side="bottom">
									<div class="dib mr7 w47 d-flex justify-content-end">
										<div class="{if $userProfile->data->is_super_payer}t-align-c-i{/if}">
											<div class="payer-level-icon mx-auto" title="{$userProfile->data->getPayerLevelTooltipLabel()}">
												{$userProfile->data->payer_level}
											</div>
											{if $userProfile->data->is_super_payer}
												<div class="super-payer-icon-label">{'супер'|t}</div>
											{/if}
										</div>
									</div>
									<span class="dib t-align-l text-nowrap">
										{$userProfile->data->getPayerLevelLabel()|nl2br}
									</span>
								</div>
							{/if}
							{if $workerOrdersCount > 0 || $payerOrdersCount > 0}
								<div class="{if $userProfile->order_done_count > 0}ml54{else}ta-center{/if}{if $userProfile->order_done_count > 0 || $showPayerLevel} mt10{/if}">
									<a href="{$baseurl}/{if $workerOrdersCount > 0}orders{else}manage_orders{/if}?s=all&filter_user_id={$userProfile->USERID}">
										{if $workerOrdersCount > 0}{'Все заказы с'|t}{else}{'Все заказы от'|t}{/if} {$userProfile->username|stripslashes} ({if $workerOrdersCount > 0}{$workerOrdersCount}{else}{$payerOrdersCount}{/if})
									</a>
								</div>
							{/if}
						</div>
						<div>
							<div class="m-visible mt15">
								{if $isWorker}
									<a class="btn GreenBtnStyle user-send-button"
									   href="{getAbsoluteURL("/settings")}">
										{'Изменить профиль и настройки'|t}
									</a>
								{elseif $privateMessageStatus === true}
									<button type="button"
											class="btn GreenBtnStyle fw600 user-send-button {$sendMessageClass}" touch-action="manipulation">
										{'Отправить сообщение'|t}
										{if $allowCustomRequest}
											<span>{'или индивидуальный заказ'|t}</span>
										{/if}
									</button>
								{/if}
							</div>
						</div>
						<div class="user-info">
							{if $userProfile->city_id || $userProfile->country_id}
								{insert name=city_id_to_name value=a assign=userlocation id=$userProfile->city_id countryId=$userProfile->country_id}
								<div class="location">
									{$userlocation}
								</div>
							{/if}
							<div class="joined">
								{'На сайте с'|t}  {$userProfile->addtime|date_format}
							</div>
							{insert name=is_online assign=isOnline value=a userid=$userProfile->USERID}
							{if $isOnline}
								<div class="colorGreen">
									<span class="dot-user-status dot-user-online"></span>
									{'Онлайн'|t}
								</div>
							{else}
								<div class="status">
									<span class="dot-user-status dot-user-offline_dark"></span> {'Оффлайн'|t} <span class="fs13">
										({$userProfile->getLastOnline()|timeLeft:false:false})
									</span>
								</div>
							{/if}
						</div>
					</div>
					<div class="user-col-about">
						{if $fullName}
						<div class="user-fullname m-hidden mt10">{$fullName} {* {if $firstLetterFromLastName}{$firstLetterFromLastName}{/if} *}</div>
						{/if}
						{if $profession}
							<div class="user-profession m-hidden mt10">{$profession}</div>
						{/if}
						<div class="user-about-me mt15 f15 m-text-left word-break-word">
							{if !$description}
								{'Пользователь не написал о себе'|t}
							{else}
								{$description|stripslashes|nl2br}
							{/if}
						</div>
					</div>
				</div>

				<div class="user-col user-col-right m-hidden bodybg">
					<div class="user-col-right-min-width"></div>
					{if $isWorker}
						<a class="mb25 btn GreenBtnStyle user-send-button user-send-button_f18"
						   href="{getAbsoluteURL("/settings")}">
							{'Изменить профиль и настройки'|t}
						</a>
					{elseif $privateMessageStatus === true}
						<button type="button"
								class="mb25 btn GreenBtnStyle fw600 user-send-button {$sendMessageClass}" touch-action="manipulation">
							{'Отправить сообщение'|t}
							{if $allowCustomRequest}
								<span>{'или индивидуальный заказ'|t}</span>
							{/if}
						</button>
					{/if}
					{if $userProfile->order_done_count > 0}
						<div class="user-rating fw600 f15 text-nowrap">
							<div class="w47 mr7 t-align-r dib">
								{include file="rating_stars_inline.tpl" rating=$userProfile->cache_rating}
							</div>
							{$userProfile->getLevelAsText()}
						</div>
						<div class="user-statistics mt10 f15 fw400">
							<div class="orders-done text-nowrap">
								<span class="dib w47 t-align-r mr7 colorGreen fw600 f18">
									{$userProfile->order_done_count}
								</span>{declension count=$userProfile->order_done_count form1="{'заказ выполнен'|t}" form2="{'заказа выполнено'|t}" form5="{'заказов выполнено'|t}"}
							</div>
							<div class="responses-left mt10 text-nowrap">
								<span class="dib w47 t-align-r mr7 colorGreen fw600 f18">
									{$totalReviewsCount}
								</span>{declension count=$totalReviewsCount form1="{'отзыв получен'|t}" form2="{'отзыва получено'|t}" form5="{'отзывов получено'|t}"}
							</div>
							<div class="orders-statistics">
								<div class="position-r text-nowrap mt10">
									<span class="dib w47 t-align-r mr7 colorGreen fw600 f18">
										{$userProfile->data->order_done_persent|round}%
									</span>
									<span class="dib">{'заказов успешно сдано'|t}</span>
									<span
										class="tooltipster kwork-icon icon-custom-help icon-custom-help_size-18 ml5"
										data-tooltip-text="{'Процент повышается, когда продавец успешно выполняет заказы. Понижается, когда он отказывается от заказов по неуважительным причинам, или получает отрицательный отзыв.'|t}"
										data-tooltip-theme="dark"
									/>
								</div>
								<div class="mt10 text-nowrap">
									<span class="dib w47 t-align-r mr7 colorGreen fw600 f18">
										{$userProfile->data->order_done_intime_persent|round}%
									</span>{'заказов сдано вовремя'|t}</div>
								<div class="mt10 text-nowrap">
									<span class="dib w47 t-align-r mr7 colorGreen fw600 f18">
										{$userProfile->data->order_done_repeat_persent|round}%
									</span>{'повторных заказов'|t}
								</div>
							</div>
						</div>
					{/if}
					{if $showPayerLevel}
						{if $userProfile->order_done_count > 0}
							{include file="user/small_tooltip.tpl" class="user-level-tooltip" level=$userProfile->data->payer_level super=$userProfile->data->is_super_payer}
							<div class="payer-level mt10 fw600 f15 d-flex justify-content-start align-items-center tooltipster" data-tooltip-content=".user-level-tooltip" data-tooltip-destroy="true" data-tooltip-side="bottom">
								<div class="w47 dib d-flex justify-content-end mr7">
									<div class="{if $userProfile->data->is_super_payer}t-align-c-i{/if}">
										<div class="payer-level-icon mx-auto"
											 title="{$userProfile->data->getPayerLevelTooltipLabel()}">
											{$userProfile->data->payer_level}
										</div>
										{if $userProfile->data->is_super_payer}
											<div class="super-payer-icon-label">{'супер'|t}</div>
										{/if}
									</div>
								</div>
								<span class="dib text-nowrap">
									{$userProfile->data->getPayerLevelLabel()|nl2br}
								</span>
							</div>
						{else}
							{include file="user/small_tooltip.tpl" class="user-level-tooltip" level=$userProfile->data->payer_level super=$userProfile->data->is_super_payer}
							<div class="payer-level mt10 fw600 f15 d-flex justify-content-center align-items-center tooltipster" data-tooltip-content=".user-level-tooltip" data-tooltip-destroy="true" data-tooltip-side="bottom">
								<div class="dib mr7">
									<div class="{if $userProfile->data->is_super_payer}t-align-c-i{/if}">
										<div class="payer-level-icon mx-auto"
											 title="{$userProfile->data->getPayerLevelTooltipLabel()}">
											{$userProfile->data->payer_level}
										</div>
										{if $userProfile->data->is_super_payer}
											<div class="super-payer-icon-label">{'супер'|t}</div>
										{/if}
									</div>
								</div>
								<span class="dib text-nowrap">
									{$userProfile->data->getPayerLevelLabel()|nl2br}
								</span>
							</div>
						{/if}
					{/if}
					{if $workerOrdersCount > 0 || $payerOrdersCount > 0}
						<div class="{if $userProfile->order_done_count > 0}ml54{else}ta-center{/if}{if $userProfile->order_done_count > 0 || $showPayerLevel} mt10{/if}">
							<a href="{$baseurl}/{if $workerOrdersCount > 0}orders{else}manage_orders{/if}?s=all&filter_user_id={$userProfile->USERID}">
								{if $workerOrdersCount > 0}{'Все заказы с'|t}{else}{'Все заказы от'|t}{/if} {$userProfile->username|stripslashes} ({if $workerOrdersCount > 0}{$workerOrdersCount}{else}{$payerOrdersCount}{/if})
							</a>
						</div>
					{/if}
				</div>
			</div>
		</div>
	</div>
{/strip}