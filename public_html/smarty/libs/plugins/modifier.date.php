<?php
/**
* выводит дату в нужном формате
*/
function smarty_modifier_date($date, $format = "j F Y, H:i")
{
	return Helper::date($date, $format);
}