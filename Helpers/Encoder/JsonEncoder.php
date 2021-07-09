<?php
/**
 * Сериализация данных в json-строку
 *
 * @author Juriy Panasevich <juriy.panasevich@gmail.com>
 */

namespace Encoder;

use Illuminate\Database\Eloquent\Model;

class JsonEncoder {

	const DATA_ARRAY = "array";
	const DATA_OBJECT = "object";
	const DATA_SCALAR = "scalar";

	/**
	 * Сериализовать данные
	 *
	 * @param $data
	 * @return string
	 */
	public static function encode($data) {
		return self::encodeToJson(self::prepare($data));
	}

	/**
	 * Десериализовать строку
	 *
	 * @param $data
	 * @return mixed Если был сериализован объект - вернется объект, если массив - массив, или обычное скалярное значение
	 */
	public static function decode($data) {
		if (!$data) {
			return $data;
		}
		$decoded = self::decodeFromJson($data);
		if (!$decoded) {
			return unserialize($data);
		}
		return self::createEntity($decoded);
	}
	/*
	public static function decode($data) {
		if (!$data) {
			return $data;
		}
		$decoded = self::decodeFromJson($data);
		if (!$decoded) {
			return $decoded;
		}
		return self::createEntity($decoded);
	}
	*/

	/**
	 * Оборачиваем данные с указанием типа для десериализации
	 *
	 * @param $data
	 * @return array
	 */
	protected static function prepare($data): array {
		$prepared = [
			"data" => $data,
			"type" => self::DATA_SCALAR,
		];
		if (is_array($data)) {
			$prepared["type"] = self::DATA_ARRAY;
			$prepared["data"] = array_map(function($value) {
				return self::prepare($value);
			}, $data);
		}
		if (is_object($data)) {
			if ($data instanceof \Closure) {
				$prepared["data"] = null;
				return $prepared;
			}
			$prepared["type"] = self::DATA_OBJECT;
			$prepared["class"] = str_replace("\\", ":", get_class($data));
			$prepared["data"] = self::encodeObject($data);
		}

		return $prepared;
	}

	/**
	 * Кодируем в JSON
	 *
	 * @param $data
	 * @return string
	 */
	protected static function encodeToJson($data): string {
		return json_encode($data);
	}

	/**
	 * Декодируем строку
	 *
	 * @param $data
	 * @param bool $assoc	Нужно вернуть ассоциативный массив
	 * @return mixed
	 */
	protected static function decodeFromJson($data, $assoc = true) {
		return json_decode((string)$data, $assoc, 1024);
	}

	/**
	 * Кодирование объекта в строку
	 *
	 * @param $data
	 * @return string
	 */
	protected static function encodeObject($data): string {
		try {
			if ($data instanceof Model) {
				return self::encodeModel($data);
			}

			return self::encodeClass($data);
		} catch (\Throwable $e) {
			\Log::daily(__METHOD__ . ": " . $e->getMessage());
		}
		return "";
	}

	/**
	 * Декодирование строки в объект
	 *
	 * @param $data
	 * @return object
	 */
	protected static function decodeObject($data) {
		try {
			return self::decodeClass($data);
		} catch (\Throwable $e) {
			\Log::daily(__METHOD__ . ": " . $e->getMessage());
		}

		return null;
	}

	/**
	 * Декодирование строки в массив
	 *
	 * @param $data
	 * @return array
	 */
	protected static function decodeArray($data) {
		return array_map(function($value) {
			return self::createEntity($value);
		}, $data);
	}

	/**
	 * Кодирование объектов через reflection
	 *
	 * @param object $object
	 * @return string
	 * @throws \ReflectionException
	 */
	protected static function encodeClass($object): string {
		$reflection = new \ReflectionObject($object);
		$props = self::extractPropertiesFromReflection($reflection, $object);

		return self::encodeToJson($props);
	}

	protected static function encodeModel(Model $model) {
		$data = [
			"attributes" => $model->attributesToArray(),
		];

		return self::encodeToJson($data);
	}

	/**
	 * Извлечение свойств класса из объекта
	 *
	 * @param \ReflectionClass $reflection
	 * @param $obj
	 * @param array $skipped
	 * @return array
	 * @throws \ReflectionException
	 */
	protected static function extractPropertiesFromReflection(\ReflectionClass $reflection, $obj, $skipped = []) {
		$props = [];
		foreach ($reflection->getProperties() as $property) {
			if ($skipped[$property->getName()]) {
				continue;
			}
			$property->setAccessible(true);
			$value = $property->getValue($obj);
			$data = self::prepare($value);
			$data["property"] = $property->getName();
			$skipped[$property->getName()] = true;
			$props[] = $data;
		}
		$parent = $reflection->getParentClass();
		if ($parent) {
			$props = array_merge($props, self::extractPropertiesFromReflection($parent, $obj, $skipped));
		}

		return $props;
	}

	/**
	 * Декодирование объекта из строки
	 *
	 * @param $data
	 * @return object
	 * @throws \ReflectionException
	 */
	protected static function decodeClass($data) {
		$class = str_replace(":", "\\", $data["class"]);
		$reflection = new \ReflectionClass($class);
		$decoded = self::decodeFromJson($data["data"]);

		if ($reflection->isSubclassOf(\Closure::class)) {
			return null;
		}
		$instance = $reflection->newInstanceWithoutConstructor();
		if ($reflection->isSubclassOf(Model::class)) {
			/** @var Model $instance */
			$instance = new $class();
		}

		$instance = self::fillInstance($reflection, $decoded, $instance);

		return $instance;
	}

	/**
	 * Наполнение объекта данными
	 *
	 * @param \ReflectionClass $reflection
	 * @param array $decoded
	 * @param $instance
	 * @return mixed
	 * @throws \ReflectionException
	 */
	protected static function fillInstance(\ReflectionClass $reflection, array $decoded, $instance) {
		if ($instance instanceof \stdClass) {
			foreach ($decoded as $item) {
				$instance->{$item["property"]} = self::getValueFromDecodedReflection($decoded, $item["property"]);
			}

			return $instance;
		}

		if ($instance instanceof Model) {
			$instance = $instance->newFromBuilder($decoded["attributes"]);

			return $instance;
		}


		foreach ($reflection->getProperties() as $property) {
			$property->setAccessible(true);
			$value = self::getValueFromDecodedReflection($decoded, $property->getName());
			$property->setValue($instance, $value);
		}
		$parent = $reflection->getParentClass();
		if ($parent) {
			$instance = self::fillInstance($parent, $decoded, $instance);
		}

		return $instance;
	}

	/**
	 * Получить значение для вставки в свойство
	 *
	 * @param $decoded
	 * @param $name
	 * @return mixed|null
	 * @throws \ReflectionException
	 */
	protected static function getValueFromDecodedReflection($decoded, $name) {
		foreach ($decoded as $datum) {
			if ($datum["property"] !== $name) {
				continue;
			}
			if ($datum["type"] === self::DATA_SCALAR) {
				return $datum["data"];
			}
			if ($datum["type"] === self::DATA_ARRAY) {
				return self::decodeArray($datum["data"]);
			}
			if ($datum["type"] === self::DATA_OBJECT) {
				$class = str_replace(":", "\\", $datum["class"]);
				$reflection = new \ReflectionClass($class);
				$instance = $reflection->newInstanceWithoutConstructor();
				if ($reflection->isSubclassOf(Model::class)) {
					$instance = new $class;
				}

				return self::fillInstance($reflection, self::decodeFromJson($datum["data"]), $instance);
			}
		}
		return null;
	}

	/**
	 * Создать итоговую сущность - массив, объект, скаляр
	 *
	 * @param $decoded
	 * @return array|mixed|object
	 */
	protected static function createEntity($decoded) {
		if ($decoded["type"] === self::DATA_OBJECT) {
			return self::decodeObject($decoded);
		}
		if ($decoded["type"] === self::DATA_ARRAY) {
			return self::decodeArray($decoded["data"]);
		}

		return $decoded["data"];
	}
}