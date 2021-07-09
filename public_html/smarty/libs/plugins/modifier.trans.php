<?php
/**
* транслит русских букв
*/
function smarty_modifier_trans($text)
{
	if(!$text)
		return "";

	return seo_clean_titles($text);
}
?>