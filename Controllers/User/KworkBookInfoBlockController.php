<?php declare(strict_types=1);

namespace Controllers\User;

use Controllers\BaseController;
use Core\Exception\UnauthorizedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Translations;

/**
 * Меняет состояние флага Скрывать ли инфо-блок "Скачайте уроки Как эффективно зарабатывать на Kwork" на странице "Мои кворки" #8485
 *
 * Class KworkBookInfoBlockController
 * @package Controllers\User
 */
final class KworkBookInfoBlockController extends BaseController {
	
	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function __invoke(Request $request): JsonResponse {
		if ($this->isUserNotAuthenticated()) {
			return $this->failure(Translations::t("Пользователь не найден"));
		}

		$validator = $this->validate($request, [
			"is_kwork_book_info_closed" => "required|bool",
		]);
		if ($validator->fails()) {
			return $this->failure($validator->errors());
		}
		
		$userData = $this->getUserModel()->data;
		$userData->is_kwork_book_info_closed = $request->get("is_kwork_book_info_closed");
		$userData->save();

		return $this->success([
			"is_kwork_book_info_closed" => $userData->is_kwork_book_info_closed,
		]);
	}
}