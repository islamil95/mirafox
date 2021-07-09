{strip}
	<div class="b-tab js-live-tabs">
        {foreach from=$groups key=groupName item=group}
            {if $group.count eq 0}
                {continue}
            {/if}
			<div class="b-tab_item {if $groupName eq $currentGroup}active{/if}">
				<a href="{$baseurl}/manage_kworks?group={$groupName}">
					<span class="descr">{$group.title}</span>
                    {$tabColor = ''}
                    {if $groupName eq 'rejected'}{$tabColor = 'b-tab_item_number_yellow'}{/if}
                    {if $groupName eq 'suspend'}{$tabColor = 'b-tab_item_number_red'}{/if}
                    {if $groupName eq 'draft'}{$tabColor = 'b-tab_item_number_gray'}{/if}
					<span class="b-tab_item_number m-hidden {$tabColor}">{$group.count}</span>
					<span class="b-tab_item_number-m m-visible">({$group.count})</span>
				</a>
			</div>
        {/foreach}
	</div>
	<form action="{$baseurl}/manage_kworks" id="gigs_form" method="post" class="position-r">
		<table class="wMax m-table-manage-kworks table-manage-kworks">

            {section name=i loop=$posts}
				<tr>
					<td class="v-align-t sm-block">
                        {include file="manage_kworks/kwork_item.tpl" post=$posts[i] isSuspend=$actor->kwork_allow_status == "deny"}
					</td>
				</tr>
            {/section}
		</table>
		<input type="hidden" name="subme" value="1"/>
	</form>
{/strip}

{if $currentGroup eq "feated"}
	<script>
		$(function () {
			$('.js-switch__label').on("mouseover", function () {
				$('#gigs_form .js-worker-status-switch').tooltipster().tooltipster('open');
			}).on("mouseleave", function () {
				$('#gigs_form .js-worker-status-switch').tooltipster().tooltipster('close');
			});
		});
	</script>
{/if}

{Helper::printJsFile("/js/d3.v3.min.js"|cdnBaseUrl)}
{Helper::printJsFile("/js/c3.min.js"|cdnBaseUrl)}
<script>
    {literal}
	$(function () {
		kworkAnalyticsModule.init({/literal}{$statisticsJson}{literal});
	});
    {/literal}
</script>
{Helper::printJsFile("/js/kwork_statistic.js"|cdnBaseUrl)}
<script>
{literal}
	$(function () {
		var $switches = $('#worker-status-switch-wrap .js-worker-status-switch');
		var $all = $('.js-worker-status-switch-type-all');
		var $only = $('.js-worker-status-switch-type-only');

		function changeSwitchAll(val) {
			$switches.data('switch-all', val).attr('data-switch-all', val).prop('data-switch-all', val);
			$all.prop('checked', val === '1');
			$only.prop('checked', val === '0');
			if (isMobile() || jQuery('#worker-status-switch-tooltip').hasClass('js-full-version')) {
				window.forceClick = true;
				$('#gigs_form .js-worker-status-switch-container').trigger('click');
			}
			;
		}

		$(document).on('click', '.js-worker-status-switch-type-all', function () {
			changeSwitchAll('1');
		});
		$(document).on('click', '.js-worker-status-switch-type-only', function () {
			changeSwitchAll('0');
		});

		$(document).on('click', '.tooltip-radio label', function (e) {
			e.preventDefault();
			$(this).prev().click();
		});

		$('.js-manage-kworks-checkbox').change(function () {

			var form = $('#gigs_form').get(0);
			var formData = new FormData(form);

			$.ajax({
				url: '{$baseurl}/custom_request_settings',
				type: 'post',
				contentType: false,
				processData: false,
				data: formData,
				success: function (data) {
					if (data.success) {
						show_message('success', data.message);
					} else {
						show_message('error', data.message || t('Ошибка при сохранении'));
					}
				},
				error: function () {
					show_message('error', t('Ошибка при сохранении'));
				}
			});

		});
	});
{/literal}
</script>

<script>
	// js-live-tabs
	var LiveTabsModule = (function () {
		'use strict';

		var $tabs = $('.js-live-tabs');

		var _getContentWidth = function () {
			return $tabs.width() || '';
		};

		var _removeShowMore = function () {
			$tabs.find('.b-tab_item-more').remove();
		};

		var _addShowMore = function () {
			var $itemMore = $tabs.find('.b-tab_item-more');

			if ($itemMore.length === 0) {
				$tabs.append(
					'<div class="b-tab_item b-tab_item-more" style="display: none;">'
					+ '<a href="javascript: void(0);">'
					+ '<span class="descr">' + t('Ещё') + '</span>'
					+ '</a>'
					+ '<div class="b-tab_item-more--sub"></div>'
					+ '</div>'
				);
			}
		};

		var _tabDisplayCalculation = function () {
			if (isMobile()) {
				$tabs.find('.b-tab_item').show();
				_removeShowMore();

				return;
			} else {
				_addShowMore();
			}

			var contentWidth = _getContentWidth();
			var $showMore = $tabs.find('.b-tab_item-more');
			var visibleTabsWidth = $showMore.outerWidth() || 0;

			$tabs.find('.b-tab_item:not(.b-tab_item-more)').each(function () {
				var $clone = $(this).clone().css('visibility', 'hidden').removeClass('hidden').appendTo($tabs);
				visibleTabsWidth += $clone.outerWidth();
				$clone.remove();

				if (visibleTabsWidth > contentWidth) {
					$(this).hide();
				} else {
					$(this).show();
				}
			});

			if ($tabs.find('.b-tab_item:hidden:not(.b-tab_item-more)').length === 0) {
				$showMore.hide();
			} else {
				$showMore.show();
			}

			$tabs.css({
				'overflow': 'initial'
			});
		};

		var _init = function () {
			_tabDisplayCalculation();

			$('body').on('mouseenter', '.b-tab_item-more', function () {
				$('.b-tab_item-more--sub').html('');
				$('.b-tab_item:hidden:not(.b-tab_item-more)')
					.clone()
					.show()
					.appendTo('.b-tab_item-more--sub');
			});

			$(window).resize(function () {
				_tabDisplayCalculation();
			});
		}();
	});

	var liveTabs = new LiveTabsModule();
</script>