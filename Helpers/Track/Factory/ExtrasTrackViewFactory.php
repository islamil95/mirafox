<?php


namespace Track\Factory;


use Core\Traits\AuthTrait;
use Core\Traits\SingletonTrait;
use Model\Track;
use Track\Type;
use Track\View\EmptyView;
use Track\View\Extra\DeleteExtraView;
use Track\View\Extra\ExtraView;
use Track\View\IView;

/**
 * Фабрика для обработки заказа дополнительный опций
 *
 * Class ExtrasTrackViewFactory
 * @package Track\Factory
 */
class ExtrasTrackViewFactory implements ITrackViewFactory {

	use AuthTrait, SingletonTrait;

	/**
	 * @inheritdoc
	 */
	public function getView(Track $track): IView {
		switch ($track->type) {
			case Type::EXTRA:
				return new ExtraView($track);

			case Type::DELETE_EXTRA:
				return new DeleteExtraView($track);

			default:
				return new EmptyView($track);
		}
	}
}