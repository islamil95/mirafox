function setCustomBlockPosition(leftBlockObj, rightBlockObj) {
	if (leftBlockObj.length != 1 || rightBlockObj.length != 1) {
		return false;
	}
	var blockTop = $(leftBlockObj).position();
	var currentBlockDesc = $(rightBlockObj).position();
	var currentBlockMargin = parseInt($(rightBlockObj).css("margin-top"));
	/*
	var addBlockMargin = parseInt(blockTop.top - currentBlockDesc.top - currentBlockMargin);
	if (addBlockMargin > 20) {
		$(rightBlockObj).attr('style', "margin-top: " + addBlockMargin + "px !important");
	}
	*/
}

function setBlockPositions() {
	setCustomBlockPosition($('.contentArea .kwork-title'), $('.sidebarArea .block_title_moder'));
	setCustomBlockPosition($('.contentArea .kwork_slider_desktop'), $('.sidebarArea .block_img_moder'));
	setCustomBlockPosition($('.contentArea .package_cards_'), $('.sidebarArea .block_package_moder'));
	setCustomBlockPosition($('.contentArea .b-about-text'), $('.sidebarArea .block_description_moder'));
	setCustomBlockPosition($('.contentArea .requiredInfo-block'), $('.sidebarArea .block_requiredinfo_moder'));
	setCustomBlockPosition($('.contentArea .kwork-volume'), $('.sidebarArea .block_volume_moder'));
	setCustomBlockPosition($('.contentArea .order-extras'), $('.sidebarArea .block_extra_moder'));
}

function setDoneButtonColor() {
	var hasNoEmpty = false;
	$('.wrap_blocks_moder .block_moder select').each(function (obj) {
		if ($(this).val() != '') {
			hasNoEmpty = true;
		}
	});
	if ($('.wrap_blocks_moder .block_moder input:checked').length > 0) {
		hasNoEmpty = true;
	}
	if (hasNoEmpty) {
		$('.wrap-moderation-block .done-button').removeClass('GreenBtnStyle').addClass('RedBtnStyle');
	}
	else {
		$('.wrap-moderation-block .done-button').addClass('GreenBtnStyle').removeClass('RedBtnStyle');
	}
}

var kworkEditorFields = kworkEditorFields || {};

kworkEditorFields._selectors = {
    classification: {
        chainWrap: '.kwork-editor_category',
		attributesWrap: '.attribute-list',
        holder: '.classification-holder',
        block: '.kwork-save-step__field-block',
        blockLabel: '.kwork-save-step__field-label',
        blockContent: '.kwork-save-step__field-value',
        nestedElementsBlock: '.js-attribute-section-block',
        radioButton: '.js-bindable-attribute',
		checkbox: '.classification-checkbox',
        element: '.gig_categories',
        errorMessage: '.classification-error-message',
        addCustomAttribute: '.js-add-custom-attribute-btn',
	},
    lang: '#lang',
};

kworkEditorFields.getEditBlockHtml = function (type, setValue, itemId) {
	switch (type) {
		case 'title':
			setValue = setValue.replace(/<span.+?>(.+?)<\/span>/g, '$1');
			return {
				block: '<div class="pb10"><textarea id="edit_title" class="js-field-input kwork-save-step__field-input kwork-save-step__field-input_textarea kwork-save-step__field-input_name input"></textarea></div>\n\
							<div align="right"><input type="button" onclick="kworkEditorFields.setNewTitle();" value="OK" class="white-btn" /></div>',
				value: setValue
			};
			break;

		case 'description':
			setValue = setValue.replace(/<span.*?>(.*?)<\/span>/g, '$1');
			return {
				block: '<div><textarea id="edit-description" class="js-field-input js-field-input-description kwork-save-step__field-input kwork-save-step__field-input_textarea kwork-save-step__field-input_description input"></textarea></div>\n\
							<div align="right"><input type="button" onclick="kworkEditorFields.setNewDescription();" value="OK" class="white-btn" /></div>\n\<script>\n\$("#edit-description").trumbowyg({lang: "ru", fullscreenable: false,closable: false, btns: ["bold", "|", "italic", "|", "orderedList"], removeformatPasted: true });</scr' + 'ipt>',
				value: setValue
			};
			break;

		case 'category':
			return {
				block: $('.base_category_list').html() + '<input type="button" onclick="kworkEditorFields.setNewCategory();" class="white-btn" value="OK" />',
				value: ''
			};
			break;

		case 'volume':
			var widthClass = "w100p";

			if (requiredVolumeTypeId) {
				if (currentVolumeSize) {
					setValue = currentVolumeSize;
				}
				if (!setValue) {
					setValue = "";
				}
				widthClass = "w30p";
			} else {
				setValue = setValue.replace(/"/g, "&quot;");
			}
			return {
				block: ''
					+ '<div class="pb10">'
						+ '<div class="input input_size_s js-field-input mh30" contenteditable="true" id="edit_volume">' + setValue + '</div>'
				 		+ '<div id="js-volume-type-block" class="dib v-align-m"></div>'
					+ '</div>'
					+ '<div align="right"><input type="button" onclick="kworkEditorFields.setNewVolume();" value="OK" class="white-btn" /></div>',
				value: setValue
			};
			break;

		case 'requiredinfo':
			return {
				block: '<div class="pb10"><textarea id="edit_requiredinfo" class="js-field-input kwork-save-step__field-input kwork-save-step__field-input_textarea kwork-save-step__field-input_instruction input"></textarea></div>\n\
							<div align="right"><input type="button" onclick="kworkEditorFields.setNewRequiredInfo();" value="OK" class="white-btn"/></div>\n\<script>\n\$("#edit_requiredinfo").trumbowyg({lang: "ru", fullscreenable: false,closable: false, btns: ["bold", "|", "italic", "|", "orderedList"], removeformatPasted: true });</scr' + 'ipt>',
				value: setValue
			};
			break;

		case 'packageCustom':
			var block = '<input maxlength="18" class="input kwork-save-step__field-input_package-item" id="package-custom-item-' + itemId + '" type="text"/>';
			block += '<input type="button" onclick="kworkEditorFields.setNewPackageCustomName(' + itemId + ');" class="white-btn lh20" value="OK" />';
			return {
				block: block,
				value: setValue
			};
			break;

		case 'packageCustomValue':
			var block = '<input maxlength="16" class="input kwork-save-step__field-input_package-item_value" id="package-custom-item-value-' + itemId + '" type="text"/>';
			block += '<input type="button" onclick="kworkEditorFields.setNewPackageCustomValue(' + itemId + ');" class="white-btn lh20" value="OK" />';
			return {
				block: block,
				value: setValue
			};
			break;
		case 'optionText':
			var block = '<input maxlength="80" class="input input_size_s kwork-save-step__field-input_option-item_text" id="option-item-text-' + itemId + '" type="text"/>';
			block += '<input type="button" onclick="kworkEditorFields.setNewOptionText(' + itemId + ');" class="white-btn" value="OK"/>';
			return {
				block: block,
				value: setValue
			};
			break;
		case "translates":
			var block = "<div class='bold'>Редактирование языков перевода</div>";
			return {
				block: block,
				value: setValue
			};
			break;
	}
	return '';
};

kworkEditorFields.changeBackBlock = function ($obj, type, html) {
	var wrapBlock = $($obj).parents('.kwork-editor_' + type);
	switch (type) {
		case 'title':
			$(wrapBlock).find('.base-content h1').text($($obj).val());
			break;

		case 'description':
			$(wrapBlock).find('.base-content .description-text-source').text($obj.val());
			$('img.edit-description').show();
			$(".description-text").html(html).show();
			var baseBlock = $(wrapBlock).find('.base-content').html();
			$(wrapBlock).replaceWith(baseBlock);
			return;

		case 'volume':
			var value = $($obj).text();
			if (window.currentVolumeTypeId) {
				value += " " + VolumeTypesModule.pluralVolumeById(window.currentVolumeTypeId, value);
			}
			$(wrapBlock).find('.base-content .kwork-volume-text').text(value);
			break;

		case 'requiredinfo':
			$(wrapBlock).find('.base-content .requiredInfo-text-source').text($obj.val());
			$('img.cur').show();
			$(".requiredInfo-text").html(html).show();
			var baseBlock = $(wrapBlock).find('.base-content').html();
			$(wrapBlock).replaceWith(baseBlock);
			break;

		case 'packageCustom':
			$(wrapBlock).find('.base-content .package-item-custom').text($obj.val());
			$(".package-item-custom").html(html).show();
			var baseBlock = $(wrapBlock).find('.base-content').html();
			$(wrapBlock).replaceWith(baseBlock);
			break;
		case 'packageCustomValue':
			$(wrapBlock).find('.base-content .package-item_type__custom__value__text').text($obj.val());
			$(".package-item_type__custom__value__text").html(html).show();
			var baseBlock = $(wrapBlock).find('.base-content').html();
			$(wrapBlock).replaceWith(baseBlock);
			break;
		case 'optionText':
			$(wrapBlock).find('.base-content .option-item__text').text($obj.val());
			$(".option-item__text").html(html).show();
			var baseBlock = $(wrapBlock).find('.base-content').html();
			$(wrapBlock).replaceWith(baseBlock);
			break;
	}
	var baseBlock = $(wrapBlock).find('.base-content').html();
	$(wrapBlock).replaceWith(baseBlock);
};

kworkEditorFields.setNewTitle = function () {
	var setValue = $('#edit_title').val();
	$.post('/moder_kwork/save', {
		'field': 'title',
		'value': setValue,
		'id': moderKworkId,
		'entity': 'kwork'
	}, function (answer) {
		if (answer.success) {
			kworkEditorFields.changeBackBlock($('#edit_title'), 'title');
			kworkEditorFields.addEditIconTitle();
			$('.kwork-title').removeClass('patch-border__yellow');
			$('#old-kwork-title').remove();
			kworkModerateSpellChecker.check('title');
		}
		else {
			if (typeof answer.error !== 'undefined' && answer.error.length > 0) {
				kworkEditorFields.showEditErrors(answer.error);
			}
			else {
				alert(t('Ошибка при сохранении'));
			}
		}
	}, 'JSON');
};

kworkEditorFields.setNewDescription = function () {
	var setValue = $('#edit-description').val();
	$.post('/moder_kwork/save', {
		'field': 'description',
		'value': setValue,
		'id': moderKworkId,
		'entity': 'kwork'
	}, function (answer) {
		if (answer.success) {
			kworkEditorFields.changeBackBlock($('#edit-description'), 'description', answer.html);
			kworkModerateSpellChecker.check('description');
		}
		else {
			if (typeof answer.error !== 'undefined' && answer.error.length > 0) {
				kworkEditorFields.showEditErrors(answer.error);
			}
			else {
				alert(t('Ошибка при сохранении'));
			}
		}
	}, 'JSON');
};

kworkEditorFields.setNewCategory = function (button) {
	var sl = kworkEditorFields._selectors;

	var parentCategory = $('.kwork-editor_category .parents').val();
	window.currentCategory = $('#sub_category_' + parentCategory).val();

	var breadcrumbsContent = $('<p class="breadcrumbs-content">' +
		$('.kwork-editor_category .parents option:selected').text() + ' &nbsp;&nbsp;>&nbsp;&nbsp; ' +
		$('#sub_category_' + parentCategory).find('option:selected').text() +
		'</p>');

	var $category = $('#sub_category_' + parentCategory).find('option:selected');

	var $classificationChecked = $(sl.classification.chainWrap)
		.find(sl.classification.holder)
		.find(sl.classification.blockContent + ':first').first()
		.find(sl.classification.radioButton + ':checked');
	var classificationsText = $classificationChecked.next().text();

	if (classificationsText.length) {
		classificationsText = ' &nbsp;&nbsp;>&nbsp;&nbsp; ' + classificationsText;
		breadcrumbsContent.append(classificationsText);
	}


	var activeSubCatSelect = $('.kwork-editor_category select.gig_categories:not(.hidden)'),
		setValue = activeSubCatSelect.val(),
		attrRequired = activeSubCatSelect.find(':selected').attr('data-attr-required');

	var parentId=$('.kwork-editor_category .parents').val();
	$('.bread-crump').data('parent', parentId).data('cat', setValue);
	otherListUpdate();

	// скрываем/открываем блок с демо-отчетом в зависимости от выбранной классификации
	if (1 === $classificationChecked.data('demoFileUpload')) {
		$('.kwork-report-demo-block').css('display', 'block');
	} else {
		$('.kwork-report-demo-block').css('display', 'none');
	}

	// Попробуем поискать необходимый тип числоого объема в категории
	window.requiredVolumeTypeId = CategoriesVolumeTypesModule.getRequiredVolumeType(currentCategory);
	window.allowedAdditionalVolumeTypesIds = CategoriesVolumeTypesModule.getAdditionalVolumeTypes(currentCategory);

	var classificationItems = $(sl.classification.holder).find(sl.classification.radioButton + ',' +  sl.classification.checkbox);
	//Смотрим необходимость загружать демо-отчет в атрибуте и отображаем блок у модератора
	var needUploadDemoFile = false;
	classificationItems.each(function() {
		var attribute = $(this);
		if(attribute.data("demo-file-upload")  && attribute.prop("checked")){
			needUploadDemoFile = true;
		}
	});
	if(needUploadDemoFile){
		$(".upload-demofile").removeClass("hidden");
	}else{
		$(".upload-demofile").addClass("hidden");
		$(".upload-demofile input:checkbox").removeAttr("checked");
		setDoneButtonColor();
	}


	// Если в категории нет - поищем в атрибутах
	if(!window.requiredVolumeTypeId){
		classificationItems.each(function() {
			var attribute = $(this);
			if (attribute.is(':checked') && attribute.data('volumeTypeId')) {
				window.requiredVolumeTypeId = attribute.data('volumeTypeId');
				window.allowedAdditionalVolumeTypesIds = attribute.data('additionalVolumeTypesIds');
				return false;
			}
		});
	}

	// Применим к инпуту числового объема изменения
	window.volumeTypeInput.setRequiredVolumeTypeId(window.requiredVolumeTypeId);
	window.volumeTypeInput.setAllowedAdditionalVolumeTypesIds(window.allowedAdditionalVolumeTypesIds);
	window.volumeTypeInput.setToggle();

	$('.base-content').removeClass('hidden');
	if (setValue == 35 || setValue == 152) {
		if ($('#translates-data .kwrok-tranlation-language').length) {
			viewTranslates();
		} else {
			editTranslates()
		}
	} else {
		hideTranslates();
	}

	if(button){
		if (!checkForErrors()) {
			return;
		}

		showAlerts();

		var	attributesObject = {};
		classificationItems.each(function(index) {
			if ($(this).is(':checked')) {
				var parentId = $(this).closest(sl.classification.attributesWrap).data('parent-id');
				if ($(this).hasClass(sl.classification.checkbox.substring(1))) {
					if (!(parentId in attributesObject)) {
						attributesObject[parentId] = [];
					}
					attributesObject[parentId].push($(this).val());
				} else {
					attributesObject[parentId] = $(this).val();
				}
			}
		});

		var customAttributeTitleObject = {};
		$(kworkEditorFields._selectors.classification.nestedElementsBlock).find("input[name^='custom_attribute_title']").each(function () {
			var attributeId = $(this).parents(".attribute-item-custom").find(".classification-checkbox").val();
			customAttributeTitleObject[attributeId] = $(this).val();
		});

		var checkedReasons = [];
		$("input[name='reasons[]']:checked").each(function() {
			checkedReasons.push($(this).val());
		});

		$.post('/moder_kwork/save', {
			'field': 'category',
			'value': setValue,
			'id': moderKworkId,
			'entity': 'kwork',
			'attribute': attributesObject,
			'custom_attribute_title': customAttributeTitleObject,
			"checked_reasons": checkedReasons
		}, function (answer) {
			if (answer.success) {
				kworkEditorFields.changeBackBlock($('.kwork-editor_category select.gig_categories:visible'), 'category');
				$('.bread-crump')
					.removeClass('patch-border__yellow')
					.empty()
					.append(breadcrumbsContent);
				$('#old-kwork-category').remove();
				kworkEditorFields.addEditIconCategory();
				selectedCategoryId = setValue;
				$('#moderAcceptKworkBtn').attr('data-attr-required', attrRequired);

				if (typeof button.withoutDecision === 'undefined' || !button.withoutDecision) {
                    decideModerFormSubmit(button);
				}
			}
			else {
				if (typeof answer.error !== 'undefined' && answer.error.length > 0) {
					kworkEditorFields.showEditErrors(answer.error);
				}
				else {
					alert(t('Ошибка при сохранении'));
				}
			}
		}, 'JSON');

	}else{
        if (!checkForErrors()) {
            return;
        }

		showAlerts();

		var clsText = "";
		$('.bread-crump')
			.removeClass('patch-border__yellow')
			.empty()
			.append(breadcrumbsContent);
		kworkEditorFields.addEditIconCategory();
		$('.kwork-editor-block').hide();
		clsText = getAttributesList();
		var moderContainer = $("#classifications_moder_list");
		var moderContainerItem = '<div class="clearfix"><span class=""></span><div class="pull-right font-OpenSans f12 t-align-r classification-more-info" style="width: 80%"></div><hr class="gray"></div>'
		moderContainerItem = $(moderContainerItem);
		moderContainer.html("<br>");
		var moderList = $('div[data-input-name^="new_custom_attribute"]');
		moderList.each(function(index) {
			var itemBlock = $(this);
            var cloneItem = moderContainerItem.clone();
			if(itemBlock.hasClass('kwork-save-field-checkbox')){
				clsText = getAttributesList();
				cloneItem.find("span").text(itemBlock.parent().prev().find('label').text());
				cloneItem.find(".classification-more-info").text(clsText.replace(/^, |, $/g,''));
				moderContainer.append(cloneItem)
			}
			if(itemBlock.hasClass('kwork-save-field-radiobox')){
				if(index){
					cloneItem.find("span").text(itemBlock.parent().prev().find('label').text());
					cloneItem.find(".classification-more-info").text(itemBlock.find('.styled-radio:checked').next().text());
					moderContainer.append(cloneItem);
				}
			}
		});

		if(moderList.hasClass('kwork-save-field-checkbox')){
			clsText = getAttributesList();
		}
		moderContainer.show();
		$('.base-content').removeClass('hidden');
	}

	function getAttributesList() {
		var clsText = "";
		$('.kwork-save-field-checkbox .attribute-item-custom, .kwork-save-field-checkbox .attribute-item').each(function() {
			if ($(this).hasClass("attribute-item-custom")) {
				if ($(this).find("input:checked").length > 0) {
					clsText += $(this).find("input[type='text']").val() + ", ";
				}
			} else if ($(this).hasClass("attribute-item")) {
				if ($(this).find("input:checked").length > 0) {
					clsText += $(this).text() + ', ';
				}
			}
		});

		return clsText;
	}

	function checkForErrors() {
        var errors = {};
        $(sl.classification.block).each(function(index) {
            var isRequired = $(this).find(sl.classification.attributesWrap).data('required');
            var elementType = 'checkbox';
            if ($(this).find(sl.classification.radioButton).length) {
                elementType = 'radio';
            }

            switch (elementType) {
                case 'radio':
                    if (!$(this).find(sl.classification.radioButton + ':checked').length && isRequired) {
                        errors[index] = $(this);
                    }
                    break;
                case 'checkbox':
                    if (!$(this).find(sl.classification.checkbox + ':checked').length && isRequired) {
                        errors[index] = $(this);
                    }
                    break;
                default:
                    break;
            }
        });

        if (Object.keys(errors).length) {
            for (var key in errors) {
                if (errors.hasOwnProperty(key)) {
                    errors[key]
                        .find(sl.classification.errorMessage)
                        .remove();
                    errors[key]
                        .find(sl.classification.blockLabel)
                        .append('<p class="' + sl.classification.errorMessage.substring(1) + '">' + t('Поле обязательно для заполнения') + '</p>');
                }
            }
            return false;
        }

        return true;
	}

	function showAlerts() {
		if (kworkLang == 'ru' && price != 500 && !$category.data('free-price') && !button) {
			var isFreePrice = false;
			$(sl.classification.chainWrap)
				.find(sl.classification.holder)
				.find(sl.classification.radioButton + ':checked')
				.each(function () {
					if ($(this).data('is-free-price')) {
						isFreePrice = true;
						return false;
					}
				});
			if (!isFreePrice) {
				alert(t('В данном кворке включена свободная цена, однако вы выбираете категорию/классификацию без свободной цены. Цена будет сброшена до 500 руб. Проверьте корректность смены категории/классификации.'));
			}
		}

		if (requiredVolumeTypeId && !button && requiredVolumeTypeId != currentVolumeTypeId) {
			$('.kwork-volume .volume_error').remove();
			if ($('#kwork-volume-text').text() !== "") {
				$('.kwork-volume').prepend('<div class="volume_error">' + t('Объем услуги в кворке:')
					+ '&nbsp;' + $('#kwork-volume-text').text() + '</div>');
			}
			currentVolumeSize = "";
			$('#kwork-volume-text')
				.find('.spell-mistake').remove().end()
				.contents()
				.filter(function () {
					return this.nodeType == 3; // Node.TEXT_NODE
				}).remove();
			alert(t('Обязательно установите числовой объем кворка.'));
		}
	}

};

kworkEditorFields.setNewVolume = function () {
	var setValue = $('#edit_volume').text();
	window.currentVolumeTypeId = $('#step2-volume-type-id').val();

	if (window.currentVolumeTypeId) {
		window.currentVolumeSize = setValue;
	}

	$.post('/moder_kwork/save', {
		'field': 'volume',
		'value': setValue,
		'type': currentVolumeTypeId,
		'id': moderKworkId,
		'entity': 'kwork'
	}, function (answer) {
		if (answer.success) {
			kworkEditorFields.changeBackBlock($('#edit_volume'), 'volume');
			kworkEditorFields.addEditIconVolume();
			$('.kwork-volume-text').removeClass('patch-border__yellow');
			$('#old-kwork-work').remove();
			kworkModerateSpellChecker.check('volume');
		}
		else {
			if (typeof answer.error !== 'undefined' && answer.error.length > 0) {
				kworkEditorFields.showEditErrors(answer.error);
			}
			else {
				alert(t('Ошибка при сохранении'));
			}
		}
	}, 'JSON');
}

kworkEditorFields.setNewRequiredInfo = function () {
	var setValue = $('#edit_requiredinfo').val();
	$.post('/moder_kwork/save', {
		'field': 'requiredinfo',
		'value': setValue,
		'id': moderKworkId,
		'entity': 'kwork'
	}, function (answer) {
		if (answer.success) {
			kworkEditorFields.changeBackBlock($('#edit_requiredinfo'), 'requiredinfo', answer.html);
			kworkModerateSpellChecker.check('instruction');
		}
		else {
			if (typeof answer.error !== 'undefined' && answer.error.length > 0) {
				kworkEditorFields.showEditErrors(answer.error);
			}
			else {
				alert(t('Ошибка при сохранении'));
			}
		}
	}, 'JSON');
}

kworkEditorFields.setNewPackageCustomName = function (itemId) {
	var setValue = $('#package-custom-item-' + itemId).val();
	if (setValue.length > 18) {
		alert("Превышение длины 18 символов");
		return false;
	}
	$.post('/moder_kwork/save',
		{
			field: 'packageCustom',
			value: setValue,
			id: moderKworkId,
			entity: 'kwork',
			itemId: itemId
		}, function (answer) {
			if (answer.success) {
				kworkEditorFields.changeBackBlock($('#package-custom-item-' + itemId), 'packageCustom');
				kworkEditorFields.addEditIconPackageItemCustom(itemId);
			}
			else {
				if (typeof answer.error !== 'undefined' && answer.error.length > 0) {
					kworkEditorFields.showEditErrors(answer.error);
				}
				else {
					alert('Ошибка при сохранении');
				}
			}
		}, 'JSON');
};

kworkEditorFields.setNewPackageCustomValue = function (itemId) {
	var setValue = $('#package-custom-item-value-' + itemId).val();
	if (setValue.length > 16) {
		alert("Превышение длины 16 символов");
		return false;
	}
	$.post('/moder_kwork/save',
		{
			field: 'packageCustomValue',
			value: setValue,
			id: moderKworkId,
			entity: 'kwork',
			itemId: itemId
		}, function (answer) {
			if (answer.success) {
				kworkEditorFields.changeBackBlock($('#package-custom-item-value-' + itemId), 'packageCustomValue');
				kworkEditorFields.addEditIconPackageItemCustomValue(itemId);
			}
			else {
				if (typeof answer.error !== 'undefined' && answer.error.length > 0) {
					kworkEditorFields.showEditErrors(answer.error);
				}
				else {
					alert('Ошибка при сохранении');
				}
			}
		}, 'JSON');
};

kworkEditorFields.setNewOptionText = function (itemId) {
	var setValue = $('#option-item-text-' + itemId).val();
	if (setValue.length === 0) {
		alert("Введите название опции");
		return false;
	}
	if (setValue.length > 80) {
		alert("Превышение длины 80 символов");
		return false;
	}

	$.post('/moder_kwork/save',
		{
			field: 'optionText',
			value: setValue,
			id: moderKworkId,
			entity: 'kwork',
			itemId: itemId
		}, function (answer) {
			if (answer.success) {
				$('#option-item-text-' + itemId).closest(".order-extra-item").find(".chosen-container").css({'margin-top': '-6px'});
				kworkEditorFields.changeBackBlock($('#option-item-text-' + itemId), 'optionText');
				kworkEditorFields.addEditIconOptionsText(itemId);
				kworkModerateSpellChecker.check('extraLabels');
			}
			else {
				if (typeof answer.error !== 'undefined' && answer.error.length > 0) {
					kworkEditorFields.showEditErrors(answer.error);
				}
				else {
					alert('Ошибка при сохранении');
				}
			}
		}, 'JSON');
};

kworkEditorFields.setNewTranslates = function () {
	$.post('/moder_kwork/save', {
		'field': 'translates',
		'value': $("#translates-form").serialize(),
		'id': moderKworkId,
		'entity': 'kwork'
	}, function (answer) {
		if (answer.success) {
			if (typeof answer.viewData !== "undefined" && answer.viewData.length > 0) {
				$("#translates-data").html(answer.viewData);
			}
			if (typeof answer.editData !== "undefined" && answer.editData.length > 0) {
				$("#translate_pairs").replaceWith(answer.editData);
			}

			TranslatesModule.updateTranslate();
			viewTranslates();
		}
		else {
			if (typeof answer.error !== 'undefined' && answer.error.length > 0) {
				kworkEditorFields.showEditErrors(answer.error);
			} else {
				alert(t('Ошибка при сохранении'));
			}
		}
	}, 'JSON');
}

kworkEditorFields.showEditErrors = function (error) {
	alert(error);
};

kworkEditorFields.getSetValue = function ($obj, type) {
	switch (type) {
		case 'volume':
		case 'title':
		case 'requiredinfo':
			return $($obj).text();
			break;
		case 'description':
			return $($obj).text();
			break;
		case 'packageCustom':
			return $($obj).text();
			break;
		case 'packageCustomValue':
			return $($obj).text();
			break;
		case 'optionText':
			return $($obj).text();
			break;
		case 'translates':
			return $($obj).text();
			break;

	}
};

kworkEditorFields.loadClassifications = function($obj, multiple) {
	multiple = multiple || false;
	var sl = kworkEditorFields._selectors,
		$chainWrapElement = $(sl.classification.chainWrap),
		$langElement = $(sl.lang),
		$classificationHolderElement = $chainWrapElement.find(sl.classification.holder),
        categoryId = $chainWrapElement.find(sl.classification.element + ':visible').val();

    if (!parseInt(categoryId)) {
    	throw new Error('Category id isn\'t specified');
	}

	if (typeof $langElement === 'undefined') {
        throw new Error('Element that shows current system language is absent');
	}

	if ($obj !== false && typeof _isPackage !== 'undefined' && _isPackage) {
		$(sl.classification.chainWrap + ' .parents').off('change');
		$(sl.classification.chainWrap).off('click', '.gig_categories');
		$(sl.classification.chainWrap + ' .select-styled').attr('disabled', 'disabled');
	}

    var	requestData = {
        categoryId: categoryId,
        lang: $langElement.val(),
        attributeId: $obj ? $obj.val() : "",
        kworkId: moderKworkId,
	    needPackageDisable: 1,
    };

    var currentContainer = false;
    if (!$classificationHolderElement.html().length) {
        currentContainer = $classificationHolderElement;
    } else {
        var currentClassificationBlock = $obj.closest(sl.classification.block);
        currentContainer = currentClassificationBlock.find(sl.classification.nestedElementsBlock);
		if (!multiple) {
			currentContainer.empty();
		} else {
			currentContainer.find('.js-field-block[data-parent-id="' + requestData.attributeId + '"]').remove();
		}
    }

	$.ajax({
		url: '/api/attribute/loadclassification',
		data: requestData,
		method: 'get',
		dataType: 'json'
	}).done(function(response) {
        if (response === '') {
            alert('Error: response is empty');
            return;
        }

        if (response.success) {
            currentContainer.eq(0).append(response.html);

            $classificationHolderElement.find(sl.classification.addCustomAttribute).remove();

            if (currentContainer !== false) {
                currentContainer.find(sl.classification.block).each(function(index) {
                    var checkedElement = $(this).find(sl.classification.radioButton + ':checked');
                    if (checkedElement.length && response.html.length) {
                        checkedElement.trigger('change');
                    }
                    checkedElement = $(this).find(sl.classification.checkbox + ':checked');
                    if (checkedElement.length && response.html.length) {
                        checkedElement.trigger('change');
                    }
                });
            }

            $(sl.classification.block).each(function(index) {
                var errorHolder = $(this).find(sl.classification.errorMessage);
                if (errorHolder.length) {
                    errorHolder.remove();
                }
            });
            kworkEditorFields.changeClassification();
            $(kworkEditorFields._selectors.classification.checkbox).on('change', kworkEditorFields.changeClassification);
			if (response.disableIds && categoryChanges <= 1) {
				//Если категория изменялась лишь 1 раз (только нажали кнопку изменения)
				for (var i in response.disableIds) {
					var parentId = response.disableIds[i];
					var $targetParent = $(".attribute-list[data-parent-id=" + parentId + "]");
					$('#attribute_item_' + parentId).prop('disabled', true);
				}
				currentContainer.find(">.kwork-editor-block .attribute-item input").prop('disabled', true);
				$(".kwork-editor-block .kwork-editor_category select").prop('disabled', true);
			}
        } else {
            if (response.error) {
                alert(response.error);
            } else {
                alert('Unknown error');
            }
        }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.log(jqXHR, textStatus, errorThrown);
        alert(t('Ошибка при получении данных'));
    });
}

kworkEditorFields.addEditBlock = function ($obj, type) {
	$("#classifications_moder_list").hide();
	if($('.kwork-editor-block').length && type === 'category') {
		$('.kwork-editor-block').show();
	} else if (type === "translates") {
		editTranslates();
	} else {
		$obj.find('img.cur').remove();
		var setValue = kworkEditorFields.getSetValue($obj, type);
		var itemId;
		switch (type) {
			case "packageCustom":
				itemId = $obj.data("packageItemCustomId");
				break;
			case "packageCustom":
				itemId = $obj.data("packageItemCustomValueId");
				break;
			case "optionText":
				itemId = $obj.data("optionId");
				break;
		}
		var setHtml = kworkEditorFields.getEditBlockHtml(type, setValue, itemId);
		var wrapper = $('<div class="kwork-editor_' + type + '">\n\
								<div class="base-content hidden">' + $($obj)[0].outerHTML + '</div>\n\
								<div class="kwork-editor-block">' + setHtml.block + '\n\
								<div class="' + kworkEditorFields._selectors.classification.holder.substring(1) + '"></div>\n\
								</div>\n\
							</div>');

		switch (type) {
			case 'packageCustom':
				$(wrapper).find('input.kwork-save-step__field-input_package-item').val(setHtml.value);
				break;
			case 'packageCustomValue':
				$(wrapper).find('input.kwork-save-step__field-input_package-item_value').val(setHtml.value);
				break;
			case 'optionText':
				$($obj).closest(".order-extra-item").find(".chosen-container").css({'margin-top': '0'});
				$(wrapper).find('input.kwork-save-step__field-input_option-item_text').val(setHtml.value);
				break;
			default:
				$(wrapper).find('textarea.kwork-save-step__field-input').text(setHtml.value);
				$(wrapper).find('input.kwork-save-step__field-input').val(setHtml.value);
				break;
		}
		$($obj).replaceWith(wrapper);
		kworkEditorFields.afterEditBlock(type);
		window.volumeTypeInput.setToggle();
	}

};
var categoryChanges = 0;
$(document).on("change", ".kwork-editor_category select", function () {
	categoryChanges++;
});

kworkEditorFields.afterEditBlock = function (type) {
	switch (type) {
		case 'description':
			$(".description-text").hide();
			return $('img.edit-description').hide();
			break;
		case 'requiredinfo':
			$(".requiredInfo-text").hide();
			$('.edit-required-info').hide();
			break;

		case 'category':
			var $mainWrapElement = $(kworkEditorFields._selectors.classification.chainWrap);
			$('.kwork-editor_category .parents').on('change', function () {
				$('.kwork-editor_' + type + ' select.gig_categories').addClass('hidden');
				$('.kwork-editor_' + type + ' select#sub_category_' + $(this).val()).removeClass('hidden');

				if (type === 'category') {
                    $mainWrapElement.find(kworkEditorFields._selectors.classification.holder).empty();
                    kworkEditorFields.loadClassifications(false);
				}

				var $subCategory = $('#sub_category_' + $(this).val());
			});

            $mainWrapElement.on('change', '.gig_categories', function() {
                $mainWrapElement.find(kworkEditorFields._selectors.classification.holder).empty();
                kworkEditorFields.loadClassifications(false);
			});

            $mainWrapElement.on('change', kworkEditorFields._selectors.classification.radioButton, function() {
                kworkEditorFields.loadClassifications($(this));
            });

            $mainWrapElement.on('change', kworkEditorFields._selectors.classification.checkbox, function() {
				var attributeId = $(this).val();
				if ($(this).prop("checked")) {
					kworkEditorFields.loadClassifications($(this), true);
				} else {
					$('.js-field-block[data-parent-id="' + attributeId + '"]').remove();
				}
            });

			var $option = $('.kwork-editor_' + type + ' select.gig_categories option[value=' + selectedCategoryId + ']');
			var $subSelectId = $($option).parents('select').attr('id');
			$($option).parents('select').val(selectedCategoryId);
			var parentCategoryId = $subSelectId.substring(13);
			$('.kwork-editor_' + type + ' .parents').val(parentCategoryId).trigger("change");

			break;
	}
};

kworkEditorFields.addEditIconTitle = function () {
	$('h1.kwork-title').append('<img style="vertical-align:middle;" class="cur" src="' + Utils.cdnImageUrl("/edit-ico.png") + '" onclick="kworkEditorFields.addEditBlock($(\'h1.kwork-title\'), \'title\');return false;"/>');
};

kworkEditorFields.addEditIconDescription = function () {
	$('h2.kwork-description').append('<img style="vertical-align:middle;" class="cur edit-description" src="' + Utils.cdnImageUrl("/edit-ico.png") + '" onclick="kworkEditorFields.addEditBlock($(\'.description-text-source\'), \'description\');return false;"/>');
};

kworkEditorFields.addEditIconCategory = function () {
	if (!window.moderDisableCategoryChange) {
		$('.bread-crump').append('<img style="vertical-align:middle;" class="cur edit-description" src="' + Utils.cdnImageUrl("/edit-ico.png") + '" onclick="kworkEditorFields.addEditBlock($(\'.bread-crump\'), \'category\');return false;"/>');
		$('.bread-crump').children().css("display", "inline-block");
	}
};

kworkEditorFields.addEditIconVolume = function () {
	$('.kwork-volume-text').append('<img style="vertical-align:middle;" class="cur" src="' + Utils.cdnImageUrl("/edit-ico.png") + '" onclick="kworkEditorFields.addEditBlock($(\'.kwork-volume-text\'), \'volume\');return false;"/>');
};

kworkEditorFields.addEditIconRequiredInfo = function () {
	$('h2.kwork-requiredInfo').append('<img style="vertical-align:middle;" class="cur edit-required-info" src="' + Utils.cdnImageUrl("/edit-ico.png") + '" onclick="kworkEditorFields.addEditBlock($(\'.requiredInfo-block .requiredInfo-block-text .requiredInfo-text-source\'), \'requiredinfo\');return false;"/>');
};

kworkEditorFields.addEditIconPackageItemCustom = function (itemId) {
	if (itemId) {
		var items = $('.package-item-custom');
		for (var i = 0; i < items.length; i++) {
			if ($(items[i]).data("packageItemCustomId") === itemId) {
				$(items[i]).append('<img style="vertical-align:middle;" class="cur" src="' + Utils.cdnImageUrl("/edit-ico.png") + '" onclick="kworkEditorFields.addEditBlock($(this).parent(), \'packageCustom\');return false;"/>');
				break;
			}
		}
	}
	else {
		$('.package-item-custom').append('<img style="vertical-align:middle;" class="cur" src="' + Utils.cdnImageUrl("/edit-ico.png") + '" onclick="kworkEditorFields.addEditBlock($(this).parent(), \'packageCustom\');return false;"/>');
	}

};


kworkEditorFields.addEditIconPackageItemCustomValue = function (itemId) {
	if (itemId) {
		var items = $('.package-item_type__custom__value__text');
		for (var i = 0; i < items.length; i++) {
			if ($(items[i]).data("packageItemCustomValueId") === itemId) {
				$(items[i]).append('<img style="vertical-align:middle;" class="cur" src="' + Utils.cdnImageUrl("/edit-ico.png") + '" onclick="kworkEditorFields.addEditBlock($(this).parent(), \'packageCustomValue\');return false;"/>');
				break;
			}
		}
	}
	else {
		$('.package-item_type__custom__value__text').append('<img style="vertical-align:middle;" class="cur" src="' + Utils.cdnImageUrl("/edit-ico.png") + '" onclick="kworkEditorFields.addEditBlock($(this).parent(), \'packageCustomValue\');return false;"/>');
	}

};

kworkEditorFields.addEditIconOptionsText = function (itemId) {
	if (itemId) {
		var items = $('.option-item__text');
		for (var i = 0; i < items.length; i++) {
			if ($(items[i]).data("optionId") === itemId) {
				$(items[i]).append('<img style="vertical-align:middle;" class="cur" src="' + Utils.cdnImageUrl("/edit-ico.png") + '" onclick="kworkEditorFields.addEditBlock($(this).parent(), \'optionText\');return false;"/>');
				break;
			}
		}
	}
	else {
		$('.option-item__text').append('<img style="vertical-align:middle;" class="cur" src="' + Utils.cdnImageUrl("/edit-ico.png") + '" onclick="kworkEditorFields.addEditBlock($(this).parent(), \'optionText\');return false;"/>');
	}

};

kworkEditorFields.addEditIconTranslates = function() {
	$('#translates-title').append('<img style="vertical-align:middle;" class="cur" src="' + Utils.cdnImageUrl("/edit-ico.png") + '" onclick="kworkEditorFields.addEditBlock($(this).parent(), \'translates\');return false;"/>');
};

kworkEditorFields.addEditIcons = function () {
	kworkEditorFields.addEditIconTitle();
	kworkEditorFields.addEditIconDescription();
	kworkEditorFields.addEditIconCategory();

	if (!isPackage) {
		kworkEditorFields.addEditIconOptionsText();
	} else {
		kworkEditorFields.addEditIconPackageItemCustom();
		kworkEditorFields.addEditIconPackageItemCustomValue();
	}

	$('#reason_' + packageCategoryReasonId).change(function () {
		setPackageCategoryReason(this);
	});

	kworkEditorFields.addEditIconVolume();
	kworkEditorFields.addEditIconRequiredInfo();
	kworkEditorFields.addEditIconTranslates();
};

kworkEditorFields.changeClassification = function () {
    $(kworkEditorFields._selectors.classification.checkbox).each(function(){
        var parent = $(this).parents('.kwork-save-field-checkbox'),
			checkedSize = parent.find('input[type="checkbox"]:checked').size(),
            multipleMaxCount = parent.data('multiple-max-count');
        if(multipleMaxCount > 0){
            if (checkedSize >= multipleMaxCount){
                parent.find('input[type="checkbox"]').not(':checked').prop('disabled', true).parent().css('opacity', '0.5');
            }else {
                parent.find('input[type="checkbox"]').not(':checked').prop('disabled', false).parent().css('opacity', '1');
            }
        }
    });
};

function setPackageCategoryReason(obj) {
	var blockWraper = $(obj).parents('.wrap_reason').find('.sub_reasons');
	$(blockWraper).html('');
	$('.package_category_error').text('');
	if ($(obj).prop('checked')) {
		$(blockWraper).html($('.base_category_list').html() + '<div class="js-category-attributes-select-wrapper"/>');

		$(blockWraper).find('.parents').prepend('<option value="">' + t('Выберите категорию') + '</option>');
		$(blockWraper).find('.childs').prepend('<option value="">' + t('Выберите подкатегорию') + '</option>');

		$(blockWraper).find('.parents').change(function () {
			$(blockWraper).find('select.gig_categories').addClass('hidden');
			$(blockWraper).find('select#sub_category_' + $(this).val()).removeClass('hidden');
		});

		$(blockWraper).find('.childs').change(function () {
			$(blockWraper).find('label.gig_attributes').addClass('hidden');
			$(blockWraper).find('label.gig_attributes[data-category=' + $(this).val() + ']').removeClass('hidden');
		});

		var $option = $(blockWraper).find('select.gig_categories option[value=' + selectedCategoryId + ']');
		var $subSelectId = $($option).parents('select').attr('id');
		var parentCategoryId = $subSelectId.substring(13);

		$(blockWraper).find('.parents').val(parentCategoryId).trigger("change");
		$($option).parents('select').val(selectedCategoryId);

		// Подгрузка селектов с выбранными атрибутами
		if (attributesTree.length > 0) {
			var attributesHtml = buildHtmlRecursive(selectedAttributesIds, attributesTree);
			jQuery(_classes.attributesSelectWrapper).html(attributesHtml);

			//Сохраняем id выбранных классификаций
			saveSelectedAttributesId(selectedAttributesId);
		}
	}
}

function checkValidPackageError() {
	var reasonInput = $('#reason_' + packageCategoryReasonId);
	if ($(reasonInput).prop('checked')) {
		var setErrorCategoryId = $(reasonInput).parents('.wrap_reason').find('.sub_reasons select.gig_categories:visible').val();

		//проверка, были ли изменены классификации
		var isAttributesChanged = false,
			selectedAttributesIdNew = {};
		saveSelectedAttributesId(selectedAttributesIdNew);
		if (!jQuery.isEmptyObject(selectedAttributesIdNew) && JSON.stringify(selectedAttributesId) !== JSON.stringify(selectedAttributesIdNew)) {
			isAttributesChanged = true;
		}

		if ((setErrorCategoryId < 1 || selectedCategoryId == setErrorCategoryId) && isAttributesChanged === false) {
			return false;
		}
		$('#h_set_package_category_error').val(setErrorCategoryId);
	}
	return true;
}

function decideAction(button) {
	if($(".kwork-editor-block").length){
		if ($("input.white-btn:visible").length) {
			$("html").animate({
				scrollTop: ($('input.white-btn:visible').first().offset().top + 50)},
				1000,
				"swing", function () {
					alert("Закончите редактирование");
				});
			return false;
		}
		kworkEditorFields.setNewCategory(button);
	} else {
		decideModerFormSubmit(button);
	}

}

function decideModerFormSubmit(button) {
	button.disabled = true;
	var hasEmptySubReason = false;
	$('.wrap_blocks_moder .block_moder input:checked').each(function () {
		$(this).parents('.wrap_reason').find('.sub_reason_error').text('');
		var subReason = $(this).parents('.wrap_reason').find('.sub_reasons input:checked');
		var hasSubReasons = $(this).parents('.wrap_reason').find('.sub_reasons input').not('.skip-check').length > 0;
		if (subReason.length == 0 && hasSubReasons) {
			button.disabled = false;
			var errorElement = $(this).parents('.wrap_reason').find('.sub_reason_error');
            errorElement.text('Причина не выбрана');
            if (!hasEmptySubReason) {
                $('html, body').animate({scrollTop: (errorElement.position().top - window.innerHeight / 2)}, 200);
			}
            hasEmptySubReason = true;
		}
	});

	if (!checkValidPackageError()) {
		button.disabled = false;
		$('.package_category_error').text(t('Категории/классификации не изменены'));
		hasEmptySubReason = true;
	}

	// Проверка категории на заполненность
	if (checkCategorySelect()) {
		button.disabled = false;
		$('.package_category_error').text(t('Не задана категория'));
		hasEmptySubReason = true;
	}

	// Проверка подкатегории на заполненность
	if (checkSubCategorySelect()) {
		button.disabled = false;
		$('.package_category_error').text(t('Не задана подкатегория'));
		hasEmptySubReason = true;
	}

	// Проверка атрибутов выбранной подкатегории
	if (checkCategoryAttributesSelect()) {
		button.disabled = false;
		$('.package_category_error').text(t('Заполните хотя бы одну классификацию'));
		hasEmptySubReason = true;
	}

	if (window.requiredVolumeTypeId && !currentVolumeSize) {
		button.disabled = false;
		alert('Не указан объем услуги');
		hasEmptySubReason = true;
	}

	if (typeof $(button).attr('data-attr-required') !== 'undefined' && !!$(button).attr('data-attr-required')) {
		var	requestData = {
			categoryId: selectedCategoryId,
			lang: $(kworkEditorFields._selectors.lang).val(),
			attributeId: "",
			kworkId: moderKworkId
		};

		$.ajax({
			url: '/api/attribute/loadclassification',
			data: requestData,
			method: 'get',
			dataType: 'json'
		}).done(function(response) {
			if (response.success && !response.selectedCount && response.count) {
				button.disabled = false;
				alert(t('Не выбраны атрибуты!'));
			} else {
				if (hasEmptySubReason === false) {
					$('form#decide_moder_action').submit();
				}
			}
		});
	} else {
		if (hasEmptySubReason === false) {
			$('form#decide_moder_action').submit();
		}
	}
}
function showHideSubReason(obj) {
	if ($(obj).prop('checked')) {
		$(obj)
			.parents('.wrap_reason')
			.find('.sub_reasons')
			.removeClass('hidden');
	}
	else {
		$(obj)
			.parents('.wrap_reason')
			.find('.sub_reasons')
			.addClass('hidden')
			.find('input')
			.prop('checked', false);
	}
}

/**
 * Поскольку текст узла может состоять из вложенных блоков - проходим
 * по этим блоками. Если в тексте блока найдено совпадение - сохраняем
 * оригинал и подсвеченную версию в массив, который и возвращаем.
 *
 * @param $element
 *   объект jQuery, в котором проводится поиск.
 * @param pattern
 *   искомая строка.
 * @param highlightClass
 *   класс, который будет присвоен оборачивающему вхождение тэгу span.
 *
 * @return
 *   массив объектов со следующими свойствами:
 *     'original': оригинальный текст отдельного блока.
 *     'result': подсвеченная версия текста.
 * Если $element не содержит вхождений искомой строки возвращается пустой массив.
 */
function parseElementText($element, pattern, highlightClass) {
	var lowerCasePattern = pattern.toLowerCase();
	var entries = [];
	$element.find("*:not(a)").andSelf().contents().filter(function () {
		return this.nodeType === 3 && !$(this).parent().hasClass(highlightClass);
	})
		.each(function (index, value) {
			var $this = $(this);
			var text = $this.text();
			var lowerCaseText = text.toLowerCase();
			if (lowerCaseText.indexOf(lowerCasePattern) >= 0 && !$this.parents().is('a')) {
				var entry = {
					original: text,
					result: highlightWord(pattern, text, highlightClass)
				};
				entries.push(entry);
			}
		});

	return entries;
}

/**
 * Получаем html элемента и для каждого совпадения заменяем вхождение
 * на подсвеченную версию.
 * Затем устанавливаем для элемента подсвеченный html.
 *
 * @param entries
 *   массив, полученный функцией parseElementText().
 * @param $element
 *   объект jQuery, html которого надо заменить.
 */
function processSpellcheckResult(entries, $element) {
	var html = $element.html();
	$.each(entries, function(index, entry) {
		html = html.replace(entry.original, entry.result);
	});
	$element.html(html);

}

// Порт PHP-функции preg_quote.
function pregQuote(str, delimiter) {
	return (str + '').replace(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + (delimiter || '') + '-]', 'g'), '\\$&');
}

/**
 * Окружаем совпавшую часть строки тэгом span с нужным классом.
 *
 * Предусловие: строка содержит искомое подстроку.
 *
 * @param pattern
 *   искомая строка.
 * @param string
 *   строка, в которой находится pattern.
 * @param highlightClass
 *   класс, который будет присвоен оборачивающему вхождение тэгу span.
 */
function highlightWord(pattern, string, highlightClass) {
	var regex = new RegExp(pregQuote(pattern), 'i');
	// Совпавшая часть строки (в том же виде, что и в исходнике).
	var match = string.match(regex);
	var $replacement = '<span class="' + highlightClass + '" title="Возможно слово содержит ошибку.">' + match + '</span>';
	return string.replace(new RegExp(match, 'g'), $replacement);
}

var spellChecker = function() {
	// Сохраним все элементы, чтобы потом не искать.
	this.textElements = {
		title: null,
		description: null,
		instruction: null,
		volume: null,
		extraLabels: {}
	};

	this.extraLabels = {};
};

spellChecker.prototype.init = function() {
	this.textElements.title = $('#kwork-title');
	this.textElements.description = $('#description-text');
	this.textElements.instruction = $('#requiredInfo-text');
	this.textElements.volume = $('#kwork-volume-text');
	this.textElements.extraLabels = {};

	var _self = this;

	// У опций элементы вложенные.
	$('.order-extra-item').each(function(i, el) {
		var $label = $(this).find('label');
		var id = $label.prop('for');
		_self.textElements.extraLabels[id] = $label;
		_self.extraLabels[id] = $label.text();
	});
};

spellChecker.prototype.check = function(itemName) {
	// из view.tpl
	if (!spellCheck) {
		return;
	}

	var _self = this;
	_self.init();

	if ('undefined' === typeof itemName) {
		// Пойдет на сервер как 'text'.
		var texts = {
			title: _self.textElements.title.text(),
			description: textWithRemovedLinks(_self.textElements.description),
			instruction: textWithRemovedLinks(_self.textElements.instruction),
			volume: _self.textElements.volume.text(),
			extraLabels: _self.extraLabels
		};

		var params = {
			text: texts
		};
		$.post('/api/speller/checktext', params,	function (response) {
			if (response.success === true) {
				var mistakes = response.mistakes;

				// Сначала обработаем вложенные и уберем их.
				if(mistakes['extraLabels']){
					spellcheckProcessMistakes(mistakes['extraLabels'], _self.textElements['extraLabels']);
					delete mistakes['extraLabels'];
				}

				spellcheckProcessMistakes(mistakes, _self.textElements);
			}
		}, "json");
	}
	else {
		var texts = {};
		if ('extraLabels' === itemName) {
			texts[itemName] = _self.extraLabels;
		}
		else {
			texts[itemName] = _self.textElements[itemName].text();
		}

		var params = {
			text: texts
		};
		$.post('/api/speller/checktext', params,	function (response) {
			if (response.success === true) {
				var mistakes = response.mistakes;
				if ('extraLabels' === itemName) {
					spellcheckProcessMistakes(mistakes[itemName], _self.textElements[itemName]);
				}
				else {
					spellcheckProcessMistakes(mistakes, _self.textElements);
				}
			}
		}, "json");
	}
};

function textWithRemovedLinks(el){
	var cloned = el.clone();
	cloned.find("a").remove();
	return cloned.text();
}

function spellcheckProcessMistakes(mistakes, elements) {
	$.each(mistakes, function(key, words) {
		$.each(words, function(i, word) {
			var result = parseElementText(elements[key], word, 'spell-mistake');
			if (result.length > 0) {
				// меняем html на подсвеченную версию
				processSpellcheckResult(result, elements[key]);
			}
		});
	});
}

function otherListUpdate() {
	var showed = 0
	$('.wrap_other_list > div').each(function(k, v) {
		var bc = $('.bread-crump'), t = $(v);
		if(t.data('parent') != bc.data('parent') || t.data('cat') != bc.data('cat')) {
			t.hide();
		} else {
			t.show();
			showed++;
		}
	});
	if(showed > 0) {
		$('.show-other-kworks-toggle').show();
		$('.no-other-kworks').hide();
	} else {
		$('.show-other-kworks-toggle').hide();
		$('.no-other-kworks').show();
	}
}

function hideUnchangedExtras() {
	if($('#kwork-status').data('postmoderation')) {
		if(!$('.order-extras-list > .patch-border__extras-green').length && !$('.order-extras-list > .patch-border__extras-orange').length) {
			$('#newextform').hide();
			$('.block_extra_moder').hide();
		}
	};
}

var kworkModerateSpellChecker = kworkModerateSpellChecker || new spellChecker();

function getRightClassFields(catId, attrId, target) {
	if(!$(".class-field-reason span").hasClass("opened")){
		return;
	}
	var	requestData = {
		categoryId: catId,
		lang: $(kworkEditorFields._selectors.lang).val(),
		attributeId: attrId,
		kworkId: moderKworkId,
		className: "attributes_right",
		defaultClass: 'right_'
	};

	$.ajax({
		url: '/api/attribute/loadclassification',
		data: requestData,
		method: 'get',
		dataType: 'json'
	}).success(function (resp) {
		if(attrId === ""){
			target.html(resp.html);
		}else{
			if($(".block_category_moder .classfield-block .sub-classfield").length === 0){
				var html = "<div class='sub-classfield'>" + resp.html + '</div>';
				$(".block_category_moder .classfield-block").append(html);
			}else{
				$(".block_category_moder .classfield-block .sub-classfield").html(resp.html);
			}

			$(".block_category_moder .attribute-item input").prop("disabled", false);
		}

	});
}

$(document).ready(function() {
	var clicks = 0;
	otherListUpdate();
	hideUnchangedExtras();
	var classFieldId = $(".class-field-reason .reasons-input").val();

	$(".class-field-reason span").click(function () {
		if(clicks == 1){
			if($(this).hasClass("opened")){
				$(".block_category_moder .classfield-block").html("");
				$(this).removeClass("opened");
			}else{
				var catId = $(".block_category_moder .childs:not(.hidden)").val();
				if(!catId){
					catId = $(".bread-crump.block-response").data('cat');
				}
				$(this).addClass("opened");
				getRightClassFields(catId, "", $(".block_category_moder .classfield-block"));
			}
			clicks = 0;
		}else{
			clicks++;
		}


	});
	$(document).on('change', '.block_category_moder .childs, .block_category_moder .parents', function () {
		getRightClassFields($(".block_category_moder .childs:not(.hidden)").val(), "", $(".block_category_moder .classfield-block"));

	});

	$(document).on('click', '.block_category_moder .attribute-item input', function () {
		if($(this).closest(".js-field-block").data("parent-id") >= 1)
			return;
		var catId = $(".block_category_moder .childs:not(.hidden)").val();
		if(!catId){
			catId = $(".bread-crump.block-response").data('cat');
		}
		getRightClassFields(catId, $(this).val(), null);
	});

});

function viewTranslates() {
	$("#translates-data").removeClass("hidden");
	$("#translates-editor").addClass("hidden");
	$("#translate_pairs").addClass("hidden");
	$("#translates-title img").removeClass("hidden");
	$("#translates-title").removeClass("hidden");
}

function editTranslates() {
	$("#translates-data").addClass("hidden");
	$("#translates-editor").removeClass("hidden");
	$("#translate_pairs").removeClass("hidden");
	$("#translates-title img").addClass("hidden");
	$("#translates-title").removeClass("hidden");
}

function hideTranslates() {
	$("#translates-data").addClass("hidden");
	$("#translates-editor").addClass("hidden");
	$("#translate_pairs").addClass("hidden");
	$("#translates-title img").addClass("hidden");
	$("#translates-title").addClass("hidden");
}

/**
 * CSS-классы
 */
var _classes = {
	'attributesSelect': '.js-category-attributes-select',
	'attributesSelectWrapper': '.js-category-attributes-select-wrapper',
	'attributeSelectWrapper': '.js-attribute-select-wrapper',
	'categoryWrapper': '.js-category-select-wrapper'
};
var selectedAttributesId = {};
var attributeVisibility = '';

/**
 * Подгрузка атрибутов выбранной подкатегории
 *
 * @param categoryId
 * @param attributeId
 * @param parentId
 * @private
 */
function setCategoryAttributes(categoryId, attributeId, parentId) {
	var projectData = {categoryId: parseInt(categoryId), visibility: attributeVisibility};

	if (attributeId) {
		projectData.attributeId = parseInt(attributeId);
	}

	jQuery.ajax({
		url: '/api/attribute/payer_json',
		data: projectData,
		method: 'GET',
		dataType: 'json',
		success: function (response) {
			var selectAttributesHtml = '';

			if (response.success && response.data.length) {
				jQuery(response.data).each(function (index, value) {
					var parentAttribute = response.data[index],
						childrenAttribute = parentAttribute.children,
						parentId = parentAttribute.parent_id === null ? parentAttribute.id : parentAttribute.parent_id;

					selectAttributesHtml += '<div class="' + _classes.attributeSelectWrapper.substr(1) + ' ' + _classes.attributeSelectWrapper.substr(1) + '-' + parentId + '">';
					selectAttributesHtml += '<select' +
						' name="attribute[' + parentAttribute.id + ']"' +
						' class="js-category-attributes-select select-styled select-styled--thin long-touch-js f15 db mt10"' +
						' data-parent="' + parentId + '"' +
						' data-placeholder="' + parentAttribute.title + '"' +
						'>';
					selectAttributesHtml += '<option value="" selected>' + parentAttribute.title + '</option>';

					jQuery(childrenAttribute).each(function (childrenIndex, childrenValue) {
						selectAttributesHtml += '<option value="' + childrenAttribute[childrenIndex].id + '">' + childrenAttribute[childrenIndex].title + '</option>';
					});

					selectAttributesHtml += '</select>';
					selectAttributesHtml += '</div>';
				});
			}

			if (attributeId) {
				//список атрибутов выбранной классификации
				jQuery(_classes.attributeSelectWrapper + '-' + parentId).children(_classes.attributeSelectWrapper).remove();
				jQuery(_classes.attributeSelectWrapper + '-' + parentId).append(selectAttributesHtml);
			} else {
				//список классификаций выбранной подкатегории
				jQuery(_classes.attributesSelectWrapper).html(selectAttributesHtml);
			}

			jQuery('.package_category_error').text('');
		},
	});
}

/**
 * Отрисовка селектов классификаций/атрибутов на основе сохраненных данных
 *
 * @param attributesId
 * @param tree
 * @returns {string}
 */
function buildHtmlRecursive(attributesId, tree) {
	var html = '';

	for (var i in tree) {
		if (tree[i].children.length) {
			var parentId = tree[i].parent_id === null ? tree[i].id : tree[i].parent_id;

			if (tree[i].is_classification) {
				html += '<div class="' + _classes.attributeSelectWrapper.substr(1) + ' ' + _classes.attributeSelectWrapper.substr(1) + '-' + parentId + '">';
				html += '<select' +
					' name="attribute[' + tree[i].id + ']"' +
					' class="js-category-attributes-select select-styled select-styled--thin long-touch-js f15 db mt10"' +
					' data-parent="' + parentId + '"' +
					' data-placeholder="' + tree[i].title + '"' +
					'>';
				html += '<option value="" selected>' + tree[i].title + '</option>';
			}
			for (var j in tree[i].children) {
				if (tree[i].children[j].is_classification === false) {
					html += '<option ' +
						'value="' + tree[i].children[j].id + '"' +
						(attributesId.indexOf(tree[i].children[j].id) !== -1 ? ' selected' : '') +
						'>' + tree[i].children[j].title + '</option>';
				}

			}
			if (tree[i].is_classification) {
				html += '</select>';
			}

			html += buildHtmlRecursive(attributesId, tree[i].children);

			if (tree[i].is_classification) {
				html += '</div>';
			}
		}
	}

	return html;
}

/**
 * Проверка категории на заполненность
 *
 * @returns {boolean}
 */
function checkCategorySelect() {
	var hasError = false;

	if (jQuery('#reason_' + packageCategoryReasonId).prop('checked')) {
		//проверка на заполненность подкатегории
		if (jQuery('.sub_reasons .parents:not(.hidden)').val() == '') {
			hasError = true;
		}
	}

	return hasError;
}

/**
 * Проверка подкатегории на заполненность
 *
 * @returns {boolean}
 */
function checkSubCategorySelect() {
	var hasError = false;

	if (jQuery('#reason_' + packageCategoryReasonId).prop('checked')) {
		//проверка на заполненность подкатегории
		if (jQuery('.sub_reasons .gig_categories:not(.hidden)').val() == '') {
			hasError = true;
		}
	}

	return hasError;
}

/**
 * Проверка атрибутов выбранной подкатегории
 *
 * @returns {boolean}
 */
function checkCategoryAttributesSelect() {
	var hasError = false;

	if (jQuery('#reason_' + packageCategoryReasonId).prop('checked')) {
		if (jQuery(_classes.attributesSelect).length) {
			var selected = 0;
			jQuery(_classes.attributesSelect).each(function () {
				if (jQuery(this).val() != 0) {
					selected++;
				}
			});
			if (selected === 0) {
				hasError = true;
			}
		}
	}

	return hasError;
}

/**
 * Сохраняем id выбранных классификаций
 *
 * @param obj
 */
function saveSelectedAttributesId(obj) {
	jQuery('.sub_reasons .js-category-attributes-select').each(function () {
		obj[jQuery(this).attr('data-parent')] = jQuery(this).find('option:selected').val();
	});
}

/**
 * Сброс атрибутов и выбранных подкатегорий при смене корневой категории
 */
jQuery(document).on('change', '.sub_reasons .parents:not(.hidden)', function() {
	jQuery('.sub_reasons .gig_categories').prop('selectedIndex', 0);
	jQuery(_classes.attributesSelectWrapper).text('');
	jQuery('.package_category_error').text('');
});

/**
 * Подгрузка атрибутов при изменение подкатегории
 */
jQuery(document).on('change', '.sub_reasons .gig_categories:not(.hidden)', function() {
	setCategoryAttributes(jQuery(this).val(), false,false);
});

/**
 * Подгрузка атрибутов при изменение классификации
 */
jQuery(document).on('change', '.sub_reasons .js-category-attributes-select', function() {
	setCategoryAttributes(jQuery('.sub_reasons .gig_categories:not(.hidden)').val(), jQuery(this).val() | 0, jQuery(this).data('parent'));
});

$(window).load(function () {
	setDoneButtonColor();
	setBlockPositions();
	kworkEditorFields.addEditIcons();
	$('.wrap_reason input.topreason:checkbox').each(function () {
		showHideSubReason(this);
	});

	$('.wrap_reason input.topreason:checkbox').change(function () {
		showHideSubReason(this);
	});

	$('.wrap_blocks_moder .block_moder select, .wrap_blocks_moder .block_moder input').change(function () {
		setDoneButtonColor();
	});

	kworkModerateSpellChecker.check();

	// Превью карточки кворка
	$('.kwork_slider_desktop #slider_desktop').on('beforeChange', function (event, slick, currentSlide, nextSlide) {
		var src = $(slick.$slides.get(nextSlide)).find('.sliderImage img').attr('src');
		$('.kwork-card-moder-preview .cusongsblock__content a img').attr('src', src);
	});

	var reasonInput = jQuery('#reason_' + packageCategoryReasonId);
	if (reasonInput.prop('checked')) {
		setPackageCategoryReason(reasonInput);
	}
});
