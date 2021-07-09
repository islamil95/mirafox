<?php
/**
 * Created by PhpStorm.
 * User: uiouo
 * Date: 05.09.2017
 * Time: 12:06
 */

namespace Enum;


class Language extends Enum
{
	const EN = \Translations::EN_LANG;
	const RU = \Translations::DEFAULT_LANG;

	/**
	 * Получить текущий язык сайта
	 * @return Language
	 */
	public static function getCurrent() {
		$langKey = \Translations::getLang() == \Translations::EN_LANG ? "EN" : "RU";
		return new Language($langKey);
	}
}