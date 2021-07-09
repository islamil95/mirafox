<?php declare(strict_types=1);

namespace Helpers\Order\Stages\Actions\Dto;

use Helpers\PayerLevel\Dto\UpdatePayerLevelResultDto;

final class PayerApproveStagesActionResultDto {
	/**
	 * id созданных треков
	 *
	 * @var int[]
	 */
	private $createdTrackIds;

	/**
	 * Результаты обновления уровня покупателя и статуса суперпокупатель
	 *
	 * @var UpdatePayerLevelResultDto|null
	 */
	private $updatePayerLevelResult;

	/**
	 * id созданных треков
	 *
	 * @return int[]
	 */
	public function getCreatedTrackIds(): array {
		return $this->createdTrackIds;
	}

	/**
	 * id созданных треков
	 *
	 * @param int[] $trackIds
	 */
	public function setCreatedTrackIds(array $trackIds): void {
		$this->createdTrackIds = $trackIds;
	}

	/**
	 * Результаты обновления уровня покупателя и статуса суперпокупатель
	 *
	 * @return UpdatePayerLevelResultDto|null
	 */
	public function getUpdatePayerLevelResult():? UpdatePayerLevelResultDto {
		return $this->updatePayerLevelResult;
	}

	/**
	 * Результаты обновления уровня покупателя и статуса суперпокупатель
	 *
	 * @param UpdatePayerLevelResultDto $updatePayerLevelResult
	 */
	public function setUpdatePayerLevelResult(UpdatePayerLevelResultDto $updatePayerLevelResult): void {
		$this->updatePayerLevelResult = $updatePayerLevelResult;
	}
}
