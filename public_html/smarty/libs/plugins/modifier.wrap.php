<?php
/*
* урезает строку до длины
*/
function smarty_modifier_wrap($string, $maxLen)
{
	if(!$string || !$maxLen)
		return $string;
	
	$string = html_entity_decode($string, ENT_QUOTES, "UTF-8");
	
	// вырезаю теги
	$string = preg_replace("/[<][^>]+[>]/", " ", $string);
	$string = preg_replace("/[\[][^\]]+[\]]/", " ", $string);
	
	if(mb_strlen($string) > $maxLen)
	{
		$words = explode(" ", $string);
		$string = "";

		foreach($words as $key => $word)
		{
			if($key == 0)
				$string = $word;
			elseif(mb_strlen($string . " " . $word) < $maxLen)
				$string .= " " . $word;
			else
				break;
		}
		
		$string = mb_substr($string, 0, $maxLen - 1) . "…";
	}
	
	return htmlentities($string, ENT_QUOTES, "UTF-8");
}
?>