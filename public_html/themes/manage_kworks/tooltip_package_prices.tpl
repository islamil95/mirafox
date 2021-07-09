{* @param string $kworkLang Язык кворка *}
{$timesNum = KworkManager::MAX_ACCEPTABLE_PACKAGE_PRICE_RATIO}
{if Translations::isDefaultLang()}
	{$times = $timesNum|cat:" "|cat:Helper::pluralNumber($timesNum, "раз", "раза", "раз")}
{else}
	{$times = $timesNum}
{/if}
{strip}
	<p><strong>{'Почему важно отредактировать кворк?'|t}</strong></p>
	<p>{'Цена Эконом пакета должна отличаться от цены Бизнес не более чем в %s.'|t:$times}</p>
	{if $kworkLang == Translations::DEFAULT_LANG}
		<p>{'Повысьте цену пакета Эконом или понизьте цену Бизнес, иначе кворк будет автоматически приостановлен.'|t}</p>
	{else}
		<p>{'Повысьте цену пакета Эконом или понизьте цену Бизнес.'|t}</p>
	{/if}
{/strip}