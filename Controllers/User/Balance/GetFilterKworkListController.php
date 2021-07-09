<?php


namespace Controllers\User\Balance;

use Controllers\BaseController;

use Symfony\Component\HttpFoundation\Request;
use \Core\Traits\AuthTrait;


/**
 * Получение названий кворков согласно выбранному в фильтре типу операции, необходимо для фильтра кворков в операциях
 *
 * Class GetFilterKworkListController
 * @package Controllers\Users\Balance
 */
class GetFilterKworkListController extends BaseController {

	use AuthTrait;

	public function __invoke(Request $request)
	{
		$user = $this->getUserModel();
		$ordersList = \Model\Kwork::where('rev', '>', 0)
			->where('USERID', $user->USERID)
			->get(['PID as kwork_id', 'gtitle as kwork_title'])
			->toArray();

		$kworkNameList = [];
		foreach ($ordersList as $row) {
			$kworkNameList[$row['kwork_id']] = $row['kwork_title'];
		}

        return $this->success($kworkNameList);
	}

}