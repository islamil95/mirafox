<?php
/**
* статус трека
*/
function smarty_modifier_trackStatus($type, $info)
{
	$value = TrackManager::getStatusDesc($type, $info);
	return Translations::t($value);
}