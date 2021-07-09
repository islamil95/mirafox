<?php

function get($name, $needClear = true)
{
	$data = isset($_GET[$name]) ? $_GET[$name] : "";

	if($needClear)
	{
		if(is_array($data))
		{
			$data = cleanitArray($data);
		}
		else
			$data = cleanit($data);
	}

	return $data;
}

function getAll($needClear = true)
{
	$data = [];
	foreach ($_GET as $key=>$val){
		$data[$key] = get($key, $needClear);
	}
	return $data;
}

/**
 * Получить параметр из $_POST
 *
 * @param string $name Название параметра
 * @param bool $needClear Нужно ли "очищать" значение
 * @param array $saveFromCleanChars Текст, который нужно сохранить при очищении, например "<=", т.к. он обрезается strip_tags внутри cleanit()
 * @return mixed|string
 */
function post($name, $needClear = true, $saveFromCleanChars = [])
{
	$data = isset($_POST[$name]) ? $_POST[$name] : "";

	if($needClear)
	{
		if(is_array($data))
		{
			$data = cleanitArray($data, $saveFromCleanChars);
		}
		else
			$data = cleanit($data, $saveFromCleanChars);
	}

	return $data;
}

function request($name, $needClear = true)
{
	$data = isset($_REQUEST[$name]) ? $_REQUEST[$name] : "";

	if($needClear)
	{
		if(is_array($data))
		{
			$data = cleanitArray($data);
		}
		else
			$data = cleanit($data);
	}

	return $data;
}

function mres($unescaped_string) {
	$escapedString = App::pdo()->quote($unescaped_string);
	if (mb_substr($escapedString, 0, 1) == "'") {
		return mb_substr($escapedString,1,-1);
	}
	return $escapedString;
}

function redirect($url, $status = false)
{
	if($status == 301){
		header('HTTP/1.1 301 Moved Permanently');
	}

	header("Location:" . $url);
	exit;
}
function redirectBack($url)
{
	Session\SessionContainer::getSession()->set('backURL', $_SERVER['REQUEST_URI']);
	redirect($url);
}
// делает первую букву заглавной
function mb_ucfirst($text)
{
	return mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1, mb_strlen($text));
}
// делает первую букву строчной
function mb_lcfirst($text)
{
	return mb_strtolower(mb_substr($text, 0, 1)) . mb_substr($text, 1, mb_strlen($text));
}


function insert_get_short_file_name($a) {
	$fileName = $a['filename'];
	if (mb_strlen($fileName) > 25) {
		$ext = mb_substr(strrchr($fileName, '.'), 1);
		$fileName = mb_substr($fileName, 0, 19) . '...' . $ext;
	}
	return $fileName;
}
function insert_get_package_name($a) {
	$type = $a['type'];
	return PackageManager::getName($type);
}
function insert_get_file_ico($a) {
	$fileName = $a['filename'];
	if (in_array(mb_substr($fileName, -3), ['doc', 'xls', 'rtf', 'txt']) || in_array(mb_substr($fileName, -4), ['docx', 'xlsx'])) {
		return 'doc';
	} else if (in_array(mb_substr($fileName, -3), ['zip', 'rar'])) {
		return 'zip';
	} else if (in_array(mb_substr($fileName, -3), ['png', 'jpg', 'gif', 'psd']) || in_array(mb_substr($fileName, -4), ['jpeg'])) {
		return 'image';
	} else if (in_array(mb_substr($fileName, -3), ['mp3', 'wav', 'avi'])) {
		return 'audio';
	} else {
		return 'zip';
	}
}

function insert_rate_search_list($a)
{
	if (App::config("redis.enable")) {
		$result = RedisManager::getInstance()->get(Enum\Redis\RedisAliases::INSERT_RATE_SEARCH_LIST);
		if ($result)
			return $result;
	}

	global $conn;

	$result = $conn->getList("SELECT id, text FROM search ORDER BY count desc LIMIT 6");

	if (App::config("redis.enable")) {
		RedisManager::getInstance()->set(Enum\Redis\RedisAliases::INSERT_RATE_SEARCH_LIST, $result, Helper::ONE_HOUR);
	}

	return $result;
}

function insert_bookmark_count($a) {
	global $conn;
	global $actor;
	$sql = "SELECT count(*) as total FROM bookmarks WHERE USERID = '" . mres($actor->id) . "'";
	$data = $conn->Execute($sql);
	return $data->fields['total'];
}

function filter_fox_messages($var) {
	$text = $var;
	$text = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1:", $text);
	$ret = ' ' . $text;
	$ret = preg_replace("#(^|[\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1foxreplacement", $ret);
	$ret = preg_replace("#(^|[\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1foxreplacement", $ret);
	$ret = preg_replace("#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1foxreplacement", $ret);
	$ret = mb_substr($ret, 1);

	if (preg_match("/foxreplacement/i", $ret)) {
		return "1";
	} else {
		return "0";
	}
}

function insert_get_packs($a) {
	global $conn;
	global $actor;
	if (App::config('enable_levels') == "1" && App::config('price_mode') == "3") {
		$me = $actor ? $actor->id : 0;
		if ($me > 0) {
			$query = "select level from members where USERID='" . mres($me) . "'";
			$executequery = $conn->execute($query);
			$mlevel = intval($executequery->fields['level']);
			if ($mlevel == "3") {
				$addl = "WHERE l3='1'";
			} elseif ($mlevel == "2") {
				$addl = "WHERE l2='1'";
			} elseif ($mlevel == "1") {
				$addl = "WHERE l1='1'";
			}
		}
	}
	$query = "select ID,pprice from packs $addl order by pprice asc";
	$results = $conn->execute($query);
	$returnthis = $results->getrows();
	return $returnthis;
}

function insert_get_seo_profile($a) {
	$uname = $a['username'];
	echo "user/" . mb_strtolower($uname);
}

function get_seo_profile($uname) {
	return "user/" . mb_strtolower($uname);
}

function insert_get_seo_convo($a) {
	$uname = $a['username'];
	echo "conversations/" . mb_strtolower($uname)."?goToLastUnread=1";
}

function insert_get_gtitle($a) {
	global $conn;
	if ($a["oid"] == '-') {
		return Translations::t("Пополнение баланса");
	}
	$id = intval($a["oid"]);
	$query = "select A.gtitle from posts A, orders B where B.OID='" . mres($id) . "' AND B.PID=A.PID";
	$executequery = $conn->execute($query);
	$gtitle = $executequery->fields['gtitle'];
	return $gtitle;
}

function insert_get_status($a) {
	global $conn;
	$oid = intval($a['oid']);
	$query = "select status from orders where OID='" . mres($oid) . "'";
	$executequery = $conn->execute($query);
	$status = $executequery->fields['status'];
	return $status;
}

function insert_fback($a) {
	global $conn;
	global $actor;
	$oid = intval($a['oid']);
	$userid = $actor->id;
	$query = "select count(*) as total from ratings where OID='" . mres($oid) . "' AND RATER='" . mres($userid) . "'";
	$executequery = $conn->execute($query);
	$total = $executequery->fields['total'] + 0;
	return $total;
}

function insert_fback2($a) {
	global $conn;
	$oid = intval($a['oid']);
	$sid = intval($a['sid']);
	$query = "select count(*) as total from ratings where OID='" . mres($oid) . "' AND RATER='" . mres($sid) . "'";
	$executequery = $conn->execute($query);
	$total = $executequery->fields['total'] + 0;
	return $total;
}

function insert_gig_details($a) {
	global $conn;
	$id = intval($a["pid"]);
	$query = "SELECT A.*, B.seo from posts A, categories B where A.active='1' AND A.category=B.CATID AND A.PID='" . mres($id) . "' limit 1";
	$results = $conn->execute($query);
	$w = $results->getrows();
	return $w;
}

function insert_file_details($a) {
	global $conn;
	$id = intval($a["fid"]);
	$query = "SELECT FID, fname, s from files where FID='" . mres($id) . "' limit 1";
	$results = $conn->execute($query);
	$w = $results->getrows();
	return $w;
}

function insert_gfs($a) {
	global $conn;
	$id = intval($a["fid"]);
	$query = "select s from files where FID='" . mres($id) . "' limit 1";
	$executequery = $conn->execute($query);
	$s = $executequery->fields['s'];
	$file = App::config('uploadeddir') . $s;
	return formatBytes(filesize($file), 1);
}

function formatBytes($bytes, $precision = 2) {
	$units = array('B', 'KB', 'MB', 'GB', 'TB');
	$bytes = max($bytes, 0);
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);
	$bytes /= pow(1024, $pow);
	return round($bytes, $precision) . ' ' . $units[$pow];
}

function escape($data) {
	if (ini_get('magic_quotes_gpc')) {
		$data = stripslashes($data);
	}
	return mres($data);
}

function get_post_categories($postId) {
	global $conn;
	$query = "SELECT C.CATID sub_id,
					 C.name sub_name,
					 C.seo sub_seo,
					 C2.CATID parent_id,
					 C2.name parent_name,
					 C2.seo parent_seo
				FROM posts P
				JOIN categories C ON C.CATID = P.category
			LEFT JOIN categories C2 ON C2.CATID = C.parent
				WHERE P.PID = " . mres($postId) . "";
	$result = $conn->execute($query);
	$data = $result->getrows();
	if (!empty($data)) {
		return $data[0];
	}
	return [];
}

function insert_get_categories($a) {
	global $conn;
	$query = "select * from categories where parent='0' and use_count > 0 order by name asc";
	$results = $conn->execute($query);
	$returnthis = $results->getrows();
	return $returnthis;
}

/**
 * Получение категорий верхнего уровня в зависимости от языка
 *
 * @param array $a Массив параметров
 *     - string lang Язык
 *
 * @return array
 */
function insert_get_categories_by_lang($a) {
	$lang = $a["lang"];
	$sql = "SELECT *
			FROM categories
			WHERE parent = 0 AND lang = :lang
			ORDER BY name ASC";
	$result = \App::pdo()->fetchAll($sql, ["lang" => $lang]);
	return $result;
}

function insert_get_categories2($data) {
	$type = $data["type"];
	$lang = isset($data["lang"]) && in_array($data["lang"], \Translations::getLangArray()) ? $data["lang"] : \Translations::getLang();
	$categories = CategoryManager::getList($lang, $type);

	if ($data["withAdditionalData"]) {
		$sql = "SELECT id, " . VolumeTypeManager::getVolumeNamesExpr() . " FROM volume_type";
		$volumeNames = \App::pdo()->fetchAllAssocPair($sql, 0, 1);
		foreach ($categories as &$category) {
			foreach ($category->cats as &$subCategory) {
				$subCategoryData = CategoryManager::getData($subCategory->id);
				$subCategory->is_package_free_price = (int) $subCategoryData["is_package_free_price"];
				$subCategory->base_volume = $subCategoryData["base_volume"];
				$subCategory->volume_type_id = $subCategoryData["volume_type_id"];
				$subCategory->volume_names = $volumeNames[$subCategory->volume_type_id];
			}
		}
	}

	return $categories;
}

/**
 * Получение меню в шапке сайта
 *
 * @return array[stdClass]      Возвращает массив пунктов меню верхнего уровня
 *     array cats               - подменю
 *     array wideLeftCatArray   - массив id пунктов подменю - порядок вывода в левой колонке на широких экранах
 *     array wideRightCatArray  - массив id пунктов подменю - порядок вывода в правой колонке на широких экранах
 *     array thinCatArray       - массив id пунктов подменю - порядок вывода на узких экранах
 *     ...
 */
function insert_get_header_menu() {
	$menu = CategoryManager::getList(\Translations::getLang(), CategoryManager::TYPE_FILLED_POPULAR);

	$subMenu = [
		// RU
		15 => [
			"wide" => [25, 69, 67, 24, 26, 27, 30, 28, 68, 107, 29, 248, 250, 252, 254, 90],
			"thin" => [25, 69, 67, 24, 26, 27, 30, 28, 68, 107, 29, 248, 250, 252, 254, 90],
		],
		11 => [
			"wide" => [79, 38, 37, 41, 82, 81, 39, 80, 40, 247, 255],
		],
		5 => [
			"wide" => [32, 74, 73, 34, 235, 234, 53, 75, 33, 35],
			"thin" => [53, 32, 74, 73, 235, 234, 34, 75, 33, 35],
		],
		45 => [
			"wide" => [47, 49, 46, 112, 108, 113, 56],
		],
		17 => [
			"wide" => [44, 43, 71, 59, 72],
		],
		83 => [
			"wide" => [55, 84, 52, 88, 64, 63, 114, 87],
		],
		7 => [
			"wide" => [20, 23, 106, "|", 76, 77, 78],
		],
		85 => [
			"wide" => [91, 95, 89, 98, 65, 99, 103],
			"thin" => [91, 95, 89, 65, 98, 103, 99],
		],

		// EN
		132 => [
			"wide" => [142, 186, 184, 141, 143, 144, 147, 145, 185, 224, 146, 245, 246, 249, 251, 253],
			"thin" => [142, 186, 184, 141, 143, 144, 147, 145, 185, 224, 146, 245, 246, 249, 251, 253],
		],
		128 => [
			"wide" => [196, 155, 154, 158, 199, 198, 156, 197, 157, 240, 239, 241],
		],
		122 => [
			"wide" => [149, 191, 190, 151, 235, 170, 192, 150, 152, 236, 244],
			"thin" => [170, 149, 191, 190, 235, 151, 192, 150, 152, 236, 244],
		],
		162 => [
			"wide" => [164, 166, 163, 229, 225, 230, 173],
		],
		134 => [
			"wide" => [161, 160, 188, 176, 189],
		],
		200 => [
			"wide" => [172, 201, 169, 205, 181, 180, 231, 204],
		],
		124 => [
			"wide" => [137, 140, 223, 193, 194, 195, 242],
		],
		202 => [
			"wide" => [208, 212, 206, 211, 215, 182, 207, 214, 216, 220],
			"thin" => [208, 212, 206, 211, 182, 207, 214, 215, 220, 216],
		],
	];

	// foreach ($subMenu as $id => $sub) {
	// 	$idEn = App::pdo()->fetchScalar("SELECT twin_category_id FROM categories WHERE CATID = :catid", ["catid" => $id]);
	// 	print "\t\t$idEn => [\n";
	// 	foreach ($sub as $subName => $subIds) {
	// 		print "\t\t\t\"{$subName}\" => [";
	// 		$subIdsEn = [];
	// 		foreach ($subIds as $subId) {
	// 			if ($subId === "|") {
	// 				$subIdsEn[] = "\"|\"";
	// 			} else {
	// 				$subIdsEn[] = App::pdo()->fetchScalar("SELECT twin_category_id FROM categories WHERE CATID = :catid", ["catid" => $subId]);
	// 			}
	// 		}
	// 		print implode(", ", $subIdsEn);
	// 		print "],\n";
	// 	}
	// 	print "\t\t],\n";
	// }

	foreach ($menu as $categoryId => &$category) {
		if (is_array($subMenu[$categoryId])) {
			$category->wideLeftCatArray = [];
			$category->wideRightCatArray = [];
			$category->thinCatArray = [];

			$items = $subMenu[$categoryId]["wide"];

			// Широкий экран
			$newColumn = array_search("|", $items);
			if ($newColumn === false) {
				$newColumn = round(count($items) / 2);
			}
			foreach ($items as $index => $item) {
				if ($item !== "|" && array_key_exists($item, $category->cats)) {
					if ($index < $newColumn) {
						$category->wideLeftCatArray[] = $item;
					} else {
						$category->wideRightCatArray[] = $item;
					}
				}
			}

			// Узкий экран
			if (array_key_exists("thin", $subMenu[$categoryId])) {
				$items = $subMenu[$categoryId]["thin"];
			}
			foreach ($items as $item) {
				if ($item !== "|" && array_key_exists($item, $category->cats)) {
					$category->thinCatArray[] = $item;
				}
			}
		}
	}

	return $menu;
}

function insert_getParent($data)
{
	$type = $data["type"];
	$categoryId = $data["id"];
	return CategoryManager::getParent($categoryId, $type);
}

//Пагинацию нужно изменить, если будет использоваться в дальнейшем
function insert_paging_block($a)
{
	global $smarty;

	if(!$a['data']['total'] || !$a['data']['paging'])
		return "";

	$total = $a['data']['total'] <= App::config('maximum_results') ? $a['data']['total'] : App::config('maximum_results');

	$perPage = $a['data']['paging']->items_per_page;

	$currentpage = $a['data']['paging']->cur_page;

	if($a['data']['paging']->customPages) {
		$toppage = count($a['data']['paging']->customPages);
	} else {
		$toppage = ceil($total / $perPage);
	}

	if($currentpage > $toppage)
		$currentpage = $toppage;

	$theprevpage = $currentpage - 1;
	$thenextpage = $currentpage + 1;

	$pagelinks = '';
	$results = [];
	$results[0] = 1;
	$results[1] = round(($currentpage - 1) / 2) < 1 ? 0 : round(($currentpage - 1) / 2);
	$results[2] = $currentpage - 2 <= 1 ? 0 : $currentpage - 2;
	$results[3] = $currentpage - 1 <= 1 ? 0 : $currentpage - 1;
	$results[4] = $currentpage;
	$results[5] = $currentpage + 1 >= $toppage ? 0 : $currentpage + 1;
	$results[6] = $currentpage + 2 >= $toppage ? 0 : $currentpage + 2;
	$results[7] = round(($currentpage + 1 + $toppage) / 2) > $toppage ? 0 : round(($currentpage + 1 + $toppage) / 2);
	$results[8] = $toppage;

	if($currentpage > 0)
	{
		$urlPrefix = $a['data']['urlprefix'];

		$adds = isset($a['data']['adds']) ? $a['data']['adds'] : "";
		if(isset($a['data']['save_params']) && $a['data']['save_params'] == true)
		{
			$params = getAll();
			unset($params['page']);
			$adds = '&'.http_build_query($params);
		}

		if($currentpage > 1)
			$pagelinks.="<li class='prev'><a class='prev' href='$urlPrefix&page=$theprevpage$adds'>$theprevpage</a></li>&nbsp;";

		foreach ($results as $idx => $page)
		{
			if ($page == 0)
				continue;

			if ($page == $currentpage)
			{
				if ($idx != 0 && $idx != 8)
					$pagelinks .= "<li><a href='$urlPrefix&page=$page$adds' class='active'>$page</a></li>&nbsp;";
			}
			else
			{
				if (!in_array($idx, [1, 7]))
				{
					$pagelinks.="<li><a href='$urlPrefix&page=$page$adds'>$page</a></li>&nbsp;";
				}
				else if (($idx == 1 && ($currentpage - 1) > 3) || ($idx == 7 && ($toppage - $currentpage > 3)))
				{
					$pagelinks.="<li><a href='$urlPrefix&page=$page$adds'>...</a></li>&nbsp;";
				}
			}
		}
		if ($currentpage < $toppage)
		{
			$smarty->assign('tnp', $thenextpage);
			$pagelinks.="<li class='next'><a class='next' href='$urlPrefix&page=$thenextpage$adds'>$thenextpage</a></li>";
		}
	}

	if ($toppage < 2)
		return "";

	return "<div class='paging'><div class='p1'><ul>" . $pagelinks . "</ul></div></div>";
}

/**
 * Ajax-пагинация. С использованием js-функции, по аналогии с \Paging::ajaxPagination
 * @param $a
 * @return string
 * @throws \PHLAK\Config\Exceptions\InvalidContextException
 */
function insert_ajax_paging_block($a)
{
	global $smarty;

	if(!$a['data']['total'] || !$a['data']['paging'])
		return "";

	$jsFunction = $a["data"]["jsFunction"];
	if (!$jsFunction) {
		return "";
	}

	$total = $a['data']['total'] <= App::config('maximum_results') ? $a['data']['total'] : App::config('maximum_results');

	$perPage = $a['data']['paging']->items_per_page;

	$currentpage = $a['data']['paging']->cur_page;

	if($a['data']['paging']->customPages) {
		$toppage = count($a['data']['paging']->customPages);
	} else {
		$toppage = ceil($total / $perPage);
	}

	if($currentpage > $toppage)
		$currentpage = $toppage;

	$theprevpage = $currentpage - 1;
	$thenextpage = $currentpage + 1;

	$pagelinks = '';
	$results = [];
	$results[0] = 1;
	$results[1] = round(($currentpage - 1) / 2) < 1 ? 0 : round(($currentpage - 1) / 2);
	$results[2] = $currentpage - 2 <= 1 ? 0 : $currentpage - 2;
	$results[3] = $currentpage - 1 <= 1 ? 0 : $currentpage - 1;
	$results[4] = $currentpage;
	$results[5] = $currentpage + 1 >= $toppage ? 0 : $currentpage + 1;
	$results[6] = $currentpage + 2 >= $toppage ? 0 : $currentpage + 2;
	$results[7] = round(($currentpage + 1 + $toppage) / 2) > $toppage ? 0 : round(($currentpage + 1 + $toppage) / 2);
	$results[8] = $toppage;

	if($currentpage > 0)
	{
		if($currentpage > 1)
			$pagelinks.="<li class='prev'><a class='prev' onclick='$jsFunction($theprevpage)' href='javascript:void(0)'>$theprevpage</a></li>&nbsp;";

		foreach ($results as $idx => $page)
		{
			if ($page == 0)
				continue;

			if ($page == $currentpage)
			{
				if ($idx != 0 && $idx != 8)
					$pagelinks .= "<li><a onclick='$jsFunction($page)' href='javascript:void(0)' class='active'>$page</a></li>&nbsp;";
			}
			else
			{
				if (!in_array($idx, [1, 7]))
				{
					$pagelinks.="<li><a onclick='$jsFunction($page)' href='javascript:void(0)'>$page</a></li>&nbsp;";
				}
				else if (($idx == 1 && ($currentpage - 1) > 3) || ($idx == 7 && ($toppage - $currentpage > 3)))
				{
					$pagelinks.="<li><a onclick='$jsFunction($page)' href='javascript:void(0)'>...</a></li>&nbsp;";
				}
			}
		}
		if ($currentpage < $toppage)
		{
			$smarty->assign('tnp', $thenextpage);
			$pagelinks.="<li class='next'><a class='next' onclick='$jsFunction($thenextpage)' href='javascript:void(0)'>$thenextpage</a></li>";
		}
	}

	if ($toppage < 2)
		return "";

	return "<div class='paging'><div class='p1'><ul>" . $pagelinks . "</ul></div></div>";
}

function insert_active_orders($a) {
	global $conn;
	$pid = intval($a["PID"]);
	$query = "select count(*) as total from orders where PID='" . mres($pid) . "' AND status !='0'";
	$executequery = $conn->execute($query);
	$cnt = $executequery->fields['total'];
	return $cnt;
}

function insert_gig_cnt($var) {
	global $conn;
	global $actor;
	$userid = $actor->id;
	$query = "select count(*) as total from posts where USERID='" . mres($userid) . "'";
	$executequery = $conn->execute($query);
	$cnt = $executequery->fields['total'];
	if ($cnt > 0) {
		return "1";
	} else {
		return "0";
	}
}

/**
 * Функция проверки email согласно стандарта RFC 822
 * @param string $email
 * @return bool
 */
function verify_valid_email($email) {
	return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function mailme($sendto, $sendername, $from, $subject, $sendmailbody, $bcc = "") {
	global $SERVER_NAME;
	$subject = nl2br($subject);
	$sendmailbody = nl2br($sendmailbody);
	$sendto = $sendto;
	if ($bcc != "") {
		$headers = "Bcc: " . $bcc . "\n";
	}
	$headers = "MIME-Version: 1.0\n";
	$headers .= "Content-type: text/html; charset=utf-8 \n";
	$headers .= "X-Priority: 3\n";
	$headers .= "X-MSMail-Priority: Normal\n";
	$headers .= "X-Mailer: PHP/" . "MIME-Version: 1.0\n";
	$headers .= "From: " . $from . "\n";
	$headers .= "Content-Type: text/html\n";
	mail("$sendto", "$subject", "$sendmailbody", "$headers", "-f $from");
}

function get_cat($cid) {
	global $conn;
	$query = "SELECT name FROM categories WHERE CATID='" . mres($cid) . "' limit 1";
	$executequery = $conn->execute($query);
	$name = $executequery->fields["name"];
	return $name;
}

function insert_get_cat($var) {
	global $conn;
	$catid = intval($var["CATID"]);
	$query = "SELECT name FROM categories WHERE CATID='" . mres($catid) . "' limit 1";
	$executequery = $conn->execute($query);
	$name = $executequery->fields["name"];
	echo $name;
}

function insert_get_stripped_phrase($a) {
	$stripper = $a["details"];
	$stripper = str_replace("\\n", "<br>", $stripper);
	$stripper = str_replace("\\r", "", $stripper);
	$stripper = str_replace("\\", "", $stripper);
	return $stripper;
}

function insert_get_stripped_phrase2($a) {
	$stripper = $a["details"];
	$stripper = str_replace("\\n", "\n", $stripper);
	$stripper = str_replace("\\r", "\r", $stripper);
	return $stripper;
}

function listdays($selected) {
	$days = "";
	for ($i = 1; $i <= 31; $i++) {
		if ($i == $selected) {
			$days .= "<option value=\"$i\" selected>$i</option>";
		} else {
			$days .= "<option value=\"$i\">$i</option>";
		}
	}
	return $days;
}

function listmonths($selected) {
	$months = "";
	$allmonths = array("", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
	for ($i = 1; $i <= 12; $i++) {
		if ($i == $selected) {
			$months .= "<option value=\"$i\" selected>$allmonths[$i]</option>";
		} else {
			$months .= "<option value=\"$i\">$allmonths[$i]</option>";
		}
	}
	return $months;
}

function listyears($selected) {
	$years = "";
	$thisyear = date("Y");
	for ($i = $thisyear - 100 + 13; $i <= $thisyear - 13; $i++) {
		if ($i == $selected)
			$years .= "<option value=\"$i\" selected>$i</option>";
		else
			$years .= "<option value=\"$i\">$i</option>";
	}
	return $years;
}

function insert_get_member_profilepicture($var) {
	$userId = intval($var["USERID"]);
	$userInfo = UserManager::getUserData($userId);
	if (empty($userInfo['profilepicture'])) {
		return 'noprofilepicture.gif';
	}
	return $userInfo['profilepicture'];
}

function insert_com_count($var) {
	global $conn;
	$pid = intval($var["PID"]);
	$query = "SELECT count(*) as total FROM posts_comments WHERE PID='" . mres($pid) . "'";
	$executequery = $conn->execute($query);
	$total = $executequery->fields["total"];
	return intval($total);
}

function does_post_exist($a) {
	global $conn;
	$query = "SELECT USERID FROM posts WHERE PID='" . mres($a) . "'";
	$executequery = $conn->execute($query);
	if ($executequery->recordcount() > 0)
		return true;
	else
		return false;
}

function session_verification() {
	global $actor;
	if ($actor) {
		return true;
	} else {
		return false;
	}
}

function insert_get_username_from_userid($var) {
	global $conn;
	$userid = intval($var["USERID"]);
	$query = "SELECT username FROM members WHERE USERID='" . mres($userid) . "'";
	$executequery = $conn->execute($query);
	$getusername = $executequery->fields["username"];
	return "$getusername";
}

function does_profile_exist($a) {
	global $conn;
	$query = "SELECT username FROM members WHERE USERID='" . mres($a) . "'";
	$executequery = $conn->execute($query);
	if ($executequery->recordcount() > 0)
		return true;
	else
		return false;
}

function insert_get_member_comments_count($var) {
	global $conn;
	$userid = intval($var["USERID"]);
	$query = "SELECT count(*) as total FROM posts_comments WHERE USERID='" . mres($userid) . "'";
	$executequery = $conn->execute($query);
	$results = $executequery->fields["total"];
	echo "$results";
}

function insert_get_posts_count($var) {
	global $conn;
	$userid = intval($var["USERID"]);
	$query = "SELECT count(*) as total FROM posts WHERE USERID='" . mres($userid) . "'";
	$executequery = $conn->execute($query);
	$results = $executequery->fields["total"];
	echo "$results";
}

function insert_get_static($var) {
	global $conn;
	$sel = $var["sel"];
	if (!in_array($sel, array("title", "value")))
		exit;
	$id = intval($var["ID"]);
	$query = "SELECT " . mres($sel) . " FROM static WHERE ID='" . mres($id) . "'";
	$executequery = $conn->execute($query);
	$returnme = $executequery->fields[$sel];
	$returnme = strip_mq_gpc(Translations::t($returnme));
	echo "$returnme";
}

function insert_strip_special($a) {
	$text = $a['text'];
	$text = str_replace(",", "", $text);
	$text = str_replace(".", "", $text);
	$text = nl2br($text);
	$text = str_replace("\n", "", $text);
	$text = str_replace("\r", "", $text);
	$text = str_replace("<br />", "", $text);
	$text = str_replace(" ", "", $text);
	$clean = preg_replace("/^[^a-z0-9]?(.*?)[^a-z0-9]?$/i", "$1", $text);
	return $clean;
}

function firstDayOfMonth2($uts = null) {
	$today = is_null($uts) ? getDate() : getDate($uts);
	$first_day = getdate(mktime(0, 0, 0, $today['mon'], 1, $today['year']));
	return $first_day[0];
}

function firstDayOfYear2($uts = null) {
	$today = is_null($uts) ? getDate() : getDate($uts);
	$first_day = getdate(mktime(0, 0, 0, 1, 1, $today['year']));
	return $first_day[0];
}

/**
 * Очистка текста введенного пользователем
 *
 * @param string $text Текст
 * @param array $saveChars Текст, который нужно сохранить, например "<=", т.к. он обрезается strip_tags
 * @return mixed|string
 */
function cleanit($text, $saveChars = []) {
	$text = str_replace("<-", '', $text); //#2440

	foreach ($saveChars as $k => $char) {
		$mask = "#cleanIt{$k}#";
		$text = str_replace($char, $mask, $text);
	}
	$result = htmlentities(strip_tags(stripslashes($text)), ENT_COMPAT, "UTF-8");

	foreach ($saveChars as $k => $char) {
		$mask = "#cleanIt{$k}#";
		$result = str_replace($mask, $char, $result);
	}

	return $result;
}

function cleanitArray($array, $saveChars = []){
	foreach ($array as $key => $item) {
		if (is_array($item)) {
			$array[$key] = cleanitArray($item, $saveChars);
		}else {
			$array[$key] = cleanit($item, $saveChars);
		}
	}
	return $array;
}

function do_resize_image($file, $width = 0, $height = 0, $proportional = false, $output = 'file') {
	if ($height <= 0 && $width <= 0) {
		return false;
	}

	$info = getimagesize($file);
	$image = '';

	list($width_old, $height_old) = $info;

	switch ($info[2]) {
		case IMAGETYPE_GIF:
			$image = imagecreatefromgif($file);
			break;
		case IMAGETYPE_JPEG:
			$image = imagecreatefromjpeg($file);
			break;
		case IMAGETYPE_PNG:
			$image = imagecreatefrompng($file);
			break;
		default:
			return false;
	}
	if ($proportional) {
		list($image) = fit($image, $width, $height, $width_old, $height_old);
	} else {
		$width = ($width <= 0) ? $width_old : $width;
		$height = ($height <= 0) ? $height_old : $height;
	}

	$image_resized = crop($image, $width, $height, $width_old, $height_old);

	switch (strtolower($output)) {
		case 'browser':
			$mime = image_type_to_mime_type($info[2]);
			header("Content-type: $mime");
			$output = null;
			break;
		case 'file':
			$output = $file;
			break;
		case 'return':
			return $image_resized;
			break;
		default:
			break;
	}

	if (file_exists($output)) {
		@unlink($output);
	}

	imagepng($image_resized, $output);

	return true;
}

function fit($img, $width, $height, $real_w = '', $real_h = '') {
	if (!$img)
		return false;

	if (!$real_w)
		$real_w = getWidth($img);
	if (!$real_h)
		$real_h = getHeight($img);
	$width = min($width, $real_w);
	$height = min($height, $real_h);

	$ratio = min($width / $real_w, $height / $real_h);
	$width = max(1, $real_w * $ratio);
	$height = max(1, $real_h * $ratio);

	resize($img, $width, $height);

	return [$img, $width, $height];
}

function crop($img, $width, $height, $real_w = '', $real_h = '') {
	if (!$img)
		return false;

	if (!$real_w)
		$real_w = getWidth($img);
	if (!$real_h)
		$real_h = getHeight($img);
	$width = min($width, $real_w);
	$height = min($height, $real_h);

	$ratio = min($real_w / $width, $real_h / $height);
	$imgWidth = max(1, $width * $ratio);
	$imgHeight = max(1, $height * $ratio);

	return resize($img, $width, $height, $imgWidth, $imgHeight);
}

function resize($img, $width, $height, $imgWidth = null, $imgHeight = null) {
	if (!$img)
		return false;

	$imgWidth = $imgWidth ? $imgWidth : getWidth($img);
	$imgHeight = $imgHeight ? $imgHeight : getHeight($img);

	$width = round($width);
	$height = round($height);
	$imgWidth = round($imgWidth);
	$imgHeight = round($imgHeight);

	$new_image = imageCreateTrueColor($width, $height);

	imageAlphaBlending($new_image, false);
	imageSaveAlpha($new_image, true);

	imageCopyResampled($new_image, $img, 0, 0, 0, 0, $width, $height, $imgWidth, $imgHeight);

	return $new_image;
}

function getWidth($img) {
	return imagesx($img);
}

function getHeight($img) {
	return imagesy($img);
}

function translit($str) {
	$alphavit2 = [
		"йо" => "yo",
		"ые" => "ie",
		"ый" => "iy",
		/* -- */
		"ЙО" => "YO",
		"ЫЕ" => "IE",
		"ЫЙ" => "IY",
	];
	$alphavit1 = array(
		/* -- */
		"а" => "a",
		"б" => "b",
		"в" => "v",
		"г" => "g",
		"д" => "d",
		"е" => "e",
		"ё" => "yo",
		"ж" => "zh",
		"з" => "z",
		"и" => "i",
		"й" => "y",
		"к" => "k",
		"л" => "l",
		"м" => "m",
		"н" => "n",
		"о" => "o",
		"п" => "p",
		"р" => "r",
		"с" => "s",
		"т" => "t",
		"у" => "u",
		"ф" => "f",
		"х" => "kh",
		"ц" => "ts",
		"ч" => "ch",
		"ш" => "sh",
		"щ" => "shch",
		"ъ" => "",
		"ы" => "y",
		"ь" => "",
		"э" => "e",
		"ю" => "yu",
		"я" => "ya",
		/* -- */
		"А" => "A",
		"Б" => "B",
		"В" => "V",
		"Г" => "G",
		"Д" => "D",
		"Е" => "E",
		"Ё" => "YO",
		"Ж" => "ZH",
		"З" => "Z",
		"И" => "I",
		"Й" => "Y",
		"К" => "K",
		"Л" => "L",
		"М" => "M",
		"Н" => "N",
		"О" => "O",
		"П" => "P",
		"Р" => "R",
		"С" => "S",
		"Т" => "T",
		"У" => "U",
		"Ф" => "F",
		"Х" => "KH",
		"Ц" => "TS",
		"Ч" => "CH",
		"Ш" => "SH",
		"Щ" => "SHCH",
		"Ъ" => "",
		"Ы" => "Y",
		"Ь" => "",
		"Э" => "E",
		"Ю" => "YU",
		"Я" => "YA",
	);
	$str = strtr($str, $alphavit2);
	$str = strtr($str, $alphavit1);
	return $str;
}

function seo_clean_titles($title) {
	$title = preg_replace("/([^0-9A-Za-zА-Яа-яЁё]+)/u", '-', html_entity_decode($title));
	$title = trim($title);
	$title = trim($title, '-');

	return mb_strtolower(translit($title));
}

function insert_get_time_to_days_ago($a) {
	return $a['time'];
}

function count_days($a, $b) {
	$gd_a = getdate($a);
	$gd_b = getdate($b);
	$a_new = mktime(12, 0, 0, $gd_a['mon'], $gd_a['mday'], $gd_a['year']);
	$b_new = mktime(12, 0, 0, $gd_b['mon'], $gd_b['mday'], $gd_b['year']);
	return round(abs($a_new - $b_new) / 86400);
}

function insert_declension($a) {
	$count = $a['count'];
	$forms = [
		Translations::t($a['form1']),
		Translations::t($a['form2']),
		Translations::t($a['form3']),
	];
	return declension($count, $forms);
}

function declension($count, $forms, $lang = null) {
	if($lang == null) 
		$lang = Translations::getLang();
	
	if($lang == Translations::DEFAULT_LANG) {
		$count = abs($count) % 100;
		$lcount = $count % 10;
		if ($count >= 11 && $count <= 19) {
			return Translations::t($forms[2]);
		}
		if ($lcount >= 2 && $lcount <= 4) {
			return Translations::t($forms[1]);
		}
		if ($lcount == 1) {
			return Translations::t($forms[0]);
		}
		return Translations::t($forms[2]);
	} else {
		$count = abs($count);
		if($count == 1) {
			return Translations::t($forms[0]);
		}
		return Translations::t($forms[2]);
	}
}

function insert_countdown($a) {
	$days = intval($a['days']);
	$time = intval($a['time']);
	$ctime = $days * 24 * 60 * 60;
	$f_timestamp = $time + $ctime;
	$c_timestamp = time();
	if ($f_timestamp > $c_timestamp) {
		$days = floor(($f_timestamp - $c_timestamp) / (60 * 60 * 24));
		$f_timestamp = $f_timestamp - ($days * 60 * 60 * 24);
		$hours = floor(($f_timestamp - $c_timestamp) / (60 * 60));
		$f_timestamp = $f_timestamp - ($hours * 60 * 60);
		$minutes = floor(($f_timestamp - $c_timestamp) / (60));
		$f_timestamp = $f_timestamp - ($minutes * 60);
		$seconds = $f_timestamp - $c_timestamp;
		$go = "(<b>" . Translations::t("Осталось:") . " ";
		if ($days > 0) {
			$go .= $days . " " . declension($days, [Translations::t("день"), Translations::t("дня"), Translations::t("дней")]) . " ";
		}
		if ($hours > 0) {
			$go .= $hours . " " . declension($hours, [Translations::t("час"), Translations::t("часа"), Translations::t("часов")]) . " ";
		}
		if ($minutes > 0) {
			$go .= $minutes . " " . declension($minutes, [Translations::t("минута"), Translations::t("минуты"), Translations::t("минут")]);
		}
		$go .= "</b>)";
		return $go;
	}
}
function insert_countup_short($a)
{
	$f_timestamp = strtotime($a['time']);
	$now_timestamp = time();
	$days = floor(($now_timestamp - $f_timestamp) / (60 * 60 * 24));
	$now_timestamp = $now_timestamp - ($days * 60 * 60 * 24);
	$hours = floor(($now_timestamp - $f_timestamp) / (60 * 60));
	$now_timestamp = $now_timestamp - ($hours * 60 * 60);
	$minutes = floor(($now_timestamp - $f_timestamp) / (60));
	$now_timestamp = $now_timestamp - ($minutes * 60);
	$seconds = $now_timestamp - $f_timestamp;
	$go = "";
	if ($days > 0)
	{
		$go .= $days . " " . declension($days, [Translations::t("день"), Translations::t("дня"), Translations::t("дней")]) . " ";
	}
	elseif ($hours > 0)
	{
		$go .= $hours . " " . declension($hours, [Translations::t("час"), Translations::t("часа"), Translations::t("часов")]) . " ";
	}
	elseif ($minutes > 0)
	{
		$go .= $minutes . " " . declension($minutes, [Translations::t("минута"), Translations::t("минуты"), Translations::t("минут")]) . " ";
	}
	else if ($seconds > 0)
	{
		$go .= $seconds . " " . declension($seconds, [Translations::t("секунда"), Translations::t("секунды"), Translations::t("секунд")]) . " ";
	}

	return $go;
}

/**
 * Возврат даты в полной форме X день X часа
 * если type = deadline, то пришло время. если type = duration, то пришел промежуток времени
 * @param array $a [ 'time' => (int) , 'type' => 'duration' | 'deadline']
 * @return string
 */
function insert_countdown_short($a) {
	$f_timestamp = intval($a["time"]);
	$c_timestamp = $a["type"] == "deadline" ? time() : 0;

	if ($f_timestamp > $c_timestamp) {
		$days = floor(($f_timestamp - $c_timestamp) / (60 * 60 * 24));
		$f_timestamp = $f_timestamp - ($days * 60 * 60 * 24);
		$hours = floor(($f_timestamp - $c_timestamp) / (60 * 60));
		$f_timestamp = $f_timestamp - ($hours * 60 * 60);
		$minutes = floor(($f_timestamp - $c_timestamp) / (60));
		$f_timestamp = $f_timestamp - ($minutes * 60);
		$seconds = $f_timestamp - $c_timestamp;
		$go = "";

		if ($days > 0) {
			$go .= $days . " " . declension($days, [Translations::t("день"), Translations::t("дня"), Translations::t("дней")]) . " ";

			if ($hours > 0 && !$a["only_days"]) {
				$go .= $hours . " " . declension($hours, [Translations::t("час"), Translations::t("часа"), Translations::t("часов")]) . " ";
			}
		} elseif ($a["only_days"]) {
			$days = 1;
			$go .= $days . " " . declension($days, [Translations::t("день"), Translations::t("дня"), Translations::t("дней")]) . " ";
		} elseif ($hours > 0) {
			$go .= $hours . " " . declension($hours, [Translations::t("час"), Translations::t("часа"), Translations::t("часов")]) . " ";

			if ($minutes > 0) {
				$go .= $minutes . " " . declension($minutes, [Translations::t("минута"), Translations::t("минуты"), Translations::t("минут")]) . " ";
			}
		} elseif ($minutes > 0) {
			$go .= $minutes . " " . declension($minutes, [Translations::t("минута"), Translations::t("минуты"), Translations::t("минут")]) . " ";
		} elseif ($seconds > 0) {
			$go .= $seconds . " " . declension($seconds, [Translations::t("секунда"), Translations::t("секунды"), Translations::t("секунд")]) . " ";
		}

		return trim($go);
	}

	return "";
}

/**
 * Возврат даты в короткой форме X дн X ч
 * если type = deadline, то пришло время. если type = duration, то пришел промежуток времени
 * @param array $a [ 'time' => (int) , 'type' => 'duration' | 'deadline']
 * @return string
 */
function insert_countdown_short_cut($a) {
	$f_timestamp = intval($a["time"]);
	$c_timestamp = $a["type"] == "deadline" ? time() : 0;

	if ($f_timestamp > $c_timestamp) {
		$days = floor(($f_timestamp - $c_timestamp) / (60 * 60 * 24));
		$f_timestamp = $f_timestamp - ($days * 60 * 60 * 24);
		$hours = floor(($f_timestamp - $c_timestamp) / (60 * 60));
		$f_timestamp = $f_timestamp - ($hours * 60 * 60);
		$minutes = floor(($f_timestamp - $c_timestamp) / (60));
		$f_timestamp = $f_timestamp - ($minutes * 60);
		$seconds = $f_timestamp - $c_timestamp;
		$go = "";
		if ($days > 0) {
			$go .= $days . " " . Translations::t("дн") . " ";
			if ($hours > 0 && !$a["only_days"]) {
				$go .= $hours . " " . Translations::t("ч") . " ";
			}
		} elseif ($a["only_days"]) {
			$days = 1;
			$go .= $days . " " . Translations::t("дн") . " ";
		} elseif ($hours > 0) {
			$go .= $hours . " " . Translations::t("ч") . " ";
			if ($minutes > 0) {
				$go .= $minutes . " " . Translations::t("м") . " ";
			}
		} elseif ($minutes > 0) {
			$go .= $minutes . " " . Translations::t("м") . " ";
		} elseif ($seconds > 0) {
			$go .= $seconds . " " . Translations::t("c") . " ";
		}

		return trim($go);
	}

	return "";
}

function insert_dateformat($a) {
	$date = $a['date'];
	$format = $a['format'];
	return date($format, $date);
}

function insert_countup($a) {
	$days = intval($a['days']);
	$time = intval($a['time']);
	$ctime = $days * 24 * 60 * 60;
	$c_timestamp = $time + $ctime;
	$f_timestamp = time();
	if ($f_timestamp > $c_timestamp) {
		$days = floor(($f_timestamp - $c_timestamp) / (60 * 60 * 24));
		$f_timestamp = $f_timestamp - ($days * 60 * 60 * 24);
		$hours = floor(($f_timestamp - $c_timestamp) / (60 * 60));
		$f_timestamp = $f_timestamp - ($hours * 60 * 60);
		$minutes = floor(($f_timestamp - $c_timestamp) / (60));
		$f_timestamp = $f_timestamp - ($minutes * 60);
		$seconds = $f_timestamp - $c_timestamp;
		$go = "";
		if ($days > 0) {
			$go .= $days . " " . declension($days, [Translations::t("день"), Translations::t("дня"), Translations::t("дней")]) . " ";
		}
		if ($hours > 0) {
			$go .= $hours . " " . declension($hours, [Translations::t("час"), Translations::t("часа"), Translations::t("часов")]) . " ";
		}
		if ($minutes > 0) {
			$go .= $minutes . " " . declension($minutes, [Translations::t("минуту"), Translations::t("минуты"), Translations::t("минут")]);
		}
		$go .= "";
		return $go;
	}
}

function insert_countdown_twoParams($a) {
	$f_timestamp = intval($a['time']);
	$c_timestamp = time();
	if ($f_timestamp > $c_timestamp) {
		$days = floor(($f_timestamp - $c_timestamp) / (60 * 60 * 24));
		$f_timestamp = $f_timestamp - ($days * 60 * 60 * 24);
		$hours = floor(($f_timestamp - $c_timestamp) / (60 * 60));
		$f_timestamp = $f_timestamp - ($hours * 60 * 60);
		$minutes = floor(($f_timestamp - $c_timestamp) / (60));
		$f_timestamp = $f_timestamp - ($minutes * 60);
		$seconds = $f_timestamp - $c_timestamp;
		$go = "";
		if ($days > 0) {
			$go .= $days . " " . declension($days, ["дня", "дней", "дней"]) . " ";
			$go .= $hours . " " . declension($hours, ["часа", "часов", "часов"]) . " ";
			return $go;
		}
		if ($hours >= 3 && $hours < 24) {
			$go .= $hours . " " . declension($hours, ["часа", "часов", "часов"]) . " ";
		}
		if ($hours >= 1 && $hours < 3) {
			$go .= $hours . " " . declension($hours, ["часа", "часов", "часов"]) . " ";
			$go .= $minutes . " " . declension($minutes, ["минуты", "минут", "минут"]);
			return $go;
		}
		if ($hours < 1) {
			$go .= $minutes . " " . declension($minutes, ["минуты", "минут", "минут"]);
		}
		return $go;
	}
}

function insert_countdown_improved($a) {
	$a['time'] = Helper::getAnswerTimestamp($a['time'], $a['isAvailableAtWeekends']);
	return insert_countdown_twoParams($a);
}

function insert_late($a) {
	$days = intval($a['days']);
	$time = intval($a['time']);
	$ctime = $days * 24 * 60 * 60;
	$utime = $time + $ctime;
	$now = time();
	if ($now > $utime) {
		return "1";
	} else {
		return "0";
	}
}

function insert_get_days_withdraw($a) {
	$dbw = intval(App::config('days_before_withdraw'));
	$n = time();
	$wtime = $dbw * 24 * 60 * 60;
	$t = intval($a['t']) + $wtime;
	if ($t > $n) {
		return count_days($t, $n);
	} else {
		return "0";
	}
}

function get_days_withdraw($a) {
	$dbw = intval(App::config('days_before_withdraw'));
	$n = time();
	$wtime = $dbw * 24 * 60 * 60;
	$t = intval($a) + $wtime;
	if ($t > $n) {
		return count_days($t, $n);
	} else {
		return "0";
	}
}

function insert_get_yprice($a) {
	$p = number_format($a['p'], 2, '.', '');
	$c = number_format($a['c'], 2, '.', '');

	if ($c == "0") {
		$c = number_format(App::config('commission'), 2, '.', '');
	}
	if ($p > $c) {
		$pc = $p - $c;
		$formatted = sprintf("%01.2f", $pc);
		return $formatted;
	} else {
		return "0.00";
	}
}

function get_yprice($a) {
	$c = number_format(App::config('commission'), 2, '.', '');
	$p = number_format($a, 2, '.', '');

	if ($p > $c) {
		$pc = $p - $c;
		$formatted = sprintf("%01.2f", $pc);
		return $formatted;
	} else {
		return "0.00";
	}
}

function get_yprice2($a, $b) {
	$c = number_format($b, 2, '.', '');
	$p = number_format($a, 2, '.', '');
	if ($p > $c) {
		$pc = $p - $c;
		$formatted = sprintf("%01.2f", $pc);
		return $formatted;
	} else {
		return "0.00";
	}
}

function insert_get_short_url($a) {
	global $conn;
	$SPID = intval($a['PID']);
	$stitle = stripslashes($a['title']);
	$sshort = stripslashes($a['short']);
	$SSEO = stripslashes($a['seo']);
	$SSEO = str_replace("", "+", $SSEO);
	return App::config('baseurl') . "/" . $SSEO . "/" . $SPID . "/" . $stitle;
}

function insert_get_redirect($a) {
	$PID = intval($a['PID']);
	$seo = $a['seo'];
	$gtitle = $a['gtitle'];
	$rme = stripslashes($seo) . "/" . $PID . "/" . stripslashes($gtitle);
	return base64_encode($rme);
}

function get_city_or_country_translated_name(array $data):string
{
	$field = "name";
	if(!\Translations::isDefaultLang()) {
		$field = "name_en";
	}
	return $data[$field];
}

function insert_city_id_to_name($a)
{
	$cityId = (int)$a["id"];
	if($cityId)
	{
		$data = false;
		if (App::config("redis.enable")) {
			$data = RedisManager::getInstance()->get(\Enum\Redis\RedisAliases::CITY_ID_TO_NAME);
		}
		if(!$data || !array_key_exists($cityId, $data))
		{
			$data = \App::pdo()->fetchAllNameByColumn("SELECT id, name, name_en FROM city");
			if ($data && App::config("redis.enable")) {
				RedisManager::getInstance()->set(\Enum\Redis\RedisAliases::CITY_ID_TO_NAME, $data, Helper::ONE_DAY);
			}
		}
		return array_key_exists($cityId, $data) ? get_city_or_country_translated_name($data[$cityId]) : "";
	}

	$countryId = (int)$a["countryId"];
	if($countryId)
	{
		$data = false;
		if (App::config("redis.enable")) {
			$data = RedisManager::getInstance()->get(\Enum\Redis\RedisAliases::CITY_ID_TO_COUNTRY);
		}
		if(!$data || !array_key_exists($countryId, $data))
		{
			$data = \App::pdo()->fetchAllNameByColumn("SELECT id, name, name_en FROM country");
			if ($data && App::config("redis.enable")) {
				RedisManager::getInstance()->set(\Enum\Redis\RedisAliases::CITY_ID_TO_COUNTRY, $data, Helper::ONE_DAY);
			}
		}
		return array_key_exists($countryId, $data) ? get_city_or_country_translated_name($data[$countryId]) : "";
	}

	return "";
}

function insert_get_kwork_count($a) {
	global $conn;
	$sql = "SELECT count(*) total, c.parent FROM posts p 
			JOIN categories c ON c.CATID = p.category 
			WHERE " . StatusManager::kworkListEnable('p') . " AND p.category = '" . mres($a['catid']) . "'";
	$data = $conn->Execute($sql);
	if ($data->fields['parent'] != 0) {
		return $data->fields['total'];
	} else {
		$sql = "SELECT CATID FROM categories WHERE parent = '" . mres($a['catid']) . "'";

		$data2 = $conn->Execute($sql);
		$data2 = $data2->getrows();
		$subs = [];
		foreach ($data2 as $row) {
			$subs[] = mres($row['CATID']);
		}
		if (!empty($subs)) {
			$sql = "SELECT count(*) total, c.parent FROM posts p 
				JOIN categories c ON c.CATID = p.category 
				WHERE " . StatusManager::kworkListEnable('p') . " AND p.category IN (" . implode(",", $subs) . ")";
			$data3 = $conn->Execute($sql);
			$subCounter = $data3->fields['total'];
		} else {
			$subCounter = 0;
		}
		return ($data->fields['total'] + $subCounter);
	}
}

function insert_conversation_time($a) {
	$time = Timezone::setTimezoneInt($a['time']);
	return date("H:i", $time);
}

function get_percent($userId)
{
	$userId = (int)$userId;

	global $conn;

	$data = false;
	if (App::config("redis.enable")) {
		$data = RedisManager::getInstance()->get(\Enum\Redis\RedisAliases::GET_PERCENT);
	}
	if(!$data || !array_key_exists($userId, $data))
	{
		$data = $conn->getColumn("SELECT m.USERID, case when count(r.RID) > 0 then floor(sum(r.good) / count(r.RID) * 100) else 0 end as 'rating' FROM members m left join ratings r on r.USERID = m.USERID GROUP BY m.USERID");
		if ($data && App::config("redis.enable")) {
			RedisManager::getInstance()->set(\Enum\Redis\RedisAliases::GET_PERCENT, $data, Helper::ONE_MINUTE * 10);
		}
	}

	return array_key_exists($userId, $data) ? $data[$userId] : 0;
}

function insert_user_level($a) {
	$userId = intval($a["userid"]);
	$userInfo = UserManager::getUserData($userId);
	return $userInfo['level'];
}

function save_uploaded_image($file, $output) {
	if (strpos(strtolower(trim($file)), 'phar://') === 0) {
		return false;
	}

	list($width, $height, $image_type) = getimagesize($file);
	if (!in_array($image_type, array(IMAGETYPE_GIF, IMAGETYPE_PNG, IMAGETYPE_JPEG))) {
		return false;
	}

	return do_resize_image($file, $width, $height, true, $output);
}

function insert_get_extras($a) {
	$aPid = intval($a["PID"]);
	$results = ExtrasManager::getExtrasForKwork($aPid);
	$orderExtras = array();
	if (isset($a["OID"])) {
		$sql = 'SELECT * 
			FROM extras 
			WHERE 
				OID = :oid AND 
				is_volume = 1';
		$orderExtras = App::pdo()->fetchAll($sql, ['oid' => intval($a["OID"])]);
	}
	foreach ($orderExtras as $value) {
		array_push($results, new \Model\ExtrasModel($value));
	}

	usort($results, function($extra1, $extra2) {
		if ($extra1->getIsVolume() > $extra2->getIsVolume()) {
			return -1;
		} elseif ($extra1->getIsVolume() < $extra2->getIsVolume()) {
			return 1;
		} elseif ($extra1->getId() < $extra2->getId()) {
			return -1;
		} else {
			return 1;
		}
	});
	return $results;
}

function insert_getTrackOrder($a){
	global $conn, $actor;
	$messageId = intval($a['message']['MID']);
	$messageType= $a['message']['type'];
	$order = $conn->getEntity('SELECT io.order_id, io.status, o.USERID FROM inbox_order io join orders o on o.OID=io.order_id WHERE inbox_id=' . mres($messageId) . ' limit 1');
	$forUser = $actor->id == $order->USERID ? UserManager::TYPE_PAYER : UserManager::TYPE_WORKER;
	$res = KworkManager::getTrackOrder($order->order_id, $forUser, false, $order->status);
	$res['status'] = $order->status;
	return $res;
}
function insert_get_extra($a) {
	global $conn;
	$aEid = intval($a["EID"]);
	$query = "select etitle from extras where EID='" . mres($aEid) . "'";
	$executequery = $conn->execute($query);
	return $executequery->fields['etitle'];
}

function insert_youtube_key($a) {
	$youtube_url = $a['yt'];
	$youtube_url = str_replace("https://", "http://", $youtube_url);
	$pos = strpos($youtube_url, "http://www.youtube.com/watch?v=");
	if ($pos === false) {
		$posb = strpos($youtube_url, "http://www.youtu.be/");
		if ($posb === false) {
			$posc = strpos($youtube_url, "http://youtu.be/");
			if ($posc === false) {
				$ypro = "0";
			} else {
				$ypro = "3";
			}
		} else {
			$ypro = "2";
		}
	} else {
		$ypro = "1";
	}
	if ($ypro == "1") {
		$position = strpos($youtube_url, 'watch?v=') + 8;
		$remove_length = mb_strlen($youtube_url) - $position;
		$video_id = mb_substr($youtube_url, -$remove_length, 11);
		return $video_id;
	} elseif ($ypro == "2" || $ypro == "3") {
		$position = strpos($youtube_url, 'youtu.be/') + 9;
		$remove_length = mb_strlen($youtube_url) - $position;
		$video_id = mb_substr($youtube_url, -$remove_length, 11);
		return $video_id;
	}
}

function isKworkOwner($pid, $uid) {
	global $conn;
	$res = $conn->execute('SELECT 1 FROM posts WHERE PID = ' . mres($pid) . ' AND USERID = ' . mres($uid) . ' LIMIT 1');

	return isset($res->getrows()[0][1]);
}

function insert_time_ago($a) {
	$days = intval($a['days']);
	$time = intval($a['time']);
	$ctime = $days * 24 * 60 * 60;
	$c_timestamp = $time + $ctime;
	$f_timestamp = time();
	if ($f_timestamp > $c_timestamp) {
		$years = floor(($f_timestamp - $c_timestamp) / (60 * 60 * 24 * 30 * 12));
		$f_timestamp = $f_timestamp - ($years * 60 * 60 * 24 * 30 * 12);
		$months = floor(($f_timestamp - $c_timestamp) / (60 * 60 * 24 * 30));
		$f_timestamp = $f_timestamp - ($months * 60 * 60 * 24 * 30);
		$days = floor(($f_timestamp - $c_timestamp) / (60 * 60 * 24));
		$f_timestamp = $f_timestamp - ($days * 60 * 60 * 24);
		$hours = floor(($f_timestamp - $c_timestamp) / (60 * 60));
		$f_timestamp = $f_timestamp - ($hours * 60 * 60);
		$minutes = floor(($f_timestamp - $c_timestamp) / (60));
		$f_timestamp = $f_timestamp - ($minutes * 60);
		$seconds = $f_timestamp - $c_timestamp;
		$go = "";
		if ($years > 0)
		{
			$go .= $years . " " . declension($years, [Translations::t("год"), Translations::t("года"), Translations::t("лет")]) . " ";
		}
		elseif ($months > 0)
		{
			$go .= $months . " " . declension($months, [Translations::t("месяц"), Translations::t("месяца"), Translations::t("месяцев")]) . " ";
		}
		elseif ($days > 0) {
			$go .= $days . " " . declension($days, [Translations::t("день"), Translations::t("дня"), Translations::t("дней")]) . " ";
		}
		elseif ($hours > 0) {
			$go .= $hours . " " . declension($hours, [Translations::t("час"), Translations::t("часа"), Translations::t("часов")]) . " ";
		}
		elseif ($minutes > 0) {
			$go .= $minutes . " " . declension($minutes, [Translations::t("минуту"), Translations::t("минуты"), Translations::t("минут")]);
		}
		elseif ($seconds > 0) {
			$go .= $seconds . " " . declension($seconds, [Translations::t("секунду"), Translations::t("секунды"), Translations::t("секунд")]);
		}
		$go .= "";
		return $go;
	}
}

/**
 * Для вывода списка цен за опции заказа, используется при выборе опций (например в new.php)
 */
function getListExtraPrices($value = false) {
	$array = [100, 200, 300, 400, 500, 1000, 1500, 2000, 2500, 3000, 6000];
	if (!$value) {
		return $array;
	}
	return array_search($value, $array);
}

function getListExtraTimes() {
	return [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
}

function getListPackagePrices(){
	return [500, 1000, 1500, 2000];
}

/**
 * Обрезает строку для meta description
 *
 * @param string  $str    Текст meta description.
 * @param integer $maxLen Допустимая длина.
 *
 * @return string
 */
function cut_desc_for_meta(string $str, int $maxLen = 400) {
	if (mb_strlen($str) > $maxLen) {
		$array = explode(' ', $str);
		$str = '';
		$sum = 0;
		$count = count($array) - 1;
		foreach ($array as $key => $word) {
			$is_last = $key != $count;
			$sum += mb_strlen($word) + (!$is_last ? 1 : 0); //+1 на пробел.Проверка нужна, чтобы у последнего слова не учитывать пробел
			if ($sum <= $maxLen) {
				$str .= $word . ' ';
			}
		}
	}

	return trim($str);
}

function getOrdersCount($userId, $userType) {
	global $conn;

	$userId = mres($userId);

	if ($userType == "payer")
		return $conn->getCell("SELECT count(*) FROM orders WHERE status in (1,8) AND USERID = '" . mres($userId) . "'");

	if ($userType == "worker")
		return $conn->getCell("SELECT count(*) FROM orders A JOIN posts B ON B.PID = A.PID WHERE B.USERID = '" . mres($userId) . "' AND A.status in (1,4)");

	return 0;
}

function insert_is_online($a) {
	if(!App::config("redis.enable"))
		return false;

	$userId = mres($a["userid"]);

	return UserManager::checkUserOnlineStatus((int)$userId);
}

function insert_last_online_ago($a)  {
	return time() - intval($a['time']);
}

/**
 * Возвращает текстовое представление времени, которее прошло с момента береданного в time
 * @param $a
 * @return bool|mixed|string
 */
function insert_last_online_ago_more($a)  {
	$secAgo = time() - intval($a['time']);
	if($secAgo > Helper::ONE_MONTH) {
		$result = Translations::t("более месяца");
	} else {
		$result = Helper::timeLeft($secAgo, $a['textBefore'], $a['textAfter']);
	}

	return $result;
}

function insert_is_online_from_array($a)
{
	if(!App::config("redis.enable"))
		return false;

	if(!$a['posts'] || !is_array($a['posts']))
		return [];

	$userIds = [];
	foreach($a['posts'] as $post) {
		$userIds[] = $post['USERID'];
	}
	$userIds = array_unique($userIds);

	$is_online = [];
	if ($userIds) {
		// Все онлайн юзеры на данный момент
		$onlineUserIds = RedisManager::getInstance()->zrangebyscore(Enum\Redis\RedisAliases::USERS_ONLINE, time(), time() + UserManager::LIVE_TIME);

		// Пересечем запрошенных юзеров со всеми, кто онлайн и заполним искомый массив is_online
		$is_online = array_fill_keys(array_intersect($userIds, $onlineUserIds), 1);
	}

	return $is_online;
}

function insert_isOnlineUsers($a)
{
	if(!App::config("redis.enable"))
		return false;

	if (!$a['userIds']) {
		return [];
	}

	$userIds = array_unique($a['userIds']);

	$is_online = [];
	if ($userIds) {
		// Все онлайн юзеры на данный момент
		$onlineUserIds = RedisManager::getInstance()->zrangebyscore(Enum\Redis\RedisAliases::USERS_ONLINE, time(), time() + UserManager::LIVE_TIME);

		// Пересечем запрошенных юзеров со всеми, кто онлайн и заполним искомый массив is_online
		$is_online = array_fill_keys(array_intersect($userIds, $onlineUserIds), 1);
	}

	return $is_online;
}

function insert_last_order_time()
{
	$lastOrderTime = \Model\Order::where(\Model\Order::FIELD_STATUS, OrderManager::STATUS_INPROGRESS)
		->orderByDesc(\Model\Order::FIELD_OID)
		->value(\Model\Order::FIELD_TIME_ADDED);

	return max(time() - $lastOrderTime, 1);
}

function insert_session_get_and_clean($a) {
	$param = $a['param'];
	$session = Session\SessionContainer::getSession();
	if ($session->isExist($param)) {
		$result = $session->get($param);
		$session->delete($param);
		return $result;
	}
	return false;
}

function insert_get_payment_name($a){
	$payment = $a['payment'];
	$type = $a['type'];
	switch ($payment) {
		case 'yandex':
			$result = Translations::t('Яндекс.Деньги');
			break;
		case 'card':
		case 'card2':
			if ($type == 'refill') {
				$result = Translations::t('карты');
			}else{
				$result = Translations::t('карту');
			}
			break;
		case 'card3':
			if ($type == 'refill') {
				$result = Translations::t('карты');
			}else{
				$result = Translations::t('карту');
			}
			break;
		case 'webmoney':
		case 'webmoney2':
			$result = Translations::t('WebMoney');
			break;
		case 'webmoney3':
			$result = Translations::t('WebMoney');
			break;
		case 'qiwi':
			$result = Translations::t('Qiwi');
			break;
		case 'qiwi3':
			$result = Translations::t('Qiwi');
			break;
		case 'alfaClick':
			$result = Translations::t('Альфа-Банк');
			break;
		case 'euroset':
			$result = Translations::t('Евросеть');
			break;
		case 'svyaznoy':
			$result = Translations::t('Связной');
			break;
		case 'mts':
		case 'beeline':
		case 'mf':
		case 'tele2':
			if ($type == 'refill') {
				$result = Translations::t('телефона');
			}else{
				$result = Translations::t('телефон');
			}
			break;
		case 'bill':
			$result = Translations::t('безналичного счета');
			break;
		default:
			$result = $payment;
	}
	return $result;
}

// в зависимости от типа вывода, считает комиссию с суммы
function getWithdrawComisstion($amount, $type, $countryCode = null)
{
	$comission = 100;

	if($type == "card")
		$comission = App::config("purse.card.comission.internal");

	if($type == "webmoney")
		$comission = App::config("purse.webmoney.comission.internal");

	if($type == "yandex")
		$comission = App::config("purse.yandex.comission.internal");

	// выводим полную сумму, а комиссию режет платежка
	if($type == "qiwi")
		$comission = App::config("purse.qiwi.comission.internal");

	// тут 3 формулы, чтобы передать unitpay-ю столько, чтобы при его комиссиям итог равнялся комиссиям solar
	if($type == "card2")
	{
		// если не русская карта, то комиссия другая
		if($countryCode != "RU")
			return getOcComisstion($amount);

		if($amount < 1000)
			return number_format(70, 2, ".", "");

		if($amount < 1540)
			return number_format(($amount * 0.045) - 30, 2, ".", "");

		return number_format($amount / 39.2, 2, ".", "");
	}

	if($type == "webmoney2")
		$comission = App::config("purse.webmoney2.comission.internal");

	if (in_array($type, ['card3', 'webmoney3', 'qiwi3'])) {
		$comission = 0;
	}

	return number_format($amount / 100 * $comission, 2, ".", "");
}

// расчет суммы которую мы срежем при выводе на карты других стран до передачи в юнитпэй,
// так чтобы после того как юнитпэй срежет и свою комиссию,
// пользователю пришло на карту столько сколько у нас указано по тарифам солара
function getOcComisstion($amount)
{
	if($amount <= 5555)
		$cut = 0;
	elseif($amount <= 8464)
		$cut = number_format($amount - ($amount * 0.955) - 180, 2, ".", "");
	else
		$cut = number_format($amount / (100 / 3 * 2 - 2), 2, ".", "");

	// проверка не уйдет ли копейка в минус
	{
		$c = $amount - $cut;

		// юнитпэю
		$d = number_format(max($c / 100 * 3, 180), 2, ".", "");

		// пользователю
		$e = number_format($c - $d, 2, ".", "");

		// солар
		$f = number_format($amount - max($amount / 100 * 4.5, 180), 2, ".", "");

		$g = number_format(($e - $f), 2, ".", "");

		if($g < 0)
			$cut -= 0.01;
	}

	return $cut;
}

// количество пользователей онлайн
function insert_onlineUserCount()
{
	$lang = \Translations::getLang();
	return rand(0, 500);
}
/* не используется в текущее время */
function get_shortened_urls($matches) {
	$url = $matches[0];
	$url = urldecode(html_entity_decode($url));
	$maxLenght = 50;
	if (mb_strlen($url) > $maxLenght) {
		$url = mb_substr($url, 0, $maxLenght) . '...';
	}
	if(strpos("[url=", $matches[0]))
		return html_entity_decode($matches[0]);
	else
		return "<a rel='nofollow' target='_blank' href='" . html_entity_decode($matches[0]) . "'>$url</a>";
}
/* не используется в текущее время */
function replace_shortened_urls($string)
{
	// если в тексте уже есть href, то заменять не надо
	if(preg_match("/href=/i", $string))
		return $string;

	return preg_replace_callback(RegexpPatternManager::REGEX_URL, 'get_shortened_urls', $string);
}

function get_full_urls($matches) {
	$url = $matches[0];
	$url = urldecode(html_entity_decode($url));
	if (strpos("[url=", $matches[0]))
		return html_entity_decode($matches[0]);
	else
		return '<a rel="nofollow" target="_blank" class="shortened-url" href="' . htmlentities(html_entity_decode($matches[0]), ENT_QUOTES) . '">' . htmlentities($url) . '</a>';
}

function replace_full_urls($string) {
	if (preg_match("/(href|src)=('|\"|&quot;|&amp;quot;)/i", $string))
		return $string;

	return preg_replace_callback(RegexpPatternManager::REGEX_URL, 'get_full_urls', $string);
}

function insert_avg_work_time($a) {
	$time = intval($a["time"]);
	$hours = $time / Helper::ONE_HOUR;
	if ($hours > 23) {
		$days = $time / Helper::ONE_DAY;
		$result = round($days) . ' ' . declension(round($days), [Translations::t("день"), Translations::t("дня"), Translations::t("дней")]);
	}
	else{
		$result = ceil($hours) . ' ' . declension(ceil($hours), [Translations::t("час"), Translations::t("часа"), Translations::t("часов")]);
	}
	return $result;
}

function insert_get_cookie($cookie)
{
	return CookieManager::get($cookie['Name']);
}

function insert_get_category_landings($a){
	$landList = LandManager::getBy(['category_id' => $a['category_id']], 0);
	return $landList;
}

//  Замена заглавных букв строчными за исключением ссылок
function force_lower($str){
	$str = html_entity_decode($str);
	$idPart = md5($str);
	$links = [];
	$noLinks = preg_replace_callback(RegexpPatternManager::REGEX_URL,
		function($match) use(&$links, $idPart){
			$id = count($links);
			$id = md5($idPart.$id);
			$links[$id] = $match[0];
			return $id;
		},$str);
	$result = preg_replace_callback('/[A-ZА-ЯЁ]{6,}/u',function($match) {
		return mb_strtolower($match[0]);
	},$noLinks);

	foreach ($links as $id=>$link)
		$result = str_replace($id, $link, $result);
	unset($links);
	return htmlentities($result);
}
function insert_get_moderRejectComments($a) {
	global $conn;
	$kwork_id = $a['id'];
	$rejects = array_values($conn->getList("SELECT id, comment FROM kwork_moder WHERE status = 'reject' AND kwork_id = ".mres($kwork_id)." ORDER BY id DESC LIMIT 6"));
	return $rejects;
}
function sortByTime($a, $b)
{
	return $a->time == $b->time ? 0 : ($a->time < $b->time ? 1 : -1);
}

function sortByCount($a, $b)
{
	return $a["count"] == $b["count"] ? 0 : ($a["count"] < $b["count"] ? 1 : -1);
}


/**
* Сортировка ассоциативного массива по нескольким полям, вызывать через usort
* @param array $a - элемент массива
* @param array $b - элемент массива
* @param array $vals - поля и порядок сортировки: ex. $vals = ['date' => 'desc','type' => 'asc','name' => 'asc'];
*/
function multiSort($a, $b, $vals, $isObject = false) {

	foreach ($vals as $key => $val){
		if($val == "desc") {
			if ($isObject) {
				if ($a->{$key} > $b->{$key}) {
					return -1;
				}
				if ($a->{$key} < $b->{$key}) {
					return 1;
				}
			}else{
				if ($a["$key"] > $b["$key"]) {
					return -1;
				}
				if ($a["$key"] < $b["$key"]) {
					return 1;
				}
			}
		}


		if($val == "asc") {
			if ($isObject) {
				if ($a->{$key} < $b->{$key}) {
					return -1;
				}
				if ($a->{$key} > $b->{$key}) {
					return 1;
				}
			}else{
				if ($a["$key"] < $b["$key"]) {
					return -1;
				}
				if($a["$key"] > $b["$key"]) {
					return 1;
				}
			}
		}
	}
}

function timeDebugLog($timeStart, $description = '', $isFirst = false){
	//if (request('debug_time')) {
		$timeLeft = microtime(true) - $timeStart;
		$timeLeftDesc = ' ' . $description;
		$fileName = explode('?', $_SERVER['REQUEST_URI']);
		$fileName = 'debug_time_' . substr($fileName[0], strrpos($fileName[0], '/') !== false ? strrpos($fileName[0], '/') + 1 : 0);
		if ($isFirst) {
			Log::write('-----------------------------Начало нового расчета-----------------------------', $fileName);
		}
		Log::write($timeLeft . $timeLeftDesc, $fileName);
	//}
}

function mb_substr_replace($original, $replacement, $position, $length)
{
	$startString = mb_substr($original, 0, $position);
	$endString = mb_substr($original, $position + $length, mb_strlen($original));

	$out = $startString . $replacement . $endString;

	return $out;
}
// Для кириллицы \w заменяем на \p{L}\p{Nd} с модификатором /u
function mb_preg_match_all($ps_pattern, $ps_subject, &$pa_matches, $pn_flags = PREG_PATTERN_ORDER, $pn_offset = 0, $ps_encoding = NULL) {
	if (is_null($ps_encoding))
		$ps_encoding = mb_internal_encoding();

	$pn_offset = strlen(mb_substr($ps_subject, 0, $pn_offset, $ps_encoding));
	$ret = preg_match_all($ps_pattern, $ps_subject, $pa_matches, $pn_flags, $pn_offset);

	if ($ret && ($pn_flags & PREG_OFFSET_CAPTURE))
		foreach($pa_matches as &$ha_match)
			foreach($ha_match as &$ha_match)
				$ha_match[1] = mb_strlen(substr($ps_subject, 0, $ha_match[1]), $ps_encoding);

	return $ret;
}

function mb_str_replace($needle, $replacement, $haystack) {
	if(preg_match('/\|/',$needle))
	{
		$needle = preg_quote($needle,"|");
	}
	return implode($replacement, mb_split($needle, $haystack));
}

/**
 * Возвращает сумму прописью (только для русского)
 * @author runcore
 * @uses morph(...)
 */
function number2string($num) {
	if (Translations::getLang() != Translations::DEFAULT_LANG) {
		// Должно работать только для русской версии
		return $num;
	}
	$nul='ноль';
	$ten=array(
		array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
		array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
	);
	$a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
	$tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
	$hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
	$unit=array( // Units
		array('копейка' ,'копейки' ,'копеек',	 1),
		array('рубль'   ,'рубля'   ,'рублей'    ,0),
		array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
		array('миллион' ,'миллиона','миллионов' ,0),
		array('миллиард','милиарда','миллиардов',0),
	);
	//
	list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
	$out = array();
	if (intval($rub)>0) {
		foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
			if (!intval($v)) continue;
			$uk = sizeof($unit)-$uk-1; // unit key
			$gender = $unit[$uk][3];
			list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
			// mega-logic
			$out[] = $hundred[$i1]; # 1xx-9xx
			if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
			else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
			// units without rub & kop
			if ($uk>1) $out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
		} //foreach
	}
	else $out[] = $nul;
	$out[] = morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
	//$out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
	return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
}

/**
 * Склоняем словоформу
 * @ author runcore
 */
function morph($n, $f1, $f2, $f5) {
	$n = abs(intval($n)) % 100;
	if ($n>10 && $n<20) return $f5;
	$n = $n % 10;
	if ($n>1 && $n<5) return $f2;
	if ($n==1) return $f1;
	return $f5;
}
/**
 * Удаление смайлов из строки
 * @text - строка
 */
function removeEmoji($text) {

	$clean_text = "";

	// Match Emoticons
	$regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
	$clean_text = preg_replace($regexEmoticons, '', $text);

	// Match Miscellaneous Symbols and Pictographs
	$regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
	$clean_text = preg_replace($regexSymbols, '', $clean_text);

	// Match Transport And Map Symbols
	$regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
	$clean_text = preg_replace($regexTransport, '', $clean_text);

	// Match Miscellaneous Symbols
	$regexMisc = '/[\x{2600}-\x{26FF}]/u';
	$clean_text = preg_replace($regexMisc, '', $clean_text);

	// Match Dingbats
	$regexDingbats = '/[\x{2700}-\x{27BF}]/u';
	$clean_text = preg_replace($regexDingbats, '', $clean_text);

	return $clean_text;
}

if (!function_exists('array_column')) {
	function array_column($input, $column_key, $index_key = null) {
		$arr = array_map(function($d) use ($column_key, $index_key) {
			if (!isset($d[$column_key])) {
				return null;
			}
			if ($index_key !== null) {
				return array($d[$index_key] => $d[$column_key]);
			}
			return $d[$column_key];
		}, $input);

		if ($index_key !== null) {
			$tmp = array();
			foreach ($arr as $ar) {
				$tmp[key($ar)] = current($ar);
			}
			$arr = $tmp;
		}
		return $arr;
	}
}

function strip_mq_gpc($arg) {
	$arg = str_replace('"', "'", $arg);
	$arg = stripslashes($arg);
	return $arg;
}

function NewfoxToken() {
	$foxrandomtoken = md5(uniqid(rand(), true));
	return $foxrandomtoken;
}

/**
 * Получить относительный путь без параметров
 * @param string $uri
 * @return string
 */
function getRelativeUriWOParams(string $uri) : string {
	$urlParts = parse_url($uri);
	return isset($urlParts["path"]) ? $urlParts["path"] : "";
}

/**
 * Получить поля для определения режима очистки значений параметров запроса.
 *
 * @param string $uri
 * @return array
 */
function getSoftStrictModeFields(string $uri) {
	$uri = getRelativeUriWOParams($uri);
	if ($uri == "") {
		return [];
	}
	$uriForSoftMode = [
		"/sendmessage" => ["message_body"],
		"/track/action/text" => [Model\Track::FIELD_MESSAGE],
		"/track/action/instruction" => [Model\Track::FIELD_MESSAGE],
		"/track/action/worker_inprogress_check" => [Model\Track::FIELD_MESSAGE],
		"/track/action/edit" => [Model\Track::FIELD_MESSAGE],
	];
	return isset($uriForSoftMode[$uri]) ? $uriForSoftMode[$uri] : [];
}

function strict_fields(&$array, $length, $fields = []) {
	foreach ($array as $key => &$param) {
		if (is_array($param)) {
			strict_fields($param, $length, $fields);
		} else {
			$softMode = in_array($key, $fields);
			$param = clearInput($param, $length, $key, $softMode);
		}
	}
}

/**
 * Очистка значений параметров
 * @param $value string Значение параметра
 * @param $length mixed Максимальная длина параметра
 * @param string $key string Имя параметра
 * @return mixed|string|string[]|null
 */
function clearInput($value, $length, $key = "", $softMode = false) {
	// пробелы
	$value = trim($value);

	// слеши
	$value = str_replace("\\", "\\\\", $value);

	// от злостных хулиганов
	$value = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $value);
	$value = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $value);
	$value = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*d[\x00-\x20]*a[\x00-\x20]*t[\x00-\x20]*a[\x00-\x20]*:#iu', '$1=$2nojavascript...', $value);
	$value = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $value);
	$value = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $value);
	if (!$softMode) {
		$value = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $value);
		$value = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $value);

		// Делаем исключение для данных в ключе $excludedKey, которые пришли на путь начинающийся с $excludedURI.
		// Все данные указываем в нижнем регистре
		$excludedURI = "/administrator/kb/update/";
		$excludedKey = "answer_formatted";
		$exclude = (strtolower($key) == $excludedKey) && (strtolower(substr($_SERVER["REQUEST_URI"], 0, strlen($excludedURI))) == $excludedURI);

		if (!$exclude) {
			$value = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $value);
		}

		$value = preg_replace('#</*\w+:\w[^>]*+>#i', '', $value);

		do {
			$old_data = $value;
			$value = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $value);
		} while ($old_data !== $value);
	}

	$value = Helper::clearEmoji($value);

	// длина
	if (mb_strlen($value) > $length) {
		$value = mb_substr($value, 0, $length);
	}

	return $value;
}


function generateCode($length) {
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
	$code = "";
	$clen = mb_strlen($chars) - 1;
	while (mb_strlen($code) < $length) {
		$code .= $chars[mt_rand(0, $clen)];
	}
	return $code;
}

function download_photo($url, $saveto) {
	if (!curlSaveToFile($url, $saveto)) {
		if (!secondarysave($url, $saveto)) {
			return false;
		}
		return true;
	}
	return true;
}

function secondarysave($url, $local) {
	$ch = curl_init($url);
	$fp = fopen($local, 'wb');
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);

	if (filesize($local) > 10) {
		return true;
	}

	return false;
}

function curlSaveToFile($url, $local) {
	$ch = curl_init();
	$fh = fopen($local, 'w');
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FILE, $fh);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_VERBOSE, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_NOPROGRESS, true);
	curl_setopt($ch, CURLOPT_USERAGENT, '"Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.11) Gecko/20071204 Ubuntu/7.10 (gutsy) Firefox/2.0.0.11');
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_exec($ch);

	if (curl_errno($ch)) {
		return false;
	}

	curl_close($ch);
	fclose($fh);

	if (filesize($local) > 10) {
		return true;
	}

	return false;
}
/*
 * Используется для получения актора
 *
 * @return mixed
 */
function getActor() {
	global $mobileApi; //тут будет true только в случае мобильного api
	$session = Session\SessionContainer::getSession();

	$actor = null;

	if ($session->isExist("USERID")) {
		if (!is_numeric($session->get("USERID"))) {
			$session->delete("USERID");
			return false;
		}
		$userId = (int) $session->get("USERID");

		UserManager::vipeCacheUserData($userId);
		$actor = (object) UserManager::getUserData($userId);

		if (empty($actor)) {
			return NULL;
		}

		//Флаг блокировки английской версии сайта
		$actor->disableEn = ($actor->hide_en_kworks || $actor->disable_en_kworks);
		//Проверка на тестера английской версии сайта
		$actor->isLanguageTester =  UserManager::isLanguageTester($userId);

		// если залогинен виртуально
		$actor->isVirtual = $session->isExist('isVirtual') && $session->get('isVirtual');

		$actor->id = $actor->USERID;

		$actor->totalFunds = $actor->funds + $actor->bfunds + $actor->bill_funds + $actor->card_funds;
		$actor->holdAmount = $actor->bfunds + $actor->bill_funds + $actor->card_funds;
		$actor->freeAmount = $actor->funds;

		$actor->email = Crypto::decodeString($actor->email);
		$actor->analytics_enable = $actor->order_done_count >= 3;

		if ($actor && !$actor->isVirtual) {
			// Если юзер по факту онлайн, но считается что его в онлайне нет
			if (!UserManager::checkUserOnlineStatus($actor->id)) {
				UserManager::updateOnlineStatus($actor->id, true);
				// Обновляем live_date каждые UserManager::LIVE_TIME
			} elseif ($actor->live_date < time() - UserManager::LIVE_TIME) {
				UserManager::updateUserLiveDate($actor->id);
			}
		}

		// показывать ли уведомления об опросе
		if (!App::config("module.poll_notify.enable")) {
			$actor->show_poll_notify = false;
		} else {
			if ($actor->show_poll_notify) {
				$pollHash = Poll::getUserHash($actor->id);
				if ($pollHash) {
					$actor->pollHash = $pollHash[Poll::F_UH_HASH];
				} else {
					$actor->show_poll_notify = false;
				}
			}
		}

		if ($actor->kwork_allow_status == "deny") {
			$lastReason = AbuseManager::getLastBlockData($actor->id);
			if (!empty($lastReason)) {
				$actor->kworkBlock = [
					"blockType" => $lastReason["block_type"],
					"dueDate" => date("d.m.Y, H:i", strtotime($lastReason["due_date"])),
					"reason" => $lastReason["reason"]
				];
			}
		}
		// если у пользователя маленький аватар с частичным показом, то в профиле показываем ему дефолтный аватар
		if($actor->{UserManager::FIELD_AVATAR_TYPE} == UserManager::AVATAR_TYPE_CHUNK)
			$actor->profilepicture = UserManager::PROFILE_PICTURE_DEFAULT;

		// если актор может входить только с опредененного ip
		if ($actor && $actor->allow_ip) {
			$ip = BanManager::getIp();
			if ($ip != $actor->allow_ip) {
				UserManager::logout();

				$actor = null;
			}
		}
	}

	return $actor;
}

function clearBBUrl($message){
	$pattern = '|\[URL=.*?\](.*?)\[\/URL\]|i';
	return preg_replace($pattern, '$1', $message);
}

/**
 * Вырезаем bb-коды ссылки и жирного текста
 *
 * @param string $message
 * @return string
 */
function clearBBUrlBold($message) {
	$patternURL = '|\[URL=.*?\](.*?)\[\/URL\]|i';
	$patternBold = '|\[B\](.*?)\[\/B\]|i';

	$result = preg_replace($patternURL, '$1', $message);
	$result = preg_replace($patternBold, '$1', $result);

	return $result;
}

/**
 * Статический именованный кэш.
 * Использование:
 * <code>
 *  $data = &kwork_static(__FUNCTION__ . $uniqId);
 *  if (!isset($data)) { // или, если возможен NULL, по-другому как угодно
 *    $data = query('SELECT 2Gb данных FROM table WHERE id = $uniqId');
 *  }
 *  return $data;
 * </code>
 *
 * @param string $name
 * @param null $default_value
 * @param bool $reset
 *
 * @return array|mixed
 */
function &kwork_static(string $name, $default_value = NULL, bool $reset = FALSE) {
	static $data = [], $default = [];

	if (isset($data[$name]) || array_key_exists($name, $data)) {
		if ($reset) {
			$data[$name] = $default[$name];
		}
		return $data[$name];
	}

	if (isset($name)) {
		if ($reset) {
			return $data;
		}

		$default[$name] = $data[$name] = $default_value;
		return $data[$name];
	}

	foreach ($default as $name => $value) {
		$data[$name] = $value;
	}

	return $data;
}

function get_canonical_url() {
	$requestUri = $_SERVER['REQUEST_URI'];
	$requestArray = explode('?', $requestUri);
	return preg_replace('/\/ref\/\d+/', '', $requestArray[0]);
}

/**
 * Показывать ли блок переключения языковой версии кворка
 *
 * @param int $kworkUserId ID пользователя создавшего кворк
 * @return bool результат
 */
function showLangSwitchBlock($kworkUserId):bool {
	global $actor;
	return $actor
		&& !$actor->disableEn
		&& $actor->lang == \Translations::DEFAULT_LANG
		&& $actor->id == $kworkUserId
		&& (App::config("module.lang.enable") || $actor->isLanguageTester);
}

/**
 * Не принадлежит текущему пользователю
 *
 * @param int $userId ID пользователя
 * @return bool результат
 */
function isNotAllowUser($userId):bool {
	return !isAllowToUser($userId);
}

/**
 * Принадлежит текущему пользователю
 *
 * @param int $userId ID пользователя
 * @return bool результат
 */
function isAllowToUser($userId):bool {
	global $actor;
	return $actor && $actor->id == $userId;
}

//@TODO: Выпилить когда будет нормальный роутинг везде
/**
 * Создать урл для категории
 *
 * @param string $categoryAlias название категории
 * @return string URL
 */
function categoryUrl($categoryAlias) {
	$baseUrl = \App::config("baseurl");
	$catalog = Controllers\Catalog\AbstractViewController::DEFAULT_VIEW;
	return stripslashes(mb_strtolower(trim($baseUrl . "/$catalog/" . $categoryAlias)));
}

//@TODO: Выпилить когда будет нормальный роутинг везде
/**
 * Создать урл на профиль пользователя
 *
 * @param string $username имя пользователя
 * @return string урл
 */
function userProfileUrl($username) {
	$baseUrl = \App::config("baseurl");
	return stripslashes(mb_strtolower(trim($baseUrl . "/user/" . $username)));
}

//@TODO: Выпилить когда будет нормальный роутинг везде
/**
 * Создать урл для изображения пользователя среднего размера
 *
 * @param string $username имя пользователя
 * @return string урл
 */
function userMediumPicture($username) {
	$baseUrl = \App::config("membersprofilepicurl");
	return stripslashes($baseUrl . "/medium/" . $username);
}

/**
 * Создать атрибут srcset для тега <img>, содержащий ссылки на изображения
 * пользователя среднего размера
 *
 * @param string $picture название изображения
 * @return string srcset
 */
function userMediumPictureSrcset($picture) {
	$baseDir = \App::config("membersprofilepicdir");
	$baseUrl = \App::config("membersprofilepicurl");

	$srcset = "";

	if (file_exists($baseDir . "/medium_r/" . $picture)) {
		$srcset .= "srcset=\"";
		$srcset .= $baseUrl . "/medium/" . $picture . " 1x, ";
		$srcset .= $baseUrl . "/medium_r/" . $picture . " 2x";
		$srcset .= "\"";
	}

	return $srcset;
}

/**
 * Создать атрибут srcset для тега <img>, содержащий ссылки на изображения
 * указанного размера
 *
 * @param string $picture название изображения
 * @return string srcset
 */
function photoSrcset($size, $picture) {
	$baseDir = \App::config("pdir");
	$baseUrl = \App::config("purl");

	$sizeR = $size . "_r";
	$srcset = "";

	return $srcset;
}

/**
 * Пользователь с русским языком или текущий язык кворка русский
 *
 * @return bool результат
 */
function isUserOrContextRu():bool {
	global $actor;
	return $actor->lang == \Translations::DEFAULT_LANG || \Translations::isDefaultLang();
}

/**
 * Русский язык или нет
 *
 * @param string $lang язык
 * @return bool результат
 */
function isRu($lang):bool {
	return $lang == \Translations::DEFAULT_LANG;
}

/**
 * Не русский язык или нет
 *
 * @param string $lang язык
 * @return bool результат
 */
function isNotRu($lang):bool {
	return ! isRu($lang);
}

/**
 * Получить название пакета по его типу
 *
 * @param string $packageType тип пакета
 * @return mixed|string название пакета
 */
function getPackageName($packageType) {
	return PackageManager::getName($packageType);
}

/**
 * Получить полный урл на картинку по ее названию
 *
 * @param string $imageName название картинки
 * @return string урл
 */
function getImageUrl($imageName) {
	$baseUrl = \App::config("baseurl");
	return $baseUrl . getImageUrlRelative($imageName);
}

/**
 * Получить относительный урл для картинки по ее имени
 *
 * @param string $imageName название картинки
 * @return string урл
 */
function getImageUrlRelative($imageName) {
	return "/images/" . $imageName;
}

/**
 * Получить урл для загруженного файла
 *
 * @param array|stdClass $file файл
 * @return string урл
 */
function getUploadFileUrl($file) {
	$uploadUrl = \App::config("uploadedurl");
	if ($file instanceof \stdClass) {
		return $uploadUrl . "/" . $file->fpath . "/" . urlencode($file->fname);
	} elseif ($file instanceof \Model\File) {
		return $uploadUrl . "/" . $file->s . "/" . urlencode($file->fname);
	}
	return $uploadUrl . "/" . $file["fpath"] . "/" . urlencode($file["fname"]);
}

//@TODO: Выпилить когда будет нормальный роутинг везде
/**
 * Получить ссылку на редактирование кворка
 *
 * @param int $kworkId идентификатор кворка
 * @return string урл
 */
function editKworkUrl($kworkId){
	$baseUrl = \App::config("baseurl");
	return $baseUrl . "/edit?id=" . $kworkId;
}

/**
 * Получиить ссылку на изображение T2
 *
 * @param string $image изображение
 * @return string
 */
function getImageT2Url($image) {
	$pictureUrl = \App::config("purl");
	return $pictureUrl . "/t2/" . $image;
}

/**
 * Пользователь авторизирован
 *
 * @return bool
 */
function isAuth():bool {
	global $actor;
	return $actor ? true : false;
}

/**
 * Пользователь не авторизирован
 *
 * @return bool
 */
function isNotAuth():bool {
	return !isAuth();
}

/**
 * Вернуть реферальный параметр для урл
 *
 * @return string
 */
function getRefParam():string {
	global $actor;
	return "?ref=" . $actor->id;
}

/**
 * Вернуть полный урл с реферальным параметром
 *
 * @return string
 */
function getAbsoluteReferralUrl():string {
	global $actor;
	$baseUrl = \App::config("baseurl");
	return $baseUrl . "/ref/" . $actor->id;
}

/**
 * Язык пользователя
 *
 * @return string
 */
function getUserLang() {
	global $actor;
	return $actor->lang;
}

/**
 * Есть ли ошибка
 *
 * @param \Illuminate\Support\MessageBag $errors
 * @param string $key
 */
function hasError($errors, $key) {
	if (empty($errors) || ! $errors instanceof \Illuminate\Support\MessageBag) {
		return false;
	}
	return $errors->has($key);
}

/**
 * Получить ошибку
 *
 * @param \Illuminate\Support\MessageBag $errors
 * @param string $key
 */
function getError($errors, $key) {
	if (empty($errors) || ! $errors instanceof \Illuminate\Support\MessageBag) {
		return "";
	}
	return $errors->first($key);
}

/**
 * Показывать или нет текущую страницу
 *
 * @param \Illuminate\Pagination\LengthAwarePaginator $paginator пагинатор
 * @param int $page текущая страница
 * @return bool результат
 */
function showPaginationPage(\Illuminate\Pagination\LengthAwarePaginator $paginator, $page = 0):bool {
	return ($paginator->currentPage() - 2) == $page ||
		($paginator->currentPage() - 1) == $page ||
		$paginator->currentPage() == $page ||
		($paginator->currentPage() + 1) == $page ||
		($paginator->currentPage() + 2) == $page;
}

/**
 * Показывать ли многоточие в пагинации в левой части
 *
 * @param \Illuminate\Pagination\LengthAwarePaginator $paginator пагинатор
 * @return bool результат
 */
function showLeftThreeDots(\Illuminate\Pagination\LengthAwarePaginator $paginator):bool {
	return $paginator->lastPage() > 7 &&
		$paginator->currentPage() > 4;
}

/**
 * Показывать ли многоточие в правой части
 *
 * @param \Illuminate\Pagination\LengthAwarePaginator $paginator пагинатор
 * @return bool результат
 */
function showRightThreeDots(\Illuminate\Pagination\LengthAwarePaginator $paginator):bool {
	return $paginator->lastPage() > 7 &&
		($paginator->lastPage() - $paginator->currentPage()) > 3;
}

//@TODO: Выпилить когда будет нормальный роутинг везде
/**
 * Создать урл для изображения пользователя маленького размера
 *
 * @param string $username имя пользователя
 * @return string урл
 */
function userSmallPicture($username) {
	$baseUrl = \App::config("membersprofilepicurl");
	return stripslashes($baseUrl . "/small/" . $username);
}

/**
 * Сделать из относительного урл абсолютный
 *
 * @param string $url урл без домена
 * @return string результат
 */
function getAbsoluteURL($url) {
	$baseUrl = \App::config("baseurl");
	return $baseUrl . $url;
}

/**
 * Пользователь новичек
 *
 * @return bool результат
 */
function isUserNovice():bool {
	global $actor;
	return $actor && $actor->level == UserLevelManager::LEVEL_NOVICE;
}

/**
 * Пользователь не новичек
 *
 * @return bool результат
 */
function isUserNotNovice():bool {
	return !isUserNovice();
}

//@TODO: Выпилить когда будет нормальный роутинг везде
/**
 * Создать урл для изображения пользователя большого размера
 *
 * @param string $username имя пользователя
 * @return string урл
 */
function userBigPicture($username) {
	$baseUrl = \App::config("membersprofilepicurl");
	return stripslashes($baseUrl . "/big/" . $username);
}

/**
 * Получиить ссылку на изображение T4
 *
 * @param string $image изображение
 * @return string
 */
function getImageT4Url($image) {
	$pictureUrl = \App::config("purl");
	return $pictureUrl . "/t4/" . $image;
}

/**
 * Виртуальный ли пользователь
 *
 * @return bool результат
 */
function isVirtual() {
	return UserManager::isVirtual();
}

/**
 * Сделать из относительного урл абсолютный на оригинальный сайт
 * если у нас зеркало
 *
 * @param string $url урл без домена
 * @return string результат
 */
function getAbsoluteOriginalURL($url) {
	$baseUrl = \App::config("originurl");
	return $baseUrl . $url;
}

/**
 * Ссылка для входа
 *
 * @param string $redirect редирект
 * @return string ссылка
 */
function getLoginUrl($redirect = null) {
	$baseUrl = \App::config("baseurl");

	if (!empty(trim($redirect))) {
		$redirect = "?r=" . stripslashes($redirect);
	}

	return $baseUrl . "/login" . $redirect;
}

//@TODO: Выпилить когда будет нормальный роутинг везде
/**
 * Создать урл для изображения пользователя большого(large) размера
 *
 * @param string $username имя пользователя
 * @return string урл
 */
function userLargePicture($username) {
	$baseUrl = \App::config("membersprofilepicurl");
	return stripslashes($baseUrl . "/large/" . $username);
}

//@TODO: Выпилить когда будет нормальный роутинг везде
/**
 * Создать ссылку на диалог с пользователем
 *
 * @param string $username имя пользователя
 * @return string url
 */
function getConversationUrl($username) {
	$baseUrl = \App::config("baseurl");
	return $baseUrl . "/conversations/" . strtolower($username) . "?goToLastUnread=1";
}

/**
 * Получиить ссылку на изображение T3
 *
 * @param string $image изображение
 * @return string
 */
function getImageT3Url($image) {
	$pictureUrl = \App::config("purl");
	return $pictureUrl . "/t3/" . $image;
}

/**
 * Получить ссылку на send_track
 *
 * @return string
 */
function getSendTrackUrl():string {
	$baseurl = \App::config("baseurl");
	return $baseurl . "/send_track";
}

/**
 * Максимальное количество кворков которое можно купить для кворка с объемом
 *
 * @param float $price Цена кворка или пакета
 * @param string $lang Язык кворка ru|en
 *
 * @return float
 */
function maxKworkCountForVolume($price, $lang) {
	$price = (float)$price;

	$maxKworkCount = \App::config("kwork.max_count");
	$maxTotal = \App::config("order.volume_max_total_$lang");
	if ($maxTotal && $price && \App::config(\Configurator::ENABLE_VOLUME_TYPES_FOR_BUYERS)) {
		$maxKworkCount = floor($maxTotal / $price);
	}

	return $maxKworkCount;
}

/**
 * Минимальное количество кворков которое можно купить для кворка с объемом
 *
 * @param float $kworkVolume Объем кворка
 * @param float $minVolumePrice Минимальная цена кворка или пакета
 * @param float $price Цена кворка или пакета
 *
 * @return float
 */
function minKworkCountForVolume($kworkVolume, $minVolumePrice, $price) {
	$price = (float)$price;
	$minVolumePrice = (float)$minVolumePrice;

	$minKworkCount = $kworkVolume;
	
	if ($minVolumePrice && $price && \App::config(\Configurator::ENABLE_VOLUME_TYPES_FOR_BUYERS)) {
		$minKworkCount = ceil ($minVolumePrice / $price * $kworkVolume);
	}

	return $minKworkCount;
}

/**
 * Получить float из string (очищает значения заменяя пробелы и разделители)
 *
 * @param string $string Значение из post
 *
 * @return float
 */
function cleanFloat($string): float {
	$string = str_replace(" ", "", $string);
	$string = str_replace(",", ".", $string);
	return (float)$string;
}

/**
 * Получить из строки целое число, в строке удалятся все пробелы
 *
 * @param string $string
 * @return int
 */
function strToInt(string $string): int {
	return (int) str_replace(" ", "", trim($string));
}

/**
 * Подсчет медианы по массиву
 * @param array $arr Массив
 *
 * @return float|int
 */
function array_median($arr) {
	sort($arr);
	$count = count($arr); //total numbers in array
	$middleval = (int)floor(($count - 1) / 2); // find the middle value, or the lowest middle value
	if ($count % 2) { // odd number, middle is the median
		$median = $arr[$middleval];
	} else { // even number, calculate avg of 2 medians
		$low = $arr[$middleval];
		$high = $arr[$middleval + 1];
		$median = (($low + $high) / 2);
	}
	return $median;
}

/**
 * Заменяем примитивный bbcode
 *
 * @param string $string
 * @param array $bbcodes
 * @return string
 */
function bbcodeToHTML($string, $bbcodes): string {

	$replaceFrom = $replaceTo = array();
	$replaceTags = array(
		'b' => 'strong',
		'i' => 'em',
	);

	foreach ($bbcodes as $bbcode) {
		$replaceFrom[]	= "[" . $bbcode . "]";
		$replaceFrom[]	= "[/" . $bbcode . "]";

		$replaceTo[]	= '<' . $replaceTags[$bbcode] . '>';
		$replaceTo[]	= '</' . $replaceTags[$bbcode] . '>';
	}

	return str_replace($replaceFrom, $replaceTo, trim($string));
}

/**
 * Округляет число до ровно 2 знаков после запятой
 *
 * @param float $value Число
 * @return float
 */
function round2($value) {
	return number_format($value, 2);
}

/**
 * Округляет число до ровно 4 знаков после запятой
 *
 * @param float $value Число
 * @return float
 */
function round4($value) {
	return number_format($value, 4);
}

/**
 * Получить критические стили для посадочных страниц
 *
 * @param string $page Посадочная страница
 * @return string
 */
function getCriticalStyles($page) {
	$criticalStyles = "";
	$baseDir = \App::config("basedir");

	//получаем общие стили
	$criticalStylesGeneral = $baseDir . "/css/critical_styles/general_critical.css";
	if (file_exists($criticalStylesGeneral)) {
		$criticalStyles .= file_get_contents($criticalStylesGeneral);
	}

	////получаем стили посадочной страницы
	$criticalStylesPage = $baseDir . "/css/critical_styles/" . $page . "_critical.css";
	if (file_exists($criticalStylesPage)) {
		$criticalStyles .= file_get_contents($criticalStylesPage);
	}

	//вырезаем табуляции, переносы строк и комментарии
	$criticalStyles = preg_replace("/[\t\r\n]+|\/\*[^\*]*\*\//", "", $criticalStyles);

	//убираем лишние пробелы и точки с запятой
	$criticalStyles = str_replace(array(" {", ": ", ", ", ";}", " !important"), array("{", ":", ",", "}", "!important"), $criticalStyles);

	return $criticalStyles;
}

/**
 * Определят, относится ли запрашиваемая страница к Админке
 * @return bool
 */
function isAdminPage(): bool {
	return mb_strpos($_SERVER['REQUEST_URI'], "/administrator/") === 0;
}

/**
 * Разбивает строку на массив (корректно работает с кириллицей в UTF-8)
 *
 * @param string $string
 * @return array
 */
function mb_strtoarray(string $string): array {
	$result = preg_split('//u', $string, null, PREG_SPLIT_NO_EMPTY);
	return ($result) ? $result : [];
}

/**
 * Преобразовать коллекцию анонимных объектов в массив массивов
 *
 * @param \Illuminate\Support\Collection $collection
 *
 * @return array
 */
function collectionToArray(Illuminate\Support\Collection $collection) {
	return array_map(function ($n) {
		return (array)$n;
	}, $collection->toArray());
}