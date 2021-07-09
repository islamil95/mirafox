<?php

namespace Controllers\Track;

use Controllers\Track\Strategy\GetFullOrderStrategy;
use Controllers\Track\Strategy\IGetOrderStrategy;
use Model\Order;
use Strategy\Track\GetRenderParametersStrategy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Подгрузка и рендер скрытых треков
 *
 * Class TrackController
 * @package Controllers\Track
 */
class ShowHiddenTracksController extends AbstractTrackController {

	/**
	 * Логика потомка
	 *
	 * @param Request $request HTTP запрос
	 * @param Order $order заказ
	 * @return Response
	 */
	protected function processRequest(Request $request, Order $order)
	{
		$parameters = (new GetRenderParametersStrategy($order))->get();
		return $this->render('track/hidden_tracks', $parameters);
	}

	/**
	 * Получить стратегию получения заказа
	 *
	 * @param int $orderId идентификатор заказа
	 * @return IGetOrderStrategy
	 */
	protected function getOrderStrategy(int $orderId): IGetOrderStrategy
	{
		return new GetFullOrderStrategy($orderId);
	}
}