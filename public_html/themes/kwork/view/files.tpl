{strip}
<span class="dib mt20 pr20 f14 bold">{'Файлы'|t}</span>
<div id="list-files" class="mt10 pr20">
	{foreach from=$files key=k item=file}
		<div id="id{$k}" class="mb5">
			{insert name=get_file_ico value=a assign=ico filename=$file.fname}
			<a href="{getUploadFileUrl($file)}"
		   		{if $ico == 'image'}title="{$imgAltTitle|stripslashes} {$imgNumber++} - {Translations::getCurrentHost()}"{/if}
			   	target="_blank"
			   	class="color-text">
				<i class="ico-file-{$ico} dib v-align-m"></i>
				<span class="dib v-align-m ml10"
					  style="overflow: hidden;white-space:nowrap;text-overflow: ellipsis;width: calc(100% - 30px);">
					{$file.fname}
				</span>
			</a>
		</div>
	{/foreach}
</div>
{/strip}