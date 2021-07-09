{strip}
	<style>
		.info-icon {
			width: 70px;
			height: 70px;
			display: inline-block;
			text-align: center;
			vertical-align: middle;
		}

		.notify-header .close {
			border: 0;
			background: url({"/exit.png"|cdnImageUrl}) center no-repeat;
			background-size: 14px;
			width: 18px !important;
			height: 18px !important;
			padding: 20px !important;
			font-size: 0;
			cursor: pointer;
			margin: -10px -10px -10px -40px;
			position: relative;
			z-index: 1;
		}

		.info-icon .ico-idea {
			width: 70px;
			height: 70px;
			background-size: 70px;
		}
	</style>
	<div class="gray-bg-border clearfix mb20 notify-header manage-kworks-notify w780">
		<div class="contentArea mb0 w100p pb0">
			<div class="p15-20 sm-text-center">
				<button type="button" class="close pull-right js-hide-notify" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="info-icon">
					<i class="icon ico-idea"></i>
				</div>
				<div class="f14 font-OpenSans dib v-align-m mw80p ml15 sm-margin-reset requiredInfo-block">
					<div class="w100p">
						<div>{"Продавайте на Kwork больше и легче, работайте в удовольствие и с комфортом."|t}</div>
						<a href="{$baseurl}/kwork_book">{"Скачайте уроки «Как эффективно зарабатывать на Kwork»"|t}.</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script>
        $(document).ready(function(){
            $('.js-hide-notify').on('click', function(){
                $.ajax({
                    type: "POST",
                    contentType: "application/json; charset=utf-8",
                    url:'{$baseurl}/user/kwork_book_info_block',
                    data: JSON.stringify({
                        "is_kwork_book_info_closed": true
                    }),
                    success: function(response) {
                        $('.notify-header').hide();
                    },
					error: function () {
                        show_message('error', t('Произошла ошибка. Пожалуйста, попробуйте еще раз.'));
                    }
                });
                return false;
            });
        });
	</script>
{/strip}