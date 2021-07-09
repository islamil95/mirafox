<?php

namespace Core\Response;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Отдать файл на скачивание
 *
 * Class DownloadFileResponse
 * @package Core\Response
 */
class DownloadFileResponse extends Response {

	/**
	 * Конструктор
	 * @param string $pathToFile полный путь до файла
	 * @param string $name имя файла, которое будет отображаться пользователю
	 * @return type
	 */
	public function __construct(string $pathToFile, string $name) {
		// Транслитерировать $name и удалить кавычки (мягкий и твердый знаки в транслитерации)
		$name = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $name);
		$name = str_replace("ʹ", "", $name);
		$name = $name[0] == "." ? "file" . $name : $name;		
		$fileContents = file_get_contents($pathToFile);
		parent::__construct($fileContents);
        $disposition = $this->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $name
        );
        $this->headers->set('Content-Disposition', $disposition);
	}

}