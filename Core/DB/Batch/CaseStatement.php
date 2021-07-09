<?php declare(strict_types=1);

namespace Core\DB\Batch;

/**
 * Упаковка WHEN {$key} THEN ?
 *
 * Class CaseStatement
 * @package Core\DB\Batch
 */
final class CaseStatement {
	/**
	 * @var mixed
	 */
	private $statement;

	/**
	 * @var mixed
	 */
	private $value;

	/**
	 * @param mixed $key
	 * @param mixed $value
	 */
	public function __construct($key, $value) {
		$this->statement = "WHEN {$key} THEN ?";
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function getStatement(): string {
		return $this->statement;
	}

	/**
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}
}