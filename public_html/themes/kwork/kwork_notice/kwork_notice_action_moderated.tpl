{strip}
	<div class="wrap-moderation-block">
		<div class="gray-bg-border clearfix mb20">
			<div class="contentArea mb0 pb0" style="width: 100px;">
				<div class="p15-20 sm-text-center" style="padding-right: 0px;">
					<div class="block-circle block-circle-60 block-circle_orange dib v-align-m"><i class="ico-info"></i></div>

				</div>            
			</div>
			<div class="floatleft m-text-center pt20" style="width: 850px">
				<div class="pull-reset clearfix">
					{if !empty($lastModerationKwork)}<button class="hugeGreenBtn OrangeBtnStyle h50 mr20 done-button" onclick="location = '/moder_kwork/goto_back'">{'Назад'|t}</button>{/if}
					<button class="hugeGreenBtn OrangeBtnStyle h50 mr20 done-button" onclick="location = '/moder_kwork/skip?entity=kwork&id={$p.PID}'">{'Пропустить'|t}</button>

					<button class ="hugeGreenBtn GreenBtnStyle h50 done-button" id="moderAcceptKworkBtn" onclick="decideAction(this);" data-attr-required="{$isRequiredClassification}">{'Готово'|t}</button>
				</div>
				{if $lastModerationInfo['status']=='confirm'}
					<div class="mt10 font-OpenSans dib ">{'Последний раз активирован:'|t} {$lastModerationInfo['date_moder']|date}</div>
				{elseif $lastModerationInfo['status']=='reject'}
					<div class="mt10 font-OpenSans dib ">
						{'Последний раз отклонен:'|t} {$lastModerationInfo['date_moder']|date}, {'по причинам:'|t} 
						{assign var="lastReasonsInfoEnd" value=end($lastReasonsInfo)}
						{foreach $lastReasonsInfo as $reason}
							<span> {$reason['name']}{if $reason['id']!=$lastReasonsInfoEnd['id']},{/if}</span>
						{/foreach}
					</div>
				{/if}
			</div>
		</div>
	</div>
{/strip}