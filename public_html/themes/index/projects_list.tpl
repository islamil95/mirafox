{Helper::printCssFile("/css/dist/manage-projects.css"|cdnBaseUrl)}
{Helper::printJsFile("/js/dist/manage-projects.js"|cdnBaseUrl)}
<div class="lg-centerwrap centerwrap pt20 pb-md-4 pt-md-4">
	<h1 class="t-align-c pt15 m-fs22 pb8">{'Мои проекты на бирже'|t}</h1>
    {if $wantsCount > 0}
		<div class="projects-list">
			<table class="table-style projects-list__table">
				<thead>
				<tr>
					<th><strong>{'Проект'|t}</strong></th>
					<th><strong>{'Цена до'|t}</strong></th>
					<th><strong>{'Предложения'|t}</strong></th>
					<th><strong>{'Заказы'|t}</strong></th>
					<th class="projects-list__table-th-mobile"><strong>{'Статус'|t}</strong></th>
					<th class="projects-list__table-th-mobile"><strong>{'Управлять'|t}</strong></th>
				</tr>
				</thead>
				<tbody>
                {foreach $wants as $want}
                    {* шаблон вывода заказов покупателя *}
                    {include file="index/project_list/project_item.tpl"}
                {/foreach}
				</tbody>
			</table>
			<div class="mb20">
				<a href="{$baseurl}/new_project" class="js-sms-verification-action mt20 hugeGreenBtn GreenBtnStyle h50 pull-reset mw300px mAuto">
                    {'Добавить проект'|t}
				</a>
			</div>
		</div>
    {else}
		<div class="empty-project-wrapper bgLightGray border-dgray p10 mb20">
			<a href="{$baseurl}/new_project" class="js-sms-verification-action empty-project-wrapper__btn green-btn btn--big mr10 dibi">
                {'Добавить проект'|t}
			</a>
			<span class="fw600 empty-project-wrapper__text">{'У вас нет проектов на бирже'|t}</span>
			<div class="tooltipster tooltip_circle tooltip_circle--light tooltip_circle--hover tooltipstered"
				 data-tooltip-content=".tooltip-content-my-projects">?
			</div>
			<div class="hidden">
				<div class="tooltip-content-my-projects">
					<p>{'На Kwork есть магазин и биржа.'|t}</p>
					<p>{'<strong>Магазин</strong> – это каталог кворков. Достаточно перейти на нужный кворк, нажать «Заказать», чтобы купить нужную услугу.'|t}</p>
					<p>{'<strong>Биржа</strong> – это раздел, где вы размещаете свое задание, а исполнители в ответ высылают вам предложения, как и по какой цене они будут решать вашу задачу. Останется только выбрать лучшее предложение.'|t}</p>
					<p>{'Магазин идеален для стандартных задач. Биржа незаменима для уникальных проектов.'|t}</p>
					<p class="fw700">{'Испытайте биржу Kwork. Добавьте свое задание прямо сейчас.'|t}</p>
				</div>
			</div>
		</div>
    {/if}
</div>