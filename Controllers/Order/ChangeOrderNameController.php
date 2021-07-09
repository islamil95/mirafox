<?php

namespace Controllers\Order;

use Controllers\BaseController;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use \Core\Traits\AuthTrait;


/**
 * Пользовательское изменение названия заказа видное только изменившему
 *
 * Class ChangeOrderNameController
 * @package Controllers\Order
 */
class ChangeOrderNameController extends BaseController
{

    use AuthTrait;

    public function __invoke(Request $request)
    {
        $user = $this->getUserModel();
        $orderId = $request->request->get("order_id", false);
        $name = $request->request->get("name", false);

        $sql = "SELECT 
                    kwork_title, USERID, worker_id 
                FROM 
                    orders
                WHERE 
                    OID = :orderId";

        $result = \App::pdo()->fetch($sql, ["orderId" => $orderId]);

        //Если пользователь не имеет отношния к заказу, значит он пытается нас обмануть
        if ($user->USERID != $result['USERID'] && $user->USERID != $result['worker_id']) {
            return $this->failure();
        }
        $realOrderName = $result["kwork_title"];

        if ($orderId && $name) {
            //Проверяем наличие уникального имени заказа
            $sql = "SELECT 
                        id
                    FROM 
                        order_names 
                    WHERE 
                        order_id = :orderId
                    AND
                        user_id = :user_id";

            $result = \App::pdo()->fetchScalar($sql, ["orderId" => $orderId, "user_id" => $user->USERID]);
            //Если есть, то обновляем уже существующее имя
            if ($result) {
                //Если мы меняем название на оригинальное, то удаляем запись.
                if ($name == $realOrderName) {
                    $sql = "DELETE FROM
                            order_names
                        WHERE
                            ID = :onId";
                    \App::pdo()->execute($sql, ["onId" => $result]);
                } else {
                    $sql = "UPDATE 
                            order_names
                        SET 
                            order_name = :orderName 
                        WHERE 
                            ID = :onId";
                    \App::pdo()->execute($sql, ["orderName" => $name, "onId" => $result]);
                }
            } else {
                //Если для заказа от данного юзера нет имени, добавялем новую запись
                $sql = "INSERT INTO
                            order_names(order_id, user_id, order_name)
                        VALUES
                            (:orderId, :userId, :order_name)";
                \App::pdo()->execute($sql, ["orderId" => $orderId, "order_name" => $name, "userId" => $user->USERID]);
            }
        } else {
            return $this->failure();
        }

        return $this->success("1");
    }
}