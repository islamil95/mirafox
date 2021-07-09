{strip}
{if count($orderFiles) > 0}
		<div id="order-files" class="track--sidebar__files toggler mt20 ">
			<div class="track--files-title toggler--link  d-flex align-items-center bgLightGray border-gray">
				<svg width="22" height="22" viewBox="0 0 1410 1410" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M705 1375.45C1075.28 1375.45 1375.45 1075.28 1375.45 705C1375.45 334.718 1075.28 34.545 705 34.545C334.718 34.545 34.545 334.718 34.545 705C34.545 1075.28 334.718 1375.45 705 1375.45Z" stroke="#DEDED5" stroke-width="43.7241" stroke-miterlimit="10"/>
					<path d="M591.636 1073.57C535.659 1073.57 479.682 1052.28 437.1 1009.7C395.787 968.388 373.086 913.539 373.086 855.165C373.086 796.791 395.787 741.801 437.1 700.629L712.614 425.115L751.53 385.494C783.114 353.91 825.132 336.426 869.829 336.426C914.526 336.426 956.544 353.769 988.128 385.494C1019.71 417.078 1037.2 459.096 1037.2 503.793C1037.2 548.49 1019.85 590.367 988.269 621.951L949.353 661.713L688.503 922.281C665.943 944.841 636.192 956.262 606.441 956.262C576.69 956.262 547.08 944.982 524.379 922.281C479.118 877.02 479.118 803.418 524.379 758.157L785.088 497.448C794.958 487.578 811.032 487.578 821.043 497.448C830.913 507.318 830.913 523.392 821.043 533.403L560.334 794.112C534.954 819.492 534.954 860.946 560.334 886.326C585.714 911.706 627.168 911.706 652.548 886.326L913.116 625.758L952.032 586.137C974.028 564.141 986.154 534.813 986.154 503.652C986.154 472.491 974.028 443.304 952.032 421.167C930.036 399.171 900.708 387.045 869.547 387.045C838.386 387.045 809.199 399.171 787.203 421.167L748.287 460.788L472.914 736.302C407.49 801.726 407.49 908.181 472.914 973.746C538.338 1039.17 644.793 1039.17 710.358 973.746L985.872 698.232C995.742 688.362 1011.82 688.362 1021.83 698.232C1031.7 708.102 1031.7 724.176 1021.83 734.187L746.172 1009.7C703.59 1052.28 647.613 1073.57 591.636 1073.57Z" fill="#FFA800"/>
				</svg>
				<span class="dib v-align-m ml10">{'Файлы заказа'|t}</span>
				<i class="fa fa-chevron-down ml-auto"></i>
			</div>
			<div class="order-files order-files-aside toggler--content p15-20 f14 ">
				{$retention_period_notice_count = UserManager::getRetentionPeriodNoticeCount()}
				{literal}
					<script>
						window.fileRetentionPeriodNoticeCount = {/literal}{$retention_period_notice_count}{literal};
					</script>
				{/literal}
				{foreach $orderFiles as $file}
					{insert name=get_file_ico value=a assign=ico filename=$file->fname}
					{insert name=get_short_file_name value=a assign=short_fname filename=$file->fname}
					<div class="order-files-link">
						<a href="{absolute_url route="file_download" params=["filePath" => $file->s,"fileName" => $file->fname]}"
						   target="_blank" class="color-text{if $retention_period_notice_count >= 0} js-popup-file{/if}">
							<i class="ico-file-{$ico} dib v-align-m"></i>
							<span class="dib v-align-m ml10 mw80p">{$short_fname}</span>
						</a>
						<a href="{absolute_url route="file_download" params=["filePath" => $file->s,"fileName" => $file->fname]}?attachment=1"
							{if $retention_period_notice_count >= 0} class="js-popup-file"{/if}
						   target="_blank"{if $retention_period_notice_count < 0} download{/if}>
									<img src="{"/download.png"|cdnImageUrl}" alt="" class="v-align-m">
						</a>
					</div>
				{/foreach}
			</div>
		</div>
{/if}
{/strip}
