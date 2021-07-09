<?php

use Core\DB\DB;
use Model\Contest\Contest;
use Model\Contest\Contest2018Idea\ContestEntity as Contest2018Idea;
use Model\Inbox\{Inbox,InboxDraft};
use Model\Support\SupportTrainingMessage;
use Model\{File,Track,TrackDraft,Order,Kwork,Want};
use Track\Type;
use Illuminate\Database\Eloquent\Relations;

class FileManager {

	const TABLE_NAME = 'files';
	const FIELD_ID = "FID";
	const FIELD_FNAME = "fname";
	const FIELD_S = "s";
	const FIELD_USERID = "USERID";
	const FIELD_ENTITY_ID = "entity_id";
	const FIELD_ENTITY_TYPE = "entity_type";
	const FIELD_LANG = "lang";
	const FIELD_USER_ID = "USERID";
	const FIELD_TIME = "time";
	const FIELD_IP = "ip";
	const FIELD_SIZE = "size";
	const FIELD_STORAGE = "storage";
	const FIELD_MINIATURE_STATUS = "miniature_status";
	const ENTITY_TYPE_KWORK_DESCRIPTION = 'kwork_description';
	const ENTITY_TYPE_KWORK_INSTRUCTION = 'kwork_instruction';
	const ENTITY_TYPE_CONVERSATION = 'conversation';
	const ENTITY_TYPE_INBOX_DRAFT = 'inbox_draft';
	const ENTITY_TYPE_TRACK = 'track';
	const ENTITY_TYPE_TRACK_DRAFT = 'track_draft';
	const ENTITY_TYPE_WANT = 'want';
	const ENTITY_TYPE_CONTEST = 'contest';
	const ENTITY_TYPE_SUPPORT = 'support';
	const ENTITY_TYPE_CONTEST_TEXT = 'contest_text';
	const ENTITY_TYPE_TRAINING_MESSAGE = 'training_message';
	const ENTITY_TYPE_CONTEST_2018_IDEA = 'contest_2018_idea';
	const ERROR_NO_FILE = "no_file_uploaded";
	const ERROR_FILE_SIZE_EXCEED = "file_size_exceed";
	const ERROR_USER_NON_AUTHORIZED = "user_non_authorized";
	const ERROR_INVALID_FILENAME = "invalid_filename";
	const ERROR_EMPTY_MESSAGE = "empty_message";
	const ERROR_NOT_EDITABLE = "not_editable";
	/*
	 * Список разрешений для pdf файла
	 */
	const EXTENSION_PDF = ['.pdf'];
	/*
	 * Ключ в path.cnf для каталога демофайлов
	 */
	const CONFIG_DEMOFILE_DIR_KEY = 'demofiledir';
	/*
	 * Ключ в path.cnf для url демофайлов
	 */
	const CONFIG_DEMOFILE_URL_KEY = 'demofileurl';

	/**
	 * Время хранения файлов в месяцах
	 */
	const RETENTION_PERIOD = 6;

	/**
	 * Максимальное количество показов уведомлений о сроке хранения файлов
	 */
	const MAX_RETENTION_PERIOD_NOTICE_COUNT = 3;

	/**
	 * Название лога, куда ведётся логирование процесса загрузки файла
	 */
	const UPLOAD_LOG_FILENAME = "file_upload";

	/**
	 * Удалить файл
	 *
	 * @return array
	 */
	public static function api_delete() {
		$actor = UserManager::getCurrentUser();
		if (!$actor) {
			return ["result" => "error"];
		}

		$fileId = intval(request("file_id"));
		if (!$fileId) {
			return ["result" => "error"];
		}
		$file = File::find($fileId);
		if (!$file) {
			return ["result" => "error"];
		}
		$fileEntity = $file->entity()->first();

		if ($fileEntity instanceof Track && !$fileEntity->isEditable($actor->id)) {
			return [
				"result" => "error",
				"reason" => self::ERROR_NOT_EDITABLE,
			];
		}

		if ($fileEntity instanceof Inbox && !$fileEntity->isEditable($actor->id)) {
			return [
				"result" => "error",
				"reason" => self::ERROR_NOT_EDITABLE,
			];
		}

		if ($file && $file->USERID == $actor->id) {

			// Если это файл из трека-сообщения, то будем отправлять пуш об изменении
			if ($fileEntity instanceof Track) {
				TrackManager::sendTrackChangedPush($fileEntity->MID, ["action" => TrackManager::CHANGE_FILE_REMOVED]);
			}

			// Если файл из Диалогов и сообщение пустое, то нельзя удалить последний файл
			if ($fileEntity instanceof Inbox) {
				if (empty($fileEntity->message->message)) {
					$fileCount = self::getEntityFilesCount($fileEntity->MID, File::ENTITY_TYPE_CONVERSATION);
					if ($fileCount <= 1) {
						return [
							"result" => "error",
							"reason" => self::ERROR_EMPTY_MESSAGE,
						];
					}
				}
			}

			try {
				$fileDeleted = $file->safeDelete();
				if ($fileDeleted) {
					return ["result" => "success"];
				} else {
					return ["result" => "error"];
				}
			} catch (Exception $e) {}
		}

		return ["result" => "error"];
	}

	public static function upload($fileUrl, $fileName) {
		$newFilePath = self::generateFilePath();
		move_uploaded_file($fileUrl, App::config('uploadeddir') . $newFilePath);
		return $newFilePath;
	}

	public static function copy($fileSrc) {
		$newFilePath = self::generateFilePath();
		copy($fileSrc, App::config('uploadeddir') . $newFilePath);
		return $newFilePath;
	}

	/**
	 * Определение ID пользователя, загрузившего файл (для диалогов с саппортом)
	 * @return int
	 */
	private static function getSupportFileUserId() {
		$actor = UserManager::getCurrentUser();

		$baseUrl = App::config("baseurl");
		if (empty($actor->id) && strpos($_SERVER["HTTP_REFERER"], $baseUrl . "/new_project") === 0) {
			return UserManager::UNREGISTERED_USER_ID;
		}
		if ((empty($actor->id) || $actor->isVirtual) && strpos($_SERVER["HTTP_REFERER"], $baseUrl . "/support_dialog.php") === 0) {
			$session = Session\SessionContainer::getSession();
			if ($session->notEmpty("ADMINID") && $session->notEmpty("ADMINUSERNAME") && $session->notEmpty("ADMINPASSWORD")) {
				return App::config("kwork.support_id");
			}
		}

		return $actor->id;
	}

	/**
	 * Привязать файлы к сущности
	 *
	 * @param int|array $fileId массив или идентификатор файла/файлов
	 * @param int $entityId идентификатор сущности
	 * @param string $entityType тип сущности
	 * @return bool
	 */
	public static function fileLink($fileId, $entityId, $entityType) {
		$userId = self::getSupportFileUserId();

		if (empty($fileId)) {
			return false;
		}

		if (!is_array($fileId)) {
			$fileId = [$fileId];
		}

		$files = File::whereIn(File::FIELD_USERID, [$userId, UserManager::UNREGISTERED_USER_ID])
			->whereIn(File::FIELD_ID, $fileId)
			->get();

		/** @var File $file */
		foreach ($files as $file) {
			/*
			 * если локального файла-исходника не существует по какой--либо причине
			 * (например, это дубль сообщения и файл уже был загружен в Амазон и удален локально)
			 * добавлять этот файл не будем даже пытаться
			 */
			if (!file_exists($file->path) && !file_exists($file->demoFilePath)) {
				continue;
			}

			$file->entity_id = $entityId;
			$file->entity_type = $entityType;
			$file->USERID = $userId;

			// определимся по entityType где будет храниться файл и сохраним через хендлер
			try {
				$file->setStorageAndStore();
			} catch (\Exception $exception) {
				// в случае ошибки загрузки файла не будем прерываться, просто не будем его прикреплять,
				// и залогируем ошибку. Файл удалится кроном через несколько дней как никуда не
				// прикрепленный
				Log::dailyErrorException($exception);
				continue;
			}

			if ($file->save()) {
				$file->createCache();
			}
		}

		return true;
	}

	/**
	 * Добавление записи в таблицу и сохранение файла через хендлер в хранилище, если требуется
	 *
	 * @param array $value - Поля для записи
	 * @param File|null $originalFile файл-оригинал, если не задан - должен существовать
	 * локальный исходник файла в $file->path
	 * @return false|int – Идентификатор файла в таблице
	 */
	public static function createNewFile($value, File $originalFile = null) {
		// Храним в базе уже в закодированном виде
		$value[self::FIELD_FNAME] = removeEmoji($value[self::FIELD_FNAME]);
		$value[self::FIELD_FNAME] = htmlentities(html_entity_decode($value[self::FIELD_FNAME]), ENT_QUOTES);
		if ($value[self::FIELD_FNAME] == "") {
			$value[self::FIELD_FNAME] = "file";
		}

		/** @var File $file */
		$file = new File();
		$file->fill($value);

		if ($file->entity_type) {
			// определимся по entityType где будет храниться файл и сохраним через хендлер
			try {
				// Если оригинальный файл не существует на диске то не будем его создавать, будет эксепшен
				// но при этом не будем останавливать работу, чтобы заказ заказывался
				$file->setStorageAndStore(null, $originalFile);
			} catch (\Exception $exception) {
				Log::dailyErrorException($exception);
				return false;
			}
		}

		if ($file->save()) {
			$file->createCache($originalFile);

			return $file->FID;
		}

		return false;
	}

	/**
	 * API-метод для загрузки файла.
	 *
	 * @param array $_FILES Данные о загружаемом файле
	 * @param string $_POST["lang"] Язык домена
	 * @param bool $_POST["with_miniature"] Нужно ли создавать миниатюры для файла
	 * @param bool $_POST["second_user_id"] Идентификатор второго пользователя,
	 *     используется совместно с with_miniature
	 * @return array
	 */
	public static function api_upload() {
		$userId = self::getSupportFileUserId();

		$tmpFileSrc = $_FILES["upload_files"]["tmp_name"];
		$fileName = removeEmoji($_FILES["upload_files"]["name"]);
		$fileSize = $_FILES["upload_files"]["size"];

		self::logUploadProcess("Start to upload: user {$userId}, file: {$fileName}, tmp {$tmpFileSrc}");

		if (empty($userId)) {
			self::logUploadProcess("Error " . self::ERROR_USER_NON_AUTHORIZED . ": user {$userId}, file: {$fileName}, tmp {$tmpFileSrc}");
			return [
				"result" => "error",
				"reason" => self::ERROR_USER_NON_AUTHORIZED,
			];
		}
		if (!$tmpFileSrc || !$fileName) {
			self::logUploadProcess("Error " . self::ERROR_NO_FILE . ": user {$userId}, file: {$fileName}, tmp {$tmpFileSrc}");
			return [
				"result" => "error",
				"reason" => self::ERROR_NO_FILE
			];
		}

		if (!self::isAllowedFileSize($tmpFileSrc)) {
			self::logUploadProcess("Error " . self::ERROR_FILE_SIZE_EXCEED . ": user {$userId}, file: {$fileName}, tmp {$tmpFileSrc}, size: " . filesize($tmpFileSrc));
			return [
				"result" => "error",
				"reason" => self::ERROR_FILE_SIZE_EXCEED
			];
		}

		// на kwork.com запретить загружать файлы с русскими символами в названии файла
		$lang = post("lang");
		if (in_array($lang, Translations::getLangArray()) && !self::isAllowedFileName($fileName, $lang)) {
			self::logUploadProcess("Error " . self::ERROR_INVALID_FILENAME . ": user {$userId}, file: {$fileName}, tmp {$tmpFileSrc}, lang {$lang}");
			return [
				"result" => "error",
				"reason" => self::ERROR_INVALID_FILENAME,
			];
		}

		$fileName = self::sanitizeFileName($fileName);
		$newFilePath = self::generateFilePath();

		// Нужно ли создавать миниатюры для файла
		$fileContentType = mime_content_type($tmpFileSrc);
		$needMiniature = FileMiniatureManager::needMiniatures($fileContentType);
		
		$value = [
			File::FIELD_USERID => $userId,
			File::FIELD_FNAME => $fileName,
			File::FIELD_S => $newFilePath,
			File::FIELD_TIME => time(),
			File::FIELD_IP => $_SERVER["REMOTE_ADDR"],
			File::FIELD_SIZE => $fileSize,
			File::FIELD_LANG => \Translations::getLang(),
			File::FIELD_MINIATURE_STATUS => $needMiniature
				? File::MINIATURE_STATUS_CREATING
				: File::MINIATURE_STATUS_NONE,
		];
		
		$fileId = self::createNewFile($value);

		if (!$fileId) {
			return [
				"result" => "error",
				"reason" => self::ERROR_NO_FILE,
			];
		}
		
		$filePath = App::config("uploadeddir") . $newFilePath;
		move_uploaded_file($tmpFileSrc, $filePath);

		self::logUploadProcess("Uploaded: user {$userId}, file: {$fileName}, tmp {$tmpFileSrc}, path: {$filePath}");
		
		// Создание миниатюр для файла
		if (post("with_miniature") && $needMiniature) {
			$secondUserId = (int) post("second_user_id") ?: null;
			\Work\CreateFileMiniature::addWork($fileId, $secondUserId);
		}

		// Создать низкокачественный плейсхолдер для файла
		FileLqipManager::processFileUpload($fileId, $fileContentType, $filePath);

		return [
			"result" => "success",
			"file_id" => $fileId,
			"file_name" => $fileName,
			"file_path" => App::config("uploadedurl") . "/" . $newFilePath . "/" . $fileName,
			"file_path_hash" => $newFilePath,
			"file_extension" => self::getFileExtension($fileName),
			"need_miniature" => $needMiniature,
		];
	}

	/**
	 * Возвращает информацию о файле для фронта,
	 * структура ответа как в self::api_upload()
	 * Нужен для метода \OrderManager::api_getOrderProvidedData()
	 * @param  stdClass|\Model\File $file
	 * @return array
	 */
	public static function toUploaderFormat($file) {
		return [
			"file_id" => $file->FID,
			"file_name" => $file->fname,
			"file_path" => App::config('uploadedurl') . '/' . $file->s . '/' . $file->fname,
			"file_path_hash" => $file->s,
			"file_extension" => self::getFileExtension($file->fname)
		];
	}

	/**
	 * Получить безопасное название файла
	 *
	 * @param string $filename – Исходное имя файла
	 * @return string – Безопасное имя файла
	 */
	public static function sanitizeFileName(string $filename) {
		// Список запрещенных символов
		// https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
		$dangerous = array("/", "\\", "?", "%");

		return str_replace($dangerous, "", urldecode($filename));
	}

	public static function isAllowedFileSize($filePath) {
		return filesize($filePath) <= App::config('files.max_size');
	}

	/**
	 * Проверить название файла на допустимые символы
	 *
	 * @param string $fileName название файла
	 * @param string|false $lang язык сайта
	 * @return bool
	 */
	public static function isAllowedFileName($fileName, $lang = false) {
		if (!$lang) {
			$lang = \Translations::getLang();
		}

		return !($lang == \Translations::EN_LANG && preg_match("/[а-яё]/iu", $fileName));
	}

	public static function getFileExtension($fileName) {
		$pointPosition = mb_strripos($fileName, '.');
		if ($pointPosition === false) {
			return '';
		}
		$extension = mb_substr($fileName, $pointPosition);
		return mb_strtolower($extension);
	}

	public static function replaceFileExtension($fileName, $extension) {
		$pointPosition = mb_strripos($fileName, '.');
		if ($pointPosition === false) {
			return $fileName . $extension;
		} else {
			return mb_substr($fileName, 0, $pointPosition) . $extension;
		}
	}

	/**
	 * Удаление файлов, не привязанных к сущностям.
	 * @throws Exception
	 */
	public static function clearNotLinked() {
		$unlinkedFiles = File::whereNull(File::FIELD_ENTITY_ID)
			->where(File::FIELD_TIME, "<", time() - Helper::ONE_DAY)
			->get();

		/** @var File $file */
		foreach ($unlinkedFiles as $file) {
			$file->safeDelete();
		}
	}

	/**
	 * Поиск файлов, которые не привязаны к сущностям. Для логирования.
	 *
	 * @return bool
	 */
	public static function checkNotLinked() {
		$unlinkedFiles = self::getNotLinkedQuery()
			->select([
				"file." . File::FIELD_ID,
				"file." . File::FIELD_ENTITY_TYPE,
			])
			->limit(100)
			->get();

		if ($unlinkedFiles->isEmpty()) {
			return true;
		}

		$filesData = "";

		foreach ($unlinkedFiles as $file) {
			$filesData .= $file->{ File::FIELD_ID } . " - " .
				$file->{ File::FIELD_ENTITY_TYPE } . PHP_EOL;
		}

		$data = "Обнаружены файлы, не привязанные к сущностям. Необходимо проверить корректность удаления файлов при удалении сущностей. ";
		$data .= "Ниже идентификаторы этих файлов (с ограничением в 100 файлов)." . PHP_EOL . $filesData;

		Log::daily($data, "error");

		return false;
	}

	/**
	 * Удаление файлов, которые не привязаны к сущностям.
	 *
	 * @param bool $debug Отображать информацию о ходе выполнения?
	 * @return bool
	 */
	public static function deleteNotLinked($debug = false) {
		$processed = 0;
		$total = self::getNotLinkedQuery()->count();
		$debug && print "Найдено файлов: " . $total . "\n";

		while (true) {
			$unlinkedFiles = self::getNotLinkedQuery()
				->select("file.*")
				->limit(1000)
				->get();

			if ($unlinkedFiles->isEmpty()) {
				break;
			}

			foreach ($unlinkedFiles as $file) {
				$fileModel = new File();

				File::unguarded(function () use (&$fileModel, $file) {
					$fileModel->fill((array) $file);
				});

				$fileModel->exists = true;

				$isDeleted = $fileModel->safeDelete();

				if (!$isDeleted) {
					Log::daily("Не удалось удалить файл " . $fileModel->FID . " (" . __METHOD__ . ")", "error");
					$debug && print "\n";
					return false;
				}

				$processed++;
				$debug && print "\rУдалено файлов: " . round2($processed / $total * 100) . "%";
			}
		}

		$debug && print "\n";

		return true;
	}

	/**
	 * Получение Query Builder для поиска файлов, которые не привязаны к сущностям.
	 *
	 * @return Illuminate\Database\Query\Builder
	 */
	private static function getNotLinkedQuery() {
		return DB::table(File::TABLE_NAME . " AS file")
			->leftJoin(Kwork::TABLE_NAME . " AS kwork", function ($join) {
				$join
					->on("kwork." . Kwork::FIELD_PID, "=", "file." . File::FIELD_ENTITY_ID)
					->whereIn("file." . File::FIELD_ENTITY_TYPE, [
						File::ENTITY_TYPE_KWORK_DESCRIPTION,
						File::ENTITY_TYPE_KWORK_INSTRUCTION,
						File::ENTITY_TYPE_DEMO_FILE,
					]);
			})
			->leftJoin(Inbox::TABLE_NAME . " AS inbox", function ($join) {
				$join
					->on("inbox." . Inbox::FIELD_MESSAGE_ID, "=", "file." . File::FIELD_ENTITY_ID)
					->whereIn("file." . File::FIELD_ENTITY_TYPE, [
						File::ENTITY_TYPE_CONVERSATION,
						File::ENTITY_TYPE_SUPPORT,
					]);
			})
			->leftJoin(Track::TABLE_NAME . " AS track", function ($join) {
				$join
					->on("track." . Track::FIELD_ID, "=", "file." . File::FIELD_ENTITY_ID)
					->where("file." . File::FIELD_ENTITY_TYPE, File::ENTITY_TYPE_TRACK);
			})
			->leftJoin(TrackDraft::TABLE_NAME . " AS track_draft", function ($join) {
				$join
					->on("track_draft." . TrackDraft::FIELD_ID, "=", "file." . File::FIELD_ENTITY_ID)
					->where("file." . File::FIELD_ENTITY_TYPE, File::ENTITY_TYPE_TRACK_DRAFT);
			})
			->leftJoin(InboxDraft::TABLE_NAME . " AS inbox_draft", function ($join) {
				$join
					->on("inbox_draft." . InboxDraft::FIELD_ID, "=", "file." . File::FIELD_ENTITY_ID)
					->where("file." . File::FIELD_ENTITY_TYPE, File::ENTITY_TYPE_INBOX_DRAFT);
			})
			->leftJoin(Want::TABLE_NAME . " AS want", function ($join) {
				$join
					->on("want." . Want::FIELD_ID, "=", "file." . File::FIELD_ENTITY_ID)
					->where("file." . File::FIELD_ENTITY_TYPE, File::ENTITY_TYPE_WANT);
			})
			->leftJoin(Contest::TABLE_NAME . " AS contest", function ($join) {
				$join
					->on("contest." . Contest::FIELD_ID, "=", "file." . File::FIELD_ENTITY_ID)
					->whereIn("file." . File::FIELD_ENTITY_TYPE, [
						File::ENTITY_TYPE_CONTEST,
						File::ENTITY_TYPE_CONTEST_TEXT,
					]);
			})
			->leftJoin(SupportTrainingMessage::TABLE_NAME . " AS train_msg", function ($join) {
				$join
					->on("train_msg." . SupportTrainingMessage::FIELD_ID, "=", "file." . File::FIELD_ENTITY_ID)
					->where("file." . File::FIELD_ENTITY_TYPE, File::ENTITY_TYPE_TRAINING_MESSAGE);
			})
			->leftJoin(Contest2018Idea::TABLE_NAME . " AS contest_idea", function ($join) {
				$join
					->on("contest_idea." . Contest2018Idea::FIELD_ID, "=", "file." . File::FIELD_ENTITY_ID)
					->where("file." . File::FIELD_ENTITY_TYPE, File::ENTITY_TYPE_CONTEST_2018_IDEA);
			})
			->whereNotNull("file." . File::FIELD_ENTITY_ID)
			->whereNull("kwork." . Kwork::FIELD_PID)
			->whereNull("inbox." . Inbox::FIELD_MESSAGE_ID)
			->whereNull("track." . Track::FIELD_ID)
			->whereNull("track_draft." . TrackDraft::FIELD_ID)
			->whereNull("want." . Want::FIELD_ID)
			->whereNull("contest." . Contest::FIELD_ID)
			->whereNull("train_msg." . SupportTrainingMessage::FIELD_ID)
			->whereNull("contest_idea." . Contest2018Idea::FIELD_ID)
			->whereRaw("FROM_UNIXTIME(file.time) < (NOW() - INTERVAL 24 HOUR)");
	}

	/**
	 * Получить список файлов сущности
	 *
	 * @param int    $entityId   id сущности
	 * @param string|array $entityType тип сущности файла (см. константы ENTITY_TYPE_*) или массив типов сущностей
	 * @param string|null $status статус файла
	 *
	 * @return array|bool|string
	 */
	public static function getEntityFiles($entityId, $entityType, $status = null) {
		if ($entityId <= 0) {
			return false;
		}

		if (!is_array($entityType)) {
			$entityType = [$entityType];
		}

		foreach ($entityType as $type) {
			if (!EntityManager::inAllowedTypes($type)) {
				return false;
			}
		}

		$params = [
			"entityId" => (int) $entityId,
		];

		$statusWhere = "";
		if (!is_null($status) && in_array($status, [File::STATUS_ACTIVE, File::STATUS_DELETED])) {
			$statusWhere = " AND ".File::FIELD_STATUS." = :status";
			$params["status"] = $status;
		}

		$sql = "SELECT * FROM files WHERE entity_id = :entityId AND entity_type IN (" . App::pdo()->arrayToStrParams($entityType, $params) . ")" . $statusWhere;

		return App::pdo()->getList($sql, $params);
	}

	/**
	 * Привязать добавленные файлы к сущности
	 * @param int $entityId id сущности
	 * @param array $files Массив id файлов
	 * @param string $type Тип файлов
	 * @param int $limit Максимальное количество файлов
	 * @return boolean true - файлы успешно сохранены, иначе false
	 */
	public static function saveFiles($entityId, $files, $type, $limit = 0) {
		$entityId = (int) $entityId;
		if (empty($files) || !$type || $entityId <= 0) {
			return false;
		}

		$oldFilesCount = self::getEntityFilesCount($entityId, $type);

		$newFilesCount = count($files);
		$maxFilesCount = $limit ?: App::config('files.max_count');
		$limitFilesCount = min($newFilesCount, $maxFilesCount - $oldFilesCount);
		if ($limitFilesCount <= 0) {
			return false;
		}
		$files = array_splice($files, 0, $limitFilesCount);

		return FileManager::fileLink($files, $entityId, $type);
	}

	/**
	 * Удаление файлов определенных типов сущности
	 *
	 * @param int      $entityId id сущности
	 * @param int[]    $filesIds Массив id файлов
	 * @param string[] $types    Массив типов файлов
	 *
	 * @return boolean true - файлы успешно удалены, в противном случае false
	 * @throws Exception
	 */
	public static function deleteEntityFilesByType(int $entityId, array $filesIds, array $types) {
		$entityId = (int) $entityId;
		if (empty($filesIds) || empty($types) || $entityId <= 0) {
			return false;
		}

		foreach ($types as $type) {
			if (!EntityManager::isCurrentUserCanDeleteEntityByType($entityId, $type)) {
				return false;
			}
		}

		$files = File::where(File::FIELD_ENTITY_ID, $entityId)
			->whereIn(File::FIELD_ID, $filesIds)
			->whereIn(File::FIELD_ENTITY_TYPE, $types)
			->get();

		foreach ($files as $file) {
			$file->safeDelete();
		}

		return true;
	}

	/**
	 * Получить путь до файла по имени файла
	 * @param string $fileName Имя файла. Минимум 5 символов
	 * @return string Путь до файла
	 */
	public static function getPathByName($fileName) {
		if (strlen($fileName) < 5) {
			return $fileName;
		}

		$parts = str_split($fileName);
		$return = "{$parts[0]}{$parts[1]}/{$parts[2]}{$parts[3]}/" . implode('', array_slice($parts, 4));
		return $return;
	}

	/**
	 * Проверить и создать путь до папки если его нет
	 * @param string $fullFilePath Полный путь до папки
	 * @return bool true - успешно, false - ошибка (указан относительнный путь)
	 */
	public static function checkDirPath($fullFilePath) {
		$dirPath = dirname($fullFilePath);
		if (!file_exists($dirPath)) {
			self::getDirAnyway($dirPath);
		}
		return true;
	}

	/**
	 * Возвращает путь к запрашиваемой папке.
	 * Если её нет, пытается создать с указаннами правами и вернуть путь.
	 * Если папки нет и создать её не удалось, то метод выбросит стандартное исключение.
	 *
	 * @param string $dir
	 *   полный путь к папке.
	 * @param int $mode
	 *   см. параметр $mode для {@see mkdir()}, для случая, когда папки ещё нет и её надо создать.
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function getDirAnyway(string $dir, $mode = 0775) {
		if (is_dir($dir)) {
			return $dir;
		}

		// Папки могут создаваться в:
		$baseDirs = [
			APP_ROOT . '/files',
			DOCUMENT_ROOT . '/files',
			DOCUMENT_ROOT . '/images',
			DOCUMENT_ROOT . '/temporary',
			App::config('logDir'),
			App::config('pdir'),
			App::config('membersprofilepicdir'),
			App::config('portfoliodir'),
			App::config('temp_image_dir'),
			App::config('demofiledir'),
			App::config('froaladir'),
			App::config('file_miniature_dir'),
		];
		
		// Нам надо:
		// проверить весь путь, начиная от корневой (из $baseDirs), включая её
		// если какой-нибудь папки, включая корневую, нет, то создать и задать права
		foreach ($baseDirs as $baseDir) {
			// Если запрашиваемая папка - текущая корневая или должна быть внутри её
			if (FALSE !== strpos($dir, $baseDir)) {
				// то проверяем и создаём (пытаемся) саму корневую
				if (!is_dir($baseDir)) {
					self::mkDir($baseDir, $mode);
				}
				// выделяем путь относительно корневой папки
				$subDirPath = str_replace($baseDir, '', $dir);
				$subDirPath = ltrim($subDirPath, '/');
				// и если такой путь есть - то запрашивается внутренняя для текущей корневой папка
				if (!empty($subDirPath)) {
					// а значит надо пройти по всему относительному пути и попытаться создать все папки
					$subDirs = explode('/', $subDirPath);
					$childDir = $baseDir;
					foreach ($subDirs as $subDirName) {
						$childDir .= "/$subDirName";
						self::mkDir($childDir, $mode);
					}
				}
				return $dir;
			}
		}
		// Если мы тут, значит запрашивается папка не из тех, что мы создаём.
		// Поэтому просто пробуем создать (если её нет) и вернуть.
		self::mkDir($dir, $mode);
		return $dir;
	}

	/**
	 * Пытается создать папку и задать для неё права.
	 * Не проверяет папки в пути к создаваемой.
	 * Если надо более уверенное создание папки (включая все папки в пути к ней),
	 * то надо использовать {@see FileManager::getDirAnyway()}.
	 *
	 * @param string $dir
	 *   полный путь к создаваемой папке.
	 * @param int $mode
	 *   см. параметр $mode для {@see mkdir()}
	 *
	 * @throws Exception
	 */
	public static function mkDir(string $dir, int $mode = 0775) {
		// Чтобы точно установить нужные права, мы сначала создаём папку
		// с самыми широкими правами (777, который по умолчанию ставит mkdir)
		// а потом ужесточаем их с помощью chmod.
		// Папка может уже существовать, в этом случае просто ставим ей права.
		// Если не существует, то пытаемся её создать.
		// Если не получилось создать, то выбрасываем исключение, поскольку это ЧП.
		if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
			throw new Exception("Не удалось создать папку $dir");
		}
		// Возможна ситуация, когда папка существует и у неё более жесткие права,
		// чем мы пытаемся задать - новые могут и не установиться.
		try {
			chmod($dir, $mode);
		} catch (Exception $exception) {
			//игнорим ворнинги
		}
	}

	/**
	 * Получить список файлов нескольких сообщений
	 *
	 * @param array $messageIds ID сообщений
	 *
	 * @return array|false
	 */
	public static function getConversationFiles(array $messageIds) {
		return self::getEntitiesFiles($messageIds, self::ENTITY_TYPE_CONVERSATION);
	}

	/**
	 * Получить список файлов нескольких сущностей
	 *
	 * @param array  $entityIds  ID сущностей
	 * @param string $entityType тип сущностей
	 *
	 * @return array|false
	 */
	public static function getEntitiesFiles(array $entityIds, $entityType) {
		$params = [
			'entityType' => $entityType,
		];

		$sql = "SELECT 
                    *
                FROM
                    files
                WHERE
                    entity_id IN (" . App::pdo()->arrayToStrParams($entityIds, $params) . ")
                    AND entity_type = :entityType
                ORDER BY FID ASC";
		$arFiles = App::pdo()->fetchAllNameByColumn($sql, 0, $params);

		return $arFiles;
	}

	/**
	 * Передать все файлы одной сущности другой
	 * @param $fromId
	 * @param $fromType
	 * @param $toId
	 * @param $toType
	 * @return mixed
	 */
	public static function transfer($fromId, $fromType, $toId, $toType) {
		$toParams = [
			self::FIELD_ENTITY_ID => $toId,
			self::FIELD_ENTITY_TYPE => $toType
		];
		$fromParams = [
			'entityId' => $fromId,
			'entityType' => $fromType
		];
		return App::pdo()->update(self::TABLE_NAME, $toParams, self::FIELD_ENTITY_ID . " = :entityId AND " . self::FIELD_ENTITY_TYPE . " = :entityType", $fromParams);
	}

	/**
	 * Получение загруженного файла с проверкой прав
	 *
	 * @param string $path Путь к файлу в файловой системе, также известен как "s"
	 * @throws Exception Обязательно необходимо обработать
	 */
	public static function getUploadedFile($path) {
		$actor = UserManager::getCurrentUser();

		$files = File::where(File::FIELD_S, $path)
			->get();

		if (!$files->count()) {
			throw new Exception("Файл не найден в базе");
		}

		// отдавать будем первый найденный файл
		$file = $files->first();

		$trackResult = false;
		$trackIds = [];
		$conversationId = null;

        foreach ($files as $row) {
            if ($row->entity_type == self::ENTITY_TYPE_TRACK) {
                $trackIds[] = $row->entity_id;
            } elseif ($row->entity_type == self::ENTITY_TYPE_CONVERSATION) {
                $conversationId = $row->entity_id;
			} elseif ($row->entity_type == self::ENTITY_TYPE_TRACK_DRAFT) {
				throw new Exception("Доступ к файлу черновика трека запрещен");
			}
		}

		// Проверка прав доступа к файлу
		if (!UserManager::isModer() && !AdminManager::canSupport()) {
			// файл в треке
            if (!empty($trackIds)) {
				if (empty($actor)) {
					throw new Exception("Доступ к файлу трека неавторизованным пользователям запрещен");
				}

				$sql = "SELECT o.worker_id as worker_id, o.USERID as payer_id
						FROM track t
						JOIN orders o ON o.OID = t.OID
						WHERE t.MID IN (".implode(",", $trackIds).")";

				$result = App::pdo()->fetchAll($sql);
                $orderUsers = array_merge(array_column($result, 'worker_id'), array_column($result, 'payer_id'));

				if (!in_array($actor->id, array_values($orderUsers))) {
					throw new Exception("Доступ к файлу трека запрещен пользователям не участвующим в заказе запрещен");
				}

				$trackResult = true;
			}
			// Файл в личной переписке
            if (!$trackResult && !is_null($conversationId)) {
            	// Список пользователей, которым разрешены файлы в переписке
            	$allowUsers = [];
				$inbox = Inbox::with("support")->find($conversationId);
				if ($inbox) {
					$allowUsers[] = $inbox->MSGFROM;
					$allowUsers[] = $inbox->MSGTO;
					if ($inbox->support && $inbox->support->guest_id) {
						$allowUsers[] = $inbox->support->guest_id;
					}
				}

				// Переписка гостя
				if (!$actor && $inbox->MSGFROM == 0 && $inbox->support && $inbox->support->guest_id) {
					$token = \Support\SupportManager::getGuestToken();
					if (!$token) {
						throw new Exception("Доступ к файлу сообщения в саппорт без токена");
					}
					$guestId = \Support\GuestsManager::getIdByToken($token);
					if ($guestId != $inbox->support->guest_id) {
						throw new Exception("Файл от другого гостя в саппорте");
					}
				} elseif (empty($actor) || !in_array($actor->id, $allowUsers)) {
					throw new Exception("Доступ к файлу в переписке запрещен");
				}
			}
		}

		self::downloadFile($file);
	}

	/**
	 * Отдача файла на загрузку браузеру
	 *
	 * @param File $file файл на отдачу
	 * @throws Exception
	 * @return void
	 */
	private static function downloadFile(File $file) {
		try {
			$request = new \Symfony\Component\HttpFoundation\Request();
			$response = $file->getFileHandler()->getResponse($request, $file->fname);

			$response->send();
		} catch (Exception $e) {
			throw new Exception("Файл не найден");
		}
	}


	/**
	 * Залогировать процесс загрузки файла
	 * @param string $data Данные для логирования
	 */
	public static function logUploadProcess($data) {
		Log::daily($data, self::UPLOAD_LOG_FILENAME);
	}

	/**
	 * Перенос локальных файлов в Amazon s3
	 *
	 * @param int $filesToUpload сколько файлов переносить
	 * @param int? $cnt всего потоков (например: 4)
	 * @param int? $num текущий поток (например: 1-4)
	 * @param int $minFileId минимальный идентификатор файлов
	 * @param int $maxFileId максимальный идентификатор файлов
	 * @return void
	 */
	public static function uploadLocalFilesToAmazon(int $filesToUpload = 10000, ?int $cnt = 0, ?int $num = 0, int $minFileId = 0, int $maxFileId = 0) {
		$processed = 0;
		$uploadedFiles = 0;
		$errors = 0;
		$localNotFound = 0;

		$query = File::where(File::FIELD_STORAGE, File::STORAGE_LOCAL)
			->when($minFileId > 0, function(\Illuminate\Database\Eloquent\Builder $query) use ($minFileId) {
				$query->where(File::FIELD_ID, ">=", $minFileId);
				print "Минимальный идентификатор файлов: $minFileId\n";
			})
			->when($maxFileId > 0, function(\Illuminate\Database\Eloquent\Builder $query) use ($maxFileId) {
				$query->where(File::FIELD_ID, "<=", $maxFileId);
				print "Максимальный идентификатор файлов: $maxFileId\n";
			})
			->whereIn(File::FIELD_ENTITY_TYPE, File::AMAZON_S3_ENTITIES)
			->where(File::FIELD_STATUS, File::STATUS_ACTIVE);

		if ($cnt > 0 && $num > 0) {
			print "Многопоточный режим: $num/$cnt\n";

			$query->whereRaw(File::FIELD_ID." % ? = ?", [$cnt, $num - 1]);
		}

		$count = $query->count();
		$total = min($filesToUpload, $count);

		print "Всего локальных активных файлов: $count\n";
		print "Файлов к переносу в Amazon: $total\n";

		$fileIdsWithErrors = [];
		while ($processed < $filesToUpload) {

			$files = $query->whereNotIn(File::FIELD_ID, $fileIdsWithErrors)
				->orderBy(File::FIELD_ID)
				->limit(5000)
				->get();

			if ($files->isEmpty()) {
				break;
			}

			/** @var File $file */
			foreach ($files as $file) {

				// если существует локальный физический файл
				if (file_exists($file->path)) {
					try {
						$file->storage = File::STORAGE_S3;

						// сохраним файл через хендлер в Амазон
						$file->getFileHandler()->store();

						// если загрузка успешна (иначе - исключение) - обновим запись в таблице files
						if ($file->save()) {
							$uploadedFiles++;

							$file->createCache();
						}

					} catch (\Exception $e) {
						$errors++;
						Log::daily("Ошибка переноса локального файла [FID={$file->FID}] в Amazon S3: " . $e->getMessage(), "error");

						// чтобы файл не попадал в повторные выборки
						$fileIdsWithErrors[] = $file->FID;
					} finally {
						$file->destroyFileHandler();
					}
				} else {

					// если не существует локального физического файла - мягко удалим файл
					$file->status = File::STATUS_DELETED;
					if ($file->save()) {
						$localNotFound++;
					}
				}

				echo "\r" . round2(++$processed / $total * 100) . "% [$processed/$total]   ";

				if ($processed >= $filesToUpload) {
					break;
				}
			}
		}

		print "\nЗагружено файлов: $uploadedFiles\n";
		print "Мягко удалено файлов (не существует файла на диске): $localNotFound\n";
		print "Ошибок: $errors\n";
	}

	/**
	 * Получить количество файлов, привязанных к сущности
	 *
	 * @param int    $entityId   Идентификатор сущности
	 * @param string $entityType Тип сущности
	 * @return int
	 */
	public static function getEntityFilesCount(int $entityId, string $entityType) {
		return File::where(File::FIELD_ENTITY_ID, $entityId)
			->where(File::FIELD_ENTITY_TYPE, $entityType)
			->count();
	}

	/**
	 * Проверка, можно ли удалить старый файл с entity_type = track.
	 *
	 * @param array $file Параметры файла
	 *   - string s Относительный путь
	 *   - int user_id Id владельца
	 *   - int track_id Id трека
	 *   - int order_id Id заказа
	 *   - int order_status Статус заказа
	 *   - int worker_id Id продавца
	 *   - int entity_type - Тип сущности
	 *   - int entity_id - ID сущности, к которой привязан файл
	 *
	 * @return bool
	 */
	public static function canDeleteOldFileTrack(array $file) : bool {
		// Кэш [orderId => lastCheckId]
		static $lastCheckIds = [];

		if ($file["entity_type"] == self::ENTITY_TYPE_TRACK) {
			$file["track_id"] = $file["entity_id"];
		}

		if (empty($file["s"]) ||
			empty($file["user_id"]) ||
			empty($file["track_id"]) ||
			empty($file["order_id"]) ||
			empty($file["order_status"]) ||
			empty($file["worker_id"])
		) {
			return false;
		}

		// Неоплаченные заказы
		if ($file["order_status"] == \OrderManager::STATUS_CANCEL) {
			return true;

			// Оплаченные заказы
		} elseif ($file["order_status"] == \OrderManager::STATUS_DONE) {

			// Файлы продавца
			if ($file["user_id"] == $file["worker_id"]) {
				if ($file["track_type"] == Type::WORKER_INPROGRESS_CHECK && $file[Order::FIELD_HAS_STAGES]) {
					// В поэтапных заказах нельзя сдачу работы удалять
					return false;
				}

				// Последнее сообщение в заказе от продавца о сдаче работы на проверку
				$lastCheckId = $lastCheckIds[$file["order_id"]];
				if (!$lastCheckId) {
					$lastCheckId = Track::query()
						->where(Track::FIELD_TYPE, Type::WORKER_INPROGRESS_CHECK)
						->where(Track::FIELD_USER_ID, $file["user_id"])
						->where(Track::FIELD_ORDER_ID, $file["order_id"])
						->orderByDesc(Track::FIELD_ID)
						->value(Track::FIELD_ID);

					$lastCheckIds[$file["order_id"]] = $lastCheckId;
				}

				return $file["track_id"] < $lastCheckId;

				// Файлы покупателя
			} else {
				return true;
			}
		}

		return false;
	}

	/**
	 * Генерирует путь к новому файлу, и создаёт директорию до него
	 *
	 * @param bool $checkDir - Нужно ли проверять/создавать директорию до файла
	 * @return string
	 */
	public static function generateFilePath(bool $checkDir = true): string {
		$fileName = mb_substr(md5(generateCode(16) . time()), 0, 16);
		$filePath = self::getPathByName($fileName);
		if ($checkDir) {
			self::checkDirPath(App::config("uploadeddir") . $filePath);
		}
		return $filePath;
	}
}
