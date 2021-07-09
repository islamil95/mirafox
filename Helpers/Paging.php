<?php

class Paging {

    /**
     * Текущая страница
     * @var int
     */
	public $cur_page;
    /**
     * Записей на страницу
     * @var mixed
     */
	public $items_per_page;
    /**
     * Параметр со страницей
     * @var string
     */
	public $request_name;
    /**
     * Список страниц
     * @var array
     */
    public $customPages = [];

    /**
     * Paging constructor.
     * @param string $request_page
     */
	public function __construct($request_page = 'page') {
		$this->request_name = $request_page;
		$this->items_per_page = App::config("per_page_items") ?: 10;
		$this->cur_page = self::getPage($request_page);
	}

    /**
     * Задать список страниц
     * @param $pages
     */
	public function setCustomPages($pages) {
	    $this->customPages = $pages;
    }

    /**
     * Текущая страница
     * @param string $request_page
     * @return int
     */
	public static function getPage($request_page = 'page') {
		$page = (self::isPost($request_page)) ? (int) post($request_page) : (int) request($request_page);

		return $page < 1 ? 1 : $page;
	}

    /**
     * Передан ли параметр страниы POST
     * @param string $request_page
     * @return bool
     */
	public static function isPost($request_page = 'page') {
		return (int) post($request_page) > 0;
	}

    /**
     * Смещение записей
     * @return float|int|mixed
     */
	public function getPagingStart() {
	    if($this->customPages) {
	        $slice = array_slice($this->customPages, 0, $this->cur_page - 1);
            $pagingStart =  array_sum($slice);
        } else {
            $pagingStart = $this->cur_page > 1 ? ($this->cur_page - 1) * $this->items_per_page : 0;
        }

		$maxResults = App::config('maximum_results');
		if ($pagingStart > $maxResults)
			$pagingStart = $maxResults;

		return $pagingStart;
	}

    /**
     * Количество записей на страницу
     * @return mixed
     */
	public function getItemsPerPage() {
        if(isset($this->customPages[$this->cur_page - 1])) {
            return $this->customPages[$this->cur_page - 1];
        } else {
            return $this->items_per_page;
        }
    }

    /**
     * Задать максимально число страниц
     * @param $pageName
     * @param string $pageParam
     */
	public function setMaxAllowPage($pageName, $pageParam = "") {
		$session = Session\SessionContainer::getSession();
		$key = "list_" . md5($pageName . mb_strtolower(trim($pageParam)));

		if ($session->notExist($key) || $this->cur_page > $session->get($key)) {
			$session->set($key, $this->cur_page);
		}
	}

    /**
     * Максимально число страниц
     * @param $pageName
     * @param string $pageParam
     * @return mixed
     */
	public function getMaxAllowPage($pageName, $pageParam = "") {
		$session = Session\SessionContainer::getSession();
		$key = "list_" . md5($pageName . mb_strtolower(trim($pageParam)));
		return $session->get($key, 1);
	}

	/**
	 * Сформировать html-код переходов по страницам
	 * @param int $startPage Номер текущий страницы
	 * @param int $totalRows Общее количество результатов
	 * @param string $baseUrl адрес страницы
	 * @param string $add1 добавка к GET параметрам
	 * @return string html-код
	 */
	public function getPagination($startPage, $totalRows, $baseUrl = '', $add1 = "") {

		$pageLinks = "";

		if ($add1 && !preg_match("/^&/", $add1)) {
			$add1 = "&" . $add1;
		}

        if($this->customPages) {
            $lastPage = count($this->customPages);
        } else {
            $lastPage = ceil($totalRows / $this->items_per_page);
        }

		$theprevpage = $startPage - 1;
		$thenextpage = $startPage + 1;
		if ($lastPage > 1 && $startPage > 0) {
			if ($startPage > 1) {
				$pageLinks.="<a href='{$baseUrl}?page=1{$add1}' title='Первая'>Первая</a>&nbsp;";
				$pageLinks.="<a href='{$baseUrl}?page={$theprevpage}{$add1}'>Назад</a>&nbsp;";
			};
			$counter = 0;
			$lowercount = $startPage - 5;
			if ($lowercount <= 0)
				$lowercount = 1;
			while ($lowercount < $startPage) {
				$pageLinks.="<a href='{$baseUrl}?page={$lowercount}{$add1}'>{$lowercount}</a>&nbsp;";
				$lowercount++;
				$counter++;
			}
			$pageLinks.=$startPage . "&nbsp;";
			$uppercounter = $startPage + 1;
			while (($uppercounter < $startPage + 10 - $counter) && ($uppercounter <= $lastPage)) {
				$pageLinks.="<a href='{$baseUrl}?page={$uppercounter}{$add1}'>{$uppercounter}</a>&nbsp;";
				$uppercounter++;
			}
			if ($startPage < $lastPage) {
				$pageLinks.="<a href='{$baseUrl}?page={$thenextpage}{$add1}'>Следующая</a>&nbsp;";
				$pageLinks.="<a href='{$baseUrl}?page={$lastPage}{$add1}' title='Последняя'>Последняя</a>&nbsp;";
			};
		}
		return $pageLinks;
	}

    /**
     * Сформировать html-код переходов по страницам для ajax
     * @param $totalRows
     * @param $jsFunction
     * @return string
     */
	public function ajaxPagination($totalRows, $jsFunction) {
		$pageLinks = "";
		$startPage = $this->cur_page;

        if($this->customPages) {
            $lastPage = count($this->customPages);
        } else {
            $lastPage = ceil($totalRows / $this->items_per_page);
        }

		$theprevpage = $startPage - 1;
		$thenextpage = $startPage + 1;
		if ($lastPage > 1 && $startPage > 0) {
			if ($startPage > 1) {
				$pageLinks.="<a onclick='$jsFunction(1);' title='Первая'>Первая</a>&nbsp;";
				$pageLinks.="<a onclick='$jsFunction({$theprevpage});'>Назад</a>&nbsp;";
			};
			$counter = 0;
			$lowercount = $startPage - 5;
			if ($lowercount <= 0)
				$lowercount = 1;
			while ($lowercount < $startPage) {
				$pageLinks.="<a onclick='$jsFunction({$lowercount});'>{$lowercount}</a>&nbsp;";
				$lowercount++;
				$counter++;
			}
			$pageLinks.=$startPage . "&nbsp;";
			$uppercounter = $startPage + 1;
			while (($uppercounter < $startPage + 10 - $counter) && ($uppercounter <= $lastPage)) {
				$pageLinks.="<a onclick='$jsFunction({$uppercounter});'>{$uppercounter}</a>&nbsp;";
				$uppercounter++;
			}
			if ($startPage < $lastPage) {
				$pageLinks.="<a onclick='$jsFunction({$thenextpage});'>Следующая</a>&nbsp;";
				$pageLinks.="<a onclick='$jsFunction({$lastPage});' title='Последняя'>Последняя</a>&nbsp;";
			};
		}
		return $pageLinks;
	}

	/**
	 * Получить данные о пагинации
	 *
	 * @param int $totalRows – Колличество объектов всего
	 * @return array[] [page => int, title => string, type => active|non-active]
	 */
	public function getPaginationLinks(int $totalRows) {
		$pageLinks = [];
		$startPage = $this->cur_page;
		if($this->customPages) {
			$lastPage = count($this->customPages);
		} else {
			$lastPage = ceil($totalRows / $this->items_per_page);
		}
		$theprevpage = $startPage - 1;
		$thenextpage = $startPage + 1;
		if ($lastPage > 1 && $startPage > 0) {
			if ($startPage > 1) {
				$pageLinks[] = ['page' => 1, 'title' => 'Первая', 'type'=>'active'];
				$pageLinks[] = ['page' => $theprevpage, 'title' => 'Назад', 'type'=>'active'];
			}
			$counter = 0;
			$lowercount = $startPage - 5;
			if ($lowercount <= 0) {$lowercount = 1;}
			while ($lowercount < $startPage) {
				$pageLinks[] = ['page' => $lowercount, 'title' => $lowercount, 'type'=>'active'];
				$lowercount++;
				$counter++;
			}
			$pageLinks[] = ['page' => $startPage, 'title' => $startPage, 'type'=>'not-active'];
			$uppercounter = $startPage + 1;
			while (($uppercounter < $startPage + 10 - $counter) && ($uppercounter <= $lastPage)) {
				$pageLinks[] = ['page' => $uppercounter, 'title' => $uppercounter, 'type'=>'active'];
				$uppercounter++;
			}
			if ($startPage < $lastPage) {
				$pageLinks[] = ['page' => $thenextpage, 'title' => 'Следующая', 'type'=>'active'];
				$pageLinks[] = ['page' => $lastPage, 'title' => 'Последняя', 'type'=>'active'];
			}
		}
		return $pageLinks;
	}

	/**
	 * Возвращает объект PaginationData с данными для пагинации, но без ссылок
	 *
	 * @param int $totalRows
	 * @return PaginationData
	 */
	protected function generatePaginationData(int $totalRows): PaginationData {
		$paginationData = new PaginationData();
		$pagingStart = $this->getPagingStart();
		$paginationData->currentPage = $this->cur_page;

		if ($totalRows > 0) {
			if ($totalRows <= App::config('maximum_results')) {
				$paginationData->total = $totalRows;
			} else {
				$paginationData->total = App::config('maximum_results');
			}

			if (($ost = $paginationData->total % $this->items_per_page) && $paginationData->currentPage == $topPage) {
				$curCount = $ost;
			} else {
				$curCount = $this->items_per_page;
			}

			$paginationData->start = $pagingStart + 1;
			$paginationData->end = $pagingStart + $curCount;
		}

		return $paginationData;
	}

	/**
	 * Возвращает объект PaginationData с данными для пагинации
	 *
	 * @param int $totalRows
	 * @return PaginationData
	 */
	public function getPaginationData(int $totalRows): PaginationData {
		$paginationData = $this->generatePaginationData($totalRows);
		if ($totalRows > 0) {
			$paginationData->pageLinks = $this->getPagination($this->cur_page, $totalRows);
		}

		return $paginationData;
	}

	/**
	 * Для POST запросов возвращает объект PaginationData с данными для пагинации
	 * Подключить administrator\js\table_tools.js для работы!
	 *
	 * @param int $totalRows
	 * @return \PaginationData
	 */
	public function getPostPaginationData(int $totalRows): PaginationData {
		$paginationData = $this->generatePaginationData($totalRows);

		if ($totalRows > 0) {
			$topPage = ceil($paginationData->total / $this->items_per_page);
			$prevPage = $paginationData->currentPage - 1;
			$nextPage = $paginationData->currentPage + 1;
			if ($topPage > 1 && $paginationData->currentPage > 0) {
				$pageClass = "class='js-table-pagination'";
				if ($paginationData->currentPage > 1) {
					$paginationData->pageLinks .= "<a $pageClass data-page-num='1' title='first page'>Первая</a>&nbsp;";
					$paginationData->pageLinks .= "<a $pageClass data-page-num='$prevPage'>Предыдущая</a>&nbsp;";
				};
				$counter = 0;
				$lowerCount = $paginationData->currentPage - 5;
				if ($lowerCount <= 0)
					$lowerCount = 1;
				while ($lowerCount < $paginationData->currentPage) {
					$paginationData->pageLinks .= "<a $pageClass data-page-num='$lowerCount'>$lowerCount</a>&nbsp;";
					$lowerCount++;
					$counter++;
				}
				$paginationData->pageLinks .= $paginationData->currentPage . "&nbsp;";
				$upperCounter = $paginationData->currentPage + 1;
				while (($upperCounter < $paginationData->currentPage + 10 - $counter) && ($upperCounter <= $topPage)) {
					$paginationData->pageLinks .= "<a $pageClass data-page-num='$upperCounter'>$upperCounter</a>&nbsp;";
					$upperCounter++;
				}
				if ($paginationData->currentPage < $topPage) {
					$paginationData->pageLinks .= "<a $pageClass data-page-num='$nextPage'>Следующая</a>&nbsp;";
					$paginationData->pageLinks .= "<a $pageClass data-page-num='$topPage' title='last page'>Последняя</a>&nbsp;";
				};
			}
		}

		return $paginationData;
	}
}
