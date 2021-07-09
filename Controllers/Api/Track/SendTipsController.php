<?php


namespace Controllers\Api\Track;


use Controllers\Api\AbstractApiController;
use Core\Exception\SimpleJsonException;
use Model\Order;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SendTipsController
 * @package Controllers\Api\Track
 */
class SendTipsController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		$orderId = $request->request->getInt("orderId");
		$sum = $request->request->getInt("sum");
		$message = (string)$request->request->get("tips_message");
		if (!$orderId || !$sum) {
			throw new SimpleJsonException(\Translations::t("Некорректные параметры запроса"));
		}

		$user = $this->getUser();
		$funds = $user->{\UserManager::FIELD_FUNDS}
			+ $user->{\UserManager::FIELD_BFUNDS}
			+ $user->{\UserManager::FIELD_BILL_FUNDS}
			+ $user->{\UserManager::FIELD_CARD_FUNDS};

		$orderCurrencyId = Order::where(Order::FIELD_OID, $orderId)
			->value(Order::FIELD_CURRENCY_ID);
		$payerCurrencyId = \Translations::getCurrencyIdByLang($user->lang);

		$convertedPayerSum = $sum;
		if ($orderCurrencyId != $payerCurrencyId) {
			$convertedPayerSum = \Currency\CurrencyExchanger::getInstance()->convertByCurrencyId(
				$convertedPayerSum,
				$orderCurrencyId,
				$payerCurrencyId
			);
		}

		if ($funds < $convertedPayerSum) {
			// Тут будет создана операция пополнения и выброшен эксепшен с требованием пополнить
			\OperationManager::handleBalanceDeficit($convertedPayerSum - $funds, $this->getCurrentUserId());
		}

		return [
			"success" => \Tips::sendTips($orderId, $sum, $message),
			"result" => "success",
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "Track.api_sendTips";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}