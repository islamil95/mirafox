<?php


namespace Core\Response;


use Symfony\Component\HttpFoundation\JsonResponse;

class SearchResultResponse extends JsonResponse {

	private $responseData;
	public function __construct() {
		parent::__construct([], 200, [], false);
		$this->responseData = [
			"html" => "",
			"paging" => [
				"page"  => 0,
				"items_per_page" => 0,
				"total" => 0,
			],
		];

		$this->setData($this->responseData);
	}

	public function setHtml(string $html) {
		$this->responseData["html"] = $html;
		return $this->setData($this->responseData);
	}

	public function setCurrentPage($page) {
		$this->responseData["paging"]["page"] = (int) $page;
		return $this->setData($this->responseData);
	}

	public function setItemsOnPage($itemsOnPage) {
		$this->responseData["paging"]["items_per_page"] = $itemsOnPage;
		return $this->setData($this->responseData);
	}

	public function setTotal($total) {
		$this->responseData["paging"]["total"] = $total;
		return $this->setData($this->responseData);
	}
}