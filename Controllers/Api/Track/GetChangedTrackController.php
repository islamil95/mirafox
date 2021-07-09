<?php


namespace Controllers\Api\Track;


use Controllers\Api\AbstractApiController;
use Model\Order;
use Model\Track;
use Symfony\Component\HttpFoundation\Request;
use Track\Factory\TrackViewFactory;
use Track\Type;

/**
 * Class GetChangedTrackController
 * @package Controllers\Api\Track
 */
class GetChangedTrackController extends AbstractApiController {

	/**
	 * @inheritdoc
	 */
	protected function callMethod(Request $request) {
		$orderId = $request->request->getInt("orderId");
		$trackId = $request->request->getInt("trackId");
		$action = $request->request->get("action");

		if (empty($orderId) || empty($trackId) || empty($action) || $this->isUserNotAuthenticated()) {
			return [
				"success" => false,
			];
		}

		try {
			$order = Order::findOrFail($orderId);
			// Если запись удалена, нужно возврашать "success" => true, а не ошибку из блока catch
			$track = Track::find($trackId);
			if (empty($track)) {
				return [
					"success" => true,
					"trackHtml" => "",
					"filesBlockHtml" => "",
				];
			}
			// При удалении файла надо обновить (либо вообще убрать) блок файлов заказа в сайдбаре.
			// Это если - убрать.
			$filesBlockHtml = "";

			if ($order->getFilesCount()) {
				$filesBlockHtml = $this->renderView("track/sidebar_files", ["order" => $order]);
			}
			if ($this->isWorker()) {
				$order->initFirstRework();
				$order->initThresholdMessage();
			}

			// Особый момент - если такого трека нет (может удалён?) вернем пусто,
			// а клиентский код сам уже там разберется.
			// Остальные исключения идут штатно.
			try {
				if ("remove" == $action) {
					throw new \Exception(\Translations::t("Запись удалена"));
				}

				// Если изменение - это удаление файла из текстового сообщения,
				// то надо проверить само сообщение на содержательность и удалить, если что.
				if (\TrackManager::CHANGE_FILE_REMOVED == $action
					&& Type::TEXT == $track->type
					&& empty($track->message)
					&& empty($track->files)) {
					// Если у сообщения нет текста и нет файлов, то оно пустое
					// (это ведь обычное текстовое сообщение, у него больше ничего не может быть).
					$track->delete();

					// Скачем на возврат пустого html трека.
					throw new \Exception(\Translations::t("Трек удален"));
				}
			} catch (\Exception $e) {
				return [
					"success" => true,
					"trackHtml" => "",
					"filesBlockHtml" => $filesBlockHtml,
				];
			}

			$trackView = TrackViewFactory::getInstance()->getView($track);

			return [
				"success" => true,
				"trackHtml" => $trackView->render(),
				"filesBlockHtml" => $filesBlockHtml,
			];
		} catch (\Exception $e) {
			// @todo: тут бы исключение и записать куда-нибудь, для техподдержек.
			return [
				"success" => false,
				"message" => \Translations::t("Ошибка получения трека."),
			];
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function getMethodName(): string {
		return "Track.api_getChangedTrack";
	}

	/**
	 * @inheritdoc
	 */
	protected function isParametersNotValid(Request $request): bool {
		return false;
	}
}