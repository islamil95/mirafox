<?php

/**
 * Класс для работы с изображениями
 */
class CImage {
	var $image;
	var $image_type;
	var $error;

	const MAX_PHOTO_WIDTH = 6000;
	const MAX_PHOTO_HEIGHT = 6000;
	const ERROR_NOT_AVAILABLE_TYPE = 1;

	private static $_availableTypes = [
		IMAGETYPE_JPEG => "jpg",
		IMAGETYPE_PNG => "png",
		IMAGETYPE_GIF => "gif",
	];

	public function __construct($image = null) {
		if (is_resource($image)) {
			$this->image = $image;
		}
	}

	/**
	 * Загрузка изображения из файла
	 *
	 * @param string $fileName Имя файла
	 *
	 * @return array|false Возвращает информацию о файле
	 */
	function load($fileName) {
		$imageInfo = self::check($fileName, $error);

		if (!$imageInfo) {
			if ($error) {
				$this->error = $error;
			}
			return false;
		}

		$this->image_type = $imageInfo[2];

		if ($this->image_type == IMAGETYPE_JPEG)
			$this->image = imagecreatefromjpeg($fileName);

		if ($this->image_type == IMAGETYPE_GIF)
			$this->image = imagecreatefromgif($fileName);

		if ($this->image_type == IMAGETYPE_PNG) {
			$this->image = imagecreatefrompng($fileName);
			imageAlphaBlending($this->image, true);
			imageSaveAlpha($this->image, true);
		}

		return $imageInfo;
	}

	/**
	 * Проверка изображения
	 *
	 * @param string $fileName Имя файла изображения
	 * @param &$error Выходной параметр - код ошибки
	 *
	 * @return array|false Возвращает информацию о файле
	 */
	public static function check($fileName, &$error) {
		if (strpos(strtolower(trim($fileName)), 'phar://') === 0) {
			$error = self::ERROR_NOT_AVAILABLE_TYPE;
			return false;
		}

		$imageInfo = @getimagesize($fileName);

		if ($imageInfo === false) {
			return false;
		}

		if ($imageInfo[0] > self::MAX_PHOTO_WIDTH || $imageInfo[1] > self::MAX_PHOTO_HEIGHT) {
			return false;
		}

		$imageType = $imageInfo[2];

		if (!isset(self::$_availableTypes[$imageType])) {
			$error = self::ERROR_NOT_AVAILABLE_TYPE;
			return false;
		}

		return $imageInfo;
	}

	/**
	 * Получаем размер изображения и ориентацию
	 *
	 * @param string $fileName Имя файла изображения
	 *
	 * @return array|false Возвращает [width, height, orientation]|false
	 */
	public static function getSizeImage($fileName) {
		$imageInfo = self::check($fileName, $error);
		if ($imageInfo) {	
			return [
				"width" => $imageInfo[0], 
				"height" => $imageInfo[1],
				"orientation" => ($imageInfo[0] > $imageInfo[1]) ? "landscape" : "portrait",
			];			 
		}
		return false;
	}

	public static function getAvailableTypes() {
		return self::$_availableTypes;
	}

	/**
	 * Получение расширения файла 
	 *
	 * @param array $imageInfo Информация о файле, возвращенная функцией getimagesize
	 *
	 * @return string
	 */
	public static function getExtension(array $imageInfo) {
		return self::$_availableTypes[$imageInfo[2]];
	}

	/**
	 * Получение расширения файла с заменой png на jpg
	 *
	 * @param array $imageInfo Информация о файле, возвращенная функцией getimagesize
	 *
	 * @return string
	 */
	public static function getExtensionWithConvert(array $imageInfo) {
		// png преобразуем в jpg - само преобразование производится в методе resizeImage
		if ($imageInfo[2] === IMAGETYPE_PNG) {
			$fileExtension = "jpg";
		} else {
			$fileExtension = self::getExtension($imageInfo);
		}
		return $fileExtension;
	}

	function fit($width, $height) {
		if (!$this->image)
			return false;

		$width = min($width, $this->getWidth());
		$height = min($height, $this->getHeight());

		$ratio = min($width / $this->getWidth(), $height / $this->getHeight());
		$width = max(1, $this->getWidth() * $ratio);
		$height = max(1, $this->getheight() * $ratio);

		$this->resize($width, $height);
	}

	function crop($width, $height) {
		if (!$this->image)
			return false;

		$width = min($width, $this->getWidth());
		$height = min($height, $this->getHeight());

		$ratio = min($this->getWidth() / $width, $this->getHeight() / $height);
		$imgWidth = max(1, $width * $ratio);
		$imgHeight = max(1, $height * $ratio);

		$this->resize($width, $height, $imgWidth, $imgHeight);
	}

	function resize($width, $height, $imgWidth = null, $imgHeight = null) {
		if (!$this->image)
			return false;

		$imgWidth = $imgWidth ? $imgWidth : $this->getWidth();
		$imgHeight = $imgHeight ? $imgHeight : $this->getHeight();

		$width = round($width);
		$height = round($height);
		$imgWidth = round($imgWidth);
		$imgHeight = round($imgHeight);

		$new_image = imageCreateTrueColor($width, $height);

		imageAlphaBlending($new_image, false);
		imageSaveAlpha($new_image, true);

		imageCopyResampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $imgWidth, $imgHeight);
		$this->image = $new_image;
	}

	function cut($x, $y, $width, $height) {
		if (!$this->image)
			return false;

		if (!$width || !$height)
			return false;

		$new_image = imagecreatetruecolor($width, $height);

		$white = imagecolorallocate($new_image, 255, 255, 255);
		imagefill($new_image, 0, 0, $white);

		imagecopy($new_image, $this->image, 0, 0, $x, $y, $width, $height);

		$this->image = $new_image;
	}
	
	/**
	 * Сохранить изображение. По умолчанию все изображения сохраняются в JPEG,
	 * независимо от того, в каком формате изображение было загружено методом
	 * load (если не передан флаг isAllowPng - см. ниже)
	 * @param string $fileName имя файла
	 * @param int $quality качество сохраняемого изображения для JPEG (от 1 до 100)
	 * @param bool $isAllowPng разрешить сохранение в PNG. Если true и в метод
	 * load был передан PNG-файл, он сохранится в PNG, иначе в JPEG. При сохранении
	 * в PNG качество quality игнорируется
	 * @return void
	 */
	function save($fileName, $quality = 100, $isAllowPng = false)
	{
		if(!$this->image)
			return false;

		if ($this->image_type == IMAGETYPE_PNG && $isAllowPng) {
			imagepng($this->image, $fileName);
		} else {
			imagejpeg($this->image, $fileName, $quality);
		}
		chmod($fileName, 0664);
	}

	function getWidth() {
		return imagesx($this->image);
	}

	function getHeight() {
		return imagesy($this->image);
	}

	/**
	 * Обрезать по массиву user_sizes
	 *
	 * @param \Image\ImageCrop $sizes
	 */
	public function cropToUserSizes(\Image\ImageCrop $sizes) {
		if ($sizes->minW && $sizes->minH && !is_null($sizes->x1) && !is_null($sizes->y1) && !is_null($sizes->x2) && !is_null($sizes->y2)) {
			$this->cropToUserSizesByMinHW($sizes);
		} else {
			$this->cropToUserSizesByHW($sizes);
		}
	}

	/**
	 * Вариант обрезки изображения по параметрам w h x y
	 *
	 * @param \Image\ImageCrop $sizes
	 */
	public function cropToUserSizesByHW(\Image\ImageCrop $sizes) {
		$this->cut(
			(int)floor($sizes->x * $this->getWidth()),
			(int)floor($sizes->y * $this->getHeight()),
			(int)ceil($sizes->w * $this->getWidth()),
			(int)ceil($sizes->h * $this->getHeight()));
	}

	/**
	 * Вариант обрезки изображения по параметрам minW MinH x1 y1 x2 y2
	 *
	 * @param \Image\ImageCrop $sizes
	 */
	private function cropToUserSizesByMinHW(\Image\ImageCrop $sizes) {
		if ($this->getHeight() > $this->getWidth()) {
			$scale = $this->getWidth() / $sizes->minW;
		} else {
			$scale = $this->getHeight() / $sizes->minH;
		}
		$x1Position = $sizes->x1 * $scale;
		$y1Position = $sizes->y1 * $scale;
		$x2Position = $sizes->x2 * $scale;
		$y2Position = $sizes->y2 * $scale;
		$width = $x2Position - $x1Position;
		$height = $y2Position - $y1Position;
		$this->cut(
			(int)floor($x1Position),
			(int)floor($y1Position),
			(int)ceil($width),
			(int)ceil($height));
	}

	/**
	 * Делает изображение более резким
	 */
	/* function sharp() {
		$matrix = array(array(-1, -1, -1), array(-1, 16, -1), array(-1, -1, -1));
		imageconvolution($this->image, $matrix, 8, 0);
	} */

	/**
	 * Копирует изображение
	 * @return mixed Возвращает новый экземпляр CImage или false в случае ошибки
	 */
	function copy() {
		if (!$this->image) {
			return false;
		}

		$new_image = imageCreateTrueColor($this->getWidth(), $this->getHeight());

		imageAlphaBlending($new_image, false);
		imageSaveAlpha($new_image, true);

		$result = imageCopy($new_image, $this->image, 0, 0, 0, 0, $this->getWidth(), $this->getHeight());

		if (!$result) {
			return false;
		}

		return new self($new_image);
	}
}
