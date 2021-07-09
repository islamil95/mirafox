<?php
namespace Helpers;

use ExtrasManager;
use Helper;
use Image\ImageCrop;
use Image\KworkFirstPhoto;
use KworkManager;
use Model\Package\Kwork\KworkPackage;
use Model\TempImage;
use Model\UserData;
use OptionPriceManager;
use Symfony\Component\HttpFoundation\Request;
use Translations;
use UserManager;

class KworkSaveManager {

	const MODE_EDIT = "edit";
	const MODE_ADD = "add";

	private $kwork;
	private $description;
	private $categoryId;
	private $price;

	/**
	 * KworkSaveManager constructor.
	 * @param KworkManager $kwork
	 * @param Request $request
	 * @param string $mode
	 */
	public function __construct(KworkManager $kwork, Request $request, $mode = self::MODE_ADD) {
		$this->categoryId = (int) $request->get("category");
		$this->description = (string)html_entity_decode($request->get("description")) ?: "";
		$this->price = (float)preg_replace("/[^\d]/", "", $request->get("price"));

		$title = $request->get("title");

		$kwork->setTitle($title);
		$kwork->setDescription($this->description);
		$kwork->setCategoryId($this->categoryId);

		if ($mode == self::MODE_ADD) {
			global $actor;
			$kwork->setUserId($actor->id);
		}

		// при редактировании кворка не переводить из остановленного в активный
		if ($mode == self::MODE_ADD) {
			$kwork->setFeat(1);
		}

		$kwork->setQuick((int)$request->get("is_quick"));
		$kwork->setPrice($this->price);

		$this->kwork = $kwork;
	}

	/**
	 * Сохранить кворк
	 *
	 * @return false|KworkManager
	 */
	public function save() {
		return $this->kwork->save();
	}

	/**
	 * Получить поле кворка
	 *
	 * @param string $field Имя поле кворка
	 * @return mixed
	 */
	public function get($field) {
		$kwork = $this->kwork;
		return $kwork->get($field);
	}

	/**
	 * Получить id кворка
	 * @return int
	 */
	public function getKworkId() {
		return $this->kwork->getId();
	}
}