<?php

namespace Model\Scopes\Cases;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Model\CaseModel;

/**
 * Параметры сортировки модели кейсов пользователей
 *
 * Class CaseSorting
 * @package Model\Scopes\Cases
 */
class CaseSorting implements Scope {

	/**
	 * Сортировка кейсов в верном порядке
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder $builder
	 * @param  \Illuminate\Database\Eloquent\Model $model
	 * @return void
	 */
	public function apply(Builder $builder, Model $model) {
		$builder
			->orderByDesc(CaseModel::FIELD_RATING)
			->orderByDesc(CaseModel::FIELD_ID);
	}
}