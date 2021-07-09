{strip}

	<div class="debug-panel" id="debug-panel">
		<div class="debug-panel__header">
			<ul class="debug-panel__list">
				<li class="debug-panel__item debug-panel__item_has_logo debug-panel__item_cursor_pointer">
					<img class="debug-panel__logo" src="{"/supportavatar.jpg"|cdnImageUrl}" width="26" height="26" alt="">
				</li>
				{foreach from=$debugData key=i item=item}
					<li class="debug-panel__item{if $i == "id" || $i == "memory"} debug-panel__item_noborder{elseif $i == "sql" || $i == "redis"} debug-panel__item_cursor_pointer{/if}"	{if $i == "sql" || $i == "redis"} data-type="{$i}" onclick="debugTableShow(this)"{/if}>
						{if $i == "sql"}
							<span class="debug-panel__link_dashed">MySQL</span>
						{elseif $i == "redis"}
							<span class="debug-panel__link_dashed">{$i|@ucfirst}</span>
						{else}
							{$i|@ucfirst}
						{/if}
						{if $item|is_array}
							{foreach from=$item key=var item=val}
								<span class="debug-panel__badge{if $var == "count"} debug-panel__badge_theme_blue{elseif $var == "time"} debug-panel__badge_theme_orange{/if}{if $i == "sql" && $var == "count" && $sqlErrors > 0} debug-panel__badge_has_error{/if}">
									{$val}
								</span>
							{/foreach}
						{else}
							<span class="debug-panel__badge{if $i == "memory"} debug-panel__badge_theme_blue{elseif $i == "script_time"} debug-panel__badge_theme_orange{else} debug-panel__badge_theme_gray{/if}">
							{if $i == "request"}
								<span title="{$item}">{$item|truncate:30}</span>
							{else}
								{$item}
							{/if}
							</span>
						{/if}
					</li>
				{/foreach}
				<li>
					<select class="debug-panel-select">
						<option value="{$debugData.id}">*{$debugData.request}</option>
					</select>
				</li>
			</ul>
		</div>
		<div class="debug-panel__content">
			<div class="debug-panel__table-wrapper" id="debug_sql_table">
				<table class="debug-panel__table tablesorter tablesorter-bootstrap">
					<thead class="thead-dark">
					<tr>
						<th width="10">№</th>
						<th width="70">Время</th>
						<th width="90">Параметры</th>
						<th>Запрос</th>
					</tr>
					</thead>
					<tbody>
					{assign var=index value=1}
					{foreach from=$sql key=i item=item}
						{if $item.0 != "set charset utf8"}
							<tr class="
									{if $item.3}bgRed {/if}
									{if ($item.1|count)}js-debug-panel-show-params debug-panel__params_show_params {/if}
									{if ($item.4|count)}js-debug-panel-show-trace debug-panel__trace_show_trace {/if}
								"
								data-index="{$i}"
							>
								<td class="t-align-c">{$index++}</td>
								<td class="t-align-c">{$item.2} ms</td>
								<td class="t-align-c">
									<span class="debug-panel__badge {if ($item.1|count)}debug-panel__badge_theme_blue{else}debug-panel__badge_theme_light-gray{/if}">
									{$item.1|count}
									</span>
								</td>
								<td>
									{$item.0|wordwrap:150:'<br>':true}
									{if ($item.1|count)}
										<div id="js-debug-params-block-{$i}" class="js-debug-params-block debug-panel__params-block" style="display: none">
											<p>Параметры запроса:</p>
											<ul>
												{foreach from=$item.1 key=paramKey item=paramValue}
													<li><strong>{$paramKey}</strong>:
														{if $paramValue instanceOf DateTime}
															{$paramValue->format("Y-m-d H:i:s")}
														{else}
															{$paramValue}
														{/if}
													</li>
												{/foreach}
											</ul>
										</div>
									{/if}
									{if ($item.4|count)}
										<div id="js-debug-trace-block-{$i}" class="js-debug-trace-block debug-panel__trace-block" style="display: none">
											<p>Stacktrace:</p>
											<ul>
												{foreach from=$item.4 item=trace}
													<li>
														{$trace.file}:{$trace.line}(<strong>{$trace.class}::{$trace.function}</strong>)
													</li>
												{/foreach}
											</ul>
										</div>
									{/if}
								</td>
							</tr>
						{/if}
					{/foreach}
					</tbody>
				</table>
			</div>
			<div class="debug-panel__table-wrapper" id="debug_redis_table">
				<table class="debug-panel__table tablesorter tablesorter-bootstrap">
					<thead class="thead-dark">
					<tr>
						<th width="10">№</th>
						<th width="70">Время</th>
						<th>Запрос</th>
					</tr>
					</thead>
					<tbody>
					{assign var=index value=1}
					{foreach from=$redis key=i item=item}
						<tr class="{if ($item.2|count)}js-debug-panel-show-trace debug-panel__trace_show_trace {/if}"
							data-index="{$i}"
						>
							<td class="t-align-c">{$index++}</td>
							<td class="t-align-c">{$item.1} ms</td>
							<td>
								{$item.0|wordwrap:150:'<br>':true}
								{if ($item.2|count)}
									<div id="js-debug-trace-block-{$i}" class="js-debug-trace-block debug-panel__trace-block" style="display: none">
										<p>Stacktrace:</p>
										<ul>
											{foreach from=$item.2 item=trace}
												<li>
													{$trace.file}:{$trace.line}(<strong>{$trace.class}::{$trace.function}</strong>)
												</li>
											{/foreach}
										</ul>
									</div>
								{/if}
							</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</div>
{/strip}
		<script>
			function debugPanelHide() {
				$('.debug-panel__table-wrapper').hide();
				$('.debug-panel__item_active').removeClass('debug-panel__item_active');
				$('#debug-panel').css('height', 'auto');
			}
			function debugPanelToggle(panelEvent) {
				var footer = $('.footer');
				var debugPanel = $('#debug-panel');

				if (panelEvent === 'click') {
					if (!localStorage.getItem('debugPanelTiny')) {
						localStorage.setItem('debugPanelTiny', '1');
					} else {
						localStorage.removeItem('debugPanelTiny');
					}

					debugPanelHide();
					footer.toggleClass('footer_without_debug');
					debugPanel.toggleClass('debug-panel_theme_tiny');
				} else if (panelEvent === 'load') {
					if (localStorage.getItem('debugPanelTiny') === '1') {
						footer.addClass('footer_without_debug');
						debugPanel.addClass('debug-panel_theme_tiny');
					}
				}
			}
			function debugTableShow(e) {
				var el = $(e);
				var type = el.data('type');

				if (type === 'sql') {
					$('#debug_redis_table').hide();
					$('#debug_sql_table').fadeToggle(300);

					$('.debug-panel__item_active[data-type="redis"]').removeClass('debug-panel__item_active');
				} else if (type === 'redis') {
					$('#debug_sql_table').hide();
					$('#debug_redis_table').fadeToggle(300);

					$('.debug-panel__item_active[data-type="sql"]').removeClass('debug-panel__item_active');
				}

				el.toggleClass('debug-panel__item_active');
			}
			function debugTableHeight () {
				var debugPanel = $('#debug-panel');
				var debugPanelHeight = debugPanel.height();

				if (debugPanelHeight < 100) {
					debugPanelHide();
				}
			}
			$(function() {
				debugPanelToggle('load');
				debugTableHeight();

				$('#debug-panel:not(.debug-panel_theme_tiny)').resizable({
					handles: 'n',
					minHeight: 40,
				});

				$('.tablesorter').tablesorter();
			});
			$(document).on('click', '.debug-panel__item_has_logo', function () {
				debugPanelToggle('click');
			});
			$(window).on('resize', function () {
				debugTableHeight();
			});
		</script>
