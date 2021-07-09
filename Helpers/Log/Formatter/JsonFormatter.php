<?php declare(strict_types=1);

namespace Helpers\Log\Formatter;

use Core\Exception\JsonEncodingException;
use Monolog\Formatter\JsonFormatter as MonologJsonFormatter;

/**
 * Если кодирование json окончилось неудачно, форматтер бросает JsonEncodingException
 *
 * Class JsonFormatter
 * @package Helpers\Log\Formatter
 */
final class JsonFormatter extends MonologJsonFormatter {
	/**
	 * Return the JSON representation of a value
	 *
	 * @param  mixed $data
	 * @param  bool $ignoreErrors
	 * @throws JsonEncodingException if encoding fails
	 * @return string|bool
	 */
	protected function toJson($data, bool $ignoreErrors = false) {
		$json = parent::toJson($data, $ignoreErrors);

		if (!$json) {
			throw new JsonEncodingException(print_r($data, true));
		}

		return $json;
	}

}