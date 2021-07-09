{strip}
	<div class="profile-cover-change-block centerwrap">
		<form id="profile-cover-change-form"
			  action="{absolute_url route="change_profile_cover" params=["username" => $userProfile->username]}"
			  enctype="multipart/form-data"
			  method="post">
			<div class="js-add-photo">
				<div class="js-template-photo-block" style="display:none;">
					<div class="add-photo__file-wrapper file-wrapper long-touch-js">
						<div class="upload-file-place js-file-wrapper-block-container">
							<div class="file-wrapper-block-profile-cover"></div>
							<input type="file"
								   class="js-file-input"
								   accept=".jpeg, .jpg, .gif, .png"
								   data-pre-upload="true"
								   data-pre-upload-type="with-progressbar">
						</div>
						<div style="text-align: center; height: 25px; padding-top: 5px;">
							<span class="js-file-add-button button f14 font-OpenSans link-color dibi mt6i"
								  data-init-text="{'Выбрать'|t}" data-edit-text="{'Изменить'|t}">
								{'Выбрать'|t}
							</span>
							<div class="dib js-delete hidden">
								&nbsp;&nbsp;&nbsp;
								<span class="f14 font-OpenSans link-color">
									{'Удалить'|t}
								</span>
							</div>
						</div>
						<input class="js-photo-size" type="hidden" value="">
						<input class="js-delete-photo" type="hidden" value="0">
					</div>
				</div>
				<div class="js-photo__block" style="text-align: center;"></div>
				<div class="js-photo-resize__block mt10">
					<img src="{"/empty.png"|cdnImageUrl}"
						 class="js-photo-resize__image"
						 id="resize-img"
						 style="max-width: 100%; height: auto;display:none;"
						 alt="">
				</div>
				<div class="add-photo_error js-add-photo_error"></div>
			</div>
			<div class="profile_error_container"></div>
			<div class="profile-cover-change-block_controls">
				<button class="hoverMe green-btn w135 mt20 f16" style="height:40px;">
					{'Сохранить'|t}
				</button>
				<button type="button" class="hoverMe red-btn w135 mt20 f16 ml30" style="height:40px;"
						onclick="showChangeProfileCover();">
					{'Отменить'|t}
				</button>
			</div>
		</form>
	</div>
{/strip}