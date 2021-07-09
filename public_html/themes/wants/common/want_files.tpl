{if $want->files && $want->files->count() > 0}
	<div id="list-files" class="files-list mt10{if $page != "project" && $want->desc|mb_strlen >= 245} hidden{/if}">
		{foreach from=$want->files key=k item=file}
			<div id="id{$k}" class="mb5 file-item">
				{insert name=get_file_ico value=a assign=ico filename=$file->fname}
				<a href="{getUploadFileUrl($file)}"
				   target="_blank" class="color-text d-flex flex-nowrap">
					<i class="ico-file-{$ico} dib v-align-m"></i>
					<span class="dib v-align-m ml10 nowrap">{$file->fname}</span>
				</a>
			</div>
		{/foreach}
	</div>
{/if}