<?php

class StatusManager
{
	/**
	 * Кворк доступен для просмотра в списках
	 * @param string $alias
	 * @return string
	 */
	public static function kworkListEnable($alias = '') {
		$addAlias = '';
		if ($alias) {
			$addAlias = $alias . '.';
		}

		return 	$addAlias . KworkManager::FIELD_ACTIVE . " = '" . KworkManager::STATUS_ACTIVE . "' AND " .
				$addAlias . KworkManager::FIELD_FEAT . " = '1'";
	}

	/**
	 * Проверка, доступен ли кворк для просмотра в списках
	 * @param $kwork
	 * @return string
	 */
	public static function kworkCheckListEnable($kwork) {
		$kwork = (array) $kwork;
		return $kwork[KworkManager::FIELD_ACTIVE] == KworkManager::STATUS_ACTIVE && $kwork[KworkManager::FIELD_FEAT] == 1;
	}
}