{strip}
	{foreach from=$files key=k item=file}
 		{if $file->status == \Model\File::STATUS_ACTIVE}
			<div id="id{$k}" class="mt5 file-item t-align-l mb10 m-ml0">
				<a href="{absolute_url route="file_download" params=["filePath" => $file->s,"fileName" => $file->fname]}"
				   target="_blank" class="color-text">
					<i class="ico-file-{get_file_ico filename=$file->fname} dib v-align-m"></i>
					<span class="dib v-align-m ml10 mw80p">{$file->fname}</span>
				</a>
			</div>
		{else}
			<div class="file-item t-align-l"><i class="ico-file-{get_file_ico filename=$file->fname} dib v-align-m"></i><span class="v-align-m ml10 mw80p">{'Срок хранения файла истек'|t}</span></div>
		{/if}
	{/foreach}
{/strip}