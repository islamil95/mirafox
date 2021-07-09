<?php


namespace Order;


class MyOrdersFactory {
	static public function getMyOrders(string $userType): MyOrders {
		if (\UserManager::TYPE_WORKER == $userType) {
			return new MyOrders();
		}
		else {
			return new MyOrdersPayer();
		}
	}
}
