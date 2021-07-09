<?php

namespace Controllers\Want\Worker;

use Controllers\BaseController;
use Core\Traits\Routing\RoutingTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Контроллер проверка предложения на шаблонность
 *
 * Class OffersController
 * @package Controllers\Want
 */
class CheckCloneOfferController extends BaseController {

	use RoutingTrait;

	public function __invoke(Request $request) {
		if ($this->isUserNotAuthenticated() || !$this->getUserId()) {
			return $this->success(["error" => \Translations::t("Необходима авторизация")]);
		}

		$description = (string)$request->request->get("description");
		$wantId = $request->request->get("wantid");
		$result = [];
		if (\OfferManager::isCommentClone($description, $this->getUserId(), $wantId)) {
			$result["error"] = \Translations::t("Вы собираетесь отправить шаблонный текст предложения. Такие отклики очень редко помогают получить заказ и приводят к пустой трате коннектов. В тексте предложения стоит описать свой релевантный опыт, то, как вы поняли задачу, и как собираетесь ее выполнять. Это значительно повысит шанс получить заказ.");
		}

		return $this->success($result);
	}

}