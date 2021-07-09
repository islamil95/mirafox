<?php
/**
* статус заказа
*/
function smarty_modifier_requestStatus($status, $type = 'status_name')
{
	if($type == 'color')
	{
		switch($status)
		{
			case 'active':
			case 'postmoder':
				return 'green';
			case 'new':
				return 'none';
			case 'user_stop':
			case 'stop':
				return 'blue';
			case 'cancel':
				return 'red';
			default:
				return "none";
		}
	}else{
		switch($status)
		{
			case 'active':
			case 'postmoder':
				return Translations::t('активный');
			case 'new':
				return Translations::t('на модерации');
			case 'user_stop':
				return Translations::t('остановлен покупателем');
			case 'stop':
				return Translations::t('остановлен');
			case 'cancel':
				return Translations::t('отклонен модератором');
			case 'delete':
				return Translations::t('удален');
			case 'archived':
				return Translations::t('в архиве');
			default:
				return "";
		}
	}
}
?>