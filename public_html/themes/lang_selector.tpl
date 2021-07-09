{strip}
	<!-- lang -->
	<div class="lang-selector pull-right ml10 nowrap">
		<div class="lang-selector-current-lang">
			{if Translations::getLang() == Translations::DEFAULT_LANG}RU{else}EN{/if}
		</div>
		<div class="block-popup">
			<ul>
				{if Translations::getLang() == Translations::DEFAULT_LANG}
					<li>
						<strong>{'Русский (RU)'|t}</strong>
					</li>
					<li>
						<a href="{Translations::translateCurrentURL()}">{'English (EN)'|t}</a>
					</li>
				{else}
					<li>
						<a href="{Translations::translateCurrentURL()}">{'Русский (RU)'|t}</a>
					</li>
					<li>
						<strong>{'English (EN)'|t}</strong>
					</li>
				{/if}
			</ul>
		</div>
	</div>
	<!-- end lang -->
{/strip}