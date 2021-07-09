<?php
/**
* умножает параметры
*/
function smarty_modifier_userKworkMark($type, $source)
{
    if(!in_array($source, ['img', 'text', 'class'])){
        return false;
    }
    
    $data = UserKwork::getTypes($type);    
    return $data[$source];
}
?>