<?php

function smarty_modifier_truncate_url($string, $length = 80, $etc = '...')
{
	$res = strip_tags($string);
	if(mb_strlen($res) > $length){
		$len = $length - mb_strlen($etc);
	}else{
		return $string;
	}

	mb_preg_match_all("/(<a([\s\p{L}\p{Nd}\d_]+)[^>]*>)([\p{L}\p{Nd}\W\s_]*?)<\/a>/u", $string, $matches, PREG_OFFSET_CAPTURE);
	if(!$matches[0])
		return mb_substr($string, 0, $len).$etc;
	$positions = [];
	$positionKoif = 0;
	foreach ($matches[0] as $key=>$item){
		$positions[$key] = $item[1] - $positionKoif;
		$positionKoif = $positionKoif + mb_strlen($item[0]) - mb_strlen($matches[3][$key][0]);
	}
	$isEtc = false;
	$res = mb_substr($res, 0, $len);
	foreach ($positions as $key=>$item){
		if($item >= $len){
			unset($item);
		}else if($len < $item + mb_strlen($matches[3][$key][0])){
			$newLen = $item + mb_strlen($matches[3][$key][0]) - $len;
			$matches[0][$key][0] = mb_str_replace('>'.$matches[3][$key][0], '>'.  mb_substr($matches[3][$key][0], 0, $newLen).$etc, $matches[0][$key][0]);
			$res = mb_substr_replace($res,$matches[0][$key][0],$matches[0][$key][1], $newLen + mb_strlen($etc) + 1);
			$isEtc = true;
		}else{
			$res = mb_substr_replace($res,$matches[0][$key][0],$matches[0][$key][1], mb_strlen($matches[3][$key][0]));
		}
	}
	if(!$isEtc){
		$res .= $etc;
	}
	return $res;
}
?>