<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Exception;

/**
 * Description of VirusTotalManyItemsException
 *
 * @author mirafox
 */
class VirusTotalManyItemsPoolException extends \Exception {
	public function __construct() {
		parent::__construct("To many items in sended VirusTotalPool");
	}
}
