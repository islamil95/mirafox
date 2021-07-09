<?php


namespace Strategy\Track;

/**
 * Может ли перезаказать еще раз
 *
 * Class CanReOrderStrategy
 * @package Strategy\Track
 */
class CanReOrderStrategy extends AbstractTrackStrategy {

	/**
	 * @inheritdoc
	 */
	public function get() {
		global $actor;

		$kwork = $this->order->kwork;

		if (!$this->order->isPayer($this->getUserId())) {
			return false;
		}

		if (!$kwork->isFeat()) {
			return false;
		}

		if (!$this->order->orderPackage && $actor->lang != $kwork->lang && $actor->lang != \Translations::DEFAULT_LANG) {
			return false;
		}

		if (($kwork->is_package && is_null($this->order->orderPackage)) ||
			(!$kwork->is_package && !is_null($this->order->orderPackage) && $this->order->orderPackage->type !== "standard")
		) {
			return false;
		}

		$stayonUnblock = false;

		// #5995 Если пользователь находится на разблокировке мы можем делать заказ
		if ($kwork->active == \KworkManager::STATUS_SUSPEND) {
			$stayonUnblock = \UserManager::onQueueforUnblock($kwork->USERID);
		}

		// Если есть еще неоплаченные этапы в заказе то кнопку не показываем
		if ($this->order->has_stages && $this->order->hasNotReservedStages()) {
			return false;
		}

		if ($this->order->orderPackage) {
			if ((!in_array($kwork->active, [\KworkManager::STATUS_ACTIVE, \KworkManager::STATUS_PAUSE]) && !$stayonUnblock) ||
				($kwork->feat != \KworkManager::FEAT_ACTIVE && !$stayonUnblock)
			) {
				return false;
			}
		} else {
			if (!\KworkManager::canOrder($kwork->feat, $kwork->active) && !$stayonUnblock) {
				return false;
			}
		}

		return true;
	}
}