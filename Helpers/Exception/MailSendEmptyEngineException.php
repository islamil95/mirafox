<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Exception;

/**
 * Description of MailSendEmptyEngineException
 *
 * @author mirafox
 */
class MailSendEmptyEngineException extends \Exception {

	public function __construct() {
		parent::__construct('Mail engine not found');
	}
}
