{strip}
	{*Шаблон с рекурсивными вызовом. strip не удалять - все сломается*}
	{foreach $attributesTree as $attribute}
		{assign var=firstSelectedAttribute value=$selectedAttributes.0}
		{if $attribute->isClassification()}
			{* Классификации не отображаются в списке, только атрибуты, но если есть атрибуты в классификации готовим список*}
			{if $attribute->getChildren() && !$attribute->getIsUnembedded() && !$attribute->isAllowMultiple()}
				<ul class="sub_cat_list {if $selectedAttributes}
						{if !in_array($attribute->getId(), $selectedAttributeParentsIds) && !$firstSelectedAttribute->getParentId()}
							{* Если атрибут выбран то отображаем только те списки которые находятся в списке родителей выбранного атрибута*}
							hide
						{elseif in_array($attribute->getId(), $selectedAttributeParentsIds) && !($firstSelectedAttribute->getParentId() == $attribute->getId() && (!$firstSelectedAttribute->getChildren() && $attribute->isClassification()))}
							{* Для нужных атрибутов поставляем класс has-sub чтобы убрать padding *}
							has-sub
						{/if}
					{else}
						{* Если атрибут не выбран то нужно отобразить только классификации верхнего уровня, остальное скрыть*}
						{if $attribute->getParentId()}
							hide
						{/if}
					{/if}">
					{include file="search/inc/attributes_list.tpl" attributesTree=$attribute->getChildren() parentAttributeId=$attribute->getParentId()}
				</ul>
				{* Отображаем только одну классификацию - первую по сортировке *}
				{break}
			{/if}
		{else}
			{* Атрибуты же отображаем в качестве элементов списка *}
			<li class="subcats {if $firstSelectedAttribute}
					{* Если выбран атрибут *}
					{if in_array($attribute->getId(), $selectedAttributeParentsIds)}
						{* Если текущий атрибут среди идентификаторов родителей выбранного атрибута то показываем со стрелочкой его *}
						arrow-show
					{elseif in_array($attribute->getId(), $selectedAttributesIds)}
						{* Если текущий атрибут - выбранный - показываем его жирным *}
						active
					{elseif $attribute->getParentId() != $firstSelectedAttribute->getParentId() && !in_array($parentAttributeId, $selectedAttributesIds)}
						{* Если у текущего атрибута идентификатор родителя не такойже как у выбранного атрибута *}
						hide
					{elseif $firstSelectedAttribute->getParentId() == $attribute->getParentId() && $attribute->isClassification()}
						{* Если у текущего атрибута такойже родительский атрибут как у выбранного и выбранного атрибута есть потомки - скрываем *}
						hide
					{/if}
					{* остальные показываются как обычно *}
				{/if}
				"
			>
				<a class="f13 subId" data-id="{$attribute->getId()}" data-alias="{$attribute->getAlias()}" href="javascript: void(0);">
					<span class="arrow {if !$firstSelectedAttribute || !$attribute->getChildren() || !in_array($attribute->getId(), array_merge($selectedAttributeParentsIds, $selectedAttributesIds)) || $attribute->isAllChildrenUnembedded()}hide{/if}"></span>
					<span class="left-filters-attr-title">
						{$attribute->getTitle()|stripslashes}
					</span>
				</a>
				{if $attribute->getChildren()}
					{* Потомком атрибута может быть только классификация по этом подключаем сам шаблон без ul*}
					{include file="search/inc/attributes_list.tpl" attributesTree=$attribute->getChildren()}
				{/if}
			</li>
		{/if}
	{/foreach}
{/strip}