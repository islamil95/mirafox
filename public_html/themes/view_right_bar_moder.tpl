<style>
	.wrap_blocks_moder .block_moder select.block_select{
		width:100%;
		max-width:100%;
	}
	.block_moder .block_moder_title{
		font-weight: bold;
		padding-bottom: 15px;
	}
	.sub_reason_error{
		color:red;
	}
	.wrap_reason .sub_reasons{
		padding-left: 25px;
	}
</style>
<!-- Trumbowyg WYSIWYG -->
{Helper::printJsFile("/trumbowyg/trumbowyg.min.js"|cdnBaseUrl)}
{Helper::printJsFile("/trumbowyg/langs/ru.min.js"|cdnBaseUrl)}
{Helper::printCssFile("/trumbowyg/ui/trumbowyg.min.css"|cdnBaseUrl)}
{Helper::printJsFile("/trumbowyg/plugins/colors/trumbowyg.colors.min.js"|cdnBaseUrl)}
{Helper::printCssFile("/trumbowyg/plugins/colors/ui/trumbowyg.colors.min.css"|cdnBaseUrl)}
<!-- !Trumbowyg WYSIWYG -->

<div class="wrap_blocks_moder">
	<form id="decide_moder_action" action="/moder_kwork/decide" method="POST">
		<input type="hidden" name="entity" value="kwork" />
		<input type="hidden" name="lang" value="{Translations::getLang()}" id="lang" />
		<input type="hidden" name="id" value="{$kwork.PID}" />
		<div class="gray-bg-border p15-20 clearfix f14 font-OpenSans m-hidden block_moder block_title_moder">
			<div class="block_moder_title">{'Заголовок'|t}</div>
			{include file='view_right_bar_moder_block.tpl' reeanList=$moderationReasons[ModerManager::REASON_TYPE_TITLE]}
		</div>

		<div class="gray-bg-border p15-20 mt20 clearfix f14 font-OpenSans m-hidden block_moder block_category_moder">
			<div class="block_moder_title">{'Категория - Подкатегория'|t}</div>
			{include file='view_right_bar_moder_block.tpl' reeanList=$moderationReasons[ModerManager::REASON_TYPE_CATEGORY]}
		</div>

		{if $showAllData || !$isPostModeration || $patchPhoto || $patchExtPhotos || $patchYoutube}
			<div class="gray-bg-border p15-20 mt20 clearfix f14 font-OpenSans m-hidden block_moder block_img_moder">
				<div class="block_moder_title"{'>Изображение / видео'|t}</div>
				{include file='view_right_bar_moder_block.tpl' reeanList=$moderationReasons[ModerManager::REASON_TYPE_IMG]}
			</div>
		{/if}

		<div class="gray-bg-border p15-20 mt20 clearfix f14 font-OpenSans m-hidden block_moder block_title_moder">
			<div class="block_moder_title">{'Общее'|t}</div>
			{include file='view_right_bar_moder_block.tpl' reeanList=$moderationReasons[ModerManager::REASON_TYPE_GENERAL]}
		</div>

		{if $kwork.is_package!=0}
			<div class="gray-bg-border p15-20 mt20 clearfix f14 font-OpenSans m-hidden block_moder block_package_moder">
				<div class="block_moder_title">{'Параметры в пакетных кворках'|t}</div>
				{include file='view_right_bar_moder_block.tpl' reeanList=$moderationReasons[ModerManager::REASON_TYPE_PACKAGE]}
			</div>
		{/if}

		{if $showAllData || !$isPostModeration || $patchDesc || $patchDescFiles}
			<div class="gray-bg-border p15-20 mt20 clearfix f14 font-OpenSans m-hidden block_moder block_description_moder">
				<div class="block_moder_title">{'Описание кворка'|t}</div>
				{include file='view_right_bar_moder_block.tpl' reeanList=$moderationReasons[ModerManager::REASON_TYPE_DESCRIPTION]}
			</div>
		{/if}

		{if $showAllData || !$isPostModeration || $patchInstruction || $patchInstructionFiles}
			<div class="gray-bg-border p15-20 mt20 clearfix f14 font-OpenSans m-hidden block_moder block_requiredinfo_moder">
				<div class="block_moder_title">{'Инструкция для покупателя'|t}</div>
				{include file='view_right_bar_moder_block.tpl' reeanList=$moderationReasons[ModerManager::REASON_TYPE_REQUIREDINFO]}
			</div>
		{/if}

		<div class="gray-bg-border p15-20 mt20 clearfix f14 font-OpenSans m-hidden block_moder block_volume_moder">
			<div class="block_moder_title">{'Объем кворка'|t}</div>
			{include file='view_right_bar_moder_block.tpl' reeanList=$moderationReasons[ModerManager::REASON_TYPE_VOLUME]}
		</div>

		<div class="gray-bg-border p15-20 mt20 clearfix f14 font-OpenSans m-hidden block_moder block_category_moder upload-demofile {if !$isNeedUploadDemo}hidden{/if}">
			<div class="block_moder_title">{'Демо-отчет'|t}</div>
			{include file='view_right_bar_moder_block.tpl' reeanList=$moderationReasons[ModerManager::REASON_TYPE_DEMOREPORT]}
		</div>

		{insert name=get_extras value=a assign=options PID=$kwork.PID}
		{if $options|@count GT 0}
			<div class="gray-bg-border p15-20 mt20 clearfix f14 font-OpenSans m-hidden block_moder block_extra_moder">
				<div class="block_moder_title">{'Опции'|t}</div>
				{include file='view_right_bar_moder_block.tpl' reeanList=$moderationReasons[ModerManager::REASON_TYPE_EXTRA]}
			</div>
		{/if}

	</form>
</div>
<div class="base_category_list hidden">
	{insert name=get_categories2 assign=categoryInfo type=3 withAdditionalData=true}
	<select class="select-styled select-styled--thin font-OpenSans f15 w320 parents db mt10 w100p" autocomplete="off">
		{foreach from=$categoryInfo key=parentId item=parentCat}
			<option value="{$parentId}">{$parentCat->name}</option>
		{/foreach}
	</select>
	{foreach from=$categoryInfo key=parentId item=parentCat}
		{if $parentCat->cats}
			<select class="select-styled select-styled--thin font-OpenSans f14 w320 childs hidden db mt10 w100p gig_categories" id="sub_category_{$parentId}" autocomplete="off">
				{foreach from=$parentCat->cats key=id item=cat}
					<option value="{$cat->id}"
						data-attr-required="{$cat->required}"
						data-free-price="{$cat->is_package_free_price}"
						{if !is_null($cat->volume_type_id)}
							data-volume="{$cat->volume_type_id}"
							data-base-volume="{$cat->base_volume}"
							data-volume-names="{$cat->volume_names}"
						{/if}
					>
						{$cat->name}
					</option>
				{/foreach}
			</select>
		{/if}
	{/foreach}
</div>
<div class="base_section_list hidden">
	{foreach $categorySections as $categorySection}
		<span class="dib pr10">
			<label><input type="radio" name="kwork_section" value="{$categorySection->id}" /> {$categorySection->tag}</label>
		</span>
	{/foreach}
</div>
<script>
	var moderKworkId = '{$kwork.PID}';
	var isPackage = _isPackage = {$kwork['is_package']};
	var selectedCategoryId = '{$kwork.category}';
	var packageCategoryReasonId ={ModerManager::getPackageKworkCategoryReasonId()};
	var packageSectionReasonId ={ModerManager::PACKAGE_SECTION_RID};
	var attributeVisibility = '{Attribute\AttributeManager::VISIBILITY_LEVEL_SELLERS}';

	{literal}
	var selectedAttributesIds = {/literal}{if $selectedAttributesIds}{$selectedAttributesIds|@json_encode:JSON_NUMERIC_CHECK}{else}[]{/if}{literal};
	var attributesTree = {/literal}{if $attributesTree}{$attributesTree|@json_encode:JSON_NUMERIC_CHECK}{else}[]{/if}{literal};
	{/literal}
</script>
