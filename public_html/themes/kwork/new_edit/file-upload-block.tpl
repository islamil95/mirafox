{strip}
	<div class="add-photo__file-wrapper file-wrapper file-upload-block"{if $maxSize} data-max-size="{$maxSize}"{/if}{if $fileExtension} data-type="{$fileExtension}"{/if}>
		<input type="hidden" name="{$fileName}" value="{$fileId}" />
		<input type="file" accept="{$extensions}" class="d-none" />
		<div class="file-wrapper-block-container js-file-wrapper-block-container">
			<div class="file-wrapper-block-rectangle">
				<div class="upload-progress">
					<div></div>
				</div>
			</div>
		</div>
		<div style="text-align: center; height: 25px; padding-top: 5px;">
			<span class="button f14 font-OpenSans link-color dibi mt6i file-upload-block__upload">{'Загрузить'|t}</span>
			<span class="button f14 font-OpenSans link-color dibi mt6i file-upload-block__cancel">{'Отмена'|t}</span>
			<span class="button f14 font-OpenSans link-color dibi mt6i file-upload-block__change">{'Изменить'|t}</span>
			<div class="dib file-upload-block__delete">
				&nbsp;&nbsp;&nbsp;<span class="f14 font-OpenSans link-color">{'Удалить'|t}</span>
			</div>
		</div>
	</div>
{/strip}