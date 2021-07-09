<?php declare(strict_types=1);

namespace Helpers\Kwork\GetKworks;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Model\Kwork;
use StatusManager;

/**
 * Class GetKworksService
 * @package Helpers\Kwork\GetKworks
 */
final class GetKworksByCategoryService {
	const SORT_POPULAR = 'popular';
	const SORT_NEW = 'new';

	/**
	 * @var string
	 */
	private $lang;

	/**
	 * @var int
	 */
	private $limit = 0;

	/**
	 * @var int
	 */
	private $offset = 0;

	/**
	 * @return string
	 */
	public function getLang(): string {
		return $this->lang;
	}

	/**
	 * @param string $lang
	 * @return self
	 */
	public function setLang(string $lang): self {
		$this->lang = $lang;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getLimit(): int {
		return $this->limit;
	}

	/**
	 * @param int $limit
	 * @return self
	 */
	public function setLimit(int $limit): self {
		$this->limit = $limit;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getOffset(): int {
		return $this->offset;
	}

	/**
	 * @param int $offset
	 * @return self
	 */
	public function setOffset(int $offset): self {
		$this->offset = $offset;

		return $this;
	}

	/**
	 * @param int $categoryId
	 * @param string|null $sort
	 * @param int|null $attributeId
	 * @return Collection
	 */
	public function getByCategoryId(int $categoryId, string $sort = null, int $attributeId = null): Collection {
		$query = Kwork::query()
			->with(['user', 'minPricePackage', 'kworkCategory'])
			->select([
				'PID',
				'gtitle',
				'youtube',
				'days',
				'price',
				'photo',
				'rating',
				'queueCount',
				'USERID',
				'category',
				'url',
				'is_package',
				'lang',
				'bonus_text',
				'bonus_moderate_status',
			])
			->whereRaw(StatusManager::kworkListEnable(Kwork::TABLE_NAME))
			->where(Kwork::FIELD_CATEGORY, $categoryId)
			->where(Kwork::FIELD_LANG, $this->getLang())
			->when($attributeId, function(Builder $query) use ($attributeId) {
				return $query->join('kwork_attributes', function(JoinClause $join) use ($attributeId) {
					return $join
						->on('kwork_attributes.kwork_id', '=', Kwork::TABLE_NAME . '.PID')
						->where('kwork_attributes.attribute_id', '=', $attributeId);
				});
			});

		if ($sort) {
			switch ($sort) {
				case self::SORT_POPULAR:
					/** @var Builder $query */
					$query
						->orderByDesc(Kwork::FIELD_CRATING)
						->orderByDesc(Kwork::FIELD_BOOKMARK_COUNT)
						->orderByDesc(Kwork::FIELD_PID);
					break;
				case self::SORT_NEW:
					/** @var Builder $query */
					$query
						->orderByDesc(Kwork::FIELD_PID);
					break;
				default:
					throw new \InvalidArgumentException('Unexpected sort parameter');
			}
		}

		return $query
			->limit($this->limit)
			->offset($this->offset)
			->get();
	}
}