{include file="header.tpl"}
{strip}
	<div class="centerwrap pt20">
		<div class="rating-update-response"></div>
		<button type="button" class="green-btn w100p btn--big18 p0 hoverMe ratings-unpdate">{"Обновить рейтинги"|t}</button>
	</div>
	<script>
		$(document).ready(function () {
			$(".ratings-unpdate").click(function (e) {
				e.preventDefault();
				$.ajax({
					url: "",
					type: "POST",
					success: function (response) {
						if (response.success) {
							var html = "<p>Рейтинги обновлены</p><ul>";
							$.each(response.data, function (id, rating) {
								html += "<li>ID " + id + ": " + rating.old + " => " + rating.new + "</li>";
							});
							html += "</ul>";
							$(".rating-update-response").html(html);
						}
					}
				});
			})
		})
	</script>
{/strip}
{include file="footer.tpl"}