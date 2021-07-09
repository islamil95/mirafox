<?php


namespace Order\Stages;


class OrderStageComparer {

	/**
	 * Сравнивает есть ли значительные изменения по этапам
	 *
	 * @param \Model\OrderStages\OrderStage[] $suggestedStages
	 * @param \Model\OrderStages\OrderStage[] $savedStages
	 *
	 * @return bool
	 */
	public static function hasImportantChanges(array $suggestedStages, array $savedStages) {
		if (count($suggestedStages) != count($savedStages)) {
			return true;
		}

		foreach ($suggestedStages as $number => $suggestedStage) {
			if (abs($suggestedStage->payer_price - $savedStages[$number]->payer_price) > 0.01) {
				return true;
			}
			if ($suggestedStage->title != $savedStages[$number]->title) {
				return true;
			}
		}

		return false;
	}
}