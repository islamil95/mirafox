<?php

namespace Core\Traits\Kwork;

trait KworkStatusTrait {
	/**
	 * Полностью активен ли кворк
	 *
	 * @param array $kwork кворк
	 * @return bool результат
	 */
	protected function isActiveAndFeatActive($kwork):bool {
		return $kwork["active"] == \KworkManager::STATUS_ACTIVE
			&& $kwork["feat"] == \KworkManager::FEAT_ACTIVE;
	}
}