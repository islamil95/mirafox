<?php


namespace Controllers\Kwork;


use App;
use Controllers\BaseController;
use Core\Exception\RedirectException;
use Core\Exception\UnauthorizedException;
use Exception\KworkSaveValidatorException;
use Helpers\KworkSaveManager;
use KworkManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SaveKworkController extends BaseController {

	public function __invoke(Request $request) {
		$actor = \UserManager::getCurrentUser();
		if (!$actor) {
			throw new UnauthorizedException();
		}
		$kworkId = (int)$request->get("id");

		if ($kworkId) {
			$kwork = new KworkManager($kworkId);
			$mode = KworkSaveManager::MODE_EDIT;
		} else {
			$kwork = new KworkManager();
			$mode = KworkSaveManager::MODE_ADD;
		}

		try {
			$kworkSaveManager = new KworkSaveManager($kwork, $request, $mode);
		} catch (KworkSaveValidatorException $exception) {
			return $this->success([
				"result" => "error",
				"errors" => [
					["text" => $exception->getMessage()],
				],
			]);
		}
		$response = [];
		if ($kworkSaveManager->save() !== false) {
			$response['result'] = 'success';
			$response['redirectUrl'] = App::config('baseurl') . $kworkSaveManager->get('url');
		} else {
			$response['result'] = 'error';
			$response['errors'] = $kworkSaveManager->get('errors');
		}
		return new JsonResponse($response);
	}
}