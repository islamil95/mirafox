{strip}
{if $track->type == "from_dialog"}
	<div class="step-block-order__dialog-toggle"
		 id="dialog-toggle">
		<a class="show-conversation link noselect">
					<span>
						{'Переписка, которая может относиться к заказу'|t}<img
								src="{'/arrow_right_blue.png'|cdnImageUrl}" width="9" alt="{"Развернуть"|t}"
								class="">
					</span>
		</a>
	</div>
{/if}
<div id="track-id-{$track->MID}" {if $config.track.isFocusGroupMember} data-type="{$track->type}" {/if}
	 class="{if $config.track.isFocusGroupMember}track--item__user-text {/if} tr-track{if $track->user_id == $actor->USERID} own{/if}"{if $author} data-user-id="{$author->USERID}"{/if}
	 data-track-id="{$track->MID}">
	<div class="{if $config.track.isFocusGroupMember} track--item {/if}  {if $track->isMessageThreshold()}nohover{/if} step-block-order_item text_message {if !$config.track.isFocusGroupMember}{if $track->type == "text" && $hasHidableConversation && !empty($track->getHiddenConversation())} hidable-message hide-by-conversation {elseif $track->getHide() && $track->type != "from_dialog"} hide{/if}{/if}{if $isUnread} unread{/if}{if $track->isRemovableByVirtual()} moder{/if}{if $isEditable} editable{/if}{if $track->type == "text" && $isEditable} removable{/if}{if $track->type == "from_dialog"} is-first-dialog-message{/if}{if $track->getHiddenConversation() == "end"} last-from-dialog{/if}  {$direction}"
		 data-track-id="{$track->MID}" data-time-create="{$track->date_create_unix()}"
		 data-message-quote-id="{$track->MID}">

        {if $config.track.isFocusGroupMember}
			<div class="track--item__sidebar">
				<div class="track--item__sidebar-image">
                    {include
                    file="user_avatar.tpl"
                    profilepicture={$author->profilepicture}
                    username=$author->username
                    size="medium"
                    }
				</div>
				<div class="track--item__sidebar-time">
                    {$track->date_create|date:"H:i"}
				</div>
			</div>
        {else}
			<div class="block-circle block-circle_gray pull-left">
                {include
                file="user_avatar.tpl"
                profilepicture={$author->profilepicture}
                username=$author->username
                size="medium"
                }
			</div>
			<div class="block-circle block-circle_gray pull-left">
				<img class="rounded" src="" alt="">
			</div>
        {/if}
        {if $config.track.isFocusGroupMember}
		<div class="track--item__main">
            {/if}
            {if $config.track.isFocusGroupMember}
				<div class="track--item__title">
					<h3 class="f15 bold d-flex align-items-center">
                        {if $isAuthorOnline}
							<i class="js-user-online-block dot-user-status dot-user-online mr7"
							   data-user-id="{$author->USERID}"></i>
                        {else}
							<i class="js-user-online-block dot-user-status dot-user-offline mr7"
							   data-user-id="{$author->USERID}"></i>
                        {/if}

                        {assign var="showProfileLink" value=(!$track->isArbiter() && $author->USERID)}
                        {if $showProfileLink}
						<a class="t-profile-link"
						   href="{absolute_url route="profile_view" params=["username" => $author->username|lower]}">
                            {/if}
                            {$author->username}
                            {if $showProfileLink}
						</a>
                        {/if}
					</h3>

					<div class="track--item__date  color-gray">
                        {if $track->date_update}
						<span class="tooltipster" data-tooltip-side="bottom" data-tooltip-theme="light"
							  data-tooltip-interactive="false"
							  data-tooltip-text="<p>{$track->date_create|date}</p><p>{"Изменено"|t}: {$track->date_update|date}</p>">{"изменено"|t}</span>
                        {/if}&nbsp;
                        {$track->date_create|date:"H:i"}
					</div>
				</div>
            {else}
				<div class="ml80">
					<div class="f14 color-gray mt3 floatright{if $track->date_update} tooltipster{/if}"
                            {if $track->date_update}
						data-tooltip-side="bottom" data-tooltip-theme="light" data-tooltip-interactive="false" data-tooltip-text="<p>{$track->date_create|date}</p><p>{"Изменено"|t}: {$track->date_update|date}</p>"
                            {/if}>
                        {if $track->date_update}
                            {"изменено"|t}
                        {/if}&nbsp;
                        {$track->date_create|date}
					</div>
					<div class="f16 online-status">
                        {if $isAuthorOnline}
							<i class="js-online-icon dot-user-status dot-user-online"
							   data-user-id="{$author->USERID}"></i>
                        {else}
							<i class="js-online-icon dot-user-status dot-user-offline"
							   data-user-id="{$author->USERID}"></i>
                        {/if}
						<span class="js-track-username username f16">


					{if $track->isArbiter()}
                        {'Арбитр'|t}&nbsp;
                    {/if}
                            {assign var="showProfileLink" value=(!$track->isArbiter() && $author->USERID)}
                            {if $showProfileLink}<a class="t-profile-link"
													href="{absolute_url route="profile_view" params=["username" => $author->username|lower]}">{/if}{$author->username}{if $showProfileLink}</a>{/if}
				</span>
					</div>
				</div>
				<div class="clear"></div>
            {/if}
            {if $config.track.isFocusGroupMember}
			<div class="track--item__content">
                {/if}
                {if $track->quote}
                    {$message = $track->quote->message}
                    {if $message === ''}
                        {$message = ', '|implode:$track->quote->files->pluck('fname')->toArray()}
                    {/if}
					<div class="js-message-quote message-quote message-quote--write mt10 {if !$config.track.isFocusGroupMember}ml80i{/if}"
						 data-quote-id="{$track->quote->MID}">
						<div class="message-quote__tooltip tooltipster m-hidden"
							 data-tooltip-side="right"
							 data-tooltip-text="{'Нажмите, чтобы перейти к цитате'|t}"></div>
						<div class="message-quote__login">{$track->quote->author->username}</div>
						<div class="js-message-quote-text message-quote__text">
							<div>{$message|bbcode_kwork_url|stripslashes|nl2br|code_to_emoji}</div>
						</div>
                        {if !$config.track.isFocusGroupMember}
							<div class="js-message-quote-remove message-quote__remove"></div>
                        {/if}
					</div>
                {/if}
				<div class="step-block-order__text {if $config.track.isFocusGroupMember}d-flex flex-wrap {else} ml80 m-ml0 m-mt10 {/if}">
					<div class="js-track-text-message f15 breakwords pre-wrap {if $config.track.isFocusGroupMember}pr20{/if}">
                        {if $track->isArbiter() || $track->isModer()}
                            {$track->message|bbcode|stripslashes|code_to_emoji}
                        {else}
                            {$track->message|bbcode_kwork_url|stripslashes|code_to_emoji}
                        {/if}
					</div>
					<div class="track-files attached-images-area vi-container" data-json-files='{$jsonFiles}'>
                        {foreach $track->files as $file}

                        {* check is $file has an image extention *}
                        {assign var=fname value=$file->fname}
                        {assign var=isImageExtention value=
                        ($fname|strstr:".png") || ($fname|strstr:".jpg") ||
                        ($fname|strstr:".jpeg") || ($fname|strstr:".gif") ||
                        ($fname|strstr:".raw") || ($fname|strstr:".tif")
                        }

                        {* show attached image with miniature and image-modal *}
                        {if \Helper::isTrackTester() && $isImageExtention}
                        {if $file->status == \Model\File::STATUS_ACTIVE}
						<div class="track-files__header mb5 {if $file->isCollapsed()}track-files__header_folded{/if}"  data-hide="{$file->isCollapsed()}">
							<span class="js-track-file-name dib f13 color-gray mr5">{$file->fname}</span>
							<div class="track-files__dropdown-caret" onclick="toggleMiniatureImage(event, {$file->FID})">
								<div class="track-files__dropdown-caret__arrow"></div>
							</div>
						</div>
						<div class="track-files__item">
							<div
									class="track-files__image attached-image-img sizeable"
									data-zoom-always="true"
									data-src="{absolute_url route="file_download" params=["filePath" => $file->s,"fileName" => $file->fname]}"
							>
								<div class="track-files__image__wrapper">
                                    {if isAllowToUser($file->USERID)}
										<div class="track-files__remove"
											 onclick="deleteTrackFile($(this),{$file->FID});return false;">
											×
										</div>
                                    {/if}

                                    {* assign random integer for image-modal id *}
                                    {assign var=dateNowTs value=$smarty.now|intval}
                                    {assign var=randomInt value=100|mt_rand:300}
                                    {math assign=imageModalId equation="x / y - y" x=$dateNowTs y=$randomInt}
                                    {assign var=randomInt value=1|mt_rand:$randomInt}
                                    {math assign=imageModalId equation="x * y" x=$imageModalId y=$randomInt}
                                    {assign var=imageModalId value=$imageModalId|round:0}
									<img
											src="{absolute_url route="file_download" params=["filePath" => $file->s,"fileName" => $file->fname]}"
											alt=""
											onclick="showImageModal({$imageModalId})"
									>
                                    {* image modal *}
									<div class="track-image-modal" id="{$imageModalId}">
                                        {* image *}
										<div class="track-image-modal__wrapper">
											<img src="{absolute_url route="file_download" params=["filePath" => $file->s,"fileName" => $file->fname]}"
												 alt="image">
										</div>
                                        {* blur *}
										<div class="track-image-modal__blur"
											 onclick="closeImageModal()"></div>
                                        {* close button *}
										<div class="track-image-modal__close"
											 onclick="closeImageModal()"></div>
									</div>

								</div>

							</div>
						</div>
                            {else}
							<div><i class="ico-file-{$ico} dib v-align-m"></i><span
										class="v-align-m ml10 mw80p">{'Срок хранения файла истек'|t}</span>
							</div>
                            {/if}

                            {else}
                            {insert name=get_file_ico value=a assign=ico filename=$file->fname}
                            {if $file->status == \Model\File::STATUS_ACTIVE}
								<a href="{absolute_url route="file_download" params=["filePath" => $file->s,"fileName" => $file->fname]}"
								   target="_blank"
								   class="dib color-text{if $retention_period_notice_count >= 0} js-popup-file{/if}"
								   style="width: calc(100% - 35px);">
									<i class="ico-file-{$ico} dib v-align-m"></i>
									<span class="js-track-file-name v-align-m ml10 mw80p dib">{$file->fname}</span>
								</a>
                                {if isAllowToUser($file->USERID)}
									<a href="" class="remove-file-link"
									   onclick="deleteTrackFile($(this),{$file->FID});return false;">
									</a>
                                {/if}
                            {else}
								<div><i class="ico-file-{$ico} dib v-align-m"></i><span
											class="v-align-m ml10 mw80p">{'Срок хранения файла истек'|t}</span>
								</div>
                            {/if}
                            {/if}

                            {/foreach}
						</div>
                        {include file="track/view/status-bar.tpl" track=$track}
                        {assign var="hasBudget" value=$track->inboxData->budget && $track->inboxData->currency_id}
                        {if $track->inbox && $track->inboxData && ($hasBudget || $track->inboxData->duration)}
							<div class="mt30">
                                {if $hasBudget}
									<div class="fw600">
                                        {'Бюджет'|t}:&nbsp;
										<span>
                                {include file="utils/currency.tpl" currencyId=$track->inboxData->currency_id total=$track->inboxData->budget}
							</span>
									</div>
                                {/if}
                                {if $track->inboxData->duration}
									<div class="fw600">
                                        {'Срок (дней)'|t}:&nbsp;
										<span>
								{insert name=countdown_short value=a assign=timeLeftDialog time=$track->inboxData->duration type="duration"}
                                            {$timeLeftDialog}
							</span>
									</div>
                                {/if}
							</div>
                        {/if}
					</div>
					<div class="step-block-order__wrap-edit-form  {if !$config.track.isFocusGroupMember} ml80 {/if} m-ml0 m-mt10"></div>
					{if !$config.track.isFocusGroupMember}
						{if $track->type == "text" && $hasHidableConversation && $track->getHiddenConversation() == "end"}
							<div class="hide-conversation link-color" data-order_id="{$order->OID}">
								{'Свернуть переписку'|t}
								<img src="{"/arrow_right_blue.png"|cdnImageUrl}"
									 class="rotate180" width="9"
									 alt="{"Свернуть"|t}">
							</div>
						{/if}
                    {/if}

                    {if $track->isMessageThreshold()}
                        {include file="track/view/loyality/loyality_message.tpl" loyalityVisible=true}
                    {/if}
                    {if $config.track.isFocusGroupMember}
				</div>
			</div>
            {/if}
		</div>
		{if $config.track.isFocusGroupMember}
			{if $track->type == "text" && $hasHidableConversation && $track->getHiddenConversation() == "end"}
				<div class="hide-conversation link-color" data-order_id="{$order->OID}">
					{'Свернуть переписку'|t}
					<img src="{"/arrow_right_blue.png"|cdnImageUrl}"
						 class="rotate180" width="9"
						 alt="{"Свернуть"|t}">
				</div>
			{/if}
		{/if}
        {block name="additional"}
        {/block}
	</div>
    {/strip}
