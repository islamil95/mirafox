{strip}
<div class="sampleblock">
	<div class="sampleblock_review">
		<div>
			<img class="rounded" alt="{$sample->username}" src="{"/small/{$sample->profilepicture}"|cdnMembersProfilePicUrl}" width="30">
			<span class="sampleblock_review_username">{$sample->username}</span>
		</div>
		<div class="sampleblock_review_text">
			{$sample->comment|truncate:90:"..."}
		</div>
	</div>
	<div class="sampleblock_image" style="background-image: url("{"/t4/{$sample->photo}"|cdnPortfolioUrl}");"></div>
	<div class="sampleblock_review_small">
		<div>
			<img class="rounded" alt="{$sample->username}" src="{"/small/{$sample->profilepicture}"|cdnMembersProfilePicUrl}" width="30">
			<span class="sampleblock_review_small_text">{$sample->comment|truncate:25:"..."}</span>
		</div>
	</div>
</div>
{/strip}