{strip}
<div class="sys-stats">
	<table>
		<tr>
			<td class="w33p">
				<div class="stat-count f34 ta-center" style="line-height:30px;">
					{$stat_act_kworks_count|space:3}
				</div>
				<div class="stat-desc f14 ta-center">
					{declension count=$stat_act_kworks_count form1="Активный кворк" form2="Активных кворка" form5="Активных кворков"}
				</div>
			</td>
			<td class="w33p">
				<div class="stat-count f34 ta-center" style="line-height:30px;">
					{$stat_act_kworks_to_week_count|space:3}
				</div>
				<div class="stat-desc f14 ta-center">
					{declension count=$stat_act_kworks_to_week_count form1="Новый кворк" form2="Новых кворка" form5="Новых кворков"} {'за неделю'|t}
				</div>
			</td>
			<td class="w33p">
				<div class="stat-count ta-center" style="font-size:20px; line-height:19px;">
					{insert name=countdown_short value=a assign=work_time time=$stat_avg_order_done type="duration"}
					{$work_time|num_span:1}
				</div>
				<div class="stat-desc f14 ta-center">
					{'Среднее время выполнения'|t}
				</div>
			</td>
		</tr>
	</table>
</div>
{/strip}