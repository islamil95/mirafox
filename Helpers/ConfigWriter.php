<?php declare(strict_types=1);

namespace Helpers;

use Configurator;

/**
 * Класс, для изменения файлов конфигурации
 *
 * Class ConfigWriter
 * @package Helpers
 */
final class ConfigWriter {
	/**
	 * @var string
	 */
	private $filename;

	/**
	 * Постфикс, добавляемый в конец строки при записи в конфиг
	 *
	 * @var string
	 */
	private $suffix = " ;automatically changed by Config Writer";

	/**
	 * Массив строк конфига
	 *
	 * @var string[]
	 */
	private $configData;

	/**
	 * @var bool
	 */
	private $isConfigChanged = false;

	/**
	 * Длина строки в конфиге (сколько символов) до '= %value%'
	 *
	 * @var int
	 */
	private $configLengthBeforeEqualSign;

	/**
	 * @param string $filename
	 */
	public function __construct(string $filename) {
		$this->filename = $filename;

		$this->init();
	}

	/**
	 * Установить параметр конфига
	 *
	 * @param string $param
	 * @param $value
	 * @return ConfigWriter
	 */
	public function set(string $param, $value): self {
		$configParams = array_map(function($line) {
			return Configurator::getInstance()->parseRow($line)[0];
		}, $this->configData);

		if (!in_array($param, $configParams, true)) {
			// Если параметра еще нет в текущем файле конфига, добавляем в конец
			$this->configData[] = PHP_EOL . $this->composeLine($param, $value);

			$this->isConfigChanged = true;
		} else {
			foreach ($this->configData as $lineIndex => $configLine) {
				[$currentParam, $currentValue] = Configurator::getInstance()->parseRow($configLine);

				if ($currentParam === $param && $currentValue !== $value) {
					$this->configData[$lineIndex] = $this->composeLine($param, $value);

					$this->isConfigChanged = true;
				}
			}
		}

		return $this;
	}

	/**
	 * Сохранить изменения в конфиг
	 */
	public function save(): void {
		if ($this->isConfigChanged) {
			file_put_contents($this->filename, $this->configData);
		}
	}

	/**
	 * Установить постфикс, добавляемый в конец строки при записи в конфиг
	 *
	 * @param string $suffix
	 * @return $this
	 */
	public function setSuffix(string $suffix): self {
		$this->suffix = $suffix;

		return $this;
	}

	private function init(): void {
		$this->configData = file($this->filename);

		$this->calcConfigLengthBeforeEqualSign();
	}

	/**
	 * Определяет длину строки в конфиге (сколько символов) до '= %value%'
	 */
	private function calcConfigLengthBeforeEqualSign(): void {
		foreach ($this->configData as $lineIndex => $configLine) {
			[$currentParam, $currentValue] = Configurator::getInstance()->parseRow($configLine);
			if ($currentParam && $currentValue) {
				$parts = explode("=", $configLine);

				$this->configLengthBeforeEqualSign = strlen($parts[0]);

				break;
			}
		}
	}

	/**
	 * Создать строку для записи в конфиг
	 *
	 * @param string $param
	 * @param $value
	 * @return string
	 */
	private function composeLine(string $param, $value): string {
		$stub = $this->generateStub($param);

		return $param . $stub . "= " . $value . $this->suffix . PHP_EOL;
	}

	/**
	 * Сгенерировать заглушку-расширитель строки для форматирования конфига
	 * (для выравнивания по горизонтали знака = в конфиге)
	 *
	 * @param string $param
	 * @return string
	 */
	private function generateStub(string $param): string {
		$stub = "";

		$stubSize = $this->configLengthBeforeEqualSign - strlen($param);

		$stubSize = $stubSize ?: 0;

		while ($stubSize) {
			$stub .= " ";

			$stubSize--;
		}

		return $stub;
	}
}