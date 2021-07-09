<?php

/**
 * Description of ModelIllegalParameterExecption
 *
 * @author jsosnovsky
 */
namespace Exception;

class ModelIllegalParameterExecption extends \Exception{
    
    public function __construct() {
	parent::__construct('Illegal parameter in model');
    }
}
